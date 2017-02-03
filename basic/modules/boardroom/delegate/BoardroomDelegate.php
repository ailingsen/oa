<?php

namespace app\modules\boardroom\delegate;

use app\lib\Tools;
use app\models\OrgMemberModel;
use app\models\OrgModel;
use yii;
use app\models\MeetingMsgModel;
use app\models\ReserveDetailModel;
use app\models\MembersModel;
use app\lib\FResponse;
use app\models\ReserveRoomModel;
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/7/5
 * Time: 17:46
 */
class BoardroomDelegate
{
    private $time_config = array(
        0 => '9:00',
        1 => '9:30',
        2 => '10:00',
        3 => '10:30',
        4 => '11:00',
        5 => '11:30',
        6 => '12:00',
        7 => '13:30',
        8 => '14:00',
        9 => '14:30',
        10 => '15:00',
        11 => '15:30',
        12 => '16:00',
        13 => '16:30',
        14 => '17:00',
        15 => '17:30',
        16 => '18:00',
    );
    /*
     * 会议室预定
     */
    public function reserve($uid, $types, $dataTime , $corEmailUid, $meetingName, $bookTime, $meeting, $meetingDesc,$memberInfo,$realName)
    {
        $reserve_time = strtotime($dataTime);
        $roomId = '';
        $transaction = Yii::$app->db->beginTransaction();
        try {
            //判断该会议室该日期的该时段是否被预定
            $typeLeng = count($types);
            foreach ($types as $key => $val) {
                //判断预定时间是否过期
                if($key==0){
                    if($val['timeId'] >= 6){
                        $reserve_time_tmp = strtotime($dataTime.$this->time_config[$val['timeId']+1]);
                        $resBeginTime = strtotime($dataTime.$this->time_config[$val['timeId']+1]);
                    }else{
                        $reserve_time_tmp = strtotime($dataTime.$this->time_config[$val['timeId']]);
                        $resBeginTime = strtotime($dataTime.$this->time_config[$val['timeId']]);
                    }
                }
                if($key == ($typeLeng-1)){
                    if($val['timeId'] >= 6){
                        $resEndTime = strtotime($dataTime.$this->time_config[$val['timeId']+2]);
                    }else{
                        $resEndTime = strtotime($dataTime.$this->time_config[$val['timeId']+1]);
                    }
                }
                if(!empty($val['roomId'])){
                    $roomId = $val['roomId'];
                }
//                if($key==0){
//                    $resBeginTime = strtotime($dataTime.$this->time_config[$val['timeId']]);
//                }
//                if($key == ($typeLeng-1)){
//                    $resEndTime = strtotime($dataTime.$this->time_config[$val['timeId']+1]);
//                }
                if(time() > $reserve_time_tmp) {
                    throw new \Exception(1);
                }
                $res = ReserveDetailModel::getRoomRelatedInformation($roomId, $val['timeId'], $reserve_time);
                if (!empty($res)) {
                    throw new \Exception(2);
                }
            }
            //插入数据库
            $insertRes = Yii::$app->db->createCommand()->insert('oa_reserve_room', array(
                'room_id' => $roomId,
                'uid' => $uid,
                'create_time' => time(),
                'reserve_time' => $reserve_time,
                'book_meeting_desc'=> $meetingDesc,
                'book_meeting_name' => $meeting,
                'book_time' => $bookTime,
                'meeting_name' => $meetingName,
                'cor_email_uid' => $corEmailUid
            ))->execute();
            if ($insertRes) {
                $insertid = Yii::$app->db->getLastInsertID();
                foreach ($types as $k => $v) {
                    Yii::$app->db->createCommand()->insert('oa_reserve_detail', array(
                        'res_id' => $insertid,
                        'room_id' => $roomId,
                        'uid' => $uid,
                        'time_type' => $v['timeId'],
                        'reserve_time' => $reserve_time,
                        'create_time' => time(),
                    ))->execute();
                }
            } else {
                throw new \Exception(3);
            }
            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            if($e->getMessage() == 1) {
                FResponse::output(['code'=>2, 'msg'=> '不可预定该时间段，请重新预定！']);
            }else if($e->getMessage() == 2) {
                FResponse::output(['code'=>2, 'msg'=> '该会议室已被预定，请重新预定！']);
            }else if($e->getMessage() == 3) {
                FResponse::output(['code'=>2, 'msg'=> '预定会议室失败，请重新预定！']);
            }else {
                FResponse::output(['code'=>2, 'msg'=> '预定会议室失败，请重新预定！']);
            }
        }
        if(!empty($memberInfo)){
            self::sendEmailDelegate($uid,$meetingName, $bookTime, $meeting, $meetingDesc, $memberInfo);
            $uidArr =explode(',',$corEmailUid);
            foreach ($uidArr as $key => $val){
                if(empty($val)){
                    unset($uidArr[$key]);
                }
            }
            if(count($uidArr)==0){
                $uidArr[0] = 0; 
            }
            Tools::msgJpush(6,$insertid,$realName.'邀请你参加会议'.$meeting,$uidArr);

        }
        self::ReserveMsg($corEmailUid,$insertid,$uid,$meeting,$meetingName,$resBeginTime,$resEndTime,'邀请你参加会议');
        $dataInfo = [
            'insertid' =>$insertid,
            'resBeginTime' => $resBeginTime,
            'resEndTime'  => $resEndTime
        ];
        FResponse::output(['code'=>20000, 'msg'=> '预定会议室成功','data' => $dataInfo]);
    }
    /*
     * 预定会议室发送邮件
     */
    public static function sendEmailDelegate($meetingUid,$meetingName, $bookTime, $meeting, $meetingDesc, $memberName)
    {
        $roomBookName = MembersModel::find()->select('real_name')->where(['u_id' => $meetingUid])->asArray()->one()['real_name'];
        $sendContent = '<p>'.$roomBookName.'通知您于'.$bookTime.'到'.$meetingName.'参加会议'.$meeting.'<br></p>'.'<p>会议说明：'.$meetingDesc;
        foreach ($memberName as $key => $val){
            $receiverEmail = MembersModel::find()->select('email')->where(['u_id'=>$val['uid']])->asArray()->one()['email'];
            Tools::asynSendMail($meeting,$sendContent,$receiverEmail);
        }
        
    }

    /**
     * @param $meetingUid
     * @param $roomInfo
     * 取消会议室 发送邮件
     */
    public static function sendEmailCancelDelegate($meetingUid, $roomInfo)
    {
        $roomBookName = MembersModel::find()->select('real_name')->where(['u_id' => $meetingUid])->asArray()->one()['real_name'];
        $roomBookUidInfo =explode(',',$roomInfo['cor_email_uid']);
        $sendContent = '<p>'.$roomBookName.'通知您于'.$roomInfo['book_time'].'在'.$roomInfo['meeting_name'].'的会议'.$roomInfo['book_meeting_name'].' <span style="color:#f00">已取消！</span>';
        foreach ($roomBookUidInfo as $key => $val){
            if (!empty($val)){
                $receiverEmail = MembersModel::find()->select('email')->where(['u_id'=>$val])->asArray()->one()['email'];
                Tools::asynSendMail($roomInfo['book_meeting_name'],$sendContent,$receiverEmail);
            }
        }
    }

    public function getMyReserveMeetingDeg($uid, $pageSize, $curPage,$isPermStatus)
    {
        if(Yii::$app->controller->id == "boardroom" && Yii::$app->controller->action->id == "my-reserve-meeting"){
            $meetingInfo = ReserveRoomModel::getMyReserveRoomInfo($uid, $pageSize, $curPage);
            //$sumReadable = ReserveRoomModel::find()->select('sum(readable) as sumReadable')->where(['uid' => $uid,'readable'=>1])->groupBy('uid')->asArray()->one();
        }else if(Yii::$app->controller->id == "boardroom" && Yii::$app->controller->action->id == "my-meeting"){
            $meetingInfo = ReserveRoomModel::getMyMeetingInfo($uid, $pageSize, $curPage);
//            $sumReadable = ReserveRoomModel::find()->select('sum(readable) as sumReadable')->where(['in','cor_email_uid',array($uid)])->andWhere(['readable'=>1])->groupBy('uid')->asArray()->one();
        }

        foreach ($meetingInfo['meetingInfo'] as $key => $val){
            if(!$val['name']){
                $meetingInfo['meetingInfo'][$key]['name'] = '';
            }
            $timeData = ReserveDetailModel::find()->where(['res_id' => $val['res_id']])->asArray()->all();
            foreach ($timeData as $k => $v){
                if($k==0){
                    if($v['time_type'] >= 6){
                        $timeBegin = $this->time_config[$v['time_type']+1];
                    }else{
                        $timeBegin = $this->time_config[$v['time_type']];
                    }
                }
                if ($k == count($timeData)-1){
                    if($v['time_type'] >= 6){
                        $timeEnd = $this->time_config[$v['time_type']+2];
                    }else{
                        $timeEnd = $this->time_config[$v['time_type']+1];
                    }

                }
            }
            switch (date('w',$val['reserve_time'])) {
                case 0:$weekDay="星期天";break;
                case 1:$weekDay="星期一";break;
                case 2:$weekDay="星期二";break;
                case 3:$weekDay="星期三";break;
                case 4:$weekDay="星期四";break;
                case 5:$weekDay="星期五";break;
                case 6:$weekDay="星期六";break;
            }
            if($timeBegin == $timeEnd){
                $meetingInfo['meetingInfo'][$key]['meetingTime'] = date('Y-m-d',$val['reserve_time']).'('.$weekDay.')'.' '.$timeBegin;
                $meetingInfo['meetingInfo'][$key]['endTime'] = strtotime(date('Y-m-d',$val['reserve_time']).' '.$timeBegin);
            }else{
                $meetingInfo['meetingInfo'][$key]['meetingTime'] = date('Y-m-d',$val['reserve_time']).'('.$weekDay.')'.' '.$timeBegin.'-'.$timeEnd;
                $meetingInfo['meetingInfo'][$key]['endTime'] = strtotime(date('Y-m-d',$val['reserve_time']).' '.$timeEnd);
            }
            $meetingInfo['meetingInfo'][$key]['beginTime'] = strtotime(date('Y-m-d',$val['reserve_time']).' '.$timeBegin);
            $meetingInfo['meetingInfo'][$key]['meetingIng'] = 1;
            if( $meetingInfo['meetingInfo'][$key]['beginTime'] <= time() && $meetingInfo['meetingInfo'][$key]['endTime'] > time()){
                //会议进行中
                $meetingInfo['meetingInfo'][$key]['meetingIng'] = 2;
            }
            if ($meetingInfo['meetingInfo'][$key]['beginTime'] > time()){
                //会议未开始
                $meetingInfo['meetingInfo'][$key]['meetingIng'] = 1;
            }
            if ($meetingInfo['meetingInfo'][$key]['endTime'] < time()){
                //会议已结束
                $meetingInfo['meetingInfo'][$key]['meetingIng'] = 3;
            }
        }
        $flag=array();
        foreach($meetingInfo['meetingInfo'] as $arr){
            $flag[] = $arr['beginTime'];
        }
        array_multisort($flag, SORT_ASC, $meetingInfo['meetingInfo']);
        $meetingInfo['meetingInfo'] = self::pageArray($curPage, $pageSize, $meetingInfo['meetingInfo']);
        //$meetingInfo['sumReadable'] = $sumReadable['sumReadable'];
        $meetingInfo['isPermStatus'] = $isPermStatus;
        FResponse::output(['code'=>20000,'msg'=>'ok','data' =>$meetingInfo]);
    }

    /**
     * 数组分页函数 核心函数 array_slice
     * 用此函数之前要先将数据库里面的所有数据按一定的顺序查询出来存入数组中
     * $pageSize  每页多少条数据
     * $curPage  当前第几页
     * $array  查询出来的所有数组
     */
    public static function pageArray($curPage, $pageSize, $array)
    {
        $pageSize = (empty($pageSize)) ? 1:$pageSize; //判断当前页面是否为空 如果为空就表示为第一页面
        $start = ($curPage-1)*$pageSize;
        $pageData = array_slice($array,$start,$pageSize);
        return $pageData;

}
    public function getMyReserveMeetingDetailDeg($resId)
    {
        $meetingDetailInfo = ReserveRoomModel::getMyReserveDetail($resId);
        $timeData = ReserveDetailModel::find()->where(['res_id' => $meetingDetailInfo['res_id']])->asArray()->all();
        if(empty($timeData)){
            FResponse::output(['code'=>20005,'msg'=>'数据不存在！']);
        }
        foreach ($timeData as $k => $v){
            if($k==0){
                if($v['time_type'] >= 6){
                    $timeBegin = $this->time_config[$v['time_type']+1];
                }else{
                    $timeBegin = $this->time_config[$v['time_type']];
                }
            }
            if ($k == count($timeData)-1){
                if($v['time_type'] >=6){
                    $timeEnd = $this->time_config[$v['time_type']+2];
                }else{
                    $timeEnd = $this->time_config[$v['time_type']+1];
                }
            }
        }
        switch (date('w',$meetingDetailInfo['reserve_time'])) {
            case 0:$weekDay="星期天";break;
            case 1:$weekDay="星期一";break;
            case 2:$weekDay="星期二";break;
            case 3:$weekDay="星期三";break;
            case 4:$weekDay="星期四";break;
            case 5:$weekDay="星期五";break;
            case 6:$weekDay="星期六";break;
        }
        if($timeBegin == $timeEnd){
            $meetingDetailInfo['meetingTime'] = date('Y-m-d',$meetingDetailInfo['reserve_time']).'('.$weekDay.')'.' '.$timeBegin;
            $meetingDetailInfo['endTime'] = strtotime(date('Y-m-d',$meetingDetailInfo['reserve_time']).' '.$timeBegin);
        }else{
            $meetingDetailInfo['meetingTime'] = date('Y-m-d',$meetingDetailInfo['reserve_time']).'('.$weekDay.')'.' '.$timeBegin.'-'.$timeEnd;
            $meetingDetailInfo['endTime'] = strtotime(date('Y-m-d',$meetingDetailInfo['reserve_time']).' '.$timeEnd);
        }
        $meetingDetailInfo['beginTime'] = strtotime(date('Y-m-d',$meetingDetailInfo['reserve_time']).' '.$timeBegin);
        $meetingDetailInfo['meetingIng'] = 1;
        if( $meetingDetailInfo['beginTime'] <= time() && $meetingDetailInfo['endTime'] > time()){
            //会议进行中
            $meetingDetailInfo['meetingIng'] = 2;
        }
        if ($meetingDetailInfo['beginTime'] > time()){
            //会议未开始
            $meetingDetailInfo['meetingIng'] = 1;
        }
        if ($meetingDetailInfo['endTime'] < time()){
            //会议已结束
            $meetingDetailInfo['meetingIng'] = 3;
        }
        $joinUid = explode(",",$meetingDetailInfo['cor_email_uid']);
        $joinMemberInfo = [];
       foreach ($joinUid as $val){
           if($val){
               //$orgId= OrgMemberModel::find()->where(['u_id'=>$val])->asArray()->one()['org_id'];
               $joinName = MembersModel::find()->where(['u_id' => $val])->asArray()->one()['real_name'];
//               $joinMemberInfo[] = $joinName.' ('.OrgModel::getParentGroup($orgId).')';
               $joinMemberInfo[] = $joinName;
           }
       }
        $meetingDetailInfo['joinMemberInfo'] = $joinMemberInfo;
        //ReserveRoomModel::updateAll(['readable'=>2],['res_id'=>$resId]);
        FResponse::output(['code'=>20000,'msg'=>'ok','data' =>$meetingDetailInfo]);
    }

    public static function  ReserveMsg($corUid,$insertid,$uid,$meetingName,$meeting,$resBeginTime,$resEndTime,$msgInfo)
    {
        $roomBookUidInfo =explode(',',$corUid);
        foreach ($roomBookUidInfo as $key => $val){
            if (!empty($val)){
                \Yii::$app->db->createCommand()->insert('oa_meeting_msg', [
                    'uid'       =>$val,
                    'res_id'    => $insertid,
                    'sponsor'   => $uid,
                    'title'     => $msgInfo,
                    'meeting_name'=> $meetingName,
                    'room_name' =>  $meeting,
                    'begin_time' => $resBeginTime,
                    'end_time'  =>  $resEndTime,
                    'create_time' => time()
                ])->execute();
            }
        }
    }
    
    
    public function getReserveDelegate($timeType)
    {
        if($timeType >= 6){
            $reserveTime = $this->time_config[$timeType+1];
        }else{
            $reserveTime = $this->time_config[$timeType];
        }
        return $reserveTime;
    }
    
    
//    public static function  ReserveMsg($corUid,$insertid,$uid,$meeting,$meetingName,$resBeginTime,$resEndTime,$msgInfo)
//    {
//        $roomBookUidInfo =explode(',',$corUid);
//        $msgSave = new MeetingMsgModel();
//        foreach ($roomBookUidInfo as $key => $val){
//            if (!empty($val)){
//                $msgSave->uid = $val;
//                $msgSave->res_id = $insertid;
//                $msgSave->sponsor = $uid;
//                $msgSave->title = $msgInfo;
//                $msgSave->meeting_name = $meetingName;
//                $msgSave->room_name = $meeting;
//                $msgSave->begin_time = $resBeginTime;
//                $msgSave->end_time = $resEndTime;
//                $msgSave->create_time = time();
//            }
//            $msgSave->save(false);
//        }
//    }
}