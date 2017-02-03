<?php
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/11/8
 * Time: 17:16
 */

namespace app\modules\v1\controllers;

use app\lib\FResponse;
use app\lib\Tools;
use app\modules\task\helper\DoTaskHelper;
use Yii;
use yii\db\Exception;
use app\models\TaskModel;
use app\modules\management\delegate\SkillDelegate;
use app\modules\task\delegate\TaskDelegate;
use app\models\TaskSkillModel;

class DoTaskController extends BaseController
{
    public $modelClass = 'app\models\MembersModel';

    /**
     * 接受任务
     * @param $taskId
     * @return array
     */
    public function actionAcceptTask()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata,true);
        $taskInfo = DoTaskHelper::isTaskExist($request['taskId'], 1);
        if ($taskInfo->status != 1) {
            FResponse::output(['code' => 20001, 'msg' => '该任务已经被接受']);
        }
        if($taskInfo->charger != $this->userInfo['u_id']){
            FResponse::output(['code' => 20001, 'msg' => '失败，该任务已指派给他人']);
        }
        $rs = DoTaskHelper::acceptTask($taskInfo, $this->userInfo);

        if ($rs) {
            TaskDelegate::taskMsg($taskInfo->creater,$this->userInfo['u_id'],$request['taskId'],'接受了你分配的任务',$taskInfo->task_title,2,1);
            Tools::msgJpush(3,$request['taskId'],$this->userInfo['real_name'].'接受了你分配的任务'.$taskInfo->task_title,[$taskInfo->creater],['taskType'=>2]);
            FResponse::output(['code' => 20000, 'msg' => '接受成功']);
        }
        FResponse::output(['code' => 20001, 'msg' => '数据库操作失败']);
    }

    /**
     * 提交审核
     * @throws Exception
     */
    public function actionCommitTask()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata,true);
        $taskId = $request['taskId'];
        $workNote = $request['workNote'];
        $rs = DoTaskHelper::commitTask($taskId, $workNote , $this->userInfo);

        if ($rs) {
            FResponse::output(['code' => 20000, 'msg' => '提交任务成功，请等待审核']) ;
        }
        FResponse::output(['code' => 20001, 'msg' => '提交任务失败']);
    }

    /**
     * 待接收拒绝任务
     * @throws Exception
     */
    public function actionRefuseTask()
    {
        $postData = json_decode(file_get_contents("php://input"));
        if (!$postData->taskId) {
            FResponse::output(['code' => 20001, 'msg' => "系统参数错误"]);
        }
        //获取拒绝理由
        if (!$postData->reason) {
            FResponse::output(['code' => 20001, 'msg' => "请填写拒绝理由!"]);
        }
        $taskInfo = DoTaskHelper::isTaskExist($postData->taskId, 1);
        if($taskInfo->charger != $this->userInfo['u_id']){
            FResponse::output(['code' => 20001, 'msg' => "失败，该任务已指派给他人!"]) ;
        }
        $rs = DoTaskHelper::refuseTask($this->userInfo, $postData->reason, $postData->taskId);

        if ($rs) {
            FResponse::output(['code' => 20000, 'msg' => "操作成功!"]);
        }
        FResponse::output(['code' => 20001, 'msg' => '操作失败']);
    }

    /**
     * 确认完成
     */
    public function actionDoneTask()
    {
        $postData = json_decode(file_get_contents("php://input"),true);
        //获取分数
        if (isset($postData['type']) && 1 == $postData['type']) {
            if (!isset($postData['reason']) || $postData['reason'] == '' || strlen($postData['reason']) == 0) {
                FResponse::output(['code' => 20001, 'msg' => "不通过原因不能为空"]);
            }
            DoTaskHelper::notPass($this->userInfo, $postData, $postData['taskId']);
            FResponse::output(['code' => 20000, 'msg' => '审批成功']);
        }
        if(TaskModel::find()->where(['task_id'=>$postData['taskId']])->asArray()->one()['status']==4){
            FResponse::output(['code' => 20001, 'msg' => "该任务已被审核通过！"]);
        }
        if (!isset($postData['data']['quality'])) {
            FResponse::output(['code' => 20001, 'msg' => "质量不能为空"]);
        }
        if (!isset($postData['data']['speed'])) {
            FResponse::output(['code' => 20001, 'msg' => "速度不能为空"]);
        }
        
        $rs = DoTaskHelper::doneTask($postData['data'], $postData['taskId'], $this->userInfo);
        if ($rs) {
            FResponse::output(['code' => 20000, 'msg' => '审核通过']);
        }
        FResponse::output(['code' => 20001, 'msg' => '数据库更新失败']);
    }

    /**
     * 关闭任务
     */
    public function actionCloseTask()
    {
        $postData = json_decode(file_get_contents("php://input"));
        if (!$postData->taskId || !$postData->taskType) {
            FResponse::output(['code' => 20001, 'msg' => "系统参数错误!"]);
        }
        $rs = DoTaskHelper::closeTask($this->userInfo, $postData->taskId, $postData->taskType);

        if ($rs) {
            FResponse::output(['code' => 20000, 'msg' => "关闭成功!"]);
        }
        FResponse::output(['code' => 20001, 'msg' => '关闭失败！']);
    }

    /**
     * 延期任务
     */
    public function actionDelayTask()
    {
        $postData = json_decode(file_get_contents("php://input"),true);
        if (!$postData['taskId']) {
            return ['code' => 20081, 'msg' => "task_id不能为空"];
        }
        $proEndTime =  TaskModel::find()->select('oa_project.end_time,oa_project.delay_time,oa_task.end_time as taskEndTime,oa_task.task_type,oa_task.delay_time as delayTime')->leftJoin('oa_project','oa_project.pro_id=oa_task.pro_id')->where(['task_id'=> $postData['taskId']])->asArray()->one();
        if($proEndTime['task_type']==1){
            if(!empty($proEndTime['delay_time']) && strtotime($postData['delayTime']) > $proEndTime['delay_time']){
                FResponse::output(['code'=>20001,'msg' => '任务结束时间不能大于该项目结束时间，请重新选择！']);
            }
            if (empty($proEndTime['delay_time']) && strtotime($postData['delayTime']) > $proEndTime['end_time']){
                FResponse::output(['code'=>20001,'msg' => '任务结束时间不能大于该项目结束时间，请重新选择！']);
            }  
        }
        if(!empty($proEndTime['delay_time']) && (strtotime($postData['delayTime']) <= $proEndTime['delayTime'])){
            FResponse::output(['code'=>20001,'msg' => '任务延期时间必须大于上一次延期时间，请重新选择！']);
        }
        if(strtotime($postData['delayTime'])< $proEndTime['taskEndTime']){
            FResponse::output(['code'=>20001,'msg' => '任务延期结束时间不能小于该任务的结束时间，请重新选择！']);
        }
        $rs = DoTaskHelper::delayTask($this->userInfo, $postData['taskId'], $postData['delayTime'],$postData['reason'], $postData['taskType']);
        if ($rs) {
            FResponse::output(['code' => 20000, 'msg' => '延期成功']);
        }
        FResponse::output(['code' => 20001, 'msg' => '延期失败！']);
    }

    /**
     * 获取技能列表（权限）
     */
    public function actionSkilllist()
    {
        $postData = json_decode(file_get_contents("php://input"),true);
        $skillName = !empty($postData['skillName']) ? $postData['skillName'] : "";
        $skillData = SkillDelegate::bianli(SkillDelegate::getSkillList($skillName));
        FResponse::output(['code' => 20000, 'msg' => 'ok', 'data' => $skillData]);
    }

    /**
     * 删除任务
     * @throws Exception
     */
    public function actionDeleteTask()
    {
        $postData = json_decode(file_get_contents("php://input"),true);
        if (!$postData['taskId']) {
            FResponse::output(['code' => 20001, 'msg' => "系统参数错误"]);
        }
        $rs = DoTaskHelper::deleteTask($this->userInfo, $postData['taskId'], $postData['taskType']);

        if ($rs) {
            FResponse::output(['code' => 20000, 'msg' => '删除成功']);
        }
        FResponse::output(['code' => 20001, 'msg' => '删除失败！']);
    }

    /**
     * 发布任务
     */
    public function actionPublish()
    {
        $postData = json_decode(file_get_contents("php://input"));
        if (!$postData->taskId) {
            FResponse::output(['code' => 20001, 'msg' => 'taskId不能为空']);
        }
        $rs = DoTaskHelper::publish($postData->taskId, $this->userInfo, $postData->taskType);
        if ($rs) {
            FResponse::output(['code' => 20000, 'msg' => '发布成功']);
        }
        FResponse::output(['code' => 20001, 'msg' => '发布失败']);
    }
}