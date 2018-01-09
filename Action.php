<?php

namespace georgique\yii2\jsonrpc;

use yii\base\InvalidRouteException;
use yii\base\UserException;
use yii\helpers\Json;

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
                throw new JsonRpcException('Could not parse JSON-RPC request body - empty result.', JSON_RPC_ERROR_REQUEST_INVALID);
            }

            return $parameters;
        }
        catch (JsonRpcException $e) {
            throw new JsonRpcException('Could not parse JSON-RPC request.', JSON_RPC_ERROR_PARSE, $e);
        }
    }


    /**
     * Validates method string and converts it into a route.
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
        if (!isset($request->jsonrpc) || $request->jsonrpc !== '2.0') {
            throw new JsonRpcException("The JSON sent is not a correct JSON-RPC request - missing or incorrect version.", JSON_RPC_ERROR_REQUEST_INVALID);
        }

        if (!isset($request->method) || !is_string($request->method) || (!$route = $this->parseMethod($request->method))) {
            throw new JsonRpcException("The JSON sent is not a correct JSON-RPC request - missing or incorrect method.", JSON_RPC_ERROR_REQUEST_INVALID);
        }

        $params = null;
        if (isset($request->params)) {
            $params = (array) $request->params;
        }

        if (!isset($request->id)) {
            if (!is_int($request->id) && !ctype_digit($request->id)) {
                throw new JsonRpcException("The JSON sent is not a correct JSON-RPC request - incorrect id.", JSON_RPC_ERROR_REQUEST_INVALID);
            }
        }

        return \Yii::createObject([
            'class' => JsonRpcRequest::className(),
            'id' => $request->id,
            'route' => $route,
            'params' => $params
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
                    $result = ($exception instanceof JsonRpcException)
                        ? $exception
                        : new JsonRpcException("Error happened during request parsing.", JSON_RPC_ERROR_INTERNAL, $exception);
                }
                $results[] = $result;
            }
            return $results;
        }

        throw new JsonRpcException("The JSON sent is not a correct JSON-RPC request.", JSON_RPC_ERROR_REQUEST_INVALID);
    }

    /**
     * Executes JSON-RPC request by route.
     * @param JsonRpcRequest $request
     * @return JsonRpcException|mixed
     */
    public function executeRequest($request) {
        $route = $request->route;
        try {
            \Yii::trace("Route requested: '$route'", __METHOD__);
            \Yii::$app->requestedRoute = $route;
            $result = \Yii::$app->runAction($request->route, $request->params);
            return $result;
        }
        catch (\Exception $exception) {
            if ($exception instanceof InvalidRouteException) {
                $result = new JsonRpcException('Method not found.', JSON_RPC_ERROR_METHOD_NOT_FOUND, $exception);
                return $result;
            }
            else if ($exception instanceof JsonRpcException) {
                return $exception;
            }
            else {
                $result = new JsonRpcException('Internal error.', JSON_RPC_ERROR_INTERNAL, $exception);
                return $result;
            }
        }
    }


    public function runWithParams($params)
    {
        // Parse errors will be caught and formatted by ErrorHandler
        $requests = $this->parseJsonRpcBody(file_get_contents('php://input'));
        $requestObjects = $this->parseRequests($requests);

        $response = [];
        foreach ($requestObjects as $request) {
            $executionResult = $this->executeRequest($request);
            $renderMethod = ($executionResult instanceof \Exception) ? 'renderError' : 'renderSuccess';
            $response[] = $this->$renderMethod($executionResult, $request->id);
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
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
        ];

        if (YII_DEBUG) {
            $result['data'] = [
                'type' => get_class($exception)
            ];
            if (!$exception instanceof UserException) {
                $result['data'] += [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'stack-trace' => explode("\n", $exception->getTraceAsString())
                ];

                if ($exception instanceof \yii\db\Exception) {
                    $result['data']['error-info'] = $exception->errorInfo;
                }
            }
        }
        if (($prev = $exception->getPrevious()) !== null) {
            $result['data']['previous'] = $this->renderException($prev);
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
