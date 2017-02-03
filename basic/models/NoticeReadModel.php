<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_notice_read".
 *
 * @property integer $notice_read_id
 * @property integer $notice_id
 * @property integer $u_id
 * @property integer $create_time
 */
class NoticeReadModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_notice_read';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['notice_id', 'u_id'], 'required'],
            [['notice_id', 'u_id', 'create_time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'notice_read_id' => 'Notice Read ID',
            'notice_id' => 'Notice ID',
            'u_id' => 'U ID',
            'create_time' => 'Create Time',
        ];
    }
}
