<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_flow".
 *
 * @property integer $flow_id
 * @property integer $model_id
 * @property integer $modeltype
 * @property integer $type
 * @property integer $visibleman
 * @property integer $condition
 * @property integer $item
 * @property string $value
 * @property string $flow
 */
class ApplyFlowModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_flow';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_id', 'flow'], 'required'],
            [['model_id', 'modeltype', 'type', 'visibleman', 'condition', 'item'], 'integer'],
            [['flow'], 'string'],
            [['value'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'flow_id' => 'Flow ID',
            'model_id' => 'Model ID',
            'modeltype' => 'Modeltype',
            'type' => 'Type',
            'visibleman' => 'Visibleman',
            'condition' => 'Condition',
            'item' => 'Item',
            'value' => 'Value',
            'flow' => 'Flow',
        ];
    }
}
