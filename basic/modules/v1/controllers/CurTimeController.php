<?php
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/11/11
 * Time: 10:49
 */

namespace app\modules\v1\controllers;
use yii\web\Controller;
use app\lib\FResponse;

class CurTimeController extends  Controller
{
    public function actionGetCurTime()
    {
        $curTime = date('Y-m-d H:i:s',time());
        FResponse::output(['code'=>20000, 'msg'=> 'ok', 'data'=> $curTime]);
    }
}