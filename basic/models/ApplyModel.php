<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_model".
 *
 * @property string $model_id
 * @property string $title
 * @property string $tablename
 * @property integer $modeltype
 * @property integer $status
 * @property integer $limit_type
 * @property integer $limit_num
 */
class ApplyModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_model';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['modeltype', 'status', 'is_set', 'limit_type', 'limit_num'], 'integer'],
            [['title'], 'string', 'max' => 40],
            [['tablename'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'model_id' => 'Model ID',
            'title' => 'Title',
            'tablename' => 'Tablename',
            'modeltype' => 'Modeltype',
            'status' => 'Status',
            'is_set' => 'Is Set',
            'limit_type' => 'Limit Type',
            'limit_num' => 'Limit Num',
        ];
    }

    /**
     * 时间格式化
     */
    public static function setFormatDate($date){
        return date('Y-m-d H:i',$date);
    }
}
