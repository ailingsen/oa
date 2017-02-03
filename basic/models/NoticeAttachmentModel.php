<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_notice_attachment".
 *
 * @property integer $notice_att_id
 * @property integer $notice_id
 * @property string $file_name
 * @property string $real_name
 * @property integer $file_size
 * @property string $file_path
 * @property integer $create_time
 */
class NoticeAttachmentModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_notice_attachment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['notice_id', 'file_name', 'real_name', 'file_path'], 'required'],
            [['notice_id', 'file_size', 'create_time'], 'integer'],
            [['file_name', 'real_name'], 'string', 'max' => 255],
            [['file_path'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'notice_att_id' => 'Notice Att ID',
            'notice_id' => 'Notice ID',
            'file_name' => 'File Name',
            'real_name' => 'Real Name',
            'file_size' => 'File Size',
            'file_path' => 'File Path',
            'create_time' => 'Create Time',
        ];
    }
}
