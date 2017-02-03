<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_attachment".
 *
 * @property integer $apply_att_id
 * @property integer $apply_id
 * @property string $file_name
 * @property string $real_name
 * @property integer $file_size
 * @property string $file_path
 * @property integer $create_time
 */
class ApplyAttachmentModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_attachment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'file_size', 'create_time'], 'integer'],
            [['file_name', 'real_name', 'file_path'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_att_id' => 'Apply Att ID',
            'apply_id' => 'Apply ID',
            'file_name' => 'File Name',
            'real_name' => 'Real Name',
            'file_size' => 'File Size',
            'file_path' => 'File Path',
            'create_time' => 'Create Time',
        ];
    }
}
