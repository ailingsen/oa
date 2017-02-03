<?php

namespace app\modules\v1\delegate;


//模型委托类。 处理控制器和动作列表

use app\models\ChecktimeModel;
use app\models\MembersModel;
use Yii;

class AttendanceDelegate
{

    /**
     * 获取我的考勤(根据日期获取当月)
     */
    public static function getMyAttend($u_id,$date)
    {
        $checmModel = ChecktimeModel::find()->select('MAX(oa_checktime.checktime) as offTime,MIN(oa_checktime.checktime) as onTime,oa_checktime.day as showDate,oa_members.real_name ')
            ->leftJoin('oa_members','card_no=oa_checktime.badgenumber')->where('oa_members.u_id=:u_id',[':u_id'=>$u_id]);
        if(isset($date) && !empty($date)){//开始时间查询
            $strDate = strtotime($date);
            //获取当月的第一天
            $BeginDate = strtotime(date('Y-m-1', $strDate));
            //获取当月的最后一天
            $FirstDate=date('Y-m-1',$strDate);
            $EndDate = strtotime(date('Y-m-d 23:59:59', strtotime("$FirstDate +1 month -1 day")));
            $checmModel->andWhere(['>=','oa_checktime.day',$BeginDate]);
            $checmModel->andWhere(['<=','oa_checktime.day',$EndDate]);
        }

        $checmModel->groupBy('oa_checktime.day,oa_members.real_name');

        $res = $checmModel->orderBy('oa_checktime.day asc')->asArray()->all();
        $memInfo = MembersModel::findOne($u_id);
        //处理记录
        $tempFirstDate = $BeginDate;
        while($tempFirstDate<=$EndDate && $tempFirstDate<=time()){
            $n = date('N',$tempFirstDate);
            if($n!=6 && $n!=7){
                $is_data=1;
                foreach($res as $key=>$val){
                    if($tempFirstDate == $val['showDate']){
                        $is_data=0;
                        break;
                    }
                }
                $tempArr=['offTime'=>0,'onTime'=>0,'showDate'=>$tempFirstDate,'real_name'=>$memInfo->real_name];
                if($is_data==1){
                    $res[]=$tempArr;
                }
            }
            $tempFirstDate+=86400;
        }
        $sortArr = [];
        foreach($res as $key=>$val){
           $sortArr[] = $val['showDate'];
        }
        array_multisort($sortArr,SORT_ASC ,$res);
        return $res;
    }

    /**
     * 获取我的考勤(根据日期获取当月)
     */
    public static function getMyAttendDay($u_id,$date)
    {
        $checmModel = ChecktimeModel::find()->select('MAX(oa_checktime.checktime) as offTime,MIN(oa_checktime.checktime) as onTime,oa_checktime.day as showDate,oa_members.real_name ')
            ->leftJoin('oa_members','card_no=oa_checktime.badgenumber')->where('oa_members.u_id=:u_id',[':u_id'=>$u_id]);
        if(isset($date) && !empty($date)){//开始时间查询
            $strDate = strtotime($date);
            //获取当天开始
            $BeginDate = strtotime(date('Y-m-d', $strDate));
            //获取当天结束
            $EndDate=strtotime(date('Y-m-d 23:59:59',$strDate));
            $checmModel->andWhere(['>=','oa_checktime.day',$BeginDate]);
            $checmModel->andWhere(['<=','oa_checktime.day',$EndDate]);
        }

        $checmModel->groupBy('oa_checktime.day,oa_members.real_name');

        $res = $checmModel->orderBy('oa_checktime.day asc')->asArray()->one();
        return $res;
    }

    /**
     * 获取我的考勤(根据日期获取当天)
     * $date 时间戳
     */
    public static function getDateCheckTime($u_id,$date)
    {
        $checmModel = ChecktimeModel::find()->select('MAX(oa_checktime.checktime) as offTime,MIN(oa_checktime.checktime) as onTime,oa_checktime.day as showDate,oa_members.real_name ')
            ->leftJoin('oa_members','card_no=oa_checktime.badgenumber')->where('oa_members.u_id=:u_id',[':u_id'=>$u_id]);
        if(isset($date) && !empty($date)){//开始时间查询
            //获取当天的开始一天
            $BeginDate = strtotime(date('Y-m-d', $date));
            //获取当天的最后时间
            $EndDate=strtotime(date('Y-m-d 23:59:59',$date));
            $checmModel->andWhere(['>=','oa_checktime.day',$BeginDate]);
            $checmModel->andWhere(['<=','oa_checktime.day',$EndDate]);
        }

        $checmModel->groupBy('oa_checktime.day,oa_members.real_name');

        $res = $checmModel->orderBy('oa_checktime.day asc')->asArray()->all();
        return $res;
    }

    /**
     * @param $uid
     * @return array|\yii\db\ActiveRecord[]
     * 获取考勤天数
     */
    public static function getDateWork($uid,$date)
    {
        $checmModel = ChecktimeModel::find()->select('MAX(oa_checktime.checktime) as offTime,MIN(oa_checktime.checktime) as onTime,oa_checktime.day as showDate,oa_members.real_name ')
            ->leftJoin('oa_members','card_no=oa_checktime.badgenumber')
            ->leftJoin('oa_holiday','oa_holiday.day=oa_checktime.day')->where(['oa_members.u_id'=>$uid]);
        if(isset($date) && !empty($date)){//开始时间查询
            $strDate = strtotime($date);
            //获取当月的第一天
            $BeginDate = strtotime(date('Y-m-1', $strDate));
            //获取当月的最后一天
            $FirstDate=date('Y-m-1',$strDate);
            $EndDate = strtotime(date('Y-m-d 23:59:59', strtotime("$FirstDate +1 month -1 day")));
            $FirstDate=strtotime($FirstDate);
            $checmModel = $checmModel->andWhere(['between','oa_checktime.day',$FirstDate,$EndDate]);
        }

        $checmModel->groupBy('oa_checktime.day,oa_members.real_name');
        $res = $checmModel->orderBy('oa_checktime.day asc')->asArray()->all();
        return $res;
    }

}