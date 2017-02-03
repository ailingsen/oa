<?php
namespace app\controllers;

use app\lib\errors\ErrorCode;
use app\lib\FResponse;
use app\models\MembersModel;
use app\models\OrgMemberModel;
use app\modules\permission\delegate\PermissionDelegate;
use Yii;
use yii\rest\ActiveController;
use yii\web\Controller;

class BaseController extends ActiveController
{
	public $enableCsrfValidation = false;
	protected $userInfo = array();
	public function actions()
	{
		 $actions = parent::actions();
		 return $actions;
	}

	public function fields()
	{
	    $fields = parent::fields();

	    // 删除一些包含敏感信息的字段，比如密码
	    unset($fields['pwd']);

	    return $fields;
	}

	public function beforeAction($action)
	{
		parent::beforeAction($action);
		/*$uid = 264;
		$this->userInfo = MembersModel::getUserInfo($uid);
		$this->userInfo['org'] = OrgMemberModel::getMemberOrgInfo($uid);
		return true;*/
		if ($action->controller->id == 'login' || $action->controller->id == 'sendmail') {
			return true;  //login控制排除
		}

        $session = Yii::$app->session;
        if(!(isset($session['expri_time']) && time()<$session['expri_time'])){
            $session->destroy();
            if($action->controller->action->id == 'exportexcel' || $action->controller->action->id == 'attend-count-exp' || $action->controller->action->id=='vacation-excel'){//导出excel登陆超时处理
                $url = '/index_news.html#/';
                echo "<script language='javascript'type='text/javascript'>window.location.href='".$url."';</script>";
               // header("Location: ".$url);
                die;
            }
            throw new \yii\web\HttpException(401, '未找到用户信息');
        }else{
            $session->set('expri_time',time()+3600);
        }
        if(!isset($session['token'])){
            $session->destroy();
            throw new \yii\web\HttpException(401, '未找到用户信息');
        }
        list( $auth , $uid ) = explode("|", $session['token']);
        $member = new MembersModel();
        $userInfo = $member::getUserInfo($uid);
        if(!isset($userInfo['u_id'])){
            throw new \yii\web\HttpException(401, '未找到用户信息');
        }
        $userInfo['org'] = OrgMemberModel::getMemberOrgInfo($uid);
        $this->userInfo = $userInfo;
		//检查权限
		if($this->permit()) {
			return true;
		}else {
			echo json_encode(['code' => 0, 'msg' => '没有权限']);
			return false;
		}
//        return true;
       /* $cookieUserInfoData = new \stdClass();
        $cookieUserInfoData->access_token = 'sdfsdfsdfsdfsdf|231';
        list( $auth , $uid ) = explode("|", $cookieUserInfoData->access_token);
        $member = new MembersModel();
        $userInfo = $member::getUserInfo($uid);
        //$userInfo['org'] = OrgMemberModel::getMemberOrgInfo($uid);
        if(!isset($userInfo)){
            throw new \yii\web\HttpException(401, '未找到用户信息');
        }
        $this->userInfo = $userInfo;*/
	}

	/**
	 * 检查权限
	 */
	public function permit()
	{
		$pCodeList = PermissionDelegate::getUserPcode($this->userInfo['u_id'], $this->userInfo['perm_groupid']);
//		$moduleName = $this->action->controller->modules;
//		$className = $this->action->controller->className();
//		$pathArr = explode('\\', $className);
//		$className = str_replace('Controller', '', end($pathArr));
//        $actionMethod = str_replace('action', '', $this->action->actionMethod);
		$pCode = strtolower(Yii::$app->request->post('p_code'));
		if($pCode){
			if(!$pCodeList || !in_array($pCode, $pCodeList)) {
				return false;
			}
		}
		return true;
	}
}