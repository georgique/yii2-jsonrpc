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

class Controller extends \yii\web\Controller {

    public function actions()
    {
        return [
            'index' => [
                'class' => Action::class,
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
