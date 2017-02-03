<?php
/**
 * Created by PhpStorm.
 * User: 李正运
 * Date: 2016/8/24
 * Time: 14:37
 */

namespace app\modules\apply\delegate;

use app\models\AnnualLeaveModel;
use app\models\ApplyAttachmentModel;
use app\models\ApplyBaseModel;
use app\models\ApplyLeaveModel;
use app\models\AttendanceModel;
use app\models\MembersModel;
use app\models\VacationInventoryModel;
use app\models\VacationSetModel;
use app\models\VacationUsedModel;
use app\modules\apply\helper\LeaveHelper;
use yii;

class LeaveDelegate
{
    /**
     * 发起申请数据保存处理
    */
    public static function saveLeaveApply($userInfo,$data)
    {
        //使用记录
        $usedData = [];
        switch($data['type']){
            case 1://年假
                //判断员工是否转正
                $info = MembersModel::getUserMessage($userInfo['u_id'],'is_formal');
                if($info['is_formal']!=1){
                    return ['code'=>0,'msg'=>'该员工未转正！'];
                }
                //获取年假数量
                $arrAnnLeaveSum = AnnualLeaveModel::getAnnualLeave($userInfo['u_id']);
                $daysum = $arrAnnLeaveSum['normal_leave']+$arrAnnLeaveSum['delay_leave'];
                if ($daysum < $data['leave_sum']) {
                    return ['code'=>0,'msg'=>'申请失败，年假数不够！'];
                }
                $alModel = AnnualLeaveModel::findOne(['u_id'=>$userInfo['u_id']]);
                if($data['leave_sum'] <= $arrAnnLeaveSum['delay_leave']){//请假天数不超过顺延年假数
                    //顺延年假
                    $usedData[0]['used_num'] = $data['leave_sum'];
                    $usedData[0]['vacation_type'] = 2;
                    $alModel->delay_leave = $arrAnnLeaveSum['delay_leave']-$data['leave_sum'];
                }else{
                    if($arrAnnLeaveSum['delay_leave']>0){
                        //顺延年假
                        $usedData[0]['used_num'] = $arrAnnLeaveSum['delay_leave'];
                        $usedData[0]['vacation_type'] = 2;
                        $alModel->delay_leave = 0;
                        //年假
                        $usedData[1]['used_num'] = $data['leave_sum'] - $arrAnnLeaveSum['delay_leave'];
                        $usedData[1]['vacation_type'] = 1;
                        $alModel->normal_leave = $arrAnnLeaveSum['normal_leave']-($data['leave_sum']-$arrAnnLeaveSum['delay_leave']);
                    }else{
                        //年假
                        $usedData[1]['used_num'] = $data['leave_sum'];
                        $usedData[1]['vacation_type'] = 1;
                        $alModel->normal_leave = $arrAnnLeaveSum['normal_leave']-$data['leave_sum'];
                    }
                }
                if(!$alModel->save(false)){
                    return ['code'=>0,'msg'=>'申请失败，请重试！'];
                }
                break;
            case 2://调休
                //可调休天数
                $daysum = self::getInventorySum($userInfo['u_id']);
                $arrInvInfo = self::getInventoryInfo($userInfo['u_id'],$data['leave_sum']);
                if($daysum < $data['leave_sum']){
                    return ['code'=>0,'msg'=>'申请失败，调休数不够！'];
                }
                foreach ($arrInvInfo as $key => $val) {
                    $arrVacInv[] = $val['id'];
                    $modelInv = VacationInventoryModel::findOne($val['id']);
                    $modelInv->is_valid = 1;
                    if (!$modelInv->save()) {
                        return ['code'=>0,'msg'=>'申请失败，请重试！'];
                    }
                }
                $usedData[0]['used_num'] = $data['leave_sum'];
                $usedData[0]['vacation_type'] = 3;
                //保存调休ID
                $data['inventory_id'] = implode(',', $arrVacInv);
                break;
            case 3://带薪病假
                //获取请假时间月份带薪病假的天数
                $sickSum = self::getSickLeaveSum($userInfo['u_id'],$data['begin_time']);
                if ($sickSum < $data['leave_sum']) {
                    return ['code'=>0,'msg'=>'每月只能提交一天带薪病假申请，您已超过限制！'];
                }
                $usedData[0]['used_num'] = $data['leave_sum'];
                $usedData[0]['vacation_type'] = 4;
                $daysum = self::getYearSickLeaveSum($userInfo['u_id'],$data['begin_time']);
                break;
            default:
                $usedData[0]['used_num'] = $data['leave_sum'];
                if($data['type']==4 || $data['type']==5){
                    $usedData[0]['vacation_type'] = $data['type']+1;
                }else{
                    $usedData[0]['vacation_type'] = $data['type'];
                }
        }

        //添加年假和调休日志
        if($data['type']==1 || $data['type']==2){
            $arrLog = array();
            $arrLog['u_id'] = $userInfo['u_id'];
            $arrLog['log_type'] = $data['type']==1?2:1;//1调休 2年假
            $arrLog['log_content'] = $userInfo['real_name'].'提出了请假申请';
            $arrLog['create_time'] = time();
            $arrLog['operator_id'] = $userInfo['u_id'];
            $arrLog['value_before'] = $daysum;
            $arrLog['value_after'] = $daysum-$data['leave_sum'];
            $res = \Yii::$app->db->createCommand()->insert('oa_vacation_log',$arrLog)->execute();
            if(!$res){
                return ['code'=>0,'msg'=>'申请失败，请重试！'];
            }
        }

        return ['usedData'=>$usedData,'data'=>$data];
    }

    /**
     * 获取可调休天数
     * $uid 用户ID
     */
    public static function getInventorySum($u_id)
    {
        $data = VacationInventoryModel::find()->where('u_id=:u_id and is_valid=0 and expire_time>='.time(),[':u_id'=>$u_id])->count();
        return $data/2;
    }

    /**
     * 获取可调休天数详情
     * $uid 用户ID
     * $day 调休天数
     */
    public static function getInventoryInfo($u_id,$day){
        $count = $day*2;
        $data = VacationInventoryModel::find()->where('u_id=:u_id and is_valid=0 and expire_time>='.time(),[':u_id'=>$u_id])->orderBy('creat_time ASC')->limit($count)->asArray()->all();
        return $data;
    }

    /**
     * 获取带薪病假天数
     * $uid 用户ID
     * $time  请假日期
     */
    public static function getSickLeaveSum($uid,$time){
        //获取剩余带薪病假天数
        $sickLeaveSum = self::getYearSickLeaveSum($uid,$time);
        /*echo '当年剩余带薪病假'.$sickLeaveSum."<br/>";*/
        if($sickLeaveSum<=0){
            return 0;
        }else{
            //获取当月的第一天
            /*echo '当月第一天'.date('Y-m-01 00:00:00', $time)."<br/>";
            echo strtotime(date('Y-m-01 00:00:00', $time))."<br/>";*/
            $BeginDate = strtotime(date('Y-m-01 00:00:00', $time));
            //获取当月的最后一天
            $curTime=date('Y-m-1',$time);
            $EndDate = strtotime(date('Y-m-d 23:59:59', strtotime("$curTime +1 month -1 day")));
            /*echo '当月最后一天'.date('Y-m-d 23:59:59', strtotime("$curTime +1 month -1 day"))."<br/>";
            echo strtotime(date('Y-m-d 23:59:59', strtotime("$curTime +1 month -1 day")))."<br/>";*/
            $ySickLeaveSum = VacationUsedModel::find()->where('vacation_type=4 and status=1 and start_time>='.$BeginDate." and start_time<=".$EndDate." and u_id=:uid",[':uid'=>$uid])->sum('used_num');
            /*echo '当月已使用带薪病假'.$ySickLeaveSum;die;*/
            if($ySickLeaveSum>=1){
                return 0;
            }else{
                return 1-$ySickLeaveSum;
            }
        }
    }

    /**
     * 获取一年中带薪病假的天数
     */
    public static function getYearSickLeaveSum($uid,$time){
        //获取当年第一天
        /*echo '当年的第一天'.date('Y-1-1 00:00:00',$time)."<br/>";
        echo strtotime(date('Y-1-1 00:00:00',$time))."<br/>";*/
        $yStartTime = strtotime(date('Y-1-1 00:00:00',$time));
        //获取一年的最后一天
        $Y = date('Y',$time);
        $yNextStartTime=($Y+1).'-01-01';
        /*echo '当年的最后一天'.date('Y-m-d 23:59:59', strtotime("$yNextStartTime -1 day"))."<br/>";
        echo strtotime(date('Y-m-d 23:59:59', strtotime("$yNextStartTime -1 day")))."<br/>";*/
        $yEndTime = strtotime(date('Y-m-d 23:59:59', strtotime("$yNextStartTime -1 day")));
        $sickLeaveSum = VacationUsedModel::find()->where('vacation_type=4 and status=1 and start_time>='.$yStartTime." and start_time<=".$yEndTime." and u_id=:uid",[':uid'=>$uid])->sum('used_num');
        /*echo '当年已使用的带薪病假'.$sickLeaveSum."<br/>";*/
        //获取一年带薪病假的天数
        $leaveConf = \Yii::$app->params['vacation'];
        if($sickLeaveSum>=$leaveConf['sick_leave_sum']){
            return 0;
        }else if(empty($sickLeaveSum)){
            return $leaveConf['sick_leave_sum'];
        }else{
            return $leaveConf['sick_leave_sum']-$sickLeaveSum;
        }
    }

    /**
     * 请假申请审批最后一步通过处理
    */
    public static function setLeaveSum(ApplyBaseModel $apply,$data,$userInfo)
    {
        if(!(isset($data->leave_sum) && $data->leave_sum>0)){
            return ['code'=>0,'msg'=>'请输入正确的请假时长！'];
        }
        if(!preg_match("/^(?!0*$)[0-9]+(\\.(?=.*[0,5])[0,5]{1})?$/",$data->leave_sum)){
            return ['code'=>0,'msg'=>'休假时长必须大于零且以半天为基数！'];
        }
        $alModel = ApplyLeaveModel::findOne($apply->detail_id);

        //获取申请人信息
        $applyUserInfo = MembersModel::find()->where('u_id=:u_id',[':u_id'=>$apply->applyer])->asArray()->one();

        $arrTempApplyInfo['begin_time'] = $alModel->begin_time;
        $arrTempApplyInfo['end_time'] = $alModel->end_time;

        //修改考勤状态
        $begin_time = strtotime(date('Y-m-d',$alModel->begin_time));
        $end_time = strtotime(date('Y-m-d',$alModel->end_time));
        $tempAttDay = 0;
        $strtotimeday = strtotime("+$tempAttDay day",$begin_time);

        if($alModel->leave_sum == $data->leave_sum){//等于之前的天数
            return self::updateAttend($tempAttDay,$data->leave_sum,$strtotimeday,$apply->applyer,$alModel->type,$begin_time);
        }else{
            //使用记录
            $usedData = [];
            switch($alModel->type){
                case 1://年假
                    if($alModel->leave_sum < $data->leave_sum){//大于之前的天数
                        //获取可用年假天数
                        $arrAnnLeaveSum = AnnualLeaveModel::getAnnualLeave($apply->applyer);
                        $annLeaveSum = $arrAnnLeaveSum['normal_leave']+$arrAnnLeaveSum['delay_leave']+$alModel->leave_sum;
                        if($annLeaveSum<$data->leave_sum){
                            return ['code'=>0,'msg'=>'失败，年假数不足'];
                        }
                        $temp_leave_sum = $data->leave_sum-$alModel->leave_sum;
                        $almModel = AnnualLeaveModel::findOne(['u_id'=>$apply->applyer]);
                        if($temp_leave_sum <= $arrAnnLeaveSum['delay_leave']){//请假天数不超过顺延年假数
                            //顺延年假
                            $usedData[0]['used_num'] = $temp_leave_sum;
                            $usedData[0]['vacation_type'] = 2;
                            $almModel->delay_leave = $arrAnnLeaveSum['delay_leave']-$temp_leave_sum;
                        }else{
                            if($arrAnnLeaveSum['delay_leave']>0){
                                //顺延年假
                                $usedData[0]['used_num'] = $arrAnnLeaveSum['delay_leave'];
                                $usedData[0]['vacation_type'] = 2;
                                $almModel->delay_leave = 0;
                                //年假
                                $usedData[1]['used_num'] = $temp_leave_sum - $arrAnnLeaveSum['delay_leave'];
                                $usedData[1]['vacation_type'] = 1;
                                $almModel->normal_leave = $arrAnnLeaveSum['normal_leave']-($temp_leave_sum-$arrAnnLeaveSum['delay_leave']);
                            }else{
                                //年假
                                $usedData[1]['used_num'] = $temp_leave_sum;
                                $usedData[1]['vacation_type'] = 1;
                                $almModel->normal_leave = $arrAnnLeaveSum['normal_leave']-$temp_leave_sum;
                            }
                        }
                        if(!$almModel->save(false)){
                            return ['code'=>0,'msg'=>'申请失败，请重试！'];
                        }
                        //添加使用日志
                        $resLog = self::addLog($alModel->type,$applyUserInfo,$arrAnnLeaveSum['normal_leave']+$arrAnnLeaveSum['delay_leave'],($arrAnnLeaveSum['normal_leave']+$arrAnnLeaveSum['delay_leave'])-($data->leave_sum-$alModel->leave_sum),$userInfo);
                        if($resLog['code']==0){
                            return $resLog;
                        }
                        //添加使用日志
                        $resUsedLog = self::addUsedLog($usedData,$arrTempApplyInfo,$apply->applyer,$apply->apply_id);
                        if($resUsedLog['code']==0){
                            return $resUsedLog;
                        }
                    }else{//小于之前的天数
                        $is_delay_leave = false;//判断是否使用了顺延年假
                        //获取可用年假天数
                        $arrAnnLeaveSum = AnnualLeaveModel::getAnnualLeave($apply->applyer);
                        $annLeaveSum = $arrAnnLeaveSum['normal_leave']+$arrAnnLeaveSum['delay_leave'];
                        //获取年假过期时间配置
                        $arrVacationSet = VacationSetModel::find()->asArray()->one();
                        $tempMD = date('Y-'.$arrVacationSet['vacation_expire'],time());
                        $MD = date('-m-d',strtotime("$tempMD +1 day"));

                        //获取该申请年假使用记录
                        $usedInfo = VacationUsedModel::find()->where('apply_id=:apply_id',[':apply_id'=>$apply->apply_id])->orderBy('vacation_type desc')->asArray()->all();
                        if(is_array($usedInfo)){
                            $temp=['normal_leave'=>0,'delay_leave'=>0];
                            $tempday = 0;
                            foreach($usedInfo as $key=>$val){
                                //审批时间和申请时间同年，且大于3月31日
                                if(date('Y',$val['create_time'])==date('Y',time()) && time()>=strtotime(date('Y',$val['create_time']).$MD)){
                                    if($val['vacation_type']==1){//普通年假
                                        if($is_delay_leave){//使用了顺延年假
                                            if($tempday==0){
                                                $temp['normal_leave'] = $val['used_num'];
                                                $UpdateData=array('status'=>-1);
                                                $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id.' and id='.$val['id'])->execute();
                                                if(!$res){
                                                    return false;
                                                }
                                            }else{
                                                $temp['normal_leave'] = $val['used_num']-$tempday;
                                                $UpdateData=array('used_num'=>$tempday);
                                                $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id.' and id='.$val['id'])->execute();
                                                if(!$res){
                                                    return false;
                                                }
                                            }
                                        }else{
                                            $temp['normal_leave'] = $val['used_num']-$data->leave_sum;
                                            $UpdateData=array('used_num'=>$data->leave_sum);
                                            $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id.' and id='.$val['id'])->execute();
                                            if(!$res){
                                                return false;
                                            }
                                        }
                                    }else if($val['vacation_type']==2){//顺延年假
                                        $is_delay_leave = true;
                                        if($val['used_num']>=$data->leave_sum){
                                            $UpdateData=array('used_num'=>$data->leave_sum);
                                        }else{
                                            $UpdateData=array('used_num'=>$val['used_num']);
                                            $tempday = $data->leave_sum-$val['used_num'];
                                        }
                                        if($val['used_num']!=$data->leave_sum){
                                            $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id.' and id='.$val['id'])->execute();
                                            if(!$res){
                                                return false;
                                            }
                                        }
                                    }
                                    //审批时间和申请时间同年，且小于等于3月31日
                                }else if(date('Y',$val['create_time'])==date('Y',time()) && time()<strtotime(date('Y',$val['create_time']).$MD)){
                                    if($val['vacation_type']==1){//普通年假
                                        if($is_delay_leave) {//使用了顺延年假
                                            if($tempday==0){
                                                $temp['normal_leave'] = $val['used_num'];
                                                $UpdateData=array('status'=>-1);
                                            }else{
                                                $temp['normal_leave'] = $val['used_num']-$tempday;
                                                $UpdateData=array('used_num'=>$tempday);
                                            }
                                            $temp['normal_leave'] = $val['used_num'];
                                        }else{
                                            $temp['normal_leave'] = $val['used_num']-$data->leave_sum;
                                            $UpdateData=array('used_num'=>$data->leave_sum);
                                        }
                                    }else if($val['vacation_type']==2){//顺延年假
                                        $is_delay_leave = true;
                                        if($val['used_num']>=$data->leave_sum){
                                            $UpdateData=array('used_num'=>$data->leave_sum);
                                            $temp['delay_leave'] = $val['used_num']-$data->leave_sum;
                                        }else{
                                            $UpdateData=array('used_num'=>$val['used_num']);
                                            $tempday = $data->leave_sum-$val['used_num'];
                                        }
                                    }
                                    if($val['used_num']!=$data->leave_sum){
                                        $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id.' and id='.$val['id'])->execute();
                                        if(!$res){
                                            return false;
                                        }
                                    }
                                    //第二年审批，且小于等于3月31日
                                }else if(time()>=strtotime((date('Y',$val['create_time'])+1).'-1-1') && time()<strtotime((date('Y',$val['create_time'])+1).$MD)){
                                    if($val['vacation_type']==1){
                                        if($is_delay_leave) {//使用了顺延年假
                                            if($tempday==0){
                                                $temp['delay_leave'] = $val['used_num'];
                                                $UpdateData=array('status'=>-1,'vacation_type'=>2);
                                                $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id.' and id='.$val['id'])->execute();
                                                if(!$res){
                                                    return false;
                                                }
                                            }else{
                                                $temp['delay_leave'] = $val['used_num']-$tempday;
                                                $UpdateData=array('used_num'=>$tempday);
                                                $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id.' and id='.$val['id'])->execute();
                                                if(!$res){
                                                    return false;
                                                }
                                            }
                                        }else{
                                            $temp['delay_leave'] = $val['used_num']-$data->leave_sum;
                                            $UpdateData=array('used_num'=>$data->leave_sum);
                                            $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id.' and id='.$val['id'])->execute();
                                            if(!$res){
                                                return false;
                                            }
                                        }
                                    }else if($val['vacation_type']==2){
                                        $is_delay_leave = true;
                                        if($val['used_num']>=$data->leave_sum){
                                            $UpdateData=array('used_num'=>$data->leave_sum);
                                        }else{
                                            $UpdateData=array('used_num'=>$val['used_num']);
                                            $tempday = $data->leave_sum-$val['used_num'];
                                        }
                                        if($val['used_num']!=$data->leave_sum){
                                            $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id.' and id='.$val['id'])->execute();
                                            if(!$res){
                                                return false;
                                            }
                                        }
                                    }
                                    //其他情况
                                }else{
                                    if($val['vacation_type']==1){
                                        if($tempday==0){
                                            $UpdateData=array('status'=>-2);
                                            $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id.' and id='.$val['id'])->execute();
                                            if(!$res){
                                                return false;
                                            }
                                        }else{
                                            $UpdateData=array('used_num'=>$tempday);
                                            $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id.' and id='.$val['id'])->execute();
                                            if(!$res){
                                                return false;
                                            }
                                        }
                                    }else if($val['vacation_type']==2){
                                        if($val['used_num']>=$data->leave_sum){
                                            $UpdateData=array('used_num'=>$data->leave_sum);
                                        }else{
                                            $UpdateData=array('used_num'=>$val['used_num']);
                                            $tempday = $data->leave_sum-$val['used_num'];
                                        }
                                        if($val['used_num']!=$data->leave_sum){
                                            $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id.' and id='.$val['id'])->execute();
                                            if(!$res){
                                                return false;
                                            }
                                        }
                                    }
                                }
                            }
                            $anLeaveModel = AnnualLeaveModel::findOne(['u_id'=>$apply->applyer]);

                            //添加使用日志
                            $resLog = self::addLog($alModel->type,$applyUserInfo,$annLeaveSum,$anLeaveModel->normal_leave+$temp['normal_leave']+$anLeaveModel->delay_leave+$temp['delay_leave'],$userInfo);
                            if($resLog['code']==0){
                                return $resLog;
                            }

                            $anLeaveModel->normal_leave = $anLeaveModel->normal_leave+$temp['normal_leave'];
                            $anLeaveModel->delay_leave = $anLeaveModel->delay_leave+$temp['delay_leave'];
                            $res = $anLeaveModel->save(false);
                            if(!$res){
                                return false;
                            }
                            $temp=['normal_leave'=>0,'delay_leave'=>0];
                        }
                    }
                    break;
                case 2://调休
                    //获取可用调休天数
                    $overtimeSum = self::getInventorySum($apply->applyer);
                    $overtimeSumBefore = $overtimeSum;
                    if($alModel->leave_sum < $data->leave_sum){//大于之前的天数
                        $overtimeSum = $overtimeSum+$alModel->leave_sum;
                        if($overtimeSum<$data->leave_sum){
                            return ['code'=>0,'msg'=>'失败，调休数不足'];
                        }
                        $arrInvInfo = self::getInventoryInfo($apply->applyer,$data->leave_sum-$alModel->leave_sum);
                        foreach ($arrInvInfo as $key => $val) {
                            $arrVacInv[] = $val['id'];
                            $modelInv = VacationInventoryModel::findOne($val['id']);
                            $modelInv->is_valid = 1;
                            if (!$modelInv->save()) {
                                return ['code'=>0,'msg'=>'申请失败，请重试！'];
                            }
                        }
                        $usedData[0]['used_num'] = $data->leave_sum;
                        $usedData[0]['vacation_type'] = 3;
                        //保存调休ID
                        $alModel->inventory_id = $alModel->inventory_id.','.implode(',', $arrVacInv);
                        //添加使用日志
                        $resUsedLog = self::addUsedLog($usedData,$arrTempApplyInfo,$apply->applyer,$apply->apply_id);
                        if($resUsedLog['code']==0){
                            return $resUsedLog;
                        }
                    }else{//小于之前的天数
                        //将调休假设置为未使用
                        $arrInventory_id = explode(',',$alModel->inventory_id);
                        rsort($arrInventory_id);
                        $arrTempInventory_id = array_slice($arrInventory_id,0,count($arrInventory_id)-($data->leave_sum*2));
                        $res = \Yii::$app->db->createCommand()->update('oa_vacation_inventory', ['is_valid' => 0],['in','id',$arrTempInventory_id])->execute();
                        if(!$res){
                            return false;
                        }
                        sort($arrInventory_id);
                        $alModel->inventory_id = implode(',',array_slice($arrInventory_id,0,($data->leave_sum*2)));
                    }
                    //添加使用日志
                    $overtimeSumAfter = self::getInventorySum($apply->applyer);
                    $resLog = self::addLog($alModel->type,$applyUserInfo,$overtimeSumBefore,$overtimeSumAfter,$userInfo);
                    if($resLog['code']==0){
                        return $resLog;
                    }
                    break;
                case 3://带薪病假
                    if($data->leave_sum>1){
                        return ['code'=>0,'msg'=>'失败，带薪病假数不足'];
                    }
                    if($alModel->leave_sum < $data->leave_sum){//大于之前的天数
                        //获取可用带薪病假天数
                        $sum = self::getYearSickLeaveSum($apply->applyer,$begin_time);
                        $sum = $sum+$alModel->leave_sum;
                        if($sum<$data->leave_sum){
                            return ['code'=>0,'msg'=>'失败，带薪病假数不足'];
                        }
                    }
                    $vacModel = VacationUsedModel::find()->where('apply_id=:apply_id',[':apply_id'=>$apply->apply_id])->one();
                    $vacModel->used_num = $data->leave_sum;
                    if(!$vacModel->save(false)){
                        return false;
                    }
                    break;
            }

            $alModel->leave_sum = $data->leave_sum;
            if($alModel->save(false)){
                return self::updateAttend($tempAttDay,$data->leave_sum,$strtotimeday,$apply->applyer,$alModel->type,$begin_time);
            }else{
                return false;
            }

        }
    }

    /**
     * 审批更新考勤
    */
    public static function updateAttend($tempday,$leave_sum,$strtotimeday,$u_id,$type,$begin_time)
    {
        while($tempday < ceil($leave_sum)){
            $attendObj='';
            $attendObj = AttendanceModel::find()->where('workDate=:workDate and u_id=:u_id',[':workDate'=>$strtotimeday,':u_id'=>$u_id])->one();
            if(!isset($attendObj->aid)){
                return ['code'=>0,'msg'=>'尚未生成打卡记录，不能作最后一步审批'];
            }
            $attendObj->status = 7;
            $attendObj->substatus = $type;
            if(!$attendObj->save(false)){
                return false;
            }
            $tempday++;
            $strtotimeday = strtotime("+$tempday day",$begin_time);
        }
        return true;
    }

    /**
     * 添加年假和调休日志
    */
    public static function addLog($type ,$userInfo,$daysum,$leave_sum,$curUserInfo)
    {
        $arrLog = array();
        $arrLog['u_id'] = $userInfo['u_id'];
        $arrLog['log_type'] = $type==1?2:1;//1调休 2年假
        $arrLog['log_content'] = $curUserInfo['real_name'].'做最后一步审批修改';
        $arrLog['create_time'] = time();
        $arrLog['operator_id'] = $curUserInfo['u_id'];
        $arrLog['value_before'] = $daysum;
        $arrLog['value_after'] = $leave_sum;
        $res = \Yii::$app->db->createCommand()->insert('oa_vacation_log',$arrLog)->execute();
        if(!$res){
            return ['code'=>0,'msg'=>'失败，请重试!'];
        }else{
            return ['code'=>1];
        }
    }

    /**
     * 添加使用日志
    */
    public static function addUsedLog($usedData,$arrData,$u_id,$apply_id)
    {
        $usedData = LeaveHelper::leaveUsedFormat($usedData,$arrData,$u_id,$apply_id);
        $usedRes = self::saveLeaveUsed($usedData);
        return $usedRes;
    }

    /**
     * 请假申请保存附件
     * $arrLeaveAtt  附件数组
    */
    public static function saveLeaveAtt($arrLeaveAtt)
    {
        $attRes = false;
        $attRes = \Yii::$app->db->createCommand()->batchInsert('oa_apply_attachment',['apply_id','file_name','real_name','file_size','file_path','file_type','create_time'],$arrLeaveAtt)->execute();
        return $attRes;
    }

    /**
     * 保存假期使用日志
     * $usedData
    */
    public static function saveLeaveUsed($usedData,$apply_id=0)
    {
        if($apply_id!=0){
            $delRes = \Yii::$app->db->createCommand()->delete('oa_vacation_used', 'apply_id=:apply_id', array(':apply_id' => $apply_id))->execute();
            if (!$delRes) {
                return ['code' => 0, 'msg' => '申请失败，请重试！'];
            }
        }
        foreach($usedData as $key=>$val){
            $insertRes = \Yii::$app->db->createCommand()->insert('oa_vacation_used', $val)->execute();
            if (!$insertRes) {
                return ['code' => 0, 'msg' => '申请失败，请重试！'];
            }
        }
        return ['code' => 1];
    }

    /**
     * 请假申请驳回或撤回做数据处理
     * $u_id  当前用户ID
     * $apply  ApplyBaseModel对象
    */
    public static function  returnLeaveData($u_id,ApplyBaseModel $apply,$msgType='驳回')
    {
        //获取当前用户信息
        $curUser = MembersModel::find()->select('real_name,u_id')->where('u_id=:u_id',[':u_id'=>$u_id])->asArray()->one();

        //获取请假申请详情
        $arrLeaveInfo = ApplyLeaveModel::find()->where('id=:id',[':id'=>$apply->detail_id])->asArray()->one();

        if($arrLeaveInfo['type'] == 1){//年假
            //年假获取写日志变更前的值
            $arrAnnLeaveSum = AnnualLeaveModel::getAnnualLeave($apply->applyer);
            $daysum = $arrAnnLeaveSum['normal_leave']+$arrAnnLeaveSum['delay_leave'];

            //将年假设置为未使用

            //获取年假过期时间配置
            $arrVacationSet = VacationSetModel::find()->asArray()->one();
            $tempMD = date('Y-'.$arrVacationSet['vacation_expire'],time());
            $MD = date('-m-d',strtotime("$tempMD +1 day"));

            //获取该申请年假使用记录
            $usedInfo = VacationUsedModel::find()->where('apply_id=:apply_id',[':apply_id'=>$apply->apply_id])->asArray()->all();
            if(is_array($usedInfo)){
                $temp=['normal_leave'=>0,'delay_leave'=>0];
                foreach($usedInfo as $key=>$val){
                    //审批时间和申请时间同年，且大于3月31日
                    if(date('Y',$val['create_time'])==date('Y',time()) && time()>=strtotime(date('Y',$val['create_time']).$MD)){
                        if($val['vacation_type']==1){//普通年假
                            $temp['normal_leave'] = $val['used_num'];
                            $UpdateData=array('status'=>-1);
                            $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id.' and id='.$val['id'])->execute();
                            if(!$res){
                                return false;
                            }
                        }else if($val['vacation_type']==2){//顺延年假
                            $UpdateData=array('status'=>-2);
                            $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id.' and id='.$val['id'])->execute();
                            if(!$res){
                                return false;
                            }
                        }
                        //审批时间和申请时间同年，且小于等于3月31日
                    }else if(date('Y',$val['create_time'])==date('Y',time()) && time()<strtotime(date('Y',$val['create_time']).$MD)){
                        if($val['vacation_type']==1){//普通年假
                            $temp['normal_leave'] = $val['used_num'];
                        }else if($val['vacation_type']==2){//顺延年假
                            $temp['delay_leave'] = $val['used_num'];
                        }
                        $UpdateData=array('status'=>-1);
                        $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id.' and id='.$val['id'])->execute();
                        if(!$res){
                            return false;
                        }
                        //第二年审批，且小于等于3月31日
                    }else if(time()>=strtotime((date('Y',$val['create_time'])+1).'-1-1') && time()<strtotime((date('Y',$val['create_time'])+1).$MD)){
                        if($val['vacation_type']==1){
                            $temp['delay_leave'] = $val['used_num'];
                            $UpdateData=array('status'=>-1,'vacation_type'=>2);
                            $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id.' and id='.$val['id'])->execute();
                            if(!$res){
                                return false;
                            }
                        }else if($val['vacation_type']==2){
                            $UpdateData=array('status'=>-2);
                            $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id.' and id='.$val['id'])->execute();
                            if(!$res){
                                return false;
                            }
                        }
                        //其他情况
                    }else{
                        $UpdateData=array('status'=>-2);
                        $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id.' and id='.$val['id'])->execute();
                        if(!$res){
                            return false;
                        }
                    }
                }
                $anLeaveModel = AnnualLeaveModel::findOne(['u_id'=>$apply->applyer]);
                $anLeaveModel->normal_leave = $anLeaveModel->normal_leave+$temp['normal_leave'];
                $anLeaveModel->delay_leave = $anLeaveModel->delay_leave+$temp['delay_leave'];
                $res = $anLeaveModel->save(false);
                if(!$res){
                    return false;
                }
                $temp=['normal_leave'=>0,'delay_leave'=>0];
            }
            $value_after = $anLeaveModel->normal_leave+$anLeaveModel->delay_leave;

        }else if($arrLeaveInfo['type'] == 2){//调休
            //调休获取写日志变更前的值
            $daysum = self::getInventorySum($apply->applyer);

            //将调休假设置为未使用
            $res = \Yii::$app->db->createCommand()->update('oa_vacation_inventory', ['is_valid' => 0],['in','id',explode(',',$arrLeaveInfo['inventory_id'])])->execute();
            if(!$res){
                return false;
            }
            $value_after = $daysum+$arrLeaveInfo['leave_sum'];

        }else{//其他请假
            $UpdateData=array('status'=>-1);
            $res = \Yii::$app->db->createCommand()->update('oa_vacation_used', $UpdateData, 'apply_id=' . $apply->apply_id)->execute();
            if(!$res){
                return false;
            }
        }

        //年假或年假保存使用记录
        if($arrLeaveInfo['type'] == 1 || $arrLeaveInfo['type'] == 2){
            $arrLog = array();
            $arrLog['u_id'] = $apply->applyer;
            $arrLog['log_type'] = $arrLeaveInfo['type']==1?2:1;//1调休 2年假
            $arrLog['log_content'] = $curUser['real_name'].$msgType.'了请假申请';
            $arrLog['create_time'] = time();
            $arrLog['operator_id'] = $curUser['u_id'];
            $arrLog['value_before'] = $daysum;
            $arrLog['value_after'] = $value_after;
            $res = \Yii::$app->db->createCommand()->insert('oa_vacation_log',$arrLog)->execute();
            if(!$res){
                return false;
            }
        }

        return true;

    }

    /**
     * 编辑请假申请删除旧附件
     * $apply_id
    */
    public static function delOldAtt($apply_id)
    {
        /*$res = true;
        $count = ApplyAttachmentModel::find()->where('apply_id=:apply_id',[':apply_id'=>$apply_id])->count();
        if($count > 0){
            $res = ApplyAttachmentModel::deleteAll('apply_id=:apply_id',[':apply_id'=>$apply_id]);
        }*/
        $res = ApplyAttachmentModel::deleteAll('apply_id=:apply_id',[':apply_id'=>$apply_id]);
        if($res === false) {
            $res = false;
        }else {
            $res = true;
        }
        return $res;
    }

    /**
     * 读取附件
     * $apply_id
    */
    public static function getAtt($apply_id,$field=[])
    {
        $aaModel = ApplyAttachmentModel::find()->where('apply_id=:apply_id',[':apply_id'=>$apply_id]);
        if(count($field)>0){
            $arrAtt = $aaModel->select($field)->asArray()->all();
        }else{
            $arrAtt = $aaModel->asArray()->all();
        }
        return $arrAtt;
    }

    /**
     * 获取请假申请的类型
    */
    public static function getLeaveApplyType()
    {
        $res = [];
        $arrLeaveStatus = \Yii::$app->params['leavestatus'];
        $i=0;
        foreach($arrLeaveStatus as $key=>$val){
            $res[$i]['statuskey'] = $key;
            $res[$i]['statusstr'] = $val;
            $i++;
        }
        return $res;
    }

    /**
     * 获取请假申请考勤信息
     * $apply_id
    */
    public static function getClockTime($apply_id)
    {
        $res = [];
        $applyBaseInfo = ApplyBaseModel::find()->where('apply_id=:apply_id',[':apply_id'=>$apply_id])->asArray()->one();
        $applyLeaveInfo = ApplyLeaveModel::find()->where('id=:id',[':id'=>$applyBaseInfo['detail_id']])->asArray()->one();
        $res['leave_sum'] = $applyLeaveInfo['leave_sum'];
        if($applyLeaveInfo['leave_sum'] < 1){
            $arr_begin_time =date('Y-m-d',$applyLeaveInfo['begin_time']);
            $attendInfo = AttendanceModel::find()->select('aid,onTime,offTime')->where('workDate=:workDate and u_id =:u_id',[':workDate'=>strtotime($arr_begin_time),':u_id'=>$applyBaseInfo['applyer']])->asArray()->one();
            if(isset($attendInfo['aid'])){
                if(isset($attendInfo['onTime'])){
                    if(!empty($attendInfo['onTime'])){
                        $res['onTime'] = date('Y-m-d H:i',$attendInfo['onTime']);
                    }else{
                        $res['onTime'] = '--';
                    }
                    if(!empty($attendInfo['offTime'])){
                        $res['offTime'] = date('Y-m-d H:i',$attendInfo['offTime']);
                    }else{
                        $res['offTime'] = '--';
                    }
                }
            }
        }else{
            $res['onTime'] = '--';
            $res['offTime'] = '--';
        }
        return $res;
    }

}


/* while($tempday < ceil($data->leave_sum)){
                $attendObj='';
                $attendObj = AttendanceModel::find()->where('workDate=:workDate and u_id=:u_id',[':workDate'=>$strtotimeday,':u_id'=>$apply->applyer])->one();
                if(!isset($attendObj->aid)){
                    return ['code'=>0,'msg'=>'尚未生成打卡记录，不能作最后一步审批'];
                }
                $attendObj->status = 7;
                $attendObj->substatus = $alModel->type;
                if(!$attendObj->save(false)){
                    return false;
                }
                $tempday++;
                $strtotimeday = strtotime("+$tempday day",$begin_time);
            }
            return true;*/