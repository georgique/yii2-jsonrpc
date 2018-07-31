<?php

namespace georgique\yii2\jsonrpc\exceptions;

/**
 * Class InternalErrorException
 * @package georgique\yii2\jsonrpc\exceptions
 */
class InternalErrorException extends JsonRpcException
{
    const CODE = -32603;
    public function __construct($message = "", \Exception $previous = null)
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
