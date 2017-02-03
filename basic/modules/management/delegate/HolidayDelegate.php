<?php
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/7/29
 * Time: 10:10
 */

namespace app\modules\management\delegate;
use app\models\HolidayModel;
use app\lib\FResponse;

class HolidayDelegate
{
    /**
     * 根据时间获取该月的工作日
    */
    public static function getMonthWorkday($date)
    {
        $list=[];
        $time = strtotime($date);
        //获取当月的第一天
        $BeginDate = strtotime(date('Y-m-01 00:00:00', $time));
        //获取当月的最后一天
        $curTime=date('Y-m-1',$time);
        $EndDate = strtotime(date('Y-m-d 23:59:59', strtotime("$curTime +1 month -1 day")));
        $list = HolidayModel::find()->where(['>=', 'day', $BeginDate])->andWhere(['<=','day', $EndDate])->andWhere(['=','iswork', 1])->asArray()->all();
        return $list;
    }

    /**
     * 设置工作日
    */
    public static function setWorkday($date,$status)
    {
        $time = strtotime($date);
        $data = ['iswork'=>$status];
        $res = \Yii::$app->db->createCommand()->update('oa_holiday',$data,'day=:day',[':day'=>$time])->execute();
        return $res;
    }

    /**
     * 判断是否有数据
     * $date
    */
    public static function isSetDay($date)
    {
        $time = strtotime($date);
        $info = HolidayModel::find()->where(['=', 'day', $time])->asArray()->one();
        if(isset($info['hid'])){
            return true;
        }else{
            return false;
        }
    }

}