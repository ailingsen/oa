<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_task_msg".
 *
 * @property integer $msg_id
 * @property integer $uid
 * @property integer $operator
 * @property integer $task_id
 * @property string $title
 * @property string $task_title
 * @property integer $is_read
 * @property integer $create_time
 */
class TaskMsgModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_task_msg';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'operator', 'task_id', 'is_read', 'menu', 'create_time'], 'integer'],
            [['title', 'task_title'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'msg_id' => 'Msg ID',
            'uid' => 'Uid',
            'operator' => 'Operator',
            'task_id' => 'Task ID',
            'title' => 'Title',
            'task_title' => 'Task Title',
            'is_read' => 'Is Read',
            'create_time' => 'Create Time',
            'menu' => 'Menu'
        ];
    }

    /**
     * 获取最新消息
     * @param int $uid
     * @param array $field
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getNewMsg($uid,$field = []) {
        $res = self::find()->select($field)->leftJoin('oa_members','oa_task_msg.operator=oa_members.u_id')
            ->where(['oa_task_msg.uid' => $uid])->orderBy('oa_task_msg.create_time DESC')->asArray()->one();
        if(isset($res['create_time']) && !empty($res['create_time'])) {
            $res['create_time_com'] = date('Y年m月d日',$res['create_time']);
            $res['create_time_time'] = date('H:i',$res['create_time']);
            $res['create_time'] = date('n月j日',$res['create_time']);
        }
        return $res;
    }

    /**
     * 消息置为已读
     * @param $uid
     */
    public static function setRead($uid) {
        self::updateAll(['is_read' => 1,'newest' => 0],['uid' => $uid]);
    }
}
