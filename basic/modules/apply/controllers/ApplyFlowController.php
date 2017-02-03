<?php
/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2016/8/2
 * Time: 17:23
 */

namespace app\modules\apply\controllers;

use app\controllers\BaseController;
use app\models\ApplyFlowModel;
use app\models\ApplyModel;
use app\modules\apply\delegate\ApplyModelDelegate;

class ApplyFlowController extends BaseController
{
    public $modelClass = 'app\models\ApplyFlowModel';

    //创建表单流程
    public function actionCreateFlow()
    {
        $data = json_decode(file_get_contents("php://input"),true);

        if(!isset($data['model_id']) || empty($data['model_id'])) {
            return ['code' => 0, 'msg' => '参数错误，缺少model_id'];
        }

        if(!isset($data['modeltype'])) {
            return ['code' => 0, 'msg' => '参数错误，缺少modeltype'];
        }

        $applyModel = ApplyModel::findOne(['model_id' => $data['model_id'],'status' => 0]);
        if(empty($applyModel)) {
            return ['code' => 0, 'msg' => '该审批单不存在或处于启用状态'];
        }

        //设置表单提交限制
//        if(isset($data['limit_type']) && !empty($data['limit_type']) && isset($data['limit_num']) && !empty($data['limit_num'])) {
            $applyModel->limit_type = $data['limit_type'];
            $applyModel->limit_num = empty($data['limit_num']) ? 0 : $data['limit_num'];
//        }

        $applyModel->status = 1;
        $applyModel->is_set = 1;

        if($applyModel->save(false)) {
            //删除原有流程
            ApplyFlowModel::deleteAll(['model_id' => $applyModel->model_id]);
            foreach($data['flow'] as $key => $value) {
                $applyFlow = new ApplyFlowModel();
                $applyFlow->model_id = $data['model_id'];
                $applyFlow->modeltype = $data['modeltype'];
                $applyFlow->type = $value['type'];
                $applyFlow->visibleman = ','.$value['visibleman'].',';
                $applyFlow->condition = $value['condition'];
                $applyFlow->item = $value['item'];
                $applyFlow->value = $value['value'];
                $applyFlow->flow = json_encode($value['flow']);
                $applyFlow->save(false);
            }
            return ['code' => 1, 'msg' => '编辑审批单流程成功'];
        }
        return ['code' => 0, 'msg' => '编辑审批单流程失败'];
    }

    //获取表单流程
    public function actionShowFlow($model_id)
    {
        //获取表单配置流程
        $data = ApplyModelDelegate::getModelFlow($model_id);

        return ['code' => 1,'data' => $data];
    }

}