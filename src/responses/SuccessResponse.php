<?php

namespace georgique\yii2\jsonrpc\responses;

use georgique\yii2\jsonrpc\JsonRpcRequest;
use ReturnTypeWillChange;

/**
 * Class SuccessResponse
 * @property mixed $result
 * @package georgique\yii2\jsonrpc\responses
 */
class SuccessResponse extends JsonRpcResponse
{
    public $result;

    /**
     * SuccessResponse constructor.
     * @param JsonRpcRequest $request
     * @param $result
     */
    public function __construct(JsonRpcRequest $request, $result)
    {
        return parent::__construct([
            'id' => $request->id,
            'result' => $result
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
            'result' => $this->result,
            'id' => $this->id
        ];
    }
}
