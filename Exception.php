<?php

namespace georgique\yii2\jsonrpc;

class Exception extends \yii\base\Exception {

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        switch ($this->code) {
            case 32700:
                return 'Parse Error';

            case 32600:
                return 'Invalid Request';

            case 32601:
                return 'Method not found';

            case 32602:
                return 'Invalid params';

            case 32603:
                return 'Internal error';

            default:
                if ($this->statusCode > 32000 && $this->statusCode < 32099) {
                    return 'Server error';
                }
                else {
                    return 'Error';
                }
        }
    }

}
