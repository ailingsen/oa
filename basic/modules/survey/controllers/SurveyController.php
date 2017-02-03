<?php

namespace app\modules\survey\controllers;

use app\controllers\BaseController;
use app\models\SurveyModel;
use app\models\SurveyReplyModel;
use app\modules\project\delegate\ProjectDelegate;
use app\modules\survey\delegate\SurveyDelegate;
use app\modules\survey\helper\SurveyHelper;
use Yii;

/**
 * Default controller for the `survey` module
 */
class SurveyController extends BaseController
{
    public $modelClass = 'app\models\SurveyModel';

    /**
     * 调研列表
     * $page
    */
    public function actionSurveyList()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        $page = isset($postdata['page']) ? $postdata['page'] : 1;
        //获取翻页参数
        $pageParam = SurveyHelper::setPage(1,$page);
        $surveyList = SurveyDelegate::getSurveyList(1,$this->userInfo['u_id'],$pageParam['limit'],$pageParam['offset']);
        //设置数据格式
        $surveyList['surList'] = SurveyHelper::setSurveyData($surveyList['surList']);
        $surveyList['page']['curPage'] = $page;
        return ['code'=>1,'data'=>$surveyList];
    }

    /**
     * 我发布的调研
     * $page
    */
    public function actionMySurveyList()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        $page = isset($postdata['page']) ? $postdata['page'] : 1;
        //获取翻页参数
        $pageParam = SurveyHelper::setPage(1,$page);
        $surveyList = SurveyDelegate::getSurveyList(2,$this->userInfo['u_id'],$pageParam['limit'],$pageParam['offset']);
        //设置数据格式
        $surveyList['surList'] = SurveyHelper::setSurveyData($surveyList['surList']);
        $surveyList['page']['curPage'] = $page;
        return ['code'=>1,'data'=>$surveyList];
    }

    /**
     *查看调研详情
     * $survey_id
    */
    public function actionSurveyDetail()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if(!(isset($postdata['survey_id']) && $postdata['survey_id']>0)){
            return ['code'=>-1,'msg'=>'参数错误!'];
        }
        $surveyInfo = SurveyDelegate::getSurveyDetail($postdata['survey_id']);
        if($surveyInfo)
        {
            //格式化时间
            $surveyInfo['create_time_f'] = SurveyHelper::setFormatDate($surveyInfo['create_time']);
            return ['code'=>1,'data'=>$surveyInfo];
        }else{
            return ['code'=>-1,'msg'=>'参数错误!'];
        }
    }

    /**
     *查看我发布的调研详情
     * $survey_id
     */
    public function actionMySurveyDetail()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if(!(isset($postdata['survey_id']) && $postdata['survey_id']>0)){
            return ['code'=>-1,'msg'=>'参数错误!'];
        }
        $surveyInfo = SurveyDelegate::getMySurveyDetail($postdata['survey_id'],$this->userInfo['u_id']);
        if($surveyInfo)
        {
            $page = isset($postdata['page']) ? $postdata['page'] : 1;
            //获取翻页参数
            $pageParam = SurveyHelper::setPage(1,$page);
            $replyInfo = SurveyDelegate::getMySurveyReply($postdata['survey_id'],$pageParam['limit'],$pageParam['offset']);
            $replyInfo['page']['curPage'] = $page;
            //格式化时间
            $surveyInfo['create_time_f'] = SurveyHelper::setFormatDate($surveyInfo['create_time']);
            //设置数据格式
            $replyInfo['repList'] = SurveyHelper::setSurveyReplyData($replyInfo['repList']);
            return ['code'=>1,'data'=>['surveyInfo'=>$surveyInfo,'replyInfo'=>$replyInfo]];
        }else{
            return ['code'=>-1,'msg'=>'参数错误或该调研已关闭!'];
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
        if(!(isset($postdata['reply_content']) && strlen($postdata['reply_content'])>0)){
            return ['code'=>-1,'msg'=>'回复内容不能为空!'];
        }
        if(!ProjectDelegate::isStrlen($postdata['reply_content'],600)){
            return ['code'=>-1,'msg'=>'回复内容限制600字以内!'];
        }
        $surveyInfo = SurveyDelegate::getSurveyDetail($postdata['survey_id']);
        if(!isset($surveyInfo['status']) || $surveyInfo['status']==2){
            return ['code'=>-1,'msg'=>'该调研已结束!'];
        }
        $srModel = new SurveyReplyModel();
        $srModel->reply_content = $postdata['reply_content'];
        $srModel->u_id = $this->userInfo['u_id'];
        $srModel->survey_id = $postdata['survey_id'];
        $srModel->create_time = time();
        if($srModel->save(false)){
            return ['code'=>1,'msg'=>'回复成功!'];
        }else{
            return ['code'=>-1,'msg'=>'回复失败，请重试!'];
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
        $postdata = json_decode(file_get_contents("php://input"), true);
        if(!(isset($postdata['title']) && strlen($postdata['title'])>0)){
            return ['code'=>-1,'msg'=>'调研标题不能为空!'];
        }
        if(!ProjectDelegate::isStrlen($postdata['title'],60)){
            return ['code'=>-1,'msg'=>'调研标题限制60字以内!'];
        }
        if(!(isset($postdata['explain']) && strlen($postdata['explain'])>0)){
            return ['code'=>-1,'msg'=>'调研说明不能为空!'];
        }
        if(!ProjectDelegate::isStrlen($postdata['explain'],600)){
            return ['code'=>-1,'msg'=>'调研说明限制600字以内!'];
        }
        if(!(isset($postdata['content']) && strlen($postdata['content'])>0)){
            return ['code'=>-1,'msg'=>'调研内容不能为空!'];
        }
        $isTitle = SurveyDelegate::searchTitleSurvey($postdata['title']);
        if($isTitle){
            return ['code'=>-1,'msg'=>'该调研标题已存在!'];
        }
        $sModel = new SurveyModel();
        $sModel->title = $postdata['title'];
        $sModel->explain = $postdata['explain'];
        $sModel->content = $postdata['content'];
        $sModel->u_id = $this->userInfo['u_id'];
        $sModel->create_time = time();
        if($sModel->save(false)){
            return ['code'=>1,'msg'=>'创建成功!'];
        }else{
            return ['code'=>-1,'msg'=>'创建失败，请重试!'];
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
            return ['code'=>-1,'msg'=>'参数错误!'];
        }
        $surveyInfo = SurveyDelegate::getMySurveyDetail($postdata['survey_id'],$this->userInfo['u_id']);
        if(!isset($surveyInfo['status']) || $surveyInfo['status']==2){
            return ['code'=>-1,'msg'=>'该调研已结束!'];
        }
        $sModel = SurveyModel::findOne($postdata['survey_id']);
        $sModel->status = 2;
        if($sModel->save(false)){
            return ['code'=>1,'msg'=>'结束成功!'];
        }else{
            return ['code'=>-1,'msg'=>'结束失败，请重试!'];
        }
    }

}
