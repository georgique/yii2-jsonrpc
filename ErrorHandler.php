<?php

namespace georgique\yii2\jsonrpc;

use Yii;
use yii\base\UserException;
use yii\web\Response;

/**
 * Class ErrorHandler
 * This class is supposed to catch any unhandled errors and format them into JSON-RPC response.
 * @author George Shestayev george.shestayev@gmail.com
 * @package georgique\yii2\jsonrpc
 */
class ErrorHandler extends \yii\base\ErrorHandler
{

    /**
     * Renders the exception.
     * @param \Exception|\Error $exception the exception to be rendered.
     */
    protected function renderException($exception)
    {
        if (Yii::$app->has('response')) {
            $response = Yii::$app->getResponse();
            // reset parameters of response to avoid interference with partially created response data
            // in case the error occurred while sending the response.
            $response->isSent = false;
            $response->stream = null;
            $response->data = null;
            $response->content = null;
        } else {
            $response = new Response();
        }

        $response->setStatusCode(200);
        $response->data = $this->convertExceptionToArray($exception);
        $response->send();
    }

    /**
     * Converts an exception into an array.
     * @param \Exception|\Error $exception the exception being converted
     * @return array the array representation of the exception.
     */
    protected function convertExceptionToArray($exception)
    {
        if (!YII_DEBUG && !$exception instanceof JsonRpcException) {
            $exception = new JsonRpcException(null, 'Internal error.', JSON_RPC_ERROR_INTERNAL);
        }

        $errorArray = [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
        ];
        if (YII_DEBUG) {
            $errorArray['data'] = [
                'type' => get_class($exception)
            ];
            if (!$exception instanceof UserException) {
                $errorArray['data'] += [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'stack-trace' => explode("\n", $exception->getTraceAsString())
                ];

                if ($exception instanceof \yii\db\Exception) {
                    $errorArray['data']['error-info'] = $exception->errorInfo;
                }
            }
        }
        if (($prev = $exception->getPrevious()) !== null) {
            $errorArray['data']['previous'] = $this->convertExceptionToArray($prev);
        }

        return [
            'jsonrpc' => '2.0',
            'error' => $errorArray,
            'id' => ($exception instanceof JsonRpcException) ? $exception->id : null,
        ];
    }

}
