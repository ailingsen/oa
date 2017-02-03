<?php

/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2016/8/3
 * Time: 17:45
 */

namespace app\modules\apply\delegate;

use app\lib\Tools;
use app\models\ApplyFieldModel;
use app\models\ApplyFlowModel;
use app\models\ApplyModel;
use app\models\MembersModel;
use app\models\OrgModel;
use Yii;

class ApplyModelDelegate
{

    const TABLE_NAME = 'oa_form';

    /**
     * 判断申请表单模型是否存在
     * @param $condition
     * @return bool
     */
    public static function isModelExist(array $condition)
    {
        return ApplyModel::find()->where($condition)->exists();
    }

    /**
     * 创建自定义数据表,插入自定义表字段
     * @param $data
     * @param $modelID
     * @return mixed
     */
    public static function createTable($data, $modelID)
    {
        $sql = 'CREATE TABLE ' . self::TABLE_NAME . '_' . $modelID . '(' .
            '`id` int(11) not null primary key auto_increment';

        foreach ($data as $index => $value) {
            $dataType = self::getDataType($value['formtype']);
            if (!empty($dataType)) {
                $sql .= ',`' . $index . '` ' . $dataType;
            }
            $modelField = new ApplyFieldModel();
            $modelField->model_id = $modelID;
            $modelField->field = $index;
            foreach ($value as $key => $val) {
                if ($key == 'setting') {
                    $modelField->$key = serialize($val);
                } else {
                    $modelField->$key = $val;
                }
            }
            $modelField->save(false);
        }
        $sql .= ')  ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $res = Yii::$app->db->createCommand($sql)->execute();
        if ($res === false) {
            return false;
        } else {
            return self::TABLE_NAME . '_' . $modelID;
        }
    }

    /**
     * 分析数据表字段类型
     * @param $formtype
     * @return string
     */
    public static function getDataType($formtype)
    {
        $dataType = '';
        switch ($formtype) {
            case 'Text':
                $dataType = 'varchar(255)';
                break;
            case 'TextArea':
                $dataType = 'text';
                break;
            case 'RadioBox':
                $dataType = 'varchar(255)';
                break;
            case 'CheckBox':
                $dataType = 'mediumtext';
                break;
            case 'Select':
                $dataType = 'varchar(255)';
                break;
            case 'DateComponent':
                $dataType = 'varchar(255)';
                break;
            case 'DateInterval':
                $dataType = 'mediumtext';
                break;
            case 'NumberComponent':
                $dataType = 'int(11)';
                break;
            case 'Money':
                $dataType = 'decimal(10,2)';
                break;
            case 'Employee':
                $dataType = 'text';
                break;
            case 'Department':
                $dataType = 'text';
                break;
            case 'DividingLine':
                $dataType = '';
                break;
            case 'Paragraph':
                $dataType = '';
                break;
            case 'Email':
                $dataType = 'varchar(255)';
                break;
            case 'Phone':
                $dataType = 'varchar(20)';
                break;
            case 'Mobile':
                $dataType = 'varchar(20)';
                break;
            case 'FileComponent':
                $dataType = 'text';
                break;
            case 'ImageComponent':
                $dataType = 'text';
                break;
            case 'DataTable':
                $dataType = 'text';
                break;
        }
        return $dataType;
    }

    /**
     * 获取审批单列表
     * @param array $field
     * @param array $condition
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getApplyModelList(array $field=[],array $condition=[])
    {
        if (empty($condition)) {
            $list = ApplyModel::find()->select($field)->asArray()->all();
        } else {
            $list = ApplyModel::find()->select($field)->where($condition)->asArray()->all();
        }
        return $list;
    }

    /**
     * 更新单个审批单
     * @param array $param
     * @param array $condition
     * @return bool
     */
    public static function updateApplyModel(array $param, array $condition)
    {
        $applyModel = ApplyModel::findOne($condition);
        foreach ($param as $key => $value) {
            $applyModel->$key = $value;
        }
        return $applyModel->save(false);
    }

    /**
     * 获取审批流程配置
     * @param $model_id
     * @return array
     */
    public static function getModelFlow($model_id)
    {
        $config = [];
        $applyModel = ApplyModel::find()->where(['model_id' => $model_id])->asArray()->one();
        if ($applyModel) {
            $config['limit_type'] = $applyModel['limit_type'];
            $config['limit_num'] = $applyModel['limit_num'];
            //获取表单所有字段值
            $config['fieldList'] = ApplyFieldModel::find()->select(['field','title'])->where(['and', 'model_id='.$model_id, ['or', "formtype='Money'", "formtype='NumberComponent'"]])->asArray()->all();
            $flow = ApplyFlowModel::find()->where(['model_id' => $model_id])->asArray()->all();
            foreach ($flow as $key => $value) {
                $ids = explode(',', $value['visibleman']);
                if ($value['type'] == 0) {
                    $visiblemanInfo = MembersModel::find()->select(['u_id', 'real_name', 'head_img'])->where(['u_id' => $ids])->asArray()->all();
                } else {
                    $visiblemanInfo = OrgModel::getOrgInfo($ids, ['org_id', 'org_name']);
                }
                $flow[$key]['visiblemanInfo'] = $visiblemanInfo;
                //审批流程处理
                $flowTmp = json_decode($value['flow'], true);
                $flowRes = [];
                foreach($flowTmp as $k => $v) {
                    $tmpArray = [];
                    if($v == -1) {
                        $tmpArray['u_id'] = -1;
                        $tmpArray['real_name'] = '直属上级';
                        $tmpArray['head_img'] = Tools::getHeadImg(false);
                    }else {
                        $handlerInfo = MembersModel::find()->select(['u_id', 'real_name', 'head_img'])->where(['u_id' => $v])->asArray()->one();
                        $tmpArray['u_id'] = $handlerInfo['u_id'];
                        $tmpArray['real_name'] = $handlerInfo['real_name'];
                        $tmpArray['head_img'] = Tools::getHeadImg($handlerInfo['head_img']);
                    }
                    $flowRes[$k] = $tmpArray;
                }
                $flow[$key]['flow'] = $flowRes;
                $flow[$key]['visibleman'] = substr(substr($value['visibleman'],1),0,-1);implode(',',explode(',', $value['visibleman']));
            }
            $config['flow'] = $flow;
        }
        return $config;
    }

    /**
     * 获取表单所有字段
     * @param $model_id
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getModelField($model_id)
    {
        $fields = ApplyFieldModel::find()->where(['model_id' => $model_id])->asArray()->all();
        foreach($fields as $key => $value) {
            if (!empty($value['setting'])) {
                $fields[$key]['setting'] = unserialize($value['setting']);
            }
        }
        return  $fields;
    }

}