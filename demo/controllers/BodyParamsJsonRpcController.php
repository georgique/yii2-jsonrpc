<?php

namespace app\controllers;

use \georgique\yii2\jsonrpc\Controller;

class BodyParamsJsonRpcController extends Controller
{
    // Disable CSRF validation for JSON-RPC POST requests
    public $enableCsrfValidation = false;

    public $paramsPassMethod = self::JSON_RPC_PARAMS_PASS_BODY;
}
