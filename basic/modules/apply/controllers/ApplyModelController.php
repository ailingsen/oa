<?php
/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2016/8/3
 * Time: 16:02
 */

namespace app\modules\apply\controllers;


use app\controllers\BaseController;
use app\models\ApplyBaseModel;
use app\models\ApplyFieldModel;
use app\models\ApplyFlowModel;
use app\models\ApplyModel;
use app\modules\apply\delegate\ApplyModelDelegate;
use app\modules\apply\delegate\ApplyDelegate;
use Yii;

class ApplyModelController extends BaseController
{
    public $modelClass = 'app\models\ApplyModel';

    //创建审批单
    public function actionCreateModel()
    {
        $postdata = Yii::$app->request->post('postdata');
        $data = json_decode($postdata, true);

        if(!isset($data['title']) || $data['title'] == '') {
            return ['code' => 0, 'msg' => '表单标题不能为空'];
        }

        if(!isset($data['field']) || empty($data['field'])) {
            return ['code' => 0, 'msg' => '请设置审批单字段'];
        }

        if(ApplyModelDelegate::isModelExist(['title' => $data['title']])) {
            return ['code' => 0, 'msg' => '审批单模型已存在'];
        }

        $applyModel = new ApplyModel();
        $applyModel->modeltype = 0;
        $applyModel->title = $data['title'];
        $applyModel->html = $data['html'];;
;

//        if(isset($data['limit_type']) && !empty($data['limit_type']) && isset($data['limit_num']) && !empty($data['limit_num'])) {
//            $applyModel->limit_type = $data['limit_type'];
//            $applyModel->limit_num = $data['limit_num'];
//        }

        if($applyModel->save(false)) {
            $tableName = ApplyModelDelegate::createTable($data['field'],$applyModel->model_id);
            if($tableName) {
                $applyModel->tablename = $tableName;
                $applyModel->save(false);
                return ['code' => 1,'msg' => '创建审批单成功','model_id' => $applyModel->model_id,'model_type' => $applyModel->modeltype];
            }
        }
        return ['code' => 0,'msg' => '创建审批单失败'];
    }

    //审批单管理列表
    public function actionModelList()
    {
        $list = ApplyModelDelegate::getApplyModelList(['model_id','title','modeltype','status','is_set']);
        foreach($list as $key=>$value) {
            $res = ApplyBaseModel::find()->where(['model_id' => $value['model_id']])->exists();
            if($res) {
                $list[$key]['allow_del'] = false;
            }else {
                $list[$key]['allow_del'] = true;
            }
        }
        return ['code' => 1,'data' => $list];
    }

    //自定义表单列表
    public function actionCustomModelList()
    {
        $list = ApplyModelDelegate::getApplyModelList(['model_id','title'],['modeltype' => 0]);
        return ['code' => 1,'data' => $list];
    }

    //可使用申请列表
    public function actionUsefulList()
    {
        //申请人
        $uid = $this->userInfo['u_id'];
        //申请人所属组
        $org_id = $this->userInfo['org']['org_id'];
        $model_id = ApplyFlowModel::find()->select(['model_id'])
                    ->where("(type=0 and visibleman like :uid) or (type=1 and visibleman like :org_id)")
                    ->addParams([':uid' => '%,'.$uid.',%',':org_id' => '%,'.$org_id.',%'])
                    ->orderBy('model_id ASC')
                    ->groupBy('model_id')
                    ->column();
        $list = ApplyModelDelegate::getApplyModelList(['model_id','title','modeltype'],['status' => 1,'model_id' => $model_id]);
        return ['code' => 1,'data' => $list];
    }

    //修改审批单名称
    public function actionUpdateTitle()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if(!isset($data['model_id']) || empty($data['model_id'])) {
            return ['code' => 0, 'msg' => '参数错误，缺少model_id'];
        }

        if(!isset($data['title']) || empty($data['title'])) {
            return ['code' => 0, 'msg' => '请设置审批单标题'];
        }

        if(ApplyModelDelegate::isModelExist(['title' => $data['title']])) {
            return ['code' => 0, 'msg' => '该名称审批单模型已存在'];
        }

        $res = ApplyModelDelegate::updateApplyModel(['title' => $data['title']],['model_id' => $data['model_id']]);

        if($res == 0) {
            return ['code' => 0,'msg' => '未修改任何数据'];
        }else if($res === false) {
            return ['code' => 0,'msg' => '修改审批单标题失败'];
        }else {
            return ['code' => 1,'msg' => '修改审批单标题成功'];
        }

    }

    //停用/启用审批单
    public function actionModelState()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if(!isset($data['model_id']) || empty($data['model_id'])) {
            return ['code' => 0, 'msg' => '参数错误，缺少model_id'];
        }

        if(!isset($data['status'])) {
            return ['code' => 0, 'msg' => '参数错误，缺少status'];
        }

        //判断该流程是否允许上线操作
        $msg = '停用审批单';
        if($data['status'] == 1) {
            $msg = '启用审批单';
            if(!ApplyModelDelegate::isModelExist(['model_id' => $data['model_id'],'is_set' => 1])) {
                return ['code' => 0, 'msg' => '请先配置该申请单流程'];
            }
        }

        $res = ApplyModelDelegate::updateApplyModel(['status' => $data['status']],['model_id' => $data['model_id']]);

        if($res == 0) {
            return ['code' => 0,'msg' => '未修改任何数据'];
        }else if($res === false) {
            return ['code' => 0,'msg' => $msg.'失败'];
        }else {
            return ['code' => 1,'msg' => $msg.'成功'];
        }

    }

    //删除审批单模型
    public function actionModelDelete($model_id)
    {
        $model = ApplyModel::findOne($model_id);
        if(empty($model)) {
            return ['code' => 0,'msg' => '数据不存在'];
        }
        if($model->modeltype == 1) {
            return ['code' => 0,'msg' => '定制表单不允许删除'];
        }
        if($model->status == 1) {
            return ['code' => 0,'msg' => '请先停用该流程'];
        }
        if(ApplyDelegate::isApplyExist(['model_id' => $model_id])) {
            return ['code' => 0,'msg' => '已存在该审批单数据，不允许删除'];
        }
        //删除表单模型
        ApplyModel::deleteAll(['model_id' => $model_id]);
        //删除表单字段
        ApplyFieldModel::deleteAll(['model_id' => $model_id]);
        //删除表单流程
        ApplyFlowModel::deleteAll(['model_id' => $model_id]);
        //删除表
        if(!empty($model->tablename)) {
            $sql = "DROP TABLE ".$model->tablename;
            Yii::$app->db->createCommand($sql)->execute();
        }
        return ['code' => 1,'msg' => '删除审批单成功'];
    }

    //展示审批单模型(自定义表单使用)
    public function actionModelShow($model_id)
    {
        $applyModel = ApplyModel::find()->where(['model_id' => $model_id])->asArray()->one();

        if(empty($applyModel)) {
            return ['code' => 0, 'msg' => '审批单模型不存在'];
        }

        $applyModel['field'] = ApplyModelDelegate::getModelField($model_id);

        return ['code' => 1,'data' => $applyModel];
    }

}