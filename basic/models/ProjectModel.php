<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_project".
 *
 * @property integer $pro_id
 * @property string $pro_name
 * @property integer $public_type
 * @property integer $u_id
 * @property integer $begin_time
 * @property integer $end_time
 * @property integer $delay_time
 * @property integer $create_time
 * @property integer $complete
 */
class ProjectModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_project';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['public_type', 'u_id', 'begin_time', 'end_time', 'delay_time', 'create_time', 'complete'], 'integer'],
            [['pro_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pro_id' => 'Pro ID',
            'pro_name' => 'Pro Name',
            'public_type' => 'Public Type',
            'u_id' => 'U ID',
            'begin_time' => 'Begin Time',
            'end_time' => 'End Time',
            'delay_time' => 'Delay Time',
            'create_time' => 'Create Time',
            'complete' => 'Complete',
        ];
    }

    public function getMember()
    {
        // 第一个参数为要关联的子表模型类名，
        // 第二个参数指定 通过子表的customer_id，关联主表的id字段
        return $this->hasOne(MembersModel::className(), ['u_id' => 'u_id']);
    }
    public function getProjectmember()
    {
        // 第一个参数为要关联的子表模型类名，
        // 第二个参数指定 通过子表的customer_id，关联主表的id字段
        return $this->hasMany(ProjectMemberModel::className(), ['pro_id' => 'pro_id']);
    }
    public function getTask()
    {
        // 第一个参数为要关联的子表模型类名，
        // 第二个参数指定 通过子表的customer_id，关联主表的id字段
        return $this->hasMany(TaskModel::className(), ['pro_id' => 'pro_id'])->where('oa_task.status!=0');
    }

    public function getTaskall()
    {
        // 第一个参数为要关联的子表模型类名，
        // 第二个参数指定 通过子表的customer_id，关联主表的id字段
        return $this->hasMany(TaskModel::className(), ['pro_id' => 'pro_id']);
    }

    /**
     * 指派任务可选择的项目
     * @param $uid
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getMyProject($uid)
    {
        $data = self::find()->select("oa_project.pro_name label,oa_project.pro_id nums,begin_time,end_time")
            ->leftJoin(['opm' => 'oa_project_member'], 'opm.pro_id=oa_project.pro_id')
            ->where("complete=0 AND opm.u_id=$uid AND end_time>" . time())
            ->asArray()
            ->all();
        return $data;
    }

    /**
     * 添加项目记录
     * @param $projectId
     * @param $content
     * @throws \yii\db\Exception
     */
    public static function addLog($projectId, $content)
    {
        $record = ['project_id' => intval($projectId), 'content' => $content, 'add_time' => time()];
        Yii::$app->db->createCommand()->insert('oa_project_log', $record)->execute();
    }

    /**
     * 根据项目ID获取项目基本信息
     * @param $proID
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function  getProjectInfo($proID)
    {
        return self::find()->where('pro_id=:proID', [':proID' => $proID])->asArray()->one();
    }
}
