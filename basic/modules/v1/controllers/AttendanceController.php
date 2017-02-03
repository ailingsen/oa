<?php
namespace app\modules\v1\controllers;

use app\models\FResponse;
use app\models\MembersModel;
use app\models\WorkSetModel;
use app\modules\v1\delegate\AttendanceDelegate;
use app\modules\v1\helper\AttendanceHelper;
use Yii;
use Yii\base\Object;
use app\models\Mcache;

class AttendanceController extends BaseController
{
    public $modelClass = 'app\models\AttendanceModel';
    /**
     * 考勤打卡
     * $date 日期
    */
    public function actionMyAttend()
    {
        $this->isPerm('AttendanceMine');
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata,true);
        if( empty($postdata['date']) ) {
            FResponse::output(['code' => 20001, 'msg' => "日期不能为空", 'data'=>new Object()]);
        }
        $date = isset($postdata['date'])? $postdata['date'] :date('Y-m-d',time());
        //获取工作日时间设置
        $timeSet = \app\modules\attendance\delegate\AttendanceDelegate::getTimeSet();
        //获取我的考勤
        $res = AttendanceDelegate::getMyAttend($this->userInfo['u_id'], $date);
        //处理我的考勤数据
        if(count($res)>0){
            $res = \app\modules\attendance\helper\AttendanceHelper::setMyAttend($timeSet,$res);
        }
        FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>$res]);
    }

    /**
     * 考勤打卡
     * $date 日期
     */
    public function actionMyAttendDay()
    {
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata,true);
        $date = isset($postdata['date'])? $postdata['date'] :date('Y-m-d',time());
        //获取工作日时间设置
        $timeSet = \app\modules\attendance\delegate\AttendanceDelegate::getTimeSet();
        //获取我的考勤
        $res = AttendanceDelegate::getMyAttendDay($this->userInfo['u_id'], $date);
        $temp[]=$res;
        //处理我的考勤数据
        $temp1=array();
        if(count($res)>0){
            $temp1 = \app\modules\attendance\helper\AttendanceHelper::setMyAttend($timeSet,$temp);
        }
        $temp2 = array();
        if(count($temp1)){
            $temp2=$temp1[0];
        }
        if(count($temp2)<=0){
            $temp2 = (object)$temp2;
        }
        FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>$temp2]);
    }

    //打卡
    public function actionAddAttendance(){
        $postData = json_decode(file_get_contents("php://input"));
        if( empty($postData->imei) || !isset($postData->bind) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $imei = $postData->imei;
        $bind = $postData->bind;

        $userInfo = $this->userInfo;

        //设备号绑定和判断
        if( $bind == 0 ) {
            if (empty($userInfo['imei'])) {
                FResponse::output(['code' => 20080,'msg' => '尚未绑定设备号', 'data'=>new Object()]);
            }
            if( $userInfo['imei'] != $imei ){
                FResponse::output(['code' => 20074,'msg' => 'OA账号与设备标识不一致，签到失败', 'data'=>new Object()]);
            }
        }
        elseif( $bind == 1 ){
            if ( !empty($userInfo['imei']) ) {
                FResponse::output(['code' => 20073,'msg' => '设备号已绑定，无法再次绑定', 'data'=>new Object()]);
            }
            $user = MembersModel::find()->where(['imei'=>$imei])->one();
            if( $user ){
                FResponse::output(['code' => 20077,'msg' => '此设备已被绑定', 'data'=>new Object()]);
            }

            $user = MembersModel::find()->where(['u_id'=>$this->uid])->one();
            if( $user ) {
                $user->imei = $imei;
                if( !$user->save(false) ){
                    FResponse::output(['code' => 20075,'msg' => '绑定失败', 'data'=>new Object()]);
                }
            }
            else{
                FResponse::output(['code' => 20075,'msg' => '绑定失败', 'data'=>new Object()]);
            }
        }
        else{
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }

        $ipAddress=$_SERVER["REMOTE_ADDR"];

        if( !AttendanceHelper::isInternalIp($ipAddress) ){
            FResponse::output(['code' => 20072,'msg' => '请在公司内网打卡', 'data'=>new Object()]);
        }

        if(!$userInfo['card_no']){
            FResponse::output(['code' => 20070,'msg' => '您还未设置员工编号', 'data'=>new Object()]);
        }

        //写入mysql
        $connection = Yii::$app->db;
        $membersModel = new MembersModel();
        $member = $membersModel->findOne($this->userInfo['u_id']);
        $badgenumber = $member->card_no;
        $day = strtotime(date('Y-m-d'));
        //如果在0点至早6点打卡  视为前一天的下班打卡时间
        if(time() >= $day && time() < strtotime(date('Y-m-d').' 6:00:00')) {
            $day = strtotime('-1 day',$day);
        }
        $ret = $connection->createCommand()->insert('oa_checktime', [
            'badgenumber' => $badgenumber,
            'checktime' => time(),
            'day' => $day
        ])->execute();
        if($ret){
            //获取工作日时间设置
            $timeSet = \app\modules\attendance\delegate\AttendanceDelegate::getTimeSet();
            //获取我的考勤
            $res = AttendanceDelegate::getDateCheckTime($this->userInfo['u_id'], time());
            //处理我的考勤数据
            if(count($res)>0){
                $res = \app\modules\attendance\helper\AttendanceHelper::setMyAttend($timeSet,$res);
            }else{
                $res[0] =["status"=>1,"NAME"=>$this->userInfo['real_name'],"onTime"=>'',"offTime"=>'',"showDate"=>date('Y-m-d',time())];
            }
            FResponse::output(['code' => 20000,'msg' => 'ok', 'data'=>['curCheckTime'=>$res[0]]]);
            //FResponse::output(['code' => 20000,'msg' => 'success', 'data'=>new Object()]);
        }else{
            FResponse::output(['code' => 20076,'msg' => '签到失败', 'data'=>new Object()]);
        }

        /*
        //数据库连接
        $link = mssql_connect('ser2005', 'OA', 'zqlt2015');

        if(!$link) {
            FResponse::output(['code' => 20071,'msg' => '服务器连接失败', 'data'=>new Object()]);
        }

        //获取用户的打卡机id
        $sql = " SELECT C.*,u.BADGENUMBER FROM USERINFO as u LEFT JOIN CHECKINOUT AS C ON u.USERID = C.USERID WHERE u.BADGENUMBER=".$userInfo['card_no'];
        //查询出前一天的打卡记录
        $query = mssql_query($sql, $link);
        $row = mssql_fetch_array($query);
        //插入打卡记录2108-12-29 21:00:55.000
        $dateTime = date("Y-m-d H:i:s").'.000';
        $insertSql = "INSERT INTO CHECKINOUT VALUES('".$row['USERID']."','".$dateTime."','I','0','2','','0','6027153300123','1')";
        if(mssql_query($insertSql)){
            FResponse::output(['code' => 20000,'msg' => 'ok', 'data'=>new Object()]);
        }else{
            FResponse::output(['code' => 20076,'msg' => '签到失败', 'data'=>new Object()]);
        }
        */
    }

    //打卡提醒
    public function actionClockRemind(){
        //获取工作日时间设置
        $timeSet = \app\modules\attendance\delegate\AttendanceDelegate::getTimeSet();
        $data = ['begin_time'=>$timeSet['begin_time'],'end_time'=>$timeSet['end_time']];
        $result = [
            'code'=>20000,
            'msg'=>'获取数据成功！',
            'data'=>$data
        ];
        FResponse::output($result);
    }

}