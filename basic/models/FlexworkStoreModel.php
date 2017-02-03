<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_flexwork_store".
 *
 * @property integer $id
 * @property integer $begin_time
 * @property integer $end_time
 * @property string $hours
 * @property integer $valid
 * @property integer $create_time
 */
class FlexworkStoreModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_flexwork_store';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['begin_time', 'end_time', 'valid', 'create_time'], 'integer'],
            [['hours'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'begin_time' => 'Begin Time',
            'end_time' => 'End Time',
            'hours' => 'Hours',
            'valid' => 'Valid',
            'create_time' => 'Create Time',
        ];
    }
}
