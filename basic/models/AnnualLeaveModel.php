<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_annual_leave".
 *
 * @property integer $al_id
 * @property integer $u_id
 * @property double $normal_leave
 * @property double $delay_leave
 * @property double $manual_leave
 */
class AnnualLeaveModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_annual_leave';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['u_id'], 'integer'],
            [['normal_leave', 'delay_leave', 'manual_leave'], 'number'],
            [['u_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'al_id' => 'Al ID',
            'u_id' => 'U ID',
            'normal_leave' => 'Normal Leave',
            'delay_leave' => 'Delay Leave',
            'manual_leave' => 'Manual Leave',
        ];
    }

    /*
     * 获取年假相关信息
     */
    public static function getAnnualLeave($uid)
    {
        return self::find()->select('u_id, normal_leave, delay_leave')->where(['u_id'=>$uid])->asArray()->one();
    }

    /*
     * 修改年假
     * @param $uid
     */
    public static function updateAnnualLeave($uid, $manual_value)
    {
        $annualLeave = self::find()->where(['u_id'=>$uid])->asArray()->one();
        $normal_leave = $annualLeave['normal_leave']+$manual_value;
        $manual_leave = $annualLeave['manual_leave']+$manual_value;
        $updateAnnualLeave = self::updateAll(['normal_leave'=>$normal_leave, 'manual_leave'=>$manual_leave],['u_id'=>$uid]);
        if($updateAnnualLeave){
            return true;
        }
    }
}
