<?php

namespace georgique\yii2\jsonrpc;

use georgique\yii2\jsonrpc\exceptions\InternalErrorException;
use georgique\yii2\jsonrpc\responses\ErrorResponse;
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
        $response->data = new ErrorResponse(new InternalErrorException('Error while processing request', [], $exception));
        $response->send();
    }

}