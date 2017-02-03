<?php
/**
 * Created by PhpStorm.
 * User: liaoshuochao
 * Date: 2016/7/11
 * Time: 17:26
 */

namespace app\modules\permission\controllers;

use app\controllers\BaseController;
use app\lib\errors\ErrorCode;
use app\models\PermissionGroupModel;
use app\modules\permission\delegate\PermissionDelegate;
use app\modules\permission\delegate\PermissionGroupDelegate;
use app\modules\project\delegate\ProjectDelegate;
use Yii;

class PermissiongroupController extends BaseController
{
    public $modelClass = 'app\models\PermissionGroupModel';
    public function actionGroupList()
    {
        $size = Yii::$app->request->post('size', 10);
        $page = Yii::$app->request->post('page', 1);

        $perGroup = PermissionGroupModel::getPerGroup(intval($size), intval($page));
        $count = PermissionGroupModel::getPerGroupCount();
        $count = ceil($count / $size);
        return ['code' => 20000, 'msg' => "ok", 'data' => ['totalPage' => $count, 'page' => $page, 'permission_group' => $perGroup]];
    }

    /**
     * 添加角色
     * @throws \app\lib\errors\ValidateException
     */
    public function actionAdd()
    {
        $groupName = Yii::$app->request->post('group_name');
        $permission = Yii::$app->request->post('permission');
        if (PermissionGroupDelegate::isGroupNameExist($groupName)) {
            return ['code' => ErrorCode::E_CANNOT_REPEAT_ADD, 'msg' => '添加失败，该角色已存在！'];
        }
        if(!ProjectDelegate::isStrlen($groupName,20)){
            return ['code' => ErrorCode::E_CANNOT_REPEAT_ADD, 'msg' => '添加失败，角色名长度不能超过20个字！'];
        }
        $data = ['group_name' => $groupName, 'permission' => json_encode($permission)];
        $result = PermissionGroupModel::addGroup($data);
        if ($result) {
            return ['code' => 20000, 'msg' => "添加成功", 'data' => new \stdClass()];
        } else {
            return ['code' => 20001, 'msg' => "添加失败-请联系管理员！", 'data' => new \stdClass()];
        }
    }

    
    /**
     * 删除角色
     * @throws \app\lib\errors\ClientException
     */
    public function actionDel()
    {
        $groupId = Yii::$app->request->post('group_id');
        $transaction = \Yii::$app->db->beginTransaction();
        /*if (PermissionGroupDelegate::isGroupUsed($groupId)) {
            return ['code' => 20002, 'msg' => "该角色下还有员工，不能删除！", 'data' => new \stdClass()];
        }*/
        if (PermissionGroupModel::deleteX($groupId) && PermissionGroupDelegate::delMemRole($groupId)) {
            $transaction->commit();
            return ['code' => 20000, 'msg' => "删除成功！", 'data' => new \stdClass()];
        }else{
            $transaction->rollBack();
            return ['code' => 20003, 'msg' => "删除失败！", 'data' => new \stdClass()];
        }
    }

    /**
     * 修改角色
     * @throws \app\lib\errors\ClientException
     * @throws \app\lib\errors\ValidateException
     * @throws \yii\db\Exception
     */
    public function actionEdite()
    {
        $gid = Yii::$app->request->post('group_id');
        $groupName = Yii::$app->request->post('group_name');
        $selectJson = Yii::$app->request->post('permission');

        $data['group_name'] = $groupName;
        $data['permission'] = $selectJson;
        if(!ProjectDelegate::isStrlen($groupName,20)){
            return ['code' => ErrorCode::E_CANNOT_REPEAT_ADD, 'msg' => '修改失败，角色名长度不能超过20个字！'];
        }
        $ret = PermissionGroupModel::EditGroup($gid,$data);
        if (isset($ret)) {
            $pdata = array(
                'permission'=> $selectJson,
            );
            if(!empty($pdata['permission'])) {
                Yii::$app->db->createCommand()->update('oa_members', $pdata, 'perm_groupid=:perm_groupid', array(':perm_groupid' => $gid))->execute();
            }
            return ['code' => 20000, 'msg' => "修改成功！", 'data' => new \stdClass()];
        } else {
            return ['code' => 20001, 'msg' => "修改失败-请联系管理员！", 'data' => new \stdClass()];
        }
        return ['code' => 20000, 'msg' => "修改成功！", 'data' => new \stdClass()];
    }
    
    public function actionPermissionlist()
    {
        $result = PermissionGroupDelegate::getPermissionList(Yii::$app->request->post());
        if ($result) {
            return ['code' => 20000, 'msg' => "查询成功", 'data' => $result];
        } else {
            return ['code' => 20001, 'msg' => "查询失败", 'data' => new \stdClass()];
        }
    }

}