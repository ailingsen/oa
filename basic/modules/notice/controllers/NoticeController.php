<?php

namespace app\modules\notice\controllers;

use app\controllers\BaseController;
use app\lib\FileUploadHelper;
use app\lib\Tools;
use app\models\NoticeModel;
use app\modules\notice\delegate\NoticeDelegate;
use app\modules\notice\helper\NoticeHelper;
use app\modules\project\delegate\ProjectDelegate;
use Yii;

/**
 * Default controller for the `notice` module
 */
class NoticeController extends BaseController
{
    public $modelClass = 'app\models\NoticeModel';

    /**
     * 获取公告列表
     * $page 当前页
     */
    public function actionGetNotice()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        $page = isset($postdata['page']) ? $postdata['page'] : 1;
        $pageParam = NoticeHelper::setPage(1,$page);
        $info = NoticeDelegate::getNoticeList($this->userInfo['u_id'],$pageParam['limit'], $pageParam['offset'], $postdata);
        //处理是否有附件已经时间转换
        $info['notList'] = NoticeHelper::setData($info['notList']);
        $info['page']['curPage'] = $page;
        return ['code'=>1,'data'=>$info];
    }

    /**
     * 查看公告详情
     * $notice_id  公告ID
     */
    public function actionNoticeDetail()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if(NoticeDelegate::is_notice($postdata['notice_id'])){
            return ['code'=>-1,'msg'=>'该公告不存在或已删除'];
        }
        //获取公告详情
        $info = NoticeDelegate::getNoticeDetail($postdata['notice_id']);
        //设置已读
        if(isset($postdata['is_manager']) && $postdata['is_manager']==0 && !NoticeDelegate::isRead($postdata['notice_id'],$this->userInfo['u_id'])){
            NoticeDelegate::setRead($postdata['notice_id'],$this->userInfo['u_id']);
        }
        return ['code'=>1,'data'=>$info];
    }

    /**
     * 删除公告
     * $notice_id 公告ID
     */
    public function actionDelNotice()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if(NoticeDelegate::is_notice($postdata['notice_id'])){
            return ['code'=>-1,'msg'=>'该公告不存在或已删除'];
        }
        $res = NoticeDelegate::delNotice($postdata['notice_id']);
        if($res){
            return ['code'=>1,'msg'=>'删除成功'];
        }else{
            return ['code'=>-1,'msg'=>'删除失败'];
        }
    }

    /**
     * 创建公告
     */
    public function actionCreateNotice()
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        $postdata = json_decode(file_get_contents("php://input"), true);
        if(!isset($postdata['title']) || strlen($postdata['title'])<=0){
            return ['code'=>-1,'msg'=>'公告标题不能为空'];
        }
        if(!ProjectDelegate::isStrlen($postdata['title'],40)){
            return ['code'=>-1,'msg'=>'公告标题最多40个字'];
        }
        if(NoticeDelegate::is_notice_title($postdata['title'])){
            return ['code'=>-1,'msg'=>'该公告已存在'];
        }
        if(!isset($postdata['content']) || strlen($postdata['content'])<=0){
            return ['code'=>-1,'msg'=>'公告内容不能为空'];
        }
        $mNotice = new NoticeModel();
        $mNotice->u_id=$this->userInfo['u_id'];
        $mNotice->title=$postdata['title'];
        $mNotice->content=$postdata['content'];
        $mNotice->create_time=time();
        if($mNotice->save(false)){
            $notice_id = Yii::$app->db->getLastInsertID();
            if(count($postdata['att'])>0){
                //设置附件格式
                $att = NoticeHelper::setAtt($postdata['att'],$notice_id);
                //保存附件
                $res = NoticeDelegate::addAtt($att);
                if(!$res){
                    $transaction->rollBack();
                    return ['code'=>-1,'msg'=>'添加失败'];
                }else{
                    $transaction->commit();
                    //添加极光推送
                    Tools::msgJpush(1,$notice_id,$postdata['title']);
                    return ['code'=>1,'msg'=>'添加成功'];
                }
            }else{
                //添加极光推送
                Tools::msgJpush(1,$notice_id,$postdata['title']);
                $transaction->commit();
                return ['code'=>1,'msg'=>'添加成功'];
            }
        }else{
            $transaction->rollBack();
            return ['code'=>-1,'msg'=>'添加失败'];
        }
    }

    /**
     * 附件上传
     */
    public function actionUpload()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        $data = FileUploadHelper::fileUpload(Yii::getAlias('@notice'));
        if($data){
            echo  json_encode(array('code'=>1,'data'=>$data));
            die;
        }else{
            echo  json_encode(array('code'=>-1,'msg'=>'上传失败,请重试！'));
            die;
        }
    }

    /**
     * 删除已上传附件
     * @return array
     */
    public function actionDelAtt() {
        $postdata = json_decode(file_get_contents("php://input"), true);
        //删除相应文件
        if (file_exists(Yii::getAlias('@upload') . '/' . $postdata['file_path'] . '/'.$postdata['real_name'])) {
            if (unlink(Yii::getAlias('@upload') . '/' . $postdata['file_path'] . '/'.$postdata['real_name'])) {
                return ['code' => 1, 'msg' => '删除成功'];
            } else {
                return ['code' => -1, 'msg' => '删除失败'];
            }
        } else {
            return ['code' => 0, 'msg' => '该文件不存在'];
        }
    }

    /**
     * 附件下载
     */
    public function actionDownfile()
    {
        $filepath = Yii::$app->request->get('filepath');//文件目录和文件
        $file_name = Yii::$app->request->get('file_name');
        $size = Yii::$app->request->get('file_size');
        $status=NoticeHelper::getDownFile(Yii::getAlias('@upload').$filepath,$file_name);
        //$status=NoticeHelper::getDownFile("D:/www/oa4/file".$filepath,$file_name,$size);
        if($status==1){
            return array('code' => 0, 'msg' =>'文件不存在');
        }
    }

}
