<?php
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/11/7
 * Time: 10:17
 */

namespace app\modules\v1\controllers;

use Yii;
use app\models\TaskLogModel;
use yii\web\Controller;
use app\models\TaskModel;
use app\modules\task\delegate\TaskListDelegate;
use app\models\RewardTaskModel;
use app\models\TaskMsgModel;
use app\models\TaskMemberModel;
use app\lib\FResponse;

class TaskListController extends BaseController
{
    public $modelClass = 'app\models\RewardTaskModel';
    /**
    *我接受的任务列表
    */
    public function actionMyTaskList()
    {
        $this->isPerm('TaskMytask');
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata,true);
        $proId = !empty($request['proId']) ? $request['proId']:'';
        $taskType = !empty($request['taskType']) ? $request['taskType']:'';
        $status = $request['status'];
        $beginTime = !empty($request['beginTime'])?$request['beginTime']:'';
        $endTime = !empty($request['endTime']) ? $request['endTime']:'';
        $taskTitle = $request['taskTitle'];
        $num = !empty($request['num'])?$request['num']:'10';
        $current = !empty($request['current'])? $request['current']:'0';
        $overtime = !empty($request['overtime']) ? $request['overtime']:'';
        $uid = $this->userInfo['u_id'];
        $taskListData = TaskListDelegate::taskDelegate($uid, $proId, $taskType, $status, $beginTime, $endTime, $taskTitle, $overtime,$num, $current, $this->isPermStatus('TaskCreate'));
    }


     /**
     * 我发布的任务列表
     */
    public function actionMyReleaseList()
    {
        $this->isPerm('TaskMyrelease');
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata,true);
        $proId = !empty($request['proId']) ? $request['proId']:'';
        $taskType = !empty($request['taskType']) ? $request['taskType']:'';
        $status = $request['status'];
        $beginTime = !empty($request['beginTime'])?$request['beginTime']:'';
        $endTime = !empty($request['endTime']) ? $request['endTime']:'';
        $taskTitle = $request['taskTitle'];
        $num = !empty($request['num'])?$request['num']:'10';
        $current = !empty($request['current'])? $request['current']:'0';
        $overtime = !empty($request['overtime']) ? $request['overtime']:'';
        $uid = $this->userInfo['u_id'];
        $taskListData = TaskListDelegate::taskDelegate($uid, $proId, $taskType, $status, $beginTime, $endTime, $taskTitle, $overtime,$num, $current,$this->isPermStatus('TaskCreate'));
    }
    /**
     * 悬赏池列表信息
     */
    public function actionRewardList()
    {
        $this->isPerm('TaskReward');
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata,true);
        $status = !empty($request['status']) ? $request['status'] : '';
        $taskTitle = $request['taskTitle'];
        $orgId = $request['orgId'];
        $num = !empty($request['num']) ? $request['num']:'10';
        $current = !empty($request['current'])? $request['current']:'0';
        $rewardListData = RewardTaskModel::getRewardData($status, $taskTitle, $num, $current, $orgId);
        FResponse::output(['code'=>20000, 'msg'=> 'ok', 'data' => $rewardListData]);
    }

    /**
     *我的悬赏任务列表
     */
    public function actionMyReward()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata,true);
        $uid = $this->userInfo['u_id'];
        $status = !empty($request['status']) ? $request['status'] : '';
        $taskTitle = $request['taskTitle'];
        $pageSize = !empty($request['pageSize']) ? $request['pageSize'] : '10';
        $curPage = !empty($request['curPage']) ? $request['curPage'] : '0';
        $myRewardData = RewardTaskModel::getMyRewardData($uid, $taskTitle, $status, $pageSize, $curPage);
        FResponse::output(['code'=>20000, 'msg'=> 'ok', 'data' => $myRewardData]);
    }

    /**
     *我认领的记录
     */
    public function actionApplicationRecord()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata,true);
        $curPage = !empty($request['curPage']) ? $request['curPage'] : '0';
        $pageSize = !empty($request['pageSize']) ? $request['pageSize'] : '10';
        $uid = $this->userInfo['u_id'];
        FResponse::output(['code'=>20000, 'msg'=> 'ok', 'data' => TaskMemberModel::getRewardApplicationRecord($uid, $pageSize, $curPage)]);
    }

     /**
     *任务详情
     */
    public function actionTaskDetails()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata,true);
        $taskId = !empty($request['taskId']) ? $request['taskId'] : '';
        $res = TaskListDelegate::getRewardListInfoData($taskId, $this->userInfo,1);
        $res['data']['headImg'] = substr($this->apiDomain, 0, -1).$res['data']['headImg'];
        if(!empty($res['data']['applicant'])){
            foreach ($res['data']['applicant'] as $k => $v){
                $res['data']['applicant'][$k]['headImg'] = substr($this->apiDomain, 0, -1).$v['headImg'];
            }
        }
        if(!empty($res['data']['workNoteFiles'])){
            foreach ($res['data']['workNoteFiles'] as $ke => $va){
                    $res['data']['workNoteFiles'][$ke]['file_path'] = Yii::getAlias('@file_root').'/'.$va['file_path'].'/'.$va['file_name'];
                }
            }
        if(!empty($res['data']['attachmentInfo'])){
            foreach ($res['data']['attachmentInfo'] as $key => $val){
                $res['data']['attachmentInfo'][$key]['file_path'] = Yii::getAlias('@file_root').'/'.$val['file_path'].'/'.$val['file_name'];
            }
        }
        $res['data']['temp_task_type']=1;
        return FResponse::output($res);
    }
    /*
    *我发布的任务详情
    */
    public function actionMyTaskDetails()
        {
            $postdata = file_get_contents("php://input");
            $request = json_decode($postdata,true);
            $taskId = !empty($request['taskId']) ? $request['taskId'] : '';
            $res = TaskListDelegate::getRewardListInfoData($taskId, $this->userInfo,2);
            $res['data']['headImg'] = substr($this->apiDomain, 0, -1).$res['data']['headImg'];
            if(!empty($res['data']['applicant'])){
                foreach ($res['data']['applicant'] as $k => $v){
                    $res['data']['applicant'][$k]['headImg'] = substr($this->apiDomain, 0, -1).$v['headImg'];
                }
            }
            if(!empty($res['data']['workNoteFiles'])){
                foreach ($res['data']['workNoteFiles'] as $ke => $va){
                        $res['data']['workNoteFiles'][$ke]['file_path'] = Yii::getAlias('@file_root').'/'.$va['file_path'].'/'.$va['file_name'];
                    }
                }
            if(!empty($res['data']['attachmentInfo'])){
                foreach ($res['data']['attachmentInfo'] as $key => $val){
                    $res['data']['attachmentInfo'][$key]['file_path'] = Yii::getAlias('@file_root').'/'.$val['file_path'].'/'.$val['file_name'];
                }
            }
            $res['data']['temp_task_type']=1;
            return FResponse::output($res);
        }
    /*
     * 悬赏池列表详细信息
     */
    public function actionRewardListDetails()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata,true);
        $taskId = !empty($request['taskId']) ? $request['taskId'] : '';
        $res = TaskListDelegate::getRewardListInfoData($taskId, $this->userInfo);
        $res['data']['headImg'] = substr($this->apiDomain, 0, -1).$res['data']['headImg'];
        if(!empty($res['data']['applicant'])){
            foreach ($res['data']['applicant'] as $k => $v){
                $res['data']['applicant'][$k]['headImg'] = substr($this->apiDomain, 0, -1).$v['headImg'];
            }
        }
        if(!empty($res['data']['workNoteFiles'])){
                foreach ($res['data']['workNoteFiles'] as $ke => $va){
                    $res['data']['workNoteFiles'][$ke]['file_path'] = Yii::getAlias('@file_root').'/'.$va['file_path'].'/'.$va['file_name'];
                }
        }
        foreach ($res['data']['attachmentInfo'] as $key => $val){
            $res['data']['attachmentInfo'][$key]['file_path'] = Yii::getAlias('@file_root').'/'.$val['file_path'].'/'.$val['file_name'];
        }
        $res['data']['temp_task_type']=2;
        FResponse::output($res);
    }
    /**
     * 任务操作日志
     */
    public function actionGetOperationTask()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata,true);
        $taskType = $request['taskType'];
        $taskId = $request['taskId'];
        $pageSize = !empty($request['pageSize']) ? $request['pageSize'] : 10;
        $curPage =  !empty($request['curPage']) ? $request['curPage'] : 0;
        $res = TaskLogModel::getTaskOperationLog($taskId, $taskType, $pageSize, $curPage);
        foreach ($res['operationLogData'] as $key => $val){
            $res['operationLogData'][$key]['head_img'] = substr($this->apiDomain, 0, -1).$val['head_img'];
        }
        FResponse::output(['code'=>20000, 'msg'=> 'ok', 'data' => $res]);
    }
}