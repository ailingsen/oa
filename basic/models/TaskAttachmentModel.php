<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_task_attachment".
 *
 * @property integer $task_att_id
 * @property integer $task_id
 * @property integer $type
 * @property integer $task_type
 * @property integer $file_type
 * @property string $file_name
 * @property string $real_name
 * @property integer $file_size
 * @property string $file_path
 * @property integer $create_time
 */
class TaskAttachmentModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_task_attachment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id', 'type', 'task_type', 'file_size', 'create_time'], 'integer'],
            [['file_name', 'real_name', 'file_path', 'file_type'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'task_att_id' => 'Task Att ID',
            'task_id' => 'Task ID',
            'type' => 'Type',
            'task_type' => 'Task Type',
            'file_type' => 'File Type',
            'file_name' => 'File Name',
            'real_name' => 'Real Name',
            'file_size' => 'File Size',
            'file_path' => 'File Path',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 插入任务附件表
     * @param $data
     * @return bool
     */
    public static function createX($data)
    {
        $model = new self;
        $model->attributes = $data;
        return $model->save(false);
    }
    
    /*
     * 获取任务附件相关信息
     */
    public static function getAttachmentFileInfo($taskId, $type = 1, $taskType = 1)
    {
        return self::find()->select('task_att_id, type, task_id, task_type, file_name, real_name, file_path, file_size, file_type, create_time')->where(['task_id'=>$taskId, 'task_type'=>$taskType, 'type' => $type])->asArray()->all();
    }
}
