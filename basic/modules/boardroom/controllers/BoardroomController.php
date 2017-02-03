<?php
namespace app\modules\boardroom\controllers;

use app\models\MembersModel;
use yii;
use app\models\MeetingMsgModel;
use app\controllers\BaseController;
use app\models\MeetingRoomModel;
use app\models\ReserveRoomModel;
use app\models\ReserveDetailModel;
use app\modules\boardroom\delegate\BoardroomDelegate;
use app\lib\FResponse;
use app\lib\Tools;
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/7/5
 * Time: 15:48
 */
class BoardroomController extends BaseController
{
    public $modelClass = 'app\models\ReserveRoomModel';
    //会议室与预定记录列表
    public function actionRoomReserveList()
    {
        $searchTime = !empty(Yii::$app->request->post('dataTime')) ? Yii::$app->request->post('dataTime'):date("Y-m-d",time());
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


    //预定会议室
    public function actionReserve() {
        $uid = $this->userInfo['u_id'];
        $types = Yii::$app->request->post('types');
        $dataTime = Yii::$app->request->post('dataTime');
        $memberInfo = !empty(Yii::$app->request->post('memberInfo'))?Yii::$app->request->post('memberInfo'):'';
        $meetingName = !empty(Yii::$app->request->post('meetingName'))?Yii::$app->request->post('meetingName'):'';
        $corEmailUid = ',';
        if(!empty($memberInfo)){
            foreach ($memberInfo as $key => $val){
                $corEmailUid .= $val['uid'].',';
            }
        }
        $bookTime = !empty(Yii::$app->request->post('bookTime'))?Yii::$app->request->post('bookTime'):'';
        $meeting = !empty(Yii::$app->request->post('meeting'))?Yii::$app->request->post('meeting'):'';
        $meetingDesc = !empty(Yii::$app->request->post('meetingDesc'))?Yii::$app->request->post('meetingDesc'):'';
        $boardroom = new BoardroomDelegate;
        $boardroom->reserve($uid, $types, $dataTime, $corEmailUid, $meetingName, $bookTime, $meeting, $meetingDesc,$memberInfo,$this->userInfo['real_name']);
    }
    /*
     * 预定会议室后发送邮件
     */
    public function actionSendEmail()
    {
        $meetingName = Yii::$app->request->post('meetingName');
        $bookTime = Yii::$app->request->post('bookTime');
        $meeting = Yii::$app->request->post('meeting');
        $meetingDesc = Yii::$app->request->post('meetingDesc');
        $memberName = Yii::$app->request->post('memberName');
        $uid = $this->userInfo['u_id'];
        BoardroomDelegate::sendEmailDelegate($uid,$meetingName, $bookTime, $meeting, $meetingDesc, $memberName);
    }
    //新增会议室
    public function actionAdd() 
    {
        $meetingName = Yii::$app->request->post('name');
        $meetingFloor = Yii::$app->request->post('floor');
        $meetingDesc = Yii::$app->request->post('desc');
        $meetingHot = Yii::$app->request->post('hot');
        $existence = MeetingRoomModel::find()->where(['name'=>$meetingName])->asArray()->one();
        if($existence){
            FResponse::output(['code'=>-1, 'msg'=> '该会议室已存在，请重新输入！']);
        }
        $res = new MeetingRoomModel();
        $res->name = $meetingName;
        $res->floor = $meetingFloor;
        $res->desc = $meetingDesc;
        $res->hot = $meetingHot;
        $res->create_time = time();
        if($res->save()){
            FResponse::output(['code'=>20000, 'msg'=> '添加会议室成功！']);
        } else {
            FResponse::output(['code'=>-1, 'msg'=> '添加会议室失败！']);
        }
    }

    /*
     * 获取会议室相关信息
     */
    public function actionGetMeetingInfo()
    {
        $meetingId = Yii::$app->request->post('meetingId');
        $meetingInfo = MeetingRoomModel::getMeetingInfo($meetingId);
        FResponse::output(['code'=>20000, 'msg'=> 'ok', 'data'=> $meetingInfo]);
    }

    /*
     * 查询所有会议室
     */
    public function actionSelectAllMeeting()
    {
        $meetingRoomInfo = MeetingRoomModel::getMeetingRome();
        FResponse::output(['code'=>20000, 'msg'=> 'ok', 'data'=> $meetingRoomInfo]);
    }

    /*
     * 编辑会议室相关信息
     */
    public function actionEditMeetingInfo()
    {
        $meetingId = Yii::$app->request->post('meetingId');
        $name = Yii::$app->request->post('name');
        $desc = Yii::$app->request->post('desc');
        $floor = Yii::$app->request->post('floor');
        $hot = Yii::$app->request->post('hot');
        $flag = Yii::$app->request->post('flag');
        if($flag==2){
            $existence = MeetingRoomModel::find()->where(['name'=>$name])->asArray()->one();
            if($existence){
                FResponse::output(['code'=>-1, 'msg'=> '该会议已存在，请重新输入！']);
            }
        }
        MeetingRoomModel::editMeetingRoom($meetingId, $name, $desc, $floor, $hot);
    }

    /*
     *删除会议室
     */
    public function actionDeleteMeeting()
    {
        $meetingId = Yii::$app->request->post('meetingId');
        MeetingRoomModel::deleteAll(['room_id'=>$meetingId]);
    }
    /*
     * 取消我预定的会议室
     */
    public function actionCancelReserve()
    {
        $resId = Yii::$app->request->post('resId');
        $uid = $this->userInfo['u_id'];
        $emailInfo = ReserveRoomModel::find()->select('oa_reserve_room.cor_email_uid, oa_reserve_room.book_meeting_name, oa_reserve_room.book_meeting_desc, oa_reserve_room.book_time, oa_reserve_room.meeting_name,oa_meeting_room.name')->leftJoin('oa_meeting_room','oa_meeting_room.room_id=oa_reserve_room.room_id')->where(['res_id'=>$resId])->asArray()->one();
        $resTimeInfo = MeetingMsgModel::find()->where(['res_id'=>$resId])->asArray()->one();
        $uidArr = explode(',',$emailInfo['cor_email_uid']);
        if($emailInfo){
            BoardroomDelegate::sendEmailCancelDelegate($uid,$emailInfo);
        }
        $res = ReserveRoomModel::deleteAll(['res_id'=>$resId]);
        if($res && !empty($resId)){
            BoardroomDelegate::ReserveMsg($emailInfo['cor_email_uid'],$resId,$uid,$emailInfo['book_meeting_name'],$emailInfo['name'],$resTimeInfo['begin_time'],$resTimeInfo['end_time'],'取消了会议');
            foreach ($uidArr as $key => $val){
                if(empty($val)){
                    unset($uidArr[$key]);
                }
            }
            if(count($uidArr)==0){
                $uidArr[0]=0;
            }
            Tools::msgJpush(6,$resId,$this->userInfo['real_name'].'取消了会议'.$emailInfo['book_meeting_name'],$uidArr);
            ReserveDetailModel::deleteAll(['res_id'=>$resId]);
            FResponse::output(['code'=>20000, 'msg'=> 'ok']);
        }else{
            FResponse::output(['code'=>0, 'msg'=> 'Error']);
        }
    }

    /*
     * 鼠标拖动
     */
    public function actionUpdateGalleryId()
    {
        $uid = Yii::$app->request->post('uid');
        MembersModel::updateAll(['gallery'=>2],['u_id'=>$uid]);
    }
}