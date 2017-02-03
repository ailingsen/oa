<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_holiday".
 *
 * @property string $hid
 * @property string $day
 * @property string $weekDay
 * @property integer $iswork
 */
class HolidayModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_holiday';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['day', 'iswork'], 'integer'],
            [['weekDay'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'hid' => 'Hid',
            'day' => 'Day',
            'weekDay' => 'Week Day',
            'iswork' => 'Iswork',
        ];
    }
    /**
     *  判断是否是节假日
     * @$beginTime string 开始日期
     * @$endTime string 结束日期
     */
    public static function isVacation($beginTime, $endTime) {
        $vacation = self::find()->select('hid, day, weekDay, iswork')->where(['>=', 'day' , strtotime($beginTime)])->andWhere(['<=', 'day', strtotime($endTime)])->andWhere(['iswork'=>1])->asArray()->one();
        return empty($vacation);
    }

    /**
     * 判断是否是工作日
     * @$beginTime string 开始日期
     * @$endTime string 结束日期
     */
    public static function isWork($beginTime, $endTime) {
        $work = self::find()->select('hid, day, weekDay, iswork')->where(['>=', 'day' , strtotime($beginTime)])->andWhere(['<=', 'day', strtotime($endTime)])->andWhere(['iswork'=>0])->asArray()->one();
        return empty($work);
    }
}
