<?php
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/7/29
 * Time: 10:06
 */

namespace app\modules\management\controllers;

use yii;
use yii\web\Controller;
use app\modules\management\delegate\HolidayDelegate;
use app\modules\management\helper\HolidayHelper;

class HolidayController extends Controller
{
    /**
     * 根据时间获取当月的工作日
     * $date
    */
    public function actionMonthWorkday()
    {
        $date = Yii::$app->request->post('date');
        $list = HolidayDelegate::getMonthWorkday($date);
        if(count($list)>0){
            $list = HolidayHelper::setMonthWorkday($list);
        }
        return json_encode(['code'=>1,'data'=>$list]);
        die;
    }

    /**
     * 设置或取消工作日
     * $date
     * $status
    */
    public function actionSetWorkday()
    {
        $date = Yii::$app->request->post('date');
        $status = Yii::$app->request->post('status');
        if(!HolidayDelegate::isSetDay($date)){
            return json_encode( ['code'=>0,'msg'=>'没有该天的数据，无法设置!']);
        }
        $res = HolidayDelegate::setWorkday($date,$status);
        if($res){
            return json_encode( ['code'=>1,'msg'=>'设置成功!']);
        }else{
            return json_encode( ['code'=>0,'msg'=>'设置失败!']);
        }
    }

}