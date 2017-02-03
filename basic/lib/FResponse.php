<?php

namespace app\lib;

use app\lib\errors\ErrorCode;
use Yii;
use yii\base\Object;


/**
 * Class FResponse
 *
 * 作者: yuanzhouwen(yuanzhouwe@126.com)
 * 时间: 2014-05-10 01:19:41
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 */
class FResponse {

    /**
     * header
     * @var array
     */
    protected $header = array();

    /**
     * 设字符集，如果设置过 Content-type 为 json, 返回false
     *
     * @param string $encoding
     *
     * @return bool
     */
    public function setCharacterEncoding($encoding = 'utf-8') {

        // json 不设编码
        if ($this->header['Content-type'] == 'application/json') return false;

        $this->setHeader('Content-type', 'text/html; charset=' . $encoding);
        return true;
    }


    /**
     * 设置 header
     *
     * @param $headerKey
     * @param $headerValue
     */
    public function setHeader($headerKey, $headerValue) {
        $this->header[$headerKey] = $headerValue;
    }

    public function setContentType($contentType) {

        if ($contentType == 'json') {
            $this->setHeader('Content-type', 'application/json');
        }
    }

    /**
     * 文本输出内容
     *
     * @param $content string 内容
     */
    public function write($content = null) {
        ob_clean();
        ob_start();
        if ($content) echo $content;
        foreach ($this->header as $h_key => $h_value) {
            header("{$h_key}: $h_value");
        }
        exit();

    }

    /**
     * 输出内容，可以是数组，可以是文本
     *
     * @param $mix
     * @return bool
     */
    public static function output($mix) {

        $response = new self;

        if (is_array($mix)) {
            if (!isset($mix['msg'])) {
                $msg = ErrorCode::message($mix['code']);
                $msg && $mix['msg'] = $msg;
            }
            if (!isset($mix['data'])) {
                $mix['data'] = new \stdClass();
            }
            $response->setContentType('json');

            $response->write(json_encode($mix));;
        } elseif (is_string($mix)) {
            $response->write($mix);
        }

        return true;
    }

    /**
     * 返回数组
     * @param $mix
     * @return array
     */
    public static function outputArr($mix) {
        if (is_array($mix)) {
            if (!isset($mix['msg'])) {
                $msg = ErrorCode::message($mix['code']);
                $msg && $mix['msg'] = $msg;
            }
            if (!isset($mix['data'])) {
                $mix['data'] = new Object();
            }
        }

        return $mix;
    }

    public static function sendHeader($headerKey, $headerValue = null) {

        if (is_numeric($headerKey) && $headerValue == null) {
            self::sendStatusHeader($headerKey);
        } else {
            header($headerKey . ': ' . $headerValue);
        }
    }

    /**
     * 发送HTTP状态
     *
     * @param integer $code 状态码
     *
     * @return void
     */
    public static function sendStatusHeader($code) {
        static $httpStatusMap = array(
            // Success 2xx
            200 => 'OK',
            // Redirection 3xx
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily ', // 1.1
            // Client Error 4xx
            400 => 'Bad Request',
            403 => 'Forbidden',
            404 => 'Not Found',
            // Server Error 5xx
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
        );

        if (isset($httpStatusMap[$code])) {
            header('HTTP/1.1 ' . $code . ' ' . $httpStatusMap[$code]);
            // 确保FastCGI模式下正常
            header('Status:' . $code . ' ' . $httpStatusMap[$code]);
        }
    }

    /**
     * 跳转
     *
     * @param $url
     * @param null $target
     * @return bool
     */
    public static function redirect($url, $target = null) {
        global $_F;
        if ($url == 'r') {
            $url = $_SERVER ['HTTP_REFERER'];
        }

        if ($_F ['in_ajax']) {
            self::output(array('result' => 'redirect', 'redirect_url' => $url, 'target' => $target));
            exit;
        }

        if ($target == 301) {
            self::sendStatusHeader(301);
            self::sendHeader('Location', $url); // 跳转到新地址
        } elseif ($target) {
            echo "<script> {$target}.location.href = '{$url}'; </script>";
        } else {
            header("location: " . $url);
        }

        exit;
    }

    /**
     * 刷新页面
     */
    public static function refresh() {
        self::redirect('r');
    }
}
