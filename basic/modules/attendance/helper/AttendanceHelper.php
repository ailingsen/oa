<?php

namespace app\modules\attendance\helper;

//控制器辅助类

class AttendanceHelper {

    const PAGE_SIZE1 =10;

    /**
     * 处理翻页数据
     */
    public static function setPage($type,$page)
    {
        $res=['offset'=>0,'limit'=>self::PAGE_SIZE1];
        if($type==1){
            $res['offset'] = self::PAGE_SIZE1 * ($page - 1);
            $res['limit'] =self::PAGE_SIZE1;
        }
        return $res;
    }

    /**
     * 处理我的考勤数据
    */
    public static function setMyAttend($set,$data)
    {
        $list = array();
        foreach($data as $key => $value) {
            $row = [];
            if ($value['onTime'] < strtotime(date('Y-m-d',$value['showDate']) .' '.$set['begin_time'])+60 && $value['offTime'] >= strtotime(date('Y-m-d',$value['showDate']) .' '.$set['end_time'])) {
                $row['status'] = 1;
            } else {
                $row['status'] = 0;
            }
            $n = date('N',$value['showDate']);
            $row['NAME'] = $value['real_name'];
            if($value['onTime']>0){
                $row['onTime'] = date('H:i:s', $value['onTime']);
            }else{
                $row['onTime'] = '';
            }
            if($value['offTime']>0){
                $row['offTime'] = date('H:i:s', $value['offTime']);
            }else{
                $row['offTime'] = '';
            }
            //$row['showDate'] = date('Y-m-d',$value['showDate']).'  ('.self::setWeek($value['showDate']).')';
            $row['showDate'] = date('Y-m-d',$value['showDate']);
            $list[] = $row;
        }

        return $list;
    }

    /**
     * 设置状态
    */
    public static function setStatus($data){
        //请假状态
        $arrLeaveStatus = \Yii::$app->params['leavestatus'];
        //考勤状态
        $arrAttStatus = \Yii::$app->params['attstatus'];
        foreach($data as $key=>$val){
            //时间格式化
            if($val['workDate']){
                $data[$key]['workDate_f'] = date('Y-m-d',$val['workDate']).' '.self::setWeek($val['workDate']);
            }else{
                $data[$key]['workDate_f'] = '--';
            }
            if($val['onTime']){
                $data[$key]['onTime_f'] = date('H:i:s',$val['onTime']);
            }else{
                $data[$key]['onTime_f'] = '--';
            }
            if($val['offTime']){
                $data[$key]['offTime_f'] = date('H:i:s',$val['offTime']);
            }else{
                $data[$key]['offTime_f'] = '--';
            }

            if($val['status']==7){
                //显示子状态
                $data[$key]['strstatus'] = $arrAttStatus[$val['status']].'（'.$arrLeaveStatus[$val['substatus']].'）';
            }else{
                $data[$key]['strstatus'] = $arrAttStatus[$val['status']];
            }
        }
        return $data;
    }

    public static function setWeek($date)
    {
        $weekArr = array(
            '1' => '星期一',
            '2' => '星期二',
            '3' => '星期三',
            '4' => '星期四',
            '5' => '星期五',
            '6' => '星期六',
            '7' => '星期日',
        );
        $n = date('N',$date);
        return $weekArr[$n];
    }

    //考勤统计数据格式处理
    public static function setAttendCountData($data)
    {
        foreach($data as $key=>$val){
            $data[$key]['begin_time'] = date('Y-m-d H:i',$val['begin_time']);
            $data[$key]['end_time'] = date('Y-m-d H:i',$val['end_time']);
        }
        return $data;
    }
    
}