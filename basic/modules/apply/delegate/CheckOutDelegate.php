<?php
/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2016/8/26
 * Time: 15:17
 */

namespace app\modules\apply\delegate;


use app\models\ApplyCheckoutModel;
use app\models\AttendanceModel;
use app\models\ApplyBaseModel;
use app\models\WorkSetModel;

class CheckOutDelegate
{
    /**
     * 忘打卡申请入库数据预处理
     * @param array $data
     * @return array
     */
    public static function filterData(array $data)
    {
        //获取工作日加班过期配置
        $workDayConfig = WorkSetModel::findOne(1);
        $current_time = time();
        //上班时间
        $begin_time = strtotime($data['check_date'].$workDayConfig->begin_time);
        //下班时间
        $end_time = strtotime($data['check_date'].$workDayConfig->end_time);
        //上午
        if($data['is_am'] == 1 && $current_time <= $begin_time) {
            return ['code' => 0, 'msg' => '提交忘记打卡申请时间必须小于当前时间'];
        }
        //下午
        if($data['is_am'] == 2 && $current_time <= $end_time) {
            return ['code' => 0, 'msg' => '提交忘记打卡申请时间必须小于当前时间'];
        }
        $data['check_date'] = strtotime($data['check_date']);
        return $data;
    }

    /**
     * 忘打卡最后一步审批
     * @param ApplyBaseModel $apply
     * @return mix
     */
    public static function doneCheckout(ApplyBaseModel $apply)
    {
        $detail = ApplyCheckoutModel::findOne($apply->detail_id);
        $atten = AttendanceModel::find()->where(['u_id' => $apply->applyer,'workDate' => $detail->check_date])->one();
        if(empty($atten)) {
            return ['code' => 0, 'msg' => '尚未生成打卡记录，不能作最后一步审批'];
//            return false;
        }
        //迟到忘打卡或早退忘打卡修改考勤状态为正常
        if(($atten->status == 2 && $detail->is_am == 1) || ($atten->status == 3 && $detail->is_am == 2)) {
            $atten->status = 1;
            return $atten->save(false);
        }
        //迟到早退早上忘打卡则修改考勤状态为早退
        if($atten->status == 4 && $detail->is_am == 1) {
            $atten->status = 3;
            return $atten->save(false);
        }
        //迟到早退下午忘打卡则修改考勤状态为迟到
        if($atten->status == 4 && $detail->is_am == 2) {
            $atten->status = 2;
            return $atten->save(false);
        }
        return true;
    }
}