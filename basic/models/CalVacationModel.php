<?php
/**
 * Created by PhpStorm.
 * User: liaoshuochao
 * Date: 2016/07/29
 * Time: 17:35
 */
namespace app\models;

use yii;
use app\config\Dict;
/**
 * This is the model class for table "oa_annual_leave".
 */
class CalVacationModel extends AnnualLeaveModel
{
    const STATUS_EFFECTIVE = 1;

    function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Shanghai');
    }

    /**
     * 计算普通年假
     * @param $uId
     * @param $entryTime
     * @return array
     */
    public function calculateAnnualVacation($uId, $entryTime)
    {
        $vacationConf = $this->doVacationSet(VacationSetModel::findOne(1)->toArray());
        $initAnnualVacation = $vacationConf['ini_annual_vacation'];

        $entryTimeArray = explode('-', $entryTime);
        $entryYear = $entryTimeArray[0];  //入职年
        $entryMonth = $entryTimeArray[1];  //入职月
        $entryDay = $entryTimeArray[2];  //入职日

        //计算工龄
        // 例：2016年1月1日入职，到2017年1月1日,$workYear=1
        //     2016年1月2日入职，到2017年1月3日,$workYear=0,
        if ($entryMonth . '-' . $entryDay == $vacationConf['cal_cycle_start']) {
            $workYear = date('Y') - $entryYear;
        } else {
            $workYear = date('Y') - $entryYear - 1;
        }

        //初始化返回值
        $vacations = ['annual_vacation' => 0];
        //计算今年已休普通年假
        $usedAnnual = $this->calculateUsedAnnualVacation($uId, $vacationConf, Dict::TYPE_ANNUAL_VACATION);

        //年假增加规则
        $increaseRules = json_decode($vacationConf['increase_rules'], true);

        //1.入职未满1年 今年可休年假=X/365*5 最小取0.5
        if (time() < strtotime(($entryYear + 1) . $entryMonth . $entryDay)) {
            //普通年假
            $oneYear = 365 * 24 * 3600;
            //a,如果当前年份 > 入职年份
            if (date('Y') > $entryYear) {
                $workTime = time() - strtotime(date('Y') . $vacationConf['cal_cycle_start']);
            } //b,如果当前年份 = 入职年份
            else {
                $workTime = time() - strtotime($entryTime);
            }
            $totalAnnual = intval(2 * $initAnnualVacation * $workTime / $oneYear) / 2;

        } //2.入职满1年且工龄不到3年,默认可休年假=5天
        elseif (time() >= strtotime(($entryYear + 1) . $entryMonth . $entryDay) && $workYear <= $increaseRules[1]['work_year']) {
            //普通年假
            //$totalAnnual = $vacationConf['ini_annual_vacation'] + intval($increaseRules[1]['increase_num']);
            $totalAnnual = $vacationConf['ini_annual_vacation'];
        } //3.工龄满3年 可休年假 = 工龄 + 3
        elseif ($workYear > $increaseRules[1]['work_year']) {
            //普通年假
            //$totalAnnual = $vacationConf['ini_annual_vacation'] + intval($increaseRules[1]['increase_num']);
            $totalAnnual = $vacationConf['ini_annual_vacation'] + (($workYear - $increaseRules[1]['work_year'])*intval($increaseRules[1]['increase_num']));
        }
        //年假最多15天
        if ($totalAnnual > 15) {
            $totalAnnual = 15;
        }

        $vacations['annual_vacation'] = $totalAnnual - $usedAnnual;

        //返回数据不为负数，若为负数则置零
        $vacations['annual_vacation'] = $vacations['annual_vacation'] < 0 ? 0 : $vacations['annual_vacation'];

        return $vacations;
    }

    public function doVacationSet($vacationConf)
    {
        $vacationConf['cal_cycle_start'] = isset($vacationConf['cal_cycle_start']) ? $vacationConf['cal_cycle_start'] : '01-01';
        $vacationConf['cal_cycle_end'] = isset($vacationConf['cal_cycle_end']) ? $vacationConf['cal_cycle_end'] : '12-31';
        $vacationConf['ini_annual_vacation'] = isset($vacationConf['ini_annual_vacation']) ? $vacationConf['ini_annual_vacation'] : 5;
        $vacationConf['increase_rules'] = isset($vacationConf['increase_rules']) ? $vacationConf['increase_rules'] : '[{"work_year":2, "increase_num":0},{ "work_year": 2, "increase_num":1}]';
        return $vacationConf;
    }

    /**
     * 计算今年已休的年假
     * @param $uId
     * @param $vacationConf
     * @param $vacationType 1:普通年假 2.顺延年假
     * @return int
     */
    public function calculateUsedAnnualVacation($uId, $vacationConf, $vacationType)
    {
        $rangeStart = strtotime(date('Y') . '-' . $vacationConf['cal_cycle_start']);
        $rangeEnd = strtotime((date('Y') + 1) . '-' . $vacationConf['cal_cycle_end'] . ' 23:59:59');

        $ret = (new \yii\db\Query())
            ->select('sum(used_num) as total_used_num')
            ->from('oa_vacation_used')
            ->where('u_id=:uId', [':uId' => intval($uId)])
            ->andWhere('vacation_type=:vacation_type', [':vacation_type' => $vacationType])
            ->andWhere('status=:status', [':status' => self::STATUS_EFFECTIVE])
            ->andWhere(['between', 'create_time', $rangeStart, $rangeEnd])
            ->one();

        if ($ret['total_used_num']) {
            $used_num = $ret['total_used_num'];
        } else {
            $used_num = 0;
        }

        return $used_num;
    }

    /**
     * 计算前一年已休的年假
     * @param $uId
     * @param $entryTime
     * @param $vacationType 1:普通年假 2.顺延年假
     * @return int
     */
    public function calLastYearUsedAnnualVacation($uId, $entryTime, $vacationType)
    {
        $entryTimeArray = explode('-', $entryTime);
        $entryMonth = $entryTimeArray[1];  //入职月
        $entryDay = $entryTimeArray[2];  //入职日

        //计算起止时间
        if (date('md') >= $entryMonth . $entryDay) {
            $rangeStart = (date('Y') - 1) . $entryMonth . $entryDay;
            $rangeEnd = (date('Y')) . $entryMonth . $entryDay;
        } else {
            $rangeStart = (date('Y') - 2) . $entryMonth . $entryDay;
            $rangeEnd = (date('Y') - 1) . $entryMonth . $entryDay;
        }

        $ret = (new \yii\db\Query())
            ->select('sum(used_num) as total_used_num')
            ->from('oa_vacation_used')
            ->where('u_id=:uId', [':uId' => $uId])
            ->andWhere('vacation_type=:vacation_type', [':vacation_type' => $vacationType])
            ->andWhere('status=:status', [':status' => self::STATUS_EFFECTIVE])
            ->andWhere(['between', 'create_time', $rangeStart, $rangeEnd])
            ->one();

        if ($ret['total_used_num']) {
            $used_num = $ret['total_used_num'];
        } else {
            $used_num = 0;
        }

        return $used_num;
    }


    /**
     * 增加年假
     * @param $uId
     * @param $increamemt
     * @return array|bool
     */
    public function addAnnualLeave($uId, $increamemt)
    {
        $annualLeave = self::findOne(['u_id' => $uId]);
        $oldNormalLeave = 0;
        if (!$annualLeave) {
            $annualLeave = new self;
            $annualLeave->normal_leave = $increamemt;
            $annualLeave->u_id = $uId;
            $annualLeave->delay_leave = $annualLeave->manual_leave = 0;
        } else {
            $oldNormalLeave = $annualLeave->normal_leave+$annualLeave->delay_leave;
            $annualLeave->normal_leave = $increamemt + $annualLeave->manual_leave;
        }


        $annualLeave->normal_leave = $annualLeave->normal_leave < 0 ? 0 : $annualLeave->normal_leave;

        $increamemt = $annualLeave->normal_leave - $annualLeave->getOldAttribute('normal_leave');

        if (!$annualLeave->save()) {
            return $annualLeave->getErrors();
        }

        if ($increamemt) {
            $insertLog = [
                'u_id' => $uId,
                'log_type' => 2,
                'value_before' => $oldNormalLeave,
                //'value_after' => $annualLeave->normal_leave+$oldNormalLeave,
                'value_after' => $increamemt+$oldNormalLeave,
                'log_content' => '系统新增年假' . $increamemt . '天',
                'create_time' => time(),
                'operator_id' => 1
            ];

            if ($increamemt < 0) {
                $insertLog['log_content'] = '系统顺延年假' . abs($increamemt) . '天';
            }

            $this->addVacationLog($insertLog);
        }
        return true;
    }

    /**
     * 修改年假
     * @param $uId
     * @param array|$data
     * @return array|bool
     */
//    public function updateAnnualLeave($uId, $data)
//    {
//        $annualLeave = self::findOne(['u_id' => $uId]);
//        if (!$annualLeave) {
//            return false;
//        }
//        $annualLeave->attributes = $data;
//
//        if (!$annualLeave->save()) {
//            return $annualLeave->getErrors();
//        }
//
//        return true;
//    }

    /**
     * 4月1日清空顺延年假
     * @param self|$annualLeave
     * @return array|bool
     */
    public function delDelayLeave($annualLeave)
    {
        if (!$annualLeave || 0 == $annualLeave->delay_leave) {
            return false;
        }
        $annualLeave->delay_leave = 0;

        $insertLog = [
            'u_id' => $annualLeave->u_id,
            'log_type' => 2,
            'value_before' => $annualLeave->getOldAttribute('delay_leave')+$annualLeave->getOldAttribute('normal_leave'),
            'value_after' => $annualLeave->getOldAttribute('normal_leave'),
            'log_content' => '系统清空顺延年假',
            'create_time' => time(),
            'operator_id' => 1
        ];
        if (!$annualLeave->save()) {
            return $annualLeave->getErrors();
        }


        $this->addVacationLog($insertLog);

        return true;
    }

    /**
     * 1月1日刷新年假
     * 普通年假转移到延期年假，普通年假和手工年假清0
     * @param self|$annualLeave
     * @return array|bool
     */
    public function flushAnnualLeave($annualLeave)
    {
        if (!$annualLeave) {
            return false;
        }
        $annualLeave->delay_leave = $annualLeave->normal_leave;
        $annualLeave->normal_leave = 0;
        $annualLeave->manual_leave = 0;

        $insertLog = [
            'u_id' => $annualLeave->u_id,
            'log_type' => 2,
            'value_before' => $annualLeave->getOldAttribute('normal_leave'),
            'value_after' => $annualLeave->normal_leave+$annualLeave->delay_leave,
            'log_content' => '系统刷新年假',
            'create_time' => time(),
            'operator_id' => 1
        ];

        if (!$annualLeave->save(false)) {
            return $annualLeave->getErrors();
        }

        $this->addVacationLog($insertLog);

        return true;
    }

    /**
     * 1月1日刷新年假
     * 普通年假转移到延期年假，普通年假和手工年假清0
     * @param string|$select
     * @param array|$conditionArray
     * @param int|$limit
     * @param int|$page
     * @return array|bool
     */
    public function getAnnualLeaveList($select, $conditionArray, $limit, $page)
    {
        $offset = ($page - 1) * $limit;
        return self::find()->select($select)
            ->onCondition($conditionArray)
            ->offset($offset)
            ->limit($limit)
            ->all();

    }

    /**
     * 新增日志
     * ['u_id', 'log_type', 'value_before','value_after','log_content','create_time','operator_id' ]
     * @param $logInfo
     * @return array|bool
     */
    public function addVacationLog($logInfo)
    {
        return Yii::$app->db->createCommand()->insert('oa_vacation_log', $logInfo)->execute();
    }
}
