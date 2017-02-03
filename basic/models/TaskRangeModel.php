<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_task_range".
 *
 * @property integer $task_org_id
 * @property integer $task_id
 * @property integer $org_id
 */
class TaskRangeModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_task_range';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id', 'org_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'task_org_id' => 'Task Org ID',
            'task_id' => 'Task ID',
            'org_id' => 'Org ID',
        ];
    }

    /**
     *插入任务悬赏范围
     * @param $data
     * @return bool
     */
    public static function createX($data)
    {
        $taskRangeModel = new self;
        $taskRangeModel->attributes = $data;
        return $taskRangeModel->save(false);
    }
    /*
     * 获取悬赏任务范围相关数据
     */
    public static function getRewardRangeInfo($taskId)
    {
        return self::find()->select('oa_org.org_name,oa_org.org_id')->leftJoin('oa_org','oa_org.org_id=oa_task_range.org_id')
                ->where(['oa_task_range.task_id'=>$taskId])->asArray()->all();
    }
}
