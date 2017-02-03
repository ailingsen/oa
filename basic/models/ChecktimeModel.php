<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_checktime".
 *
 * @property string $id
 * @property string $badgenumber
 * @property string $checktime
 * @property integer $day
 */
class ChecktimeModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_checktime';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['checktime', 'day'], 'integer'],
            [['badgenumber'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'badgenumber' => 'Badgenumber',
            'checktime' => 'Checktime',
            'day' => 'Day',
        ];
    }
}
