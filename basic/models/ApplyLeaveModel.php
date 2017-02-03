<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_leave".
 *
 * @property integer $id
 * @property integer $ltype
 * @property integer $begin_time
 * @property integer $end_time
 * @property double $leave_sum
 * @property string $content
 */
class ApplyLeaveModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_leave';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'begin_time', 'end_time', 'leave_sum', 'content'], 'required'],
            [['type', 'begin_time', 'end_time'], 'integer'],
            [['leave_sum'], 'number'],
            [['content','inventory_id'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'begin_time' => 'Begin Time',
            'end_time' => 'End Time',
            'leave_sum' => 'Leave Sum',
            'content' => 'Content',
            'inventory_id' => 'Inventory Id',
        ];
    }
}
