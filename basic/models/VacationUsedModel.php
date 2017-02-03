<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_vacation_used".
 *
 * @property integer $id
 * @property integer $u_id
 * @property string $used_num
 * @property integer $vacation_type
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $create_time
 * @property integer $apply_id
 * @property integer $status
 * @property string $note
 */
class VacationUsedModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_vacation_used';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['u_id', 'used_num', 'vacation_type', 'start_time'], 'required'],
            [['u_id', 'vacation_type', 'start_time', 'end_time', 'create_time', 'apply_id', 'status'], 'integer'],
            [['used_num'], 'number'],
            [['note'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'u_id' => 'U ID',
            'used_num' => 'Used Num',
            'vacation_type' => 'Vacation Type',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'create_time' => 'Create Time',
            'leave_id' => 'Apply ID',
            'status' => 'Status',
            'note' => 'Note',
        ];
    }

    public static function getTakeOff($uid)
    {
        return self::find()->select('sum(used_num) as usedNum')->where(['u_id'=>$uid])->andWhere(['vacation_type'=>3])->andWhere(['status'=> 1])->asArray()->one();
    }

    public static function statistic($uid, $starTime, $endTime)
    {
        $query = self::find()->select('sum(used_num) as used_num,vacation_type')
            ->where(['u_id' => $uid]);
        if ($starTime && $endTime) {
            $query->andWhere(['between', 'start_time', $starTime, $endTime]);
        }
        return $query->groupBy('vacation_type')
            ->asArray()
            ->all();
    }
}
