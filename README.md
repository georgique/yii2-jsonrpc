# Yii2 JSON-RPC

Yii2 extension for JSON-RPC server implementation. Works not as Remote Procedure Call, but Remote Action Call, therefor leaves all Yii2 power for your service. CURRENTLY IN DEVELOPMENT. CONTRIBUTION, TESTING AND FEEDBACK ARE WELCOME.

## Features
* Uses full Yii2 power, because method string is translated into a route. 
* Lightweight.
* Fully [JSON-RPC 2.0](http://www.jsonrpc.org/specification) compliant.


## Usage
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

// There are some minor side-effects of this solutions, because original request is made to the
// entry point, not to the target controller and action. Be careful working with Request object,
// especially when working on access restriction to the target actions. For example, you want an
// action to be reached only with GET verb only, but you do POST request to the endpoint. In that
// case you will get Internal Error because access will be denied.
class ExampleController extends \yii\web\Controller {

    // Note that URL patterns won't be used to resolve the method - this would not be resourse-wise.
    // Method string should simply be [[module.]controller.]action where module and controller parts
    // can be omitted, so default module and index controller will be used.
    public function actionTry() {
        return "You've got it!";
    }

    // Method params are directly translated into action arguments. Make sure your call matches action
    // signature.
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
