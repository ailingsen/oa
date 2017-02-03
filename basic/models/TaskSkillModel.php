<?php

namespace app\models;

use app\lib\errors\ValidateException;
use Yii;

/**
 * This is the model class for table "oa_task_skill".
 *
 * @property integer $task_skill_id
 * @property integer $task_id
 * @property integer $task_type
 * @property integer $skill_id
 */
class TaskSkillModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_task_skill';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id'], 'required'],
            [['task_id', 'task_type', 'skill_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'task_skill_id' => 'Task Skill ID',
            'task_id' => 'Task ID',
            'task_type' => 'Task Type',
            'skill_id' => 'Skill ID',
        ];
    }

    /**
     * 插入任务技能
     * @param $data
     * @return static
     * @throws ValidateException
     */
    public static function createX($data)
    {
        $model = new static();
        $model->attributes = $data;
        if (!$model->save(false) && $model->hasErrors()) {
            throw new ValidateException($model);
        }

        return $model;
    }

    /*
     *获取任务技能范围
     */
    public static function getTaskSkillRange($taskId, $taskType)
    {
        return self::find()->select('oa_task_skill.task_type, oa_skill.skill_name, oa_skill.skill_id')
                ->leftJoin('oa_skill', 'oa_skill.skill_id=oa_task_skill.skill_id')
                ->where(['oa_task_skill.task_id' => $taskId, 'oa_task_skill.task_type' => $taskType])->asArray()->all();
    }

    public static function countTaskSkillNub($taskId,$taskType)
    {
        return self::find()->select('count(oa_task_skill.skill_id) as skillNub')->where(['oa_task_skill.task_id'=>$taskId, 'oa_task_skill.task_type'=>$taskType])->asArray()->one();
    }
}
