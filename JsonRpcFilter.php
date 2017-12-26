<?php

namespace georgique\yii2\jsonrpc;

use \Yii;
use yii\base\ActionFilter;
use yii\base\BootstrapInterface;
use yii\web\Response;

class JsonRpcFilter extends ActionFilter implements BootstrapInterface {

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $response = Yii::$app->getResponse();
        $response->format = Response::FORMAT_JSON;
        $response->acceptMimeType = 'application/json';
        $response->acceptParams = [];
    }

}
