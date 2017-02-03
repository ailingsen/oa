<?php
/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2016/8/23
 * Time: 14:37
 */

namespace app\modules\apply\delegate;


use app\models\ApplyBaseModel;
use app\models\ApplyOvertimeModel;
use app\models\FlexworkStoreModel;
use app\models\VacationInventoryModel;
use app\models\HolidayModel;
use app\models\VacationLogModel;
use app\models\VacationSetModel;
use app\models\WorkSetModel;

class OverTimeDelegate
{
    /**
     * 加班申请入库数据预处理(计算加班时长)
     * @param array $data
     * @param $uid
     * @param $action
     * @param $detail_id
     * @return array
     */
    public static function  filterData(array $data,$uid,$action = '',$detail_id = '')
    {
//        $date = date('Y-m-d',strtotime($data['begin_time']));
        $overtime = strtotime($data['currentDay']);
        if ($data['type'] == 1 && !HolidayModel::isWork($data['currentDay'], $data['currentDay'])) {
            return ['code' => 0, 'msg' => '您选择的时间区间内有节假日，请重新选择'];
        }
        if ($data['type'] == 2 && !HolidayModel::isVacation($data['currentDay'], $data['currentDay'])) {
            return ['code' => 0, 'msg' => '您选择的时间区间内有工作日，请重新选择'];
        }
        $data['uid'] = $uid;
        //编辑模式判断是否修改加班时间  若无修改直接允许提交  若修改则判断是否已提交过改日期加班申请
        if($action == 'edit') {
            $detail = ApplyOvertimeModel::findOne($detail_id);
            if($detail->overtime == $overtime) {
                $res = false;
            }else {
                $res = ApplyOvertimeModel::find()->where(['uid' => $uid,'overtime' => $overtime])->exists();
            }
        }else {
            $res = ApplyOvertimeModel::find()->where(['uid' => $uid,'overtime' => $overtime])->exists();
        }
        if($res) {
            return ['code' => 0, 'msg' => '该日期您已发起过加班申请，请勿重发'];
        }
        $data['begin_time'] = strtotime($data['currentDay'].$data['begin_time_str']);
        if($data['isNextDay']) {
            $data['end_time'] = strtotime($data['currentDay'].$data['end_time_str']) + 24 * 60 * 60;
        }else {
            $data['end_time'] = strtotime($data['currentDay'].$data['end_time_str']);
        }
        $data['overtime'] = $overtime;
        $data['is_next_day'] = $data['isNextDay'] ? 1 : 0;

        $hours = round(($data['end_time'] - $data['begin_time']) / 3600,1);
        //例(4.5 hours 处理后为 4.5,4.2为4,4.8为4.5)
        if(round($hours) <= $hours) {
            $data['hours'] = floor($hours);
        }else {
            $data['hours'] = floatval(floor($hours).'.5');
        }

        unset($data['currentDay']);
        unset($data['isNextDay']);
        return $data;
    }

    /**
     * 加班申请最后一步审批业务逻辑
     * @param ApplyBaseModel $apply
     * @param $data
     * @return bool
     */
    public static function doneOverTime(ApplyBaseModel $apply,$data)
    {
        $detail = ApplyOvertimeModel::findOne($apply->detail_id);
        $detail->real_hours = $data->real_hours;
        if($detail->save(false)) {
            //工作日加班
            if($detail->type == 1) {
                //获取工作日加班过期配置
                $workDayConfig = WorkSetModel::findOne(1);
                $flexwork = new FlexworkStoreModel();
                $flexwork->uid = $apply->applyer;
                $flexwork->begin_time = $detail->begin_time;
                $flexwork->end_time = $detail->end_time;
                $flexwork->hours = $data->real_hours;
                $flexwork->create_time = time();
                $flexwork->expire_time = time() + $workDayConfig['workday_lose'] * 24 * 3600;
                //插入弹性上班记录
                if($flexwork->save(false)) {
                    return true;
                }
            }
            //节假日加班
            if($detail->type == 2) {
                $days = floor(($data->real_hours / 8 * 10 )) /10;
                //例(0.5 hours 处理后为 0.5,0.4为0,0.8为0.5)
//                if(round($days) <= $days) {
//                    $days = floor($days);
//                }else {
//                    $days = floatval(floor($days).'.5');
//                }
                if($days < 0.5) {
                    $days = 0;
                }else if($days >= 0.5 && $days < 1) {
                    $days = 0.5;
                }else if($days >= 1) {
                    $days = 1;
                }
//                $days = $days > 1 ? 1 : $days;
                //获取调休过期配置
                if($days > 0) {
                    $set = VacationSetModel::findOne(1);
                    $inventory = new VacationInventoryModel();
                    $inventory->u_id = $apply->applyer;
                    $inventory->expire_time = time() + $set['overtime_expire'] * 24 * 3600;
                    $inventory->creat_time = time();
                    $inventory->over_time = $detail->overtime;
                    //查询旧有调休假数量
                    $old_vacation = VacationInventoryModel::getVaInventory($apply->applyer);
                    //插入调休记录
                    if($inventory->save(false)) {
                        //添加日志
                        VacationLogModel::addTuneVacationLog($apply->applyer,$old_vacation['workDays'],1,'节假日加班调休假增加');
                    }else {
                        return false;
                    }
                    //加班天数等于1时则插入两天调休记录
                    if($days == 1) {
                        $inventory = new VacationInventoryModel();
                        $inventory->u_id = $apply->applyer;
                        $inventory->expire_time = time() + $set['overtime_expire'] * 24 * 3600;
                        $inventory->creat_time = time();
                        $inventory->over_time = $detail->overtime;
                        //查询旧有调休假数量
                        $old_vacation = VacationInventoryModel::getVaInventory($apply->applyer);
                        //插入调休记录
                        if($inventory->save(false)) {
                            //添加日志
                            VacationLogModel::addTuneVacationLog($apply->applyer,$old_vacation['workDays'],1,'节假日加班调休假增加');
                            return true;
                        }
                        return false;
                    }
                    return true;
                }
                return true;
            }
        }
        return false;
    }
}