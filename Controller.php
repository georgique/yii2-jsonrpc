<?php

namespace georgique\yii2\jsonrpc;

use Yii;
use yii\filters\ContentNegotiator;
use yii\web\Response;

const JSON_RPC_ERROR_PARSE = -32700;
const JSON_RPC_ERROR_REQUEST_INVALID = -32600;
const JSON_RPC_ERROR_METHOD_NOT_FOUND = -32601;
const JSON_RPC_ERROR_METHOD_PARAMS_INVALID = -32602;
const JSON_RPC_ERROR_INTERNAL = -32603;

// Pass params as function arguments
const JSON_RPC_PARAMS_PASS_FUNCARGS = 1;

// Pass params as request body
const JSON_RPC_PARAMS_PASS_BODY = 2;

class Controller extends \yii\web\Controller {

    /**
     * @var int $paramsPassMethod Defines method to pass params to the target action.
     */
    public $paramsPassMethod = JSON_RPC_PARAMS_PASS_FUNCARGS;

    /**
     * @var array Whether JSON parse should parse objects in `params` as associate arrays or objects
     */
    public $requestParseAsArray = true;

    public function actions()
    {
        return [
            'index' => [
                'class' => Action::class,
                'paramsPassMethod' => $this->paramsPassMethod,
                'requestParseAsArray' => $this->requestParseAsArray,
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    '*' => Response::FORMAT_JSON,
                ],
            ]
        ];
    }

    public function init()
    {
        Yii::$app->set('errorHandler', ['class' => ErrorHandler::className()]);
        Yii::$app->getErrorHandler()->register();

        parent::init();
    }

}
