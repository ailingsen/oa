<?php
namespace app\modules\v1\controllers;

use app\models\FResponse;
use app\models\WorkStatementModel;
use app\modules\userinfo\helper\UserHelper;
use app\modules\v1\delegate\WorkDelegate;
use app\modules\v1\helper\WorkHelper;
use Yii;
use Yii\base\Object;
use app\models\Mcache;

class WorkController extends BaseController
{
    public $modelClass = 'app\models\WorkStatementModel';
    /**
     * 我的工作报告列表
     * @return array
     */
    public function actionMyWork()
    {
        $this->isPerm('WorkstateMyWorkstate');
        $postData = json_decode(file_get_contents("php://input"));
        if( empty($postData->page) || empty($postData->pageSize) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $page = $postData->page;
        $pageSize = $postData->pageSize;
        $offset = ($page-1)*$pageSize;
        $limit = $pageSize;
        $workList = WorkDelegate::workList($this->userInfo['u_id'], $postData,$offset, $limit);
        $workList['list'] = WorkHelper::setWorkListData($workList['list']);
        FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>$workList]);
    }

    /**
     * 添加工作报告
     * @return array
     */
    public function actionAddWork()
    {
        $postData = json_decode(file_get_contents("php://input"),true);
        if(!(isset($postData['work_id']) && isset($postData['work_data']['work_content']) && isset($postData['work_data']['plan_content'])) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        if(strlen($postData['work_data']['work_content'])<=0) {
            FResponse::output(['code' => 20004, 'msg' => "工作内容不能为空", 'data'=>new Object()]);
        }
        if(strlen($postData['work_data']['plan_content'])<=0 ) {
            FResponse::output(['code' => 20004, 'msg' => "工作计划不能为空", 'data'=>new Object()]);
        }
        $workId = $postData['work_id'];
        $workData = $postData['work_data'];
        $workModel = WorkStatementModel::findOne($workId);
        //权限判断
        $is_addMsg = true;
        if($workModel->status==0){//添加
            $this->isPerm('WorkstateMyWorkstateWrite');
            //获取所有父组的负责人
            $manageUser = \app\modules\work\delegate\WorkDelegate::getMsgUserInfo($this->userInfo['org']['org_id'],$this->userInfo['u_id']);
            //消息
            if(count($manageUser)>0){
                foreach($manageUser as $key=>$val){
                    $manageUser[$key]['menu'] = 1;
                }
                $is_addMsg = \app\modules\work\delegate\WorkDelegate::addWorkMsg($this->userInfo['u_id'],'提交了',$workId,$manageUser,$workModel->type);
            }

        }else if($workModel->status==1){//编辑
            $this->isPerm('WorkstateMyWorkstateEdite');
        }
        $rs = \app\modules\work\delegate\WorkDelegate::updateWorkState($workId, $workData);
        if ($rs && $is_addMsg) {
            FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>new Object()]);
        } else {
            FResponse::output(['code' => 20003, 'msg' => "添加失败", 'data'=>new Object()]);
        }
    }

    /**
     * 详情
     * @return array
     */
    public function actionDetail()
    {

        $postData = json_decode(file_get_contents("php://input"),true);
        //权限
        if(empty($postData['type'])) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $is_my_work = 0;
        //判断是否有查看权限
        if($postData['type'] == 1){//我的工作报告查看详情
            $this->isPerm('WorkstateMyWorkstateDetail');
            $is_my_work = 1;
        }else{//工作报告审阅查看详情
            $this->isPerm('WorkstateApproveView');
            if (!UserHelper::isManager($this->userInfo['u_id'], $this->userInfo['org']['org_id'])) {
                FResponse::output(['code' => 20506, 'msg' => "您无访问此功能权限,请找管理员开通~", 'data'=>new Object()]);
            }
        }

        if(empty($postData['work_id'])) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $detail = WorkDelegate::workDetail($postData['work_id'], $this->userInfo['u_id']);
        $detail['isPerm'] = 0;
        if($is_my_work == 1){//判断是否有编辑或添加工作报告的权限
            if($detail['status']==0 && $this->isPermStatus('WorkstateMyWorkstateWrite')){//添加
                $detail['isPerm'] = 1;
            }else if($detail['status']==1 && $this->isPermStatus('WorkstateMyWorkstateEdite')){//编辑
                $detail['isPerm'] = 1;
            }
        }
        //处理周报的周期
        if($detail['type']==2){
            $detail['cycle'] = WorkHelper::setCycle($detail['cycle']);
        }
        FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>$detail]);
    }

    /**
     * 工作报告审阅列表
     * @return array
     */
    public function actionWorkApprovelist()
    {
        $this->isPerm('WorkstateApprove');
        if (!UserHelper::isManager($this->userInfo['u_id'], $this->userInfo['org']['org_id'])) {
            FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>new Object()]);
        }
        $postData = json_decode(file_get_contents("php://input"),true);
        if( empty($postData['page']) || empty($postData['pageSize']) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $page = $postData['page'];
        $pageSize = $postData['pageSize'];
        $offset = ($page-1)*$pageSize;
        $limit = $pageSize;
        $workList = WorkDelegate::workApproveList($this->userInfo['org']['org_id'], $postData, $offset, $limit, $this->userInfo['u_id']);
        $workList['list'] = WorkHelper::setWorkListData($workList['list']);
        FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>$workList]);
    }

    /**
     * 审阅
     * @return array
     */
    public function actionApproveWork()
    {
        $this->isPerm('WorkstateApproveCheck');
        $postData = json_decode(file_get_contents("php://input"),true);
        if(empty($postData['work_id']) || empty($postData['commit_time'])) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $rs = WorkDelegate::approveWork($this->userInfo['u_id'], $postData['work_id'], $postData['commit_time']);
        if ($rs) {
            FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>new Object()]);
        } else {
            FResponse::output(['code' => 20003, 'msg' => "审阅失败", 'data'=>new Object()]);
        }
    }

}