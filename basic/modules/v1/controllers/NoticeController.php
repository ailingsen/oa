<?php
namespace app\modules\v1\controllers;

use app\lib\FileUploadHelper;
use app\lib\Tools;
use app\models\FResponse;
use app\models\NoticeModel;
use app\modules\project\delegate\ProjectDelegate;
use app\modules\v1\delegate\NoticeDelegate;
use app\modules\v1\helper\NoticeHelper;
use Yii;
use Yii\base\Object;
use app\models\Mcache;

class NoticeController extends BaseController
{
    public $modelClass = 'app\models\NoticeModel';
    /**
     * 获取公告列表
     * $page 当前页
     */
    public function actionGetNotice()
    {
        $this->isPerm('NoticeView');
        $postData = json_decode(file_get_contents("php://input"));
        $page = $postData->page;
        $pageSize = $postData->pageSize;
        $offset = ($page-1)*$pageSize;
        $limit = $pageSize;
        $info = NoticeDelegate::getNoticeList($this->userInfo['u_id'],$limit, $offset);
        //处理是否有附件已经时间转换
        $info['notList'] = NoticeHelper::setData($info['notList']);
        $info['is_perm'] = $this->isPermStatus('NoticeManage');
        $info['is_create_perm'] = $this->isPermStatus('NoticeCreate');
        FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>$info]);
    }

    /**
     * 删除公告
     * $notice_id 公告ID
     */
    public function actionDelNotice()
    {
        $this->isPerm('NoticeManage');
        $postdata = json_decode(file_get_contents("php://input"), true);
        if( empty($postdata['notice_id']) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        if(\app\modules\notice\delegate\NoticeDelegate::is_notice($postdata['notice_id'])){
            FResponse::output(['code' => 20005, 'msg' => "该公告不存在或已删除", 'data'=>new Object()]);
        }
        $res = \app\modules\notice\delegate\NoticeDelegate::delNotice($postdata['notice_id']);
        if($res){
            FResponse::output(['code' => 20000, 'msg' => "删除成功", 'data'=>new Object()]);
        }else{
            FResponse::output(['code' => 20003, 'msg' => "删除失败", 'data'=>new Object()]);
        }
    }

    /**
     * 查看公告详情
     * $notice_id  公告ID
     */
    public function actionNoticeDetail()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if( empty($postdata['notice_id']) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        if(\app\modules\notice\delegate\NoticeDelegate::is_notice($postdata['notice_id'])){
            FResponse::output(['code' => 20005, 'msg' => "该公告不存在或已删除", 'data'=>new Object()]);
        }

        //设置已读
        if(!(\app\modules\notice\delegate\NoticeDelegate::isRead($postdata['notice_id'],$this->userInfo['u_id']))){
            \app\modules\notice\delegate\NoticeDelegate::setRead($postdata['notice_id'],$this->userInfo['u_id']);
        }

        //获取公告详情
        $info = NoticeDelegate::getNoticeDetail($postdata['notice_id']);
        //处理附件路径
        if(isset($info['att']) && count($info['att'])>0){
            foreach($info['att'] as $key=>$val){
                $info['att'][$key]['path'] = Yii::getAlias('@file_root').'/'.$val['file_path'].'/'.$val['real_name'];
            }
        }

        //处理img标签路径
        $detail = $info['content'];
        $explode =explode('<img src="',$detail);
        foreach( $explode as $k => $v ){
            if( $k == 0 ) continue;
            $explode[$k] = substr($this->apiDomain,0,strlen($this->apiDomain)-1).$explode[$k];
        }
        $temp = '';
        foreach( $explode as $k => $v ){
            if( $k != 0 ) $temp .= '<img src="';
            $temp .= $v;
        }
        $info['content'] = $temp;

        FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>$info]);
    }

    /**
     * 创建公告
     */
    public function actionCreateNotice()
    {
        $this->isPerm('NoticeCreate');
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        $postdata = json_decode(file_get_contents("php://input"), true);
        if(!isset($postdata['title']) || strlen($postdata['title'])<=0){
            FResponse::output(['code' => 20003, 'msg' => "公告标题不能为空", 'data'=>new Object()]);
        }
        if(!ProjectDelegate::isStrlen($postdata['title'],40)){
            FResponse::output(['code' => 20003, 'msg' => "公告标题最多40个字", 'data'=>new Object()]);
        }
        if(\app\modules\notice\delegate\NoticeDelegate::is_notice_title($postdata['title'])){
            FResponse::output(['code' => 20003, 'msg' => "该公告已存在", 'data'=>new Object()]);
        }
        if(!isset($postdata['content']) || strlen($postdata['content'])<=0){
            FResponse::output(['code' => 20003, 'msg' => "公告内容不能为空", 'data'=>new Object()]);
        }
        //将图片添加到内容后面
        if(isset($postdata['att']) && count($postdata['att'])>0 && is_array($postdata['att'])){
            foreach($postdata['att'] as $key=>$val){
                $postdata['content'] .= "<img src=\"".$val['url']."\" />";
            }
        }
        $mNotice = new NoticeModel();
        $mNotice->u_id=$this->userInfo['u_id'];
        $mNotice->title=$postdata['title'];
        $mNotice->content=$postdata['content'];
        $mNotice->create_time=time();
        if($mNotice->save(false)){
            $notice_id = Yii::$app->db->getLastInsertID();
            //添加极光推送
            Tools::msgJpush(1,$notice_id,$postdata['title']);
            $transaction->commit();
            FResponse::output(['code' => 20000, 'msg' => "添加成功", 'data'=>new Object()]);
        }else{
            $transaction->rollBack();
            FResponse::output(['code' => 20003, 'msg' => "添加失败", 'data'=>new Object()]);
        }
    }

    /**
     * 图片上传
     */
    public function actionUpload()
    {
        $data = FileUploadHelper::fileUpload(Yii::getAlias('@ueditor'),5,['gif', 'jpeg', 'jpg', 'png']);
        if($data){
            $res['file_path'] =Yii::getAlias('@file_root') . '/ueditor/php/upload/image/'.date('Ymd',time()).'/'.$data['data']['real_name'];
            $res['path'] = '/ueditor/php/upload/image/'.date('Ymd',time()).'/'.$data['data']['real_name'];
            FResponse::output(['code' => 20000, 'msg' => "上传成功", 'data'=>$res]);
            die;
        }else{
            FResponse::output(['code' => 20003, 'msg' => "上传失败,请重试！", 'data'=>new Object()]);
            die;
        }
    }

    /**
     * 附件下载
     */
    public function actionDownfile()
    {
        $file_path = Yii::$app->request->get('file_path');//文件目录
        $real_name = Yii::$app->request->get('real_name');//文件名称
        $file_name = Yii::$app->request->get('file_name');
        $status=NoticeHelper::getDownFile(Yii::getAlias('@file_root').'/'.$file_path.'/'.$real_name,$file_name);
        //$status=NoticeHelper::getDownFile("D:/www/oa4/file".$filepath,$file_name,$size);
        if($status==1){
            return array('code' => 0, 'msg' =>'文件不存在');
        }
    }

}