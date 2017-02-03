<?php
namespace app\lib\errors;

/**
 * ClientException represents an "Client Error" HTTP exception with status code 400.
 *
 * @author liaoshuochao
 * @since 1.0
 */
class ClientException extends BaseException
{
    /**
     * ClientException constructor.
     * @param null|string $code
     * @param array $params
     * @param \Exception|null $previous
     */
    public function __construct($code, $params = [], \Exception $previous = null)
    {
        parent::__construct(ErrorCode::message($code, $params), $code, $previous);
    }
}