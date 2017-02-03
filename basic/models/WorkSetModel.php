<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_workday_set".
 *
 * @property integer $id
 * @property string $begin_time
 * @property string $end_time
 * @property string $workday_time
 * @property integer $workday_lose
 * @property double $unworkday_time
 */
class WorkSetModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_workday_set';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'workday_lose'], 'integer'],
            [['unworkday_time'], 'number'],
            [['begin_time', 'end_time', 'workday_time'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'begin_time' => 'Begin Time',
            'end_time' => 'End Time',
            'workday_time' => 'Workday Time',
            'workday_lose' => 'Workday Lose',
            'unworkday_time' => 'Unworkday Time',
        ];
    }
}
