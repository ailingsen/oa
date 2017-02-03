<?php

namespace app\models;

use Yii;
use app\models\VacationUsedModel;

/**
 * This is the model class for table "oa_vacation_inventory".
 *
 * @property integer $id
 * @property integer $u_id
 * @property integer $expire_time
 * @property integer $is_valid
 * @property integer $creat_time
 * @property integer $over_time
 */
class VacationInventoryModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_vacation_inventory';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['u_id', 'expire_time', 'is_valid', 'creat_time', 'over_time'], 'integer'],
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
            'expire_time' => 'Expire Time',
            'is_valid' => 'Is Valid',
            'creat_time' => 'Creat Time',
            'over_time' => 'Over Time',
        ];
    }

    public static function getVaInventory($uid)
    {
        $time = time();
        $vaInventory = self::find()->select('(count(oa_vacation_inventory.u_id)/2) as workDays')->where(['oa_vacation_inventory.u_id' => $uid])
                        ->andWhere(['>','oa_vacation_inventory.expire_time',$time])->andWhere(['oa_vacation_inventory.is_valid'=> 0])
                        ->asArray()->one();
        //$vaInventory['workDays']=  $vaInventory['workDays']-VacationUsedModel::getTakeOff($uid)['usedNum'];
        $vaInventory['workDays']=  $vaInventory['workDays']-0;
        return $vaInventory;
    }
}
