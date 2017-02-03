<?php

namespace app\modules\task\controllers;

use app\controllers\BaseController;
use app\models\TaskModel;
use app\modules\task\delegate\TaskDelegate;
use app\modules\task\helper\DoTaskHelper;
use app\lib\Tools;
use Yii;
use yii\db\Exception;
use app\models\TaskSkillModel;

class DotaskController extends BaseController
{
    public $modelClass = 'app\models\MembersModel';

    /**
     * 接受任务
     * @param $taskId
     * @return array
     */
    public function actionAcceptTask($taskId)
    {
        if (!$taskId) {
            return ['code' => 20081, 'msg' => "系统参数错误"];
        }
        $taskInfo = DoTaskHelper::isTaskExist($taskId, 1);
        if ($taskInfo->status != 1) {
            return ['code' => 20011, 'msg' => '该任务已经被接受'];
        }
        if($taskInfo->charger != $this->userInfo['u_id']){
            return ['code' => 20011, 'msg' => '失败，该任务已指派给他人'];
        }
        $rs = DoTaskHelper::acceptTask($taskInfo, $this->userInfo);

        if ($rs) {
            TaskDelegate::taskMsg($taskInfo->creater,$this->userInfo['u_id'],$taskId,'接受了你分配的任务',$taskInfo->task_title,2,1);
//            $type, $id, $content, $uid = [], $extras = []
            Tools::msgJpush(3,$taskId,$this->userInfo['real_name'].'接受了你分配的任务'.$taskInfo->task_title,[$taskInfo->charger],['taskType'=>2]);
            return ['code' => 20000, 'msg' => '接受成功'];
        }
        return ['code' => 20064, 'msg' => '数据库操作失败'];
    }

    /**
     * 待接收拒绝任务
     * @throws Exception
     */
    public function actionRefuseTask()
    {
        $postData = json_decode(file_get_contents("php://input"));
        if (!$postData->task_id) {
            return ['code' => 20081, 'msg' => "系统参数错误"]; 
        }

        //获取拒绝理由
        if (!$postData->reason) {
            return ['code' => 20011, 'msg' => "请填写拒绝理由"]; 
        }
        $taskInfo = DoTaskHelper::isTaskExist($postData->task_id, 1);
        if($taskInfo->charger != $this->userInfo['u_id']){
            return ['code' => 20001, 'msg' => '失败，该任务已指派给他人'];
        }
        $rs = DoTaskHelper::refuseTask($this->userInfo, $postData->reason, $postData->task_id);

        if ($rs) {
            return ['code' => 20000, 'msg' => '操作成功'];
        } 
        return ['code' => 20001, 'msg' => '操作失败'];
    }

    /**
     * 提交审核
     * @throws Exception
     */
    public function actionCommitTask()
    {
        if (!Yii::$app->request->post('task_id')) {
            return ['code' => 20081, 'msg' => "系统参数错误"]; 
        }

        $rs = DoTaskHelper::commitTask(Yii::$app->request->post('task_id'), Yii::$app->request->post('work_note'), $this->userInfo);

        if ($rs) {
            return ['code' => 20000, 'msg' => '提交任务成功，请等待审核'];
        }
        return ['code' => 20001, 'msg' => '提交任务失败'];
    }

    /**
     * 删除任务
     * @throws Exception
     */
    public function actionDeleteTask()
    {
        $postData = json_decode(file_get_contents("php://input"));
        if (!$postData->task_id) {
            return ['code' => 20081, 'msg' => "系统参数错误"]; 
        }
        $rs = DoTaskHelper::deleteTask($this->userInfo, $postData->task_id, $postData->task_type);

        if ($rs) {
            return ['code' => 20000, 'msg' => '删除成功'];
        }
        return ['code' => 20001, 'msg' => '删除失败！']; 
    }

    /**
     * 关闭任务
     */
    public function actionCloseTask()
    {
        $postData = json_decode(file_get_contents("php://input"));
        if (!$postData->task_id || !$postData->task_type) {
            return ['code' => 20081, 'msg' => "系统参数错误"]; 
        }
        $rs = DoTaskHelper::closeTask($this->userInfo, $postData->task_id, $postData->task_type);

        if ($rs) {
            return ['code' => 20000, 'msg' => '关闭成功'];
        }
        return ['code' => 20001, 'msg' => '关闭失败！']; 
    }

    /**
     * 延期任务
     */
    public function actionDelayTask()
    {
        if (!Yii::$app->request->post('task_id')) {
            return ['code' => 20081, 'msg' => "task_id不能为空"]; 
        }
        $proEndTime =  TaskModel::find()->select('oa_project.end_time,oa_project.delay_time,oa_task.end_time as taskEndTime,oa_task.task_type,oa_task.delay_time as delayTime')->leftJoin('oa_project','oa_project.pro_id=oa_task.pro_id')->where(['task_id'=>Yii::$app->request->post('task_id')])->asArray()->one();
        if( $proEndTime['task_type'] == 1){
            if(!empty($proEndTime['delay_time']) && strtotime(Yii::$app->request->post('delay_time')) > $proEndTime['delay_time']){
                return ['code'=>20001,'msg' => '任务结束时间不能大于该项目结束时间，请重新选择！'];
            }
            if (empty($proEndTime['delay_time']) && strtotime(Yii::$app->request->post('delay_time')) > $proEndTime['end_time']){
                return ['code'=>20001,'msg' => '任务结束时间不能大于该项目结束时间，请重新选择！'];
            }
        }
        if(!empty($proEndTime['delay_time']) && (strtotime(Yii::$app->request->post('delay_time')) <= $proEndTime['delayTime'])){
            return ['code'=>20001,'msg' => '任务延期时间必须大于上一次延期时间，请重新选择！'];
        }
        if(strtotime(Yii::$app->request->post('delay_time'))< $proEndTime['taskEndTime']){
            return ['code'=>20001,'msg' => '任务结束时间不能小于该任务的结束时间，请重新选择！'];
        }
        $rs = DoTaskHelper::delayTask($this->userInfo, Yii::$app->request->post('task_id'), Yii::$app->request->post('delay_time'),Yii::$app->request->post('reason'), Yii::$app->request->post('task_type'));

        if ($rs) {
            return ['code' => 20000, 'msg' => '延期成功'];
        }
        return ['code' => 20001, 'msg' => '延期失败！', 'data' => new \stdClass()];
    }

    /**
     * 确认完成
     */
    public function actionDoneTask()
    {
        //获取分数
        $postData = Yii::$app->request->post();
        if (isset($postData['type']) && 1 == $postData['type']) {
            if (!isset($postData['reason']) || $postData['reason'] == '' || strlen($postData['reason']) == 0) {
                return ['code' => 20091, 'msg' => "不通过原因不能为空"]; 
            }
            DoTaskHelper::notPass($this->userInfo, $postData, Yii::$app->request->post('task_id'));
            return ['code' => 20000, 'msg' => '审核成功'];
        }
        if(TaskModel::find()->where(['task_id'=>Yii::$app->request->post('task_id')])->asArray()->one()['status']==4){
            return ['code' => 20001, 'msg' => "该任务已被审核通过！"];
        }
        if (!isset($postData['data']['quality'])) {
            return ['code' => 20091, 'msg' => "质量不能为空"]; 
        }
        if (!isset($postData['data']['speed'])) {
            return ['code' => 20091, 'msg' => "速度不能为空"]; 
        }

        $rs = DoTaskHelper::doneTask($postData['data'], $postData['task_id'], $this->userInfo);
        if ($rs) {
            return ['code' => 20000, 'msg' => '审核通过'];
        }
        return ['code' => 20001, 'msg' => '数据库更新失败']; 
    }

    /**
     * 获取任务技能
     */
    public function actionGetTaskSkill()
    {
        $data = TaskSkillModel::find(['task_id' => Yii::$app->request->post('task_id')])->asArray()->all();
        if ($data) {
            return ['code' => 20000, 'msg' => 'ok', 'data' => $data]; 
        }
        return ['code' => 20002, 'msg' => '查询失败']; 
    }

    /**
     * 发布任务
     */
    public function actionPublish()
    {
        $postData = json_decode(file_get_contents("php://input"));
        if (!$postData->task_id) {
            return ['code' => 20081, 'msg' => "task_id不能为空"]; 
        }
        $rs = DoTaskHelper::publish($postData->task_id, $this->userInfo, Yii::$app->request->post('task_type'));
        if ($rs) {
            return ['code' => 20000, 'msg' => '发布成功'];
        }
        return ['code' => 20001, 'msg' => '发布失败'];
    }

}
