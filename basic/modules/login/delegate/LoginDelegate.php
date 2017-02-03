<?php

namespace app\modules\login\delegate;


//模型委托类。 处理控制器和动作列表

use app\models\Mcache;
use app\models\MembersModel;
use app\models\OrgMemberModel;
use app\models\PermissionModel;
use Yii;

class LoginDelegate {

    /**
     * 根据用户输入的用户名和密码获取用户的信息
    */
    public static function validateUserInfo($username)
    {
        $member = new MembersModel();
        $username = (string)$username;
        $memberInfo=$member->find()->where('username=:username and is_del=0',[':username'=>$username])->asArray()->one();
        return $memberInfo;

    }

    /**
     * 获取用户所在组信息
    */
    public static function getMemberOrgInfo($u_id)
    {
        $res = OrgMemberModel::getMemberOrgInfo($u_id);
        return $res;
    }

    /**
     *设置用户信息缓存
    */
    public static function setUserCache($u_id,$memberInfo)
    {
        MembersModel::setUserCache($u_id,$memberInfo);
    }

    /**
     * 获取所有权限信息
    */
    public static function getAllPer()
    {
        $res = PermissionModel::find()->where('parent_id!=0')->all();
        return $res;
    }

    /**
     * 根据u_id获取用户信息
    */
    public static function getUserInfo($u_id)
    {
        $res = MembersModel::find()->where('u_id=:u_id and is_del=0',[':u_id'=>$u_id])->one();
        return $res;
    }

    /**
     * 根据username获取用户信息
    */
    public static function getUsernmeInfo($email)
    {
        $res = MembersModel::find()->where('username=:username',[':username'=>$email])->asArray()->one();
        return $res;
    }

    /**
     * 获取忘记密码验证码
    */
    public static function getCachePwdToken($id)
    {
        $res = Mcache::getCache($id);
        return $res;
    }

    /**
     * 设置忘记密码token
    */
    public static function setCachePwdToken($u_id,$password_token)
    {
        Mcache::setCache('password_token'.$u_id,$password_token,1800);
    }

    /**
     * 清除缓存值
    */
    public static function clearCachePwdToken($id)
    {
        Mcache::deleteCache($id);
    }

}