<?php
/**
 * Created by PhpStorm.
 * User: liaoshuochao
 * Date: 2016/7/28
 * Time: 9:52
 */
namespace app\modules\work\helper;

use app\lib\FResponse;
use app\models\MembersModel;
use app\models\TaskLogModel;
use app\models\TaskModel;
use app\models\WorkStatementModel;
use \app\config\Dict;
use app\modules\work\delegate\WorkDelegate;
use Yii;
use yii\base\Exception;

Class WorkHelper
{
    /**
     * 新增工作报告
     * @param $userInfo
     * @param $workId
     * @param $tasks
     * @param $works
     * @param $plans
     * @return bool
     */
    public static function addWorkStatement($userInfo, $workId, $tasks, $works, $plans)
    {
        $statement = WorkStatementModel::findOne($workId);
        if (!$statement) {
            FResponse::output(['code' => 20001, 'msg' => "工作报告不能存在"]);
        }
        $allWorks = [];
        if(is_array($tasks)) {
            foreach ($tasks as $item) {
                if (!isset($item['task_id']) || !isset($item['status']) || 0 == $item['status']) {
                    continue;
                }
                $task = TaskModel::findOne($item['task_id']);
                //如果task不存在或者不是进行中
                if (!$task || $task->status != 2) {
                    continue;
                }
                $task->status = 3;
                $workItem = ['content' =>$task->task_title, 'status' => 1, 'task_id' => $task->task_id];
                if ($task->save()) {
                    $info = MembersModel::getUserInfo($task->creater);
                    //插入任务日志
                    TaskLogModel::insertTaskLog($userInfo, "提交了审核", $item['task_id']);
                    //发邮件
//                    $url = urlencode($_SERVER['HTTP_HOST'] . '#/task/myReleaseTask/' . $item['task_id']);
//                    Tools::asynSendMail($info['username'], $userInfo['real_name'] . '提交了任务审核 ' . $task->task_title . $url, $info['real_name'], $userInfo['real_name']);
                    $workItem['status'] = 2;
                }
                $works[] = $workItem;
            }
        }
        $allWorks = array_merge($allWorks, $works);
        switch ($statement->type) {
            case 1:
                $rs = WorkHelper::addDailyWorkItems($workId, $allWorks, $plans);
                break;
            case 2:
                $rs = WorkHelper::addWeeklyWorkItems($workId, $allWorks, $plans);
                break;
        }
        return $rs;
    }

    public static function editeWorkStatement($workId, $workData)
    {
        $statement = WorkStatementModel::findOne($workId);
        if (!$statement) {
            FResponse::output(['code' => 20001, 'msg' => "工作报告不存在"]);
        }
        if (2 == $statement->status) {
            FResponse::output(['code' => 20001, 'msg' => "工作报告已被审阅"]);
        }
        
    }

    /**
     * 新增日报
     * @param $workId
     * @param $workItems
     * @param $workPlans
     * @return bool
     */
    public static function addDailyWorkItems($workId, $workItems, $workPlans)
    {
        if (!is_array($workItems) || !is_array($workPlans)) {
            return false;
        }
        $rs = true;
        try {
            $transaction = Yii::$app->db->beginTransaction();//事务开始
            //新增日报
            foreach ($workItems as $items) {
                $rs = $rs && WorkDelegate::addWorkItem($workId, Dict::TYPE_DAILY_WORK, $items);
            }
            //新增明日计划
            foreach ($workPlans as $items) {
                $rs = $rs && WorkDelegate::addWorkItem($workId, Dict::TYPE_TOMORROW_WORK, $items);
            }
            //修改工作报告
            WorkStatementModel::updateWorkStatement($workId, ['status' => Dict::STATUS_WORK_TO_APPROVE, 'commit_time' => time()]);
            
            if ($rs) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        } catch (Exception $e) {
            $transaction->rollback();//回滚函数
            $rs['info'] = $e->getMessage();//异常信息
            FResponse::output(['code' => 20066, 'msg' => "新增工作报告失败" . $rs['info']]);
        }
        return $rs;
    }

    /**
     * 新增周报
     * @param $workId
     * @param $weeklyWork
     * @param $weeklyPlans
     * @return bool
     */
    public static function addWeeklyWorkItems($workId, $weeklyWork, $weeklyPlans)
    {
        if (!is_array($weeklyWork) || !is_array($weeklyPlans)) {
            return false;
        }
        $rs = true;
        try {
            $transaction = Yii::$app->db->beginTransaction();//事务开始
            //新增周报
            foreach ($weeklyWork as $items) {
                $rs = $rs && WorkDelegate::addWorkItem($workId, Dict::TYPE_WEEKLY_WORK, $items);
            }
            //新增下周计划
            foreach ($weeklyPlans as $items) {
                $rs = $rs && WorkDelegate::addWorkItem($workId, Dict::TYPE_NEXT_WEEK_WORK, $items);
            }

            //修改工作报告
            $rs = $rs && WorkStatementModel::updateWorkStatement($workId, ['status' => Dict::STATUS_WORK_TO_APPROVE, 'commit_time' => time()]);
            if ($rs) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        } catch (Exception $e) {
            $transaction->rollback();//回滚函数
            $rs['info'] = $e->getMessage();//异常信息
            FResponse::output(['code' => 20066, 'msg' => "新增工作报告失败" . $rs['info']]);
        }
        return $rs;
    }

    /**
     * 修改工作报告
     * @param $workItems
     * @return bool
     */
    public static function editWorkItems($workId, $workItems)
    {
        if (!is_array($workItems)) {
            return false;
        }
        $rs = true;
        try {
            $transaction = Yii::$app->db->beginTransaction();//事务开始
            //修改工作报告
            foreach ($workItems as $items) {
                if (!$items['item_id']) {
                    $rs = false;
                    break;
                }
                $rs = $rs && WorkDelegate::updateWorkItem($items['item_id'], $items);
            }

            if ($rs) {
                $transaction->commit();
            } else {
                $transaction->rollback();
            }
        } catch (Exception $e) {
            $transaction->rollback();//回滚函数
            $rs['info'] = $e->getMessage();//异常信息
            FResponse::output(['code' => 20066, 'msg' => "修改工作报告失败" . $rs['info']]);
        }
        return $rs;
    }

    /**
     * 获取周期
     * @param $type 1:日报  2:周报
     * @param $dateTime
     * @return mixed
     */
    public static function getWorkCycle($type, $dateTime)
    {
        if (1 == $type) {
            $cycle = date('Y-m-d', $dateTime);
        } elseif (2 == $type) {
            $cycle = self::getMonFri($dateTime);
            $cycle = implode('~', $cycle);
        }
        return $cycle;
    }

    /**
     * 获取周一周五日期
     * @param $dateTime
     * @return mixed
     */
    public static function getMonFri($dateTime){
        $curtime = $dateTime;

        $curWeekday = date('w', $dateTime);

        //为0是 就是 星期七
        $curWeekday = $curWeekday ? $curWeekday : 7;

        $curMon = $curtime - ($curWeekday - 1) * 86400;
        $curFri = $curtime + (5 - $curWeekday) * 86400;

        $cur['mon'] = date('Y-m-d', $curMon);
        $cur['fri'] = date('Y-m-d', $curFri);

        return $cur;
    }
}