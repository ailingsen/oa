<?php
/**
 * Created by PhpStorm.
 * User: liaoshuochao
 * Date: 2016/7/15
 * Time: 10:30
 */
namespace app\modules\task\helper;

use app\lib\FResponse;
use app\lib\Tools;
use app\models\MembersModel;
use app\models\ProjectLogModel;
use app\models\TaskMsgModel;
use app\modules\task\delegate\TaskDelegate;
use app\models\ProjectMemberModel;
use app\models\RewardTaskModel;
use app\models\ScoreLogModel;
use app\models\SkillMemberModel;
use app\models\TaskAttachmentModel;
use app\models\TaskLogModel;
use app\models\TaskModel;
use app\models\TaskSkillModel;
use yii;

Class DoTaskHelper {
    public static function isTaskExist($taskId, $taskType)
    {
        if (1 == $taskType) {
            $taskInfo = TaskModel::findOne(['task_id' => $taskId]);
        } else {
            $taskInfo = RewardTaskModel::findOne(['task_id' => $taskId]);
        }
        if (!$taskInfo) {
            FResponse::output(['code' => 20011, 'msg' => '没找到该任务']);
        }
        return $taskInfo;
    }
    
    public static function acceptTask($taskInfo, $userInfo)
    {
        $taskInfo->status = 2;
        $transaction = Yii::$app->db->beginTransaction();//事务开始
        try {
            //项目内发布指定任务若指定人不在该项目内则添加项目成员关系
            $insProMemRes = true;
            if (!empty($taskInfo['pro_id']) && $taskInfo['charger']) {
                $projectMemberModel = new ProjectMemberModel();
                $proMember = ProjectMemberModel::getProMemberByProAndMember($taskInfo['charger'], $taskInfo['pro_id']);
                if (empty($proMember)) {
                    //更新项目成员信息
                    $insProMemRes = ProjectMemberModel::insertProMember($taskInfo['charger'], $taskInfo['pro_id']);
                }
            }

            $taskInfo->update_time = time();
            $rs = false;
            if ($taskInfo->save(false) && $insProMemRes) {
                $rs = true;

                //插入任务日志
                $rs = $rs && TaskLogModel::insertTaskLog($userInfo,  "接受了任务 " . $taskInfo->task_title, $taskInfo['task_id']);
            }
            if ($rs) {
                $transaction->commit();//事物结束
            } else {
                $transaction->rollback();//回滚函数
            }
        } catch (Exception $e) {
            $transaction->rollback();//回滚函数
            FResponse::output(['code' => 20063, 'msg' => "数据库操作失败" . $e->getMessage()]);
        }

        return $rs;
    }

    /**
     * 拒绝任务
     * @param $userInfo
     * @param $reason
     * @param $taskId
     * @return bool
     * @throws yii\db\Exception
     */
    public static function refuseTask($userInfo, $reason, $taskId)
    {
        $transaction = Yii::$app->db->beginTransaction();//事务开始

        try {
            $taskInfo = self::isTaskExist($taskId, 1);
            $taskInfo->status = 6;
            if ($taskInfo->save(false)) {
                //插入任务日志
                TaskLogModel::insertTaskLog($userInfo, "拒绝任务：" . $taskInfo->task_title . ',原因：' . $reason, $taskId);
                TaskDelegate::taskMsg($taskInfo->creater,$userInfo['u_id'],$taskId,'拒绝了你分配的任务',$taskInfo->task_title,2,1);
                Tools::msgJpush(3,$taskId,$userInfo['real_name'].'拒绝了你分配的任务'.$taskInfo->task_title,[$taskInfo->creater],['taskType'=>2]);
                $info = MembersModel::getUserInfo($taskInfo['creater']);
                //发邮件
                //$url = urlencode($_SERVER['HTTP_HOST'] . '#/task/release/1/releaseDetail/' . $taskId . '/2');
//                Tools::asynSendMail($info['username'], $userInfo['real_name'] . '拒绝了任务 ' . $taskInfo['task_title'] . $url, $info['real_name'], $userInfo['real_name']);
                $transaction->commit();//事物结束
                return true;
            }
        } catch (Exception $e) {
            $transaction->rollback();//回滚函数
            FResponse::output(['code' => 20063, 'msg' => "数据库操作失败" . $e->getMessage()]);
        }
        return false;
    }

    /**
     * @param $taskId
     * @param $workNote
     * @param $userInfo
     * @return bool
     * @throws yii\db\Exception
     */
    public static function commitTask($taskId, $workNote, $userInfo)
    {
        $taskInfo = self::isTaskExist($taskId, 1);
        if($taskInfo->status==3){
            FResponse::output(['code' => 20001, 'msg' => "已提交工作笔记相关信息！"]);
        }
        $info = MembersModel::getUserInfo($taskInfo->creater);
        $transaction = Yii::$app->db->beginTransaction();//事务开始

        $rs = false;
        try {
            $taskInfo->status = 3;
            $taskInfo->work_note = trim($workNote);
            if ($taskInfo->save(false)) {
                //插入任务日志
                $log = "提交任务" . $taskInfo->task_title;
                if (trim($workNote)) {
                    $log = "提交任务" . $taskInfo->task_title . ',工作笔记：' . trim($workNote);
                }
                TaskLogModel::insertTaskLog($userInfo, $log, $taskId);
                TaskDelegate::taskMsg($taskInfo->creater,$userInfo['u_id'],$taskId,'提交了你分配的任务',$taskInfo->task_title,2,1);
                Tools::msgJpush(3,$taskId,$userInfo['real_name'].'提交了你分配的任务'.$taskInfo->task_title,[$taskInfo->creater],['taskType'=>2]);
                //发邮件
//                $url = urlencode($_SERVER['HTTP_HOST'] . '#/task/release/1/releaseDetail/' . $taskId . '/2');
//                Tools::asynSendMail($info['username'], $userInfo['real_name'] . '提交了任务审核 ' . $taskInfo['task_title'] . $url, $info['email']);

                $transaction->commit();//事物结束
                $rs = true;
            }
        } catch (Exception $e) {
            $transaction->rollback();//回滚函数
            FResponse::output(['code' => 20063, 'msg' => "数据库操作失败" . $e->getMessage(), 'data'=>new Object()]);
        }
        return $rs;
    }

    /**
     * 审核不通过
     * @param $userInfo
     * @param $postData
     * @param $taskId
     * @param $rePointUser
     * @return bool
     * @throws yii\db\Exception
     */
    public static function notPass($userInfo, $postData, $taskId)
    {
        $transaction = Yii::$app->db->beginTransaction();//事务开始
        try {
            $taskInfo = self::isTaskExist($taskId, 1);
            $taskInfo->status = 2;
            $taskInfo->reason = trim($postData['reason']);
            $taskCharger = $taskInfo->charger;
            if (isset($postData['pointer']) && !empty($postData['pointer'])) {
                if($taskCharger != $postData['pointer']){
                    $taskInfo->status = 1;
                    $taskInfo->is_repoint = 1;
                }
                $taskInfo->charger = $postData['pointer'];
            }
            if ($taskInfo->save(false)) {
                //插入任务日志
                TaskLogModel::insertTaskLog($userInfo, $taskInfo->task_title . "审核不通过" . ',原因：' . $postData['reason'], $taskId);
                TaskDelegate::taskMsg($taskCharger,$userInfo['u_id'],$taskId,'未通过你提交的任务',$taskInfo->task_title,1,1);
                Tools::msgJpush(3,$taskId,$userInfo['real_name'].'未通过你提交的任务'.$taskInfo->task_title,[$taskCharger],['taskType'=>1]);
                if(!empty($postData['pointer']) && ($postData['pointer'] != $taskCharger)){
                    TaskDelegate::taskMsg($taskCharger,$userInfo['u_id'],$taskId,'重新指派了你提交的任务',$taskInfo->task_title,0,1);
                    Tools::msgJpush(3,$taskId,$userInfo['real_name'].'重新指派了你提交的任务'.$taskInfo->task_title,[$taskCharger],['taskType'=>0]);
                    TaskDelegate::taskMsg($postData['pointer'],$userInfo['u_id'],$taskId,'给你指派了任务',$taskInfo->task_title,1,1);
                    Tools::msgJpush(3,$taskId,$userInfo['real_name'].'给你指派了任务'.$taskInfo->task_title,[$postData['pointer']],['taskType'=>1]);
                }

//                if (isset($rePointUser)) {
//                    TaskLogModel::insertTaskLog($userInfo, $userInfo['real_name'] . "重新指派了任务给" . $rePointUser, $taskId);
//                }

                $info = MembersModel::getUserInfo($taskInfo['creater']);
                //发邮件
//                $url = urlencode($_SERVER['HTTP_HOST'] . '#/task/release/1/releaseDetail/' . $taskId . '/2');
//                Tools::asynSendMail($info['username'], $userInfo['real_name'] . '审核不通过任务 ' . $taskInfo['task_title'] . $url, $info['real_name'], $userInfo['real_name']);
                $transaction->commit();//事物结束
            }
        } catch (Exception $e) {
            $transaction->rollback();//回滚函数
            FResponse::output(['code' => 20063, 'msg' => "数据库操作失败" . $e->getMessage()]);
        }
        return true;
    }

    /**
     * 删除任务
     * @param $userInfo
     * @param $taskId
     * @param $taskType
     * @return bool
     * @throws \Exception
     * @throws yii\db\Exception
     */
    public static function deleteTask($userInfo, $taskId, $taskType)
    {
        $taskInfo = self::isTaskExist($taskId, $taskType);

        if (1 == $taskInfo->is_publish) {
            FResponse::output(['code' => 20091, 'msg' => "不能删除已发布任务"]);
        }
        //判断权限
        if ($userInfo['u_id'] != $taskInfo->creater) {
            FResponse::output(['code' => 20092, 'msg' => "您没有权限操作该步骤"]);
        }

        $transaction = Yii::$app->db->beginTransaction();//事务开始

        $rs = true;
        try {
            if ($taskInfo->is_publish == 0 && $taskInfo->delete()) {

                //插入任务日志
//                TaskLogModel::insertTaskLog($userInfo, $userInfo['real_name'] . "删除了该任务", $taskInfo->task_id);
//                if ($taskType == 1 && $taskInfo->pro_id > 0) {
//                    ProjectLogModel::addLog($userInfo, $userInfo['real_name'] . "删除了任务'" . $taskInfo->task_title . "'", $taskInfo->pro_id);
//                }

                //删除技能
                TaskSkillModel::deleteAll(['task_id' => $taskInfo->task_id, 'task_type' => $taskType]);

                //删除附件
                TaskAttachmentModel::deleteAll(['task_id' => $taskInfo->task_id, 'task_type' => $taskType]);
                if(!empty($taskInfo->charger)){
                    TaskDelegate::taskMsg($taskInfo->charger,$userInfo['u_id'],$taskId,'关闭了分配给你的任务',$taskInfo->task_title,0,$taskInfo->task_type);
                }
                //返回任务积分(任务关闭和产品沟通后取消积分返还)
                if ($taskInfo->point > 0) {
                 /*   $rs = $rs && Yii::$app->db->createCommand('update oa_members set leave_points=leave_points+' . $taskInfo->point . " where u_id=" . $taskInfo->creater)->execute();
                    $logInfo = ['u_id' => $taskInfo->creater,
                        'type' => 2,
                        'content' => '删除任务返还积分',
                        'score' => $taskInfo->point,
                        'score_before' => $userInfo['leave_points'],
                        'score_after' => $userInfo['leave_points'] + $taskInfo->point,
                        'create_time' => time(),
                        'operator' => $userInfo['u_id']
                    ];
                    ScoreLogModel::insertScoreLog($logInfo);*/
                }
            }
            if ($rs) {
                $transaction->commit();//事物结束
            } else {
                $transaction->rollBack();//回滚
            }
        } catch (Exception $e) {
            $transaction->rollback();//回滚函数
            FResponse::output(['code' => 20063, 'msg' => "数据库操作失败" . $e->getMessage()]);
        }
        return $rs;
    }

    /**
     * 关闭任务
     * @param $userInfo
     * @param $taskId
     * @param $taskType
     * @return bool
     * @throws yii\db\Exception
     */
    public static function closeTask($userInfo, $taskId, $taskType)
    {
        $taskInfo = self::isTaskExist($taskId, $taskType);
        if (!in_array($taskInfo->status, [1, 2, 6])) {
            FResponse::output(['code' => 20091, 'msg' => "只能关闭“待接受”“进行中”“已拒绝”的任务"]);
        }
        //判断权限
        if ($userInfo['u_id'] != $taskInfo->creater) {
            FResponse::output(['code' => 20092, 'msg' => "您没有权限操作该步骤"]);
        }

        $transaction = Yii::$app->db->beginTransaction();//事务开始

        $rs = true;
        try {
            $taskInfo->status = 5;
            if ($taskInfo->save(false)) {
                //插入任务日志
                TaskLogModel::insertTaskLog($userInfo, "关闭了该任务" . $taskInfo->task_title, $taskInfo->task_id, $taskType);
                if(!empty($taskInfo->charger)){
                    TaskDelegate::taskMsg($taskInfo->charger,$userInfo['u_id'],$taskId,'关闭了分配给你的任务',$taskInfo->task_title,1,1);
                    Tools::msgJpush(3,$taskInfo->task_id,$userInfo['real_name'].'关闭了分配给你的任务'.$taskInfo->task_title,[$taskInfo->charger],['taskType'=>1]);
                }
                //返回任务积分(任务关闭和产品沟通后取消积分返还)
                if ($taskInfo['point'] > 0) {
                    /*$rs = $rs && Yii::$app->db->createCommand('update oa_members set leave_points=leave_points+' . $taskInfo['point'] . " where u_id=" . $taskInfo->creater)->execute();
                    //积分日志
                    $scoreBefore = MembersModel::find()->select('leave_points')->where(['u_id' => $taskInfo['creater']])->asArray()->one()['leave_points'];
                    $scoreAfter = $scoreBefore+ $taskInfo['point'];
                    $logInfo = ['u_id' => $userInfo['u_id'],
                        'type' => 1,
                        'content' => '关闭任务返还积分！',
                        'score' => $taskInfo['point'] ,
                        'score_before' => $scoreBefore,
                        'score_after' => $scoreAfter ,
                        'create_time' => time(),
                        'operator' => $taskInfo['creater']
                    ];
                    ScoreLogModel::insertScoreLog($logInfo);*/
                }
            }
            if ($rs) {
                $transaction->commit();//事物结束
            } else {
                $transaction->rollBack();//回滚
            }
        } catch (Exception $e) {
            $transaction->rollback();//回滚函数
            FResponse::output(['code' => 20063, 'msg' => "数据库操作失败" . $e->getMessage()]);
        }
        return $rs;
    }

    /**
     * 延期任务
     * @param $userInfo
     * @param $taskId
     * @param $delayTime
     * @param  $reason
     * @param $taskType
     * @return bool
     * @throws yii\db\Exception
     */
    public static function delayTask($userInfo, $taskId, $delayTime, $reason, $taskType)
    {
        $taskInfo = self::isTaskExist($taskId, $taskType);

        if (!in_array($taskInfo->status, [2, 3])) {
            FResponse::output(['code' => 20091, 'msg' => "只能延期进行中和待审核的任务"]);
        }
        //判断权限
        if ($userInfo['u_id'] != $taskInfo->creater) {
            FResponse::output(['code' => 20092, 'msg' => "您没有权限操作该步骤"]);
        }

        $transaction = Yii::$app->db->beginTransaction();//事务开始

        $rs = true;
        try {
            $taskInfo->delay_time = strtotime($delayTime);
            if ($taskInfo->save(false)) {
                //插入任务日志
                TaskLogModel::insertTaskLog($userInfo, "延期任务" . $taskInfo->task_title . '到' . date('Ymd H:i', strtotime($delayTime)) . "，延期原因：" . $reason, $taskInfo->task_id);
                TaskDelegate::taskMsg($taskInfo['charger'],$userInfo['u_id'],$taskId,'延期了任务',$taskInfo->task_title,1,$taskType);
                Tools::msgJpush(3,$taskId,$userInfo['real_name'].'延期了任务'.$taskInfo->task_title,[$taskInfo['charger']],['taskType'=>1]);
            }
            if ($rs) {
                $transaction->commit();//事物结束
            } else {
                $transaction->rollBack();//回滚
            }
        } catch (Exception $e) {
            $transaction->rollback();//回滚函数
            FResponse::output(['code' => 20063, 'msg' => "数据库操作失败" . $e->getMessage()]);
        }
        return $rs;
    }

    /**
     * @param $postData
     * @param $taskId
     * @param $userInfo
     * @return bool
     * @throws yii\db\Exception
     */
    public static function doneTask($postData,$taskId, $userInfo)
    {
        //质量
        $quality = $postData['quality'];
        //速度
        $speed = $postData['speed'];
        //获取任务详情
        $taskInfo = TaskModel::findOne(['task_id' => $taskId]);
        if (!$taskInfo) {
            FResponse::output(['code' => 20081, 'msg' => "没找到该任务"]);
        }
        if ($taskInfo['status'] == 4) {
            FResponse::output(['code' => 20082, 'msg' => '该任务已经完成！']);
        }
        $taskInfo->status = 4;
        $taskInfo->update_time = time();
        //任务质量速度得分
        $taskInfo->quality = $quality;
        $taskInfo->speed = $speed;
        $taskInfo->reason = '';

        $transaction = Yii::$app->db->beginTransaction();//事务开始

        $rs = true;
        try {
            if ($taskInfo->save(false)) { //获取用户信息
                //插入任务日志
                TaskLogModel::insertTaskLog($userInfo, $taskInfo->task_title . " 审核通过", $taskInfo->task_id);
                $info = MembersModel::findOne(['u_id' => $taskInfo->charger]);
//                if ($taskInfo->pro_id > 0) {
//                    ProjectLogModel::addLog($userInfo, $info['real_name'] . "完成了任务'" . $taskInfo->task_title . "'", $taskInfo->pro_id);
//                }
                //更新用户积分
                if ($taskInfo->point > 0) {
                    $info->points = $info->points + $taskInfo->point;
                    $logInfo = ['u_id' => $taskInfo->charger,
                        'type' => 1,
                        'content' => '任务获取：' . $taskInfo->task_title,
                        'score' => $taskInfo->point,
                        'score_before' => $info->oldAttributes['points'],
                        'score_after' => $info->points,
                        'create_time' => time(),
                        'operator' => $userInfo['u_id']
                    ];
                    ScoreLogModel::insertScoreLog($logInfo);
                    if ($info->save(false)) {
                        MembersModel::deleteUserCache($info->u_id);
                    }
                }

                //更新个人技能得分
                $taskSkillList = TaskSkillModel::find()
                    ->where(['task_id' => $taskInfo->task_id, 'task_type' => 1])
                    ->asArray()
                    ->all();
                if ($taskSkillList) {
                    SkillMemberModel::upMemberSkillSorce($taskSkillList, $taskInfo->charger, $quality + $speed);
                }
                TaskLogModel::insertTaskLog($userInfo, '任务完成', $taskInfo->task_id);
            } else {
                FResponse::output(['code' => 20091, 'msg' => '网络超时！']);
            }
            if ($rs) {
                TaskDelegate::taskMsg($taskInfo->charger,$userInfo['u_id'],$taskId,'通过了你提交的任务',$taskInfo->task_title,1,1);
                Tools::msgJpush(3,$taskId,$userInfo['real_name'].'通过了你提交的任务'.$taskInfo->task_title,[$taskInfo->charger],['taskType'=>1]);
                $transaction->commit();//事物结束
            } else {
                $transaction->rollback();//回滚函数
            }
        } catch (Exception $e) {
            $transaction->rollback();//回滚函数
            FResponse::output(['code' => 20063, 'msg' => "数据库操作失败" . $e->getMessage()]);
        }
        return $rs;
    }

    /**
     * 发布任务
     * @param $taskId
     * @param $userInfo
     * @param $taskType
     * @return bool
     * @throws yii\db\Exception
     */
    public static function publish($taskId, $userInfo, $taskType)
    {
        $taskInfo = self::isTaskExist($taskId, $taskType);
        //判断权限
        if($userInfo['u_id'] != $taskInfo->creater){
            FResponse::output(['code' => 20092, 'msg' => "您没有权限操作该步骤"]);
        }

        $rs = false;
        $taskInfo->status = 1;
        $taskInfo->is_publish = 1;
        if ($taskInfo->save(false)) {
            //插入任务日志
            TaskLogModel::insertTaskLog($userInfo, "发布了任务" . $taskInfo->task_title, $taskId, $taskType);
            if(!empty($taskInfo->charger)){
                TaskDelegate::taskMsg($taskInfo->charger,$userInfo['u_id'],$taskId,'给你指派了任务',$taskInfo->task_title,1,1);
                Tools::msgJpush(3,$taskId,$userInfo['real_name'].'给你指派了任务'.$taskInfo->task_title,[$taskInfo->charger],['taskType'=>1]);
            }
            $rs = true;
        }
        return $rs;
    }
}

