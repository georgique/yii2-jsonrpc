<?php

namespace georgique\yii2\jsonrpc\exceptions;

use yii\base\Exception;

/**
 * Class JsonRpcException
 * @package georgique\yii2\jsonrpc\exceptions
 */
class JsonRpcException extends Exception
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        if ($this->code >= -32099 && $this->code <= -32000) {
            return 'Server error';
        } else {
            return 'JSON-RPC Error';
        }
    }
}
