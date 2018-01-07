<?php

namespace georgique\yii2\jsonrpc;

use yii\base\InvalidRouteException;
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
     * @throws Exception
     */
    public function parseJsonRpcBody($rawBody) {
        try {
            $parameters = Json::decode($rawBody, false);
            if (!$parameters) {
                throw new Exception('Not a valid JSON-RPC 2.0 request.', JSON_RPC_ERROR_PARSE);
            }

            return $parameters;
        }
        catch (Exception $e) {
            throw new Exception('Not a valid JSON-RPC 2.0 request.', JSON_RPC_ERROR_PARSE);
        }
    }


    /**
     * Validates method string and converts it into a route.
     * @param $method
     * @return bool|string
     */
    public function parseMethod($method) {
        if (!preg_match('/^[\d\w_.]+/$', $method)) {
            return false;
        }

        $parts = explode($method, '.');
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
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function parseRequest($request) {
        if (!isset($request->jsonrpc) || $request->jsonrpc !== '2.0') {
            // TODO Customize error message to make the problem clear
            throw new Exception("The JSON sent is not a correct JSON-RPC request.", JSON_RPC_ERROR_INVALID_REQUEST);
        }

        if (!isset($request->method) || !is_string($request->method) || (!$route = $this->parseMethod($request->method))) {
            throw new Exception("The JSON sent is not a correct JSON-RPC request.", JSON_RPC_ERROR_INVALID_REQUEST);
        }

        $params = null;
        if (isset($request->params)) {
            if (!is_array($request->params)) {
                throw new Exception("The JSON sent is not a correct JSON-RPC request.", JSON_RPC_ERROR_INVALID_REQUEST);
            }
            $params = $request->params;
        }

        if (!isset($request->id)) {
            if (!is_scalar($request->id)) {
                throw new Exception("The JSON sent is not a correct JSON-RPC request.", JSON_RPC_ERROR_INVALID_REQUEST);
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
     * @throws Exception
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
                    if ($exception instanceof Exception) {
                        if (isset($request->id) && is_string($request->id) || is_int($request->id)) {
                            $exception->id = $request->id;
                        }
                        $result = $exception;
                    }
                    else {
                        $result = new Exception("Error happened during request parsing.", JSON_RPC_ERROR_INTERNAL);
                    }
                }
                $results[] = $result;
            }
            return $results;
        }

        throw new Exception("The JSON sent is not a correct JSON-RPC request.", JSON_RPC_ERROR_INVALID_REQUEST);
    }

    /**
     * Executes JSON-RPC request by route.
     * @param JsonRpcRequest $request
     * @return Exception|mixed
     */
    public function executeRequest($request) {
        $route = $request->route;
        try {
            \Yii::trace("Route requested: '$route'", __METHOD__);
            $this->requestedRoute = $route;
            $result = \Yii::$app->runAction($request->route, $request->params);
            return $result;
        }
        catch (\Exception $exception) {
            if ($exception instanceof InvalidRouteException) {
                return new Exception('Method not found.', JSON_RPC_ERROR_METHOD_NOT_FOUND);
            }
            else if ($exception instanceof Exception) {
                return $exception;
            }
            else {
                return new Exception('Internal error.', JSON_RPC_ERROR_INTERNAL);
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
            if ($request instanceof Exception) {
                $response[] = [
                    'jsonrpc' => '2.0',
                    'error' => $request->toJsonRpcFormat(),
                    'id' => $request->id
                ];
            }
            else {
                $executionResult = $this->executeRequest($request);
                if ($executionResult instanceof Exception) {
                    $response[] = [
                        'jsonrpc' => '2.0',
                        'error' => $executionResult->toJsonRpcFormat(),
                        'id' => $request->id
                    ];
                } else {
                    $response[] = [
                        'jsonrpc' => '2.0',
                        'result' => $executionResult,
                        'id' => $request->id
                    ];
                }
            }
        }

        return (sizeof($response) == 1) ? array_shift($response) : $response;
    }
}
