<?php

namespace georgique\yii2\jsonrpc\responses;

use yii\base\BaseObject;

/**
 * Class JsonRpcResponse
 * Common json-rpc server response
 * @property string|int|null $id
 * @property string $jsonrpc
 * @package georgique\yii2\jsonrpc
 */
abstract class JsonRpcResponse extends BaseObject implements \JsonSerializable
{
    public $jsonrpc = '2.0';
    public $id;
}
