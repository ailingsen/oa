<?php

namespace app\modules\management\delegate;


//模型委托类。 处理控制器和动作列表
use app\lib\Easemob;
use app\lib\FileUploadHelper;
use app\lib\Tools;
use app\models\AnnualLeaveModel;
use app\models\ApptabSetModel;
use app\models\CalVacationModel;
use app\models\OrgMemberModel;
use app\models\PermissionGroupModel;
use app\models\PermissionMemberModel;
use app\modules\permission\helper\PermissionHelper;
use Yii;
use app\models\MembersModel;
use app\models\OrgModel;
use app\models\PermissionModel;
use yii\db\Query;

class EmployeeDelegate {

    /**
     * 获取员工信息
    */
    public static function getList($limit,$offset,$data,$field=[])
    {
        $M = (new Query())->from('oa_members as m')
            ->leftJoin('oa_org_member as om','om.u_id=m.u_id')
            ->leftJoin('oa_org as o','o.org_id=om.org_id')
            ->where('m.is_del=0');
        if(count($field) > 0){
            $M->select($field);
        }else{
            $M->select('m.*,o.org_name as org_name,o.org_id as org_id');
        }
        if(isset($data['perm_gourp_id']) && $data['perm_gourp_id']>0){//按照角色查询
            $M->andWhere('m.perm_groupid=:gid',[':gid'=>$data['perm_gourp_id']]);
        }
        if(isset($data['org_id']) && $data['org_id']>0){//按照部门查询
            $arrChildOrgId = OrgModel::getAllChildOrgId($data['org_id']);
            $M->andWhere(['in','o.org_id',$arrChildOrgId]);
        }
        if(isset($data['real_name']) && strlen($data['real_name'])>0){//按照员工姓名查询
            $M->andWhere(['like','m.real_name',$data['real_name']]);
        }
        $res['memList'] = $M->limit($limit)->offset($offset)->orderBy('m.u_id desc')->all();
        $res['page']['sumPage'] = ceil($M->count()/$limit);
        return $res;
        /*if($flag==1){//按照角色查询
            $res['data'] = $M->andWhere('m.perm_groupid=:gid',[':gid'=>$gid])->limit($size)->offset($offset)->orderBy('m.add_time desc')->all();
            $res['count'] = $M->andWhere('m.perm_groupid=:gid',[':gid'=>$gid])->count();
        }else if($flag==2){//按照部门查询
            $child_gid = (new Query())->select('all_children_id')->from('oa_org')->where(['org_id'=>$gid])->one();
            $res['data'] = $M->andWhere(['in','o.org_id',$child_gid['all_children_id']])->limit($size)->offset($offset)->orderBy('m.add_time desc')->all();
            $res['count'] = $M->andWhere(['in','o.org_id',$child_gid['all_children_id']])->count();
        }else if($flag==3){//按照员工姓名查询
            $res['data'] = $M->andWhere(['like','m.real_name',$gid])->limit($size)->offset($offset)->orderBy('m.add_time desc')->all();
            $res['count'] = $M->andWhere(['like','m.real_name',$gid])->count();
        }else{
            $res['data'] = $M->limit($size)->offset($offset)->orderBy('m.add_time desc')->all();
            $res['count'] = $M->count();
        }*/
    }

    /**
     * 获取角色信息
    */
    public static function getRoleInfo(){
        $roleInfo = PermissionGroupModel::find()->asArray()->all();
        foreach ($roleInfo as $key => $item) {
            if ($item['group_name'] == '超级管理员') {
                $permissList = PermissionModel::find()->select('pid')
                    ->asArray()
                    ->all();
                foreach ($permissList as $item) {
                    $pCodeList[] = $item['pid'];
                }
                $roleInfo[$key]['permission'] = json_encode($pCodeList);
                break;
            }
        }
        return $roleInfo;
    }

    /**
     * 获取所有组信息
    */
    public static function getAllOrgInfo(){
        $allOrgInfo = OrgModel::find()->asArray()->all();
        return $allOrgInfo;
    }

    /**
     * 获取员工基本信息
    */
    public static function getMemInfo($u_id)
    {
        $res = MembersModel::find()->select('oa_members.*,oa_org_member.org_id as org_id,oa_org_member.is_manager,o.org_name')
            ->leftJoin('oa_org_member as om','om.u_id=oa_members.u_id')
            ->leftJoin('oa_org as o','o.org_id=om.org_id')
            ->leftJoin('oa_org_member','oa_org_member.u_id = oa_members.u_id')->where('oa_members.u_id=:u_id and is_del=0',[':u_id'=>$u_id])->asArray()->one();
        return $res;
    }

    /**
     * 添加员工
    */
    public static function addEmp($arr,$arrOrg){
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        //添加用户信息
        $res = Yii::$app->db->createCommand()->insert('oa_members',$arr)->execute();
        if($res){
            $u_id = Yii::$app->db->getLastInsertID();
            //添加用户组信息
            //如果是部门负责人，则将部门其他负责人设置为不是负责人
            /*$isUpdate = true;
            if($arrOrg['is_manager'] == 1){
                $isUpdate = OrgMemberModel::updateAll(['is_manager'=>0],'org_id=:org_id',[':org_id'=>$arrOrg['org_id']]);
            }*/
            $orgData = ['org_id'=>$arrOrg['org_id'],'u_id'=>$u_id,'parent_org_id'=>$arrOrg['parent_org_id'],'is_manager'=>$arrOrg['is_manager']];
            $res = Yii::$app->db->createCommand()->insert('oa_org_member',$orgData)->execute();
            //if($res && $isUpdate){
            if($res){
                //环信用户名
                list($h_id,$h_null) = explode('@',$arr['username']);
                //插入环信库
                $hx = new Easemob(array());
                $hx = $hx->createUser($h_id,"000000");
                $UpdateData = [
                    'h_id' => $h_id,
                    'h_pwd' => '000000',
                ];
                $hxres = Yii::$app->db->createCommand()->update('oa_members', $UpdateData, 'u_id=' . $u_id)->execute();

                //计算年假
                $calVaction = new CalVacationModel();
                $vacation = $calVaction->calculateAnnualVacation($u_id,$arr['entry_time']);
                //插入年假库存表
                $leave = new AnnualLeaveModel();
                $leave->u_id = $u_id;
                $leave->normal_leave = $vacation['annual_vacation'];
                $isLeave = $leave->save(false);

                //添加移动端tab设置
                $apptab = new ApptabSetModel();
                $apptab->u_id = $u_id;
                $isApptab = $apptab->save(false);

                if($isLeave && $hxres && $isApptab){
                    $transaction->commit();
                    return array('code'=>1,'msg'=>'添加成功！','data'=>['u_id'=>$u_id]);
                }else{
                    $transaction->rollBack();
                    return array('code'=>0,'msg'=>'添加失败4！');
                }
            }else{
                $transaction->rollBack();
                return array('code'=>0,'msg'=>'添加失败5！');
            }
        }else{
            $transaction->rollBack();
            return array('code'=>0,'msg'=>'添加失败6！');
        }
    }

    /**
     * 编辑员工信息
    */
    public static function editEmp($arr,$arrOrg,$u_id){
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        //更新用户信息
        $memres = Yii::$app->db->createCommand()->update('oa_members', $arr, 'u_id=:u_id', [':u_id'=>$u_id])->execute();
        //更新组信息
        $orgres = true;
        $orgmemInfo = OrgMemberModel::find()->where('u_id=:u_id',[':u_id'=>$u_id])->asArray()->one();
        /*$isUpdate = true;
        //如果是部门负责人，则将部门其他负责人设置为不是负责人
        if($arrOrg['is_manager'] == 1){
            $isUpdate = OrgMemberModel::updateAll(['is_manager'=>0],'org_id=:org_id',[':org_id'=>$arrOrg['org_id']]);
        }*/
        //$orgData = ['org_id'=>$arrOrg['org_id'],'parent_org_id'=>$arrOrg['parent_org_id'],'is_manager'=>$arrOrg['is_manager']];
        $omModel = OrgMemberModel::findOne(['u_id'=>$u_id]);
        $omModel->org_id = $arrOrg['org_id'];
        $omModel->parent_org_id = $arrOrg['parent_org_id'];
        $omModel->is_manager = $arrOrg['is_manager'];
        $orgres = $omModel->save(false);
        //$orgres = Yii::$app->db->createCommand()->update('oa_org_member', $orgData, 'u_id=:u_id',[':u_id'=>$u_id])->execute();

        //if($memres && $orgres && $isUpdate){
        if($memres && $orgres){

            //插入年假库存表
            $adminMembers = MembersModel::findOne($u_id);
            $temp_entry_time = $adminMembers->entry_time;
            if(strtotime($temp_entry_time) != strtotime($arr['entry_time'])){
                $leave = AnnualLeaveModel::findOne(['u_id'=>$u_id]);
                if(!isset($leave->normal_leave)){
                    $transaction->rollback();
                    return array('code'=>-1,'msg'=>'数据错误-请联系管理员！');
                }else{
                    $calvaModel = new CalVacationModel();
                    $temp = $calvaModel->calculateAnnualVacation($u_id,$arr['entry_time']);
                    $leave->normal_leave = $temp['annual_vacation'];
                    $isLeave = $leave->save(false);
                    if($isLeave){
                        $transaction->commit();
                        return array('code'=>1,'msg'=>'修改成功！','data'=>['u_id'=>$u_id]);
                    }else{
                        $transaction->rollBack();
                        return array('code'=>-1,'msg'=>'修改失败-请重试！');
                    }
                }
            }else{
                $transaction->commit();
                return array('code'=>1,'msg'=>'修改成功！','data'=>['u_id'=>$u_id]);
            }
        }else{
            $transaction->rollBack();
            return array('code'=>-1,'msg'=>'更新失败！');
        }
    }

    /**
     * 获取所有父组组名
    */
    public static function getAllParentOrgInfo($list){
        if(!empty($list)){
            foreach($list as $key=>$val){
                if($val['org_id']>0){
                    $temp = OrgModel::getAllParentOrgname($val['org_id']);
                    $list[$key]['all_org_name'] =$temp['data'];
                }else{
                    $list[$key]['all_org_name'] = '';
                }

            }
        }
        return $list;
    }

    /**
     * 获取组信息
     * $org_id
    */
    public static function getOrgInfo($org_id)
    {
        $res = OrgModel::find()->where('org_id =:org_id',[':org_id'=>$org_id])->asArray()->one();
        return $res;
    }

    public static function checkUserName($username,$u_id=0){
        $flag = false;
        if($u_id>0){
            $info = MembersModel::find()->select('u_id')->where('username=:username and u_id!=:u_id',[':username'=>$username,':u_id'=>$u_id])->asArray()->one();
            if(isset($info['u_id'])){
                $flag = true;
            }
        }else{
            $info = MembersModel::find()->select('u_id')->where('username=:username',[':username'=>$username])->asArray()->one();
            if(isset($info['u_id'])){
                $flag = true;
            }
        }
        return $flag;
    }

    /**
     * 删除员工
    */
    public static function delEmp($u_id)
    {
        $data = ['is_del'=>1, 'leave_points'=>0];
        $res = Yii::$app->db->createCommand()->update('oa_members', $data, 'u_id=:u_id',[':u_id'=>$u_id])->execute();
        return $res;
    }

    /**
     * 获取用户的基本信息和权限信息
    */
    public static function getUserPermission($u_id)
    {
        $data = MembersModel::find()->where('oa_members.u_id=:u_id',[':u_id'=>$u_id])
            ->select('oa_members.u_id,oa_members.real_name,oa_members.position,o.org_name,oa_members.perm_groupid')
            ->leftJoin('oa_org_member as om','om.u_id=oa_members.u_id')
            ->leftJoin('oa_org as o','o.org_id=om.org_id')
            ->asArray()->one();
        return $data;
    }

    /**
     * 获取所有权限
     * $u_id
     */
    public static function getAllPermission($u_id,$is_create="false")
    {
        //获取用户权限
        if($is_create == 'false'){
            $arrUserPermInfo = PermissionMemberModel::find()->where('u_id=:u_id',[':u_id'=>$u_id])->asArray()->all();
        }else{
            $permissGroupModel = PermissionGroupModel::findOne(3);//默认普通用户
            $arrTempUserPermInfo = json_decode($permissGroupModel->permission);
            foreach($arrTempUserPermInfo as $key=>$val){
                $arrUserPermInfo[]['pid'] = $val;
            }
        }
        $arrUserPerm = [];
        foreach($arrUserPermInfo as $key=>$val){
            $arrUserPerm[] = $val['pid'];
        }
        //获取所有权限
        $arrAllPerm = PermissionModel::find()->where(['is_use' => 1])->asArray()->all();

        $permissionList = PermissionHelper::doPermission($arrAllPerm, $arrUserPerm);
        $permissionList = Tools::createTreeArr($permissionList, 0, 'parent_id', 'pid');
        $memInfo = self::getUserPermission($u_id);
        return ['permissionList'=>$permissionList,'memInfo'=>$memInfo];
    }

    /**
     * 保存用户角色
     * $u_id
     * $perm_groupid  角色ID
    */
    public static function saveUserRole($u_id,$perm_groupid)
    {
        $mModel = MembersModel::findOne($u_id);
        $mModel->perm_groupid = $perm_groupid;
        if($mModel->save(false)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 删除用户权限
    */
    public static function delUserPermission($u_id)
    {
        $res = PermissionMemberModel::deleteAll('u_id=:u_id',[':u_id'=>$u_id]);
        if($res === false) {
            return false;
        }else{
            return true;
        }
    }

    /**
     * 保存用户权限
     */
    public static function saveUserPermission($data)
    {
        $res = Yii::$app->db->createCommand()->batchInsert('oa_permission_member',['u_id','pid'],$data)->execute();
        if($res) {
            return true;
        }else{
            return false;
        }
    }

    /**
     * 判断员工编号是否已经存在
    */
    public static function isCardNo($card_no, $u_id=0){
        $memInfo = [];
        if($u_id>0){
            $memInfo = MembersModel::find()->where('u_id!=:u_id and card_no=:card_no',[':u_id'=>$u_id,':card_no'=>$card_no])->asArray()->one();
        }else{
            $memInfo = MembersModel::find()->where('card_no=:card_no',[':card_no'=>$card_no])->asArray()->one();
        }
        if(isset($memInfo['u_id'])){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 根据部门ID获取该部门的负责人信息
    */
    public static function getOrgManagerInfo($org_id)
    {
        $res = [];
        $res = OrgMemberModel::find()->select('oa_members.u_id,oa_members.real_name')->leftJoin('oa_members','oa_members.u_id=oa_org_member.u_id')->where('org_id=:org_id and is_manager=1 and oa_members.is_del=0',[':org_id'=>$org_id])->asArray()->one();
        return $res;
    }

    /**
     * 判断部门是否存在负责人
     */
    public static function is_manager($u_id,$org_id)
    {
        $info = OrgMemberModel::find()->leftJoin('oa_members','oa_members.u_id=oa_org_member.u_id')->select('oa_org_member.org_u_id')->where('oa_org_member.is_manager=1 and oa_org_member.org_id=:org_id and oa_org_member.u_id!=:u_id and oa_members.is_del=0',[':org_id'=>$org_id,':u_id'=>$u_id])->asArray()->one();
        if(isset($info['org_u_id'])){
            return true;
        }else{
            return false;
        }
    }


}