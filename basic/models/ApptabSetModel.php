<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apptab_set".
 *
 * @property integer $id
 * @property integer $u_id
 * @property integer $project
 * @property integer $task
 * @property integer $attend
 * @property integer $apply
 * @property integer $meeting
 * @property integer $approval
 * @property integer $work
 * @property integer $survey
 */
class ApptabSetModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apptab_set';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['u_id'], 'required'],
            [['u_id', 'project', 'task', 'attend', 'apply', 'meeting', 'approval', 'work', 'survey'], 'integer'],
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
            'project' => 'Project',
            'task' => 'Task',
            'attend' => 'Attend',
            'apply' => 'Apply',
            'meeting' => 'Meeting',
            'approval' => 'Approval',
            'work' => 'Work',
            'survey' => 'Survey',
        ];
    }
}
