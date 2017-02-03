<?php

namespace app\modules\apply\helper;

//控制器辅助类

use app\modules\project\delegate\ProjectDelegate;

class LeaveHelper {

    /**
     * 处理和验证提交请假申请的数据
     * $data 发起请假申请提交的数据
    */
    public static function setCreateLeaveApplyData($data)
    {
        $arrLeaveType = \Yii::$app->params['leavestatus'];
        if(!(isset($data['type']) && isset($arrLeaveType[$data['type']]))){
            return ['code'=>0,'msg'=>'请选择正确的请假类型！'];
        }
        if(!(isset($data['begin_time']) && isset($data['end_time']) && strlen($data['begin_time'])>0 && strlen($data['end_time'])>0 && (strtotime($data['begin_time'])<strtotime($data['end_time']))) ){
            return ['code'=>0,'msg'=>'请选择正确的请假时间！'];
        }
        $data['begin_time'] = strtotime($data['begin_time']);
        $data['end_time'] = strtotime($data['end_time']);
        if(!preg_match("/^(?!0*$)[0-9]+(\\.(?=.*[0,5])[0,5]{1})?$/",$data['leave_sum'])){
            return ['code'=>0,'msg'=>'合计时长必须大于零且以半天为基数！'];
        }
        if($data['leave_sum']>=1000){
            return ['code'=>0,'msg'=>'合计时长不能大于等于1000！'];
        }
        if(ceil(($data['end_time']-$data['begin_time'])/86400)<$data['leave_sum']){
            return ['code'=>0,'msg'=>'合计时长不能大于休假时间！'];
        }
        if(!(strlen($data['content'])>0)){
            return ['code'=>0,'msg'=>'详细说明不能为空！'];
        }
        if(!ProjectDelegate::isStrlen($data['content'],100)){
            return ['code'=>0,'msg'=>'详细说明不能大于100个字！'];
        }
        return $data;
    }

    /**
     * 处理请假申请附件格式
     * $arrLeaveAtt  附件数据
    */
    public static function leaveAttFormat($arrLeaveAtt,$apply_id)
    {
        $res = [];
        foreach($arrLeaveAtt as $key=>$val){
            $res[$key]['apply_id'] = $apply_id;
            $res[$key]['file_name'] = $val['file_name'];
            $res[$key]['real_name'] = $val['real_name'];
            $res[$key]['file_size'] = $val['file_size'];
            $res[$key]['file_path'] = $val['file_path'];
            $res[$key]['file_type'] = $val['file_type'];
            $res[$key]['create_time'] = time();
        }
        return $res;
    }

    /**
     * 设置使用日志格式
     * $data     假期使用日志
     * $arrData  请假申请表单数据
     * $u_id    当前用户ID
     * $apply_id
    */
    public static function leaveUsedFormat($data,$arrData,$u_id,$apply_id)
    {
        foreach($data as $key=>$val){
            $data[$key]['u_id'] = $u_id;
            $data[$key]['start_time'] = $arrData['begin_time'];
            $data[$key]['end_time'] = $arrData['end_time'];
            $data[$key]['create_time'] = time();
            $data[$key]['apply_id'] = $apply_id;
        }
        return $data;
    }
    
}