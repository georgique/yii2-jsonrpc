<?php

namespace georgique\yii2\jsonrpc\exceptions;

/**
 * Class ParseErrorException
 * @package georgique\yii2\jsonrpc\exceptions
 */
class ParseErrorException extends JsonRpcException
{
    public const CODE = -32700;

    public function __construct(string $message = "", $data = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, static::CODE, $data, $previous);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Parse error';
    }
}
