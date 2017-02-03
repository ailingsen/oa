<?php

namespace app\modules\workmate\delegate;

use app\models\MembersModel;
use app\models\ProjectMemberModel;
use app\models\TaskModel;
use app\models\TaskSkillModel;
use Yii;
use app\models\OrgMemberModel;
use app\models\OrgModel;
use app\models\ModelFormSetModel;
use app\models\SkillMemberModel;
use app\models\ScoreLogModel;
use app\lib\Tools;
use app\lib\FResponse;


class WorkmateDelegate
{
    /**
     * 获取组织列表
     * @param $parentGroupId
     * @return array
     */
    public static function getList($parentGroupId){
        $list = OrgModel::getParentOrgList($parentGroupId, ['org_id', 'org_name', 'parent_org_id', 'org_points', 'org_all_points']);
        foreach ($list as $key => $value) {
            $orgIds = OrgModel::getAllChildrenOrgId($value['org_id']);
            if (empty($orgIds)) {
                $list[$key]['key'] = false;
            } else {
                $list[$key]['key'] = true;
            }
            $orgIds[] = $value['org_id'];
            $list[$key]['count'] = OrgMemberModel::getOrgMemberNum($orgIds);
        }
        return $list;
    }
    /**
     * 获取组织列包括负责人
     * @param $parentGroupId
     * @return array
     */
    public static function getOrgList($parentGroupId){
        $list = OrgModel::getParentOrgList($parentGroupId, ['org_id', 'org_name', 'parent_org_id', 'org_points', 'org_all_points']);
        $manager = OrgMemberModel::find()->select('oa_members.real_name, oa_members.head_img,oa_org_member.org_id, oa_members.h_id, oa_members.u_id, oa_org_member.is_manager')->leftJoin('oa_members','oa_members.u_id=oa_org_member.u_id')->where(['oa_org_member.org_id'=>$parentGroupId,'oa_members.is_del'=>0])->asArray()->all();
        foreach ($list as $key => $value) {
            $orgIds = OrgModel::getAllChildrenOrgId($value['org_id']);
            if (empty($orgIds)) {
                $list[$key]['key'] = false;
            } else {
                $list[$key]['key'] = true;
            }
            $orgIds[] = $value['org_id'];
            $list[$key]['count'] = OrgMemberModel::getOrgMemberNum($orgIds);
        }
        return [
            'list' => $list,
            'manager' => $manager
        ];
    }
    
    public static function getAll(){
        $groupList = OrgModel::getAllGroups();
        $selected = Yii::$app->request->post('selected');
        if ($selected && is_array($selected)) {
            foreach ($groupList as $key => $val) {
                foreach ($selected as $slectedItem) {
                    if ($slectedItem['org_id'] == $val['org_id']) {
                        $groupList[$key]['is_select'] = true; 
                    }
                }
            }
        }
        return $groupList;
    }
    
    public static function getOrgInfo($orgId){
        $orgInfo = OrgModel::getOrgInfo($orgId, ['org_id', 'org_name', 'parent_org_id']);
        $pOrgName = OrgModel::getOrgInfo($orgInfo['parent_org_id'], ['org_name']);
        $orgInfo['parent_org_name'] = $pOrgName['org_name'];
        FResponse::output(['code'=>20000, 'msg' => 'ok','data' => $orgInfo]);
    }

    public static function getMemberList($orgId, $page , $pageSize, $realName)
    {
        $listData = OrgMemberModel::getOrgMemberList($orgId, $page, $pageSize, ['oa_org_member.org_u_id', 'oa_org_member.is_manager', 'oa_org_member.org_id',
            'oa_members.u_id', 'oa_members.real_name', 'oa_members.phone', 'oa_members.email', 'oa_members.resumeId','oa_members.leave_points','oa_members.head_img', 'oa_members.points', 'oa_members.position', 'oa_org.org_name'],$realName);
        //获取用户技能积分等级  分组信息
        foreach ($listData['list'] as $key => $value) {
            $parent_org_ids = OrgModel::getAllParentOrg($value['org_id']);
            $result = OrgModel::find()->select(['org_name'])->where(['org_id' => $parent_org_ids])->asArray()->all();
            $tmpStr = '';
            foreach($result as $k => $v) {
                $tmpStr .= $v['org_name'] .'-';
            }
            $tmpStr .= $listData['list'][$key]['org_name'];
            $listData['list'][$key]['org_info'] = $tmpStr;
            $listData['list'][$key]['skills'] = SkillMemberModel::getMemberSkill($value['u_id']);
            $listData['list'][$key]['head_img'] = Tools::getHeadImg($value['head_img']);
            $listData['list'][$key]['isShow'] = false;
            if(empty($value['points'])){
                $listData['list'][$key]['points'] = 0;
            }
            //工作相关信息
            $listData['list'][$key]['pCount'] = 0;
            $listData['list'][$key]['unfinished'] = 0;
            $listData['list'][$key]['finished'] = 0;
            $listData['list'][$key]['isShow'] = false;
            $workSitData = TaskModel::getWorkingSituation($value['u_id']);
            $project = array();
            foreach ($workSitData as $workKey => $workVal){
                if (!in_array($workVal['pro_id'], $project)) {
                    $project[] = $workVal['pro_id'];
                }
                if($value['u_id'] == $workVal['charger']){
                    //未完成的任务数
                    if($workVal['status']==2 || $workVal['status']==3){
                        $listData['list'][$key]['unfinished'] += 1;
                    }
                    //已完成的任务数
                    if($workVal['status']==4){
                        $listData['list'][$key]['finished'] += 1;
                    }
                }
            }
            $listData['list'][$key]['project_count'] = count($project);
            //总任务数
            $listData['list'][$key]['pCount'] = $listData['list'][$key]['unfinished'] + $listData['list'][$key]['finished'];
        }
        $listData['sum'] = count($listData['list']);
        FResponse::output(['code'=>20000, 'msg' => 'ok','data' => $listData]);
    }

    /**
     * @param $orgId
     * @param $lately
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getOrgMemberList($orgId, $lately)
    {
        $cacheArray = explode(',', $lately);
        $arrSelect = OrgMemberModel::find()->select('oa_members.u_id,oa_members.real_name,oa_members.head_img')
            ->where(['in', 'oa_org_member.u_id', $cacheArray])
            ->leftJoin('oa_members', 'oa_org_member.u_id=oa_members.u_id')
            ->asArray()
            ->all();
        if (count($arrSelect)) {
            $rs = $arrSelect;
        } else {
            $rs = OrgMemberModel::getOrgMemberList($orgId, 1, 30, ['oa_members.u_id','oa_members.real_name','oa_members.head_img']);
            $rs = $rs['list'];
        }
        return $rs;
    }

    /**
     * @param $search
     * @return array
     */
    public static function searchMember($search) {
        $groupRes = OrgModel::find()->asArray()->all();

        $rs = array();
        foreach ($groupRes as $key => $value) {
            $db = OrgMemberModel::find()->select('oa_members.u_id,oa_members.real_name,oa_members.head_img')
                ->where('oa_org_member.org_id=:id', array(':id' => $value['org_id']))
                ->asArray()
                ->leftJoin('oa_members','oa_members.u_id=oa_org_member.u_id');

            $db->andFilterWhere(['like', 'oa_members.real_name', $search]);

            $res = $db->all();
            if (count($res)>0) {
                $rs = array_merge($rs, $res);
            }

        }
        return $rs;
    }


    public static function getCreateOrg($groups, $group_name)
    {
        $groups_arr = explode('-', $groups);
        if (0 == ($groups_arr[0])) {
            $parent_group_id = 0;
        } else if (0 == ($groups_arr[1])) {
            $parent_group_id = $groups_arr[0];
        } else {
            $parent_group_id = $groups_arr[1];
        }
        $org = OrgModel::find()->where(['org_name' => $group_name])->asArray()->one();
        if (!empty($org)) {
            FResponse::output(['code'=>0, 'msg' => '该部门已存在']);
        }
        $new_org = new OrgModel();
        $new_org->org_name = $group_name;
        $new_org->parent_org_id = $parent_group_id;

        if ($new_org->save()) {
            $lastId = Yii::$app->db->getLastInsertID();
            OrgModel::UpdateOrgChildrenId($lastId);
            OrgModel::UpdateOrgParentId($lastId);
//            if($parent_group_id) {
//                //更新相关申请可见人
//                $list = ModelFormSetModel::find()->where(['like', 'seeman', 'g_'.$parent_group_id])->all();
//                foreach($list as $key => $value) {
//                    $tmp = json_decode($value->seeman,true);
//                    $tmp[] = 'g_'.$lastId;
//                    $value->seeman = json_encode($tmp);
//                    $value->save();
//                }
//            }
            FResponse::output(['code'=>1,'msg'=>'']);
        } else {
            FResponse::output(['code'=>-1,'msg'=>'']);
        }
    }
    /*
     * 解散部门
     */
    public static function DeleteOrgDelegate($org_id)
    {
        //判断该部门下是否有子部门
        $res = OrgModel::find()->where(['parent_org_id' => $org_id])->asArray()->one();
        if (!empty($res)) {
            FResponse::output(['code'=> 0, 'msg'=>'该部门下存在子部门']);
        }

        //判断该部门下是否有部门成员
        $res = OrgMemberModel::find()->where(['org_id' => $org_id])->asArray()->one();
        if (!empty($res)) {
            FResponse::output(['code'=> 0, 'msg'=>'该部门下存在员工']);
        }
        //获取组信息
        $org = OrgModel::findOne($org_id);
        if ($org->delete() !== false) {
            OrgModel::UpdateOrgChildrenId($org_id);
            OrgModel::UpdateOrgParentId($org_id);
            FResponse::output(['code'=> 20000, 'msg'=>'删除部门成功']);
        } else {
            FResponse::output(['code'=> 0, 'msg'=>'删除部门失败']);
        }
    }

    /*
     * 转移部门
     */
    public static function transferDepartmentDelegate($orgUid, $orgId)
    {
        if (empty($orgUid) || !isset($orgId)) {
            FResponse::output(['code'=>0, 'msg' => '转移部门失败！']);
        }
        //获取组信息
        $org = OrgModel::findOne($orgId);
        if(!$org){
            FResponse::output(['code'=>0, 'msg' => '部门不存在！']);
        }
        $sta = array();
        $ret = array();
        $res = OrgMemberModel::find()->select('u_id')->where(['org_u_id' => $orgUid,'org_id'=>$orgId])->asArray()->one();
        if (!empty($res)) {
            $sta[] = '成员id' . $res['u_id'] . '已经是该部门成员';
        } else {
            $ret = OrgMemberModel::updateOrgMember($orgUid, $orgId);
        }
        if ($ret) {
            $uid = OrgMemberModel::find()->where(['org_u_id' => $orgUid])->asArray()->one()['u_id'];
            MembersModel::updateAll(['leave_points'=>0],['u_id'=>$uid]);
            FResponse::output(['code'=>20000, 'msg' => '添加成功！','data'=>$sta]);
        } else {
            FResponse::output(['code'=>0, 'msg' => '已经是该部门成员！','data'=>$sta]);
        }
    }

    /*
     * 个人工作信息
     */

    /*
     *工作情况
     */
    public static function workingSituationDelegate($realName, $num, $current)
    {
        $memberListData = MembersModel::getAllMemberList($realName, $num, $current);
        $workSitList['totalPage'] = $memberListData['totalPage'];
        $memberList['work'] = $memberListData['memberList'];
        foreach ($memberList['work'] as $key => $val){
            $workSitList['work'][$key]['pCount'] = 0;
            $workSitList['work'][$key]['unfinished'] = 0;
            $workSitList['work'][$key]['finished'] = 0;
            $workSitList['work'][$key]['realName'] = '';
            $workSitData = TaskModel::getWorkingSituation($val['u_id']);
            $workSitList['work'][$key]['project_count'] = empty(ProjectMemberModel::getCuntMyPartakePro($val['u_id'])['proNub'])? 0 : ProjectMemberModel::getCuntMyPartakePro($val['u_id'])['proNub'];
            foreach ($workSitData as $workKey => $workVal){
                if($val['u_id'] == $workVal['charger']){
                    //未完成的任务数
                    if($workVal['status']==2 || $workVal['status']==3){
                        $workSitList['work'][$key]['unfinished'] += 1;
                    }
                    //已完成的任务数
                    if($workVal['status']==4){
                        $workSitList['work'][$key]['finished'] += 1;
                    }
                }
            }
            $workSitList['work'][$key]['realName'] = $val['real_name'];
            //总任务数
            $workSitList['work'][$key]['pCount'] = $workSitList['work'][$key]['unfinished'] + $workSitList['work'][$key]['finished'];
        }
        $workSitList['totalPage'] = $memberListData['totalPage'];
        FResponse::output(['code'=>20000, 'msg' => 'ok','data' => $workSitList]);
    }

    public static function integralDelegate($data)
    {
        $beginData = '';
        $endData = '';
        $t = time();
        if ($data == 'week') {
            $beginData = strtotime(date('Y-m-d',(time()-((date('w')==0?7:date('w'))-1)*24*3600)));//本周开始时间戳
            $endData = $beginData + 604800;
        } elseif ($data == 'month') {
            $beginData = strtotime(date('Y-m-01', strtotime(date("Y-m-d"))));
            $endData =  strtotime(date('Y-m-d', mktime(0,0,0, date('m')+1, 1, date('Y'))));
        } elseif ($data == 'quarter') {
            //本季度未最后一月天数
            $season = ceil((date('n'))/3);//当月是第几季度
            $beginData = strtotime(date('Y-m-d H:i:s', mktime(0, 0, 0,$season*3-3+1,1,date('Y'))));
            $endData = strtotime(date('Y-m-d H:i:s', mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date("Y"))),date('Y'))));
        }
        $Integral = TaskModel::getIntegral($beginData, $endData);
        $res = [];
        foreach ($Integral as $key => $val){
            $temp = 0;
            $tempkey = 0;
            if(count($val['taskskill'])==0){
                continue;
            }
            foreach($res as $k=>$v){
                if($v['u_id']==$val['u_id']){
                    $temp = 1;
                    $tempkey=$k;
                    break;
                }
            }
            if($temp==0){
                $res[$key]=['u_id'=>$val['u_id'],'points'=>($val['speed']+$val['quality'])*count($val['taskskill']),'head_img'=>$val['head_img'],'real_name'=>$val['real_name']];
            }else{
                $res[$tempkey]['points'] =$res[$tempkey]['points']+(($val['speed']+$val['quality'])*count($val['taskskill']));
            }
        }
        foreach($res as $key=>$val){
            $res[$key]['head_img'] = Tools::getHeadImg($val['head_img']);
            if($val['points']==0){
                unset($res[$key]);
            }
        }
        //根据数组中的技能积分按倒序排列
       $flag=array();
        foreach($res as $arr){
            $flag[]=$arr['points'];
        }
        array_multisort($flag, SORT_DESC, $res);
        return $res;
    }

    /**
     * 部门设置
     * @param $group_id
     * @param $group_name
     * @param $parentId
     * @return array
     * @throws \Exception
     */
    public static function sectorSettingsDelegate($group_id, $group_name, $parentId, $gMid, $flag, $userUid)
    {
        if ($group_id == $parentId) {
            FResponse::output(['code'=>0, 'msg' => '不能选择自己作为上级部门']);
        }
        $allGroupId = OrgModel::getAllChildrenOrgId($group_id);
        if (in_array($parentId, $allGroupId)) {
            FResponse::output(['code'=>0, 'msg' => '不能选择自己的下级部门']);
        }
        if($flag == 1){
            $org = OrgModel::find()->where(['org_name' => $group_name])->asArray()->one();
            if (!empty($org)) {
                FResponse::output(['code'=>0, 'msg' => '该部门已存在,请重新输入！']);
            }
        }
        if (!$parentId) {
            $parentId = 0;
        }
        $org = OrgModel::findOne($group_id);
        $org->org_name = $group_name;
        $org->parent_org_id = $parentId;

        //取消之前的负责人
        $om_manager = OrgMemberModel::find()->where(['org_id' => $group_id,'is_manager' => 1])->one();
        if(!empty($om_manager)) {
            $om_manager->is_manager = 0;
            MembersModel::updateAll(['leave_points'=>0],['u_id' => $om_manager->u_id]);
            $om_manager->update(false);
        }
        $om = OrgMemberModel::findOne($gMid);
        $userId = $om->u_id;
        $om->is_manager = 1;
        $om->org_id = $group_id;
        $leavePoint = 0;
        if ($org->update(false) !== false && $om->update(false) !== false) {
            OrgModel::UpdateOrgChildrenId($group_id);
            OrgModel::UpdateOrgParentId($group_id);
            OrgModel::updateLeavePoints($group_id,$userId);
            if($userUid == $userId){
                $leavePoint = MembersModel::find()->where(['u_id' => $userId])->asArray()->one()['leave_points'];
            }
            FResponse::output(['code'=>20000, 'msg' => '修改部门成功','data' => $leavePoint]);
        } else {
            FResponse::output(['code'=>0, 'msg' => '修改部门失败']);
        }

    }

    /**
     * @param $orgId
     * @param $pageSize
     * @param $curPage
     * 我的团队成员及相关信息
     */
    public static function myDepartmentMemberListDelegate($orgId, $pageSize, $curPage)
    {
        $list = OrgMemberModel::myDepartmentMemberList($orgId, $pageSize, $curPage);
        foreach ($list['list'] as $key => $value) {
            $parent_org_ids = OrgModel::getAllParentOrg($value['org_id']);
            $result = OrgModel::find()->select(['org_name'])->where(['org_id' => $parent_org_ids])->asArray()->all();
            $tmpStr = '';
            foreach($result as $k => $v) {
                $tmpStr .= $v['org_name'] .'-';
            }
            $tmpStr .= $list['list'][$key]['org_name'];
            $list['list'][$key]['org_info'] = $tmpStr;
            $list['list'][$key]['skills'] = SkillMemberModel::getMemberSkill($value['u_id']);
            $list['list'][$key]['isShow'] = false;
            if(empty($value['points'])){
                $list['list'][$key]['points'] = 0;
            }
            //工作相关信息
            $list['list'][$key]['pCount'] = 0;
            $list['list'][$key]['unfinished'] = 0;
            $list['list'][$key]['finished'] = 0;
            $list['list'][$key]['isShow'] = false;
            $workSitData = TaskModel::getWorkingSituation($value['u_id']);
            $list['list'][$key]['project_count'] = !empty(ProjectMemberModel::getCuntMyPartakePro($value['u_id'])['proNub'])?ProjectMemberModel::getCuntMyPartakePro($value['u_id'])['proNub']:0;
            foreach ($workSitData as $workKey => $workVal){
                if($value['u_id'] == $workVal['charger']){
                    //未完成的任务数
                    if($workVal['status']==2 || $workVal['status']==3){
                        $list['list'][$key]['unfinished'] += 1;
                    }
                    //已完成的任务数
                    if($workVal['status']==4){
                        $list['list'][$key]['finished'] += 1;
                    }
                }
            }
            //总任务数
            $list['list'][$key]['pCount'] = $list['list'][$key]['unfinished'] + $list['list'][$key]['finished'];
        }
        FResponse::output(['code'=>20000, 'msg' => 'ok','data' => $list]);
    }
}
