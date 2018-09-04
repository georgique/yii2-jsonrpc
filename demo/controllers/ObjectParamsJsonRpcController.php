<?php

namespace app\controllers;

use \georgique\yii2\jsonrpc\Controller;

class ObjectParamsJsonRpcController extends Controller
{
    // Disable CSRF validation for JSON-RPC POST requests
    public $enableCsrfValidation = false;

    public $requestParseAsArray = false;
}
