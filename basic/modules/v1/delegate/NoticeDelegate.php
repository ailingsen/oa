<?php

namespace app\modules\v1\delegate;


//模型委托类。 处理控制器和动作列表

use app\models\MembersModel;
use app\models\NoticeModel;
use app\models\NoticeReadModel;
use Yii;

class NoticeDelegate
{

    /**
     * 获取公告列表信息
     * $data 请求数据
     */
    public static function getNoticeList($u_id,$limit,$offset)
    {
        $res =['notList'=>[],'totalPage'=>0];
        $mNotice = NoticeModel::find()->select('oa_notice.notice_id,oa_notice.u_id,oa_notice.title,oa_notice.content,oa_notice.create_time,oa_notice_read.notice_read_id,oa_members.real_name')->with('att')->where('oa_notice.is_del=1')
            ->leftJoin('oa_notice_read','oa_notice_read.notice_id=oa_notice.notice_id and oa_notice_read.u_id=:u_id',[':u_id'=>$u_id])
            ->leftJoin('oa_members','oa_members.u_id=oa_notice.u_id');
        $res['notList'] = $mNotice->offset($offset)->limit($limit)->orderBy('oa_notice.create_time desc')->asArray()->all();
        $res['totalPage']= ceil($mNotice->count()/$limit);
        return $res;
    }

    /**
     * 获取公告详情
     * $notice_id  公告ID
     */
    public static function getNoticeDetail($notice_id)
    {
        $res = NoticeModel::find()->select('oa_notice.notice_id,oa_notice.u_id,oa_notice.title,oa_notice.content,oa_notice.create_time,oa_members.real_name')->joinWith('att')->where('oa_notice.notice_id=:notice_id',[':notice_id'=>$notice_id])
            ->leftJoin('oa_members','oa_members.u_id = oa_notice.u_id')
            ->asArray()->one();
        //统计公告已读和未读人数
        $count = MembersModel::find()->where(['is_del'=>0])->count();
        $readCount = NoticeReadModel::find()->where(['notice_id'=>$notice_id])->count();
        $unReadCount = $count-$readCount;
        $res['readCount'] = $readCount;
        $res['unReadCount'] = $unReadCount;
        $res['create_time_f'] = date('Y-m-d',$res['create_time']);
        return $res;
    }

    /**
     * 获取未读公告数
    */
    public static function getUnReadNoticeCount($u_id)
    {
        $res = NoticeModel::find()->leftJoin('oa_notice_read','oa_notice_read.notice_id=oa_notice.notice_id and oa_notice_read.u_id=:u_id',[':u_id'=>$u_id])
            ->where('oa_notice_read.notice_read_id is null and oa_notice.is_del=1')->count();
        return $res;
    }

    /**
     * 获取最新一条未读公告详情
     */
    public static function getUnReadOneNoticeDetail($u_id)
    {
        $mNotice = NoticeModel::find()->select('oa_notice.notice_id,oa_notice.title,oa_notice.create_time')->where('oa_notice.is_del=1')
            ->leftJoin('oa_notice_read','oa_notice_read.notice_id=oa_notice.notice_id and oa_notice_read.u_id=:u_id',[':u_id'=>$u_id])
            ->andWhere('oa_notice_read.notice_read_id is null');
        $res = $mNotice->orderBy('oa_notice.create_time desc')->asArray()->one();
        $res['create_time'] = date('n月j日',$res['create_time']);
        return $res;
    }

    /**
     * 获取最新一条公告详情
     */
    public static function getNewNoticeDetail()
    {
        $mNotice = NoticeModel::find()->select('oa_notice.notice_id,oa_notice.title,oa_notice.create_time')->where('oa_notice.is_del=1');
        $res = $mNotice->orderBy('oa_notice.create_time desc')->asArray()->one();
        $res['create_time'] = date('n月j日',$res['create_time']);
        return $res;
    }


}