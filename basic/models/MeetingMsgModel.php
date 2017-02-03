<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_meeting_msg".
 *
 * @property integer $msg_id
 * @property integer $uid
 * @property integer $res_id
 * @property integer $sponsor
 * @property string $title
 * @property string $meeting_name
 * @property string $room_name
 * @property integer $begin_time
 * @property integer $end_time
 * @property integer $is_read
 * @property integer $create_time
 */
class MeetingMsgModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_meeting_msg';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'res_id', 'sponsor', 'begin_time', 'end_time', 'is_read', 'create_time'], 'integer'],
            [['title', 'meeting_name', 'room_name'], 'string', 'max' => 1000],
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
            'res_id' => 'Res ID',
            'sponsor' => 'Sponsor',
            'title' => 'Title',
            'meeting_name' => 'Meeting Name',
            'room_name' => 'Room Name',
            'begin_time' => 'Begin Time',
            'end_time' => 'End Time',
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
        $res = self::find()->select($field)->leftJoin('oa_members','oa_meeting_msg.sponsor=oa_members.u_id')
            ->where(['oa_meeting_msg.uid' => $uid])->orderBy('oa_meeting_msg.create_time DESC')->asArray()->one();
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
