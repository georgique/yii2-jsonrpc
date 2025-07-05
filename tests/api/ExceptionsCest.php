<?php
namespace georgique\yii2\jsonrpc\tests\api;

use Tester;
use georgique\yii2\jsonrpc\exceptions\InternalErrorException;
use georgique\yii2\jsonrpc\exceptions\InvalidParamsException;
use georgique\yii2\jsonrpc\exceptions\InvalidRequestException;
use georgique\yii2\jsonrpc\exceptions\MethodNotFoundException;
use georgique\yii2\jsonrpc\exceptions\ParseErrorException;

class ExceptionsCest
{
    public function checkParseError(Tester $I)
    {
        $I->wantTo('Check Parse Error Exception');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('json-rpc', "Some invalid content");
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'error' => [
                'message' => 'Parse error',
                'code' => ParseErrorException::CODE
            ]
        ]);
    }

    public function checkInvalidParams(Tester $I)
    {
        $I->wantTo('Check Invalid Params Exception');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('json-rpc', <<<JSON
{"jsonrpc": "2.0", "method": "demo.method-with-params",
 "params": {"foo": "123", "forgot-about-bar": true}, "id": 1}
JSON
        );
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'error' => [
                'code' => InvalidParamsException::CODE
            ]
        ]);
    }

    public function checkMethodNotFound(Tester $I)
    {
        $I->wantTo('Check Method Not Found Exception');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('json-rpc',
            '{"jsonrpc": "2.0", "method": "missing-method", "params": [1, 2, 3], "id": 1}'
        );
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'error' => [
                'code' => MethodNotFoundException::CODE
            ]
        ]);
    }

    public function checkInternalError(Tester $I)
    {
        $I->wantTo('Check Internal Error Exception');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('json-rpc', '{"jsonrpc": "2.0", "method": "demo.internal-error", "id": 1}');
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'error' => [
                'code' => InternalErrorException::CODE
            ]
        ]);
    }

    public function checkInvalidRequest(Tester $I)
    {
        $I->wantTo('Check Invalid Request Exception');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('json-rpc', '{"some-incorrect-request-data": 100500}');
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'error' => [
                'code' => InvalidRequestException::CODE
            ]
        ]);
    }
}
