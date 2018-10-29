<?php

namespace georgique\yii2\jsonrpc;

use georgique\yii2\jsonrpc\exceptions\JsonRpcException;
use georgique\yii2\jsonrpc\exceptions\InternalErrorException;
use georgique\yii2\jsonrpc\exceptions\InvalidParamsException;
use georgique\yii2\jsonrpc\exceptions\MethodNotFoundException;
use yii\base\InlineAction;
use yii\base\InvalidRouteException;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Application;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class JsonRpcRequest
 * @author George Shestayev george.shestayev@gmail.com
 * @package georgique\yii2\jsonrpc
 */
class JsonRpcRequest extends Model
{
    public $id;
    public $jsonrpc;
    public $method;
    public $params = [];

    public $paramsPassMethod;
    public $parseAsArray;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['jsonrpc', 'method'], 'required'],
            ['jsonrpc', 'validateJsonRpc'],
            ['method', 'string'],
            ['params', 'safe'],
            ['id', 'validateId', 'skipOnEmpty' => false]
        ];
    }

    /**
     * Validates request id
     * @param $attribute
     */
    public function validateId($attribute)
    {
        $valid = is_null($this->$attribute)
            || is_numeric($this->$attribute)
            || (is_string($this->$attribute) && $this->$attribute !== '');

        if (!$valid) {
            $this->addError($attribute, 'Invalid ID');
        }
    }

    /**
     * Validates "jsonrpc" request param
     * @param $attribute
     */
    public function validateJsonRpc($attribute)
    {
        if ($this->$attribute !== '2.0') {
            $this->addError($attribute, 'Invalid JsonRPC version');
        }
    }

    /**
     * Validates method string and converts it into a route,
     * which is going to be parsed by UrlManager before executing
     * the request.
     * @param $method
     * @return bool|string
     */
    public function parseMethod($method)
    {
        if (!preg_match('/^[\d\w_\-.]+$/', $method)) {
            return false;
        }

        $parts = explode('.', $method);
        foreach ($parts as $part) {
            // There cannot be empty part in route
            if (empty($part)) {
                return false;
            }
        }

        return implode($parts, '/');
    }

    /**
     * @param $route
     * @param array $params
     * @throws \ReflectionException
     * @throws \yii\base\InvalidConfigException
     * @throws InvalidRouteException
     */
    public function bindParamsArray($route, array $params)
    {
        $assocParams = [];

        $parts = \Yii::$app->createController($route);
        if (!is_array($parts)) {
            throw new InvalidRouteException('Unable to resolve request "' . $route . '"');
        }

        /* @var $controller Controller */
        list($controller, $actionID) = $parts;
        $action = $controller->createAction($actionID);
        if ($action === null) {
            throw new InvalidRouteException('Unable to resolve request "' . $route . '" "'. $actionID .'"');
        }

        if ($action instanceof InlineAction) {
            $method = new \ReflectionMethod($controller, $action->actionMethod);
        } else {
            $method = new \ReflectionMethod($action, 'run');
        }

        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            if (is_array($params) && !empty($params)) {
                $assocParams[$name] = array_shift($params);
            }
        }

        return $assocParams;
    }

    /**
     * Executes JSON-RPC request by route.
     * @throws \georgique\yii2\jsonrpc\exceptions\JsonRpcException
     * @return mixed
     */
    public function execute()
    {
        /* @var Application $app */
        $app = \Yii::$app;
        if (!$route = $this->parseMethod($this->method)) {
            throw new MethodNotFoundException();
        }

        try {
            // Replacing requested URL and path info
            $app->request->setUrl($route);
            \Yii::$app->request->setPathInfo(null);

            try {
                $routeWithParams = $app->request->resolve();
            } catch (NotFoundHttpException $exception) {
                $routeWithParams = false;
            }

            if (!$routeWithParams) {
                throw new MethodNotFoundException();
            }
            list($routeParsed, $params) = $routeWithParams;

            // Replacing route
            $app->requestedRoute = $route;
            if ($this->paramsPassMethod == Controller::JSON_RPC_PARAMS_PASS_BODY) {
                $app->request->setBodyParams($this->params);
                $app->request->setRawBody(Json::encode($this->params));
                $result = $app->runAction($routeParsed, $params);
            } else {
                if (ArrayHelper::isAssociative($this->params)) {
                    $params += $this->params;
                } else {
                    // allow non-named parameters
                    $params += $this->bindParamsArray($routeParsed, $this->params);
                }

                if (is_array($params) && !$this->parseAsArray) {
                    foreach ($params as $key => $value) {
                        $params[$key] = Json::decode(Json::encode($value), false);
                    }
                }

                $result = $app->runAction($routeParsed, $params);
            }
        } catch (JsonRpcException $e) {
            throw $e;
        } catch (BadRequestHttpException $e) {
            throw new InvalidParamsException('Invalid params', $e);
        } catch (InvalidRouteException $e) {
            throw new MethodNotFoundException('Method not found: ' . $route . '.', $e);
        } catch (\Exception $e) {
            throw new InternalErrorException('Internal error', $e);
        }
        return $result;
    }
}
