<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_checkout".
 *
 * @property integer $id
 * @property integer $check_date
 * @property integer $is_am
 * @property string $note
 */
class ApplyCheckoutModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_checkout';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['check_date', 'is_am'], 'integer'],
            [['note'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'check_date' => 'Check Date',
            'is_am' => 'Is Am',
            'note' => 'Note',
        ];
    }
}
