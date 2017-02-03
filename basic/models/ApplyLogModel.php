<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_log".
 *
 * @property integer $log_id
 * @property integer $apply_id
 * @property integer $handler
 * @property string $comment
 * @property integer $reply_time
 * @property integer $status
 * @property integer $step
 */
class ApplyLogModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'handler', 'reply_time', 'status', 'step'], 'integer'],
            [['comment'], 'required'],
            [['comment'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'log_id' => 'Log ID',
            'apply_id' => 'Apply ID',
            'handler' => 'Handler',
            'comment' => 'Comment',
            'reply_time' => 'Reply Time',
            'status' => 'Status',
            'step' => 'Step',
        ];
    }
}
