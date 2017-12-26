<?php

namespace georgique\yii2\jsonrpc;

class Action extends \yii\base\Action {

    /**
     * Validates structure of JSON-RPC request.
     * @param $params
     * @return bool
     * @throws Exception
     */
    protected function validateJsonRpcRequest($rpcRequest) {
        $compulsoryParams = ['jsonrpc', 'method'];
        $optionalParams = ['params', 'id'];

        // Checking for compulsory params passed in request
        $compulsoryParamsMissing = array_diff($compulsoryParams, array_keys($rpcRequest));
        if ($compulsoryParamsMissing) {
            throw new Exception('Not a valid JSON-RPC 2.0 request.', 32600);
        }

        // Checking JSON-RPC version
        if ($rpcRequest['jsonrpc'] !== '2.0') {
            throw new Exception('Not a valid JSON-RPC 2.0 request.', 32600);
        }

        // Checking if there are non-specification params passed
        $wrongParams = array_diff(array_keys($rpcRequest), array_merge($compulsoryParams, $optionalParams));
        if ($wrongParams) {
            throw new Exception('Not a valid JSON-RPC 2.0 request.', 32600);
        }

        return true;
    }

    protected function validationJsonRpcRequestArray($rpcRequestArray) {
        if (is_array($rpcRequestArray)) {
            $responseArray = [];
            foreach ($rpcRequestArray as $rpcRequest) {
                $responseArray[] = $this->validateJsonRpcRequest($rpcRequest);
            }
        }
        elseif (is_object($rpcRequestArray)) {
        }
    }


    public function runWithParams($params)
    {
        $request = \Yii::$app->request;
        $bodyParams = $request->getBodyParams();
        if (is_object($bodyParams)) {
            return 'object';
        }
        else if (is_array($bodyParams)) {
            return 'array';
        }

        return 'nothing';
    }
}
