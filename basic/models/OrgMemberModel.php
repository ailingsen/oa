<?php

namespace app\models;

use app\lib\Tools;
use Yii;

/**
 * This is the model class for table "oa_org_member".
 *
 * @property integer $org_u_id
 * @property integer $orgId
 * @property integer $company_id
 * @property integer $u_id
 * @property integer $is_manager
 * @property integer $parent_org_id
 * @property integer $deep
 */
class OrgMemberModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_org_member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['org_id', 'u_id'], 'required'],
            [['org_id', 'company_id', 'u_id', 'is_manager', 'parent_org_id', 'deep'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'org_u_id' => 'Org U ID',
            'org_id' => 'Org ID',
            'company_id' => 'Company ID',
            'u_id' => 'U ID',
            'is_manager' => 'Is Manager',
            'parent_org_id' => 'Parent Org ID',
            'deep' => 'Deep',
        ];
    }

    /**
     * 根据组织ID获取该组织成员数量
     * @param $orgId
     * @return int|string
     */
    public static function getOrgMemberNum($orgId) 
    {
        return self::find()->leftJoin('oa_members','oa_org_member.u_id=oa_members.u_id')->where(['oa_org_member.org_id' => $orgId,'oa_members.is_del' => 0])->count();
    }

    /**
     * 查询该组所有成员
     * @param $orgId
     * @param int $page
     * @param int $pageSize
     * @param array $file
     * @param string $realName
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getOrgMemberList($orgId, $page = 1, $pageSize = 30, $file = ['oa_org_member.org_u_id', 'oa_org_member.is_manager', 'oa_org_member.org_id',
        'oa_members.u_id', 'oa_members.real_name', 'oa_members.phone', 'oa_members.email', 'oa_members.resumeId','oa_members.leave_points','oa_members.head_img', 'oa_members.points', 'oa_members.position', 'oa_org.org_name', 'oa_members.u_id'], $realName = '')
    {
        $orgIds = OrgModel::getAllChildrenOrgId($orgId);
        $orgIds[] = $orgId;
        //查询本组所有用户
        $query = self::find()->select($file)
            ->leftJoin('oa_org', 'oa_org_member.org_id=oa_org.org_id')
            ->leftJoin('oa_members', 'oa_members.u_id=oa_org_member.u_id');

        $query->where(['oa_members.status' => 1, 'oa_members.is_del' => 0]);

        if ($orgId != 0) {
            $query->where(['oa_org_member.org_id' => $orgIds,'oa_members.is_del'=>0]);
        }

        if(strlen($realName)>0){
            $query->andWhere(['like','oa_members.real_name',$realName]);
        }
        $totalPage = ceil($query->groupBy('oa_members.u_id')->count()/$pageSize);
        if(!empty($page)) {
            $query->limit($pageSize)->offset(($page-1)*$pageSize)->orderBy(['oa_org_member.is_manager' =>SORT_DESC,'oa_org_member.org_id'=>SORT_ASC,'oa_org_member.u_id'=> SORT_ASC]);
        }
        $list = $query->groupBy('oa_members.u_id')->orderBy('oa_members.u_id asc')->asArray()->all();
        return [
            'list' => $list,
            'totalPage' => $totalPage
        ];
    }

    /**
     * 查询本组所有成员数量
     * @param $orgId
     * @param string $realName
     * @return int|string
     */
    public static function getOrgMemberListCount($orgId, $realName = '')
    {
        $orgIds = OrgModel::getAllChildrenOrgId($orgId);
        $orgIds[] = $orgId;
        //查询本组所有用户
        $query = self::find();

        if ($orgId != 0) {
            $query->where(['oa_org_member.org_id' => $orgIds]);
        }

        if(strlen($realName)>0){
            $query->leftJoin('oa_members', 'oa_members.u_id=oa_org_member.u_id');
            $query->andWhere(['like','oa_members.real_name',$realName]);
        }

        return $query->count();
    }

    /**
     * 查询单个成员详情
     * @param $uid
     * @param array $file
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getOrgMemberitem($uid, $file = ['oa_org_member.org_u_id', 'oa_org_member.is_manager', 'oa_org_member.org_id',
        'oa_members.u_id', 'oa_members.real_name', 'oa_members.phone', 'oa_members.email', 'oa_members.resumeId','oa_members.leave_points',
        'oa_members.points', 'oa_members.position', 'oa_org.org_name']) 
    {

        //查询本组所有用户
        $result = self::find()->select($file)
            ->leftJoin('oa_org', 'oa_org_member.org_id=oa_org.org_id')
            ->leftJoin('oa_members', 'oa_members.u_id=oa_org_member.u_id')
            ->where(['oa_members.u_id' => $uid])
            ->asArray()->one();
        $result['org_name'] = OrgModel::getAllParentOrgname($result['org_id']);
        return $result;
    }

    /**
     * 获取用户的org信息
     * @param $uid
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getMemberOrgInfo($uid,$select='*')
    {
        return self::find()->select($select)->leftJoin('oa_org', 'oa_org_member.org_id=oa_org.org_id')->where(['oa_org_member.u_id' => $uid])->asArray()->one();
    }

    /**
     * 根据组ID获取该组负责人信息
     * @param $orgId
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getGroupMember($orgId)
    {
        $memberinfo = array();
        $temarr = OrgMemberModel::find()->leftJoin('oa_members','oa_members.u_id = oa_org_member.u_id')->select('oa_org_member.u_id')->where('org_id=:org_id and is_manager=1 and oa_members.is_del=0', array(':org_id' => $orgId))->asArray()->one();
        if (!(isset($temarr['u_id']) && $temarr['u_id'] > 0)) {
            return $memberinfo;
        }
        return MembersModel::find()->where('u_id=' . $temarr['u_id'])->one();
    }


    /**
     * 更新用户组织部门
     */
    public static function updateOrgMember($orgUid, $orgId)
    {
        return self::updateAll(['org_id'=>$orgId,'is_manager' => 0],['org_u_id'=>$orgUid]);
    }

    //获取用户直属上级UID
    public static function getLeaderUid($uid,$gid) {
        //取处理人的直属上级
        $result = self::find()->leftJoin('oa_members','oa_org_member.u_id=oa_members.u_id')->select(['oa_org_member.u_id'])
                    ->where(['oa_org_member.org_id' => $gid, 'oa_org_member.is_manager' => 1,'oa_members.is_del' => 0])->asArray()->one();
        //如果直属上级是申请人本人，则查询父类群组负责人作为其直属上级
        if(empty($result['u_id']) || $result['u_id'] == $uid) {
            //查询父组织
            $parent_group = OrgModel::find()->select('parent_org_id')->where(['org_id' => $gid])->asArray()->one();
            if ($parent_group['parent_org_id'] > 0) {
                return self::getLeaderUid($result['u_id'],$parent_group['parent_org_id']);
            }else {
                return false;           //已到顶级
            }
        }else {
            return $result['u_id'];
        }
    }
//    /**
//     *获取年假相关信息
//     */
//    public static function getAnnualVacationsInfo($orgId)
//    {
//        $annualInfo = self::find()->select('oa_members.real_name, oa_org.org_name, oa_annual_leave.normal_leave, oa_annual_leave.delay_leave')
//                        ->leftJoin('oa_annual_leave', 'oa_annual_leave.u_id=oa_org_member.u_id')
//                        ->leftJoin('oa_members', 'oa_members.u_id=oa_org_member.u_id')
//                        ->leftJoin('oa_org', 'oa_org.org_id=oa_org_member.org_id')
//                        ->where(['oa_org_member.org_id'=>$orgId]);
//        if($orgId){
//            
//        }
//    }
//
//    /*
//     * 获取组织部门下面的成员
//     */
//    public static function getOrgMembersList($orgId, $name)
//    {
//        $orgMemberData = self::find()->select('oa_members.real_name')
//                         ->leftJoin('oa_members', 'oa_members.u_id=oa_org_member.u_id')
//                         ->where(['oa_org_member.org_id' => $orgId])->andFilterWhere(['like','oa_members.real_name',$name])->asArray()->all();
//        print_r($orgMemberData);
//    }

    /**
     * @param $orgId
     * 设置部门负责人，相关成员
     */
    public static function getDepartmentMember($orgId, $searchName)
    {
        return self::find()->select('oa_members.head_img, oa_members.real_name, oa_org_member.org_u_id,oa_org_member.is_manager')
            ->leftJoin('oa_members', 'oa_members.u_id=oa_org_member.u_id')->where(['org_id'=>$orgId, 'oa_members.is_del'=>0, 'oa_members.status' => 1])->andFilterWhere(['like','oa_members.real_name',$searchName])->asArray()->all();
    }

    /**
     * @param $orgId
     * 我的团队成员列表
     */
    public static function myDepartmentMemberList($orgId, $pageSize, $curPage)
    {
        $orgIds = OrgModel::getAllChildrenOrgId($orgId);
        $orgIds[] = $orgId;
        //查询本组所有用户
        $query = self::find()->select('oa_org_member.org_u_id, oa_org_member.is_manager, oa_org_member.org_id,
            oa_members.u_id, oa_members.real_name, oa_members.phone, oa_members.email, oa_members.resumeId, oa_members.leave_points,oa_members.head_img, oa_members.points, oa_members.position, oa_org.org_name, oa_members.u_id, oa_members.h_id')
            ->leftJoin('oa_org', 'oa_org_member.org_id=oa_org.org_id')
            ->leftJoin('oa_members', 'oa_members.u_id=oa_org_member.u_id')->where(['oa_members.status' => 1, 'oa_members.is_del' => 0, 'oa_org_member.org_id' => $orgIds]);
        $totalPage = ceil($query->count()/$pageSize);
        $sum = $query->count();
        $query = $query->limit($pageSize)->offset($pageSize*($curPage-1))->orderBy(['oa_org_member.is_manager' =>SORT_DESC,'oa_org_member.org_id'=>SORT_ASC,'oa_org_member.u_id'=> SORT_ASC])->asArray()->all();
        foreach ($query as $key => $val){
            $query[$key]['head_img'] = Tools::getHeadImg($val['head_img']);
        }
        return [
            'totalPage' => $totalPage,
            'list' => $query,
            'sum' => $sum
        ];
    }

}
