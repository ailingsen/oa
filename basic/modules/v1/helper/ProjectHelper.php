<?php

namespace app\modules\v1\helper;

use Yii;

//控制器辅助类

class ProjectHelper {
    /**
     * 处理项目数据
     */
    public static function setProData($data,$isDegree=1)
    {
        if(is_array($data)){
            foreach($data as $key=>$val)
            {
                //处理项目的状态
                $data[$key]['status'] = self::setProStatus($val);
                //处理项目的进度
                if($isDegree==1){
                    if (isset($val['task'])) {
                        $data[$key]['degree'] = self::setProDegree($val['task']);
                    } else {
                        $data[$key]['degree']['degree'] = 0;
                    }
                }
                //处理项目时间
                $data[$key] = self::setTimeFormat($data[$key]);
            }
        }
        return $data;
    }

    /**
     * 处理项目的状态
     * $proinfo  array项目信息
     * 1未开始    2进行中-正常   3进行中-超时   4已完成
     */
    public static function setProStatus($proinfo)
    {
        $time = time();
        if($proinfo['complete']==1){
            return 4;
        }
        if($proinfo['begin_time']<=$time && $proinfo['end_time']>=$time){
            return 2;
        }
        if($proinfo['begin_time']>$time){
            return 1;
        }
        if($proinfo['end_time']<$time){
            return 3;
        }
    }

    /**
     * 处理项目的进度
     * $taskinfo  array项目所有任务数据
     */
    public static function setProDegree($taskinfo)
    {
        //完成数
        $fcount=0;
        //任务总数
        $count=count($taskinfo);
        foreach($taskinfo as $k=>$v){
            if($v['status']==4){
                $fcount++;
            }
        }
        //计算项目的进度
        if($count==0){
            return ['count'=>$count,'fcount'=>$fcount,'degree'=>0];
        }else{
            return ['count'=>$count,'fcount'=>$fcount,'degree'=>sprintf("%.2f",$fcount/$count)*100];
        }
    }

    /**
     * 处理项目时间格式化
     */
    public static function setTimeFormat($data)
    {
        if($data['delay_time']==0){
            $data['delay_time_f'] = '';
        }else{
            $data['delay_time_f'] = date('Y-m-d H:i',$data['delay_time']);
        }
        $data['begin_time_f'] = date('Y-m-d H:i',$data['begin_time']);
        $data['end_time_f'] = date('Y-m-d H:i',$data['end_time']);
        return $data;
    }

    public static function setDateFormat($time,$type=1)
    {
        if($type==1){
            return date('Y-m-d H:i:s',$time);
        }else{
            return date('Y-m-d H:i',$time);
        }
    }


}