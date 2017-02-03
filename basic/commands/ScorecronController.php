<?php

/**
 * Created by PhpStorm.
 * User: liaoshuochao
 * Date: 2016/08/31
 * Time: 9:35
 */

namespace app\commands;

use app\models\CrontabModel;
use app\models\MembersModel;
use app\models\OrgMemberModel;
use app\models\OrgModel;
use app\models\ScoreLogModel;
use app\modules\management\delegate\ScoreDelegate;
use yii\console\Controller;
use Yii;

class ScorecronController extends Controller
{
    const PER_COUNT = 200;
    const STATUS_EFFECTIVE = 1;
    const STATUS_IS_DEDETE = 0;

    /**
     * 入口
     */
    public function actionIndex()
    {
        date_default_timezone_set('Asia/Shanghai');
        //个人积分发放脚本
        $personalCron = CrontabModel::findOne(['cron_name' => 'add_personal_score']);
        if ($this->isTime($personalCron['run_time'])) {
            $runTime = ScoreDelegate::updateRuntime($personalCron->run_cycle);
            $personalCron->last_run_time = time();
            $personalCron->run_time = $runTime;
            $personalCron->save(false);
            $this->addPersonalScore($personalCron['params']);
        }

        //部门积分发放脚本
        $groupCron = CrontabModel::findOne(['cron_name' => 'add_group_score']);
        if ($this->isTime($groupCron['run_time'])) {
            $runTime = ScoreDelegate::updateRuntime($groupCron->run_cycle);
            $groupCron->last_run_time = time();
            $groupCron->run_time = $runTime;
            $groupCron->save(false);
            $this->addGroupScore($groupCron['params']);
        }

        exit('执行积分发放脚本任务');
    }

    /**
     * 判断脚本是否该跑了
     * @param $runCycle
     * @param $lastRunTime
     * @return bool
     */
    public function isTime($runTime)
    {
        /*$isTimeOk = false;
        $common = (time() - $lastRunTime);
        $year = floor($common / 86400 / 360);    //整数年
        $month = floor($common / 86400 / 30) - $year * 12; //整数月
        $day = floor($common / 86400) - $year * 360 - $month * 30;   //整数日
        $allDay = floor($common / 86400);    //总的天数
        switch ($runCycle) {
            case '1':
                //每年跑一次
                if ($year >= 1) {
                    $isTimeOk = true;
                }
                break;
            case '2':
                //每半年跑一次
                if ($month >= 6) {
                    $isTimeOk = true;
                }
                break;
            case '3':
                //每季度跑一次
                if ($month >= 3) {
                    $isTimeOk = true;
                }
                break;
            case '4':
                //每月跑一次
                if ($month >= 1) {
                    $isTimeOk = true;
                }
                break;
            case '5':
                //每天跑一次
                $dateNum = date("d", time());
                $nowDateNum = date("d", time());
                if ($allDay >= 1 || $dateNum != $nowDateNum) {
                    $isTimeOk = true;
                }
                break;
        }*/
        if(time() >= $runTime) {
            return true;
        }else {
            return false;
        }
    }

    /**
     * 新增个人积分
     * @param int $addScore
     */
    public function addPersonalScore($addScore)
    {
        $member = new MembersModel();
        $conditions = ['status' => self::STATUS_EFFECTIVE, 'is_del' => self::STATUS_IS_DEDETE];
        $conditionType = 2;
        $totalCount = $member::getMembersCount('u_id, entry_time', $conditions, $conditionType);
        $totalTimes = ceil($totalCount / self::PER_COUNT);

        //每次操作self::PER_COUNT条，执行$totalTimes次
        $count = 0;//计数器
        for ($num = 1; $num <= $totalTimes; $num ++) {
            $users = $member::getMembersByCondition('u_id', $conditions, $conditionType, self::PER_COUNT, $num);
            foreach ($users as $key => $val) {
                //加积分
                if ($this->addPoint($val['u_id'], $addScore)) {
                    $count ++;
                }
            }
        }
    }

    /**
     * 增加部门积分
     * @param $addScore
     */
    public function addGroupScore($addScore)
    {
        $count = 0;
        $orgs = OrgModel::find()->select('org_id')->asArray()->all();
        foreach ($orgs as $key => $val) {
            //加积分
            if ($this->addGroupPoint($val['org_id'], $addScore)) {
                $count ++;
            }
        }
    }

    /**
     * 增加个人积分
     * @param $memberId
     * @param $point
     * @return bool
     */
    public static function addPoint($memberId, $point)
    {
        //查询用户
        $member = MembersModel::findOne($memberId);
        if (empty($member)) {
            echo '  没有该用户  uid:'.$member->u_id;
        }
        $member->points = $member->points + $point;
        if ($member->points < 0) {
            $member->points = 0;
        }
        if ($member->points > 100000000) {
            echo '  不能超过100000000  uid:'.$member->u_id;
        }
        $logInfo = ['u_id' => $member->u_id,
            'type' => 1,
            'content' => '系统下拨',
            'score' => $point,
            'score_before' => $member->oldAttributes['points'],
            'score_after' => $member->points,
            'create_time' => time(),
            'operator' => 1
        ];
        ScoreLogModel::insertScoreLog($logInfo);
        if ($member->save(false)) {
            return  true;
        } else {
            return  false;
        }
    }

    /**
     * 追加群组积分
     * @param $groupId
     * @param $point
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function addGroupPoint($groupId, $point)
    {
        //查询部门负责人
        $groupManager = OrgMemberModel::getGroupMember($groupId);
        if (empty($groupManager)) {
            return false;
        } else {
            $transaction = Yii::$app->db->beginTransaction();

            $member = MembersModel::findOne($groupManager->u_id);
            $member->leave_points = $member->leave_points + $point;
            $group = OrgModel::findOne($groupId);
            $group->org_points = $group->org_points + $point;
            $group->org_all_points = $group->org_all_points + $point;
            $logInfo = ['u_id' => $groupManager->u_id,
                'type' => 2,
                'content' => '系统下拨',
                'score' => $point,
                'score_before' => $group->oldAttributes['org_points'],
                'score_after' => $group->org_points,
                'create_time' => time(),
                'operator' => 1
            ];

            ScoreLogModel::insertScoreLog($logInfo);
            if ($group->save(false) && $member->save(false)) {
                $transaction->commit();
                return  true;
            } else {
                $transaction->rollback();
                return  false;
            }
        }
    }


}