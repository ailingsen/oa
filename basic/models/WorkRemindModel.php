<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_work_remind".
 *
 * @property integer $remind_id
 * @property integer $u_id
 * @property integer $type
 * @property integer $remind_time
 * @property integer $day
 * @property integer $is_use
 */
class WorkRemindModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_work_remind';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['remind_id', 'u_id', 'type', 'remind_time'], 'required'],
            [['remind_id', 'u_id', 'type', 'remind_time', 'day', 'is_use'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'remind_id' => 'Remind ID',
            'u_id' => 'U ID',
            'type' => 'Type',
            'remind_time' => 'Remind Time',
            'day' => 'Day',
            'is_use' => 'Is Use'
        ];
    }

    /**
     * 插入
     * @param $data
     * @return bool
     */
    public static function createX($data)
    {
        $taskModel = new self;
        $data = array_intersect_key($data, array_flip(['u_id', 'type', 'remind_time', 'day']));
        $taskModel->attributes = $data;
        return $taskModel->save(false);
    }
}
