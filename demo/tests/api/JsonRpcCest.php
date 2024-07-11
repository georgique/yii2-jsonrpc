<?php
namespace api\tests;

use tests\Tester;
use yii\helpers\Json;

class JsonRpcCest
{
    public function checkSomeMethod(Tester $I)
    {
        $I->wantTo('Check that demo setup is working');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('json-rpc', [
            "jsonrpc" => "2.0",
            "method" => "demo.some-method",
            "id" => 1
        ]);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'result' => 'Some response'
        ]);
    }

    public function checkMethodWithParams(Tester $I)
    {
        $I->wantTo('Check method with params');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('json-rpc', <<<JSON
{"jsonrpc": "2.0", "method": "demo.method-with-params", "params": {"bar": "123", "foo": "baz"}, "id": 1}
JSON
        );
        $I->seeResponseCodeIs(200);

        $I->seeResponseEqualsJson(Json::decode(<<<JSON
{"result":["baz","123"],"jsonrpc":"2.0","id":1}
JSON
        ));
    }

    public function checkNotification(Tester $I)
    {
        $I->wantTo('Check empty response for notification');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('json-rpc', <<<JSON
{"jsonrpc": "2.0", "method": "demo.notification", "params": {"foo": true}}
JSON
        );
        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('');
    }
}
