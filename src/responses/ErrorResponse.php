<?php

namespace georgique\yii2\jsonrpc\responses;

use georgique\yii2\jsonrpc\exceptions\JsonRpcException;
use georgique\yii2\jsonrpc\JsonRpcError;
use georgique\yii2\jsonrpc\JsonRpcRequest;
use ReturnTypeWillChange;

/**
 * Class ErrorResponse
 *
 * @property JsonRpcError $error
 *
 * @package georgique\yii2\jsonrpc\responses
 */
class ErrorResponse extends JsonRpcResponse
{
    public $error;

    /**
     * ErrorResponse constructor.
     * @param JsonRpcException $exception
     * @param JsonRpcRequest|null $request
     */
    public function __construct(JsonRpcException $exception, ?JsonRpcRequest $request = null)
    {
        return parent::__construct([
            'id' => $request?->id,
            'error' => new JsonRpcError($exception)
        ]);
    }

    /**
     * @return array
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        // just for nice output order
        return [
            'jsonrpc' => $this->jsonrpc,
            'error' => $this->error,
            'id' => $this->id
        ];
    }
}
