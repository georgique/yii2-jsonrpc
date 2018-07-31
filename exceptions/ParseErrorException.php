<?php

namespace georgique\yii2\jsonrpc\exceptions;

/**
 * Class ParseErrorException
 * @package georgique\yii2\jsonrpc\exceptions
 */
class ParseErrorException extends JsonRpcException
{
    const CODE = -32700;

    public function __construct($message = "", \Exception $previous = null)
    {
        parent::__construct($message, static::CODE, $previous);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Parse error';
    }
}
