<?php
/**
 * Created by PhpStorm.
 * User: liaoshuochao
 * Date: 2016/7/19
 * Time: 15:14
 */
namespace  app\modules\task\controllers;
use app\controllers\BaseController;
use app\modules\task\helper\RewardTaskHelper;
use yii;

Class RewardtaskController extends BaseController{
    public $modelClass = 'app\models\MembersModel';
    /**
     * 认领悬赏任务
     * @throws \yii\db\Exception
     */
    public function actionClaimTask()
    {
        $postData = Yii::$app->request->post();
        if (!$postData['task_id']) {
            return ['code' => 20071, 'msg' => "task_id不能为空"]; 
        }

        $userInfo = $this->userInfo;
        $rs = RewardTaskHelper::claimTask($userInfo, $postData['task_id']);

        if ($rs) {
            return ['code' => 20000, 'msg' => '申请认领成功，请等待审核！'];
        }
        return ['code' => 20001, 'msg' => '认领失败'];
    }

    /**
     * 确认任务-指派人员
     */
    public function actionPointTask()
    {
        $postData = Yii::$app->request->post();
        if (!$postData['task_id']) {
            return ['code' => 20091, 'msg' => "task_id不能为空"]; 
        }
        if (!isset($postData['point_uid'])) {
            return ['code' => 20092, 'msg' => "请选择指派给谁"]; 
        }
        $rs = RewardTaskHelper::pointTask($postData, $this->userInfo);
        return ['code' => 20000, 'msg' => '确认成功'];
    }
}