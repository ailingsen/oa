<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_meeting_room".
 *
 * @property integer $room_id
 * @property string $name
 * @property string $desc
 * @property string $floor
 * @property integer $hot
 * @property integer $create_time
 */
class MeetingRoomModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_meeting_room';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['desc', 'create_time'], 'required'],
            [['desc'], 'string'],
            [['hot', 'create_time'], 'integer'],
            [['name', 'floor'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'room_id' => 'Room ID',
            'name' => 'Name',
            'desc' => 'Desc',
            'floor' => 'Floor',
            'hot' => 'Hot',
            'create_time' => 'Create Time',
        ];
    }
    /*
     * 查询所有会议室
     * return array
     */
    public static function getMeetingRome()
    {
        //查询所有会议室
        return self::find()->select('room_id, name, desc, floor, hot, create_time')->orderBy(['hot'=>SORT_DESC,'create_time'=>SORT_DESC])->asArray()->all();
    }

    /**
     * @param $roomId
     * @return array|null|\yii\db\ActiveRecord
     * 会议室市相关信息
     */
    public static function getMeetingReserveRome($roomId)
    {
        return self::find()->where(['room_id'=>$roomId])->asArray()->one();
    }
    /**
     * 查询会议室相关信息
     */
    public static function getMeetingInfo($meetingId)
    {
        return self::find()->select('name, desc, floor, hot')->where(['room_id'=>$meetingId])->asArray()->one();
    }

    /*
     * 编辑会议室
     */
    public static function editMeetingRoom($meetingId, $name, $desc, $floor, $hot)
    {
        return self::updateAll(['name' => $name, 'desc' => $desc,'floor' => $floor,'hot' => $hot],['room_id'=>$meetingId]);
    }
}
