<?php
/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2016/11/10
 * Time: 14:48
 */

namespace app\modules\v1\delegate;


use app\models\ApplyMsgModel;
use app\models\ApprovalMsgModel;
use app\models\MeetingMsgModel;
use app\models\ProjectMsgModel;
use app\models\ReportMsgModel;
use app\models\TaskMsgModel;

class MessageDelegate
{
    /**
     * 我的申请未读消息
     * @param int $uid
     * @return int|string
     */
    public static function getUnreadApplyMsgNum($uid) {
        return ApplyMsgModel::find()->where(['uid' => $uid,'is_read' => 0])->count();
    }

    /**
     * 我的审批未读消息
     * @param int $uid
     * @return int|string
     */
    public static function getUnreadApprovalMsgNum($uid) {
        return ApprovalMsgModel::find()->where(['uid' => $uid,'is_read' => 0])->count();
    }

    /**
     * 会议未读消息
     * @param int $uid
     * @return int|string
     */
    public static function getUnreadMeetingMsgNum($uid) {
        return MeetingMsgModel::find()->where(['uid' => $uid,'is_read' => 0])->count();
    }

    /**
     * 任务未读消息
     * @param int $uid
     * @return int|string
     */
    public static function getUnreadTaskMsgNum($uid) {
        return TaskMsgModel::find()->where(['uid' => $uid,'is_read' => 0])->count();
    }

    /**
     * 工作报告未读消息
     * @param int $uid
     * @return int|string
     */
    public static function getUnreadReportMsgNum($uid) {
        return ReportMsgModel::find()->where(['uid' => $uid,'is_read' => 0])->count();
    }

    /**
     * 项目未读消息数
     * @param int $uid
     * @return int|string
     */
    public static function getUnreadProjectMsgNum($uid) {
        return ProjectMsgModel::find()->where(['uid' => $uid,'is_read' => 0])->count();
    }

    /**
     * @param $uid
     * 获取所有气泡
     */
    public static function getUnreadAll($uid)
    {
        //公告气泡
        $noticeNub = NoticeDelegate::getUnReadNoticeCount($uid);
        //项目气泡
        $proNub = ProjectMsgModel::find()->where(['uid' => $uid,'is_read'=>0])->count();
        //任务气泡
        $taskNub = TaskMsgModel::find()->where(['uid' => $uid,'is_read'=>0])->count();
        //申请气泡
        $applyNub = ApplyMsgModel::find()->where(['uid' => $uid,'is_read'=>0])->count();
        //会议室气泡
        $meetingNub = MeetingMsgModel::find()->where(['uid' => $uid,'is_read'=>0])->count();
        //审批
        $approvalNub = ApprovalMsgModel::find()->where(['uid' => $uid,'is_read'=>0])->count();
        //工作报告
        $workNub = ReportMsgModel::find()->where(['uid' => $uid,'is_read'=>0])->count();
        return [
            'noticeNub' => $noticeNub,
            'proNub' => $proNub,
            'taskNub' => $taskNub,
            'applyNub' => $applyNub,
            'meetingNub'=>$meetingNub,
            'approvalNub' => $approvalNub,
            'workNub' => $workNub
        ];
    }

}