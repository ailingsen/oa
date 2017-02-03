<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
namespace app\lib;

use app\models\ApplyBaseModel;
use app\models\ApplyMsgModel;
use app\models\ApprovalMsgModel;
use app\models\MembersModel;
use app\models\ModelField;
use app\models\ModelForm;
use Yii;

class Tools
{
    /**
     * 创建目录
     * @param $path
     */
    public static function createDir($path)
    {
        if (!file_exists($path)) {
            self::createDir(dirname($path));
            mkdir($path, 0777);
        }
    }

    /**
     * 格式化树形结构
     * @param $arr
     * @param int $parent_id
     * @param $parentId
     * @param $cId
     * @return array
     */
    public static function createTreeArr($arr, $parent_id = 0, $parentId, $cId)
    {
        $ret = array();
        foreach ($arr as $k => $v) {
            if ($v[$parentId] == $parent_id) {
                $tmp = $arr[$k];
                unset($arr[$k]);
                $tmp['children'] = self::createTreeArr($arr, $v[$cId], $parentId, $cId);
                $ret[] = $tmp;
            }
        }
        return $ret;
    }

    /**
     * 异步发送邮件
     * @param  [vachar] $subject [主题]
     * @param  [vachar] $content  [发送内容]
     * @param  [vachar] $receiver [发件人]
     * @return true
     */
    public static function asynSendMail($subject, $content, $receiver)
    {
        $host = $_SERVER['SERVER_NAME'];
        $receiver = trim($receiver);
        $fp = fsockopen($host, $_SERVER['SERVER_PORT'], $errno, $errstr, 5);
        if (!$fp) {
            echo "$errstr ($errno)<br />\n";
        }
        $header = "POST /index.php?r=sendmail/send-mail HTTP/1.1\r\n";
        $header .= "Host: $host\r\n";

        $_post = ["content=" . urlencode($content), "receiver=" . urlencode($receiver), "subject=" . urlencode($subject)];//必须做url转码以防模拟post提交的数据中有&符而导致post参数键值对紊乱
        $_post = implode('&', $_post);
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";//POST数据
        $header .= "Content-Length: " . strlen($_post) . "\r\n";//POST数据的长度
        $header .= "Connection: Close\r\n\r\n";//长连接关闭
        $header .= $_post; //传递POST数据

        fwrite($fp, $header);
        //-----------------调试代码区间-----------------
//        $html = '';
//        while (!feof($fp)) {
//            $html .= fgets($fp);
//        }
//        echo $html;
        //-----------------调试代码区间-----------------
        fclose($fp);
    }


    public static function sendMail($subject = "", $content = '', $receiver = '')
    {
        $mail = Yii::$app->mailer->compose();
        $subject = mb_substr($subject, 0, 14);
        $mail->setFrom(['hnoa@supernano.com' => 'admin'])
            ->setTo($receiver)
            ->setSubject($subject)
            ->setHtmlBody($content);

        //开发环境屏蔽邮件
        if (YII_ENV === 'dev') {
            $res = true;
            $res = $mail->send();
        } else {
            $res = $mail->send();
        }
        return $res;
    }

    /**
     * 获取用户头像地址
     * @param $headImg
     * @return string
     */
    public static function getHeadImg($headImg)
    {
        if (!$headImg) {
            return '/static/head-img/defaultHead.png';
        }
        return '/static/head-img/uploads/' . $headImg . '.jpg';
    }

    /**
     * 插入审批消息
     * @param $uid
     * @param $applyId
     * @param $applyer
     * @param $applyTitle
     * @param string $applyerName
     */
    public static function addApprovalMsg($uid, $applyId, $applyer, $applyTitle, $applyerName = '')
    {
        $format = '提交了%s需要你审批';
//        if(empty($applyerName)) {
//            $memberInfo = MembersModel::findOne($applyer);
//            $applyerName = $memberInfo->real_name;
//        }
        $model = new ApprovalMsgModel();
        $model->uid = $uid;
        $model->apply_id = $applyId;
        $model->applyer = $applyer;
        $model->title = sprintf($format, $applyTitle);
        $model->apply_title = $applyTitle;
        $model->is_read = 0;
        $model->create_time = time();
        $model->save(false);
        $userInfo = MembersModel::find()->select(['real_name'])->where(['u_id' => $applyer])->asArray()->one();
        $modeltype = ApplyBaseModel::find()->leftJoin('oa_apply_model','oa_apply_base.model_id=oa_apply_model.model_id')
                    ->select('oa_apply_model.model_id,modeltype,oa_apply_model.title')->where(['oa_apply_base.apply_id' => $applyId])->asArray()->one();
        self::msgJpush(5,$applyId,$userInfo['real_name'].$model->title,[$uid],$modeltype);
    }

    /**
     * 插入申请消息
     * @param $uid
     * @param $applyId
     * @param $handler
     * @param $status   操作状态 1 通过 2 驳回 3 已完成
     * @param $applyTitle
     * @param string $handlerName
     */
    public static function addApplyMsg($uid, $applyId, $handler, $status, $applyTitle, $handlerName = '')
    {
        $format = '审批了你的%s';
//        if(empty($handlerName)) {
//            $memberInfo = MembersModel::findOne($handler);
//            $handlerName = $memberInfo->real_name;
//        }
        $model = new ApplyMsgModel();
        $model->uid = $uid;
        $model->apply_id = $applyId;
        $model->handler = $handler;
        $model->title = sprintf($format, $applyTitle);
        $model->apply_title = $applyTitle;
        $model->status = $status;
        $model->is_read = 0;
        $model->create_time = time();
        $model->save(false);
        $userInfo = MembersModel::find()->select(['real_name'])->where(['u_id' => $handler])->asArray()->one();
        $modeltype = ApplyBaseModel::find()->leftJoin('oa_apply_model','oa_apply_base.model_id=oa_apply_model.model_id')
            ->select('oa_apply_model.model_id,modeltype,oa_apply_model.title')->where(['oa_apply_base.apply_id' => $applyId])->asArray()->one();
        self::msgJpush(4,$applyId,$userInfo['real_name'].$model->title,[$uid],$modeltype);
    }

    /**
     * 极光推送
     * @param $type 1公告 2项目 3任务 4 申请 5 审批 6 会议室通知
     * @param $id 详情ID
     * @param $content 消息体
     * @param $uid 不传uid则给所有人发（自动过滤关闭推送用户）
     * @param $extras 附加字段 申请 审批附加字段为modeltype 任务附加字段为task_type
     */
    public static function msgJpush($type, $id, $content, $uid = [], $extras = [])
    {
        $config = [1 => 'allow_notice_app',2 => 'allow_project_app',3 => 'allow_task_app',4 => 'allow_apply_app',5 => 'allow_approval_app',6 => 'allow_meeting_app',];
        if(!empty($uid)) {
            $pushUid = MembersModel::find()->select(['u_id'])->where(['u_id' => $uid,$config[$type] => 1])->column();
        }else {
            $pushUid = MembersModel::find()->select(['u_id'])->where([$config[$type] => 1])->column();
        }
        JPush::push($type, $id, $content, $pushUid ,$extras);
    }

}