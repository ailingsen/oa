<?php
/**
 * APP移动端申请功能跳转控制器
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2016/2/24
 * Time: 10:27
 */
namespace app\controllers;

use app\models\MembersModel;
use app\models\OrgMemberModel;
use yii\web\Controller;
use yii;

class AppController extends Controller
{

    public function actionTest() {
        $set_cookies = Yii::$app->response->cookies;

        $set_cookies->add(new \yii\web\Cookie([
            'name' => 'app',
            'value' => 1,
            'httpOnly'=>false
        ]));

        $set_cookies->add(new \yii\web\Cookie([
            'name' => 'app_token',
            'value' => '103f4ecd730bb3b1a56f71cc3b6cc5f3|229',
            'httpOnly'=>false
        ]));
    }

    public function actionJump()
    {
        $cookies = Yii::$app->request->cookies;
        if(!isset($cookies['app']) || empty($cookies['app']) || !isset($cookies['app_token']) || empty($cookies['app_token'])) {
//            throw new \yii\web\HttpException(400, '无效请求');
//            $this->redirect('/#/applymobile/jump');
            return $this->renderPartial('/error/apply_jump.html');
        }

        $path = Yii::$app->request->get('path');
        if(!isset($path) || empty($path)) {
            throw new \yii\web\HttpException(400, '无效参数');
        }

        list( $auth , $uid ) = explode("|", $cookies['app_token']);
        $member = new MembersModel();

        $userInfo = $member::getUserInfo($uid);
        $userInfo['org'] = OrgMemberModel::getMemberOrgInfo($uid);
        if(!$userInfo || $userInfo['app_access_token'] != $cookies['app_token']){
            return $this->renderPartial('/error/apply_jump.html');
//            throw new \yii\web\HttpException(401, '未找到用户信息');
        }

        $real_path = '';
        switch($path) {
            case 'create' :
                $model_id = Yii::$app->request->get('mid');
                $real_path = '/index_news.html#/applymobile/application/customcreate/'.$model_id;
                break;
            case 'edit' :
                $apply_id = Yii::$app->request->get('aid');
                $real_path = '/index_news.html#/applymobile/mine/'.$apply_id.'/0/1';
                break;
            case 'detail' :
                $apply_id = Yii::$app->request->get('aid');
                $real_path = '/index_news.html#/applymobile/mine/'.$apply_id.'/0/0';
                break;
            case 'verify' :
                $apply_id = Yii::$app->request->get('aid');
                $real_path = '/index_news.html#/applymobile/agent/'.$apply_id.'/0/0';
                break;
        }

        if(empty($real_path)) {
            throw new \yii\web\HttpException(400, '无效参数');
        }

        $session = Yii::$app->session;

        //生成登陆session
        $token=md5($userInfo['username'].$userInfo['pwd'].time());
        $token = $token."|".$userInfo['u_id'];
        $session->set('token',$token);
        $session->set('expri_time',time()+3600);

        /*$set_cookies = Yii::$app->response->cookies;

        $set_cookies->add(new \yii\web\Cookie([
            'name' => 'sessionoa',
            'value' => $userInfo['access_token'],
            'httpOnly'=>false
        ]));

        $set_cookies->add(new \yii\web\Cookie([
            'name' => 'userInfo',
            'value' => json_encode($userInfo),
            'httpOnly'=>false
        ]));

        $set_cookies->add(new \yii\web\Cookie([
            'name' => 'app',
            'value' => 1,
            'httpOnly'=>false
        ]));

        $set_cookies->add(new \yii\web\Cookie([
            'name' => 'app_token',
            'value' => $cookies['app_token'],
            'httpOnly'=>false
        ]));*/

        $this->redirect($real_path);
    }
}