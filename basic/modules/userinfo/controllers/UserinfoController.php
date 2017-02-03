<?php

namespace app\modules\userinfo\controllers;

use app\controllers\BaseController;
use app\lib\Tools;
use app\models\AnnualLeaveModel;
use app\models\MembersModel;
use app\modules\apply\delegate\LeaveDelegate;
use app\modules\userinfo\delegate\UserDelegate;
use app\modules\userinfo\helper\UserHelper;
use Yii;


class UserinfoController extends BaseController
{
    public $modelClass = 'app\models\MembersModel';

    /**
     * 上传头像
     */
    public function actionSetheadimg()
    {
        UserHelper::uploadUserHead($this->userInfo);
    }

    /**
     * 修改用户信息
     */
    public function actionSet()
    {
        $postdata = Yii::$app->request->post();
        return UserDelegate::updateUserInfo($postdata, $this->userInfo);
    }

    /**
     * 获取部门名称
     * @return array
     */
    public function actionGetdept()
    {
        $dept = UserDelegate::getDept($this->userInfo);
        if (!empty($dept)) {
            return ['code' => 20000, 'msg' => $dept['group_name'], 'data' => $dept['position']];
        } else {
            return ['code' => 20001, 'msg' => 'group name unavailable'];
        }
    }

    /**
     * 用户信息
     */
    public function actionUserInfo()
    {
        $res = array();
        $res['memberinfo'] = UserDelegate::getUserInfo($this->userInfo['u_id']);
        $res['memberinfo']['head_img'] = Tools::getHeadImg($res['memberinfo']['head_img']);
        $skillList = UserDelegate::getUserSkill($this->userInfo['u_id']);
        $skillStr = '';
        foreach ($skillList as $item) {
            $skillStr .= $item['skill_name'] . '、';
        }
        
        $res['skills'] = mb_substr($skillStr, 0, mb_strlen($skillStr) - 1, 'utf-8');
       
        if (!empty($res)) {
            return ['code' => 1, 'msg' => '成功', 'data' => $res];
        } else {
            return ['code' => -1, 'msg' => '失败'];
        }
    }

    /**
     * 获取用户技能
     * @return array
     */
    public function actionGetmemberskill()
    {
        $res['memberinfo'] = UserDelegate::getUserInfo($this->userInfo['u_id']);
        $res['memberinfo']['head_img'] = Tools::getHeadImg($res['memberinfo']['head_img']);
        $skill_list = UserDelegate::getUserSkill($this->userInfo['u_id']);
        $res['skill_list'] = UserHelper::doSkill(array_splice($skill_list,0,5));
        $res['skill_rulls'] = UserDelegate::getSkillRulls();
        return ['code' => 20000, 'msg' => 'ok', 'data' => $res];
    }

    /**
     * 获取假期信息
     * @return array
     */
    public function actionGetVacation()
    {
        //判断员工是否转正
        $info = MembersModel::getUserMessage($this->userInfo['u_id'],'is_formal');
        if($info['is_formal']!=1){
            $rs['vacation']['u_id'] = $this->userInfo['u_id'];
            $rs['vacation']['normal_leave'] = 0;
            $rs['vacation']['delay_leave'] = 0;
        }else{
            $rs['vacation'] = AnnualLeaveModel::getAnnualLeave($this->userInfo['u_id']);
            $rs['vacation']['normal_leave'] = floatval($rs['vacation']['normal_leave']);
        }
        $rs['over_time'] = floatval(LeaveDelegate::getInventorySum($this->userInfo['u_id']));
        return ['success' => 20000, 'msg' => 'ok', 'data' => $rs];
    }

    /**
     * 技能设置
     * @return array
     */
    public function actionSkillSet()
    {
        return UserDelegate::setSkill($this->userInfo['u_id'], Yii::$app->request->post());
    }

    /**
     * 提醒设置
     * @return array
     */
    public function actionRemindSet()
    {
        $rs = UserDelegate::setUserRemind($this->userInfo['u_id'], Yii::$app->request->post());
        if ($rs) {
            return ['success' => 20000, 'msg' => 'ok','data' => $rs];
        }
        return ['success' => 20001, 'msg' => 'failed'];
    }

    /**
     * 推送配置
     * @return array
     */
    public function actionSetpush() {
        $postData = Yii::$app->request->post();
        $member = MembersModel::findOne($this->userInfo['u_id']);
        $member->{$postData->option} = $postData->val;
        if($member->update(false) !== false) {
            return ['code' => 20000, 'msg' =>'修改成功'];
        }else {
            return ['code' => 200001, 'msg' =>'修改失败'];
        }
    }
}
