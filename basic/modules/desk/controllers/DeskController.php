<?php

namespace app\modules\desk\controllers;

use app\controllers\BaseController;
use app\lib\Tools;
use app\models\ApplyMsgModel;
use app\models\ApprovalMsgModel;
use app\models\MeetingMsgModel;
use app\models\MembersModel;
use app\models\ProjectMsgModel;
use app\models\ReportMsgModel;
use app\models\TaskMsgModel;
use app\modules\desk\delegate\DeskDelegate;
use app\modules\notice\delegate\NoticeDelegate;
use app\modules\notice\helper\NoticeHelper;
use app\modules\survey\delegate\SurveyDelegate;
use app\modules\survey\helper\SurveyHelper;
use app\modules\userinfo\helper\UserHelper;
use Yii;

/**
 * Default controller for the `desk` module
 */
class DeskController extends BaseController
{
    public $modelClass = 'app\models\SurveyModel';

    /**
     * 工作台模板列表
     * $page
    */
    public function actionDesktemplet()
    {
        $data = DeskDelegate::getTempletList($this->userInfo['u_id']);
        return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
    }

    public function actionDeskSet()
    {
        $data = DeskDelegate::getTplSet($this->userInfo['u_id']);
        return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
    }
    /**
     * 修改工作台
     * @return array
     */
    public function actionModifyDesk()
    {
        $data = DeskDelegate::editeDeskTemplet($this->userInfo['u_id'], Yii::$app->request->post());
        if($data) {
            return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
        }
        return ['code' => '20001', 'msg' => '操作失败', 'data' => $data];
    }

    /**
     * 修改工作台(大)
     * @return array
     */
    public function actionModifyBig()
    {
        $data = DeskDelegate::editeDeskTemplet($this->userInfo['u_id'], Yii::$app->request->post(), true);
        if($data) {
            return ['code' => '20000', 'msg' => '调整成功', 'data' => $data];
        }
        return ['code' => '20001', 'msg' => '调整失败', 'data' => $data];
    }

    /**
     * 我的任务
     * @return array
     */
    public function actionMyTask()
    {
        $data = DeskDelegate::getMytask($this->userInfo['u_id']);
        return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
    }

    /**
     * 我的工作 
     * @return array
     */
    public function actionMyWork()
    {
        $data = DeskDelegate::getMywork($this->userInfo['u_id']);
        return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
    }

    /**
     * 我的考勤
     * @return array
     */
    public function actionMyAttendance()
    {
        $data = DeskDelegate::getMyAttendance($this->userInfo['u_id']);
        return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
    }

    /**
     * 项目
     * @return array
     */
    public function actionProject()
    {
        $data = DeskDelegate::getProject($this->userInfo['u_id']);
        return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
    }

    /**
     * 公告
     * @return array
     */
    public function actionNotice()
    {
        $data = NoticeDelegate::getNoticeList($this->userInfo['u_id'], 6, 0, []);
        $data = NoticeHelper::setIsNew($data['notList']);
        return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
    }

    /**
     * 我的申请
     * @return array
     */
    public function actionMyApply()
    {
        $data = DeskDelegate::getMyApply($this->userInfo['u_id']);
        return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
    }

    /**
     * 我的审批待办
     * @return array
     */
    public function actionMyApproval()
    {
        $data = DeskDelegate::getMyApproval($this->userInfo['u_id']);
        return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
    }

    /**
     * 用户调研
     * @return array
     */
    public function actionSurvey()
    {
        $data = SurveyDelegate::getSurveyList(1, $this->userInfo['u_id'], 6, 0);
        //设置数据格式
        $data = SurveyHelper::setSurveyData($data['surList']);
        return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
    }

    /**
     * 工作报告审阅
     * @return array
     */
    public function actionWorkstateApprove()
    {
        if (!UserHelper::isManager($this->userInfo['u_id'], $this->userInfo['org']['org_id'])) {
            return ['code' => '20000', 'msg' => '操作成功', 'data' => new \stdClass()];
        }
        $data = DeskDelegate::getWorkstateApprove($this->userInfo['org']['org_id'], $this->userInfo['u_id']);
        return ['code' => '20000', 'msg' => '操作成功', 'data' => $data['list']];
    }

    /**
     * 积分榜
     * @return array
     */
    public function actionScoreBoard()
    {
        if (2 == Yii::$app->request->post('type')) {
            $data = DeskDelegate::scoreBoardweek('week');
        } else {
            $data = DeskDelegate::scoreBoard();
        }

        return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
    }

    /**
     * 获取用户信息
    */
    public function actionUserInfo()
    {
        //获取用户信息
        $memInfo = MembersModel::getMemberInfoDetail($this->userInfo['u_id'], 'oa_org.org_name,oa_members.position,oa_members.phone,oa_members.email,oa_members.points');
        //获取最高技能等级信息
        $temphighSkillInfo = DeskDelegate::getHighSkill($this->userInfo['u_id']);
        $highSkillInfo[] = $temphighSkillInfo;
        $skillInfo = [];
        if(count($highSkillInfo)>0){
            $info = UserHelper::doSkill($highSkillInfo);
            $skillInfo = $info[0];
        }else{
            $skillInfo = 0;
        }
        return ['code'=>1,'data'=>['memInfo'=>$memInfo,'skillInfo'=>$skillInfo]];
    }

    //消息列表
    public function actionMsgList() {


        $list = [];
        $uid = $this->userInfo['u_id'];
        $temp = ApprovalMsgModel::getNewMsg($uid,['real_name','title','create_time']);
        if($temp['create_time']){
            $temp['is_new'] = 0;
            if($temp['create_time_com'] == date('Y年m月d日')){
                $temp['is_new'] = 1;
            }
            $temp['type'] = 1;
            $list[] =$temp;
        }
        $temp = [];
        $temp = ApplyMsgModel::getNewMsg($uid,['real_name','title','oa_apply_msg.status','create_time']);
        if($temp['create_time']){
            $temp['is_new'] = 0;
            if($temp['create_time_com'] == date('Y年m月d日')){
                $temp['is_new'] = 1;
            }
            $temp['type'] = 2;
            $list[] =$temp;
        }
        $temp = [];
        $temp = MeetingMsgModel::getNewMsg($uid,['real_name','title','meeting_name','create_time']);
        if($temp['create_time']){
            $temp['is_new'] = 0;
            if($temp['create_time_com'] == date('Y年m月d日')){
                $temp['is_new'] = 1;
            }
            $temp['type'] = 3;
            $list[] =$temp;
        }
        $temp = [];
        $temp = TaskMsgModel::getNewMsg($uid,['real_name','title','task_title','create_time']);
        if($temp['create_time']){
            $temp['is_new'] = 0;
            if($temp['create_time_com'] == date('Y年m月d日')){
                $temp['is_new'] = 1;
            }
            $temp['type'] = 4;
            $list[] =$temp;
        }
        $temp = [];
        $temp = ProjectMsgModel::getNewMsg($uid,['real_name','title','project_name','create_time']);
        if($temp['create_time']){
            $temp['is_new'] = 0;
            if($temp['create_time_com'] == date('Y年m月d日')){
                $temp['is_new'] = 1;
            }
            $temp['type'] = 5;
            $list[] =$temp;
        }
        $temp = [];
        $temp = ReportMsgModel::getNewMsg($uid,['real_name','title','work_title','create_time']);
        if($temp['create_time']){
            $temp['is_new'] = 0;
            if($temp['create_time_com'] == date('Y年m月d日')){
                $temp['is_new'] = 1;
            }
            $temp['type'] = 6;
            $list[] =$temp;
        }

        return ['code'=>1,'data'=>$list];
    }

    public function actionMsgListInfo()
    {
        $data = json_decode(file_get_contents("php://input"),true);
        $page = isset($data['page']) ? $data['page'] : 1;
        $offset = 10 * ($page - 1);
        $limit =10;
        switch($data['type'])
        {
            case 1:
                $data = DeskDelegate::approvalMsg($this->userInfo['u_id'],$limit,$offset);
                //设置已读
                ApprovalMsgModel::setRead($this->userInfo['u_id']);
                break;
            case 2:
                $data = DeskDelegate::applyMsg($this->userInfo['u_id'],$limit,$offset);
                ApplyMsgModel::setRead($this->userInfo['u_id']);
                break;
            case 3:
                $data = DeskDelegate::meetingMsg($this->userInfo['u_id'],$limit,$offset);
                MeetingMsgModel::setRead($this->userInfo['u_id']);
                break;
            case 4:
                $data = DeskDelegate::taskMsg($this->userInfo['u_id'],$limit,$offset);
                TaskMsgModel::setRead($this->userInfo['u_id']);
                break;
            case 5:
                $data = DeskDelegate::projectMsg($this->userInfo['u_id'],$limit,$offset);
                ProjectMsgModel::setRead($this->userInfo['u_id']);
                break;
            case 6:
                $data = DeskDelegate::reportMsg($this->userInfo['u_id'],$limit,$offset);
                ReportMsgModel::setRead($this->userInfo['u_id']);
                break;
        }
        $data['page']['curPage'] = $page;
        return ['code'=>1,'data'=>$data];
    }

}
