<?php

namespace georgique\yii2\jsonrpc\exceptions;

/**
 * Class InternalErrorException
 * @package georgique\yii2\jsonrpc\exceptions
 */
class InternalErrorException extends JsonRpcException
{
    public const CODE = -32603;
    public function __construct(string $message = "", $data = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, static::CODE, $data, $previous);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Internal error';
    }
}
