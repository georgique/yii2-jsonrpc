<?php
namespace api\tests;

use tests\Tester;
use yii\helpers\Json;

class BatchRequestsCest
{
    public function checkSimpleBatchRequest(Tester $I)
    {
        $I->wantTo('Check that simple batch request is working');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('json-rpc', <<<JSON
[
	{"jsonrpc": "2.0", "method": "demo.some-method", "id": 1},
	{"jsonrpc": "2.0", "method": "demo.method-with-params", "params": {"foo": "fubar", "bar": "baz"}, "id": 4}
]
JSON
        );
        $I->seeResponseCodeIs(200);

        $I->seeResponseEqualsJson(Json::decode(<<<RESPONSE
[{"jsonrpc":"2.0","result":"Some response","id":1},{"jsonrpc":"2.0","result":["fubar","baz"],"id":4}]
RESPONSE
        ));
    }

    public function checkDifferentParamsStyle(Tester $I)
    {
        $I->wantTo('Check different params style: named and non-named');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('json-rpc', <<<JSON
[
	{"jsonrpc": "2.0", "method": "demo.sum", "params": [10, 5], "id": 1},
	{"jsonrpc": "2.0", "method": "demo.sum", "params": {"a": 5, "b": 3}, "id": 2},
	{"jsonrpc": "2.0", "method": "demo.method-with-params", "params": ["b", "c"], "id": 2}
]
JSON
        );
        $I->seeResponseCodeIs(200);

        $I->seeResponseEqualsJson(Json::decode(<<<RESPONSE
[{"jsonrpc":"2.0","result":15,"id":1},{"jsonrpc":"2.0","result":8,"id":2},{"jsonrpc":"2.0","result":["b","c"],"id":2}]
RESPONSE
        ));
    }

    public function checkMixedResponse(Tester $I)
    {
        $I->wantTo('Check success + error');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('json-rpc', <<<JSON
[
	{"jsonrpc": "2.0", "method": "demo.some-method", "id": 1},
	{"jsonrpc": "2.0", "method": "demo.method-with-params", "params": {"invalid-params-here": true}, "id": 4}
]
JSON
        );
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson(Json::decode(<<<RESPONSE
[
    {"jsonrpc":"2.0","result":"Some response","id":1},
    {"jsonrpc":"2.0","error":{"code":-32602,"message":"Invalid params"},"id":4}
]
RESPONSE
        ));
    }

    public function checkMixedResponseWithNotification(Tester $I)
    {
        $I->wantTo('Check success + error + silent notification');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('json-rpc', <<<JSON
[
	{"jsonrpc": "2.0", "method": "demo.some-method", "id": 1},
	{"jsonrpc": "2.0", "method": "demo.method-with-params", "params": {"invalid-params-here": true}, "id": 4},
	{"jsonrpc": "2.0", "method": "some invalid route to notification"}
]
JSON
        );
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson(Json::decode(<<<RESPONSE
[
    {"result":"Some response","jsonrpc":"2.0","id":1},
    {"error":{"code":-32602,"message":"Invalid params"},"jsonrpc":"2.0","id":4}
]
RESPONSE
        ));
    }

    public function checkMultipleErrors(Tester $I)
    {
        $I->wantTo('Check 2 errors');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('json-rpc', <<<JSON
[
	{"jsonrpc": "1.99", "method": "demo.some-method", "id": 1},
	{"jsonrpc": "2.0", "method": "missing", "id": 4}
]
JSON
        );
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson(Json::decode(<<<RESPONSE
[
    {"error":{"code":-32600,"message":"Invalid Request"},"jsonrpc":"2.0","id":1},
    {"error":{"code":-32601,"message":"Method not found: missing."},"jsonrpc":"2.0","id":4}
]
RESPONSE
        ));
    }

    public function checkParseError(Tester $I)
    {
        $I->wantTo('Check parse error with batch request');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('json-rpc', <<<JSON
[
	{"jsonrpc": "2.0", "method": "demo.some-method", "id": 1}
	invalid json
	{"jsonrpc": "2.0", "method": "missing", "id": 4}
]
JSON
        );
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson(Json::decode(<<<RESPONSE
{"error":{"code":-32700,"message":"Parse error"},"jsonrpc":"2.0","id":null}
RESPONSE
        ));
    }

    public function checkInvalidBatch(Tester $I)
    {
        $I->wantTo('Check invalid batch');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('json-rpc', '[1, 2, 3]');
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson(Json::decode(<<<RESPONSE
[
    {"error":{"code":-32600,"message":"Invalid Request"},"jsonrpc":"2.0","id":null},
    {"error":{"code":-32600,"message":"Invalid Request"},"jsonrpc":"2.0","id":null},
    {"error":{"code":-32600,"message":"Invalid Request"},"jsonrpc":"2.0","id":null}
]
RESPONSE
        ));
    }

    public function checkEmptyArray(Tester $I)
    {
        $I->wantTo('Check empty array');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('json-rpc', '[]');
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson(Json::decode(<<<RESPONSE
{"error":{"code":-32600,"message":"Invalid Request"},"jsonrpc":"2.0","id":null}
RESPONSE
        ));
    }
}
