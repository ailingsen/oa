<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_base".
 *
 * @property integer $apply_id
 * @property integer $model_id
 * @property integer $detail_id
 * @property string $title
 * @property integer $applyer
 * @property string $handler
 * @property string $form_json
 * @property string $flow
 * @property integer $step
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $status
 * @property integer $is_press
 * @property integer $is_attachment
 */
class ApplyBaseModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_base';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_id', 'detail_id', 'applyer', 'step', 'create_time', 'update_time', 'status', 'is_press', 'is_attachment'], 'integer'],
            [['title', 'flow'], 'required'],
            [['form_json', 'flow'], 'string'],
            [['title', 'handler'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => 'Apply ID',
            'model_id' => 'Model ID',
            'detail_id' => 'Detail ID',
            'title' => 'Title',
            'applyer' => 'Applyer',
            'handler' => 'Handler',
            'form_json' => 'Form Json',
            'flow' => 'Flow',
            'step' => 'Step',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'status' => 'Status',
            'is_press' => 'Is Press',
            'is_attachment' => 'Is Attachment',
        ];
    }
}
