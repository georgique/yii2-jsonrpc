<?php

namespace georgique\yii2\jsonrpc;

use yii\base\BaseObject;

/**
 * Class JsonRpcRequest
 * Dumb class describing JSON-RPC request.
 * @author George Shestayev george.shestayev@gmail.com
 * @package georgique\yii2\jsonrpc
 */
class JsonRpcRequest extends BaseObject {

    public $id;
    public $route;
    public $params = [];

}
