<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_reserve_room".
 *
 * @property integer $res_id
 * @property integer $room_id
 * @property integer $uid
 * @property integer $create_time
 * @property integer $reserve_time
 */
class ReserveRoomModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_reserve_room';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['room_id', 'uid'], 'required'],
            [['room_id', 'uid', 'create_time', 'reserve_time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'res_id' => 'Res ID',
            'room_id' => 'Room ID',
            'uid' => 'Uid',
            'create_time' => 'Create Time',
            'reserve_time' => 'Reserve Time',
        ];
    }

    /**
     * @param $uid
     * @param $pageSize
     * @param $curPage
     * @return array
     * 获取我预定的会议室
     */
    public static function getMyReserveRoomInfo($uid, $pageSize, $curPage)
    {
        $meetingInfo =  self::find()->select('oa_meeting_room.name, oa_reserve_room.res_id, oa_reserve_room.uid, oa_reserve_room.reserve_time, oa_reserve_room.readable')->leftJoin('oa_meeting_room','oa_meeting_room.room_id=oa_reserve_room.room_id')->where(['oa_reserve_room.uid'=>$uid]);
        //$meetingInfo = $meetingInfo->limit($pageSize)->offset($pageSize*($curPage-1))->orderBy(['oa_reserve_room.reserve_time'=> SORT_ASC])->asArray()->all();
        $meetingInfo = $meetingInfo->asArray()->all();
        $totalPage = ceil(count($meetingInfo)/$pageSize);
        return [
            'totalPage' => $totalPage,
            'meetingInfo' => $meetingInfo,
        ];
    }

    /**
     * @param $resId
     * @return array|null|\yii\db\ActiveRecord
     * 获取我预定的会议室详情页
     */
    public static function getMyReserveDetail($resId)
    {
        return self::find()->select('oa_meeting_room.name, oa_members.real_name,oa_reserve_room.res_id, oa_reserve_room.uid, oa_reserve_room.reserve_time,oa_reserve_room.cor_email_uid, oa_reserve_room.book_meeting_name, oa_reserve_room.book_meeting_desc')
            ->leftJoin('oa_meeting_room','oa_meeting_room.room_id=oa_reserve_room.room_id')
            ->leftJoin('oa_members','oa_members.u_id=oa_reserve_room.uid')->where(['oa_reserve_room.res_id' => $resId])->asArray()->one();
    }

    /**
     * @param $uid
     * @return array|\yii\db\ActiveRecord[]
     * 我参与的会议
     */
    public static function getMyMeetingInfo($uid,$pageSize,$curPage)
    {
        $meetingInfo =  self::find()->select('oa_meeting_room.name, oa_reserve_room.res_id, oa_reserve_room.uid, oa_reserve_room.reserve_time, oa_reserve_room.readable')->leftJoin('oa_meeting_room','oa_meeting_room.room_id=oa_reserve_room.room_id')->andFilterWhere(['like','cor_email_uid',','.$uid.',']);
        //$meetingInfo = $meetingInfo->limit($pageSize)->offset($pageSize*($curPage-1))->orderBy(['oa_reserve_room.reserve_time'=> SORT_ASC])->asArray()->all();
        $meetingInfo = $meetingInfo->asArray()->all();
        $totalPage = ceil(count($meetingInfo)/$pageSize);
        return [
            'totalPage' => $totalPage,
            'meetingInfo' => $meetingInfo,
        ];
    }
}
