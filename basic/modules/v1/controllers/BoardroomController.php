<?php
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/11/2
 * Time: 14:42
 */

namespace app\modules\v1\controllers;

use app\lib\FResponse;
use app\lib\Tools;
use app\modules\boardroom\delegate\BoardroomDelegate;
use app\models\ReserveRoomModel;
use app\models\ReserveDetailModel;
use app\models\MeetingRoomModel;
use app\models\MeetingMsgModel;

class BoardroomController extends BaseController
{
    public $modelClass = 'app\models\ChecktimeModel';

    /**
     * 会议室（我的预定）
     */
    public function actionMyReserveMeeting()
    {
        //$this->isPerm('BoadroomBoad');
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $boardroomDelegate = new BoardroomDelegate;
        $boardroomDelegate->getMyReserveMeetingDeg($request->u_id, $request->pageSize, $request->curPage,$this->isPermStatus('BoadroomBoad'));
    }

    /**
     * 我的预定详情页面信息
     */
    public function actionMyReserveDetail()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        if(empty($request->resId)){
            FResponse::output(['code' => 20001, 'msg' => "缺少参数！", 'data'=>""]);
        }
        $boardroomDelegate = new BoardroomDelegate;
        $boardroomDelegate->getMyReserveMeetingDetailDeg($request->resId);
    }

    /**
     * 取消我预定的会议室
     */
    public function actionCancelReserve()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $msgInfo = '取消了会议';
        if(empty($request->resId)){
            FResponse::output(['code' => 20001, 'msg' => "缺少参数！"]);
        }
        $uid = $this->userInfo['u_id'];
        $userId = ReserveRoomModel::find()->where(['res_id'=>$request->resId])->asArray()->one()['uid'];
        if($uid != $userId){
             FResponse::output(['code'=>20001, 'msg'=> '你没有权限']);
        }
        $emailInfo = ReserveRoomModel::find()->select('oa_reserve_room.cor_email_uid, oa_reserve_room.book_meeting_name, oa_reserve_room.book_meeting_desc, oa_reserve_room.book_time, oa_reserve_room.meeting_name,oa_meeting_room.name')->leftJoin('oa_meeting_room','oa_meeting_room.room_id=oa_reserve_room.room_id')->where(['res_id'=>$request->resId])->asArray()->one();
        $resTimeInfo = MeetingMsgModel::find()->where(['res_id'=>$request->resId])->asArray()->one();
        $uidArr = explode(',',$emailInfo['cor_email_uid']);
        if($emailInfo){ 
            BoardroomDelegate::sendEmailCancelDelegate($uid,$emailInfo);
        }
        $res = ReserveRoomModel::deleteAll(['res_id'=>$request->resId]);
        if($res && !empty($request->resId)){
            BoardroomDelegate::ReserveMsg($emailInfo['cor_email_uid'],$request->resId,$uid,$emailInfo['book_meeting_name'],$emailInfo['name'],$resTimeInfo['begin_time'],$resTimeInfo['end_time'],$msgInfo);
            if(count($uidArr)==0){
                $uidArr[0] = 0;
            }
            Tools::msgJpush(6,$request->resId,$this->userInfo['real_name'].$msgInfo.$emailInfo['book_meeting_name'],$uidArr);
            ReserveDetailModel::deleteAll(['res_id'=>$request->resId]);
            FResponse::output(['code'=>20000, 'msg'=> 'ok']);
        }else{
            FResponse::output(['code'=>0, 'msg'=> 'Error']);
        }
    }

    /**
     * 我的会议
     */
    public function actionMyMeeting()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        if(empty($request->pageSize) || empty($request->curPage)){
            FResponse::output(['code' => 20001, 'msg' => "缺少参数！", 'data'=>""]);
        }
        $uid = $this->userInfo['u_id'];
        $boardroomDelegate = new BoardroomDelegate;
        $boardroomDelegate->getMyReserveMeetingDeg($uid, $request->pageSize, $request->curPage, $this->isPermStatus('BoadroomBoad'));
    }
    /**
     * 查询所有会议室
     */
    public function actionSelectAllMeeting()
    {
        $meetingRoomInfo = MeetingRoomModel::getMeetingRome();
        FResponse::output(['code'=>20000, 'msg'=> 'ok', 'data'=> $meetingRoomInfo]);
    }

    /**
     * 会议室预定
     */
    public function actionReserve()
    {
        $this->isPerm('BoadroomBoad');
        MeetingMsgModel::updateAll(['is_read'=>1]);
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata,true);
        $uid = $this->userInfo['u_id'];
        $types = $request['types'];
        $dataTime = $request['dataTime'];
        $memberInfo = $request['memberInfo'];
        $meetingName = $request['meetingName'];
        $corEmailUid = ',';
        $bookTime = $request['bookTime'];
        $meeting = $request['meeting'];
        $meetingDesc = $request['meetingDesc'];
        if(!empty($memberInfo)){
            foreach ($memberInfo as $key => $val){
                $corEmailUid .= $val['uid'].',';
            }
        }
        $boardroom = new BoardroomDelegate;
        $boardroom->reserve($uid, $types, $dataTime, $corEmailUid, $meetingName, $bookTime, $meeting, $meetingDesc,'', $this->userInfo['real_name']);
    }

    /**
     * 移动端预定相关处理
     */
    public function actionAppReserve()
    {
        $postdata = json_decode(file_get_contents("php://input"),true);
        $memberInfo = $postdata['memberInfo'];
        $meetingName = $postdata['meetingName'];
        $meetingDesc = $postdata['meetingDesc'];
        $bookTime = $postdata['bookTime'];
        $meeting = $postdata['meeting'];
        $emailType = $postdata['emailType'];
        $resBeginTime = $postdata['resBeginTime'];
        $resEndTime = $postdata['resEndTime'];
        $resId = $postdata['resId'];
        if(!isset($resId) || !isset($memberInfo)|| !isset($meeting) || !isset($bookTime) || !isset($emailType) || !isset($resBeginTime) || !isset($resEndTime)){
            FResponse::output(['code'=>20001, 'msg'=> '参数错误!']);
        }
        $corEmailUid = ',';
        if(!empty($memberInfo)){
            foreach ($memberInfo as $key => $val){
                $corEmailUid .= $val['uid'].',';
            }
        }
        ReserveRoomModel::updateAll([
            'cor_email_uid'=>$corEmailUid,
            'book_meeting_name'=>$meeting,
            'book_meeting_desc'=>$meetingDesc,
            'meeting_name' => $meetingName,
            'book_time'=>$bookTime
        ],['res_id'=>$resId]);
        $uidArr =explode(',',$corEmailUid);
        if(count($uidArr)==0){
            $uidArr[0] = 0;
        }
        Tools::msgJpush(6,$resId,$this->userInfo['real_name'].'邀请你参加会议'.$meeting,$uidArr);
        BoardroomDelegate::ReserveMsg($corEmailUid,$resId,$this->userInfo['u_id'],$meeting,$meetingName,$resBeginTime,$resEndTime,'邀请你参加了会议');
        if($emailType==2){
            BoardroomDelegate::sendEmailDelegate($this->userInfo['u_id'],$meetingName, $bookTime, $meeting, $meetingDesc, $memberInfo);
        }
        FResponse::output(['code'=>20000, 'msg'=> 'ok']);
    }
    /**
     * 会议室相关信息
     */
    public function actionRoomReserveList()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata,true);
        $searchTime = !empty($request['searchTime']) ? $request['searchTime']:date("Y-m-d",time());
        $weekAfter = strtotime(" +1 week");
        if(is_int($searchTime)){
            $searchTime = date("Y-m-d",$searchTime);
        }
        //查询所有会议室
        $res['list'] = MeetingRoomModel::getMeetingRome();
        $res['weekAfter'] = $weekAfter;
        //查询会议室相关预定
        foreach ($res['list'] as $key => $value) {
            $res['list'][$key]['reserves'] = ReserveDetailModel::getConferenceRoomRelated($value['room_id'], $searchTime);
        }
        FResponse::output(['code'=>20000, 'msg'=> 'ok', 'data'=> $res]);
    }

    /**
     * 会议室相关信息
     */
    public function actionGetMeetingReserveRome()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata,true);
        $roomId = $request['roomId'];
        $searchTime = !empty($request['searchTime']) ? $request['searchTime']:date("Y-m-d",time());
        if(!isset($roomId)){
            FResponse::output(['code'=>20001, 'msg'=> '参数错误！']);
        }
        $res['list'] = MeetingRoomModel::getMeetingReserveRome($roomId);
        $weekAfter = strtotime(" +1 week");
        if(is_int($searchTime)){
            $searchTime = date("Y-m-d",$searchTime);
        }
        $res['weekAfter'] = $weekAfter;
        //查询会议室相关预定
        $res['list']['reserves'] = ReserveDetailModel::getConferenceRoomRelated($res['list']['room_id'], $searchTime);
        $res['isPermStatus'] = empty($this->isPermStatus('BoadroomBoad')) ? 0: $this->isPermStatus('BoadroomBoad');
        FResponse::output(['code'=>20000, 'msg'=> 'ok', 'data'=> $res]);
    }

    
}