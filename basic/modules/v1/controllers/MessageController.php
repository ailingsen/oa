<?php
/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2016/11/10
 * Time: 15:23
 */

namespace app\modules\v1\controllers;


use app\lib\Tools;
use app\models\ApplyBaseModel;
use app\models\WorkSetModel;
use app\models\ApplyMsgModel;
use app\models\ApprovalMsgModel;
use app\models\MeetingMsgModel;
use app\models\NoticeModel;
use app\models\ReserveDetailModel;
use app\models\ProjectMsgModel;
use app\models\ReportMsgModel;
use app\models\ReserveRoomModel;
use app\models\SurveyModel;
use app\models\TaskModel;
use app\models\TaskMsgModel;
use app\models\WorkStatementModel;
use app\modules\v1\delegate\MessageDelegate;
use app\modules\v1\delegate\NoticeDelegate;
use app\lib\FResponse;
use app\modules\v1\delegate\ProjectDelegate;
use app\modules\v1\delegate\AttendanceDelegate;
use app\modules\boardroom\delegate\BoardroomDelegate;
use app\modules\v1\helper\ProjectHelper;
use yii\base\Object;

class MessageController extends BaseController
{
    //消息列表
    public function actionMsgList() {
        $list = [];
        $uid = $this->userInfo['u_id'];
        $list['apply']['info'] = ApplyMsgModel::getNewMsg($uid,['real_name','title','apply_title','oa_apply_msg.status','create_time','newest']);
        $list['apply']['num'] = MessageDelegate::getUnreadApplyMsgNum($uid);
        $list['approval']['info'] = ApprovalMsgModel::getNewMsg($uid,['real_name','title','apply_title','create_time','newest']);
        $list['approval']['num'] = MessageDelegate::getUnreadApprovalMsgNum($uid);
        $list['task']['info'] = TaskMsgModel::getNewMsg($uid,['real_name','title','task_title','create_time','newest']);
        $list['task']['num'] = MessageDelegate::getUnreadTaskMsgNum($uid);
        $list['meeting']['info'] = MeetingMsgModel::getNewMsg($uid,['real_name','title','meeting_name','create_time','newest']);
        $list['meeting']['num'] = MessageDelegate::getUnreadMeetingMsgNum($uid);
        $list['report']['info'] = ReportMsgModel::getNewMsg($uid,['real_name','title','create_time','work_title','newest']);
        $list['report']['num'] = MessageDelegate::getUnreadReportMsgNum($uid);
        $list['project']['info'] = ProjectMsgModel::getNewMsg($uid,['real_name','title','project_name','create_time','newest']);
        $list['project']['num'] = MessageDelegate::getUnreadProjectMsgNum($uid);
        $list['notice']['info'] = NoticeDelegate::getNewNoticeDetail();
        $list['notice']['num'] = NoticeDelegate::getUnReadNoticeCount($uid);

        FResponse::output(['code'=>20000, 'msg'=> 'ok', 'data'=> $list]);
    }

    //申请消息列表
    public function actionApplyMsg() {
        $data = json_decode(file_get_contents("php://input"));
        if( empty($data->page) || empty($data->pageSize) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $uid = $this->userInfo['u_id'];
        $page = $data->page;
        $pageSize = $data->pageSize;
        $offset = ($page-1)*$pageSize;
        $limit = $pageSize;
        $query = ApplyMsgModel::find()->leftJoin('oa_members','oa_apply_msg.handler=oa_members.u_id')
            ->leftJoin('oa_apply_base','oa_apply_msg.apply_id=oa_apply_base.apply_id')
            ->leftJoin('oa_apply_model','oa_apply_base.model_id=oa_apply_model.model_id')
            ->select(['oa_apply_msg.apply_id','oa_apply_msg.title','oa_apply_msg.apply_title','oa_apply_msg.status','oa_apply_msg.create_time','real_name','head_img','oa_apply_model.model_id','oa_apply_model.modeltype'])
            ->where(['oa_apply_msg.uid' => $uid])->orderBy('oa_apply_msg.create_time DESC');
        $list = $query->limit($limit)->offset($offset)->asArray()->all();
        $totalPage = ceil($query->count() / $limit);
        foreach($list as $key => $value) {
            $list[$key]['head_img'] = $this->getUserHeadimg($value['head_img']);
            $list[$key]['create_time'] = date('Y-n-j H:i',$value['create_time']);
            if($value['status'] == 1) {
                $list[$key]['status'] = '同意';
            }else if($value['status'] == 2) {
                $list[$key]['status'] = '已拒绝';
            }else if($value['status'] == 3) {
                $list[$key]['status'] = '已审批';
            }
        }
        //我的申请消息置为已读
        ApplyMsgModel::setRead($uid);

        FResponse::output(['code' => 20000, 'msg' => "ok", 'data'=>['list' => $list,'totalPage' => $totalPage]]);
    }

    //审批消息列表
    public function actionApprovalMsg() {
        $data = json_decode(file_get_contents("php://input"));
        if( empty($data->page) || empty($data->pageSize) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $uid = $this->userInfo['u_id'];
        $page = $data->page;
        $pageSize = $data->pageSize;
        $offset = ($page-1)*$pageSize;
        $limit = $pageSize;
        $query = ApprovalMsgModel::find()->leftJoin('oa_members','oa_approval_msg.applyer=oa_members.u_id')
            ->leftJoin('oa_apply_base','oa_approval_msg.apply_id=oa_apply_base.apply_id')
            ->leftJoin('oa_apply_model','oa_apply_base.model_id=oa_apply_model.model_id')
            ->select(['oa_approval_msg.apply_id','oa_approval_msg.title','oa_approval_msg.apply_title','oa_approval_msg.create_time','real_name','head_img','oa_apply_model.model_id','oa_apply_model.modeltype'])
            ->where(['oa_approval_msg.uid' => $uid])->orderBy('oa_approval_msg.create_time DESC');
        $list = $query->limit($limit)->offset($offset)->asArray()->all();
        $totalPage = ceil($query->count() / $limit);
        foreach($list as $key => $value) {
            $list[$key]['head_img'] = $this->getUserHeadimg($value['head_img']);
            $list[$key]['create_time'] = date('Y-n-j H:i',$value['create_time']);
        }
        //待审批消息置为已读
        ApprovalMsgModel::setRead($uid);

        FResponse::output(['code' => 20000, 'msg' => "ok", 'data'=>['list' => $list,'totalPage' => $totalPage]]);
    }

    //任务消息列表
    public function actionTaskMsg() {
        $data = json_decode(file_get_contents("php://input"));
        if( empty($data->page) || empty($data->pageSize) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $uid = $this->userInfo['u_id'];
        $page = $data->page;
        $pageSize = $data->pageSize;
        $offset = ($page-1)*$pageSize;
        $limit = $pageSize;
        $query = TaskMsgModel::find()->leftJoin('oa_members','oa_task_msg.operator=oa_members.u_id')
            ->select(['task_id','title','task_title','create_time','real_name','head_img','menu'])
            ->where(['oa_task_msg.uid' => $uid])->orderBy('oa_task_msg.create_time DESC, oa_task_msg.msg_id DESC');
        $list = $query->limit($limit)->offset($offset)->asArray()->all();
        $totalPage = ceil($query->count() / $limit);
        foreach($list as $key => $value) {
            $list[$key]['head_img'] = $this->getUserHeadimg($value['head_img']);
            $list[$key]['create_time'] = date('Y-m-d H:i',$value['create_time']);
        }

        //任务消息置为已读
        TaskMsgModel::setRead($uid);
        FResponse::output(['code' => 20000, 'msg' => "ok", 'data'=>['list' => $list,'totalPage' => $totalPage]]);
    }

    //会议室消息列表
    public function actionMeetingMsg() {
        $data = json_decode(file_get_contents("php://input"));
        if( empty($data->page) || empty($data->pageSize) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $uid = $this->userInfo['u_id'];
        $page = $data->page;
        $pageSize = $data->pageSize;
        $offset = ($page-1)*$pageSize;
        $limit = $pageSize;
        $query = MeetingMsgModel::find()->leftJoin('oa_members','oa_meeting_msg.sponsor=oa_members.u_id')
            ->select(['res_id','title','meeting_name','room_name','begin_time','end_time','create_time','real_name','head_img'])
            ->where(['oa_meeting_msg.uid' => $uid])->orderBy('oa_meeting_msg.create_time DESC');
        $list = $query->limit($limit)->offset($offset)->asArray()->all();
        $totalPage = ceil($query->count() / $limit);
        foreach($list as $key => $value) {
            $list[$key]['head_img'] = $this->getUserHeadimg($value['head_img']);
            $list[$key]['create_time'] = date('Y-m-d H:i',$value['create_time']);
            $list[$key]['begin_time'] = date('Y-m-d H:i:s',$value['begin_time']);
            $list[$key]['end_time'] = date('Y-m-d H:i:s',$value['end_time']);
        }
        //会议消息置为已读
        MeetingMsgModel::setRead($uid);
        FResponse::output(['code' => 20000, 'msg' => "ok", 'data'=>['list' => $list,'totalPage' => $totalPage]]);
    }

    //工作报告消息列表
    public function actionReportMsg() {
        $data = json_decode(file_get_contents("php://input"));
        if( empty($data->page) || empty($data->pageSize) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $uid = $this->userInfo['u_id'];
        $page = $data->page;
        $pageSize = $data->pageSize;
        $offset = ($page-1)*$pageSize;
        $limit = $pageSize;
        $query = ReportMsgModel::find()->leftJoin('oa_members','oa_report_msg.operator=oa_members.u_id')
            ->select(['work_id','title','create_time','real_name','head_img','menu','work_title'])
            ->where(['oa_report_msg.uid' => $uid])->orderBy('oa_report_msg.create_time DESC');
        $list = $query->limit($limit)->offset($offset)->asArray()->all();
        $totalPage = ceil($query->count() / $limit);
        foreach($list as $key => $value) {
            $list[$key]['head_img'] = $this->getUserHeadimg($value['head_img']);
            $list[$key]['create_time'] = date('Y-m-d H:i',$value['create_time']);
        }
        //工作报告消息置为已读
        ReportMsgModel::setRead($uid);
        FResponse::output(['code' => 20000, 'msg' => "ok", 'data'=>['list' => $list,'totalPage' => $totalPage]]);
    }

    //项目消息列表
    public function actionProjectMsg() {
        $data = json_decode(file_get_contents("php://input"));
        if( empty($data->page) || empty($data->pageSize) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $uid = $this->userInfo['u_id'];
        $page = $data->page;
        $pageSize = $data->pageSize;
        $offset = ($page-1)*$pageSize;
        $limit = $pageSize;
        $query = ProjectMsgModel::find()->leftJoin('oa_members','oa_project_msg.operator=oa_members.u_id')
            ->leftJoin('oa_project','oa_project.pro_id=oa_project_msg.project_id')
            ->select(['oa_project_msg.project_id','oa_project_msg.title','oa_project_msg.project_name','oa_project_msg.create_time','real_name','head_img','oa_project.begin_time','oa_project.end_time','oa_project.complete','menu as pro_menu'])
            ->where(['oa_project_msg.uid' => $uid])->orderBy('oa_project_msg.create_time DESC');
        $list = $query->limit($limit)->offset($offset)->asArray()->all();
        $totalPage = ceil($query->count() / $limit);
        foreach($list as $key => $value) {
            $list[$key]['head_img'] = $this->getUserHeadimg($value['head_img']);
            $list[$key]['create_time'] = date('Y-m-d H:i',$value['create_time']);
            //设置状态
            $list[$key]['pro_status'] = ProjectHelper::setProStatus($value);
        }
        //项目消息置为已读
        ProjectMsgModel::setRead($uid);
        FResponse::output(['code' => 20000, 'msg' => "ok", 'data'=>['list' => $list,'totalPage' => $totalPage]]);
    }

    //搜索
    public function actionSearch() {
        $data = json_decode(file_get_contents("php://input"));
        if( !isset($data->keyWord) || $data->keyWord == '' ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $uid = $this->userInfo['u_id'];
        //申请
        $apply = ApplyBaseModel::find()->select(['apply_id','model_id','title'])->where(['applyer' => $uid])->andWhere(['like', 'title', $data->keyWord])->asArray()->all();
        //审批
        $approval = ApplyBaseModel::find()->select(['apply_id','model_id','title'])->where(['handler' => $uid])->andWhere(['like', 'title', $data->keyWord])->asArray()->all();
        //项目
        $project = ProjectDelegate::getMyAllPro($uid, $data->keyWord);
        //任务
        $task_create = TaskModel::find()->select(['task_id','task_title'])
            ->where('creater=:creater AND task_title LIKE :task_title')
            ->addParams([':creater' => $uid, ':task_title' => '%'.$data->keyWord.'%'])
            ->asArray()->all();
        $task_accept = TaskModel::find()->select(['task_id','task_title'])
            ->where('charger=:charger AND task_title LIKE :task_title')
            ->addParams([':charger' => $uid, ':task_title' => '%'.$data->keyWord.'%'])
            ->asArray()->all();
        $task = [];
        foreach($task_create as $key => $value) {
            $value['type'] = 1;
            $task[] = $value;
        }
        foreach($task_accept as $key => $value) {
            $value['type'] = 2;
            $task[] = $value;
        }
        //工作报告
        $report = WorkStatementModel::find()->select(['work_id','work_content','plan_content'])
            ->where('u_id=:uid AND (work_content LIKE :work_content OR plan_content LIKE :plan_content)')
            ->addParams([':uid' => $uid, ':work_content' => '%'.$data->keyWord.'%', ':plan_content' => '%'.$data->keyWord.'%'])
            ->asArray()->all();
        //会议室预定
        $meeting = ReserveRoomModel::find()->select(['res_id','book_meeting_name'])
            ->where('(uid=:uid OR cor_email_uid LIKE :cor_email_uid) AND book_meeting_name LIKE :meeting_name')
            ->addParams([':uid' => $uid, ':cor_email_uid' => '%,'.$uid.',%', ':meeting_name' => '%'.$data->keyWord.'%'])
            ->asArray()->all();
        //公告
        $notice = NoticeModel::find()->select(['notice_id','title'])->where(['like', 'title', $data->keyWord])->andWhere('is_del=1')->asArray()->all();
        //用户调研
        $survey = SurveyModel::find()->select(['survey_id','title'])->where(['like', 'title', $data->keyWord])->asArray()->all();

        $list = ['apply' => $apply ,'approval' => $approval,'project' => $project,'task' => $task,'report' => $report,'meeting' => $meeting,'notice' => $notice,'survey' => $survey];

        FResponse::output(['code' => 20000, 'msg' => "ok", 'data' => $list]);
    }

    /**
     * 首页工作页面相关信息
     */
    public function actionWorkList()
    {
        $postData = json_decode(file_get_contents("php://input"),true);
        $uid = $postData['u_id'];
        $boardroomDelegate = new BoardroomDelegate;
        $data = date('Y-m-d H:i:s',time());
        $attendanceData = AttendanceDelegate::getDateWork($uid,$data);
        //$workTime = WorkSetModel::find()->asArray()->one();
//        foreach ($attendanceData as $key => $val){
//            $beginTime =strtotime(date('Y-m-d', $val['onTime']).' '.$workTime['begin_time'])+660;
//            $endTime = strtotime(date('Y-m-d', $val['onTime']).' '.$workTime['end_time']);
//            if($val['onTime']>=$beginTime || $val['offTime']< $endTime){
//                unset($attendanceData[$key]);
//            }
//        }
        $meeting =  ReserveRoomModel::getMyMeetingInfo($uid,100000,0);
        foreach ($meeting['meetingInfo'] as $key => $val){
            $reserveData = ReserveDetailModel::find()->where(['res_id' => $val['res_id']])->orderBy(['time_type'=> SORT_ASC])->asArray()->all();
            $lenth = count($reserveData);
            foreach ($reserveData as $k => $v){
                if($k == 0){
                    $reserveBegin =strtotime(date('Y-m-d', $val['reserve_time']).' '.$boardroomDelegate->getReserveDelegate($v['time_type']));
                }
                if(($lenth-1) == $k){
                    $reserveEnd = strtotime(date('Y-m-d', $val['reserve_time']).' '.$boardroomDelegate->getReserveDelegate($v['time_type']));
                }
            }
            if($reserveBegin < time() && $reserveEnd<time()){
                unset($meeting['meetingInfo'][$key]);
            }
        }
        //会议室数据
        $reserveNub = count($meeting['meetingInfo']);
        $allWorkNub = MessageDelegate::getUnreadAll($uid);
        $list = [
            0 => [
                'name' => '出勤天数',
                'nub' => count($attendanceData),
                'isPermStatus' => 1
            ],
            1 => [
                'name' => '会议邀请',
                'nub' => $reserveNub,
                'isPermStatus' => 1
            ],
            2 => [
                'name' => '排行榜',
                'nub' => 0,
                'isPermStatus'  => $this->isPermStatus('WorkmateScoreboard')
            ],
            3 => [
                'name' => '公告',
                //'nub'  => $allWorkNub['noticeNub']
                'nub'  => 0,
                'isPermStatus' => $this->isPermStatus('Notice')
            ],
            4 => [
                'name' => '项目',
                //'nub'  => $allWorkNub['proNub']
                'nub'  => 0,
                'isPermStatus' => $this->isPermStatus('Project')
            ],
            5 => [
                'name' => '任务',
                //'nub'  => $allWorkNub['taskNub']
                'nub'  => 0,
                'isPermStatus' => $this->isPermStatus('Task')
            ],
            6 => [
                'name' => '考勤打卡',
                'nub' => 0,
                'isPermStatus' => 1,
            ],
            7 => [
                'name' => '申请',
               // 'nub' => $allWorkNub['applyNub']
                'nub' => 0,
                'isPermStatus' => $this->isPermStatus('Apply'),
            ],
            8 => [
                'name' => '会议室预定',
                'nub' => $allWorkNub['meetingNub'],
                'isPermStatus' => $this->isPermStatus('BoadroomBoad')
            ],
            9 => [
                'name' => '审批',
                //'nub'  => $allWorkNub['approvalNub']
                'nub'  => 0,
                'isPermStatus' =>  $this->isPermStatus('ApplyMyapprove')
            ],
            10 => [
                'name' => '工作报告',
                //'nub'  =>  $allWorkNub['workNub']
                'nub'  => 0,
                'isPermStatus' => $this->isPermStatus('Workstate')
            ],
            11 => [
                'name' => '用户调研',
                'nub' => 0,
                'isPermStatus' => $this->isPermStatus('Survey')
            ],
        ];
        FResponse::output(['code' => 20000, 'msg' => "ok", 'data' => $list]);
    }

    //客户端删除操作接口
    public function actionSetMsgNewest() {
        $data = json_decode(file_get_contents("php://input"));
        if( !isset($data->module_id) || empty($data->module_id)) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error"]);
        }
        $uid = $this->userInfo['u_id'];
        switch($data->module_id) {
            //审批
            case 1:
                ApprovalMsgModel::updateAll(['newest' => 0],['uid' => $uid]);
                break;
            //申请
            case 2:
                ApplyMsgModel::updateAll(['newest' => 0],['uid' => $uid]);
                break;
            //会议
            case 3:
                MeetingMsgModel::updateAll(['newest' => 0],['uid' => $uid]);
                break;
            //任务
            case 4:
                TaskMsgModel::updateAll(['newest' => 0],['uid' => $uid]);
                break;
            //项目
            case 5:
                ProjectMsgModel::updateAll(['newest' => 0],['uid' => $uid]);
                break;
            //报告
            case 6:
                ReportMsgModel::updateAll(['newest' => 0],['uid' => $uid]);
                break;
        }
        FResponse::output(['code' => 20000, 'msg' => "ok"]);
    }
}