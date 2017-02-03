<?php
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/11/4
 * Time: 17:07
 */

namespace app\modules\v1\controllers;

use app\models\MembersModel;
use app\lib\Tools;
use app\lib\FResponse;
use app\models\OrgModel;
use app\models\OrgMemberModel;
use app\modules\workmate\delegate\WorkmateDelegate;
use app\modules\boardroom\delegate\BoardroomDelegate;

class WorkmateController extends BaseController
{
    public $modelClass = 'app\models\ChecktimeModel';
    /**
     * 纳米币
     */
    public function actionRichIntegral()
    {
        $this->isPerm('WorkmateScoreboard');
        $postdata = json_decode(file_get_contents("php://input"),true);
        $richIntegral['list'] = MembersModel::getRichIntegral();
        $curPage = empty($postdata['curPage']) ? 1:$postdata['curPage'];
        $pageSize = empty($postdata['pageSize']) ? 10:$postdata['pageSize'];
        foreach ($richIntegral['list'] as $key => $val){
            $richIntegral['list'][$key]['head_img'] = substr($this->apiDomain, 0, -1).Tools::getHeadImg($val['head_img']);
        }
        $richIntegral['totalPage'] = ceil(count($richIntegral['list'])/$pageSize);
        $richIntegral['list'] = BoardroomDelegate::pageArray($curPage, $pageSize, $richIntegral['list']);
        FResponse::output(['code'=>20000, 'msg' => 'ok','data' => $richIntegral]);
    }
    /**
     * 积分晋升榜
     */
    public function actionIntegral()
    {
        $this->isPerm('WorkmateScoreboard');    
        $postdata = json_decode(file_get_contents("php://input"),true);
        $curPage = empty($postdata['curPage']) ? 1:$postdata['curPage'];
        $pageSize = empty($postdata['pageSize']) ? 10:$postdata['pageSize'];
        $data = empty($postdata['data']) ? 'week' : $postdata['data'];
        $scoreBord['list'] = WorkmateDelegate::integralDelegate($data);
        foreach ($scoreBord['list']  as $key => $value){
            $scoreBord['list'] [$key]['head_img'] = substr($this->apiDomain, 0, -1).$value['head_img'];
        }
        $scoreBord['totalPage'] = ceil(count($scoreBord['list'] )/$pageSize);
        $scoreBord['list']  = BoardroomDelegate::pageArray($curPage, $pageSize, $scoreBord['list'] );
        FResponse::output(['code'=>20000, 'msg' => 'ok','data' => $scoreBord]);
    }

    /**
     * 获取组织列表
     * @param $parentGroupId
     * @return array
     */
    public function actionList()
    {
        $postdata = json_decode(file_get_contents("php://input"),true);
        $parentGroupId = $postdata['parentId'];
        $groupList = WorkmateDelegate::getOrgList($parentGroupId);
        foreach ($groupList['manager'] as $key =>$val){
            $groupList['manager'][$key]['head_img'] = substr($this->apiDomain, 0, -1).Tools::getHeadImg($val['head_img']);
        }
        FResponse::output(['code'=>20000, 'msg' => 'ok','data' => $groupList]);
    }

    /**
     * 获取所有组织列表
     * @return array
     */
    public function actionAllGroup()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata,true);
        $orgId = $request['orgId'];
        $groupList = array_reverse(OrgModel::getAllChildOrgId($orgId));
        foreach ($groupList as $key => $value) {
            $orgIds[$key]= OrgModel::find()->select('org_name,org_id')->where(['org_id'=>$value])->asArray()->one();
        }
        FResponse::output(['code'=>20000, 'msg' => 'ok','data' => $orgIds]);
    }

    /**
     * 获取部门下成员
     */
    public function actionGetMyDepMember()
    {
        $postdata = json_decode(file_get_contents("php://input"),true);
        $orgId = $postdata['orgId'];
        $type = !empty($postdata['type']) ? $postdata['type'] : 0;
        $pageSize = !empty($postdata['pageSize']) ? $postdata['pageSize'] : 10;
        if($type==1){
            $pageSize = 10000;
        }
        $curPage = !empty($postdata['curPage']) ? $postdata['curPage'] : 0;
        WorkmateDelegate::myDepartmentMemberListDelegate($orgId, $pageSize, $curPage);
    }


    /**
     * 选择部门负责人，成员列表
     */
    public function actionSelectDepartmentMember()
    {
        $postdata = json_decode(file_get_contents("php://input"),true);
        $orgId = $postdata['orgId'];
        if(!isset($orgId)){
            FResponse::output(['code'=>20001, 'msg' => '参数错误！']);
        }
        $searchName = !empty($postdata['searchName']) ? $postdata['searchName']:'';
        $orgMemberList = OrgMemberModel::getDepartmentMember($orgId,$searchName);
        foreach ($orgMemberList as $key => $val){
            $orgMemberList[$key]['head_img'] = Tools::getHeadImg($val['head_img']);
        }
        FResponse::output(['code'=>20000, 'msg' => 'ok','data' => $orgMemberList]);
    }
}