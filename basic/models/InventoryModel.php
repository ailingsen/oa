<?php

namespace app\models;

use yii;
use app\models\VacationInventoryModel;


/**
 * This is the model class for table "oa_inventory".
 *
 * @property integer $id
 * @property string $inv_name
 * @property integer $effect_type
 * @property integer $unit
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $min_unit
 * @property integer $reset_type
 * @property integer $reset_time
 */
class InventoryModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_inventory';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['effect_type', 'unit', 'start_time', 'end_time', 'min_unit', 'reset_type', 'reset_time'], 'integer'],
            [['inv_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'inv_name' => 'Inv Name',
            'effect_type' => 'Effect Type',
            'unit' => 'Unit',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'min_unit' => 'Min Unit',
            'reset_type' => 'Reset Type',
            'reset_time' => 'Reset Time',
        ];
    }
    /**
     * 加调休假
     * @param $u_id
     * @param $increment
     * @return array|bool
     * @throws \yii\db\Exception
     */
    public function addTuneVacation($u_id, $increment)
    {
        $times = $increment * 2;
        $expireTime = VacationSetModel::find()->select('overtime_expire')->asArray()->one()['overtime_expire'];
        //使用事务
        $transaction = Yii::$app->db->beginTransaction();
        try {
            for ($i = 0; $i < $times; $i++) {
                $model = new VacationInventoryModel();
                $model->u_id = $u_id;
                $model->expire_time = time() + $expireTime * 24 * 3600;
                $model->is_valid = 0;
                $model->creat_time = time();
                if (!$model->save()) {
                    return $model->getErrors();
                }
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }
        return true;
    }

    /**
     * 减调休假
     * @param $u_id
     * @param $increment
     * @return array|bool
     * @throws \yii\db\Exception
     */
    public function reduceTuneVacation($u_id, $increment)
    {
        $increment_abs = abs($increment);
        $times = $increment_abs * 2;
        //查出可用的调休假
        $tune_vacation = (new \yii\db\Query())
            ->select('id')
            ->from('oa_vacation_inventory')
            ->where('u_id=:u_id', [':u_id' => $u_id])
            ->andWhere('is_valid=:is_valid', [':is_valid' => 0])
            ->orderBy('expire_time ASC')
            ->limit($times)
            ->all();
        //修改调休假的状态
        //使用事务
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($tune_vacation as $item) {
                $model = VacationInventoryModel::findOne($item['id']);
                $model->is_valid = 1;
                if (!$model->save()) {
                    return $model->getErrors();
                }
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }
        return true;
    }
}
