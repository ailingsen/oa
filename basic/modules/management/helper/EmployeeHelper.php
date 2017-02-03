<?php

namespace app\modules\management\helper;

//控制器辅助类
use app\models\MembersModel;
use app\models\OrgModel;

class EmployeeHelper {

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
     * 处理员工权限要保存的数据格式
     * $data 员工权限
     * $u_id
    */
    public static function setUserPermSaveFormat($data,$u_id)
    {
        $arrUserPerm = [];
        foreach($data as $key=>$val){
            $arrUserPerm[$key]['u_id'] = $u_id;
            $arrUserPerm[$key]['pid'] = $val;
        }
        return $arrUserPerm;
    }
    
}