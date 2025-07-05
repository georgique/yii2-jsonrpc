<?php

namespace georgique\yii2\jsonrpc\exceptions;

/**
 * Class InvalidRequestException
 * @package georgique\yii2\jsonrpc\exceptions
 */
class InvalidRequestException extends JsonRpcException
{
    public const CODE = -32600;

    public function __construct(string $message = "", $data = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, static::CODE, $data, $previous);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Invalid Request';
    }
}
