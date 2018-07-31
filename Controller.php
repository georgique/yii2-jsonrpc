<?php

namespace georgique\yii2\jsonrpc;

use yii\filters\ContentNegotiator;
use yii\web\Response;
use Yii;

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
                'class' => ContentNegotiator::class,
                'formats' => [
                    '*' => Response::FORMAT_JSON,
                ],
            ]
        ];
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        Yii::$app->set('errorHandler', ['class' => ErrorHandler::class]);
        Yii::$app->getErrorHandler()->register();
        parent::init();
    }
}
