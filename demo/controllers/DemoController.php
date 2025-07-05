<?php

namespace app\controllers;

use yii\base\InvalidConfigException;
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
     * @param array $params
     * @return array
     */
    public function actionEcho(array $params)
    {
        return [
            'params' => $params,
            'type' => gettype($params)
        ];
    }

    public function actionEchoObject($params)
    {
        return [
            'params' => $params,
            'type' => gettype($params)
        ];
    }

    public function actionObjectFoo($object)
    {
        return $object->foo;
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

    /**
     * @return int
     * @throws InvalidConfigException
     */
    public function actionSumIntegerList()
    {
        $params = \Yii::$app->request->getBodyParams();
        return array_reduce($params, function ($acc, $item) {
            $acc += is_int($item) ? $item : 0;
            return $acc;
        }, 0);
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function actionDumpRequest()
    {
        $output = "Params received: ";
        $output_chunks = [];
        foreach (\Yii::$app->request->getBodyParams() as $name => $value) {
            $output_chunks[] = "$name = $value\n";
        }
        return $output . implode(', ', $output_chunks) . '.';
    }
}
