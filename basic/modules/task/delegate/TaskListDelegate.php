<?php
namespace app\modules\task\delegate;
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/7/15
 * Time: 17:41
 */
use app\models\MembersModel;
use app\models\RewardTaskModel;
use yii;
use app\models\TaskModel;
use app\models\TaskRangeModel;
use app\models\TaskMemberModel;
use app\models\TaskAttachmentModel;
use app\models\TaskSkillModel;
use app\lib\Tools;
use app\lib\FResponse;

class TaskListDelegate
{
    public static function taskDelegate($uid, $proId, $taskType, $status, $beginTime, $endTime, $taskTitle, $overtime,$num, $current, $isPermStatus='')
    {
        $taskListData = TaskModel::getMyTaskList($uid, $proId, $taskType, $status, $beginTime, $endTime, $taskTitle, $overtime,$num, $current);
        foreach ($taskListData['taskListData'] as $key => $val){
            if( $val['end_time']<time()){
                $taskListData['taskListData'][$key]['overtime'] = 1;
            }
        }
        $taskListData['isPermStatus'] = empty($isPermStatus) ? 0 : $isPermStatus;
        FResponse::output(['code'=>20000, 'msg'=>'ok', 'data'=>$taskListData]);
    }
    /*
    * 任务列表详细信息
    */
    public static function getRewardListInfoData($taskId, $userInfo,$type=2)
    {
        //悬赏池列表详情基本信息
        if(Yii::$app->controller->action->id == "my-task-details" || Yii::$app->controller->action->id == "task-details"){
            $rewardDetailData = TaskModel::getTaskListDetails($taskId);
            $rewardDetailData['point'] = empty($rewardDetailData['point']) ? 0 : $rewardDetailData['point'];
//            if($rewardDetailData['charger'] != $userInfo['u_id'] && $type==1){
//                FResponse::output(['code'=>20005, 'msg'=>'数据不存在了~']);
//            }
            $taskType = 1;
            //是否已认领
        }
        $claim = false;
        if(Yii::$app->controller->id == "task-list" && Yii::$app->controller->action->id == "reward-list-details"){
            $taskType = 2;
            //悬赏池列表
            $rewardDetailData =  RewardTaskModel::getRewardTaskDetails($taskId);
            $rewardDetailData['is_charge'] = 0;
            $rewardDetailData['point'] = empty($rewardDetailData['point']) ? 0 : $rewardDetailData['point'];
            $rewardDetailData['createName'] = MembersModel::find()->where(['u_id'=>$rewardDetailData['creater']])->asArray()->one()['real_name'];
            //悬赏任务认领人相关数据
            $rewardClaimMan = TaskMemberModel::getRewardResponsibilityData($taskId);
            $rewardDetailData['is_applied'] = 0;//还未申请过
            foreach ($rewardClaimMan as $key => $value){
                if($value['is_charge']==1){
                    $claim = true;
                }
                $rewardClaimMan[$key]['headImg'] = Tools::getHeadImg($value['head_img']);
                if($value['u_id'] == $userInfo['u_id']){
                    $rewardDetailData['is_applied'] = 1;//已经申请过
                }
                if($value['u_id'] == $userInfo['u_id'] && $value['is_charge']!=1){
                    $rewardDetailData['is_charge'] = 0;
                }elseif ($value['u_id'] == $userInfo['u_id'] && $value['is_charge'] ==1){
                    $rewardDetailData['is_charge'] = 1;
                }
            }
            $rewardDetailData['applicant'] = $rewardClaimMan;
            $rewardDetailData['claim'] = $claim;

            //悬赏范围相关数据
            $rewardDetailData['range'] = TaskRangeModel::getRewardRangeInfo($taskId);
        } else {
            if (null == $rewardDetailData['reason']) {
                $rewardDetailData['reason'] = '';
            }
        }
        $rewardDetailData['headImg'] = Tools::getHeadImg($rewardDetailData['head_img']);

        //任务技能相关信息
        $skillInfo = TaskSkillModel::getTaskSkillRange($taskId, $taskType);
        //附件相关信息
        $attachmentFileInfo = TaskAttachmentModel::getAttachmentFileInfo($taskId, 1, $taskType);
        //附件相关信息
        $rewardDetailData['workNoteFiles'] = TaskAttachmentModel::getAttachmentFileInfo($taskId, 2, $taskType);
        $rewardDetailData['attachmentInfo'] = $attachmentFileInfo;

        //是否已超时
        $rewardDetailData['is_overtime'] = 0;
        if(time() > $rewardDetailData['end_time']) {
            $rewardDetailData['is_overtime'] = 1;
        }
        $rewardDetailData['skillInfo'] = $skillInfo;
        $rewardDetailData['rewardStatus'] = !empty($rewardDetailData['rewardStatus']) ? $rewardDetailData['rewardStatus']:"";
        return ['code'=>20000, 'msg'=> 'ok', 'data'=>$rewardDetailData];
    }
    
}