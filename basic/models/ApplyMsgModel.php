<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_msg".
 *
 * @property integer $msg_id
 * @property integer $uid
 * @property integer $apply_id
 * @property integer $handler
 * @property string $title
 * @property integer $status
 * @property integer $is_read
 * @property integer $create_time
 */
class ApplyMsgModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_msg';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'apply_id', 'handler', 'status', 'is_read', 'create_time'], 'integer'],
            [['create_time'], 'required'],
            [['title'], 'string', 'max' => 1000],
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
            'apply_id' => 'Apply ID',
            'handler' => 'Handler',
            'title' => 'Title',
            'status' => 'Status',
            'is_read' => 'Is Read',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 获取最新消息
     * @param int $uid
     * @param array $field
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getNewMsg($uid,$field = []) {
        $res = self::find()->select($field)->leftJoin('oa_members','oa_apply_msg.handler=oa_members.u_id')
            ->where(['oa_apply_msg.uid' => $uid])->orderBy('oa_apply_msg.create_time DESC')->asArray()->one();
        if(isset($res['create_time']) && !empty($res['create_time'])) {
            $res['create_time_com'] = date('Y年m月d日',$res['create_time']);
            $res['create_time_time'] = date('H:i',$res['create_time']);
            $res['create_time'] = date('n月j日',$res['create_time']);
        }
        if(isset($res['status']) && !empty($res['status'])) {
            if($res['status'] == 1) {
                $res['status'] = '同意';
            }else if($res['status'] == 2) {
                $res['status'] = '已拒绝';
            }else if($res['status'] == 3) {
                $res['status'] = '已审批';
            }
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
