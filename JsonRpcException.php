<?php

namespace georgique\yii2\jsonrpc;

/**
 * Class JsonRpcException
 * Basic exception class for handling JSON-RPC errors.
 * @author George Shestayev george.shestayev@gmail.com
 * @package georgique\yii2\jsonrpc
 */
class JsonRpcException extends \yii\base\Exception {

    public function __construct($id, $message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->id = $id;
    }

    // Request ID if applicable
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

}
