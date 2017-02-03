<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_vacation_set".
 *
 * @property integer $set_id
 * @property integer $ini_annual_vacation
 * @property string $cal_cycle_start
 * @property string $cal_cycle_end
 * @property string $vacation_expire
 * @property integer $overtime_expire
 * @property integer $update_time
 * @property string $increase_rules
 */
class VacationSetModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_vacation_set';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ini_annual_vacation', 'overtime_expire', 'update_time'], 'integer'],
            [['cal_cycle_start', 'cal_cycle_end', 'vacation_expire', 'overtime_expire', 'update_time'], 'required'],
            [['cal_cycle_start', 'cal_cycle_end', 'vacation_expire'], 'string', 'max' => 10],
            [['increase_rules'], 'string', 'max' => 200], 
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'set_id' => 'Set ID',
            'ini_annual_vacation' => 'Ini Annual Vacation',
            'cal_cycle_start' => 'Cal Cycle Start',
            'cal_cycle_end' => 'Cal Cycle End',
            'vacation_expire' => 'Vacation Expire',
            'overtime_expire' => 'Overtime Expire',
            'update_time' => 'Update Time',
            'increase_rules' => 'Increase Rules'
        ];
    }

    /**
     * 插入
     * @param $data
     * @return bool
     */
    public static function createX($data)
    {
        $model = new self;
        $model->attributes = $data;
        return $model->save(false);
    }
}
