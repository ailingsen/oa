<?php

namespace app\modules\management\controllers;

use app\controllers\BaseController;
use app\lib\Easemob;
use app\lib\FileUploadHelper;
use app\models\MembersModel;
use app\models\PermissionModel;
use app\modules\notice\helper\NoticeHelper;
use app\modules\permission\delegate\PermissionDelegate;
use app\modules\project\delegate\ProjectDelegate;
use Yii;
use app\modules\management\helper\EmployeeHelper;
use app\modules\management\delegate\EmployeeDelegate;
use yii\web\UploadedFile;

/**
 * Default controller for the `management` module
 */
class EmployeeController extends BaseController
{
    public $modelClass = 'app\models\MembersModel';

    /**
     * 获取列表员工信息
    */
    public function actionList(){
        $postdata = json_decode(file_get_contents("php://input"), true);
        $page = isset($postdata['page']) ? $postdata['page'] : 1;
        $pageParam = EmployeeHelper::setPage(1,$page);
        $field = ['m.u_id','m.card_no','m.email','m.entry_time','m.head_img','m.imei','m.is_formal','o.org_id','o.org_name','m.perm_groupid','m.permission','m.phone','m.position','m.real_name','m.username'];
        $list = EmployeeDelegate::getList($pageParam['limit'], $pageParam['offset'],$postdata,$field);//获取员工信息和员工总数
        //获取员工所在组的所有父类组信息
        $list['memList'] = EmployeeDelegate::getAllParentOrgInfo($list['memList']);
        $list['page']['curPage'] = $page;
        return ['code'=>1,'data'=>$list];
    }

    /**
     * 获取所有角色信息
    */
    public function actionAllperm()
    {
        //获取角色信息
        $allPerm = EmployeeDelegate::getRoleInfo();
        return ['code'=>1,'data'=>$allPerm];
    }

    /**
     * 根据u_id获取员工信息
     * $u_id
    */
    public function actionEmpInfo()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if(!(isset($postdata['u_id']) && $postdata['u_id']>0)){
            return ['code'=>-1,'msg'=>'参数错误！'];
        }
        //获取员工基本信息
        $info = EmployeeDelegate::getMemInfo($postdata['u_id']);
        $info['resumeId'] = json_decode($info['resumeId']);
        return ['code'=>1,'data'=>$info];
    }

    /**
     * 添加或修改员工信息
     * $type   add添加    edit修改
    */
    public function actionSaveEmpInfo(){
        $postdata = file_get_contents("php://input");
        $data = json_decode($postdata,true);
        if(!isset($data['type']) || ($data['type']!='edit' && $data['type']!='add')){
            return ['code'=>-1,'msg'=>'数据错误！'];
        }
        $type = $data['type'];
        $u_id=0;
        if($type=='edit'){
            if(isset($data['u_id']) && $data['u_id']>0){
                $memInfo = EmployeeDelegate::getMemInfo($data['u_id']);
                if(!isset($memInfo['u_id'])){
                    return array('code'=>-1,'msg'=>'该用户不存在！');
                }
                $u_id=$data['u_id'];
            }else{
                return array('code'=>-1,'msg'=>'数据错误！');
            }
        }
        $arr=[];
        $arr['username'] = $data['email'];
        $arr['real_name'] = $data['real_name'];
        if(!strlen($data['real_name'])>0){
            return ['code'=>-1,'msg'=>'姓名不能为空！'];
        }
        if(!ProjectDelegate::isStrlen($data['real_name'],20)){
            return ['code'=>-1,'msg'=>'姓名的长度不能超过20个字！'];
        }
        $is_realname = MembersModel::chkRealname($arr['real_name'],$u_id);
        if($is_realname){//判断用户真实名称唯一性
            return ['code'=>-1,'msg'=>'不能添加重复的姓名！'];
        }
        $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
        if (!preg_match( $pattern, $arr['username'] ))
        {
            return ['code'=>-1,'msg'=>'请输入正确的邮箱格式！'];
        }
        $flag = EmployeeDelegate::checkUserName($arr['username'],$u_id);
        if($flag){
            return ['code'=>-1,'msg'=>'不能添加重复的邮箱！'];
        }

        $arr['email'] = $data['email'];
        $arr['position'] = $data['position'];
        if(!strlen($data['position'])>0){
            return ['code'=>-1,'msg'=>'职位不能为空！'];
        }
        if(!ProjectDelegate::isStrlen($arr['position'],20)){
            return ['code'=>-1,'msg'=>'职位不能超过20个字！'];
        }
        if(isset($data['entry_time']) && strlen($data['entry_time'])>0){
            $arr['entry_time'] = date('Y-m-d',strtotime($data['entry_time']));
        }else{
            return ['code'=>-1,'msg'=>'入职时间不能为空！'];
        }
        $arr['is_formal'] = $data['is_formal'];
        if(isset($data['org_id']) && $data['org_id']>0){
            $orgInfo = EmployeeDelegate::getOrgInfo($data['org_id']);
            if(!isset($orgInfo['org_id'])){
                return ['code'=>-1,'msg'=>'该部门不存在！'];
            }
            $arrOrg = ['org_id'=>$data['org_id'],'parent_org_id'=>$orgInfo['parent_org_id']];
        }else{
            return ['code'=>-1,'msg'=>'请选择所属部门！'];
        }
        //设置负责人时判断是否存在负责人
        if($data['is_manager']==1){
            if(EmployeeDelegate::is_manager($u_id,$data['org_id'])){
                return ['code'=>-1,'msg'=>'保存失败，该部门已存在负责人！'];
            }
        }
        if(!(strlen($data['is_manager'])>0 && ($data['is_manager'] ==0 || $data['is_manager'] ==1))){
            return ['code'=>-1,'msg'=>'数据错误！'];
        }
        $arrOrg['is_manager'] = $data['is_manager'];
        $arr['card_no'] = $data['card_no'];
        if(!ProjectDelegate::isStrlen($arr['card_no'],10)){
            return ['code'=>-1,'msg'=>'员工编号不能超过10个字！'];
        }
        if(strlen($data['card_no'])>0){
            if(EmployeeDelegate::isCardNo($data['card_no'],$u_id)){//判断员工编号是否存在
                return ['code'=>-1,'msg'=>'该员工编号已存在！'];
            }
        }
        $arr['phone'] = $data['phone'];
        if(strlen($arr['phone'])>0){
            $pattern = "/^(13[0-9]|14[5|7]|15[0|1|2|3|5|6|7|8|9]|18[0|1|2|3|5|6|7|8|9]|17[7])\d{8}$/";
            if (!preg_match( $pattern, $arr['phone'] ))
            {
                return ['code'=>-1,'msg'=>'请输入正确的电话格式！'];
            }
        }
        if(isset($data['resumeId']['save_name'])){
            $arr['resumeId'] = json_encode($data['resumeId']);
        }

        $arr['add_time'] = time();
        if($type=='add'){//添加员工
            //设置初始密码
            $arr['pwd'] = md5(Yii::$app->params['pwd']);
            $res = EmployeeDelegate::addEmp($arr,$arrOrg);
            return $res;
        }else if($type=='edit'){//编辑员信息
            if(strlen($data['pwd'])>0 && $data['pwd']){
                if(!preg_match('/^[A-Za-z0-9]{6,16}$/',$data['pwd'])){
                    return ['code'=>-1,'msg'=>'密码长度为6-16位，仅允许数字或字母！'];
                }
                $arr['pwd'] = md5($data['pwd']);
            }
            $res = EmployeeDelegate::editEmp($arr,$arrOrg,$u_id);
            return $res;
        }
    }

    /**
     * 删除员工
     * $u_id
    */
    public static function actionDelEmp()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if(!(isset($postdata['u_id']) && $postdata['u_id']>0)){
            return ['code'=>-1,'msg'=>'参数错误！'];
        }
        $res = MembersModel::isMember($postdata['u_id']);
        if(!$res){
            return ['code'=>0,'msg'=>'该用户不存在'];
        }
        $res = EmployeeDelegate::delEmp($postdata['u_id']);
        if($res){
            $hx = new Easemob(array());
            $hid_res = MembersModel::find()->select('h_id')->where(['u_id' => $postdata['u_id']])->asArray()->one();
            $hx->deleteUser($hid_res['h_id']);
            return ['code'=>1,'msg'=>'删除成功！'];
        }else{
            return ['code'=>-1,'msg'=>'删除失败！'];
        }
    }

    /**
     * 简历上传
     */
    public function actionUpload()
    {
        $file = UploadedFile::getInstanceByName('file');
        if ($file == null) {
            return ['code' => 0,'msg' => '没有文件被上传或超过了服务器大小限制'];
        }
        if ($file->error == 1) {
            return ['code' => 0,'msg' => '文件大小超过限制'];
        }
        if ($file->size <= 0) {
            return ['code' => 0,'msg' => '文件大小文件大小必须大于0'];
        }
        if ($file->size > 5 * 1024 * 1024) {
            return ['code' => 0,'msg' => '文件大小不能超过5M'];
        }
        $ext = ['xls','xlsx','doc','docx','pdf'];
        if(count($ext)>0){
            if(!in_array(strtolower($file->extension),$ext)){
                return ['code' => 0,'msg' => '只能上传xls,xlsx,doc,docx格式的文件'];
            }
        }

        $extName = $file->extension;
        $save_name = 'resume_'.time().'.'.$extName;
        $save_path = '/static/resume/';
        $web_root = $web_root = \Yii::$app->basePath . '/web'.$save_path;
        $web_root = FileUploadHelper::createDir($web_root);
        if (!$file->saveAs($web_root.$save_name)){
            return ['code' => 0,'msg' => '上传失败，请重试'];
        }

        //处理上传附件图标
        $file_type='moren';
        $attIcon = include(FILE_ROOT.'/config/atticon.php');
        foreach($attIcon as $key=>$val){
            if(in_array($file->extension,$val)){
                $file_type=$key;
                break;
            }
        }

        echo json_encode([
            'code'=>1,
            'data'=>[
                'file_name' => $file->name,
                'save_name' => $save_name,
                'file_path' => $save_path,
                'file_size' => $file->size,
                'file_type' => $file_type
            ]
        ],true);
        die;
    }

    /**
     * 获取所有权限设置
    */
    public function actionAllPermission()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        $res = MembersModel::isMember($postdata['u_id']);
        if(!$res){
            return ['code'=>0,'msg'=>'该用户不存在'];
        }
        $is_create = isset($postdata['is_create'])?$postdata['is_create']:false;
        $data = EmployeeDelegate::getAllPermission($postdata['u_id'],$is_create);
        return ['code'=>1,'data'=>$data];
    }

    /**
     * 保存用户权限和角色
    */
    public function actionSaveUserPerm()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        $res = MembersModel::isMember($postdata['u_id']);
        if(!$res){
            return ['code'=>0,'msg'=>'该用户不存在'];
        }
        $transaction = Yii::$app->db->beginTransaction();
        //保存用户角色
        $saveRoleRes = EmployeeDelegate::saveUserRole($postdata['u_id'],$postdata['perm_groupid']);

        //保存用户权限
        //删除旧权限
        $delRes = EmployeeDelegate::delUserPermission($postdata['u_id']);

        //添加权限
        //设置权限格式
        $arrUserPerm = EmployeeHelper::setUserPermSaveFormat($postdata['userPermission'],$postdata['u_id']);
        $saveUserPermRes = true;
        if(count($arrUserPerm)>0){
            $saveUserPermRes = EmployeeDelegate::saveUserPermission($arrUserPerm);
        }

        if($saveRoleRes && $delRes && $saveUserPermRes){
            $transaction->commit();
            PermissionDelegate::delPermissionCache($postdata['u_id']);//删除权限缓存
            return ['code'=>1,'msg'=>'修改成功！'];
        }else{
            $transaction->rollBack();
            return ['code'=>0,'msg'=>'失败，请重试！'];
        }
    }

    /**
     * 根据部门ID获取部门负责人信息
    */
    public function actionOrgManagerInfo()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        $managerInfo = EmployeeDelegate::getOrgManagerInfo($postdata['org_id']);
        if(isset($managerInfo['real_name'])){
            return ['code'=>1,'managerInfo'=>$managerInfo];
        }else{
            return ['code'=>0];
        }
    }

    /**
     * 下载简历
    */
    public function actionDownfile(){
        $filepath = Yii::$app->request->get('filepath');//文件目录和文件
        $file_name = Yii::$app->request->get('file_name');
        $status=NoticeHelper::getDownFile(WEB_ROOT.$filepath,$file_name);
        //$status=NoticeHelper::getDownFile("D:/www/oa4/file".$filepath,$file_name,$size);
        if($status==1){
            return array('code' => 0, 'msg' =>'文件不存在');
        }
    }

}
