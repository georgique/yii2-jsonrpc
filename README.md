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

Author: George Shestayev george.shestayev@gmail.com
