<?php

namespace app\controllers;

use app\lib\Tools;
use Yii;

class SendmailController extends BaseController
{
    public $modelClass = 'app\models\ApplyFlowModel';
    /**
     * 发送邮件
     */
    public function actionSendMail(){
        $subject = Yii::$app->request->post("subject");
        $content = Yii::$app->request->post("content");
        $receiver = Yii::$app->request->post("receiver");
        return Tools::sendMail($subject, $content, $receiver);
    }

    /**
     * 异步发送邮件
     */
    public function actionAsynSendmail(){
        $subject = Yii::$app->request->get("subject");
        $content = Yii::$app->request->get("content");
        $receiver = Yii::$app->request->get("receiver");
        return Tools::asynSendMail($subject, $content, $receiver);
    }

}
