<?php

namespace app\models;

use Yii;
use app\lib\Tools;

/**
 * This is the model class for table "oa_task_log".
 *
 * @property integer $task_log_id
 * @property integer $taskId
 * @property integer $task_type
 * @property integer $u_id
 * @property string $content
 * @property integer $create_time
 */
class TaskLogModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_task_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id', 'task_type', 'u_id', 'create_time'], 'integer'],
            [['content'], 'required'],
            [['content'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'task_log_id' => 'Task Log ID',
            'task_id' => 'Task ID',
            'task_type' => 'Task Type',
            'u_id' => 'U ID',
            'content' => 'Content',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * @param $userInfo
     * @param $logContent
     * @param $taskId
     * @return int
     * @throws \yii\db\Exception
     */
    public static function insertTaskLog($userInfo, $logContent, $taskId, $taskType=1)
    {
        return Yii::$app->db->createCommand()->insert('oa_task_log', ['u_id' => $userInfo['u_id'], 'content' => $logContent, 'create_time' => time(),'task_id' => $taskId,'task_type' => $taskType])->execute();
    }

    /**
     * 任务操作日志
     */
    public static function getTaskOperationLog($taskId,$taskType,$pageSize,$curPage)
    {
        $operationLogData =self::find()->select('oa_task_log.task_type, oa_task_log.content, oa_task_log.create_time, oa_members.head_img, oa_members.real_name')
            ->leftJoin('oa_members','oa_members.u_id=oa_task_log.u_id')
            ->where(['task_id'=>$taskId])
            ->andWhere(['task_type'=>$taskType])
            ->orderBy(['task_log_id' => SORT_DESC]);

        $totalPage = ceil($operationLogData->count()/$pageSize);
        $operationLogData =  $operationLogData->limit($pageSize)->offset($pageSize*($curPage-1))->asArray()->all();
        foreach ($operationLogData as $key => $val){
            $operationLogData[$key]['head_img'] = Tools::getHeadImg($val['head_img']);
        }
        return [
            'totalPage' => $totalPage,
            'operationLogData' => $operationLogData
        ];
    }

    /**
     * 插入任务日志表
     * @param $data
     * @return bool
     */
    public static function createX($data)
    {
        $model = new self;
        $model->attributes = $data;
        return $model->save(false);
    }
}
