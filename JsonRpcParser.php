<?php

namespace georgique\yii2\jsonrpc;

use yii\base\InvalidParamException;
use yii\helpers\Json;
use yii\web\RequestParserInterface;

class JsonRpcParser implements RequestParserInterface
{
    /**
     * @var bool whether to return objects in terms of associative arrays.
     */
    public $asArray = false;


    /**
     * Parses a HTTP request body.
     * @param string $rawBody the raw HTTP request body.
     * @param string $contentType the content type specified for the request body.
     * @return array parameters parsed from the request body
     * @throws Exception if the body contains invalid json.
     */
    public function parse($rawBody, $contentType)
    {
        try {
            $parameters = Json::decode($rawBody, $this->asArray);
            return $parameters === null ? [] : $parameters;
        } catch (InvalidParamException $e) {
            throw new Exception('Cannot parse JSON-RPC request.', 32700);
        }
    }
}
