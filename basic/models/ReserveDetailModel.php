<?php

namespace app\models;

use Yii;
use app\models\OrgMemberModel;

/**
 * This is the model class for table "oa_reserve_detail".
 *
 * @property integer $det_id
 * @property integer $res_id
 * @property integer $uid
 * @property integer $room_id
 * @property integer $time_type
 * @property integer $reserve_time
 * @property integer $create_time
 */
class ReserveDetailModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_reserve_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['res_id', 'uid', 'room_id', 'time_type', 'create_time'], 'required'],
            [['res_id', 'uid', 'room_id', 'time_type', 'reserve_time', 'create_time'], 'integer'],
            [['room_id', 'time_type', 'reserve_time'], 'unique', 'targetAttribute' => ['room_id', 'time_type', 'reserve_time'], 'message' => 'The combination of Room ID, Time Type and Reserve Time has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'det_id' => 'Det ID',
            'res_id' => 'Res ID',
            'uid' => 'Uid',
            'room_id' => 'Room ID',
            'time_type' => 'Time Type',
            'reserve_time' => 'Reserve Time',
            'create_time' => 'Create Time',
        ];
    }
    
    /*
     *获取跟会议室相关的数据
     * return array 
     */
    public static function getConferenceRoomRelated($room_id, $date)
    {
        $roomData = self::find()->select('oa_reserve_detail.det_id, oa_reserve_detail.uid,oa_reserve_detail.res_id, oa_reserve_detail.time_type, oa_reserve_detail.create_time, oa_org.org_id, oa_org.org_name, oa_members.real_name')
            ->leftJoin('oa_members', 'oa_members.u_id=oa_reserve_detail.uid')
            ->leftJoin('oa_org_member', 'oa_members.u_id=oa_org_member.u_id')
            ->leftJoin('oa_org', 'oa_org_member.org_id=oa_org.org_id')
            ->where(['room_id'=>$room_id, 'reserve_time'=>strtotime($date)])->asArray()->all();
        foreach ($roomData as $key => $val){
            $roomData[$key]['memInfo'] =OrgMemberModel::getOrgMemberitem($val['uid']);
        }
        
        return $roomData;
    }
    /*
     * 获取会议室相关信息
     * return array
     */
    public static function getRoomRelatedInformation($room, $time_type, $reserve_time)
    {
        return self::find()->select('det_id')->where(['room_id'=>$room, 'time_type'=>$time_type, 'reserve_time'=>$reserve_time])->asArray()->one();
    }

    public static function getMeetingTimeInfo()
    {
    }
}
