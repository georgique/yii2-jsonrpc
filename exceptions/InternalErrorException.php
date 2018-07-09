<?php

namespace georgique\yii2\jsonrpc\exceptions;

use Throwable;

/**
 * Class InternalErrorException
 * @package georgique\yii2\jsonrpc\exceptions
 */
class InternalErrorException extends JsonRpcException
{
    const CODE = -32603;
    public function __construct(string $message = "", Throwable $previous = null)
    {
        parent::__construct($message, static::CODE, $previous);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Internal error';
    }
}
