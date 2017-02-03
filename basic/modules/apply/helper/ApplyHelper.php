<?php

namespace app\modules\apply\helper;

//控制器辅助类

use app\models\ApplyModel;

class ApplyHelper {

    /**
     * 定制申请查看详情数据格式设置
    */
    public static function applyFormat($data)
    {
        $data['data']['begin_time'] = ApplyModel::setFormatDate($data['data']['begin_time']);
        $data['data']['end_time'] = ApplyModel::setFormatDate($data['data']['end_time']);
        $data['create_time'] = ApplyModel::setFormatDate($data['create_time']);
        return $data;
    }

    /**
     * 定制申请查看详情数据格式设置
     */
//    public static function applyFormatApp($data)
//    {
//        $data['detail']['begin_time'] = ApplyModel::setFormatDate($data['detail']['begin_time']);
//        $data['detail']['end_time'] = ApplyModel::setFormatDate($data['detail']['end_time']);
////        $data['create_time'] = ApplyModel::setFormatDate($data['create_time']);
//        return $data;
//    }

    /**
     * 审批记录
     */
    public static function leaveRecordFormat($data)
    {
        foreach($data as $key=>$val){
            $data[$key]['reply_time'] = ApplyModel::setFormatDate($val['reply_time']);
        }
        return $data;
    }
    
}