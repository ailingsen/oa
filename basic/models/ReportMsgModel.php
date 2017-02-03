<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_report_msg".
 *
 * @property integer $msg_id
 * @property integer $uid
 * @property integer $operator
 * @property integer $work_id
 * @property string $title
 * @property integer $is_read
 * @property integer $create_time
 */
class ReportMsgModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_report_msg';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'operator', 'work_id', 'is_read', 'create_time'], 'integer'],
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
            'operator' => 'Operator',
            'work_id' => 'Work ID',
            'title' => 'Title',
            'is_read' => 'Is Read',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 最新消息
     * @param int $uid
     * @param array $field
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getNewMsg($uid,$field = []) {
        $res = self::find()->select($field)->leftJoin('oa_members','oa_report_msg.operator=oa_members.u_id')
            ->where(['oa_report_msg.uid' => $uid])->orderBy('oa_report_msg.create_time DESC')->asArray()->one();
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
