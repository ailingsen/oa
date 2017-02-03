<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_field".
 *
 * @property string $field_id
 * @property string $model_id
 * @property string $field
 * @property string $title
 * @property string $describe
 * @property string $styleType
 * @property string $formtype
 * @property string $setting
 * @property string $listorder
 * @property integer $required
 * @property string $content
 * @property string $dateType
 * @property string $moneyType
 */
class ApplyFieldModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_field';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_id', 'listorder', 'required'], 'integer'],
            [['setting', 'content'], 'string'],
            [['field', 'formtype', 'dateType'], 'string', 'max' => 20],
            [['title', 'styleType'], 'string', 'max' => 30],
            [['describe'], 'string', 'max' => 255],
            [['moneyType'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'field_id' => 'Field ID',
            'model_id' => 'Model ID',
            'field' => 'Field',
            'title' => 'Title',
            'describe' => 'Describe',
            'styleType' => 'Style Type',
            'formtype' => 'Formtype',
            'setting' => 'Setting',
            'listorder' => 'Listorder',
            'required' => 'Required',
            'content' => 'Content',
            'dateType' => 'Date Type',
            'moneyType' => 'Money Type',
        ];
    }
}
