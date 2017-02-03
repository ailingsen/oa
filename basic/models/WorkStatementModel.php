<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_work_statement".
 *
 * @property integer $work_id
 * @property integer $u_id
 * @property integer $type
 * @property string $cycle
 * @property integer $status
 * @property integer $commit_time
 * @property integer $approve_time
 * @property integer $approver
 * @property integer $create_time
 * @property integer $work_content
 * @property integer $plan_content
 */
class WorkStatementModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_work_statement';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'status', 'commit_time', 'approve_time', 'create_time'], 'integer'],
            [['cycle', 'commit_time', 'approve_time', 'u_id'], 'required'],
            [['cycle'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'work_id' => 'Work ID',
            'u_id' => 'U ID',
            'type' => 'Type',
            'cycle' => 'Cycle',
            'status' => 'Status',
            'commit_time' => 'Commit Time',
            'approve_time' => 'Approve Time',
            'approver' => 'Approver',
            'create_time' => 'Create Time',
            'work_content' => 'Work Content',
            'plan_content' => 'Plan Content',
        ];
    }

    /**
     * 插入
     * @param $data
     * @return bool
     */
    public static function createX($data)
    {
        $taskModel = new self;
        $taskModel->attributes = $data;
        return $taskModel->save(false);
    }

    /**
     * 修改
     * @param $workId
     * @param $stateMent
     * @return bool
     */
    public static function updateWorkStatement($workId, $stateMent)
    {
        $workModel = WorkStatementModel::findOne($workId);
        if (!$workModel) {
            return false;
        }
        if (isset($stateMent['status'])) {
            $workModel->status = $stateMent['status'];
        }
        if (isset($stateMent['cycle'])) {
            $workModel->cycle = $stateMent['cycle'];
        }
        if (isset($stateMent['commit_time'])) {
            $workModel->commit_time = $stateMent['commit_time'];
        }
        if (isset($stateMent['approve_time'])) {
            $workModel->approve_time = $stateMent['approve_time'];
        }
        if (isset($stateMent['approver'])) {
            $workModel->approver = $stateMent['approver'];
        }
        if (isset($stateMent['work_content'])) {
            $workModel->work_content = $stateMent['work_content'];
        }
        if (isset($stateMent['plan_content']) && $workModel->plan_content != $stateMent['plan_content']) {
            $workModel->plan_content = $stateMent['plan_content'];
        }
        return $workModel->save(false);
    }

    public function getToday()
    {
        // 第一个参数为要关联的子表模型类名，
        // 第二个参数指定 通过子表的customer_id，关联主表的id字段
        return $this->hasMany(WorkItemModel::className(), ['work_id' => 'work_id'])->onCondition('oa_work_item.type=1');
    }
    public function getTomorrow()
    {
        // 第一个参数为要关联的子表模型类名，
        // 第二个参数指定 通过子表的customer_id，关联主表的id字段
        return $this->hasMany(WorkItemModel::className(), ['work_id' => 'work_id'])->onCondition('oa_work_item.type=3');
    }

}
