<?php

namespace georgique\yii2\jsonrpc\exceptions;

/**
 * Class InvalidRequestException
 * @package georgique\yii2\jsonrpc\exceptions
 */
class InvalidRequestException extends JsonRpcException
{
    const CODE = -32600;

    public function __construct($message = "", \Exception $previous = null)
    {
        parent::__construct($message, static::CODE, $previous);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Invalid Request';
    }
}
