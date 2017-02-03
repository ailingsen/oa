<?php

namespace app\modules\permission\delegate;

use app\lib\Tools;
use app\models\Mcache;
use app\models\PermissionGroupModel;
use app\models\PermissionMemberModel;
use app\models\PermissionModel;
use app\modules\permission\helper\PermissionHelper;

//模型委托类。 处理控制器和动作列表

class PermissionDelegate {
    const USER_PERMISSION_CACHE_KEY = 'USER_PERMISSION_CACHE_';

    public static function dealPContr($ControllerAction)
    {
        $tmpList = $mainList = array();
        if (!empty($ControllerAction)) {
            foreach ($ControllerAction as $key => $value) {
                $flag = false;
                $tmp_ca = '';
                if (strstr($value, '/')) {
                    $cac_arr = explode('/', $value);
                    $tmpList[$key]['controller'] = $cac_arr[0];
                    $tmpList[$key]['action'] = $cac_arr[1];;
                    $tmp_ca = $cac_arr[0].$cac_arr[1];
                    $tmpList[$key]['is_contoller_k'] = '-1';
                    $tmpList[$key]['is_contoller'] = '否';
                } else {
                    $tmp_ca = $value;
                    $tmpList[$key]['controller'] = $value;
                    $tmpList[$key]['action'] = '';
                    $tmpList[$key]['is_contoller_k'] = '1';
                    $tmpList[$key]['is_contoller'] = '是';
                    $flag = true;
                }

                $pModel = PermissionModel::find()->where('controller_action=:cac', [':cac' => $tmp_ca])->one();
                if (empty($pModel)) {
                    $tmpList[$key]['p_name'] = '';
                    $tmpList[$key]['pid']=0;
                    $tmpList[$key]['p_router'] = '';
                    $tmpList[$key]['parent_id']=0;
                } else {
                    $tmpList[$key]['p_name'] = $pModel->p_name;
                    $tmpList[$key]['pid']=$pModel->pid;
                    $tmpList[$key]['parent_id']=$pModel->parent_id;
                    $tmpList[$key]['p_router'] = $pModel->p_router;
                    if($flag){
                        $mainList[] =array('pname'=>$pModel->p_name,'pid'=>$pModel->pid);
                    }
                }
            }
        }
        
        return ['temp_list' => $tmpList, 'main_list' => $mainList];
    }

    /**
     * 查询权限列表
     * 如果传了$params['u_id']，该用户有该权限，则给该权限的is_selected赋值为true
     * 如果传了$params['group_id']，该角色有该权限，则给该权限的is_selected赋值为true
     * @param $params
     * @return array
     */
    public static function getPermissionList($params)
    {
        $permissionList = PermissionModel::find()->where(['is_use' => 1])->asArray()->all();
        if (isset($params['u_id'])) {
            $permissionMember = PermissionMemberModel::find()->select('pid')->where(['u_id' => $params['u_id']])->asArray()->all();
            $permissionMember = array_values($permissionMember);
            $permissionList = PermissionHelper::doPermission($permissionList, $permissionMember);
        } else if (isset($params['group_id'])) {
            $permissionMember = PermissionGroupModel::find()->select('permission')->where(['group_id' => $params['group_id']])->asArray()->all();
            $permissionMember = json_decode($permissionMember, true);
            $permissionList = PermissionHelper::doPermission($permissionList, $permissionMember);
        }
        return Tools::createTreeArr($permissionList, 0, 'parent_id', 'pid');
    }

    /**
     * @param $uid
     * @param $groupId
     * @return $this|array
     */
    public static function getUserPcode($uid, $groupId)
    {
        $pCodeList = [];
//        $pCodeList = Mcache::getCache(self::USER_PERMISSION_CACHE_KEY.$uid);
//        if ($pCodeList) {
//            return $pCodeList;
//        }
        $permissionMember = PermissionMemberModel::find()->select('oa_permission.code')
            ->leftJoin('oa_permission', 'oa_permission.pid=oa_permission_member.pid')
            ->where(['oa_permission_member.u_id' => $uid])
            ->asArray()
            ->all();
//        if (!$permissionMember) {
//            $permissGroupModel = PermissionGroupModel::findOne($groupId);
//            if ($permissGroupModel->group_name == '超级管理员') {
//                $permissionMember = PermissionModel::find()->select('code')
//                    ->asArray()
//                    ->all();
//            } else {
//                $permissionMember = PermissionModel::find()->select('code')
//                    ->where(['pid' => json_decode($permissGroupModel->permission, true)])
//                    ->asArray()
//                    ->all();
//            }
//        }

        foreach ($permissionMember as $item) {
            $pCodeList[] = strtolower($item['code']);
        }
        Mcache::setCache(self::USER_PERMISSION_CACHE_KEY.$uid, $pCodeList);
        return $pCodeList;
    }
    
    public static function delPermissionCache($uid){
        return Mcache::deleteCache(self::USER_PERMISSION_CACHE_KEY . $uid);;
    }
    
}