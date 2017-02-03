<?php
namespace app\modules\v1\controllers;

use app\models\FResponse;
use app\models\SurveyModel;
use app\models\SurveyReplyModel;
use app\modules\project\delegate\ProjectDelegate;
use app\modules\v1\delegate\SurveyDelegate;
use app\modules\v1\helper\SurveyHelper;
use Yii;
use Yii\base\Object;
use app\models\Mcache;

class SurveyController extends BaseController
{
    public $modelClass = 'app\models\SurveyModel';
    /**
     * 调研列表
     * $page
     */
    public function actionSurveyList()
    {
        $this->isPerm('SurveyList');
        $postData = json_decode(file_get_contents("php://input"), true);
        if( empty($postData['page']) || empty($postData['pageSize']) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $page = $postData['page'];
        $pageSize = $postData['pageSize'];
        $offset = ($page-1)*$pageSize;
        $limit = $pageSize;
        $surveyList = SurveyDelegate::getSurveyList(1,$this->userInfo['u_id'],$limit,$offset);
        //设置数据格式
        $surveyList['surList'] = SurveyHelper::setSurveyData($surveyList['surList']);
        FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>$surveyList]);
    }

    /**
     * 我发布的调研
     * $page
     */
    public function actionMySurveyList()
    {
        $this->isPerm('SurveyMine');
        $postData = json_decode(file_get_contents("php://input"), true);
        if( empty($postData['page']) || empty($postData['pageSize']) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $page = $postData['page'];
        $pageSize = $postData['pageSize'];
        $offset = ($page-1)*$pageSize;
        $limit = $pageSize;
        $surveyList = SurveyDelegate::getSurveyList(2,$this->userInfo['u_id'],$limit,$offset);
        //设置数据格式
        $surveyList['surList'] = SurveyHelper::setSurveyData($surveyList['surList']);
        FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>$surveyList]);
    }

    /**
     *查看调研详情
     * $survey_id
     */
    public function actionSurveyDetail()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if(!(isset($postdata['survey_id']) && $postdata['survey_id']>0)){
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $surveyInfo = \app\modules\survey\delegate\SurveyDelegate::getSurveyDetail($postdata['survey_id']);
        if($surveyInfo)
        {
            //格式化时间
            $surveyInfo['create_time_f'] = SurveyHelper::setFormatDate($surveyInfo['create_time']);

            //处理img标签路径
            $detail = $surveyInfo['content'];
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
            $surveyInfo['content'] = $temp;

            FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>$surveyInfo]);
        }else{
            FResponse::output(['code' => 20003, 'msg' => "该调研不存在", 'data'=>new Object()]);
        }
    }

    /**
     *查看我发布的调研详情
     * $survey_id
     */
    public function actionMySurveyDetail()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if((!(isset($postdata['survey_id']) && $postdata['survey_id']>0)) || empty($postdata['page']) || empty($postdata['pageSize'])){
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $surveyInfo = \app\modules\survey\delegate\SurveyDelegate::getMySurveyDetail($postdata['survey_id'],$this->userInfo['u_id']);
        if($surveyInfo)
        {
            $page = $postdata['page'];
            $pageSize = $postdata['pageSize'];
            $offset = ($page-1)*$pageSize;
            $limit = $pageSize;
            $replyInfo = SurveyDelegate::getMySurveyReply($postdata['survey_id'],$limit,$offset);
            //格式化时间
            $surveyInfo['create_time_f'] = SurveyHelper::setFormatDate($surveyInfo['create_time']);

            //处理img标签路径
            $detail = $surveyInfo['content'];
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
            $surveyInfo['content'] = $temp;

            //设置数据格式
            $replyInfo['repList'] = \app\modules\survey\helper\SurveyHelper::setSurveyReplyData($replyInfo['repList']);
            FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>['surveyInfo'=>$surveyInfo,'replyInfo'=>$replyInfo]]);
        }else{
            FResponse::output(['code' => 20003, 'msg' => "没有权限或该调研不存在", 'data'=>new Object()]);
        }
    }

    /**
     * 回复调研
     * $survey_id
     * $reply_content
     */
    public function actionSurveyReply()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if(!(isset($postdata['survey_id']) && $postdata['survey_id']>0)){
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        if(!(isset($postdata['reply_content']) && strlen($postdata['reply_content'])>0)){
            FResponse::output(['code' => 20001, 'msg' => "回复内容不能为空", 'data'=>new Object()]);
        }
        if(!ProjectDelegate::isStrlen($postdata['reply_content'],600)){
            FResponse::output(['code' => 20001, 'msg' => "回复内容限制600字以内!", 'data'=>new Object()]);
        }
        $surveyInfo = \app\modules\survey\delegate\SurveyDelegate::getSurveyDetail($postdata['survey_id']);
        if(!isset($surveyInfo['status']) || $surveyInfo['status']==2){
            FResponse::output(['code' => 20001, 'msg' => "该调研已结束!", 'data'=>new Object()]);
        }
        $srModel = new SurveyReplyModel();
        $srModel->reply_content = $postdata['reply_content'];
        $srModel->u_id = $this->userInfo['u_id'];
        $srModel->survey_id = $postdata['survey_id'];
        $srModel->create_time = time();
        if($srModel->save(false)){
            FResponse::output(['code' => 20000, 'msg' => "提交成功", 'data'=>new Object()]);
        }else{
            FResponse::output(['code' => 20003, 'msg' => "回复失败，请重试!", 'data'=>new Object()]);
        }
    }

    /**
     * 创建调研
     * $title
     * $explain
     * $content
     */
    public function actionCreateSurvey()
    {
        $this->isPerm('SurveyPublish');
        $postdata = json_decode(file_get_contents("php://input"), true);
        if(!(isset($postdata['title']) && strlen($postdata['title'])>0)){
            FResponse::output(['code' => 20001, 'msg' => "调研标题不能为空!", 'data'=>new Object()]);
        }
        if(!ProjectDelegate::isStrlen($postdata['title'],60)){
            FResponse::output(['code' => 20001, 'msg' => "调研标题限制60字以内!", 'data'=>new Object()]);
        }
        $isTitle = \app\modules\survey\delegate\SurveyDelegate::searchTitleSurvey($postdata['title']);
        if($isTitle){
            FResponse::output(['code' => 20001, 'msg' => "该调研标题已存在!", 'data'=>new Object()]);
        }
        if(!(isset($postdata['explain']) && strlen($postdata['explain'])>0)){
            FResponse::output(['code' => 20001, 'msg' => "调研说明不能为空!", 'data'=>new Object()]);
        }
        if(!ProjectDelegate::isStrlen($postdata['explain'],600)){
            FResponse::output(['code' => 20001, 'msg' => "调研说明限制600字以内!", 'data'=>new Object()]);
        }
        if(!(isset($postdata['content']) && strlen($postdata['content'])>0)){
            FResponse::output(['code' => 20001, 'msg' => "调研内容不能为空!", 'data'=>new Object()]);
        }

        //将图片添加到内容后面
        if(isset($postdata['att']) && count($postdata['att'])>0 && is_array($postdata['att'])){
            foreach($postdata['att'] as $key=>$val){
                $postdata['content'] .= "<img src=\"".$val['url']."\" />";
            }
        }

        $sModel = new SurveyModel();
        $sModel->title = $postdata['title'];
        $sModel->explain = $postdata['explain'];
        $sModel->content = $postdata['content'];
        $sModel->u_id = $this->userInfo['u_id'];
        $sModel->create_time = time();
        if($sModel->save(false)){
            FResponse::output(['code' => 20000, 'msg' => "发布成功!", 'data'=>new Object()]);
        }else{
            FResponse::output(['code' => 20003, 'msg' => "发布失败，请重试!", 'data'=>new Object()]);
        }
    }

    /**
     * 关闭调研
     * $survey_id
     */
    public function actionCloseSurvey()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if(!(isset($postdata['survey_id']) && $postdata['survey_id']>0)){
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $surveyInfo = \app\modules\survey\delegate\SurveyDelegate::getMySurveyDetail($postdata['survey_id'],$this->userInfo['u_id']);
        if(!isset($surveyInfo['status']) || $surveyInfo['status']==2){
            FResponse::output(['code' => 20007, 'msg' => "该调研不存在或已结束！", 'data'=>new Object()]);
        }
        $sModel = SurveyModel::findOne($postdata['survey_id']);
        $sModel->status = 2;
        if($sModel->save(false)){
            FResponse::output(['code' => 20000, 'msg' => "调研结束", 'data'=>new Object()]);
        }else{
            FResponse::output(['code' => 20003, 'msg' => "结束失败，请重试!", 'data'=>new Object()]);
        }
    }

}