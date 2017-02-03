<?php

/**
 * Created by PhpStorm.
 * Desc: 极光推送
 * User: nielixin
 * Date: 2015/8/21
 * Time: 11:15
 */

namespace app\lib;

class JPush
{
    const APP_KEY = '7ac094500768fbfd59a1eedf';
    const MASTER_SECRET = 'ce2e929efe88eda2444b870a';
    const PUSH_URL = 'https://api.jpush.cn/v3/push';
    //推送验证地址
//    const PUSH_URL = 'https://api.jpush.cn/v3/push/validate';

    public static function request_post($url = "", $param = "", $header = "")
    {
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl); //设置URL
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        // 增加 HTTP Header（头）里的字段
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch); //运行curl

        curl_close($ch);
        return $data;
    }

    /**
     * 目前用到的app推送方法，使用这个！！！！！！关于type和id，参见文档38号记录和附录！！！！！
     * @param $type                消息类型    1公告 2项目 3任务 4 申请 5 审批 6 会议室通知
     * @param $id                  消息对象id
     * @param $content             消息内容
     * @param $audience            发送对象(这里约定的是uid)数组，为空则发送给所有人
     * @param $extras 附加字段 申请 审批附加字段为modeltype 任务附加字段为task_type
     * @return array|mixed
     */
    public static function push($type,$id,$content,$audience=[],$extras=[]){
        $tmpData = ['type' => $type,'id' => $id];
        if(!empty($extras)) {
            $tmpData = array_merge($tmpData,$extras);
        }
        $data = array(
            'content' => $content,
            'audience' => $audience,
            'extras' => array(
                'android' => $tmpData,
                'ios' => $tmpData,
            ),
        );
        return self::send($data);
    }

    //发送推送数据
    public static function send($data)
    {
        $res_arr = array('code' => -1);
        $base64 = base64_encode(self::APP_KEY . ':' . self::MASTER_SECRET);
        $header = array("Authorization:Basic $base64", "Content-Type:application/json");
        $send_data = self::processData($data);
        if($send_data) {
            $param = json_encode($send_data);
            $res = self::request_post(self::PUSH_URL, $param, $header);
            $res_arr = json_decode($res, true);
        }
        return $res_arr;
    }

    //处理推送数据
    public static function processData($data)
    {
        if(empty($data) || !isset($data['content']) || !isset($data['extras'])) {
            return false;
        }
        //推送至android数据
        $android = array(
            'alert' => $data['content'],    //通知内容
//            'title' => $data['title'],      //通知标题 如果指定了，则通知里原来展示 App名称的地方，将展示成这个字段
//            'builder_id' => 3,              //通知栏样式ID
            'extras' => $data['extras']['android']       //扩展字段
        );
        //推送至ios数据
        $ios = array(
            'alert' => $data['content'],    //通知内容
//            'sound' => '',      //通知提示声音
//            'badge' => 3,                   //应用角标
//            'content-available' => true,       //推送唤醒 boolean
//            'category' => '',              //设置APNs payload中的"category"字段值
            'extras' => $data['extras']['ios']       //扩展字段
        );
        //极光推送数据
        $sendData = array(
            'platform' => array("android", "ios"),
            'audience' => $data['audience'],
            'notification' => array(
                'android' => $android,
                'ios' => $ios,
            ),
//            'options' => array(
//                'apns_production' => false          //开发环境则传此参数  正式上线时删掉
//            ),
        );

        //推送至所有设备 或者是推送至指定别名 ($data['audience']可以是单个别名或别名数组)
        if(isset($data['audience']) && !empty($data['audience'])) {
            $sendData['audience'] = array('alias' => $data['audience']);
        }else {
            //$sendData['audience'] = 'all';
            $sendData['audience'] = [];
        }

        return $sendData;
    }
}