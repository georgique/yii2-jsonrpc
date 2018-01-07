<?php

namespace georgique\yii2\jsonrpc;

/**
 * Class Exception
 * Basic exception class for handling JSON-RPC errors.
 * @author George Shestayev george.shestayev@gmail.com
 * @package georgique\yii2\jsonrpc
 */
class Exception extends \yii\base\Exception {

    /* @var int Used for identifying request in a batch */
    public $id;

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        switch ($this->code) {
            case JSON_RPC_ERROR_PARSE:
                return 'Parse Error';

            case JSON_RPC_ERROR_REQUEST_INVALID:
                return 'Invalid Request';

            case JSON_RPC_ERROR_METHOD_NOT_FOUND:
                return 'Method not found';

            case JSON_RPC_ERROR_METHOD_PARAMS_INVALID:
                return 'Invalid params';

            case JSON_RPC_ERROR_INTERNAL:
                return 'Internal error';

            default:
                if ($this->code < -32000 && $this->code > -32099) {
                    return 'Server error';
                }
                else {
                    return 'Error';
                }
        }
    }

    public function toJsonRpcFormat() {
        return [
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
            'data' => [
                // TODO Include some useful data here
            ]
        ];
    }

}
