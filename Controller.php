<?php

namespace georgique\yii2\jsonrpc;

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
            'jsonRpcFilter' => [
                'class' => JsonRpcFilter::className(),
            ],
        ];
    }

}
