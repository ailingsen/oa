<?php

namespace app\modules\userinfo\delegate;

use app\models\MembersModel;
use app\models\OrgModel;
use app\models\SkillLevelModel;
use app\models\SkillMemberModel;
use app\models\WorkRemindModel;
use app\modules\management\delegate\SkillDelegate;
use Yii;

class UserDelegate {

    /**
     * 更新用户信息
     * @param $postData
     * @param $userInfo
     * @return array
     */
    public static function updateUserInfo($postData, $userInfo)
    {
        $attr = $postData['option'];
        $value = $postData['val'];
        $member = MembersModel::findOne($userInfo['u_id']);
        if ($attr == 'pwd') {
            if ($value != $member->pwd) {
                return ['code' => 20002, 'msg'=>'原始密码错误'];
            }
            $newpass = $postData['newpass'];
            if ($newpass != $postData['newpass2']) {
                return ['code' => 20003, 'msg'=>'两次密码不一致'];
            }
            $member->pwd = $newpass;
        } else {
            $member->{$attr} = $value;
        }
        if ($member->save(false)) {
            MembersModel::deleteUserCache($member->u_id);
            return ['code' => 20000, 'msg' => '修改成功'];
        } else {
            return ['code' => 20001, 'msg' => '修改失败'];
        }
    }

    /**
     * 获取部门名称
     */
    public static function getDept($userInfo) 
    {
        $memberM = MembersModel::findOne($userInfo['u_id']);
        $dept['position'] = $memberM->position;
        if (!$memberM) {
            return ['code' => -1, 'msg' => 'member not found'];
        }
        $orgM = OrgModel::findOne($userInfo['org']['org_id']);
        $dept['group_name'] = $orgM->org_name;
        return $dept;
    }

    /**
     * 获取用户信息
     * @param $uid
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getUserInfo($uid)
    {
        $field = 'oa_members.*,oa_org.org_name';
        return MembersModel::getUserMessage($uid, $field);
    }

    /**
     * 获取用户技能信息
     * @param $uid
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getUserSkill($uid)
    {
        return SkillMemberModel::getMemberSkill($uid);
    }

    public static function getSkillRulls()
    {
        return SkillDelegate::getSkillLevel();
    }

    /**
     * 设置技能
     * @param $uid
     * @param $skills
     * @return array
     * @throws \yii\db\Exception
     */
    public static function setSkill($uid, $skills)
    {
        SkillMemberModel::deleteAll('member_id=' . $uid);
        $temp = array();
        foreach($skills as $key=>$val){
            $temp[$key][] = $uid;
            $temp[$key][] = $val['skill_id'];
        }
        if(count($temp)>0){
            $status = Yii::$app->db->createCommand()->batchInsert('oa_skill_member', ['member_id', 'skill_id'], $temp)->execute();
            if ($status) {
                return ['code' => 1, 'data' =>'修改成功'];
            } else {
                return ['code' => -1, 'data' =>'修改失败'];
            }
        }
    }

    /**
     * 获取用户提醒设置
     * @param $uid
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getUserRemind($uid)
    {
        $rs = ['daily', 'weekly'];
        $remind = WorkRemindModel::find()->where('u_id=:uid', [':uid' => $uid])->asArray()->all();
        if (!$remind) {
            return $rs;
        }
        foreach ($remind as $key => $value) {
            if (1 == $value['type']) {
                $rs['daily']['remind_time'] = '每天' . $value['remind_time'] . ':00';
            } else if (2 == $value['type']) {
                $rs['weekly']['remind_time'] = '每天' . $value['remind_time'] . ':00';
                $rs['weekly']['day'] = '每周' . $value['day'];
            }
        }
        return $rs;
    }

    /**
     * 设置用户提醒
     * @param $uid
     * @param $data
     * @return bool
     */
    public static function setUserRemind($uid, $data)
    {
        if (isset($data['remind_id'])) {
            $remindModel = WorkRemindModel::findOne($data['remind_id']);
            if (isset($data['remind_time'])) {
                $remindModel->remind_time = $data['remind_time'];
            }
            if (isset($data['day'])) {
                $remindModel->day = $data['day'];
            }
            if (isset($data['is_use'])) {
                $remindModel->is_use = $data['is_use'];
            }
            return $remindModel->save();
        } else {
            $data['u_id'] = $uid;
            return WorkRemindModel::createX($data);
        }
    }

}