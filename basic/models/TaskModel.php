<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;


/**
 * This is the model class for table "oa_task".
 *
 * @property integer $task_level
 * @property string $task_title
 * @property integer $task_id
 * @property integer $task_type
 * @property string $task_desc
 * @property integer $begin_time
 * @property integer $end_time
 * @property integer $delay_time
 * @property integer $pro_id
 * @property integer $point
 * @property integer $creater
 * @property integer $charger
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $status
 * @property integer $is_publish
 * @property integer $speed
 * @property integer $quality
 * @property string $work_note
 * @property string $reason
 * @property string $is_repoint
 */
class TaskModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_task';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_level', 'task_id', 'task_type', 'begin_time', 'end_time', 'delay_time', 'pro_id', 'point', 'creater', 'charger', 'create_time', 'status', 'is_publish', 'speed', 'quality'], 'integer'],
            [['task_id'], 'required'],
            [['task_desc'], 'string'],
            [['task_title'], 'string', 'max' => 100],
            [['work_note', 'reason'], 'string', 'max' => 200],
        ];
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'create_time',
                'updatedAtAttribute' => 'update_time',
                'value' => time()
            ]
            ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'task_level' => 'Task Level',
            'task_title' => 'Task Title',
            'task_id' => 'Task ID',
            'task_type' => 'Task Type',
            'task_desc' => 'Task Desc',
            'begin_time' => 'Begin Time',
            'end_time' => 'End Time',
            'delay_time' => 'Delay Time',
            'pro_id' => 'Pro ID',
            'point' => 'Point',
            'creater' => 'Creater',
            'charger' => 'Charger',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'status' => 'Status',
            'is_publish' => 'Is Publish',
            'speed' => 'Speed',
            'quality' => 'Quality',
            'work_note' => 'Work Note',
            'reason' => 'Reason',
            'is_repoint' => 'Is Repoint'
        ];
    }
    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['create'] = ['task_title', 'task_type', 'begin_time', 'end_time', 'creater'];
        return $scenarios;
    }

    /**
     * 插入任务
     * @param $data
     * @return bool
     */
    public static function createX($data)
    {
        $taskModel = new self;
        $taskModel->attributes = $data;
        $taskModel->scenario = "create";
        return $taskModel->save();
    }

    /*
     *获取我的任务列表
     */
    public static function getMyTaskList($uid, $proId, $taskType, $status, $beginTime, $endTime, $taskTitle, $overtime,$num, $current)
    {
        $taskListData = self::find()->select('oa_task.task_id, oa_task.delay_time,oa_task.create_time, oa_task.task_title, oa_task.task_type, oa_task.status, oa_task.pro_id, oa_task.end_time, oa_project.pro_name, oa_members.real_name,oa_task.task_level,oa_task.charger')
            ->leftJoin('oa_project', 'oa_project.pro_id=oa_task.pro_id');
        if (!empty($uid) && Yii::$app->controller->id == "task-list" && Yii::$app->controller->action->id == "my-task-list") {
            $taskListData->leftJoin('oa_members', 'oa_members.u_id=oa_task.creater')->where(['oa_task.charger' => $uid])->andWhere(['!=','oa_task.status',0]);
//            $readableSum = self::find()->where(['oa_task.charger' => $uid, 'readable'=>1])->count();
        }
        if (!empty($uid) && Yii::$app->controller->id == "task-list" && Yii::$app->controller->action->id == "my-release-list") {
            $taskListData->leftJoin('oa_members', 'oa_members.u_id=oa_task.charger')->where(['oa_task.creater' => $uid]);
//            $readableSum = self::find()->where(['oa_task.creater' => $uid, 'readable'=>1])->count();
        }
        if (!empty($proId)){
            $taskListData->andWhere(['oa_task.pro_id' => $proId]);
        }
        if(!empty($taskType)){
            $taskListData->andWhere(['oa_task.task_type'=>$taskType]);
        }
        if(($status != '' || $status >= 0) && strlen($status) > 0){
            $taskListData->andWhere(['oa_task.status' => $status]);
        }
        if(!empty($beginTime) && empty($endTime)){
            $taskListData->andWhere(['>=','oa_task.begin_time',$beginTime]);
        }
        if(!empty($endTime) && empty($beginTime)){
            $taskListData->andWhere(['<=','oa_task.end_time',$endTime]);
        }
        if((!empty($beginTime)) && (!empty($endTime))){
            $taskListData->andWhere(['>=','oa_task.begin_time',$beginTime])->andWhere(['<=','oa_task.end_time',$endTime]);
        }
        if(!empty($overtime) && $overtime == 7){
            $dataTime = time();
            $taskListData->andWhere(['<','oa_task.end_time',$dataTime])->andWhere(['in','oa_task.status',[1,2,3]]);
        }
        $totalPage = ceil($taskListData->andFilterWhere(['like', 'oa_task.task_title', $taskTitle])->count()/$num);
        $taskListData = $taskListData->andFilterWhere(['like', 'oa_task.task_title', $taskTitle])->orderBy(['oa_task.status' => SORT_ASC, 'oa_task.update_time' => SORT_DESC,'oa_task.create_time' => SORT_DESC])->limit($num)->offset($num*($current-1))->asArray()->all();

        return [
            'totalPage'    => $totalPage,
            'taskListData' => $taskListData,
//            'readableSum'  => $readableSum
        ];
        
    }


    public static function getTaskListDetails($taskId)
    {
        $taskDetailsData = self::find()->select('oa_task.task_id,oa_task.create_time,oa_project.pro_name,oa_project.pro_id,oa_task.work_note,oa_members.head_img, oa_task.task_type,oa_task.charger, oa_task.status, oa_task.task_level, oa_task.task_title, oa_task.task_desc, oa_task.begin_time, oa_task.end_time,oa_task.delay_time,oa_task.point,oa_task.is_publish,speed,quality,oa_task.reason,oa_task.is_repoint,oa_members.real_name, oa_reward_task.status as rewardStatus,oa_task.creater')
                            ->leftJoin('oa_members', 'oa_members.u_id=oa_task.charger')
                            ->leftJoin('oa_reward_task', 'oa_reward_task.task_id=oa_task.task_id')
                            ->leftJoin('oa_project', 'oa_project.pro_id=oa_task.pro_id')
                            ->where(['oa_task.task_id'=>$taskId])->asArray()->one();
        $taskDetailsData['createrName'] = MembersModel::find()->where(['u_id'=>$taskDetailsData['creater']])->asArray()->one()['real_name'];
        return $taskDetailsData;
    }

    /*
     * 获取工作情况相关数据
     */
    public static function getWorkingSituation($uid)
    {
        return self::find()->select('oa_task.status, oa_task.pro_id, oa_task.charger')->where(['charger'=> $uid])->asArray()->all();
    }

    /*
     * 积分榜相关数据
     */
    public static function getIntegral($beginData, $endData)
    {
        return self::find()->select('oa_task.speed, oa_task.quality, oa_members.real_name,oa_members.u_id, oa_members.head_img, oa_task.task_id')
            ->leftJoin('oa_members', 'oa_members.u_id=oa_task.charger')
            ->joinWith('taskskill')
            ->andWhere(['>', 'oa_task.update_time', $beginData])
            ->andWhere(['<', 'oa_task.update_time', $endData])
            ->andWhere(['oa_members.is_del'=>0])
            ->andWhere(['oa_task.status'=>4])
            ->asArray()->all();
    }

    public function getTaskskill()
    {
        // 第一个参数为要关联的子表模型类名，
        // 第二个参数指定 通过子表的customer_id，关联主表的id字段
        return $this->hasMany(TaskSkillModel::className(), ['task_id' => 'task_id']);
    }
    
}
