<?php

namespace app\modules\management\helper;

//控制器辅助类

class HolidayHelper {

    /**
     * 数据格式处理
    */
    public static function setMonthWorkday($data)
    {
        foreach($data as $key=>$val){
            $data[$key]['sun'] = date('d',$val['day']);
            $data[$key]['format'] = date('Y-m-d',$val['day']);
        }
        return $data;
    }
    
}