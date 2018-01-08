
# yii2-jsonrpc

This is Yii2-based JSON-RPC server implementation. CURRENTLY IN DEVELOPMENT. CONTRIBUTION WELCOME.

## Features
* Uses full Yii2 power, because method string is translated into a route. You can keep using all the Yii2 feature
such as routing, access control, etc.
Examples:
`{jsonrpc: "2.0", "method": "foo", "id": 1} -> route /foo -> {action: foo, controller: index, module: default}`
`{jsonrpc: "2.0", "method": "foo.bar": "id": 2} -> route /foo/bar -> {action: bar, controller: foo, module: default}`
`{jsonrpc: "2.0", "method": "foo.bar.baz": "id": 3} -> route /foo/bar/baz -> {action: baz, controller: bar, module: foo}`
* Supports batch processing.

## Usage notes
* Method is not translated to route as an URL. It should contain actual actual module name, controller and route splitted by dots. So if you have a following URL rule:
`'do-some-stuff' => 'api1/doer/stuff'`
your method string should not be `'do-some-stuff'`, but `'api1.doer.stuff'`. Remember that you are just remotely calling procedures, which in Yii2 terms are actions.
* Note that action is called internally which causes some restrictions on them. One of them is that called action has to approve verb which you use originally for making a JSON-RPC call (most probably it will be GET or POST).

## Examples
Entry point:
```php
<?php

namespace app\controllers;

class JsonRpcController extends \georgique\yii2\json-rpc\Controller {
	// Practically you don't need anything else in this controller, 
	// unless you want to customize entry point somehow.
}
```

Controller with target actions which we are going to call:
```php
<?php
namespace app\modules\api1\controllers;

class ExampleController extends \yii\web\Controller {


    public function actionTry() {
        return "You've got it!";
    }

    public function actionTryWithParams($foo) {
        return "Params received: \$foo = $foo.";
    }

}
```

Now this is how calls and responses will look like:
```
-> {"jsonrpc": "2.0", "method": "api1.example.try", "id": 1}
<- {"jsonrpc": "2.0", "result": "You've got it!", "id": 1}

-> {"jsonrpc": "2.0", "method": "api1.example.try-with-params", "params": {"foo": "bar"},	"id": 2}
<- {"jsonrpc": "2.0", "result": "Params received: $foo = bar.", "id": 2}

-> {"jsonrpc": "2.0", "method": "api1.example.garbage", "id": 3}
<- {"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found."}, "id": 3}
```


Author: George Shestayev george.shestayev@gmail.com
