<?php

namespace georgique\yii2\jsonrpc;

use yii\filters\ContentNegotiator;
use yii\web\Response;

/**
 * Class Controller
 * @package georgique\yii2\jsonrpc
 */
class Controller extends \yii\web\Controller
{
    // Pass params as function arguments
    const JSON_RPC_PARAMS_PASS_FUNCARGS = 1;

    // Pass params as request body
    const JSON_RPC_PARAMS_PASS_BODY = 2;

    /**
     * @var int $paramsPassMethod Defines method to pass params to the target action.
     */
    public $paramsPassMethod = self::JSON_RPC_PARAMS_PASS_FUNCARGS;

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => Action::class,
                'paramsPassMethod' => $this->paramsPassMethod
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
                'class' => ContentNegotiator::class,
                'formats' => [
                    '*' => Response::FORMAT_JSON,
                ],
            ]
        ];
    }
}
