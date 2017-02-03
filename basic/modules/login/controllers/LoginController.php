<?php

namespace app\modules\login\controllers;

use app\controllers\BaseController;
use app\lib\Tools;
use app\models\MembersModel;
use app\modules\login\delegate\LoginDelegate;
use app\modules\login\helper\LoginHelper;
use app\modules\permission\delegate\PermissionDelegate;
use Yii;

/**
 * Default controller for the `login` module
 */
class LoginController extends BaseController
{
    public $modelClass = 'app\models\MembersModel';
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 登录
    */
    public function actionLogin()
    {
        $data = json_decode(file_get_contents("php://input"));
        $memberInfo= LoginDelegate::validateUserInfo($data->username);
        if($memberInfo)
        {
            if(md5($data->pwd) != $memberInfo['pwd']){
                return ['code'=>-1,'msg'=>'密码错误'];
            }
            //用户组织
            $memberInfo['org']=LoginDelegate::getMemberOrgInfo($memberInfo['u_id']);
            if(empty($memberInfo['org'])){
                return ['code'=>-1,'msg'=>'该成员还未分配组织，请联系管理员'];
                die;
            }
//            ini_set('session.gc_maxlifetime', "10"); // 秒
//            ini_set("session.cookie_lifetime","10"); // 秒
            $session = Yii::$app->session;
            //第一次登陆修改密码
            if($memberInfo['is_first_login']==1){
                $session->set('u_id',$memberInfo['u_id']);
                return ['code'=>2,'msg'=>'登录正确,第一次登录需要修改密码'];
                die;
            }else{
                //生成登陆session
                $token=md5($data->username.$data->pwd.time());
                $token = $token."|".$memberInfo['u_id'];
                $session->set('token',$token);
                $session->set('expri_time',time()+3600);
                //设置缓存
                $memberInfo['access_token']=$token;
                LoginDelegate::setUserCache($memberInfo['u_id'],$memberInfo);
                //获取所有权限信息
                $permission = LoginDelegate::getAllPer();
                //权限信息键值对设置
                //$memberInfo['allper']=LoginHelper::setPerKV($permission);
                $allper=LoginHelper::setPerKV($permission);
                $permissionMember = PermissionDelegate::getUserPcode($memberInfo['u_id'], $memberInfo['perm_groupid']);
                unset($memberInfo['permission']);
                unset($memberInfo['pwd']);
                //处理头像
                $memberInfo['head_img'] = Tools::getHeadImg($memberInfo['head_img']);
                return ['code'=>1,'msg'=>'登录正确','member'=>$memberInfo,'allper'=>$allper,'userper'=>$permissionMember];

            }
        }else{
            return ['code'=>-1,'msg'=>'用户名错误'];
        }
    }

    /**
     * 修改密码
    */
    public function actionModifyPwd()
    {
        $session = Yii::$app->session;
        if(!isset($session['u_id'])){
            return ['code'=>-2,'msg'=>'链接已超时，请重新发起修改密码!'];
        }
        $data = json_decode(file_get_contents("php://input"));
        if($data->pwd1 != $data->pwd2){
            return ['code'=>-1,'msg'=>'两次密码不一致!'];
        }

        if(strlen($data->pwd1)<6 || strlen($data->pwd1)>16){
            return ['code'=>-1,'msg'=>'密码长度为6-16位!'];
        }

        $memModel = LoginDelegate::getUserInfo($session['u_id']);
        if(!$memModel->u_id){
            return ['code'=>-2,'msg'=>'非法请求!'];
        }
        $memModel->is_first_login=2;
        $memModel->pwd=md5($data->pwd1);
        if($memModel->save(false)){
            unset($session['u_id']);
            return ['code'=>1,'msg'=>'修改成功!'];
        }else{
            return ['code'=>-1,'msg'=>'修改失败，请重试!'];
        }

    }

    /**
     * 忘记密码
    */
    public function actionForgetpassword(){
        $data = json_decode(file_get_contents("php://input"));
        if(!isset($data->email)){
            return ['code'=>0,'msg'=>'请输入邮箱地址'];
        }
        //根据邮箱获取会员
        $memberInfo=LoginDelegate::getUsernmeInfo($data->email);
        if($memberInfo){
            if(LoginDelegate::getCachePwdToken('password_token'.$memberInfo['u_id'])){
                return ['code'=>0,'msg'=>'30分钟内只能发送一次邮件，请勿重复提交'];
            }

            $password_token = md5(time().'oasys4');
            //发送邮件
            $res = LoginHelper::sendMail($data->email,$password_token,$memberInfo['u_id']);
            if($res) {
                //设置忘记密码token
                LoginDelegate::setCachePwdToken($memberInfo['u_id'],$password_token);
                return ['code'=>1,'msg'=>'邮件发送成功,请登录邮箱重置您的OA密码'];
            } else {
                return ['code'=>0,'msg'=>'邮件发送失败'];
            }
        }else{
            return ['code'=>0,'msg'=>'你还不是OA的会员请联系管理员添加'];
        }
    }

    /**
     * 密码重置
    */
    public function actionResetpassword(){
        $postData = json_decode(file_get_contents("php://input"));
        if(!isset($postData->password_token) || !isset($postData->uid) || !isset($postData->pwd1) || !isset($postData->pwd2)){
            return ['code'=>-1,'msg'=>'非法请求或链接已过期!'];
        }
        if($postData->pwd1 != $postData->pwd2){
            return ['code'=>-1,'msg'=>'两次密码不一致!'];
        }

        if(strlen($postData->pwd1)<6 || strlen($postData->pwd1)>16){
            return ['code'=>-1,'msg'=>'密码长度为6-16位!'];
        }

        $token = LoginDelegate::getCachePwdToken('password_token'.$postData->uid);

        if(empty($token)){
            return ['code'=>-1,'msg'=>'非法请求或链接已过期'];
        }
        //检测是否是修改密码页面进来
        if($postData->password_token!=$token){
            return ['code'=>-1,'msg'=>'请从邮箱点击修改密码链接'];
        }

        $member = MembersModel::findOne($postData->uid);
        $newpwd = $postData->pwd1;
        $member->pwd =md5($newpwd);
        if($member->save(false)){
            //清除保存
            LoginDelegate::clearCachePwdToken('password_token'.$postData->uid);
            return ['code'=>1,'msg'=>"您的密码已经重置成功！"];
        }else{
            return ['code'=>0,'msg'=>"初始失败"];
        }
    }

    /**
     * 退出登录
    */
    public function actionLogout(){
        $session = Yii::$app->session;
        $session->destroy();
    }


}
