<?php

namespace georgique\yii2\jsonrpc;

use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Class JsonRpcError
 * @property int $code
 * @property string $message
 * @property array $data
 * @package georgique\yii2\jsonrpc
 */
class JsonRpcError implements \JsonSerializable
{
    public $code;
    public $message;
    public $data;

    /**
     * JsonRpcError constructor.
     * @param \Throwable $exception
     */
    public function __construct(\Throwable $exception)
    {
        $this->code = $exception->getCode();
        $this->message = $this->getExceptionMessage($exception);

        if (defined('YII_DEBUG') && YII_DEBUG) {
            $this->data = $this->convertExceptionToArray($exception);
        }
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        $fields = ArrayHelper::toArray($this);
        if (!isset($this->data)) {
            unset($fields['data']);
        }

        return $fields;
    }

    /**
     * @param \Throwable $exception
     * @return string
     */
    protected function getExceptionMessage(\Throwable $exception)
    {
        $message = $exception->getMessage();
        return !empty($message) ?
            $message :
            (method_exists($exception, 'getName') ? $exception->getName() : '');
    }

    /**
     * @param \Throwable $exception
     * @return array
     */
    protected function convertExceptionToArray(\Throwable $exception)
    {
        $errorArray = [];

        $errorArray['type'] = get_class($exception);
        if (!$exception instanceof UserException) {
            $errorArray += [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'stack-trace' => explode("\n", $exception->getTraceAsString())
            ];

            if ($exception instanceof \yii\db\Exception) {
                $errorArray['error-info'] = $exception->errorInfo;
            }
        }

        if (($prev = $exception->getPrevious()) !== null) {
            $errorArray['previous'] = new JsonRpcError($prev);
        }

        return $errorArray;
    }
}
