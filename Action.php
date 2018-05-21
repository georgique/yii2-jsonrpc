<?php

namespace georgique\yii2\jsonrpc;

use yii\base\InvalidRouteException;
use yii\base\UserException;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
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
class Action extends \yii\base\Action {

    /** @var Request $originalYiiRequest */
    private $originalYiiRequest;

    public $paramsPassMethod;

    /**
     * Parses json body.
     * @param $rawBody
     * @return array
     * @throws JsonRpcException
     */
    public function parseJsonRpcBody($rawBody) {
        try {
            $parameters = Json::decode($rawBody, false);
            if (!$parameters) {
                throw new JsonRpcException(null, 'Could not parse JSON-RPC request body - empty result.', JSON_RPC_ERROR_REQUEST_INVALID);
            }

            return $parameters;
        }
        catch (JsonRpcException $e) {
            throw new JsonRpcException(null, 'Could not parse JSON-RPC request.', JSON_RPC_ERROR_PARSE, $e);
        }
    }


    /**
     * Validates method string and converts it into a route, which is going to be parsed by UrlManager before executing
     * the request.
     * @param $method
     * @return bool|string
     */
    public function parseMethod($method) {
        if (!preg_match('/^[\d\w_\-.]+$/', $method)) {
            return false;
        }

        $parts = explode('.', $method);
        foreach ($parts as $part) {
            // There cannot be empty part in route
            if (empty($part)) return false;
        }

        return implode($parts, '/');
    }

    /**
     * Parses request (parsed JSON object) and prepares JsonRpcRequest object.
     * @param $request
     * @return JsonRpcRequest
     * @throws JsonRpcException
     * @throws \yii\base\InvalidConfigException
     */
    public function parseRequest($request) {
        if (!isset($request->id)) {
            throw new JsonRpcException(null, "The JSON sent is not a correct JSON-RPC request - incorrect id.", JSON_RPC_ERROR_REQUEST_INVALID);
        }
        elseif (!is_int($request->id) && !ctype_digit($request->id)) {
            throw new JsonRpcException(null, "The JSON sent is not a correct JSON-RPC request - incorrect id.", JSON_RPC_ERROR_REQUEST_INVALID);
        }

        if (!isset($request->jsonrpc) || $request->jsonrpc !== '2.0') {
            throw new JsonRpcException($request->id, "The JSON sent is not a correct JSON-RPC request - missing or incorrect version.", JSON_RPC_ERROR_REQUEST_INVALID);
        }

        if (!isset($request->method) || !is_string($request->method) || (!$route = $this->parseMethod($request->method))) {
            throw new JsonRpcException($request->id, "The JSON sent is not a correct JSON-RPC request - missing or incorrect method.", JSON_RPC_ERROR_REQUEST_INVALID);
        }

        $params = [];
        if (isset($request->params)) {
            $params = (array) $request->params;
        }

        return \Yii::createObject([
            'class' => JsonRpcRequest::class,
            'id' => $request->id,
            'route' => $route,
            'params' => $params,
            'originalRequest' => \Yii::$app->request,
        ]);
    }

    /**
     * Parses JSON to an array of JsonRpcRequest.
     * @param $params
     * @return JsonRpcRequest[]
     * @throws JsonRpcException
     */
    public function parseRequests($params) {
        if (is_object($params)) {
            $params = [$params];
        }

        if (is_array($params)) {
            $results = [];
            foreach ($params as $request) {
                try {
                    $result = $this->parseRequest($request);
                }
                catch (\Exception $exception) {
                    $result = $exception;
                }
                $results[] = $result;
            }
            return $results;
        }

        throw new JsonRpcException(null, "The JSON sent is not a correct JSON-RPC request.", JSON_RPC_ERROR_REQUEST_INVALID);
    }

    /**
     * Preserves original Yii request.
     * @param $request
     * @return $this
     */
    protected function preserveYiiRequest() {
        $this->originalYiiRequest = clone \Yii::$app->request;
        return $this;
    }

    protected function restoreYiiRequest() {
        \Yii::$app->request->setUrl($this->originalYiiRequest->getUrl());
        \Yii::$app->request->setPathInfo($this->originalYiiRequest->getPathInfo());
        \Yii::$app->request->setBodyParams($this->originalYiiRequest->getBodyParams());
        \Yii::$app->request->setQueryParams($this->originalYiiRequest->getQueryParams());
        \Yii::$app->request->setRawBody($this->originalYiiRequest->getRawBody());
        return $this;
    }

    /**
     * Executes JSON-RPC request by route.
     * @param JsonRpcRequest $jsonRpcRequest
     * @return JsonRpcException|mixed
     */
    public function executeRequest($jsonRpcRequest) {
        $this->preserveYiiRequest();
        $route = $jsonRpcRequest->route;
        try {
            // Replacing requested URL and path info
            \Yii::$app->request->setUrl($route);
            \Yii::$app->request->setPathInfo(null);

            try {
                $routeWithParams = \Yii::$app->request->resolve();
            }
            catch (NotFoundHttpException $exception) {
                $routeWithParams = false;
            }

            if (!$routeWithParams) {
                $this->restoreYiiRequest();
                return new JsonRpcException($jsonRpcRequest->id, 'Method not found: ' . $route . '.',
                    JSON_RPC_ERROR_METHOD_NOT_FOUND);
            }
            list($routeParsed, $params) = $routeWithParams;

            // Replacing route
            \Yii::$app->requestedRoute = $route;
            if ($this->paramsPassMethod == JSON_RPC_PARAMS_PASS_BODY) {
                \Yii::$app->request->setBodyParams($jsonRpcRequest->params);
                \Yii::$app->request->setRawBody(Json::encode($jsonRpcRequest->params));
                $result = \Yii::$app->runAction($routeParsed, $params);
            }
            else {
                $params += $jsonRpcRequest->params;
                $result = \Yii::$app->runAction($routeParsed, $params);
            }
            $this->restoreYiiRequest();

            return $result;
        }
        catch (\Exception $exception) {
            if ($exception instanceof InvalidRouteException) {
                $result = new JsonRpcException($jsonRpcRequest->id, 'Method not found: ' . $route . '.',
                    JSON_RPC_ERROR_METHOD_NOT_FOUND, $exception);
            }
            else if ($exception instanceof JsonRpcException) {
                $result = $exception;
            }
            else {
                $result = new JsonRpcException($jsonRpcRequest->id, 'Internal error.', JSON_RPC_ERROR_INTERNAL, $exception);
            }
            $this->restoreYiiRequest();

            return $result;
        }
    }


    public function runWithParams($params)
    {
        // Parse errors will be caught and formatted by ErrorHandler
        try {
            $requests = $this->parseJsonRpcBody(file_get_contents('php://input'));
        }
        catch (\Exception $e) {
            return $this->renderError($e, null);
        }

        try {
            $requestObjects = $this->parseRequests($requests);
        }
        catch (\Exception $e) {
            return $this->renderError($e, null);
        }

        $response = [];
        foreach ($requestObjects as $request) {
            if ($request instanceof JsonRpcRequest) {
                $executionResult = $this->executeRequest($request);
                $renderMethod = ($executionResult instanceof \Exception) ? 'renderError' : 'renderSuccess';
                $response[] = $this->$renderMethod($executionResult, $request->id);
            }
            elseif ($request instanceof JsonRpcException) {
                $response[] = $this->renderError($request, $request->id);
            }
            else {
                $response[] =  $this->renderError($request, null);
            }
        }

        return (sizeof($response) == 1) ? array_shift($response) : $response;
    }

    /**
     * Renders exception.
     * @param \Exception $exception
     * @return array
     */
    protected function renderException($exception) {

        $result = [
            'message' => $exception->getMessage(),
            'code' => ($exception instanceof JsonRpcException) ? $exception->getCode() : JSON_RPC_ERROR_INTERNAL
        ];

        $data = [];
        if (!($exception instanceof JsonRpcException)) {
            $data['code'] = $exception->getCode();
            $data['type'] = get_class($exception);
        }

        if (YII_DEBUG) {
            if (!$exception instanceof UserException) {
                $data += [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'stack-trace' => explode("\n", $exception->getTraceAsString())
                ];

                if ($exception instanceof \yii\db\Exception) {
                    $data['error-info'] = $exception->errorInfo;
                }
            }
            if (($prev = $exception->getPrevious()) !== null) {
                $data['previous'] = $this->renderException($prev);
            }
        }

        if (!empty($data)) {
            $result['data'] = $data;
        }

        return $result;
    }

    /**
     * Renders error response.
     * @param $data
     * @param null $id
     * @return array
     */
    protected function renderError($data, $id = null) {
        return [
            'jsonrpc' => '2.0',
            'error' => ($data instanceof \Exception) ? $this->renderException($data) : $data,
            'id' => $id,
        ];
    }

    /**
     * Renders success response.
     * @param $data
     * @param null $id
     * @return array
     */
    protected function renderSuccess($data, $id = null) {
        return [
            'jsonrpc' => '2.0',
            'result' => $data,
            'id' => $id
        ];
    }
}
