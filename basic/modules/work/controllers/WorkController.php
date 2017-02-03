<?php

namespace app\modules\work\controllers;

use app\controllers\BaseController;

use app\lib\errors\ErrorCode;
use app\models\MembersModel;
use app\models\OrgModel;
use app\models\WorkStatementModel;
use app\modules\userinfo\helper\UserHelper;
use app\modules\work\delegate\WorkDelegate;
use app\modules\work\helper\WorkHelper;
use Yii;
/**
 * Default controller for the `work` module
 */
class WorkController extends BaseController
{
    public $modelClass = 'app\models\WorkItemModel';

    /**
     * @return array
     */
    public function actionAddWork()
    {
        if (!$workId = Yii::$app->request->post('work_id')) {
            return ['code' => 20002, 'msg' => 'work_id不能为空'];
        }
        $workData = Yii::$app->request->post('work_data');
        $workData['commit_time'] = time();
        $rs = WorkDelegate::updateWorkState($workId, $workData);
        //获取所有父组的负责人
        $manageUser = WorkDelegate::getMsgUserInfo($this->userInfo['org']['org_id'],$this->userInfo['u_id']);
        //消息
        $is_addMsg = true;
        if(count($manageUser)>0){
            foreach($manageUser as $key=>$val){
                $manageUser[$key]['menu'] = 1;
            }
            $workModel = WorkStatementModel::findOne($workId);
            $is_addMsg = WorkDelegate::addWorkMsg($this->userInfo['u_id'],'提交了',$workId,$manageUser,$workModel->type);
        }
        if ($rs && $is_addMsg) {
            return ['code' => 20000, 'msg' => '提交成功'];
        } else {
            return ['code' => 20003, 'msg' => '提交失败'];
        }
    }

    /**
     * 更新报告
     * @return array
     */
    public function actionUpdateWork()
    {
        if (!$workId = Yii::$app->request->post('work_id')) {
            return ['code' => 20002, 'msg' => 'work_id不能为空'];
        }
        $rs = WorkDelegate::updateWorkState($workId, Yii::$app->request->post('work_data'));
        if ($rs) {
            return ['code' => 20000, 'msg' => '修改成功'];
        } else {
            return ['code' => 20003, 'msg' => '修改失败'];
        }
    }

    /**
     * 我的工作报告列表
     * @return array
     */
    public function actionMyWork()
    {
        $workList = WorkDelegate::workList($this->userInfo['u_id'], Yii::$app->request->post(), Yii::$app->request->post('page', 1), Yii::$app->request->post('page_size', 10));
        return ['code' => 20000, 'msg' => 'ok', 'data' => $workList];
    }

    /**
     * 我的审阅工作报告列表
     * @return array
     */
    public function actionWorkApprovelist()
    {
        if (!UserHelper::isManager($this->userInfo['u_id'], $this->userInfo['org']['org_id'])) {
            return ['code' => ErrorCode::E_NOT_POWERED, 'msg' => '没有权限', 'data' => ['list' => [], 'page' => 1, 'total_page' => 0]];
        }
        $workList = WorkDelegate::workApproveList($this->userInfo['org']['org_id'], Yii::$app->request->post(), Yii::$app->request->post('page', 1), Yii::$app->request->post('page_size', 10),$this->userInfo['u_id']);
        return ['code' => 20000, 'msg' => 'ok', 'data' => $workList];
    }

    /**
     * 审阅
     * @return array
     */
    public function actionApproveWork()
    {
        $rs = WorkDelegate::approveWork($this->userInfo['u_id'], Yii::$app->request->post('work_id'), Yii::$app->request->post('commit_time'));
        if ($rs) {
            return ['code' => 20000, 'msg' => '审阅成功'];
        } else {
            return ['code' => 20001, 'msg' => '审阅失败'];
        }
    }

    /**
     * 详情
     * @return array
     */
    public function actionDetail()
    {
        $detail = WorkDelegate::workDetail(Yii::$app->request->post('work_id'), $this->userInfo['u_id']);
        $detail['author'] = $detail['real_name'];
        return ['code' => 20000, 'msg' => 'ok', 'data' => $detail];
    }

    /**
     * 获取搜索时用的用户信息信息
     * $search_real_name
     * @return array
     */
    public function actionMemberList()
    {
        $postData = file_get_contents("php://input");
        $postData = json_decode($postData, true);
        $keyword = isset($postData['search_real_name']) ? $postData['search_real_name'] : '';
        $memList = WorkDelegate::getMemberList($this->userInfo['u_id'], $keyword, $this->userInfo['org']['org_id']);
        return ['code' => 20000, 'data' => $memList];
    }
}
