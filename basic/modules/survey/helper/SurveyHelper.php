<?php

namespace app\modules\survey\helper;

//控制器辅助类

class SurveyHelper {

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
     * 设置调研列表数据
     */
    public static function setSurveyData($data){
        //今天凌晨时间戳
        $today = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        foreach($data as $key=>$val){
            if(isset($val['create_time'])){
                $data[$key]['create_time_f'] = self::setFormatDate($val['create_time']);
                if ($today > $data[$key]['create_time']) {
                    $data[$key]['is_new'] = false;
                } else {
                    $data[$key]['is_new'] = true;
                }
            }
        }
        return $data;
    }

    /**
     * 设置调研回复列表数据
    */
    public static function setSurveyReplyData($data){
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