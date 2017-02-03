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
use app\models\RewardTaskModel;
use app\models\TaskAttachmentModel;
use app\models\TaskLogModel;
use app\models\TaskMemberModel;
use app\models\TaskModel;
use app\models\TaskRangeModel;
use app\models\TaskSkillModel;
use app\modules\task\delegate\TaskDelegate;
use yii;

Class RewardTaskHelper {

    /**
     * 认领悬赏任务
     * @param $userInfo
     * @param $taskId
     * @return int
     * @throws yii\db\Exception
     */
    public static function claimTask($userInfo, $taskId)
    {
        //判断任务详情
        $taskDetail = RewardTaskModel::findOne($taskId);
        if (0 == $taskDetail->status) {
            FResponse::output(['code' => 20074, 'msg' => '该任务还没发布！']);
        }
        if (2 == $taskDetail->status) {
            FResponse::output(['code' => 20074, 'msg' => '该任务已经被指派！']);
        }
        if (5 == $taskDetail->status) {
            FResponse::output(['code' => 20074, 'msg' => '该任务已经被关闭！']);
        }
        $info = MembersModel::getUserInfo($taskDetail->creater);
        if (time() - $taskDetail->end_time > 0) {
            FResponse::output(['code' => 20072, 'msg' => '该任务已超时！']);
        }
        //检测之前是否已经认领
        $taskMembers = TaskMemberModel::findOne(['u_id' => $userInfo['u_id'], 'task_id' => $taskId]);
        if (!empty($taskMembers)) {
            FResponse::output(['code' => 20075, 'msg' => '该悬赏任务您已经认领！']);
        }

        //检查是否符合悬赏范围
        $taskRange = TaskRangeModel::find()->where(['task_id' => $taskId, 'org_id' => $userInfo['org']['org_id']])->asArray()->all();
        if (!$taskRange || empty($taskRange)) {
            FResponse::output(['code' => 20075, 'msg' => '您所在的部门不在悬赏范围内！']);
        }
        $data = [
            'u_id' => $userInfo['u_id'],
            'task_id' => intval($taskId),
            'create_time' => time()
        ];
        $rs = Yii::$app->db->createCommand()->insert('oa_task_member', $data)->execute();

        if ($rs) {
            //修改子状态为待确认
            $taskDetail->sub_status = 2;
            $taskDetail->save(false);
        }
        //发邮件
//        $url = urlencode($_SERVER['HTTP_HOST'] . '#/task/offer/3/offerDetail/' . $taskId . '/3');
//        Tools::asynSendMail($info['username'], $userInfo['real_name'] . '认领了你悬赏的任务 ' . $taskDetail['task_title'] . $url, $info['real_name'], $userInfo['real_name']);

        //插入任务日志
        TaskLogModel::insertTaskLog($userInfo, "认领了任务", $taskId, 2);
        TaskDelegate::taskMsg($taskDetail->creater,$userInfo['u_id'],$taskId,'申请认领了你发布的悬赏任务',$taskDetail->task_title,4,2);
        Tools::msgJpush(3,$taskId,$userInfo['real_name'].'申请认领了你发布的悬赏任务'.$taskDetail->task_title,[$taskDetail->creater],['taskType'=>4]);
        return $rs;
    }

    /**
     * @param $postData
     * @param $userInfo
     * @return bool
     */
    public static function pointTask($postData, $userInfo)
    {
        $taskDetail = RewardTaskModel::findOne(['task_id' => $postData['task_id']]);
        if (2 == $taskDetail->status) {
            FResponse::output(['code' => 20074, 'msg' => '该任务已经被指派！']);
        }
        //检测之前是否已经认领
        //判断权限
        if($userInfo['u_id'] != $taskDetail['creater']){
            FResponse::output(['code' => 20096, 'msg' => '您没有权限操作该步骤！']);
        }
        //修改任务状态
        $taskDetail->status = 2;
        try {
            $rs = true;
            $transaction = Yii::$app->db->beginTransaction();//事务开始
            if ($taskDetail->save(false)) {
                //插入指派任务表
                $taskModel = new TaskModel();
                $taskModel->attributes = $taskDetail->attributes;
                $taskModel->setAttribute('task_id', '');
                $taskModel->setAttribute('task_type', 2);
                $taskModel->charger = $postData['point_uid'];
                $taskModel->status = 2;
                $taskModel->is_publish = 1;
                $taskModel->save(false);
                $taskId = Yii::$app->db->lastInsertID;

                //修改oa_task_member
                $taskMemberModel = TaskMemberModel::findOne(['u_id' => $postData['point_uid'], 'task_id' => $taskDetail->task_id]);
                $taskMemberModel->is_charge = 1;
                $taskMemberModel->save(false);

                //插入技能表
                $rewardSkills = TaskSkillModel::find()->where(['task_id' => $taskDetail->task_id, 'task_type' => 2])->asArray()->all();
                TaskDelegate::insertSkill($rewardSkills, $taskId, 1);

                //插入附件表
                $attacheList = TaskAttachmentModel::find()->where(['task_id' => $taskDetail->task_id, 'task_type' => 2])->asArray()->all();

                foreach ($attacheList as $item) {
                    unset($item['id']);
                    $item['task_id'] = $taskId;
                    $item['task_type'] = 1;
                    TaskAttachmentModel::createX($item);
                }

                //插入日志表
                $assignPersonName = MembersModel::find()->select('real_name')->where(['u_id' => $postData['point_uid']])->asArray()->one()['real_name'];
                TaskDelegate::taskMsg($postData['point_uid'],$userInfo['u_id'],$taskId,'确认将悬赏任务分配给你',$taskDetail->task_title,1,2);
                Tools::msgJpush(3,$taskId,$userInfo['real_name'].'确认将悬赏任务分配给你'.$taskDetail->task_title,[$postData['point_uid']],['taskType'=>1]);
                $memberInfo = TaskMemberModel::find()->where(['task_id'=>$postData['task_id'],'is_charge'=>0])->asArray()->all();
                if(!empty($memberInfo)){
                    foreach ($memberInfo as $key => $val){
                        TaskDelegate::taskMsg($val['u_id'],$userInfo['u_id'],$postData['task_id'],'将你申请的悬赏任务已确认，你未认领成功',$taskDetail->task_title,3,2);
                        Tools::msgJpush(3,$postData['task_id'],$userInfo['real_name'].'将你申请的悬赏任务已确认，你未认领成功'.$taskDetail->task_title,[$val['u_id']],['taskType'=>3]);
                    }
                }
                $logList = [
                    'u_id' => $userInfo['u_id'],
                    'task_id' => $taskDetail->task_id,
                    'task_type' => 2,
                    'content' => '确认'.$taskDetail->task_title.'任务分配给了'.$assignPersonName,
                    'create_time' => time()
                ];
                TaskLogModel::createX($logList);
//                $taskLogData = TaskLogModel::find()->where(['task_id' => $postData['task_id'],'task_type' => 2])->asArray()->all();
//                foreach ($taskLogData as $k => $v){
//                    TaskDelegate::taskLogData($v,$taskId);
//                }
                //$logList = TaskLogModel::find()->where(['task_id' => $taskDetail->task_id, 'task_type' => 2])->asArray()->all();
//                foreach ($logList as $item) {
//                    unset($item['id']);
//                    $item['task_id'] = $taskId;
//                    TaskLogModel::createX($item);
//                }

                //修改指派人
    //            $info = MembersModel::getUserInfo($postData->pointUid);
    //            $memberList = TaskMemberModel::find()->where('task_id=:taskId' ,[':taskId' => $postData['task_id']])->asArray()->all();
    //            $memberArr = array();
    //            foreach ($memberList as $key => $value) {
    //                if ($value['member_id'] != $postData->pointUid) {
    //                    $memberArr[$key]['username'] = $value['username'];
    //                    $memberArr[$key]['real_name'] = $value['real_name'];
    //                    $memberArr[$key]['member_id'] = $value['member_id'];
    //                    $memberArr[$key]['allow_task_app'] = $value['allow_task_app'];
    //                }
    //            }
    //            foreach ($memberArr as $key => $value) {
    //                Tools::asynSendMail($value['username'], $userInfo['real_name'] . '将任务 ' . $taskDetail['task_title'] . '指派给了' . $info['real_name'] . ',任务结束', $value['real_name'], $userInfo['real_name']);
    //
    //            }
    //            $url = urlencode($_SERVER['HTTP_HOST'] . '#/task/myTask/1/mytaskDetail/' . $postData['task_id'] . '/1');
    //            Tools::asynSendMail($info['username'], $userInfo['real_name'] . '已指派悬赏任务 ' . $taskDetail['task_title'] . '给你,' . $url, $info['real_name'], $userInfo['real_name']);
                $rs = true;
                if ($rs) {
                    $transaction->commit();//事物结束
                } else {
                    $transaction->rollback();//回滚函数
                }
            }
        } catch (Exception $e) {
            $transaction->rollback();//回滚函数
            $rs['info'] = $e->getMessage();//异常信息
            FResponse::output(['code' => 20063, 'msg' => "数据库操作失败"]);
        }
        return $rs;
    }

}

