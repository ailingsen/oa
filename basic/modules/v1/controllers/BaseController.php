<?php
namespace app\modules\v1\controllers;

use app\lib\Tools;
use app\models\MembersModel;
use app\models\OrgMemberModel;
use app\models\PermissionMemberModel;
use Yii;
use yii\base\Object;
use yii\web\Controller;
use app\lib\FResponse;

class BaseController extends Controller
{
    public $enableCsrfValidation = false;
    protected $userInfo = array();
    protected $uid;
    protected $accessToken;
    protected $apiDomain = 'http://oa.supernano.com/';

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
        ini_set('date.timezone','Asia/Shanghai');
        parent::beforeAction($action);
        if( isset(Yii::$app->params['apiDomain']) ) $this->apiDomain = Yii::$app->params['apiDomain'];
        if( !Yii::$app->getRequest()->get("f") ){
            if($action->controller->id=='login' || $action->controller->id=='public' || $action->controller->id=='modify-pwd')
            {
                return true;  //login控制排除
            }
            $postData = json_decode(file_get_contents("php://input"));
            if( !isset($postData->accessToken) || !isset($postData->u_id) ) {
                FResponse::output(['code' => 20123, 'msg' => "AccessToken Error", 'data'=>new Object()]);
            }else{
                $accessToken = $postData->accessToken;
                $postUid = $postData->u_id;
                $this->checkToken($accessToken, $postUid);
            }
            return true;
        } else {
            $uid = Yii::$app->request->post('u_id');
            $accessToken = Yii::$app->request->post('accessToken');
            if( empty($accessToken) || empty($uid) ) {
                FResponse::output(['code' => 20123, 'msg' => "AccessToken Error", 'data'=>new Object()]);
            }
            $this->checkToken($accessToken, $uid);
        }
        return true;
    }

    public function checkToken($accessToken, $postUid){
        if( count(explode("|", $accessToken)) != 2 ){
            FResponse::output(['code' => 20123, 'msg' => "Invalid AccessToken", 'data'=>new Object()]);
        }
        list( $auth , $uid ) = explode("|", $accessToken);

        if( $uid != $postUid ){
            FResponse::output(['code' => 20123, 'msg' => "Invalid AccessToken", 'data'=>new Object()]);
        }
        $member = new MembersModel();
        $userInfo = $member::getUserInfo($uid);
        if(!isset($userInfo['u_id'])){
            FResponse::output(['code' => 20404, 'msg' => "User not found", 'data'=>new Object()]);
        }
        $userInfo['org'] = OrgMemberModel::getMemberOrgInfo($uid);
        $this->userInfo = $userInfo;


        if( !$userInfo || $userInfo['app_access_token'] != $accessToken ){
            FResponse::output(['code'=>20123,'msg'=>'Auth Check Failed', 'data'=>new Object()]);
            return false;
        }
        $this->uid = $uid;
        $this->accessToken = $accessToken;
    }

    public function getUserHeadimg($headImg){
        if (!$headImg) {
            return $this->apiDomain.'static/head-img/defaultHead.png';
        }
        return $this->apiDomain.'static/head-img/uploads/' . $headImg . '.jpg';
    }

    //权限判断
    public function isPerm($code)
    {
        $arrPermCode = PermissionMemberModel::find()->select('oa_permission.code')->leftJoin('oa_permission','oa_permission.pid=oa_permission_member.pid')->where(['oa_permission_member.u_id'=>$this->userInfo['u_id']])->asArray()->all();
        $arrPermCode = array_column($arrPermCode, 'code');
        if(!in_array($code,$arrPermCode)){
            FResponse::output(['code'=>20505,'msg'=>'您无访问此功能权限,请找管理员开通~', 'data'=>new Object()]);
        }
    }

    //权限判断(返回状态)
    public function isPermStatus($code)
    {
        $arrPermCode = PermissionMemberModel::find()->select('oa_permission.code')->leftJoin('oa_permission','oa_permission.pid=oa_permission_member.pid')->where(['oa_permission_member.u_id'=>$this->userInfo['u_id']])->asArray()->all();
        $arrPermCode = array_column($arrPermCode, 'code');
        if(!in_array($code,$arrPermCode)){
            return 0;
        }
        return 1;
    }

}