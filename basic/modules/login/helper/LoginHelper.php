<?php

namespace app\modules\login\helper;

use Yii;

//控制器辅助类

class LoginHelper {
    /**
     * 所有权限信息键值对设置
    */
    public static function setPerKV($permission)
    {
        $tmpp=array();
        if(!empty($permission)){
            foreach($permission as $p){
                $tmpp[$p->code]=$p->p_router;
            }
        }
        return $tmpp;
    }

    /**
     * 发送忘记密码邮件
    */
    public static function sendMail($email,$password_token,$u_id)
    {
        $contacts = '请点击链接修改你在纳米OA系统中的账户密码,为了您的账户安全请保密！该验证链接有效时间为30分钟！<a href="http://'.$_SERVER['HTTP_HOST'].'/index_news.html#/forgetPassword/'.$password_token.'/'.$u_id.'">http://'.$_SERVER['HTTP_HOST'].'/index_news.html#/forgetPassword/'.$password_token.'/'.$u_id.'</a>';
        $mail= Yii::$app->mailer->compose();
        $mail->setFrom(['hnoa@supernano.com'=>'admin'])->setTo($email);
        $mail->setSubject("纳米OA密码重置");
        $mail->setHtmlBody($contacts);    //发布可以带html标签的文本
        $res = $mail->send();
        return $res;
    }


}