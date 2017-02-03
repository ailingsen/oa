<?php

namespace app\modules\permission\controllers;

use app\controllers\BaseController;
use app\models\PermissionModel;
use app\modules\permission\helper\PermissionHelper;
use yii\base\stdClass;
use Yii;
use app\modules\permission\delegate\PermissionDelegate;
/**
 * Default controller for the `permission` module
 */
class PermissionController extends BaseController
{
    public $modelClass = 'app\models\PermissionModel';
    /**
     * 获取控制器和动作 列表
     */
    public function actionCtrlList()
    {
        $request = Yii::$app->request;
        $size = $request->post('size', 15);
        $page = $request->post('page', 1);
        $offset = $size * ($page - 1);

        $controllerAction = PermissionHelper::getAllControllerActionList();

        $listArr = PermissionDelegate::dealPContr($controllerAction);
        $pContrModel = PermissionModel::findAll(['is_contoller' => 1]);
        $pConts = array_map(function($record) {return $record->attributes;}, $pContrModel);

        $tmpListLimit = array_slice($listArr['temp_list'], $offset, $size);

        $count = count($pContrModel);
        $pages['size'] = intval($size);
        $pages['page'] = intval($page);
        $pages['count'] = ceil($count/$size);

        return ['code' => 20000, 'msg' => "ok", 'data' => ['ret' => $tmpListLimit, 'pages' => $pages, 'groupname' => $listArr['main_list'], 'pConts' => $pConts]];
    }


    /**
     * 根据父权限获取下级权限列表
     */
    public function actionParentList()
    {
        $list = PermissionHelper::getParentList(Yii::$app->request->post('parent_id', 0));
        return ['code' => 20000, 'msg' => "ok", 'data' => ['list' => $list]];
    }

    /**
     * 查询所有权限
     * @return array
     */
    public function actionPermissionlist()
    {
        $result = PermissionDelegate::getPermissionList(Yii::$app->request->post());
        if ($result) {
            return ['code' => 20000, 'msg' => "查询成功", 'data' => $result];
        } else {
            return ['code' => 20001, 'msg' => "查询失败", 'data' => new \stdClass()];
        }
    }

    /**
     * 新增数据
     */
    public function actionAdd()
    {
        $pName = Yii::$app->request->post('p_name');
        if (!empty($pName)) {
            $permission = array_intersect_key(Yii::$app->request->post(), array_flip(['parent_id', 'p_name', 'p_router']));
            $perm = Yii::$app->request->post('pid_other');
            $permArray = explode('#', $perm);
            $permission['code'] = $permArray[0];
            $permission['is_contoller'] = $permArray[1];
            if (PermissionModel::addPermission($permission)) {
                return ['code' => 20000, 'msg' => "添加成功！", 'data' => new \stdClass()];
            } else {
                return ['code' => 20001, 'msg' => "添加失败-请联系管理员！", 'data' => new \stdClass()];
            }
        }
        return ['code' => 20002, 'msg' => "操作错误！", 'data' => new \stdClass()];
    }

    /**
     * 修改数据
     */
    public function actionUpdate()
    {
        $pid = Yii::$app->request->post('pid');
        if ($pid) {
            $data = array_intersect_key(Yii::$app->request->post(), array_flip(['parent_id', 'p_name', 'p_router']));
            if (PermissionModel::updateX($pid, $data)) {
                return ['code' => 20000, 'msg' => "修改成功！", 'data' => new \stdClass()];
            } else {
                return ['code' => 20001, 'msg' => "修改失败-请联系管理员！", 'data' => new \stdClass()];
            }
        }
        return ['code' => 20002, 'msg' => "操作错误！", 'data' => new \stdClass()];
    }

    /**
     * 删除数据
     * @throws \Exception
     */
    public function actionDel()
    {
        $pid = Yii::$app->request->post('pid');
        if ($pid) {
            $permission = PermissionModel::findOne(intval($pid));
            if ($permission->delete()) {
                return ['code' => 20000, 'msg' => "ok", 'data' => new \stdClass()];
            } else {
                return ['code' => 20001, 'msg' => "删除失败-请联系管理员！", 'data' => new \stdClass()];
            }
        }
        return ['code' => 20002, 'msg' => "操作错误！", 'data' => new \stdClass()];
    }


}
