<?php

namespace app\modules\notice\delegate;


//模型委托类。 处理控制器和动作列表

use app\models\NoticeModel;
use app\models\NoticeReadModel;
use Yii;

class NoticeDelegate {

    /**
     * 获取公告列表信息
     * $data 请求数据
    */
    public static function getNoticeList($u_id,$limit,$offset,$data)
    {
        $res =['notList'=>[],'notCount'=>0];
        $mNotice = NoticeModel::find()->select('oa_notice.*,oa_notice_read.notice_read_id,oa_members.real_name')->with('att')->where('oa_notice.is_del=1')
            ->leftJoin('oa_notice_read','oa_notice_read.notice_id=oa_notice.notice_id and oa_notice_read.u_id=:u_id',[':u_id'=>$u_id])
            ->leftJoin('oa_members','oa_members.u_id=oa_notice.u_id');
        if(isset($data['title']) && strlen($data['title'])>0){
            $mNotice->andWhere(['like','title',$data['title']]);
        }
        if(isset($data['begin_time']) && !empty($data['begin_time'])){
            $begin_time = strtotime($data['begin_time']);
            $mNotice->andWhere(['>=','oa_notice.create_time',$begin_time]);
        }
        if(isset($data['end_time']) && !empty($data['end_time'])){
            $end_time = strtotime($data['end_time']);
            $mNotice->andWhere(['<=','oa_notice.create_time',$end_time]);
        }
        $res['notList'] = $mNotice->offset($offset)->limit($limit)->orderBy('oa_notice.create_time desc')->asArray()->all();
        $res['page']['sumPage'] = ceil($mNotice->count()/$limit);
        return $res;
    }

    /**
     * 获取公告详情
     * $notice_id  公告ID
    */
    public static function getNoticeDetail($notice_id)
    {
        $res = NoticeModel::find()->select('oa_notice.*,oa_members.real_name')->joinWith('att')->where('oa_notice.notice_id=:notice_id',[':notice_id'=>$notice_id])
            ->leftJoin('oa_members','oa_members.u_id = oa_notice.u_id')
            ->asArray()->one();
        $res['create_time_f'] = date('Y-m-d H:i:s',$res['create_time']);
        return $res;
    }

    /**
     * 设置已读
    */
    public static function setRead($notice_id,$u_id)
    {
        //判断是否已经设置为已读
        $info = NoticeReadModel::find()->where('notice_id=:notice_id and u_id=:u_id',[':notice_id'=>$notice_id,':u_id'=>$u_id])->asArray()->one();
        if(!isset($info['notice_id'])){
            $status = Yii::$app->db->createCommand()->insert('oa_notice_read',[
                'notice_id' => $notice_id,
                'u_id' => $u_id,
                'create_time'=>time()
            ])->execute();
            return $status;
        }else{
            return true;
        }
    }

    /**
     * 删除公告
     * $notice_id 公告ID
    */
    public static function delNotice($notice_id)
    {
        $mNotice = NoticeModel::findOne($notice_id);
        $mNotice->is_del=-1;
        $res = $mNotice->save(false);
        return $res;
    }

    /**
     * 判断公告是否存在
    */
    public static function is_notice($notice_id)
    {
        $info = NoticeModel::find()->where('notice_id=:notice_id and is_del=1',[':notice_id'=>$notice_id])->asArray()->one();
        if(isset($info['notice_id'])){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 根据公告标题判断公告是否存在
     */
    public static function is_notice_title($title)
    {
        $info = NoticeModel::find()->where('title=:title and is_del=1',[':title'=>$title])->asArray()->one();
        if(isset($info['notice_id'])){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 创建公告保存附件
    */
    public static function addAtt($data)
    {
        $res = Yii::$app->db->createCommand()->batchInsert('oa_notice_attachment',['notice_id','file_name','real_name','file_size','file_path','file_type','create_time'],$data)->execute();
        return $res;
    }

    /**
     * 判断是否已读
    */
    public static function isRead($notice_id,$u_id)
    {
        $res = NoticeReadModel::find()->where('notice_id=:notice_id and u_id=:u_id',[':notice_id'=>$notice_id,':u_id'=>$u_id])->asArray()->one();
        if(isset($res['notice_id'])){
            return true;
        }
        return false;
    }



}