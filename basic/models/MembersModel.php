<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_members".
 *
 * @property integer $u_id
 * @property integer $p_id
 * @property string $username
 * @property string $pwd
 * @property integer $company_id
 * @property integer $points
 * @property string $real_name
 * @property string $phone
 * @property string $email
 * @property string $access_token
 * @property string $app_access_token
 * @property string $position
 * @property string $resumeId
 * @property string $entry_time
 * @property string $card_no
 * @property integer $status
 * @property integer $update_time
 * @property integer $add_time
 * @property string $h_id
 * @property string $h_pwd
 * @property integer $allow_task_email
 * @property integer $allow_apply_email
 * @property integer $allow_notice_email
 * @property integer $allow_project_email
 * @property integer $allow_approval_email
 * @property integer $allow_meeting_email
 * @property integer $allow_task_wechat
 * @property integer $allow_apply_wechat
 * @property integer $allow_notice_wechat
 * @property integer $allow_task_app
 * @property integer $allow_apply_app
 * @property integer $allow_notice_app
 * @property integer $allow_project_app
 * @property integer $allow_approval_app
 * @property integer $allow_meeting_app
 * @property integer $perm_groupid
 * @property integer $is_del
 * @property string $permission
 * @property integer $leave_points
 * @property string $imei
 * @property string $employee_no
 * @property integer $is_formal
 * @property integer $leader
 * @property string $head_img
 */
class MembersModel extends \yii\db\ActiveRecord
{
    const USER_INFO_CACHE_KEY = 'USER_INFO_BY_ID';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_members';
    }

    public function scenarios()
    {
        return [
            'add' => ['real_name', 'username', 'email', 'pwd', 'card_no', 'position', 'entry_time', 'leader', 'is_formal','resumeId', 'phone','permission','perm_groupid','add_time'],
            'update' => ['real_name', 'username', 'email', 'pwd', 'card_no', 'position', 'entry_time', 'leader', 'is_formal','resumeId', 'phone','permission','perm_groupid']
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['p_id', 'company_id', 'points', 'status', 'update_time', 'add_time', 'allow_task_email', 'allow_apply_email', 'allow_notice_email', 'allow_task_wechat', 'allow_apply_wechat', 'allow_notice_wechat', 'allow_task_app', 'allow_apply_app', 'allow_notice_app', 'perm_groupid', 'is_del', 'leave_points', 'is_formal', 'leader'], 'integer'],
            [['username', 'pwd', 'real_name', 'email'], 'required'],
            [['entry_time'], 'safe'],
            [['permission'], 'string'],
            [['username', 'email', 'h_pwd'], 'string', 'max' => 50],
            [['pwd'], 'string', 'max' => 32],
            [['real_name', 'position', 'card_no', 'h_id'], 'string', 'max' => 25],
            [['phone'], 'string', 'max' => 15],
            [['access_token', 'app_access_token'], 'string', 'max' => 40],
            [['resumeId', 'imei'], 'string', 'max' => 255],
            [['employee_no'], 'string', 'max' => 20],
            [['real_name', 'username', 'email', 'pwd', 'card_no', 'position', 'entry_time', 'leader', 'is_formal', 'perm_groupid'], 'required','message' => '必填项不能为空', 'on' => ['add', 'update']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'u_id' => 'U ID',
            'p_id' => 'P ID',
            'username' => 'Username',
            'pwd' => 'Pwd',
            'company_id' => 'Company ID',
            'points' => 'Points',
            'real_name' => 'Real Name',
            'phone' => 'Phone',
            'email' => 'Email',
            'access_token' => 'Access Token',
            'app_access_token' => 'App Access Token',
            'position' => 'Position',
            'resumeId' => 'Resume ID',
            'entry_time' => 'Entry Time',
            'card_no' => 'Card No',
            'status' => 'Status',
            'update_time' => 'Update Time',
            'add_time' => 'Add Time',
            'h_id' => 'H ID',
            'h_pwd' => 'H Pwd',
            'allow_task_email' => 'Allow Task Email',
            'allow_apply_email' => 'Allow Apply Email',
            'allow_notice_email' => 'Allow Notice Email',
            'allow_task_wechat' => 'Allow Task Wechat',
            'allow_apply_wechat' => 'Allow Apply Wechat',
            'allow_notice_wechat' => 'Allow Notice Wechat',
            'allow_task_app' => 'Allow Task App',
            'allow_apply_app' => 'Allow Apply App',
            'allow_notice_app' => 'Allow Notice App',
            'perm_groupid' => 'Perm Groupid',
            'is_del' => 'Is Del',
            'permission' => 'Permission',
            'leave_points' => 'Leave Points',
            'imei' => 'Imei',
            'employee_no' => 'Employee No',
            'is_formal' => 'Is Formal',
            'leader' => 'Leader',
            'head_img' => 'Head Img'
        ];
    }

    /**
     * 获取用户详情
     * @param $uid
     * @return array|bool|null|static
     */
    public static function getUserInfo($uid)
    {
        $user = '';
        $uid = intval($uid);
        //$user = Mcache::getCache(self::USER_INFO_CACHE_KEY.$uid);
        if(!$user){
            $user = self::find()->where('u_id=:u_id and is_del=0',[':u_id'=>$uid])->one();
            if (!$user) {
                return false;
            }
            $user = $user->attributes;
            //Mcache::setCache(self::USER_INFO_CACHE_KEY.$uid,$user);
        }
        $user['org'] = self::getUserOrgInfo($uid,[]);
        return $user;
    }

    /**
     * 获取用户信息
     * @param $num 每页条数
     * @param $current
     * @param  $u_name
     * @return array
     */
    public static function getUserInformation($num, $current, $u_name)
    {
        //获取所有用户信息
        return self::find()->select('oa_members.u_id, oa_members.real_name, c.org_name, oa_members.entry_time')
            ->leftJoin('oa_org_member b', 'b.u_id = oa_members.u_id')
            ->leftJoin('oa_org c', 'b.org_id = c.org_id')
            ->where(['like', 'oa_members.real_name', $u_name])
            ->limit($num)->offset($current)->asArray()->all();

    }

    /**
     * 修改权限
     * @param $permGroupid
     * @param $permission
     * @return int
     */
    public static function updatePermission($permGroupid, $permission)
    {
        self::updateAll(['permission' => $permission], ['perm_groupid' => $permGroupid]);
        $users = self::find()
            ->select('u_id')
            ->where('perm_groupid=:perm_groupid', [':perm_groupid' => $permGroupid])
            ->asArray()
            ->all();
        foreach ($users as $tmp) {
            self::deleteUserCache($tmp['u_id']);
        }
    }
    /**
     * 获取用户组信息
     * @param $uid
     * @param array $field
     * @return mixed
     */
    public static function getUserOrgInfo($uid,$field=array()) 
    {
        $M = OrgMemberModel::find();
        if(count($field)>0){
            $M ->select($field);
        }
        return $M ->leftJoin('oa_org', 'oa_org_member.org_id=oa_org.org_id')->where(['oa_org_member.u_id' => $uid])->asArray()->one();
    }

    /**
     * 设置缓存
     * @param $uid
     * @param $user
     */
    public static function setUserCache($uid,$user)
    {
        Mcache::setCache(self::USER_INFO_CACHE_KEY.$uid,$user);
    }

    /**
     * 删除缓存
     * @param $uid
     */
    public static function deleteUserCache($uid)
    {
        Mcache::deleteCache(self::USER_INFO_CACHE_KEY.$uid);
    }

    /**
     * 更新用户缓存
     * @param $uid
     * @param $arr
     */
    public static function updateUserCache($uid,$arr)
    {
        $user = Mcache::getCache(self::USER_INFO_CACHE_KEY.$uid);

        $flushUser = array_merge($user,$arr);

        Mcache::setCache(self::USER_INFO_CACHE_KEY.$uid,$flushUser);
    }

    /**
     * 获取所有用户的信息
     * @param array $arrSelect
     * @return mixed
     */
    public static function getAllMember($arrSelect=array('u_id'))
    {
        //$db=self::Command();
        //$mModel=$db->from('{{members}}');
        $mModel=self::find();
        if(!empty($arrSelect)){
            $strSelect=implode(',',$arrSelect);
            $mModel->select($strSelect);
        }
        $data=$mModel->asArray()->all();
        return $data;
    }

    /**
     * 获取用户基本信息
     * @param $uid
     * @param $fild
     * @return mixed
     */
    public static function getUserMessage($uid, $fild)
    {
        $userDetail = self::find()->select($fild)->leftJoin('oa_org_member','oa_org_member.u_id=oa_members.u_id')
            ->leftJoin('oa_org','oa_org.org_id=oa_org_member.org_id')
            ->where(['oa_members.u_id'=>$uid])->asArray()->one();
        return $userDetail;
    }

    /**
     * 通过条件查询成员信息 type = 2 增加查询条件entry_time >= :entryTime
     * @param $select
     * @param $conditions
     * @param int $type
     * @param int $pageSize
     * @param int $page
     * @return mixed
     */
    public static function getMembersByCondition($select, $conditions, $type = 1, $pageSize = 100, $page = 1)
    {
        $entryTime = 0;
        if (isset($conditions['entry_time']) && $type == 2) {
            $entryTime = $conditions['entry_time'];
            unset($conditions['entry_time']);
        }
        $offset = ($page - 1) * $pageSize;
        $query = self::find()->select($select)
            ->onCondition($conditions);
        if ($entryTime && $type == 2) {
            $query->andWhere('entry_time >= :entryTime ', [':entryTime' => $entryTime]);
        }
        return $query->offset($offset)
            ->limit($pageSize)
            ->asArray()
            ->all();
    }

    /**
     * 通过条件查询成员记录数
     * @param $select
     * @param $conditions
     * @param int $type
     * @return mixed
     */
    public static function getMembersCount($select, $conditions, $type = 1)
    {
        $entryTime = 0;
        if (isset($conditions['entry_time']) && $type == 2) {
            $entryTime = $conditions['entry_time'];
            unset($conditions['entry_time']);
        }
        $query = self::find()->select($select)
            ->onCondition($conditions);
        if ($entryTime && $type == 2) {
            $query->andWhere('entry_time > :entryTime ', [':entryTime' => $entryTime]);
        }
        return $query->count();
    }

    /**
     * 检查员姓名的唯一性
     * @param $name
     * @param int $u_id
     * @return bool
     */
    public static function chkRealname($name,$u_id=0)
    {
        $flag = false;
        if($u_id>0){
            $info = MembersModel::find()->select('u_id')->where('real_name=:real_name and u_id!=:u_id',[':real_name'=>$name,':u_id'=>$u_id])->asArray()->one();
            if(isset($info['u_id'])){
                $flag = true;
            }
        }else{
            $info = MembersModel::find()->select('u_id')->where('real_name=:real_name',[':real_name'=>$name])->asArray()->one();
            if(isset($info['u_id'])){
                $flag = true;
            }
        }
        return $flag;
    }

    /**
     * 更新用户信息
     * @param $memberId
     * @param $memberInfo
     * @param $reason
     * @return bool
     */
    public static function updateMemberInfo($memberId, $memberInfo, $reason = '')
    {
        $memberModel = self::findOne($memberId);
        if (!$memberInfo) {
            return false;
        }
        if (isset($memberInfo['points'])) {
            $memberModel->points = $memberModel->points + $memberInfo['points'];
            $memberModel->leave_points = $memberModel->leave_points + $memberInfo['leave_points'];
            $logInfo = ['u_id' => $memberId,
                'type' => 1,
                'content' => $reason,
                'score' => $memberInfo['points'],
                'score_before' => $memberModel->oldAttributes['points'],
                'score_after' => $memberModel->points,
                'create_time' => time(),
                'operator' => $memberId
            ];
            ScoreLogModel::insertScoreLog($logInfo);
        }
        if (isset($memberInfo['pwd'])) {
            $memberModel->pwd = $memberInfo['pwd'];
        }
        if (isset($memberInfo['real_name'])) {
            $memberModel->real_name = $memberInfo['real_name'];
        }
        if (isset($memberInfo['status'])) {
            $memberModel->status = $memberInfo['status'];
        }
        if (isset($memberInfo['leave_points'])) {
            $memberModel->leave_points = $memberModel->leave_points + $memberInfo['leave_points'];
            $logInfo = ['u_id' => $memberId,
                'type' => 2,
                'content' => $reason,
                'score' => $memberInfo['leave_points'],
                'score_before' => $memberModel->oldAttributes['leave_points'],
                'score_after' => $memberModel->leave_points,
                'create_time' => time(),
                'operator' => $memberId
            ];
            ScoreLogModel::insertScoreLog($logInfo);
        }
        if (isset($memberInfo['imei'])) {
            $memberModel->imei = $memberInfo['imei'];
        }
        if (isset($memberInfo['employee_no'])) {
            $memberModel->employee_no = $memberInfo['employee_no'];
        }
        if (isset($memberInfo['leader'])) {
            $memberModel->leader = $memberInfo['leader'];
        }
        if ($rs = $memberModel->save(false)) {
            self::setUserCache($memberModel->u_id, $memberModel->toArray());
        }
        return $rs;
    }

    /**
     * 根据用户ID获取用户信息
     * @param $mid
     * @param string $arrSelect
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getMemberInfo($mid, $arrSelect='*')
    {
        $db = self::find();
        if(!empty($arrSelect) && $arrSelect != '*'){
            $db=$db->select($arrSelect);
        }
        $data=$db->where('u_id=:mid', array(':mid' => $mid))->asArray()->one();
        return $data;
    }

    /**
     * 根据用户ID获取用户详细信息
     * @param $mid
     * @param string $arrSelect
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getMemberInfoDetail($mid, $arrSelect='*')
    {
        $db = self::find();
        if(!empty($arrSelect) && $arrSelect != '*'){
            $db=$db->select($arrSelect);
        }
        $db->leftJoin('oa_org_member','oa_org_member.u_id=oa_members.u_id');
        $db->leftJoin('oa_org','oa_org.org_id=oa_org_member.org_id');
        $data=$db->where('oa_members.u_id=:mid', array(':mid' => $mid))->asArray()->one();
        return $data;
    }


    /*
     * 获取所有员工列表
     */
    public static function getAllMemberList($realName, $num, $current)
    {
        $memberList = self::find()->select('u_id, real_name')->where(['is_del'=>0])->andFilterWhere(['like','real_name',$realName]);
        $totalPage = ceil($memberList->count()/$num);
        $memberList = $memberList->limit($num)->offset($num*($current-1))->asArray()->all();
        return [
            'totalPage' => $totalPage,
            'memberList'   =>$memberList
        ];
    }
    /*
     *土豪积分榜
     */
    public static function getRichIntegral()
    {
        return self::find()->select('real_name, points, head_img')->where(['is_del'=>0])->orderBy(['points'=> SORT_DESC])->asArray()->all();
    }

    /**
     * 根据部门ID和关键字模糊查找用户
     * @param string $keyword
     * @param int $org_id
     * @param string $field
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getMemberList($keyword='', $org_id=0, $field='oa_members.u_id,oa_members.real_name')
    {
        $M = MembersModel::find()->select($field)->where('oa_members.is_del=0 and oa_members.u_id!=1');
        if($org_id>0){
            //获取所有子部门
            $arrChildOrgId = OrgModel::getAllChildOrgId($org_id,1);
            if(count($arrChildOrgId)>0){
                $M->leftJoin('oa_org_member','oa_org_member.u_id = oa_members.u_id')->andWhere(['in','oa_org_member.org_id',$arrChildOrgId]);
            }
        }
        if(strlen($keyword)>0){
            $M->andWhere(['like','oa_members.real_name',$keyword]);
        }
        $list = $M->asArray()->orderBy('oa_members.u_id asc')->all();
        return $list;
    }

    /*
     * 根据部门ID搜索部门下所有成员的调休假
     */
    public static function getOrgMemberVacation($orgId, $userName, $pageSize, $curPage)
    {
        $arrChildOrgId = OrgModel::getAllChildOrgId($orgId,1);
        $vacationData = self::find()->select(['oa_members.u_id', 'oa_org.org_name', 'oa_members.real_name', '(oa_annual_leave.normal_leave+oa_annual_leave.delay_leave) as annualLeave '])
                        ->leftJoin('oa_org_member','oa_org_member.u_id = oa_members.u_id')
                        ->leftJoin('oa_org', 'oa_org.org_id=oa_org_member.org_id')
                        ->leftJoin('oa_annual_leave', 'oa_annual_leave.u_id=oa_members.u_id');
        if(empty($userName)){
            $vacationData = $vacationData->where(['oa_members.is_del'=> 0])->andWhere(['!=','oa_members.u_id',1])->andWhere(['in','oa_org_member.org_id',$arrChildOrgId]);
        }else{
            $vacationData = $vacationData->where(['oa_members.is_del'=> 0])->andWhere(['!=','oa_members.u_id',1])->andFilterWhere(['like','oa_members.real_name',$userName]);
        }
        $totalPage = ceil($vacationData->count()/$pageSize);
        $vacationData = $vacationData->limit($pageSize)->offset($pageSize*($curPage-1))->orderBy(['oa_members.u_id' => 'SORT_ASC'])->asArray()->all();
        foreach ($vacationData as $key => $val){
            $vacationData[$key]['workDays'] = VacationInventoryModel::getVaInventory($val['u_id']);
            if(empty($val['annualLeave'])){
                $vacationData[$key]['annualLeave'] = 0;
            }else if ($val['annualLeave']<=0){
                $vacationData[$key]['annualLeave'] = 0;
            }
        }
       return[
           'totalPage' => $totalPage,
           'vacationData' => $vacationData
       ];
    }

    /*
     * 获取所有员工
     */
    public static function getAllMembers($realName)
    {
        return self::find()->select('u_id, real_name, head_img')->where(['is_del'=>0])->andFilterWhere(['like','real_name',$realName])->asArray()->all();
    }

    /**
     * 判断用户是否存在
    */
    public static function isMember($u_id){
        $info = MembersModel::find()->where('u_id=:u_id and is_del=0',[':u_id'=>$u_id])->asArray()->one();
        if(isset($info['u_id'])){
            return true;
        }else{
            return false;
        }
    }

    public function getPermission()
    {
        // 第一个参数为要关联的子表模型类名，
        // 第二个参数指定 通过子表的customer_id，关联主表的id字段
        return $this->hasMany(PermissionMemberModel::className(), ['u_id' => 'u_id']);
    }

    /**
     * 搜索联系人
     */
    public static function getGetContacts($searchName, $pageSize, $curPage, $type)
    {
        $contactsData = self::find()->select('u_id, h_id,head_img, real_name')->where(['is_del'=>0]);
        if(strlen($searchName)>0){
            $contactsData->andWhere(['like','real_name',$searchName]);
        }
        $totalPage = 1;
        if($type == 1){
            $contactsData = $contactsData->asArray()->all();
        }elseif ($type == 2){
            $totalPage = ceil($contactsData->count()/$pageSize);
            $contactsData = $contactsData->limit($pageSize)->offset($pageSize*($curPage-1))->asArray()->all();
        }
        return [
            'totalPage' => $totalPage,
            'contactsData' => $contactsData
        ];
    }
}
