<?php
/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2016/12/20
 * Time: 15:59
 */

namespace app\lib;


class WebDAV
{
    const URI = 'http://192.168.36.249/dav/';
    const DEBUG = TRUE;
    public $header = [];
    public $curl;
    public $args;
    public $result;
    public $str_result;
    public $debug;

    /**
     * 构造函数
     * @param $username
     * @param $password
     */
    public function __construct($username, $password, $header = [])
    {
        $user = base64_encode($username . ':' . $password);
        $this->header = [
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Authorization: Basic " . $user
        ];
        if (!empty($header)) {
            $this->addHeader($header);
        }
    }

    /**
     * 添加header信息
     * @param $header
     */
    private function addHeader($header)
    {
        $this->header = array_merge($this->header, $header);
    }

    /**
     * 初始化curl
     * @param $method
     */
    public function initCurl($method, $location = '')
    {
        $this->result = '';
        $this->debug = '';
        $this->str_result = '';
        $this->curl = curl_init(self::URI . $location);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);         //设置访问方法
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, TRUE);         //以字符串返回执行结果
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->header);    //设置头信息
    }

    /**
     * 发起请求
     * @param $method
     * @param array $data
     */
    public function request($method, $data = [], $path = '')
    {
        $this->initCurl($method, $path);
        if (!empty($data)) {
            $this->argsEncode($data);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->args);
        }
        $this->execute();
    }

    /**
     * @param $file 上传文件对象
     * @param string $path 文件上传路径
     */
    public function upload($file, $path = '')
    {
        $this->initCurl('PUT', $path . $file->name);
        curl_setopt($this->curl, CURLOPT_PUT, TRUE);
        curl_setopt($this->curl, CURLOPT_INFILE, fopen($file->tempName, "r"));
        curl_setopt($this->curl, CURLOPT_INFILESIZE, filesize($file->tempName));
        $this->execute();
    }

    /**
     * 处理请求参数
     * @param $data
     */
    public function argsEncode($data)
    {
        $this->args = $data;
    }

    /**
     * 格式化返回结果
     * @param $data
     */
    public function formatResult($data)
    {
        $xmlStr = preg_replace('/<(\/)?(\w+):(\w+)/', '<${1}${3}', $data);
        $objRes = simplexml_load_string($xmlStr);
        $this->result = json_decode(json_encode($objRes), true);
    }

    /**
     * 执行
     */
    public function execute()
    {
        $this->str_result = curl_exec($this->curl);
        if (self::DEBUG) {
            $this->debug = curl_getinfo($this->curl);
        }
        curl_close($this->curl);
        $this->formatResult($this->str_result);
    }
}