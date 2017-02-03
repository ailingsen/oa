<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_flexwork".
 *
 * @property integer $id
 * @property integer $store_id
 * @property integer $begin_time
 * @property integer $end_time
 * @property string $hours
 * @property string $note
 */
class ApplyFlexworkModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_flexwork';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'begin_time', 'end_time'], 'integer'],
            [['hours'], 'number'],
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
            'store_id' => 'Store ID',
            'begin_time' => 'Begin Time',
            'end_time' => 'End Time',
            'hours' => 'Hours',
            'note' => 'Note',
        ];
    }
}
