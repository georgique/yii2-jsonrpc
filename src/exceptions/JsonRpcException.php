<?php

namespace georgique\yii2\jsonrpc\exceptions;

use yii\base\Exception;

/**
 * Class JsonRpcException
 * @package georgique\yii2\jsonrpc\exceptions
 */
class JsonRpcException extends Exception
{

    protected $data;

    public function __construct(string $message = "", $code = 0, $data = [], \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->data = $data;
    }

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

    public function getData() {
        return $this->data;
    }
}
