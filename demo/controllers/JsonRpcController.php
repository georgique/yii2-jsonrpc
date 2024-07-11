<?php

namespace app\controllers;

use georgique\yii2\jsonrpc\Controller;

class JsonRpcController extends Controller
{
    // Practically you don't need anything else in this controller,
    // unless you want to customize entry point somehow.

    // Disable CSRF validation for JSON-RPC POST requests
    public $enableCsrfValidation = false;
}
