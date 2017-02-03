<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_survey".
 *
 * @property integer $survey_id
 * @property integer $u_id
 * @property string $title
 * @property string $explain
 * @property string $content
 * @property integer $status
 * @property integer $create_time
 */
class SurveyModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_survey';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['u_id', 'title', 'explain', 'content', 'create_time'], 'required'],
            [['u_id', 'status', 'create_time'], 'integer'],
            [['explain', 'content'], 'string'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'survey_id' => 'Survey ID',
            'u_id' => 'U ID',
            'title' => 'Title',
            'explain' => 'Explain',
            'content' => 'Content',
            'status' => 'Status',
            'create_time' => 'Create Time',
        ];
    }
}
