<?php

namespace georgique\yii2\jsonrpc;

use georgique\yii2\jsonrpc\exceptions\InternalErrorException;
use georgique\yii2\jsonrpc\exceptions\InvalidRequestException;
use georgique\yii2\jsonrpc\exceptions\JsonRpcException;
use georgique\yii2\jsonrpc\exceptions\ParseErrorException;
use georgique\yii2\jsonrpc\responses\ErrorResponse;
use georgique\yii2\jsonrpc\responses\SuccessResponse;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Request;

/**
 * Class Action
 *
 * Standalone action responsible for parsing json-rpc request, executing it
 * and formatting json-rpc response.
 *
 * @author George Shestayev george.shestayev@gmail.com
 * @package georgique\yii2\jsonrpc
 */
class Action extends \yii\base\Action
{

    /** @var Request $originalYiiRequest */
    private $originalYiiRequest;

    /**
     * @var int $paramsPassMethod Defines method to pass params to the target action.
     */
    public $paramsPassMethod;

    /**
     * @var array Whether JSON parse should parse objects in `params` as associate arrays or objects
     */
    public $requestParseAsArray;

    /**
     * Parses json body.
     * @param $rawBody
     * @return mixed
     * @throws InvalidRequestException
     * @throws ParseErrorException
     */
    public function parseJsonRpcBody($rawBody)
    {
        try {
            $parameters = Json::decode($rawBody, false);
            if (!$parameters) {
                throw new InvalidRequestException();
            }

            return $parameters;
        } catch (InvalidArgumentException $e) {
            throw new ParseErrorException('', [], $e);
        }
    }

    /**
     * Preserves original Yii request.
     * @return $this
     */
    protected function preserveYiiRequest()
    {
        $this->originalYiiRequest = clone \Yii::$app->request;
        return $this;
    }

    /**
     * @return $this
     * @throws InvalidConfigException
     */
    protected function restoreYiiRequest()
    {
        \Yii::$app->request->setUrl($this->originalYiiRequest->getUrl());
        \Yii::$app->request->setPathInfo($this->originalYiiRequest->getPathInfo());
        \Yii::$app->request->setBaseUrl($this->originalYiiRequest->getBaseUrl());
        \Yii::$app->request->setBodyParams($this->originalYiiRequest->getBodyParams());
        \Yii::$app->request->setQueryParams($this->originalYiiRequest->getQueryParams());
        \Yii::$app->request->setRawBody($this->originalYiiRequest->getRawBody());
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function runWithParams($params)
    {
        $batchResponse = [];

        try {
            $isBatch = false;
            $batchRequestData = $this->parseJsonRpcBody(\Yii::$app->request->getRawBody());

            if (is_array($batchRequestData)) {
                $isBatch = true;
            } else {
                // For simple processing
                $batchRequestData = [$batchRequestData];
            }

            foreach ($batchRequestData as $requestData) {
                $this->preserveYiiRequest();
                try {
                    $request = new JsonRpcRequest();
                    $request->paramsPassMethod = $this->paramsPassMethod;
                    $request->parseAsArray = $this->requestParseAsArray;

                    $request->load(ArrayHelper::toArray($requestData), '');
                    if ($request->validate()) {
                        $result = $request->execute();
                        if (!is_null($request->id)) {
                            $batchResponse[] = new SuccessResponse($request, $result);
                        }
                    } else {
                        foreach ($request->getFirstErrors() as $attribute => $error) {
                            $request->$attribute = null;
                        }
                        throw new InvalidRequestException();
                    }
                }
                catch (InvalidRequestException $e) {
                    $batchResponse[] = new ErrorResponse($e, $request ?: null);
                }
                catch (JsonRpcException $e) {
                    // We do not return response to notifications
                    if ($request && !is_null($request->id)) {
                        $batchResponse[] = new ErrorResponse($e, $request ?: null);
                    }
                }

                $this->restoreYiiRequest();
            }
        }
        catch (JsonRpcException $e) {
            return new ErrorResponse($e);
        }
        catch (\Throwable $e) {
            return new InternalErrorException('Error while processing request', [], $e);
        }

        return !$isBatch ? array_shift($batchResponse) : $batchResponse;
    }
}
