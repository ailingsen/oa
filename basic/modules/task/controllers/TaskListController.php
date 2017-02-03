<?php
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/7/15
 * Time: 10:33
 */

namespace app\modules\task\controllers;

use app\models\TaskLogModel;
use yii;
use yii\web\Controller;
use app\controllers\BaseController;
use app\models\TaskModel;
use app\modules\task\delegate\TaskListDelegate;
use app\models\RewardTaskModel;
use app\models\TaskMemberModel;
use app\lib\FResponse;


class TaskListController extends BaseController
{
    public $modelClass = 'app\models\RewardTaskModel';
    /**
     * 我接受的任务列表
     */
    public function actionMyTaskList()
    {
        $proId = !empty(Yii::$app->request->post('proId')) ? Yii::$app->request->post('proId'):'';
        $taskType = !empty(Yii::$app->request->post('taskType')) ? Yii::$app->request->post('taskType'):'';
        $status = Yii::$app->request->post('status', '');
        $beginTime = !empty(Yii::$app->request->post('beginTime'))?Yii::$app->request->post('beginTime'):'';
        $endTime = !empty(Yii::$app->request->post('endTime'))?Yii::$app->request->post('endTime'):'';
        $taskTitle = Yii::$app->request->post('taskTitle');
        $num = !empty(Yii::$app->request->post('num'))?Yii::$app->request->post('num'):'10';
        $current = !empty(Yii::$app->request->post('current'))? Yii::$app->request->post('current'):'0';
        $overtime = !empty(Yii::$app->request->post('overtime')) ? Yii::$app->request->post('overtime'):'';
        $uid = $this->userInfo['u_id'];
        TaskListDelegate::taskDelegate($uid, $proId, $taskType, $status, $beginTime, $endTime, $taskTitle, $overtime,$num, $current);

    }
    /*
     * 我发布的任务列表
     */
    public function actionMyReleaseList()
    {
        $proId = !empty(Yii::$app->request->post('proId')) ? Yii::$app->request->post('proId'):'';
        $taskType = !empty(Yii::$app->request->post('taskType')) ? Yii::$app->request->post('taskType'):'';
        $status = Yii::$app->request->post('status', '');
        $beginTime = !empty(Yii::$app->request->post('beginTime'))?Yii::$app->request->post('beginTime'):'';
        $endTime = !empty(Yii::$app->request->post('endTime'))?Yii::$app->request->post('endTime'):'';
        $taskTitle = Yii::$app->request->post('taskTitle');
        $num = !empty(Yii::$app->request->post('num'))?Yii::$app->request->post('num'):'10';
        $current = !empty(Yii::$app->request->post('current'))? Yii::$app->request->post('current'):'1';
        $overtime = !empty(Yii::$app->request->post('overtime')) ? Yii::$app->request->post('overtime'):'';
        $uid = $this->userInfo['u_id'];
        TaskListDelegate::taskDelegate($uid, $proId, $taskType, $status, $beginTime, $endTime, $taskTitle, $overtime,$num, $current);
    }
    /*
     *任务详情
     */
    public function actionTaskDetails()
    {
        $taskId = !empty(Yii::$app->request->post('taskId')) ? Yii::$app->request->post('taskId') : '';
        $type = Yii::$app->request->post('type');
        $res = TaskListDelegate::getRewardListInfoData($taskId, $this->userInfo,$type);
        $res['temp_task_type']=1;
        return FResponse::output($res);
    }

    /*
     * 悬赏池列表信息
     */
    public function actionRewardList()               
    {
        $status = !empty(Yii::$app->request->post('status')) ? Yii::$app->request->post('status') : '';
        $taskTitle = Yii::$app->request->post('taskTitle');
        $orgId = Yii::$app->request->post('orgId');
        $num = !empty(Yii::$app->request->post('num'))?Yii::$app->request->post('num'):'10';
        $current = !empty(Yii::$app->request->post('current'))? Yii::$app->request->post('current'):'0';
        $rewardListData = RewardTaskModel::getRewardData($status, $taskTitle, $num, $current, $orgId);
        FResponse::output(['code'=>20000, 'msg'=> 'ok', 'data' => $rewardListData]);
    }
    /**
     *我的悬赏任务列表
     */
    public function actionMyReward()
    {
        $uid = $this->userInfo['u_id'];
        $status = !empty(Yii::$app->request->post('status')) ? Yii::$app->request->post('status') : '';
        $taskTitle = Yii::$app->request->post('taskTitle');
        $pageSize = 10;
        $curPage = !empty(Yii::$app->request->post('curPage')) ? Yii::$app->request->post('curPage') : '0';
        $myRewardData = RewardTaskModel::getMyRewardData($uid, $taskTitle, $status, $pageSize, $curPage);
        FResponse::output(['code'=>20000, 'msg'=> 'ok', 'data' => $myRewardData]);
    }
    /*
     * 悬赏池列表详细信息
     */
    public function actionRewardListDetails()
    {
        $taskId = !empty(Yii::$app->request->post('taskId')) ? Yii::$app->request->post('taskId') : '';
        $type = Yii::$app->request->post('type');
        $res = TaskListDelegate::getRewardListInfoData($taskId, $this->userInfo,$type);
        $res['temp_task_type']=2;
        return FResponse::output($res);

    }

    /**
     *我认领的记录
     */
    public function actionApplicationRecord()
    {
        $curPage = !empty(Yii::$app->request->post('curPage')) ? Yii::$app->request->post('curPage') : '0';
        $pageSize = !empty(Yii::$app->request->post('pageSize')) ? Yii::$app->request->post('pageSize') : '10';
        $uid = $this->userInfo['u_id'];
        FResponse::output(['code'=>20000, 'msg'=> 'ok', 'data' => TaskMemberModel::getRewardApplicationRecord($uid, $pageSize, $curPage)]);
    }

    /**
     * 任务操作日志
     */
    public function actionGetOperationTask()
    {
        $taskId = Yii::$app->request->post('taskId');
        $pageSize = 1000000;
        $curPage = 0;
        $data = TaskLogModel::getTaskOperationLog($taskId, Yii::$app->request->post('taskType'), $pageSize, $curPage);
        FResponse::output(['code'=>20000, 'msg'=> 'ok', 'data' => $data['operationLogData']]);
    }

}