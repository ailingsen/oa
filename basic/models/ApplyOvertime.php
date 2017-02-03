<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_overtime".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $begin_time
 * @property integer $end_time
 * @property string $hours
 * @property string $note
 */
class ApplyOvertime extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_overtime';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'begin_time', 'end_time'], 'integer'],
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
            'type' => 'Type',
            'begin_time' => 'Begin Time',
            'end_time' => 'End Time',
            'hours' => 'Hours',
            'note' => 'Note',
        ];
    }
}
