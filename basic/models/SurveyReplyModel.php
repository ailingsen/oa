<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_survey_reply".
 *
 * @property integer $reply_id
 * @property integer $survey_id
 * @property integer $u_id
 * @property string $replay_content
 * @property integer $create_time
 */
class SurveyReplyModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_survey_reply';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['survey_id', 'u_id', 'reply_content', 'create_time'], 'required'],
            [['survey_id', 'u_id', 'create_time'], 'integer'],
            [['reply_content'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'reply_id' => 'Reply ID',
            'survey_id' => 'Survey ID',
            'u_id' => 'U ID',
            'reply_content' => 'Reply Content',
            'create_time' => 'Create Time',
        ];
    }
}
