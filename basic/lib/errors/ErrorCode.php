<?php
namespace app\lib\errors;

class ErrorCode
{
    const E_TOKEN_REQUIRED = 20001;
    const E_NOT_AUTHORIZED = 20002;
    const E_DATA_NOT_FOUND = 20003;
    const E_DATA_VALIDATION_FAILED = 20004;
    const E_CANNOT_REPEAT_ADD = 20005;
    const E_NOT_POWERED = 20006;

    const E_DATA_EXIST = 23002;
    const E_USER_NOT_EXIST = 23003;
    const E_EMAIL_NEED = 23004;
    const E_SIGN_IS_SENT = 23005;

    const E_HTTP_METHOD_NOT_SUPPORTED = 40001;
    const E_SINGLE_SIGN_ON_REQUIRED = 40002;

    const E_SERVER_INTERNAL_ERROR = 50001;

    protected static $messages = [
        self::E_TOKEN_REQUIRED => 'An Access Token is required.',
        self::E_DATA_NOT_FOUND => 'Data not found',
        self::E_DATA_VALIDATION_FAILED => 'Data validation failed',
        self::E_CANNOT_REPEAT_ADD => '不能重复添加角色名',
        self::E_NOT_POWERED => '没有权限',

        self::E_NOT_AUTHORIZED => 'You are not authorized to perform this action.',
        self::E_SINGLE_SIGN_ON_REQUIRED => 'Single Sign-On is required for this account.',
        self::E_HTTP_METHOD_NOT_SUPPORTED => 'HTTP Method not supported.',

        self::E_SERVER_INTERNAL_ERROR => 'Server Internal Error.',

        self::E_DATA_EXIST => 'Data already exist.',
        self::E_USER_NOT_EXIST => 'User not exist.',
        self::E_EMAIL_NEED => 'Email is needed.',
        self::E_SIGN_IS_SENT => 'Sign is sent',
    ];

    public static function message($code, $params = [])
    {
        if (isset(static::$messages[$code])) {
            $message = static::$messages[$code];
        } else {
            $message = "Unknown error code: {$code}";
        }

        return $message;
    }
}
