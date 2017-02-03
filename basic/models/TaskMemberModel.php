<?php

namespace app\models;

use Yii;
use app\lib\Tools;

/**
 * This is the model class for table "oa_task_member".
 *
 * @property integer $task_mem_id
 * @property integer $task_id
 * @property integer $u_id
 * @property integer $is_charge
 * @property integer $create_time
 */
class TaskMemberModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_task_member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id', 'u_id', 'is_charge', 'create_time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'task_mem_id' => 'Task Mem ID',
            'task_id' => 'Task ID',
            'u_id' => 'U ID',
            'is_charge' => 'Is Charge',
            'create_time' => 'Create Time',
        ];
    }
    /*
     * 获取悬赏任务责任人相关信息
     */
    public static function getRewardResponsibilityData($taskId)
    {
        return self::find()->select('oa_task_member.u_id, oa_task_member.is_charge, oa_members.real_name, oa_members.head_img')
                ->leftJoin('oa_members', 'oa_members.u_id=oa_task_member.u_id')
                ->where(['task_id' => $taskId])->asArray()->all();
    }

    /**
     * 悬赏池 我的认领记录相关信息
     */
    public static function getRewardApplicationRecord($uid, $pageSize, $curPage)
    {
        $rewardAppRe = self::find()->select('oa_members.head_img,oa_reward_task.task_id, oa_reward_task.task_title, oa_members.real_name, oa_task_member.is_charge, oa_task_member.create_time,oa_reward_task.point,oa_reward_task.task_level,oa_reward_task.end_time')
                        ->leftJoin('oa_reward_task', 'oa_reward_task.task_id=oa_task_member.task_id')
                        ->leftJoin('oa_members', 'oa_members.u_id=oa_task_member.u_id')
                        ->where(['oa_task_member.u_id' => $uid]);
        $totalPage = ceil($rewardAppRe->count()/$pageSize);
        $rewardAppRe = $rewardAppRe->limit($pageSize)->offset($pageSize*($curPage-1))->orderBy(['oa_reward_task.create_time'=> SORT_DESC])->asArray()->all();
        foreach ($rewardAppRe as $key => $val){
            $rewardAppRe[$key]['head_img'] = Tools::getHeadImg($val['head_img']);
            $rewardAppRe[$key]['point'] = empty($rewardAppRe[$key]['point']) ? 0 : $rewardAppRe[$key]['point'];
        }
        return [
            'totalPage' => $totalPage,
            'rewardAppRe' => $rewardAppRe
        ];
    }

    public static function getRewardCharge($taskId)
    {
        return self::find()->select('u_id')->where(['task_id'=>$taskId,'is_charge'=>1])->asArray()->one();
    }
}
