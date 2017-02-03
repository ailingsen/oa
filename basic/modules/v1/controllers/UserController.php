<?php
namespace app\modules\v1\controllers;

use app\lib\FileUploadHelper;
use app\lib\Tools;
use app\models\ApptabSetModel;
use app\models\FResponse;
use app\models\MembersModel;
use app\models\OrgMemberModel;
use app\modules\v1\delegate\UserDelegate;
use app\modules\v1\helper\UserHelper;
use Yii;
use Yii\base\Object;
use app\models\Mcache;
use yii\imagine\Image;
use yii\web\UploadedFile;

class UserController extends BaseController
{
    public $modelClass = 'app\models\MembersModel';

    /**
     * 获取当前用户信息
    */
    public function actionGetUserInfo()
    {
        $userInfo = UserDelegate::getUserInfo($this->userInfo['u_id']);
        if(isset($userInfo['u_id'])){
            //处理头像
            if(isset($userInfo['u_id'])){//处理头像
                $userInfo['head_img'] =  substr($this->apiDomain, 0, -1).Tools::getHeadImg($userInfo['head_img']);
            }
            $userInfo['highSkill'] = UserDelegate::getHighSkillInfo($this->userInfo['u_id']);
            FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>$userInfo]);
        }else{
            FResponse::output(['code' => 20003, 'msg' => "该用户不存在", 'data'=>new Object()]);
        }
    }

    /**
     * 获取其他人信息
     */
    public function actionGetOtherInfo()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        $uid = $postdata['otherUid'];
        if(!isset($uid)){
            FResponse::output(['code' => 20001, 'msg' => "参数错误！"]);
        }
        $userInfo = UserDelegate::getUserInfo($uid);
        if(isset($userInfo['u_id'])){
            //处理头像
            if(isset($userInfo['u_id'])){//处理头像
                $userInfo['head_img'] =  substr($this->apiDomain, 0, -1).Tools::getHeadImg($userInfo['head_img']);
            }
            $userInfo['highSkill'] = UserDelegate::getHighSkillInfo($uid);
            FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>$userInfo]);
        }else{
            FResponse::output(['code' => 20003, 'msg' => "该用户不存在", 'data'=>new Object()]);
        }
    }
    /**
     * 上传头像
     */
    public function actionSetheadimg()
    {
        $postData = Yii::$app->request;
        $uid = $postData->post('u_id');
        $accessToken = $postData->post('accessToken');

        //文件获取和验证
        $f = UploadedFile::getInstanceByName('file');
        if ($f == null) {
            FResponse::output(['code' => 20018, 'msg' => '没有文件被上传', 'data'=>new Object()]);
        }
        if ($f->size > 5 * 1024 * 1024) {
            FResponse::output(['code' => 20019, 'msg' => '图片大小超过限制','data'=>new Object()]);
        }
        $type = pathinfo($f->name, PATHINFO_EXTENSION);
        if (!in_array($type, array('gif', 'jpeg', 'jpg', 'png'))) {
            FResponse::output(['code' => 20020, 'msg' => '图片类型错误','data'=>new Object()]);
        }

        $member = MembersModel::find()->where(['u_id'=>$uid])->andWhere(['app_access_token'=>$accessToken])->one();
        if( !$member ) FResponse::output(['code' => 20007, 'msg' => 'Auth Check Failed','data'=>new Object()]);

        $time=time();
        $file_name = $member->u_id.'_'.$time.'.jpg';

        if (is_object($f) && get_class($f) == 'yii\web\UploadedFile') {
            if($f->saveAs('static/head-img/uploads/' . $file_name)){
                $old_file_name = $member->head_img;
                $member->head_img = $member->u_id.'_'.$time;
                if($member->save(false)){
                    //删除旧头像
                    if(strlen($old_file_name)>0){
                        if(file_exists(WEB_ROOT.'/static/head-img/uploads/' . $old_file_name.'.jpg')){
                            unlink(WEB_ROOT.'/static/head-img/uploads/' . $old_file_name.'.jpg');
                        }
                    }
                    //缩放图片
                    //Image::thumbnail('static/head-img/uploads/' . $file_name, 87, 87)->save(Yii::getAlias('static/head-img/uploads/' . $file_name), ['quality' => 100]);
                    FResponse::output(['code' => 20000, 'msg' => 'ok','data'=>['url'=>$this->apiDomain.'static/head-img/uploads/'.$file_name]]);
                }else{
                    FResponse::output(['code' => 20021, 'msg' => '上传失败','data'=>new Object()]);
                }
            }else{
                FResponse::output(['code' => 20021, 'msg' => '上传失败','data'=>new Object()]);
            }
        } else {
            FResponse::output(['code' => 20021, 'msg' => '上传失败','data'=>new Object()]);
        }
    }

    /**
     * 获取消息设置
     */
    public function actionGetMessageSet()
    {
        $data = UserDelegate::getMemssageSet($this->userInfo['u_id']);
        FResponse::output(['code' => 20000, 'msg' => 'success','data'=>$data]);
    }

    /**
     * 消息设置
    */
    public function actionMessageSet()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        $M = MembersModel::findOne($this->userInfo['u_id']);
        if(isset($postdata['allow_task_app'])){
            $M->allow_task_app = $postdata['allow_task_app'];
        }
        if(isset($postdata['allow_apply_app'])){
            $M->allow_apply_app = $postdata['allow_apply_app'];
        }
        if(isset($postdata['allow_notice_app'])){
            $M->allow_notice_app = $postdata['allow_notice_app'];
        }
        if(isset($postdata['allow_project_app'])){
            $M->allow_project_app = $postdata['allow_project_app'];
        }
        if(isset($postdata['allow_approval_app'])){
            $M->allow_approval_app = $postdata['allow_approval_app'];
        }
        if(isset($postdata['allow_meeting_app'])){
            $M->allow_meeting_app = $postdata['allow_meeting_app'];
        }
        if($M->save(false)){
            FResponse::output(['code' => 20000, 'msg' => '设置成功','data'=>new Object()]);
        }else{
            FResponse::output(['code' => 20003, 'msg' => '设置失败','data'=>new Object()]);
        }
    }

    /**
     * 修改密码验证原密码
     */
    public function actionCheckPass()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if( !isset($postdata['pwd'])) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $M = MembersModel::findOne($this->userInfo['u_id']);
        if($M->pwd == $postdata['pwd']){
            FResponse::output(['code' => 20000, 'msg' => '密码正确','data'=>new Object()]);
        }else{
            FResponse::output(['code' => 20003, 'msg' => '密码错误','data'=>new Object()]);
        }
    }

    /**
     * 修改密码
     */
    public function actionModifyPass()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if( !isset($postdata['pwd1']) || !isset($postdata['pwd2'])) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        if( $postdata['pwd1'] != $postdata['pwd2']) {
            FResponse::output(['code' => 20007, 'msg' => "两次密码不一致", 'data'=>new Object()]);
        }
        $M = MembersModel::findOne($this->userInfo['u_id']);
        $M->pwd = $postdata['pwd1'];
        if($M->save(false)){
            FResponse::output(['code' => 20000, 'msg' => '修改成功','data'=>new Object()]);
        }else{
            FResponse::output(['code' => 20003, 'msg' => '修改失败','data'=>new Object()]);
        }
    }


    public function actionGetContacts()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        $searchName = $postdata['searchName'];
        $pageSize = !empty($postdata['pageSize']) ? $postdata['pageSize'] : '20';
        $curPage = !empty($postdata['curPage']) ? $postdata['curPage'] : '1';
        $type = !empty($postdata['type']) ? $postdata['type'] : '1';
        $contactsData = MembersModel::getGetContacts($searchName,$pageSize,$curPage,$type);
        foreach ($contactsData['contactsData'] as $key => $val){
            $contactsData['contactsData'][$key]['head_img'] = substr($this->apiDomain, 0, -1).Tools::getHeadImg($val['head_img']);
        }
        FResponse::output(['code' => 20000, 'msg' => 'ok','data' => $contactsData]);
    }

    /**
     * 根据环信ID获取用户信息
    */
    public function actionHxUserInfo()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if(!(isset($postdata['h_ids']) && count($postdata['h_ids'])>0)){
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $arrHid = [];
        $arrHid = array_column($postdata['h_ids'], 'h_id');
        if(count($arrHid)<=0){
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $memberInfo = MembersModel::find()->select('u_id,username,real_name,email,phone,position,entry_time,head_img,h_id,allow_task_email,allow_apply_email,allow_notice_email,allow_project_email,allow_approval_email,allow_meeting_email,allow_task_app,allow_apply_app,allow_notice_app,allow_project_app,allow_approval_app,allow_meeting_app')
            ->where(['h_id' =>$arrHid])->asArray()->all();
        foreach($memberInfo as $key=>$val){
            $memberInfo[$key]['head_img'] = substr($this->apiDomain, 0, -1).Tools::getHeadImg($val['head_img']);
        }
        FResponse::output(['code' => 20000, 'msg' => 'ok','data' => $memberInfo]);
    }

    /**
     * 获取tab设置
    */
    public function actionGetTabset()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        $res = ApptabSetModel::find()->where('u_id=:u_id',[':u_id'=>$postdata['u_id']])->asArray()->one();
        FResponse::output(['code' => 20000, 'msg' => 'ok','data' => $res]);
    }

    /**
     * 修改tab设置
     */
    public function actionSetTab()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        $M = ApptabSetModel::find()->where('u_id=:u_id',[':u_id'=>$postdata['u_id']])->one();
        if(isset($postdata['project'])){
            $M->project = $postdata['project'];
        }
        if(isset($postdata['task'])){
            $M->task = $postdata['task'];
        }
        if(isset($postdata['attend'])){
            $M->attend = $postdata['attend'];
        }
        if(isset($postdata['apply'])){
            $M->apply = $postdata['apply'];
        }
        if(isset($postdata['meeting'])){
            $M->meeting = $postdata['meeting'];
        }
        if(isset($postdata['approval'])){
            $M->approval = $postdata['approval'];
        }
        if(isset($postdata['work'])){
            $M->work = $postdata['work'];
        }
        if(isset($postdata['survey'])){
            $M->survey = $postdata['survey'];
        }
        if($M->save(false)){
            FResponse::output(['code' => 20000, 'msg' => '设置成功','data'=>new Object()]);
        }else{
            FResponse::output(['code' => 20003, 'msg' => '设置失败','data'=>new Object()]);
        }
    }

}