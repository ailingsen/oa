<?php
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/11/12
 * Time: 11:04
 */

namespace app\modules\v1\controllers;

use app\models\OrgModel;
use app\models\TaskMemberModel;
use app\models\TaskModel;
use app\models\RewardTaskModel;
use app\modules\workmate\delegate\WorkmateDelegate;
use app\models\OrgMemberModel;
use app\models\ProjectModel;
use app\models\MembersModel;
use app\modules\task\delegate\TaskDelegate;
use app\modules\task\helper\TaskHelper;
use app\lib\FResponse;
use app\lib\Tools;
use Yii;

class TaskController extends BaseController
{
    public $modelClass = 'app\models\OrgModel';
    /**
     * 获取所有组织列表
     * @return array
     */
    public function actionAllGroup()
    {
        $groupList = WorkmateDelegate::getAll();
        foreach ($groupList as $key => $value) {
            $orgIds = OrgModel::getAllChildrenOrgId($value['org_id']);
            if (empty($orgIds)) {
                $groupList[$key]['key'] = false;
            } else {
                $groupList[$key]['key'] = true;
            }
            $orgIds[] = $value['org_id'];
            $groupList[$key]['count'] = OrgMemberModel::getOrgMemberNum($orgIds);
        }
        $allGroupData = Tools::createTreeArr($groupList, 0, 'parent_org_id', 'org_id');
        FResponse::output(['code'=>2000, 'msg' => 'ok','data' => $allGroupData]);
    }

    /**
     * @return array
     * 创建任务
     */
    public function actionCreateTask()
    {
        $this->isPerm('TaskCreate');
        $postData = json_decode(file_get_contents("php://input"),true);
//        if($postData['point'] > MembersModel::find()->where(['u_id'=> $this->userInfo['u_id']])->asArray()->one()['leave_points']){
//            FResponse::output( ['code' => 20001, 'msg' => "纳米币不足！"]);
//        }
        if(strlen($postData['title'])>50){
            FResponse::output(['code'=>20001,'msg' => '任务标题不能超过50个字符！']);
        }
        TaskHelper::checkParams(1);
        if($postData['type']==1){
            if(empty($postData['pro_id'])){
                FResponse::output(['code'=>20001,'msg' => '请选择项目！']);
            }
            $proEndTime = ProjectModel::find()->where(['pro_id'=>$postData['pro_id']])->asArray()->one();
            if (strtotime($postData['startTime']) < $proEndTime['begin_time']){
                FResponse::output(['code'=>20001,'msg' => '任务开始时间不能小于该项目开始时间，请重新选择！']);
            }
            if(!empty($proEndTime['delay_time']) && (strtotime($postData['endTime']) > $proEndTime['delay_time'])){
                FResponse::output(['code'=>20001,'msg' => '任务结束时间不能大于该项目结束时间，请重新选择！']);
            }elseif (empty($proEndTime['delay_time']) && (strtotime($postData['endTime']) > $proEndTime['end_time'])){
                FResponse::output(['code'=>20001,'msg' => '任务结束时间不能大于该项目结束时间，请重新选择！']);
            }
        }
        if( strtotime($postData['startTime']) > strtotime($postData['endTime'])){
            FResponse::output(['code'=>20001,'msg' => '任务结束时间必须大于该结束时间，请重新选择！']);
        }
        if ($postData['point'] < 0) {
            FResponse::output(['code' => 20001, 'msg' => "纳米币必须为整数"]);
        }
        if (isset($postData['point']) && preg_match('/^\d+$/',$postData['point'])) {
            $uInfo = MembersModel::getUserMessage($this->userInfo['u_id'],'leave_points');
            TaskHelper::checkPoints($postData['point'],$uInfo);
        }
        !isset($postData['projectId']) && $postData['projectId'] = 0;

        if ($taskId = TaskDelegate::createTask($postData, $this->userInfo)) {
            //更新用户信息
            FResponse::output(['code' => 20000, 'msg' => "ok", 'data' => ['taskId' => $taskId]]);
        } else {
            FResponse::output(['code' => 20001, 'msg' => "新增失败，请检查参数"]);
        }

    }

    /**
     * 修改任务
     */
    public function actionUpdateTask()
    {
        $postData = json_decode(file_get_contents("php://input"),true);
        $charger = TaskModel::find()->where(['task_id'=>$postData['task_id']])->asArray()->one()['charger'];
//        if($postData['point'] > MembersModel::find()->where(['u_id'=> $this->userInfo['u_id']])->asArray()->one()['leave_points']){
//            FResponse::output( ['code' => 20001, 'msg' => "纳米币不足！"]);
//        }
        if(strlen($postData['task_title'])>50){
            FResponse::output(['code'=>20001,'msg' => '任务标题不能超过50个字符！']);
        }
        if($postData['taskType']==1){
            $proEndTime = ProjectModel::find()->where(['pro_id'=>$postData['pro_id']])->asArray()->one();
            if(strtotime($postData['begin_time']) < $proEndTime['begin_time']){
                FResponse::output(['code'=>20001,'msg' => '任务开始时间不能小于该项目开始时间，请重新选择！']);
            }
            if(!empty($proEndTime['delay_time']) && (strtotime($postData['end_time']) > $proEndTime['delay_time'])){
                FResponse::output(['code'=>20001,'msg' => '任务结束时间不能大于该项目结束时间，请重新选择！']);
            }
            if (empty($proEndTime['delay_time']) && (strtotime($postData['end_time']) > $proEndTime['end_time'])){
                FResponse::output(['code'=>20001,'msg' => '任务结束时间不能大于该项目结束时间，请重新选择！']);
            }
        }
        if( strtotime($postData['begin_time']) > strtotime($postData['end_time'])){
            FResponse::output(['code'=>20001,'msg' => '任务结束时间必须大于该结束时间，请重新选择！']);
        }
        if (!isset($postData['task_id'])) {
            FResponse::output(['code' => 20001, 'msg' => 'task_id不能为空']);
        }
        $rs = TaskDelegate::updateTask($this->userInfo, $postData);
        if ($rs) {
            $leavePoints = MembersModel::find()->where(['u_id'=>$this->userInfo['u_id']])->asArray()->one()['leave_points'];
            if($postData['taskType']==1 && $charger==$postData['charger']['u_id']){
                TaskDelegate::taskMsg($postData['charger']['u_id'],$this->userInfo['u_id'],$postData['task_id'],'编辑了任务',$postData['task_title'],1,1);
                Tools::msgJpush(3,$postData['task_id'],$this->userInfo['real_name'].'编辑了任务'.$postData['task_title'],[$postData['charger']['u_id']],['taskType'=>1]);
            }
            if($postData['taskType']==1 && $charger!=$postData['charger']['u_id']){
                TaskDelegate::taskMsg($postData['charger']['u_id'],$this->userInfo['u_id'],$postData['task_id'],'给你指派了任务',$postData['task_title'],1,1);
                TaskDelegate::taskMsg($charger,$this->userInfo['u_id'],$postData['task_id'],'重新指派了你的任务',$postData['task_title'],0,1);
                Tools::msgJpush(3,$postData['task_id'],$this->userInfo['real_name'].'给你指派了任务'.$postData['task_title'],[$postData['charger']['u_id']],['taskType'=>1]);
                Tools::msgJpush(3,$postData['task_id'],$this->userInfo['real_name'].'重新指派了您的任务'.$postData['task_title'],[$charger],['taskType'=>0]);
            }
            if($postData['taskType']==2){
                $taskId = RewardTaskModel::find()->where(['task_title'=>TaskModel::find()->where(['task_id'=>$postData['task_id']])->asArray()->one()['task_title']])->asArray()->one()['task_id'];
                $memberInfo = TaskMemberModel::find()->where(['task_id'=>$taskId,'is_charge'=>1])->asArray()->all();
                if(!empty($memberInfo)){
                    foreach ($memberInfo as $key => $val){
                        TaskDelegate::taskMsg($val['u_id'],$this->userInfo['u_id'],$postData['task_id'],'编辑了任务',$postData['task_title'],1,2);
                        Tools::msgJpush(3,$postData['task_id'],$this->userInfo['real_name'].'编辑了任务'.$postData['task_title'],[$val['u_id']],['taskType'=>1]);
                    }
                }
            }
            FResponse::output(['code' => 20000, 'msg' => '编辑任务保存成功','data' => $leavePoints]);
        }
        FResponse::output(['code' => 20001, 'msg' => '编辑任务保存失败']);
    }


    /**
     * 文件上传
     * @param $type
     * @param $taskType
     * @return array
     */
    public function actionUpload()
    {
        $postData = Yii::$app->request->post();
        $type = $postData['type'];
        $taskType = $postData['taskType'];
        $taskId = isset($postData['taskId']) && !empty($postData['taskId']) ? $postData['taskId']: 0;
        if($taskType == 1 && TaskModel::find()->where(['task_id'=>$taskId])->asArray()->one()['status']==3){
            FResponse::output(['code' => '20001', 'msg' => '待审核任务，不能上传附件！']);
        }
        $rs = TaskHelper::upload($this->userInfo, $taskId, $type, $taskType);
        if ($rs['task_att_id'] > 0) {
            FResponse::output(['code' => '20000', 'msg' => 'ok', 'data' => $rs]);
        }
        FResponse::output(['code' => '20001', 'msg' => '上传失败']);
    }

    /**
     * 删除已上传附件
     * @param $attId
     * @return array'
     */
    public function actionDelAtt()
    {
        $postData = json_decode(file_get_contents("php://input"),true);
        $attId = $postData['attId'];
        TaskDelegate::delAttachment($attId, $this->userInfo);
    }

    /**
     * 获取个人可分配的积分
     */
    public function actionGetLeavePoints()
    {
        $leavePoints = MembersModel::find()->where(['u_id'=>$this->userInfo['u_id']])->asArray()->one()['leave_points'];
        $leavePoints= empty($leavePoints) ? 0 : $leavePoints;
        FResponse::output(['code'=>20000,'msg'=>'ok','data'=>$leavePoints]);
    }
}