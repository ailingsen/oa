<?php

namespace app\modules\permission\delegate;

use app\lib\Tools;
use app\models\MembersModel;
use app\models\PermissionGroupModel;
use app\models\PermissionModel;
use app\modules\permission\helper\PermissionHelper;

class PermissionGroupDelegate {

    /**
     * 检查名字是否存在
     * @param $groupName
     * @return bool
     */
    public static function isGroupNameExist($groupName)
    {
        if (PermissionGroupModel::findOne(['group_name' => $groupName])) {
            return true;
        }
        return false;
    }

    /**
     * 检查组是否被使用
     * @param $groupId
     * @return bool
     */
    public static function isGroupUsed($groupId)
    {
        if (MembersModel::getMembersByCondition('u_id', ['perm_groupid' => $groupId,'is_del'=>0])) {
            return true;
        }
        return false;
    }

    /**
     * 查询权限树
     * @param $params
     * @return array
     */
    public static function getPermissionList($params)
    {
        $permissionList = PermissionModel::find()->where(['is_use' => 1])->asArray()->all();
        $permissionMember = PermissionGroupModel::find()->select('group_name,permission')->where(['group_id' => $params['group_id']])->asArray()->one();
        $permissionListGroup = json_decode($permissionMember['permission'], true);
        $permissionList = PermissionHelper::doPermission($permissionList, $permissionListGroup);
        $permissionList = Tools::createTreeArr($permissionList, 0, 'parent_id', 'pid');
        return ['group_name' => $permissionMember['group_name'], 'permission' => $permissionList];
    }

    /**
     * 删除角色时更新用户的角色为空
    */
    public static function delMemRole($role_id)
    {
        $count = MembersModel::find()->where('perm_groupid=:perm_groupid',[':perm_groupid'=>$role_id])->count();
        if($count>0){
            $resSum = MembersModel::updateAll(['perm_groupid'=>0],'perm_groupid=:perm_groupid',[':perm_groupid'=>$role_id]);
            if($resSum==$count){
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }
    }
    
}