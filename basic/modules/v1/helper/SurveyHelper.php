<?php

namespace app\modules\v1\helper;

use Yii;

//控制器辅助类

class SurveyHelper {
    /**
     * 设置调研列表数据
     */
    public static function setSurveyData($data){
        foreach($data as $key=>$val){
            if(isset($val['create_time'])){
                $data[$key]['create_time_f'] = self::setFormatDate($val['create_time']);
            }
        }
        return $data;
    }

    /**
     * 时间格式化
     */
    public static function setFormatDate($date){
        return date('Y-m-d H:i:s',$date);
    }

}