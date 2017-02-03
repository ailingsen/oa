<?php

namespace app\models;

use app\modules\task\helper\TaskHelper;
use app\modules\task\Task;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "oa_reward_task".
 *
 * @property integer $task_id
 * @property integer $task_level
 * @property string $task_title
 * @property string $task_desc
 * @property integer $begin_time
 * @property integer $end_time
 * @property integer $delay_time
 * @property integer $point
 * @property integer $creater
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $status
 * @property integer $is_publish
 * @property integer $is_over
 * @property integer $sub_status
 */
class RewardTaskModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_reward_task';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id'], 'required'],
            [['task_id', 'task_level', 'begin_time', 'end_time', 'delay_time', 'point', 'creater', 'create_time', 'status', 'is_publish', 'sub_status'], 'integer'],
            [['task_desc'], 'string'],
            [['task_title'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'task_id' => 'Task ID',
            'task_level' => 'Task Level',
            'task_title' => 'Task Title',
            'task_desc' => 'Task Desc',
            'begin_time' => 'Begin Time',
            'end_time' => 'End Time',
            'delay_time' => 'Delay Time',
            'point' => 'Point',
            'creater' => 'Creater',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'status' => 'Status',
            'is_publish' => 'Is Publish',
            'is_over' => 'Is Over',
            'sub_status' => 'Sub Status'
        ];
    }
    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['create'] = ['task_title', 'begin_time', 'end_time', 'creater'];
        return $scenarios;
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
     * 悬赏池数据列表
     */
    public static function getRewardData($status, $taskTitle, $num, $current,$orgId)
    {
        $rewardData = self::find()->select('oa_reward_task.task_id,oa_reward_task.status, oa_reward_task.task_level,oa_reward_task.task_title, oa_reward_task.point, oa_reward_task.create_time,oa_reward_task.end_time, oa_members.real_name')
            ->leftJoin('oa_members', 'oa_members.u_id=oa_reward_task.creater')
            ->leftJoin('oa_task_range', 'oa_task_range.task_id=oa_reward_task.task_id')->where(['oa_reward_task.is_publish'=> 1,'oa_task_range.org_id'=>$orgId]);
        if(!empty($status)){
            $rewardData = $rewardData->andWhere(['oa_reward_task.status'=>$status]);
        }
        $rewardData = $rewardData->andWhere(['like', 'oa_reward_task.task_title', $taskTitle]);
        $totalPage = ceil($rewardData->count()/$num);
        $rewardData = $rewardData->orderBy(['oa_reward_task.status'=> 'SORT_ASC', 'oa_reward_task.update_time'=> 'SORT_DESC', 'oa_reward_task.create_time' => 'SORT_DESC'])->limit($num)->offset($num*($current-1))->asArray()->all();
        foreach ($rewardData as $key => $val){
            //相关责任人信息
            $rewardData[$key]['responInfo'] = TaskMemberModel::getRewardResponsibilityData($val['task_id']);
            $rewardData[$key]['chargeUid'] = TaskMemberModel::getRewardCharge($val['task_id'])['u_id'];
            if( $val['end_time']<time()){
                $rewardData[$key]['overtime'] = 1;
            }
        }
//        $readableSum = self::find()->where(['readable'=>1])->count();
        return [
            'totalPage'   => $totalPage,
            'rewardData'  => $rewardData,
//            'readableSum' => $readableSum,
        ];
    }


    /**
     * 我的悬赏数据列表
     */
    public static function getMyRewardData($uid, $taskTitle, $status, $pageSize, $curPage)
    {
        $myReData = self::find()->select('oa_members.real_name, oa_reward_task.task_title,oa_reward_task.end_time, oa_reward_task.task_id, oa_reward_task.status, oa_reward_task.is_publish, oa_reward_task.sub_status, oa_reward_task.point, oa_reward_task.create_time, oa_reward_task.task_level')
                    ->leftJoin('oa_members', 'oa_members.u_id=oa_reward_task.creater')->where(['oa_reward_task.creater' => $uid]);
        //我的悬赏数据列表
        if($status == ''){
           
        }
        //待发布
        if($status == 1){
            $myReData = $myReData->andWhere(['oa_reward_task.is_publish' => 0]);
        }
        //待认领
        if($status == 2){
            $myReData = $myReData->andWhere(['oa_reward_task.status' => 1])->andWhere(['oa_reward_task.sub_status' => 1])->andWhere(['oa_reward_task.is_publish' => 1]);
        }
        //待确认
        if($status == 3){
            $myReData = $myReData->andWhere(['oa_reward_task.status' => 1])->andWhere(['oa_reward_task.sub_status' => 2])->andWhere(['oa_reward_task.is_publish' => 1]);
        }
        //已指派
        if($status == 4){
            $myReData = $myReData->andWhere(['oa_reward_task.status' => 2])->andWhere(['oa_reward_task.is_publish' => 1]);
        }
        //已关闭
        if($status == 5){
            $myReData = $myReData->andWhere(['oa_reward_task.status' => 5]);
        }
        $myReData = $myReData->andFilterWhere(['like', 'oa_reward_task.task_title',$taskTitle])->orderBy('oa_reward_task.status ASC,oa_reward_task.update_time DESC, oa_reward_task.update_time DESC');
        $totalPage = ceil($myReData->count()/$pageSize);
        $myReData = $myReData->limit($pageSize)->offset($pageSize*($curPage-1))->asArray()->all();
        $myReData = TaskHelper::doTaskListData($myReData);
        return [
            'totalPage' => $totalPage,
            'myReData'  => $myReData
        ];
    }
    /**
     * @param $taskId
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getRewardTaskDetails($taskId)
    {
        $taskDetails = self::find()->select('oa_members.head_img, oa_reward_task.task_id,oa_reward_task.status, oa_reward_task.task_level, oa_reward_task.task_title, oa_reward_task.task_desc, oa_reward_task.begin_time, oa_reward_task.end_time,oa_reward_task.delay_time,oa_reward_task.point,oa_reward_task.creater,oa_reward_task.is_publish,oa_reward_task.sub_status,oa_reward_task.create_time')
            ->leftJoin('oa_task', 'oa_task.task_id=oa_reward_task.task_id')
            ->leftJoin('oa_members', 'oa_members.u_id=oa_task.charger')
            ->where(['oa_reward_task.task_id'=>$taskId])->asArray()->one();
        $taskDetails['createrName'] = MembersModel::find()->where(['u_id'=>$taskDetails['creater']])->asArray()->one()['real_name'];
        return $taskDetails;
    }


}
