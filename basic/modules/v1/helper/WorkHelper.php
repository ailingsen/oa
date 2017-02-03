<?php

namespace app\modules\v1\helper;

use Yii;

//控制器辅助类

class WorkHelper {
    public static function isInternalIp($ip) {
        $ipArr = [
            '113.247.222.58',
            '113.247.222.59',
            '113.247.222.60',
            '113.247.222.61',
            '113.247.222.62',
        ];
        if( in_array($ip,$ipArr) ) return true;

        $ip = ip2long($ip);
        $net_a = ip2long('10.255.255.255') >> 24; //A类网预留ip的网络地址
        $net_b = ip2long('172.31.255.255') >> 20; //B类网预留ip的网络地址
        $net_c = ip2long('192.168.255.255') >> 16; //C类网预留ip的网络地址

        return $ip >> 24 === $net_a || $ip >> 20 === $net_b || $ip >> 16 === $net_c ;
    }

    public static function setWorkListData($data)
    {
        foreach($data as $key => $val){
            $data[$key] = self::setTimeFormat($val);
            if($val['type']==2){
                $data[$key]['cycle'] = self::setCycle($val['cycle']);
            }
        }
        return $data;
    }

    public static function setTimeFormat($data)
    {
        if(isset($data['commit_time'])){
            if($data['commit_time']>0){
                $data['commit_time_f'] = date('Y-m-d',$data['commit_time']);
            }else{
                $data['commit_time_f'] = '';
            }
        }

        if(isset($data['approve_time'])){
            if($data['approve_time']>0){
                $data['approve_time_f'] = date('Y-m-d',$data['approve_time']);
            }else{
                $data['approve_time_f'] = '';
            }
        }

        if(isset($data['create_time'])){
            if($data['create_time']>0){
                $data['create_time_f'] = date('Y-m-d',$data['create_time']);
            }else{
                $data['create_time_f'] = '';
            }
        }

        return $data;
    }

    /**
     * 处理周报只显示月日
    */
    public static function setCycle($str){
        $arrCyc = explode('~',$str);
        $str1 = date('m-d',strtotime($arrCyc[0]));
        $str2 = date('m-d',strtotime($arrCyc[1]));
        return $str1.'~'.$str2;
    }
}