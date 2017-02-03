<?php
namespace app\modules\v1\controllers;

use app\lib\Tools;
use app\models\FResponse;
use app\models\MembersModel;
use app\models\OrgMemberModel;
use app\models\PublicModel;
use app\modules\login\delegate\LoginDelegate;
use Yii;
use Yii\base\Object;
use app\models\Mcache;

class LoginController extends BaseController
{
    public $modelClass = 'app\models\MembersModel';

    //登录
    public function actionLogin()
    {
        $postData = json_decode(file_get_contents("php://input"));
        if( !isset($postData->username) || !isset($postData->pwd) ){
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $username = (string)$postData->username;
        $pwd = $postData->pwd;

        try{
            $memberInfo = MembersModel::find()->select('u_id,username,pwd,is_first_login')->where(['username' => $username,'is_del'=>0])->asArray()->one();
            if(isset($memberInfo['u_id'])){
                if($memberInfo['pwd']==$pwd){
                    //判断是否为第一次登陆
                    if($memberInfo['is_first_login']==1){
                        $init_password_token = md5(time().'oasys4');
                        Mcache::setCache('init_password_token'.$memberInfo['u_id'],$init_password_token,1800);
                        FResponse::output(['code' => 20002, 'msg' => "第一次登陆需修改密码", 'data'=>['token'=>$init_password_token,'u_id'=>$memberInfo['u_id']]]);
                    }
                    //生成登录标示
                    $token = md5($postData->username . $postData->pwd . time());
                    $token = $token . "|" . $memberInfo['u_id'];
                    $member = MembersModel::findOne($memberInfo['u_id']);
                    $member->app_access_token = $token;
                    //保存当前token
                    if ($member->save(false)) {
                        $memberInfo = MembersModel::find()->select('u_id,username,real_name,email,phone,app_access_token,position,entry_time,head_img,h_id,h_pwd,allow_task_email,allow_apply_email,allow_notice_email,allow_project_email,allow_approval_email,allow_meeting_email,allow_task_app,allow_apply_app,allow_notice_app,allow_project_app,allow_approval_app,allow_meeting_app')
                            ->where(['u_id' =>$memberInfo['u_id']])->asArray()->one();
                        //用户组织
                        $memberInfo['org']=OrgMemberModel::getMemberOrgInfo($memberInfo['u_id'],'oa_org.org_id,oa_org.org_name');
                        $memberInfo['org']['company_name'] = "湖南纳米娱乐";
                        $memberInfo['head_img'] = substr($this->apiDomain, 0, -1).Tools::getHeadImg($memberInfo['head_img']);
                        MembersModel::setUserCache($memberInfo['u_id'], $memberInfo);
                        FResponse::output(['code' => 20000, 'msg' => "登录成功", 'data'=>$memberInfo]);
                    }else{
                        FResponse::output(['code' => 20003, 'msg' => "登录失败，请重试", 'data'=>new Object()]);
                    }
                }else{
                    FResponse::output(['code' => 20001, 'msg' => "密码输入错误", 'data'=>new Object()]);
                }
            }else{
                FResponse::output(['code' => 20001, 'msg' => "账户不存在", 'data'=>new Object()]);
            }
        }catch (Exception $e) {
            FResponse::output(['code' => 20011, 'msg' => "登录失败", 'data'=>new Object()]);
        }
    }

    //第一次登陆修改密码
    public function actionModifyPwd()
    {
        $postData = json_decode(file_get_contents("php://input"));
        if(!(isset($postData->token) && isset($postData->u_id) && isset($postData->pwd1) && isset($postData->pwd2))){
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $init_password_token = Mcache::getCache('init_password_token'.$postData->u_id);
        if($init_password_token != $postData->token){
            FResponse::output(['code' => 20123, 'msg' => "token验证失败", 'data'=>new Object()]);
        }
        if(strlen($postData->pwd1)<=0 || strlen($postData->pwd2)<=0){
            FResponse::output(['code' => 20004, 'msg' => "密码不能为空", 'data'=>new Object()]);
        }
        if($postData->pwd1 != $postData->pwd2){
            FResponse::output(['code' => 20004, 'msg' => "两次密码不一致", 'data'=>new Object()]);
        }
        $memModel = MembersModel::find()->where(['u_id'=>$postData->u_id,'is_del'=>0])->one();
        if(isset($memModel->u_id)){
            $memModel->pwd = $postData->pwd1;
            $memModel->is_first_login=2;
            if($memModel->save(false)){
                LoginDelegate::clearCachePwdToken('init_password_token'.$postData->u_id);
                FResponse::output(['code' => 20000, 'msg' => "密码修改成功", 'data'=>['username'=>$memModel->username,'pwd'=>$postData->pwd1]]);
            }else{
                FResponse::output(['code' => 20004, 'msg' => "失败，请重试", 'data'=>new Object()]);
            }
        }else{
            //清除保存
            LoginDelegate::clearCachePwdToken('init_password_token'.$postData->uid);
            FResponse::output(['code' => 20003, 'msg' => "失败，该用户不存在或已删除", 'data'=>new Object()]);
        }
    }

    //忘记密码
    public function actionForgetpwd(){
        $postData = json_decode(file_get_contents("php://input"));
        if( empty($postData->username) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error"]);
        }
        $username = $postData->username;

        $memberInfo = MembersModel::find()->where(['username'=>$username,'is_del'=>0])->asArray()->one();
        if( isset($memberInfo['u_id']) ){
            if(LoginDelegate::getCachePwdToken('password_token'.$memberInfo['u_id'])){
                FResponse::output(['code' => 20015, 'msg' => "30分钟内只能发送一次邮件，请勿重复提交"]);
            }
            $password_token = md5(time().'oasys4');
            if(Mcache::getCache('password_token'.$memberInfo['u_id'])){
                Mcache::deleteCache('password_token'.$memberInfo['u_id']);
            }
            Mcache::setCache('password_token'.$memberInfo['u_id'],$password_token,1800);

            $contacts = '请点击链接修改你在纳米OA系统中的账户密码,为了您的账户安全请保密！该验证链接有效时间为30分钟！<a href="'.$this->apiDomain.'index_news.html#/forgetPassword/'.$password_token.'/'.$memberInfo['u_id'].'">'.$this->apiDomain.'index_news.html#/forgetPassword/'.$password_token.'/'.$memberInfo['u_id'].'</a>';
            $mail= Yii::$app->mailer->compose();
            $mail->setFrom(['hnoa@supernano.com'=>'admin'])->setTo($postData->username);
            $mail->setSubject("纳米OA密码重置");
            //$mail->setTextBody('zheshisha');   //发布纯文字文本
            $mail->setHtmlBody($contacts);    //发布可以带html标签的文本
            $res = $mail->send();
            if($res) {
                FResponse::output(['code' => 20000, 'msg' => "重置密码邮件已发送到用户邮箱"]);
            } else {
                FResponse::output(['code' => 20013, 'msg' => "重置密码邮件发送失败"]);
            }
        } else {
            FResponse::output(['code' => 20014, 'msg' => "该用户不存在或已删除"]);
        }
    }

    //退出登录
    public function actionLogout()
    {
        $postData = json_decode(file_get_contents("php://input"));
        $uid = $postData->u_id;
        $member = MembersModel::findOne($uid);

        $member->app_access_token = '';
        if ($member->save(false)) {  //保存当前token
            $data = [
                'code' => 20000,
                'msg' => '退出成功',
                'data'=>new Object()
            ];

            MembersModel::deleteUserCache($uid);
            FResponse::output($data);
        } else {
            $data = ['code' => 20003,  'msg' => '退出失败','data'=>new Object()];
            FResponse::output($data);
        }
    }

}