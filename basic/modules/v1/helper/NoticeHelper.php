<?php

namespace app\modules\v1\helper;

use Yii;

//控制器辅助类

class NoticeHelper {
    /**
     * 处理是否有附件已经时间转换
     */
    public static function setData($data)
    {
        foreach($data as $key=>$val){
            /*if(count($val['att'])>0){
                $data[$key]['isatt'] = 1;
            }else{
                $data[$key]['isatt'] = 0;
            }*/
            $data[$key]['create_time_f'] = date('Y-m-d',$val['create_time']);
        }
        return $data;
    }
}