<?php

namespace georgique\yii2\jsonrpc\exceptions;

/**
 * Class MethodNotFoundException
 * @package georgique\yii2\jsonrpc\exceptions
 */
class MethodNotFoundException extends JsonRpcException
{
    const CODE = -32601;

    public function __construct($message = "", \Exception $previous = null)
    {
        parent::__construct($message, static::CODE, $previous);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Method not found';
    }
}
