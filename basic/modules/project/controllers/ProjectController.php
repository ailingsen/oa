<?php

namespace app\modules\project\controllers;

use app\controllers\BaseController;
use app\lib\Tools;
use app\models\ProjectMemberModel;
use app\models\ProjectModel;
use app\modules\project\delegate\ProjectDelegate;
use app\modules\project\helper\ProjectHelper;
use Yii;

/**
 * Default controller for the `project` module
 */
class ProjectController extends BaseController
{
    public $modelClass = 'app\models\ProjectModel';
    /**
     * 项目列表
     * $public 1我创建的项目   2我参与的项目   3公开项目
     * $page 当前页
    */
    public function actionGetpro()
    {
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata,true);
        //$type  1图表方式  2表格方式
        $type = isset($postdata['type']) ? $postdata['type'] : 1;
        $page = isset($postdata['page']) ? $postdata['page'] : 1;
        if(!in_array($postdata['public'],[1,2,3])){
            return ['code'=>-1,'data'=>'错误的请求'];
        }
        $public = $postdata['public'];
        //获取翻页参数
        $pageParam = ProjectHelper::setPage($type,$page);
        //获取项目
        $data = ProjectDelegate::getPro($public, $this->userInfo['u_id'], $pageParam['limit'], $pageParam['offset'], $postdata);
        //处理项目数据
        $data['proList'] = ProjectHelper::setProData($data['proList']);
        $data['page']['curPage'] = $page;
        return  ['code'=>1,'data'=>$data];
    }

    /**
     * 项目进度head
     * $pro_id  项目ID
    */
    public function actionProProgressHead()
    {
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata,true);
        //判断是否有权限查看项目
        $proInfo = ProjectDelegate::is_permission($this->userInfo['u_id'],$postdata['pro_id']);
        if($proInfo == false){
            return  ['code'=>-3,'msg'=>'没有权限'];
        }
        //获取项目任务
        $proTask = ProjectDelegate::getProTask($postdata['pro_id'],$type=1);
        //统计任务数据
        $taskInfo = ProjectHelper::setProDegree($proTask);
        return  ['code'=>1,'data'=>['taskInfo'=>$taskInfo,'proInfo'=>$proInfo]];
    }

    /**
     * 项目进度list
     * $pro_id  项目ID
     */
    public function actionProProgressList()
    {
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata,true);
        //判断是否有权限查看项目
        $proInfo = ProjectDelegate::is_permission($this->userInfo['u_id'],$postdata['pro_id']);
        if($proInfo == false){
            return  ['code'=>-3,'msg'=>'没有权限'];
        }
        //获取项目任务
        $proTask = ProjectDelegate::getProTask($postdata['pro_id']);
        //获取项目成员
        $proMember = ProjectDelegate::getProMem($postdata['pro_id']);
        //统计项目成员任务数据
        $proMember = ProjectHelper::getProMemTaskInfo($proMember,$proTask);
        return  ['code'=>1,'data'=>['proMember'=>$proMember,'proInfo'=>$proInfo]];
    }

    /**
     * 根据任务状态获取项目成员的对应任务信息
     * $pro_id 项目ID
     * $u_id  用户ID
     * $status 1总任务   2进行中   3已完成
     * $page  当前页
    */
    public function actionGetProMemStatusTask()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        //判断是否有权限查看项目
        $proInfo = ProjectDelegate::is_permission($this->userInfo['u_id'],$postdata['pro_id']);
        if($proInfo == false){
            return  ['code'=>-3,'msg'=>'没有权限'];
        }
        $page = isset($postdata['page']) ? $postdata['page'] : 0;
        //获取翻页参数
        $pageParam = ProjectHelper::setPage(3,$page);
        if(!in_array($postdata['status'],[1,2,3])){
            return ['code'=>-1,'data'=>'错误的请求'];
        }
        $list = ProjectDelegate::getProMemStatusTask( $postdata['u_id'], $postdata['pro_id'], $postdata['status'],$pageParam['limit'],$page,
            [
                'oa_task.task_id','oa_task.task_title','oa_task.task_type','oa_task.create_time','oa_members.u_id','oa_task.creater','oa_task.point','oa_members.real_name as release_name'
            ]);
        //处理任务数据
        $list['list'] = ProjectHelper::setTaskInfo($list['list']);
        return['code'=>1,'data'=>$list];
    }

    /**
     * 项目任务列表
     * $status  1全部  2已完成  3未完成的任务
     * $pro_id
     * $page
    */
    public function actionProTaskListInfo()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if(!in_array($postdata['status'],[1,2,3])){
            return ['code'=>-1,'data'=>'错误的请求'];
        }
        //判断是否有权限查看项目
        $proInfo = ProjectDelegate::is_permission($this->userInfo['u_id'],$postdata['pro_id']);
        if($proInfo == false){
            return  ['code'=>-3,'msg'=>'没有权限'];
        }
        $page = isset($postdata['page']) ? $postdata['page'] : 1;
        //获取翻页参数
        $pageParam = ProjectHelper::setPage(4,$page);
        $info = ProjectDelegate::getProTaskInfo($postdata['pro_id'],$postdata['status'],$pageParam['limit'], $pageParam['offset']);
        //处理任务数据
        $info['list'] = ProjectHelper::setTaskListInfo($info['list']);
        $info['page']['curPage'] = $page;
        $info['proInfo'] = $proInfo;
        return['code'=>1,'data'=>$info];
    }

    /**
     * 项目详情页
     * $pro_id  项目ID
    */
    public function actionProDetail()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        //判断是否有权限查看项目
        $proInfo = ProjectDelegate::is_permission($this->userInfo['u_id'],$postdata['pro_id']);
        if($proInfo == false){
            return  ['code'=>-3,'msg'=>'项目不存在或没有权限'];
        }
        //获取项目成员
        $proMem = ProjectDelegate::getProMem($postdata['pro_id']);
        //获取项目状态
        $proInfo['status'] = ProjectHelper::setProStatus($proInfo);
        //获取项目任务
        $proTask = ProjectDelegate::getProTask($postdata['pro_id'],$type=1);
        //获取项目任务完成比例
        $proInfo['degree'] = ProjectHelper::setProDegree($proTask);
        //时间格式化
        $proInfo = ProjectHelper::setTimeFormat($proInfo);
        //处理项目成员头像
        $proMem = ProjectHelper::setHeadImg($proMem);
        return  ['code'=>1,'data'=>['proInfo'=>$proInfo,'proMember'=>$proMem]];
    }

    /**
     * 项目进展
    */
    public function actionGantt()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        //判断是否有权限查看项目
        $proInfo = ProjectDelegate::is_permission($this->userInfo['u_id'],$postdata['pro_id']);
        if($proInfo == false){
            return  ['code'=>-3,'msg'=>'没有权限'];
        }
        //获取项目任务
        $proTask = ProjectDelegate::getProTaskUser($postdata['pro_id'],1);
        //处理甘特图要显示的数据
        $proTaskInfo = [];
        if(count($proTask)){
            $proTaskInfo = ProjectHelper::setGanttData($proTask);
        }
        return  ['code'=>1,'data'=>['proInfo'=>$proInfo,'proTask'=>$proTaskInfo]];
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
        try{
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            $postdata = json_decode(file_get_contents("php://input"), true);
            if(!(isset($postdata['pro_name']) && strlen($postdata['pro_name'])>0)){
                return  ['code'=>-1,'msg'=>'项目名称不能为空'];
            }
            if(!ProjectDelegate::isStrlen($postdata['pro_name'],20)){
                return  ['code'=>-1,'msg'=>'项目名称最多20个字'];
            }
            if(!(isset($postdata['public_type']) && in_array($postdata['public_type'],[1,2,3]))){
                return ['code'=>-1,'msg'=>'公开状态错误'];
            }
            if(!(isset($postdata['begin_time']) && isset($postdata['end_time']) && strtotime($postdata['begin_time'])<strtotime($postdata['end_time']))){
                return ['code'=>-1,'msg'=>'开始时间不能大于或等于结束时间'];
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
            if(ProjectDelegate::is_project($postdata['pro_name'])){
                return  ['code'=>-1,'msg'=>'该项目名称已存在'];
            }
            if($pModel->save(false)){
                $pro_id = Yii::$app->db->getLastInsertID();
                //处理项目成员数据
                $proMember = ProjectHelper::setAddProMem($postdata['proMember'],$pro_id);
                //添加项目成员
                $is_member = ProjectDelegate::addProMem($proMember,$pro_id);
                //添加项目日志
                $is_log = ProjectDelegate::addLog($this->userInfo['u_id'],'创建了项目“'.$postdata['pro_name'].'”',$pro_id);
                //添加消息
                $proMember = ProjectHelper::setMsgMenu($proMember);
                $is_msg = ProjectDelegate::addProMsg($this->userInfo['u_id'],'邀请你加入了',$pro_id,$postdata['pro_name'],$proMember);
                if($is_member && $is_log && $is_msg){
                    $transaction->commit();
                    return ['code'=>1,'msg'=>'创建项目成功！','pro_id'=>$pro_id];
                }else{
                    $transaction->rollBack();
                    return ['code'=>-1,'msg'=>'创建项目失败！'];
                }
            }else{
                $transaction->rollBack();
                return ['code'=>-1,'msg'=>'创建项目失败！'];
            }
        }catch (Exception $e){
            $transaction->rollBack();
            return ['code'=>-1,'msg'=>'创建项目失败！'];
        }
    }

    /**
     * 项目编辑获取项目信息
     * $pro_id  项目ID
     */
    public function actionProDetailSimp()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        //判断是否有权限查看项目
        $proInfo = ProjectDelegate::is_permission($this->userInfo['u_id'],$postdata['pro_id']);
        if($proInfo == false){
            return  ['code'=>-3,'msg'=>'没有权限'];
        }
        //获取项目成员
        $proMem = ProjectDelegate::getProMem($postdata['pro_id']);
        //设置头像
        $proMem = ProjectHelper::setHeadImg($proMem);
        //时间格式转换
        $proInfo = ProjectHelper::setTimeFormat($proInfo);
        //获取项目状态
        $proInfo['status'] = ProjectHelper::setProStatus($proInfo);
        return  ['code'=>1,'data'=>['proInfo'=>$proInfo,'proMember'=>$proMem]];
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
        $proInfo = ProjectDelegate::getProInfo($postdata['pro_id']);
        if(!isset($proInfo['u_id']) || ($proInfo['u_id'] != $this->userInfo['u_id'])){
            return ['code'=>-1,'msg'=>'没有权限！'];
        }
        //获取项目状态
        $proInfo['status'] = ProjectHelper::setProStatus($proInfo);
        if($proInfo['status']==4){
            return ['code'=>-1,'msg'=>'该状态下不可编辑项目！'];
        }
        $pModel = ProjectModel::findOne($postdata['pro_id']);
        if(!(isset($postdata['pro_name']) && strlen($postdata['pro_name'])>0)){
            return  ['code'=>-1,'msg'=>'项目名称不能为空'];
        }
        if(!ProjectDelegate::isStrlen($postdata['pro_name'],20)){
            return  ['code'=>-1,'msg'=>'项目名称最多20个字'];
        }
        if($proInfo['status']==2 || $proInfo['status']==3){
            if(strtotime($postdata['begin_time'])!=$proInfo['begin_time'] || strtotime($postdata['end_time'])!=$proInfo['end_time']){
                return ['code'=>-1,'msg'=>'该状态下不可修改项目时间！'];
            }
        }else{
            if(strtotime($postdata['begin_time'])>strtotime($postdata['end_time'])){
                return ['code'=>-1,'msg'=>'开始时间不能大于结束时间'];
            }
            $pModel->begin_time = strtotime($postdata['begin_time']);
            $pModel->end_time = strtotime($postdata['end_time']);
        }
        if(!(isset($postdata['public_type']) && in_array($postdata['public_type'],[1,2,3]))){
            return ['code'=>-1,'msg'=>'公开状态错误'];
        }

        $pModel->pro_name = $postdata['pro_name'];
        $pModel->public_type = $postdata['public_type'];
        $pModel->update_time = time();
        //判断项目是否已存在
        if(ProjectDelegate::is_project($postdata['pro_name'],$postdata['pro_id'])){
            return  ['code'=>-1,'msg'=>'该项目名称已存在'];
        }
        if($pModel->save(false)){
            $oldMem = ProjectMemberModel::find()->where(['pro_id'=>$postdata['pro_id']])->asArray()->all();
            //处理项目成员数据
            $proMember = ProjectHelper::setAddProMem($postdata['proMember'],$postdata['pro_id']);
            //添加项目成员
            $is_member = ProjectDelegate::addProMem($proMember,$postdata['pro_id']);
            //添加项目日志
            $is_log = ProjectDelegate::addLog($this->userInfo['u_id'],'编辑了项目“'.$postdata['pro_name'].'”',$proInfo['pro_id']);

            //获取添加的新成员
            $addNewMem = ProjectDelegate::getProNewAddMem($proMember,$oldMem);
            //消息
            $is_addMsg = true;
            if(count($addNewMem)>0){
                $addNewMem = ProjectHelper::setMsgMenu($addNewMem);
                $is_addMsg = ProjectDelegate::addProMsg($this->userInfo['u_id'],'邀请你加入了',$postdata['pro_id'],$postdata['pro_name'],$addNewMem);
            }
            //获取删除的成员
            $delMem = ProjectDelegate::getProDelMem($proMember,$oldMem);
            //消息
            $is_delMsg = true;
            if(count($delMem)>0){
                $delMem = ProjectHelper::setMsgMenu($delMem,1);
                $is_delMsg = ProjectDelegate::addProMsg($this->userInfo['u_id'],'把你移出了项目',$postdata['pro_id'],$postdata['pro_name'],$delMem);
            }
            $tempProMem = ProjectHelper::setMsgMenu($postdata['proMember']);
            $is_editMsg = ProjectDelegate::addProMsg($this->userInfo['u_id'],'编辑了项目',$postdata['pro_id'],$postdata['pro_name'],$tempProMem);

            if($is_member && $is_log && $is_addMsg && $is_delMsg && $is_editMsg){
                $transaction->commit();
                return ['code'=>1,'msg'=>'编辑项目成功！'];
            }else{
                $transaction->rollback();
                return ['code'=>-1,'msg'=>'编辑项目失败！'];
            }
        }else{
            $transaction->rollback();
            return ['code'=>-1,'msg'=>'编辑项目失败！'];
        }
    }

    /**
     * 搜索员工信息
    */
    public function actionAllgroupmemberdeepproject()
    {
    }

    /**
     * 获取工作日
     * $begin_time  开始时间
     * $end_time  结束时间
    */
    public function actionWorkday()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        if(strtotime($postdata['begin_time'])>strtotime($postdata['end_time'])){
            return ['code'=>-1,'count'=>0];
        }
        $count = ceil((strtotime($postdata['end_time'])-strtotime($postdata['begin_time']))/86400);
        return ['code'=>1,'count'=>$count];
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
        $proInfo = ProjectDelegate::getProInfo($postdata['pro_id']);
        if(!isset($proInfo['u_id']) || ($proInfo['u_id'] != $this->userInfo['u_id'])){
            return ['code'=>-1,'msg'=>'没有权限！'];
        }
        //获取项目任务
        $proTask = ProjectDelegate::getProTask($postdata['pro_id']);
        if(count($proTask)){
            return ['code'=>-1,'msg'=>'项目中有任务无法删除！'];
        }
        //判断项目是否已经开始
        if($proInfo['begin_time']<time()){
            return ['code'=>-1,'msg'=>'只能对未开始的项目执行删除操作！'];
        }
        $is_pro = ProjectDelegate::delPro($postdata['pro_id'],$this->userInfo['u_id']);
        if($is_pro){
            //消息
            $proMem = ProjectMemberModel::find()->where(['pro_id'=>$postdata['pro_id']])->asArray()->all();
            $proMem = ProjectHelper::setMsgMenu($proMem,1);
            $is_editMsg = ProjectDelegate::addProMsg($this->userInfo['u_id'],'删除了项目',$postdata['pro_id'],$proInfo['pro_name'],$proMem);
            //删除项目成员
            $is_pro_mem = ProjectDelegate::delProMem($postdata['pro_id']);
            //添加项目日志
            $is_log = ProjectDelegate::addLog($this->userInfo['u_id'],'删除了项目“'.$proInfo['pro_name'].'”',$proInfo['pro_id']);
            if($is_pro_mem && $is_log && $is_editMsg){
                $transaction->commit();
                return ['code'=>1,'msg'=>'删除项目成功！'];
            }else{
                $transaction->rollback();
                return ['code'=>-1,'msg'=>'删除项目失败！'];
            }
        }else{
            return ['code'=>-1,'msg'=>'删除项目失败！'];
        }
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
        $proInfo = ProjectDelegate::getProInfo($postdata['pro_id']);
        if(!isset($proInfo['u_id']) || ($proInfo['u_id'] != $this->userInfo['u_id'])){
            return ['code'=>-1,'msg'=>'没有权限！'];
        }
        //判断项目延期是否超过50个字
        if(!ProjectDelegate::isStrlen($postdata['delay_reason'],50)){
            return ['code'=>-1,'msg'=>'延期原因最多输入50个字！'];
        }
        //获取项目状态
        $proInfo['status'] = ProjectHelper::setProStatus($proInfo);
        if($proInfo['status']==4 || $proInfo['status']==1){
            return ['code'=>-1,'msg'=>'只能对进行中的项目延期！'];
        }
        $postdata['delay_time'] = strtotime($postdata['delay_time']);
        if($proInfo['delay_time']==0 && $proInfo['end_time']>=$postdata['delay_time']){
            return ['code'=>-1,'msg'=>'延期操作时间必须大于上一次项目设置的结束时间'];
        }
        if($proInfo['delay_time']!=0 && $proInfo['delay_time']>=$postdata['delay_time']){
            return ['code'=>-1,'msg'=>'延期操作时间必须大于上一次项目设置的结束时间'];
        }
        if(!(isset($postdata['delay_reason']) && strlen($postdata['delay_reason'])>0)){
            return ['code'=>-1,'msg'=>'延期原因不能为空！'];
        }
        $res = ProjectDelegate::setProDelaytime($postdata['pro_id'], $this->userInfo['u_id'], $postdata['delay_time']);
        //添加项目日志
        $is_log = ProjectDelegate::addLog($this->userInfo['u_id'],'延期项目“'.$proInfo['pro_name'].'”到'.date('Y-m-d H:i:s',$postdata['delay_time'])."，延期原因：".$postdata['delay_reason'],$proInfo['pro_id']);
        //添加消息
        $proMem = ProjectMemberModel::find()->where(['pro_id'=>$postdata['pro_id']])->asArray()->all();
        $proMem = ProjectHelper::setMsgMenu($proMem);
        $is_msg =ProjectDelegate::addProMsg($this->userInfo['u_id'],'延期了项目',$postdata['pro_id'],$proInfo['pro_name'],$proMem);

        if($res && $is_log && $is_msg){
            $transaction->commit();
            return ['code'=>1,'msg'=>'设置延期成功！','data'=>['delay_time'=>$postdata['delay_time'],'delay_time_f'=>date('Y-m-d H:i',$postdata['delay_time'])]];
        }else{
            $transaction->rollBack();
            return ['code'=>-1,'msg'=>'设置延期失败，请重试！'];
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
        $proInfo = ProjectDelegate::getProInfo($postdata['pro_id']);
        if(!isset($proInfo['u_id']) || ($proInfo['u_id'] != $this->userInfo['u_id'])){
            return ['code'=>-1,'msg'=>'没有权限！'];
        }
        if($proInfo['complete']==1){
            return ['code'=>-1,'msg'=>'该项目已归档！'];
        }
        //获取项目状态
        $proInfo['status'] = ProjectHelper::setProStatus($proInfo);
        if($proInfo['status']!=2 && $proInfo['status']!=3){
            return ['code'=>-1,'msg'=>'只能对进行中的项目设置为归档！'];
        }
        //项目设置归档
        $is_pro_comp = ProjectDelegate::setProComp($postdata['pro_id'],$this->userInfo['u_id']);
        if($is_pro_comp){
            //将项目中的任务设置为已完成状态
            $is_pro_task_comp = ProjectDelegate::setProTaskComp($postdata['pro_id']);
            //添加项目日志
            $is_log = ProjectDelegate::addLog($this->userInfo['u_id'],'归档了项目“'.$proInfo['pro_name'].'”',$proInfo['pro_id']);
            //添加消息
            $proMem = ProjectMemberModel::find()->where(['pro_id'=>$postdata['pro_id']])->asArray()->all();
            $proMem = ProjectHelper::setMsgMenu($proMem);
            $is_msg =ProjectDelegate::addProMsg($this->userInfo['u_id'],'归档了项目',$postdata['pro_id'],$proInfo['pro_name'],$proMem);
            if($is_pro_task_comp && $is_log && $is_msg){
                $transaction->commit();
                return ['code'=>1,'msg'=>'项目归档成功！'];
            }else{
                $transaction->rollback();
                return ['code'=>-1,'msg'=>'项目归档失败，请重试！'];
            }
        }else{
            $transaction->rollback();
            return ['code'=>-1,'msg'=>'项目归档失败，请重试！'];
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
        //判断是否有权限查看项目
        $proInfo = ProjectDelegate::is_permission($this->userInfo['u_id'],$postdata['pro_id']);
        if($proInfo == false){
            return  ['code'=>-3,'msg'=>'没有权限'];
        }
        $page = isset($postdata['page']) ? $postdata['page'] : 0;
        //获取翻页参数
        $pageParam = ProjectHelper::setPage(4,$page);
        $arrLog = ProjectDelegate::getLog($postdata['pro_id'], $pageParam['limit'], $page);
        $arrLog['proLog'] = ProjectHelper::setWeekStatus($arrLog['proLog']);
        //处理项目成员头像
        $arrLog['proLog'] = ProjectHelper::setHeadImg($arrLog['proLog']);
        return  ['code'=>1,'data'=>$arrLog];
    }

    /**
     * 判断是否有访问项目的权限
     * $pro_id
    */
    public function actionProPer()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        //判断是否有权限查看项目
        $proInfo = ProjectDelegate::is_permission($this->userInfo['u_id'],$postdata['pro_id']);
        if($proInfo == false){
            return  ['code'=>-3,'msg'=>'没有权限'];
        }else{
            return  ['code'=>1,'data'=>$proInfo];
        }
    }

    /**
     * 获取项目创建人信息
    */
    public function actionProCreateMemInfo()
    {
        $res = ProjectDelegate::getProCreateMemInfo($this->userInfo['u_id']);
        return  ['code'=>1,'data'=>$res];
    }

    /**
     * 项目成员工作报告
     * $pro_id,$date
    */
    public function actionProMemWorkReport()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        //判断是否有权限查看项目
        $proInfo = ProjectDelegate::is_permission($this->userInfo['u_id'],$postdata['pro_id']);
        if($proInfo == false){
            return  ['code'=>-3,'msg'=>'没有权限'];
        }
        if($postdata['date']==''){
            $postdata['date'] = date('Y-m-d',time());
        }else{
            $postdata['date'] = date('Y-m-d',strtotime($postdata['date']));
        }
        $page = isset($postdata['page']) ? $postdata['page'] : 1;
        //获取翻页参数
        $pageParam = ProjectHelper::setPage(4,$page);
        $list = ProjectDelegate::getProMemWorkReport($pageParam['limit'], $pageParam['offset'],$postdata);
        //处理用户头像
        $list['list'] = ProjectHelper::setHeadImg($list['list']);
        $list['page']['curPage'] = $page;
        $list['date']=$postdata['date'];
        return  ['code'=>1,'data'=>$list];
    }

    /**
     * 根据u_id查看项目成员日报/周报
    */
    public function actionProMemReport()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        //判断是否有权限查看项目
        $proInfo = ProjectDelegate::is_permission($this->userInfo['u_id'],$postdata['pro_id']);
        if($proInfo == false){
            return  ['code'=>-3,'msg'=>'没有权限'];
        }
        $proInfo = ProjectDelegate::is_permission($postdata['u_id'],$postdata['pro_id']);
        if($proInfo == false){
            return  ['code'=>-3,'msg'=>'没有权限'];
        }
        $res = ProjectDelegate::getProMemReport($postdata['u_id']);
        return  ['code'=>1,'data'=>$res];
    }

}
