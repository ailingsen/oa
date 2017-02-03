<?php
/**
 * Created by PhpStorm.
 * User: liaoshuochao
 * Date: 2016/6/24
 * Time: 11:26
 */

namespace app\modules\workmate\controllers;

use app\lib\Tools;
use app\models\MembersModel;
use Yii;
use app\modules\workmate\delegate\WorkmateDelegate;
use app\controllers\BaseController;
use app\models\OrgModel;
use app\models\OrgMemberModel;
use app\lib\FResponse;


class WorkmateController extends BaseController
{
    public $modelClass = 'app\models\OrgModel';
    /**
     * 获取组织列表
     * @param $parentGroupId
     * @return array
     */
    public function actionList()
    {
        $parentGroupId = Yii::$app->request->get('parent_group_id');
        $groupList = WorkmateDelegate::getList($parentGroupId);
        FResponse::output(['code'=>20000, 'msg' => 'ok','data' => $groupList]);
    }

    /**
     * 获取所有组织列表
     * @return array
     */
    public function actionAllGroup()
    {
        $groupList = WorkmateDelegate::getAll();
        foreach ($groupList as $key => $value) {
            $orgIds = OrgModel::getAllChildrenOrgId($value['org_id']);
            if (empty($orgIds)) {
                $groupList[$key]['key'] = false;
            } else {
                $groupList[$key]['key'] = true;
            }
            $orgIds[] = $value['org_id'];
            $groupList[$key]['count'] = OrgMemberModel::getOrgMemberNum($orgIds);
        }
        $allGroupData = Tools::createTreeArr($groupList, 0, 'parent_org_id', 'org_id');
        FResponse::output(['code'=>2000, 'msg' => 'ok','data' => $allGroupData]);
    }
    /**
     * 我在的部门下面的总人数
     */
    public function actionSumMyOrg()
    {
        $orgId = Yii::$app->request->post('orgId');
        $teamSum = OrgMemberModel::find()->where(['parent_org_id'=>$orgId])->count();
        FResponse::output(['code'=>20000, 'msg' => 'ok','data' => $teamSum]);
    }
    /**
     * 获取组织信息
     * @param $orgId
     * @return array
     */
    public function actionOrgInfo()
    {
        $orgId = Yii::$app->request->post('orgId');
        $orgInfo = OrgModel::getOrgInfo($orgId, ['org_id', 'org_name', 'parent_org_id']);
        $pOrgName = OrgModel::getOrgInfo($orgInfo['parent_org_id'], ['org_name']);
        $orgInfo['parent_org_name'] = $pOrgName['org_name'];
        FResponse::output(['code'=>20000, 'msg' => 'ok','data' => $orgInfo]);
    }

    /**
     * 获取组织下成员列表
     * @param $orgId
     * @param int $page
     */
    public  function actionMemberlist()
    {
        $orgId = Yii::$app->request->post('orgId');
        $page = !empty(Yii::$app->request->post('curPage')) ? Yii::$app->request->post('curPage') : 1;
        $type = Yii::$app->request->post('type');
        $pageSize = 12 ;
        $realName = !empty(Yii::$app->request->post('realName')) ? Yii::$app->request->post('realName') : '';
        if($type==1){
            $pageSize = 100000;
        }
        WorkmateDelegate::getMemberList($orgId, $page, $pageSize, $realName);
    }
    
    /*
    *添加部门 
    */
    public function actionCreateOrg()
    {
        $groups = Yii::$app->request->post('groups');
        $group_name = Yii::$app->request->post('gname');
        WorkmateDelegate::getCreateOrg($groups, $group_name);
        
    }

    public function actionCancellead()
    {
        $gmid = Yii::$app->request->post('gmid');
        $om = OrgMemberModel::findOne($gmid);
        $om->is_manager = 0;

        if ($om->update(false) !== false) {
            return ['code' => 1];
        } else {
            return ['code' => 0];
        }
    }

    //移除部门成员到另一部门
    public function actionTransferDepartment()
    {
        $orgUid = Yii::$app->request->post('orgUid');
        $orgId = Yii::$app->request->post('orgId');
        WorkmateDelegate::transferDepartmentDelegate($orgUid, $orgId);
    }

    //解散部门
    public function actionDeleteOrg()
    {
        $orgId = Yii::$app->request->post('orgId');
        WorkmateDelegate::DeleteOrgDelegate($orgId);
    }

    /*
     * 工作情况
     */
    public function actionWorkingSituation()
    {
        $realName = empty(Yii::$app->request->post('realName')) ? '': Yii::$app->request->post('realName');
        $num = empty(Yii::$app->request->post('num'))? 10 : Yii::$app->request->post('num');
        $current = empty(Yii::$app->request->post('current')) ? 0 : Yii::$app->request->post('current');
        WorkmateDelegate::workingSituationDelegate($realName, $num, $current);
    }

    /*
     * 土豪积分榜
     */
    public function actionRichIntegral()
    {
        $richIntegral = MembersModel::getRichIntegral();
        foreach ($richIntegral as $key => $val){
            $richIntegral[$key]['head_img'] = Tools::getHeadImg($val['head_img']);
        }
        FResponse::output(['code'=>20000, 'msg' => 'ok','data' => $richIntegral]);
    }

    /**
     * 积分榜
     */
    public function actionIntegral()
    {
        $data = empty(Yii::$app->request->post('data')) ? 'week' : Yii::$app->request->post('data');
        $scoreBord = WorkmateDelegate::integralDelegate($data);
        FResponse::output(['code'=>20000, 'msg' => 'ok','data' => $scoreBord]);
    }
    /**
     * 获取所有员工
     */
    public function actionGetAllMembers()
    {
        $realName = !empty(Yii::$app->request->post('realName'))?Yii::$app->request->post('realName'):'';
        $allMembers = MembersModel::getAllMembers($realName);
        foreach ($allMembers as $key => $val){
            $allMembers[$key]['head_img'] = Tools::getHeadImg($val['head_img']);
        }
        FResponse::output(['code'=>20000, 'msg' => 'ok','data' => $allMembers]);
    }

    /**
     * @return array
     * @throws \Exception
     * 部门设置
     */
    public function actionUpdateOrg()
    {
        $groupName = Yii::$app->request->post('groupName');
        $parentId = Yii::$app->request->post('parentId');
        $flag = Yii::$app->request->post('flag');
        $gMid = Yii::$app->request->post('gMid');
        if(empty($gMid)){
            FResponse::output(['code'=>0, 'msg' => '请选择部门负责人']);
        }
        $orgId = Yii::$app->request->post('orgId');
        WorkmateDelegate::sectorSettingsDelegate($orgId, $groupName, $parentId, $gMid, $flag, $this->userInfo['u_id']);
    }

//    /**
//     * 设置部门负责人
//     */
//    public function actionSetLeader()
//    {
//        $gMid = Yii::$app->request->post('gMid');
//        $orgId = Yii::$app->request->post('orgId');
//        WorkmateDelegate::setLeadDelegate($orgId, $gMid);
//    }

    public function actionGetPersonalDepInfo()
    {
        $orgUid = Yii::$app->request->post('orgUid');
        $orgId = OrgMemberModel::find()->select('org_id')->where(['org_u_id'=>$orgUid])->asArray()->one()['org_id'];
        $orgName = OrgModel::find()->select('org_name')->where(['org_id' => $orgId])->asArray()->one()['org_name'];
        return $orgName;
    }

    /**
     * 选择部门负责人，成员列表
     */
    public function actionSelectDepartmentMember()
    {
        $orgId = Yii::$app->request->post('orgId');
        $searchName = !empty(Yii::$app->request->post('searchName')) ? Yii::$app->request->post('searchName'):'';
        $orgMemberList = OrgMemberModel::getDepartmentMember($orgId,$searchName);
        foreach ($orgMemberList as $key => $val){
            $orgMemberList[$key]['head_img'] = Tools::getHeadImg($val['head_img']);
        }
        FResponse::output(['code'=>20000, 'msg' => 'ok','data' => $orgMemberList]);
    }
    
    public function actionGetMyDepMember()
    {
        $orgId = Yii::$app->request->post('orgId');
        $type = Yii::$app->request->post('type');
        $pageSize = 12;
        if($type==1){
            $pageSize = 10000;
        }
        $curPage = !empty(Yii::$app->request->post('curPage')) ? Yii::$app->request->post('curPage') : 0;
        WorkmateDelegate::myDepartmentMemberListDelegate($orgId, $pageSize, $curPage);
    }
}