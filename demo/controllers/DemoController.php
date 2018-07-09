<?php

namespace app\controllers;

use yii\web\Controller;

class DemoController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * @return string
     */
    public function actionSomeMethod()
    {
        return "Some response";
    }

    /**
     * @param $foo
     * @param $bar
     * @return array
     */
    public function actionMethodWithParams($foo, $bar)
    {
        return [$foo, $bar];
    }

    /**
     * @param $foo
     */
    public function actionNotification($foo)
    {
        // this is notification demo
        // we are not expecting any response
    }

    /**
     * @throws \Exception
     */
    public function actionInternalError()
    {
        throw new \Exception("say what again");
    }

    /**
     * @param int $a
     * @param int $b
     * @return int
     */
    public function actionSum(int $a, int $b)
    {
        return $a + $b;
    }

    public function actionSumList($params)
    {
        //var_dump($params);
        return array_reduce($params, function ($acc, $item) {
            $acc += $item;
            return $acc;
        }, 0);
    }
}
