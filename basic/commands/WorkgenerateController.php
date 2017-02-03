<?php

/**
 * Created by PhpStorm.
 * User: liaoshuochao
 * Date: 2016/05/24
 * Time: 17:35
 */

namespace app\commands;

use app\models\HolidayModel;
use app\models\MembersModel;
use app\models\WorkStatementModel;
use app\modules\work\delegate\WorkDelegate;
use app\modules\work\helper\WorkHelper;
use yii\console\Controller;
use Yii;
use dict;

class WorkgenerateController extends Controller
{
    const PER_COUNT = 200;
    const STATUS_EFFECTIVE = 1;
    const STATUS_IS_DEDETE = 0;

    /**
     * 入口
     * @param $action
     */
    public function actionIndex($action, $dateTime = '')
    {
        $action = strtolower($action);
        if (!in_array($action, ['daily', 'weekly', 'manualdaily', 'manalweekly'])) {
            echo 'action is not exists';
            exit();
        }
        date_default_timezone_set('Asia/Shanghai');
        if ($dateTime) {
            $dateTime = strtotime($dateTime . ' 06:02');
        }
        switch ($action) {
            case 'daily':
                $this->actionDailyWork($dateTime);
                break;
            case 'weekly':
                $this->actionWeeklyWork($dateTime);
                break;
        }
    }

    /**
     * 生成日报
     * @param string $dateTime
     */
    public function actionDailyWork($dateTime = '')
    {
        $member = new MembersModel();
        $conditions = ['status' => self::STATUS_EFFECTIVE, 'is_del' => self::STATUS_IS_DEDETE];
        $conditionType = 2;
        $totalCount = $member::getMembersCount('u_id, entry_time', $conditions, $conditionType);
        $totalTimes = ceil($totalCount / self::PER_COUNT);
        $isManal = $dateTime ? true : false;
        $dateTime = $dateTime ? $dateTime : time();

        //检查是否工作日
        $start = mktime(0, 0, 0, date("m", $dateTime), date("d", $dateTime), date("Y", $dateTime));
        $workDay = HolidayModel::findOne(['day' => $start]);
        if (!$workDay || $workDay->iswork == 0) {
            die();
        }


            //每次操作self::PER_COUNT条，执行$totalTimes次
        $count = 0;//计数器
        for ($num = 1; $num <= $totalTimes; $num ++) {
            $users = $member::getMembersByCondition('u_id, entry_time', $conditions, $conditionType, self::PER_COUNT, $num);
            foreach ($users as $key => $val) {
                //判断是否已经生成过了
                if (WorkStatementModel::findOne(['u_id' => $val['u_id'], 'cycle' => WorkHelper::getWorkCycle(1, $dateTime)])) {
                    continue;
                }
                //生成日报
                $rs = WorkDelegate::createWorkStatement($val['u_id'], \app\config\Dict::TYPE_DAILY_WORKSTATEMENT, $dateTime);
                if ($rs) {
                    $count ++;
                }
                
                if (true != $rs) {
                    Yii::error($rs);
                }
            }
        }
        echo 'count:',$count;

        Yii::info('DailyWork over!');
    }

    /**
     * 生成周报
     * @param $dateTime
     */
    public function actionWeeklyWork($dateTime)
    {
        $member = new MembersModel();
        $conditions = ['status' => self::STATUS_EFFECTIVE, 'is_del' => self::STATUS_IS_DEDETE];
        $conditionType = 2;
        $totalCount = $member::getMembersCount('u_id, entry_time', $conditions, $conditionType);
        $totalTimes = ceil($totalCount / self::PER_COUNT);
        $isManal = $dateTime ? true : false;
        $dateTime = $dateTime ? $dateTime : time();

        //每次操作self::PER_COUNT条，执行$totalTimes次
        $count = 0;
        for ($num = 1; $num <= $totalTimes; $num ++) {
            $users = $member::getMembersByCondition('u_id, entry_time', $conditions, $conditionType, self::PER_COUNT, $num);
            foreach ($users as $key => $val) {
                //判断是否已经生成过了
                    if (WorkStatementModel::findOne(['u_id' => $val['u_id'], 'cycle' => WorkHelper::getWorkCycle(2, $dateTime)])) {
                        continue;
                    }
                //生成周报
                $rs = WorkDelegate::createWorkStatement($val['u_id'], \app\config\Dict::TYPE_WEEKLY_WORKSTATEMENT, $dateTime);
                $rs && $count++;
                if (true != $rs) {
                    Yii::error($rs);
                }
            }
        }
        echo $count;
        Yii::info('WeeklyWork over!');
    }



}