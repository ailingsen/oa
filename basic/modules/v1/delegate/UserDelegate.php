<?php

namespace app\modules\v1\delegate;


//模型委托类。 处理控制器和动作列表

use app\models\MembersModel;
use app\models\SkillMemberModel;
use app\modules\userinfo\helper\UserHelper;
use Yii;

class UserDelegate
{

    //获取用户基本信息
    public static function getUserInfo($u_id)
    {
        $res = MembersModel::find()->select('oa_members.u_id,oa_members.head_img,oa_members.real_name,oa_members.position,oa_org.org_name,oa_members.phone,oa_members.email,oa_members.points,oa_members.h_id')
            ->leftJoin('oa_org_member','oa_org_member.u_id=oa_members.u_id')
            ->leftJoin('oa_org','oa_org.org_id=oa_org_member.org_id')
            ->where(['oa_members.u_id'=>$u_id,'is_del'=>0])->asArray()->one();
        return $res;
    }

    //获取用户最高等级的技能信息
    public static function getHighSkillInfo($u_id)
    {
        $res = ['skill_name'=>'','skill_title'=>'','skill_point'=>''];
        $info = SkillMemberModel::find()->select('oa_skill_member.skill_id,oa_skill_member.point,oa_skill.skill_name')
            ->leftJoin('oa_skill','oa_skill.skill_id=oa_skill_member.skill_id')
            ->where('oa_skill_member.member_id=:u_id',[':u_id'=>$u_id])
            ->orderBy('oa_skill_member.point desc')->asArray()->one();
        if(isset($info['skill_name'])){
            $res['skill_name'] = $info['skill_name'];
            $res['skill_point'] = $info['point'];
            $HighSkillInfo[] = $info;
            $HighSkillInfo = UserHelper::doSkill($HighSkillInfo);
            if(isset($HighSkillInfo[0]['title'])){
                $res['skill_title'] = $HighSkillInfo[0]['title'];
            }
        }
        return $res;
    }

    //获取消息设置
    public static function getMemssageSet($u_id)
    {
        $res = MembersModel::find()->select('allow_task_app,allow_apply_app,allow_notice_app,allow_project_app,allow_approval_app,allow_meeting_app')->where(['u_id'=>$u_id])->asArray()->one();
        return $res;
    }

}