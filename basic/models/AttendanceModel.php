<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_attendance".
 *
 * @property string $aid
 * @property integer $u_id
 * @property string $real_name
 * @property string $onTime
 * @property string $offTime
 * @property string $workDate
 * @property string $weekDay
 * @property integer $status
 * @property integer $substatus
 * @property integer $card_no
 * @property string $org_name
 */
class AttendanceModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_attendance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['u_id', 'onTime', 'offTime', 'workDate', 'status', 'card_no'], 'integer'],
            [['real_name'], 'string', 'max' => 20],
            [['weekDay'], 'string', 'max' => 10],
            [['org_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'aid' => 'Aid',
            'u_id' => 'U ID',
            'real_name' => 'Real Name',
            'onTime' => 'On Time',
            'offTime' => 'Off Time',
            'workDate' => 'Work Date',
            'weekDay' => 'Week Day',
            'status' => 'Status',
            'substatus' => 'Sub Status',
            'card_no' => 'Card No',
            'org_name' => 'Org Name',
        ];
    }


    public static function statistic($uid, $starTime, $endTime)
    {
        $query = self::find()->select(['count(*) as num', 'status'])
            ->where(['u_id' => $uid]);
        if ($starTime && $endTime) {
            $query->andWhere(['between', 'workDate', $starTime, $endTime]);
        }
        return $query->groupBy('status')
            ->asArray()
            ->all();
    }

    public static function statisticVacation($uid, $starTime, $endTime)
    {
        /*$query = self::find()->select(['count(*) as num', 'substatus'])
            ->where(['u_id' => $uid])
            ->andWhere(['status' => 7]);
        if ($starTime && $endTime) {
            $query->andWhere(['between', 'workDate', $starTime, $endTime]);
        }
        return $query->groupBy('substatus')
            ->asArray()
            ->all();*/
        $query = ApplyBaseModel::find()->select(['sum(oa_apply_leave.leave_sum) as num', 'oa_apply_leave.type'])
            ->leftJoin('oa_apply_leave','oa_apply_base.detail_id=oa_apply_leave.id')
            ->where(['oa_apply_base.applyer' => $uid,'oa_apply_base.status' => 1]);
        if ($starTime && $endTime) {
            $query->andWhere(['between', 'oa_apply_base.update_time', $starTime, $endTime]);
        }
        return $query->groupBy('oa_apply_leave.type')
            ->asArray()
            ->all();
    }
}
