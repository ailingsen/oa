<?php

namespace app\modules\v1\delegate;


//模型委托类。 处理控制器和动作列表

use app\lib\Tools;
use app\models\Mcache;
use app\models\OrgMemberModel;
use app\models\OrgModel;
use app\models\ProjectLogModel;
use app\models\ProjectModel;
use Yii;
use yii\db\Query;

class ProjectDelegate
{

    /**
     * 我创建的项目
     * $type  1我创建的项目  2我参与的项目  3公开项目
     */
    public static function  getPro($type = 3, $u_id, $limit, $offset, $hasMyCreate = false)
    {
        $proModel = ProjectModel::find()->select('oa_project.pro_id,oa_project.pro_name,oa_project.public_type,oa_project.u_id,oa_members.real_name,oa_project.begin_time,oa_project.end_time,oa_project.delay_time,oa_project.complete')->leftJoin('oa_members','oa_members.u_id=oa_project.u_id');

        if ($type == 1) {//我创建的项目
            $proModel->where('oa_project.u_id=:u_id', [':u_id' => $u_id]);
        } else if ($type == 2) {//我参与的项目(不包括我创建的)
            $proModel->joinWith('projectmember');
            $proModel->where('oa_project_member.u_id=:u_id', [':u_id' => $u_id]);
            if (!$hasMyCreate) {
                $proModel->andWhere('oa_project.u_id!=:u_id', [':u_id' => $u_id]);//不包括我创建的
            }
        } else {//公开项目或部门内公开(不包括我创建和参与的)
            //获取用户所在的组ID
            $orgMember = OrgMemberModel::getMemberOrgInfo($u_id);
            $proModel->leftJoin('oa_org_member', 'oa_org_member.u_id=oa_project.u_id');
            $proModel->leftJoin('oa_org', 'oa_org_member.org_id=oa_org.org_id');
            $proModel->where('public_type=1');//公开项目
            $orgInfo = OrgModel::find()->where('org_id=:org_id', [':org_id' => $orgMember['org_id']])->asArray()->one();
            $arrChildOrg = explode(',', $orgInfo['all_children_id']);
            if (count($arrChildOrg) > 1) {
                $proModel->orWhere(['and', 'public_type=2', ['like', 'oa_org.all_children_id', "%," . $orgMember['org_id'], false]]);//部门内公开
                $proModel->orWhere(['and', 'public_type=2', ['like', 'oa_org.all_children_id', "," . $orgMember['org_id'] . ","]]);//部门内公开
                $proModel->orWhere(['and', 'public_type=2', ['like', 'oa_org.all_children_id', $orgMember['org_id'] . ",%", false]]);//部门内公开
            } else {
                $proModel->orWhere(['and', 'public_type=2', ['like', 'oa_org.all_children_id', $orgMember['org_id']]]);//部门内公开
            }

            //不包括我创建的
            $proModel->andWhere('oa_project.u_id!=:u_id', [':u_id' => $u_id]);
            //不包括我参与的
            $proModel->andWhere(['not in', 'oa_project.pro_id', (new Query())->select('oa_project.pro_id')->from('oa_project')->leftJoin('oa_project_member', 'oa_project_member.pro_id=oa_project.pro_id')->where(['oa_project_member.u_id' => $u_id])]);
        }

        $proModel->orderBy('oa_project.update_time desc,oa_project.create_time desc');
        $res['proList'] = $proModel->offset($offset)->limit($limit)->asArray()->all();
        $res['totalPage'] = ceil($proModel->count() / $limit);
        return $res;
    }

    /**
     * 获取我相关的项目
    */
    public static function getMyAllPro($u_id,$search='')
    {
        $res = [];
        $proModel = ProjectModel::find()->select('oa_project.pro_id,oa_project.pro_name');
        //我创建的项目
        $proModel->where('oa_project.u_id=:u_id', [':u_id' => $u_id]);
        //我参与的项目
        //$proModel->joinWith('projectmember');
        $proModel->leftJoin('oa_project_member','oa_project_member.pro_id=oa_project.pro_id');
        $proModel->orWhere('oa_project_member.u_id=:u_id', [':u_id' => $u_id]);
        //公开的项目
        $proModel->orWhere('public_type=1');//公开项目
        //部门内公开
        //获取用户所在的组ID
        $orgMember = OrgMemberModel::getMemberOrgInfo($u_id);
        $proModel->leftJoin('oa_org_member', 'oa_org_member.u_id=oa_project.u_id');
        $proModel->leftJoin('oa_org', 'oa_org_member.org_id=oa_org.org_id');
        $orgInfo = OrgModel::find()->where('org_id=:org_id', [':org_id' => $orgMember['org_id']])->asArray()->one();
        $arrChildOrg = explode(',', $orgInfo['all_children_id']);
        if (count($arrChildOrg) > 1) {
            $proModel->orWhere(['and', 'public_type=2', ['like', 'oa_org.all_children_id', "%," . $orgMember['org_id'], false]]);//部门内公开
            $proModel->orWhere(['and', 'public_type=2', ['like', 'oa_org.all_children_id', "," . $orgMember['org_id'] . ","]]);//部门内公开
            $proModel->orWhere(['and', 'public_type=2', ['like', 'oa_org.all_children_id', $orgMember['org_id'] . ",%", false]]);//部门内公开
        } else {
            $proModel->orWhere(['and', 'public_type=2', ['like', 'oa_org.all_children_id', $orgMember['org_id']]]);//部门内公开
        }

        //查询条件
        if(isset($search) && strlen($search)>0){
            $proModel->andWhere(['like','oa_project.pro_name',$search]);
        }
        $res = $proModel->asArray()->all();
        return $res;
    }

    /**
     * 我参与的项目
     */
    public static function getInvoPro($u_id)
    {
        $proModel = ProjectModel::find()->select('oa_project.pro_id,oa_project.pro_name');
        $proModel->leftJoin('oa_project_member','oa_project_member.pro_id = oa_project.pro_id');
        $proModel->where('oa_project_member.u_id=:u_id',[':u_id'=>$u_id]);
        $proModel->andWhere('oa_project.complete=:complete',[':complete'=>0]);
        $res['proList'] = $proModel->asArray()->all();
        return $res;
    }

    /**
     * 处理头像
     * data
    */
    public static function setHeadImg($data,$apiDomain)
    {
        if(is_array($data)){
            foreach($data as $key=>$val){
                $data[$key]['head_img'] = substr($apiDomain, 0, -1).Tools::getHeadImg($val['head_img']);
            }
            return $data;
        }else{
            return substr($apiDomain, 0, -1).Tools::getHeadImg($data);
        }
    }

    /**
     * 根据org_id获取组信息
    */
    public static function getOrgInfo($org_id)
    {
        $res = OrgModel::getOrgInfo($org_id,['org_id','org_name']);
        return $res;
    }

    /**
     * 根据org_id获取子组
    */
    public static function getChildOrgInfo($parend_org_id)
    {
        $res = OrgModel::find()->select('org_id,org_name')->where(['parent_org_id'=>$parend_org_id])->asArray()->all();
        return $res;
    }

    /**
     * 根据org_id获取该组成员
     */
    public static function getOrgMem($parend_org_id)
    {
        $res =OrgMemberModel::find()->select('oa_members.head_img,oa_members.u_id,oa_members.real_name,oa_members.h_id')
            ->leftJoin('oa_members','oa_org_member.u_id=oa_members.u_id')
            ->where('oa_members.is_del=0 and oa_org_member.u_id=:org_id',[':org_id'=>$parend_org_id])->asArray()->all();
        return $res;
    }

    /**
     * 获取操作日志
     */
    public static function getLog($pro_id,$limit,$offset)
    {
        $res =['proLog'=>[],'totalPage'=>0];
        $M = ProjectLogModel::find()->select('oa_project_log.create_time,oa_project_log.content,oa_project_log.u_id,oa_members.real_name,oa_members.head_img')
            ->leftJoin('oa_members','oa_members.u_id=oa_project_log.u_id')
            ->where('pro_id=:pro_id',[':pro_id'=>$pro_id]);
        $res['proLog'] = $M->orderBy('create_time DESC')->offset($offset)->limit($limit)->asArray()->all();
        $res['totalPage'] = ceil($M->count()/$limit) ;
        return $res;
    }

    //判断是否有权限查看项目
    public static function is_project($pro_id)
    {
        $info = self::getProInfo($pro_id);
        if (!(isset($info['pro_id']) && $info['pro_id'] > 0)) {
            return false;
        }
        return true;
    }

    /**
     * 获取项目信息
     */
    public static function getProInfo($pro_id)
    {
        $res = ProjectModel::find()->where('pro_id=:pro_id',[':pro_id'=>$pro_id])->asArray()->one();
        return $res;
    }


}