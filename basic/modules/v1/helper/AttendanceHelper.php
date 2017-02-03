<?php

namespace app\modules\v1\helper;

use Yii;

//控制器辅助类

class AttendanceHelper {
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

}