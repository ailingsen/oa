<?php
namespace app\modules\v1\controllers;

use app\lib\Tools;
use app\models\FResponse;
use app\models\ProjectMemberModel;
use app\models\ProjectModel;
use app\modules\attendance\delegate\AttendanceDelegate;
use app\modules\project\Project;
use app\modules\v1\helper\ProjectHelper;
use app\modules\v1\delegate\ProjectDelegate;
use Yii;
use Yii\base\Object;
use app\models\Mcache;

class ProjectController extends BaseController
{
    public $modelClass = 'app\models\ProjectModel';
    /**
     * 项目列表
     * $type 1我创建的项目   2我参与的项目   3公开项目
    */
    public function actionGetpro()
    {
        $postData = json_decode(file_get_contents("php://input"));
        if(isset($postData->type) && !in_array($postData->type,[1,2,3])){
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]); 
        }
        //权限
        if($postData->type==1){
            $this->isPerm('ProjectMycreate');
        }else if($postData->type==2){
            $this->isPerm('ProjectMypartake');
        }else{
            $this->isPerm('ProjectPublic');
        }

        if( empty($postData->page) || empty($postData->pageSize) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $page = $postData->page;
        $pageSize = $postData->pageSize;
        $offset = ($page-1)*$pageSize;
        $limit = $pageSize;
        $res = ProjectDelegate::getPro($postData->type,$this->userInfo['u_id'],$limit,$offset);
        $res['proList'] = ProjectHelper::setProData($res['proList'],0);
        FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>$res]);
    }

    /**
     * 项目详情页
     * $pro_id  项目ID
     */
    public function actionProDetail()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if( empty($postdata['pro_id']) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        //判断项目是否存在
        $is_pro = ProjectDelegate::is_project($postdata['pro_id']);
        if($is_pro == false){
            FResponse::output(['code' => 20005, 'msg' => "数据不存在", 'data'=>new Object()]);
        }
        //判断是否有权限查看项目
        $proInfo = \app\modules\project\delegate\ProjectDelegate::is_permission($this->userInfo['u_id'],$postdata['pro_id']);
        if($proInfo == false){
            FResponse::output(['code' => 20007, 'msg' => "没有访问该项目的权限", 'data'=>new Object()]);
        }
        //获取项目成员
        $proMem = \app\modules\project\delegate\ProjectDelegate::getProMem($postdata['pro_id']);
        //获取项目状态
        $proInfo['status'] = ProjectHelper::setProStatus($proInfo);
        //获取项目任务
        $proTask = \app\modules\project\delegate\ProjectDelegate::getProTask($postdata['pro_id'],$type=1);
        //获取项目任务完成比例
        $proInfo['degree'] = ProjectHelper::setProDegree($proTask);
        //时间格式化
        $proInfo = ProjectHelper::setTimeFormat($proInfo);
        //处理项目成员头像
        $proMem = \app\modules\project\helper\ProjectHelper::setHeadImg($proMem);
        foreach($proMem as $key=>$val){
            $proMem[$key]['head_img_path'] = substr($this->apiDomain, 0, -1).$proMem[$key]['head_img_path'];
            unset($proMem[$key]['head_img']);
        }
        FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>['proInfo'=>$proInfo,'proMember'=>$proMem]]);
    }

    /**
     * 项目编辑获取项目信息
     * $pro_id  项目ID
     */
    public function actionProDetailSimp()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if( empty($postdata['pro_id']) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        //判断项目是否存在
        $is_pro = ProjectDelegate::is_project($postdata['pro_id']);
        if($is_pro == false){
            FResponse::output(['code' => 20005, 'msg' => "数据不存在", 'data'=>new Object()]);
        }
        //判断是否有权限查看项目
        $proInfo = \app\modules\project\delegate\ProjectDelegate::is_permission($this->userInfo['u_id'],$postdata['pro_id']);
        if($proInfo == false){
            FResponse::output(['code' => 20007, 'msg' => "没有该项目的访问权限", 'data'=>new Object()]);
        }
        //获取项目成员
        $proMem = \app\modules\project\delegate\ProjectDelegate::getProMem($postdata['pro_id']);
        //设置头像
        $proMem = \app\modules\project\helper\ProjectHelper::setHeadImg($proMem);
        foreach($proMem as $key=>$val){
            $proMem[$key]['head_img_path'] = substr($this->apiDomain, 0, -1).$proMem[$key]['head_img_path'];
            unset($proMem[$key]['head_img']);
        }
        //时间格式转换
        $proInfo = ProjectHelper::setTimeFormat($proInfo);
        //获取项目状态
        $proInfo['status'] = ProjectHelper::setProStatus($proInfo);
        FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>['proInfo'=>$proInfo,'proMember'=>$proMem]]);
    }

    /**
     * 编辑项目
     * $pro_id  项目ID
     * $begin_time  开始时间
     * $end_time  结束时间
     * $pro_name  项目名称
     * $public_type  公开状态
     * $proMember  项目成员
     */
    public function actionEditPro()
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        $postdata = json_decode(file_get_contents("php://input"), true);
        if( empty($postdata['pro_id']) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $proInfo = \app\modules\project\delegate\ProjectDelegate::getProInfo($postdata['pro_id']);
        if(!isset($proInfo['u_id']) || ($proInfo['u_id'] != $this->userInfo['u_id'])){
            FResponse::output(['code' => 20003, 'msg' => "不是项目创建者，不能编辑项目", 'data'=>new Object()]);
        }
        //获取项目状态
        $proInfo['status'] = ProjectHelper::setProStatus($proInfo);
        if($proInfo['status']==4){
            FResponse::output(['code' => 20003, 'msg' => "该状态下不可编辑项目", 'data'=>new Object()]);
        }
        $pModel = ProjectModel::findOne($postdata['pro_id']);

        if(isset($postdata['begin_time']) || isset($postdata['end_time'])){
            if(!isset($postdata['begin_time'])){
                $postdata['begin_time'] = date('Y-m-d H:i',$proInfo['begin_time']);
            }
            if(!isset($postdata['end_time'])){
                $postdata['end_time'] = date('Y-m-d H:i',$proInfo['end_time']);
            }
            if($proInfo['status']==2 || $proInfo['status']==3){
                if(strtotime($postdata['begin_time'])!=$proInfo['begin_time'] || strtotime($postdata['end_time'])!=$proInfo['end_time']){
                    FResponse::output(['code' => 20003, 'msg' => "该状态下不可修改项目时间!", 'data'=>new Object()]);
                }
            }else{
                if(strtotime($postdata['begin_time'])>strtotime($postdata['end_time'])){
                    FResponse::output(['code' => 20003, 'msg' => "开始时间不能大于结束时间", 'data'=>new Object()]);
                }
                $pModel->begin_time = strtotime($postdata['begin_time']);
                $pModel->end_time = strtotime($postdata['end_time']);
            }
        }

        if(isset($postdata['public_type'])){
            if(!(in_array($postdata['public_type'],[1,2,3]))){
                FResponse::output(['code' => 20003, 'msg' => "公开状态错误", 'data'=>new Object()]);
            }
            $pModel->public_type = $postdata['public_type'];
        }

        $pModel->update_time = time();

        if(isset($postdata['pro_name'])){
            if(!(isset($postdata['pro_name']) && strlen($postdata['pro_name'])>0)){
                FResponse::output(['code' => 20003, 'msg' => "项目名称不能为空", 'data'=>new Object()]);
            }
            if(!\app\modules\project\delegate\ProjectDelegate::isStrlen($postdata['pro_name'],20)){
                FResponse::output(['code' => 20003, 'msg' => "项目名称最多20个字", 'data'=>new Object()]);
            }
            //判断项目是否已存在
            if(\app\modules\project\delegate\ProjectDelegate::is_project($postdata['pro_name'],$postdata['pro_id'])){
                FResponse::output(['code' => 20003, 'msg' => "该项目名称已存在", 'data'=>new Object()]);
            }
            $pModel->pro_name = $postdata['pro_name'];
        }else{
            $postdata['pro_name']=$pModel->pro_name;
        }

        if($pModel->save(false)){
            $oldMem = ProjectMemberModel::find()->where(['pro_id'=>$postdata['pro_id']])->asArray()->all();
            $is_member = true;
            if(isset($postdata['proMember'])){
                //处理项目成员数据
                $proMember = \app\modules\project\helper\ProjectHelper::setAddProMem($postdata['proMember'],$postdata['pro_id']);
                $tempProMember = [];
                foreach($proMember as $key=>$val){
                    $tempProMember[$key]['u_id'] = $val['u_id'];
                    $tempProMember[$key]['owner'] = $val['owner'];
                    $tempProMember[$key]['add_time'] = $val['add_time'];
                    $tempProMember[$key]['pro_id'] = $val['pro_id'];
                }
                $proMember = [];
                $proMember = $tempProMember;
                //添加项目成员
                if(count($postdata['proMember'])>0){
                    $is_member = \app\modules\project\delegate\ProjectDelegate::addProMem($proMember,$postdata['pro_id']);
                }
            }
            //添加项目日志
            $is_log = \app\modules\project\delegate\ProjectDelegate::addLog($this->userInfo['u_id'],'编辑了项目“'.$postdata['pro_name'].'”',$proInfo['pro_id']);

            //获取添加的新成员
            $addNewMem = \app\modules\project\delegate\ProjectDelegate::getProNewAddMem($proMember,$oldMem);
            //消息
            $is_addMsg = true;
            if(count($addNewMem)>0){
                $addNewMem = \app\modules\project\helper\ProjectHelper::setMsgMenu($addNewMem);
                $is_addMsg = \app\modules\project\delegate\ProjectDelegate::addProMsg($this->userInfo['u_id'],'邀请你加入了',$postdata['pro_id'],$postdata['pro_name'],$addNewMem);
            }
            //获取删除的成员
            $delMem = \app\modules\project\delegate\ProjectDelegate::getProDelMem($proMember,$oldMem);
            //消息
            $is_delMsg = true;
            if(count($delMem)>0){
                $delMem = \app\modules\project\helper\ProjectHelper::setMsgMenu($delMem,1);
                $is_delMsg = \app\modules\project\delegate\ProjectDelegate::addProMsg($this->userInfo['u_id'],'把你移出了项目',$postdata['pro_id'],$postdata['pro_name'],$delMem);
            }
            $tempProMem = \app\modules\project\helper\ProjectHelper::setMsgMenu($postdata['proMember']);
            $is_editMsg = \app\modules\project\delegate\ProjectDelegate::addProMsg($this->userInfo['u_id'],'编辑了项目',$postdata['pro_id'],$postdata['pro_name'],$tempProMem);

            if($is_member && $is_log && $is_addMsg && $is_delMsg && $is_editMsg){
                $transaction->commit();
                FResponse::output(['code' => 20000, 'msg' => "编辑项目成功", 'data'=>new Object()]);
            }else{
                $transaction->rollback();
                FResponse::output(['code' => 20003, 'msg' => "编辑项目失败!", 'data'=>new Object()]);
            }
        }else{
            $transaction->rollback();
            FResponse::output(['code' => 20003, 'msg' => "编辑项目失败!", 'data'=>new Object()]);
        }
    }

    /**
     * 删除项目
     * $pro_id  项目ID
     */
    public function actionDelPro()
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        $postdata = json_decode(file_get_contents("php://input"), true);
        if( empty($postdata['pro_id']) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $proInfo = \app\modules\project\delegate\ProjectDelegate::getProInfo($postdata['pro_id']);
        if(!isset($proInfo['u_id'])){
            FResponse::output(['code' => 20005, 'msg' => "该项目不存在或已删除", 'data'=>new Object()]);
        }
        if($proInfo['u_id'] != $this->userInfo['u_id']){
            FResponse::output(['code' => 20007, 'msg' => "没有权限！", 'data'=>new Object()]);
        }
        //获取项目任务
        $proTask = \app\modules\project\delegate\ProjectDelegate::getProTask($postdata['pro_id']);
        if(count($proTask)){
            FResponse::output(['code' => 20003, 'msg' => "项目中有任务无法删除！", 'data'=>new Object()]);
        }
        //判断项目是否已经开始
        if($proInfo['begin_time']<time()){
            FResponse::output(['code' => 20003, 'msg' => "只能对未开始的项目执行删除操作！", 'data'=>new Object()]);
        }
        $is_pro = \app\modules\project\delegate\ProjectDelegate::delPro($postdata['pro_id'],$this->userInfo['u_id']);
        if($is_pro){
            //消息
            $proMem = ProjectMemberModel::find()->where(['pro_id'=>$postdata['pro_id']])->asArray()->all();
            $proMem = \app\modules\project\helper\ProjectHelper::setMsgMenu($proMem,1);
            $is_editMsg = \app\modules\project\delegate\ProjectDelegate::addProMsg($this->userInfo['u_id'],'删除了项目',$postdata['pro_id'],$proInfo['pro_name'],$proMem);
            //删除项目成员
            $is_pro_mem = \app\modules\project\delegate\ProjectDelegate::delProMem($postdata['pro_id']);
            //添加项目日志
            $is_log = \app\modules\project\delegate\ProjectDelegate::addLog($this->userInfo['u_id'],'删除了项目：“'.$proInfo['pro_name'].'”',$proInfo['pro_id']);
            if($is_pro_mem && $is_log && $is_editMsg){
                $transaction->commit();
                FResponse::output(['code' => 20000, 'msg' => "删除项目成功！", 'data'=>new Object()]);
            }else{
                $transaction->rollback();
                FResponse::output(['code' => 20003, 'msg' => "删除项目失败！", 'data'=>new Object()]);
            }
        }else{
            FResponse::output(['code' => 20003, 'msg' => "删除项目失败！", 'data'=>new Object()]);
        }
    }

    /**
     * 创建项目
     * $begin_time  开始时间
     * $end_time  结束时间
     * $pro_name  项目名称
     * $public_type  公开状态
     * $proMember  项目成员
     */
    public function actionCreatePro()
    {
        $this->isPerm('ProjectCreate');
        try{
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            $postdata = json_decode(file_get_contents("php://input"), true);
            if(!(isset($postdata['pro_name']) && strlen($postdata['pro_name'])>0)){
                FResponse::output(['code' => 20001, 'msg' => "项目名称不能为空", 'data'=>new Object()]);
            }
            if(!\app\modules\project\delegate\ProjectDelegate::isStrlen($postdata['pro_name'],20)){
                FResponse::output(['code' => 20001, 'msg' => "项目名称最多20个字", 'data'=>new Object()]);
            }
            if(!(isset($postdata['public_type']) && in_array($postdata['public_type'],[1,2,3]))){
                FResponse::output(['code' => 20001, 'msg' => "公开状态错误", 'data'=>new Object()]);
            }
            if(!(isset($postdata['begin_time']) && isset($postdata['end_time']) && strtotime($postdata['begin_time'])<strtotime($postdata['end_time']))){
                FResponse::output(['code' => 20001, 'msg' => "开始时间不能大于或等于结束时间", 'data'=>new Object()]);
            }
            $pModel = new ProjectModel();
            $pModel->pro_name = $postdata['pro_name'];
            $pModel->public_type = $postdata['public_type'];
            $pModel->u_id = $this->userInfo['u_id'];
            $pModel->begin_time = strtotime($postdata['begin_time']);
            $pModel->end_time = strtotime($postdata['end_time']);
            $pModel->create_time = time();
            $pModel->update_time = time();
            //判断项目是否已存在
            if(\app\modules\project\delegate\ProjectDelegate::is_project($postdata['pro_name'])){
                FResponse::output(['code' => 20001, 'msg' => "该项目名称已存在", 'data'=>new Object()]);
            }
            if($pModel->save(false)){
                $pro_id = Yii::$app->db->getLastInsertID();
                //处理项目成员数据
                $proMember = \app\modules\project\helper\ProjectHelper::setAddProMem($postdata['proMember'],$pro_id);
                $tempProMember = [];
                foreach($proMember as $key=>$val){
                    $tempProMember[$key]['u_id'] = $val['u_id'];
                    $tempProMember[$key]['owner'] = $val['owner'];
                    $tempProMember[$key]['add_time'] = $val['add_time'];
                    $tempProMember[$key]['pro_id'] = $val['pro_id'];
                }
                $proMember = [];
                $proMember = $tempProMember;
                //添加项目成员
                $is_member = \app\modules\project\delegate\ProjectDelegate::addProMem($proMember,$pro_id);
                //添加项目日志
                $is_log = \app\modules\project\delegate\ProjectDelegate::addLog($this->userInfo['u_id'],'创建了项目“'.$postdata['pro_name'].'”',$pro_id);
                //添加消息
                $proMember = \app\modules\project\helper\ProjectHelper::setMsgMenu($proMember);
                $is_msg = \app\modules\project\delegate\ProjectDelegate::addProMsg($this->userInfo['u_id'],'邀请你加入了',$pro_id,$postdata['pro_name'],$proMember);
                if($is_member && $is_log && $is_msg){
                    $transaction->commit();
                    FResponse::output(['code' => 20000, 'msg' => "创建项目成功！", 'data'=>['pro_id'=>$pro_id]]);
                }else{
                    $transaction->rollBack();
                    FResponse::output(['code' => 20003, 'msg' => "创建项目失败！", 'data'=>new Object()]);
                }
            }else{
                $transaction->rollBack();
                FResponse::output(['code' => 20003, 'msg' => "创建项目失败！", 'data'=>new Object()]);
            }
        }catch (Exception $e){
            $transaction->rollBack();
            FResponse::output(['code' => 20003, 'msg' => "创建项目失败！", 'data'=>new Object()]);
        }
    }

    /**
     * 获取项目创建人信息
     */
    public function actionProCreateMemInfo()
    {
        $res = \app\modules\project\delegate\ProjectDelegate::getProCreateMemInfo($this->userInfo['u_id']);
        $res['head_img'] = substr($this->apiDomain, 0, -1).$res['head_img'];
        FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>$res]);
    }

    /**
     * 创建项目搜索项目成员
     * type 1按部门   2按姓名
     * search_name  按姓名搜索
     * org_id
    */
    public function actionSearchProMem()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if(isset($postdata['type'])){
            if($postdata['type']!=1 && $postdata['type']!=2){
                FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
            }
            if($postdata['type']==1){
                if(!isset($postdata['org_id'])){
                    $postdata['org_id'] = 2;
                }
            }
            if($postdata['type']==2){
                if(!isset($postdata['search_name'])){
                    $postdata['search_name'] = '';
                }
            }
        }else{
            $postdata['type']=2;
            if(!isset($postdata['search_name'])){
                $postdata['search_name'] = '';
            }
        }

        if($postdata['type']==1){//按部门
            //获取当前组信息
            $res = ProjectDelegate::getOrgInfo($postdata['org_id']);
            //获取子组信息
            $res['childOrg'] = ProjectDelegate::getChildOrgInfo($postdata['org_id']);
            //获取组成员信息
            $arrMem = ProjectDelegate::getOrgMem($postdata['org_id']);
            $res['Mem'] =ProjectDelegate::setHeadImg($arrMem,$this->apiDomain);
        }else{//按姓名
            $res = AttendanceDelegate::getMemberList($postdata['search_name']);
            $res = ProjectDelegate::setHeadImg($res,$this->apiDomain);
        }
        FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>$res]);

    }

    /**
     * 项目延期
     * $pro_id 项目ID
     * $delay_time  延期时间
     * $delay_reason  延期原因
     */
    public function actionDelayPro()
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        $postdata = json_decode(file_get_contents("php://input"), true);
        if( !isset($postdata['pro_id']) || empty($postdata['pro_id']) || !isset($postdata['delay_time']) || empty($postdata['delay_time']) || !isset($postdata['delay_reason']) || empty($postdata['delay_reason'])) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $proInfo = \app\modules\project\delegate\ProjectDelegate::getProInfo($postdata['pro_id']);
        if(!isset($proInfo['u_id']) || ($proInfo['u_id'] != $this->userInfo['u_id'])){
            FResponse::output(['code' => 20007, 'msg' => "没有权限！", 'data'=>new Object()]);
        }
        //判断项目延期是否超过50个字
        if(!\app\modules\project\delegate\ProjectDelegate::isStrlen($postdata['delay_reason'],50)){
            FResponse::output(['code' => 20011, 'msg' => "延期原因最多输入50个字！", 'data'=>new Object()]);
        }
        //获取项目状态
        $proInfo['status'] = ProjectHelper::setProStatus($proInfo);
        if($proInfo['status']==4 || $proInfo['status']==1){
            FResponse::output(['code' => 20011, 'msg' => "只能对进行中的项目延期！", 'data'=>new Object()]);
        }
        $postdata['delay_time'] = strtotime($postdata['delay_time']);
        if($proInfo['delay_time']==0 && $proInfo['end_time']>=$postdata['delay_time']){
            FResponse::output(['code' => 20011, 'msg' => "延期操作时间必须大于上一次项目设置的结束时间", 'data'=>new Object()]);
        }
        if($proInfo['delay_time']!=0 && $proInfo['delay_time']>=$postdata['delay_time']){
            FResponse::output(['code' => 20011, 'msg' => "延期操作时间必须大于上一次项目设置的结束时间", 'data'=>new Object()]);
        }
        if(!(isset($postdata['delay_reason']) && strlen($postdata['delay_reason'])>0)){
            FResponse::output(['code' => 20011, 'msg' => "延期原因不能为空！", 'data'=>new Object()]);
        }
        $res =\app\modules\project\delegate\ProjectDelegate::setProDelaytime($postdata['pro_id'], $this->userInfo['u_id'], $postdata['delay_time']);
        //添加项目日志
        $is_log =\app\modules\project\delegate\ProjectDelegate::addLog($this->userInfo['u_id'],'延期项目“'.$proInfo['pro_name'].'”到'.date('Y-m-d H:i:s',$postdata['delay_time'])."，延期原因：".$postdata['delay_reason'],$proInfo['pro_id']);
        //添加消息
        $proMem = ProjectMemberModel::find()->where(['pro_id'=>$postdata['pro_id']])->asArray()->all();
        $proMem = \app\modules\project\helper\ProjectHelper::setMsgMenu($proMem);
        $is_msg =\app\modules\project\delegate\ProjectDelegate::addProMsg($this->userInfo['u_id'],'延期了项目',$postdata['pro_id'],$proInfo['pro_name'],$proMem);

        if($res && $is_log && $is_msg){
            $transaction->commit();
            FResponse::output(['code' => 20000, 'msg' => "设置延期成功！", 'data'=>new Object()]);
        }else{
            $transaction->rollBack();
            FResponse::output(['code' => 20003, 'msg' => "设置延期失败，请重试！", 'data'=>new Object()]);
        }
    }

    /**
     * 项目归档
     * $pro_id  项目ID
     */
    public function actionCompletePro()
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        $postdata = json_decode(file_get_contents("php://input"), true);
        if( !isset($postdata['pro_id']) || empty($postdata['pro_id'])) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $proInfo = \app\modules\project\delegate\ProjectDelegate::getProInfo($postdata['pro_id']);
        if(!isset($proInfo['u_id']) || ($proInfo['u_id'] != $this->userInfo['u_id'])){
            FResponse::output(['code' => 20007, 'msg' => "没有权限！", 'data'=>new Object()]);
        }
        if($proInfo['complete']==1){
            FResponse::output(['code' => 20003, 'msg' => "该项目已归档！", 'data'=>new Object()]);
        }
        //获取项目状态
        $proInfo['status'] = ProjectHelper::setProStatus($proInfo);
        if($proInfo['status']!=2 && $proInfo['status']!=3){
            FResponse::output(['code' => 20003, 'msg' => "只能对进行中的项目设置为归档！", 'data'=>new Object()]);
        }
        //项目设置归档
        $is_pro_comp = \app\modules\project\delegate\ProjectDelegate::setProComp($postdata['pro_id'],$this->userInfo['u_id']);
        if($is_pro_comp){
            //将项目中的任务设置为已完成状态
            $is_pro_task_comp = \app\modules\project\delegate\ProjectDelegate::setProTaskComp($postdata['pro_id']);
            //添加项目日志
            $is_log = \app\modules\project\delegate\ProjectDelegate::addLog($this->userInfo['u_id'],'归档了项目“'.$proInfo['pro_name'].'”',$proInfo['pro_id']);
            //添加消息
            $proMem = ProjectMemberModel::find()->where(['pro_id'=>$postdata['pro_id']])->asArray()->all();
            $proMem = \app\modules\project\helper\ProjectHelper::setMsgMenu($proMem);
            $is_msg =\app\modules\project\delegate\ProjectDelegate::addProMsg($this->userInfo['u_id'],'归档了项目',$postdata['pro_id'],$proInfo['pro_name'],$proMem);
            if($is_pro_task_comp && $is_log && $is_msg){
                $transaction->commit();
                FResponse::output(['code' => 20000, 'msg' => "项目归档成功！", 'data'=>new Object()]);
            }else{
                $transaction->rollback();
                FResponse::output(['code' => 20003, 'msg' => "项目归档失败，请重试！", 'data'=>new Object()]);
            }
        }else{
            $transaction->rollback();
            FResponse::output(['code' => 20003, 'msg' => "项目归档失败，请重试！", 'data'=>new Object()]);
        }
    }

    /**
     * 操作日志
     * $pro_id 项目ID
     * $page 当前页
     */
    public function actionProLog()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if( !isset($postdata['pro_id']) || empty($postdata['pro_id']) || !isset($postdata['page']) || empty($postdata['page']) || !isset($postdata['pageSize']) || empty($postdata['pageSize'])) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        //判断项目是否存在
        $is_pro = ProjectDelegate::is_project($postdata['pro_id']);
        if($is_pro == false){
            FResponse::output(['code' => 20005, 'msg' => "数据不存在", 'data'=>new Object()]);
        }
        //判断是否有权限查看项目
        $proInfo = \app\modules\project\delegate\ProjectDelegate::is_permission($this->userInfo['u_id'],$postdata['pro_id']);
        if($proInfo == false){
            FResponse::output(['code' => 20001, 'msg' => "没有访问该项目的权限", 'data'=>new Object()]);
        }
        $page = $postdata['page'];
        $pageSize = $postdata['pageSize'];
        $offset = ($page-1)*$pageSize;
        $limit = $pageSize;
        $arrLog = ProjectDelegate::getLog($postdata['pro_id'], $limit, $offset);
        //格式化时间
        foreach($arrLog['proLog'] as $key=>$val){
            $arrLog['proLog'][$key]['create_time_f'] = ProjectHelper::setDateFormat($val['create_time']);
        }
        //处理项目成员头像
        $arrLog['proLog'] = ProjectDelegate::setHeadImg($arrLog['proLog'],$this->apiDomain);
        FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>$arrLog]);
    }

    /**
     * 获取我参与的项目
     */
    public function actionGetproinvo()
    {
        $data = ProjectDelegate::getInvoPro($this->userInfo['u_id']);
        FResponse::output(['code' => 20000, 'msg' => "success", 'data'=>$data]);
    }


}