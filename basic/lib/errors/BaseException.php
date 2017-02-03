<?php
namespace app\lib\errors;

use yii\web\HttpException;

/**
 * ClientException represents an "Client Error" HTTP exception with status code 400.
 *
 * @author liaoshuochao
 * @since 1.0
 */
class BaseException extends HttpException
{
    /**
     * Constructor.
     * @param string $message error message
     * @param integer $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(449, $message, $code, $previous);
    }
}