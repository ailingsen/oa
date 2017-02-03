<?php

namespace app\modules\project\delegate;


//模型委托类。 处理控制器和动作列表

use app\lib\Tools;
use app\models\HolidayModel;
use app\models\MembersModel;
use app\models\OrgMemberModel;
use app\models\OrgModel;
use app\models\ProjectLogModel;
use app\models\ProjectMemberModel;
use app\models\ProjectModel;
use app\models\TaskModel;
use app\models\WorkStatementModel;
use app\modules\project\Project;
use Yii;
use yii\db\Query;

class ProjectDelegate {
    /**
     * 我创建的项目
     * $type  1我创建的项目  2我参与的项目  3公开项目
     * status 1未开始   2进行中   3已完成
    */
    public static function  getPro($type=3,$u_id,$limit,$offset,$data,$hasMyCreate=false)
    {
        $proModel = ProjectModel::find()->select('oa_project.*')->with('task');

        if($type==1){//我创建的项目
            $proModel->where('u_id=:u_id',[':u_id'=>$u_id]);
        }else if($type==2){//我参与的项目(不包括我创建的)
            $proModel->joinWith('projectmember');
            $proModel->where('oa_project_member.u_id=:u_id',[':u_id'=>$u_id]);
            if (!$hasMyCreate) {
                $proModel->andWhere('oa_project.u_id!=:u_id', [':u_id' => $u_id]);//不包括我创建的
            }
        }else{//公开项目或部门内公开(不包括我创建和参与的)
            //获取用户所在的组ID
            $orgMember = OrgMemberModel::getMemberOrgInfo($u_id);
            $proModel->leftJoin('oa_org_member','oa_org_member.u_id=oa_project.u_id');
            $proModel->leftJoin('oa_org','oa_org_member.org_id=oa_org.org_id');
            $proModel->where('public_type=1');//公开项目
            $orgInfo = OrgModel::find()->where('org_id=:org_id',[':org_id'=>$orgMember['org_id']])->asArray()->one();
            $arrChildOrg = explode(',',$orgInfo['all_children_id']);
            if(count($arrChildOrg)>1){
                $proModel->orWhere(['and','public_type=2',['like','oa_org.all_children_id',"%,".$orgMember['org_id'],false]]);//部门内公开
                $proModel->orWhere(['and','public_type=2',['like','oa_org.all_children_id',",".$orgMember['org_id'].","]]);//部门内公开
                $proModel->orWhere(['and','public_type=2',['like','oa_org.all_children_id',$orgMember['org_id'].",%",false]]);//部门内公开
            }else{
                $proModel->orWhere(['and','public_type=2',['like','oa_org.all_children_id',$orgMember['org_id']]]);//部门内公开
            }

            //不包括我创建的
            $proModel->andWhere('oa_project.u_id!=:u_id',[':u_id'=>$u_id]);
            //不包括我参与的
            $proModel->andWhere(['not in','oa_project.pro_id',(new Query())->select('oa_project.pro_id')->from('oa_project')->leftJoin('oa_project_member','oa_project_member.pro_id=oa_project.pro_id')->where(['oa_project_member.u_id' => $u_id])]);
        }

        $time=time();
        if(isset($data['status'])){
            if($data['status']==1){//未开始
                $proModel->andWhere(['>','oa_project.begin_time',$time]);
            }else if($data['status']==2){//进行中
                $proModel->andWhere(['<','oa_project.begin_time',$time]);
                $proModel->andWhere('oa_project.complete=0');
            }else if($data['status']==3){//已完成
                $proModel->andWhere('oa_project.complete=1');
            }
        }

        if(isset($data['begin_time']) && !empty($data['begin_time'])){
            $begin_time = strtotime($data['begin_time']);
            $proModel->andWhere(['>=','oa_project.begin_time',$begin_time]);
        }
        if(isset($data['end_time']) && !empty($data['end_time'])){
            $end_time = strtotime($data['end_time']);
            $proModel->andWhere(['<=','oa_project.end_time',$end_time]);
        }
        if(isset($data['pro_name']) && strlen($data['pro_name'])>0){
            $proModel->andWhere(['like','oa_project.pro_name',$data['pro_name']]);
        }

        $proModel->orderBy('oa_project.create_time desc');
        $res['proList'] = $proModel->offset($offset)->limit($limit)->asArray()->all();
        $res['page']['sumPage'] = ceil($proModel->count()/$limit);
        return $res;
    }

    //判断是否有权限查看项目
    public static function is_permission($u_id,$pro_id)
    {
        $info = self::getProInfo($pro_id);
        if(!(isset($info['pro_id']) && $info['pro_id']>0)){
            return false;
        }
        //判断项目是否为公开
        if($info['public_type'] == 1){
            return $info;
        }
        //项目创建者
        if($info['u_id'] == $u_id){
            return $info;
        }
        //判断是否为项目成员
        $member = ProjectMemberModel::find()->where('pro_id=:pro_id and u_id=:u_id',[':pro_id'=>$pro_id,':u_id'=>$u_id])->asArray()->one();
        if(isset($member['u_id']) && $member['u_id']>0){
            return $info;
        }
        if($info['public_type'] == 2) {
            //判断是否为创建者同属团队
            //获取当前用户所在组信息
            $curOrgInfo = OrgMemberModel::getMemberOrgInfo($u_id);
            //获取项目创建者所在的组的所有子组（包括自己）
            $proCreateOrg = OrgMemberModel::find()->select('oa_org.*')->leftJoin('oa_org', 'oa_org.org_id=oa_org_member.org_id')->where('oa_org_member.u_id=:u_id', [':u_id' => $info['u_id']])->asArray()->one();
            $arrOrgId = explode(',', $proCreateOrg['all_children_id']);
            if (in_array($curOrgInfo['org_id'], $arrOrgId)) {
                return $info;
            }
        }
        return false;
    }

    /**
     * 获取项目信息
    */
    public static function getProInfo($pro_id)
    {
        $res = ProjectModel::find()->where('pro_id=:pro_id',[':pro_id'=>$pro_id])->asArray()->one();
        return $res;
    }

    /**
     * 获取项目任务
    */
    public static function getProTask($pro_id,$type=0)
    {
        $tModel = TaskModel::find()->where('pro_id=:pro_id',[':pro_id'=>$pro_id]);
        if($type!=0){//不包括待发布的任务
            $tModel->andWhere('status!=:status',[':status'=>0]);
        }
        $res = $tModel->asArray()->all();
        return $res;
    }

    /**
     * 获取项目任务（不包括已完成和已关闭的）
     */
    public static function getProTaskUnfinish($pro_id)
    {
        $res = TaskModel::find()->where('pro_id=:pro_id and status!=4 and status!=5',[':pro_id'=>$pro_id])->asArray()->all();
        return $res;
    }

    /**
     * 根据状态获取项目任务
     * $status 1总任务   2已完成   3未完成
     */
    public static function getProTaskInfo($pro_id,$status=1,$limit,$offset)
    {
        $proM = TaskModel::find()->select('oa_task.*,oa_members.real_name')->leftJoin('oa_members','oa_task.creater=oa_members.u_id')->where('oa_task.pro_id=:pro_id and oa_task.status!=0',[':pro_id'=>$pro_id]);
        if($status == 2){
            $proM->andWhere('oa_task.status=:status',[':status'=>4]);
        }else if($status == 3){
            $proM->andWhere('oa_task.status!=:status',[':status'=>4]);
        }
        $res['list'] = $proM->offset($offset)->limit($limit)->asArray()->all();
        $count = $proM->count();
        $res['page']['sumPage'] = ceil($count/$limit);
        return $res;
    }

    /**
     * 获取项目任务(包括负责人详情)
     * type=0   判断是否获取待发布任务
     */
    public static function getProTaskUser($pro_id,$type=0)
    {
        $tModel = TaskModel::find()->select('oa_task.*,oa_members.real_name')->leftJoin('oa_members','oa_members.u_id=oa_task.charger')->where('oa_task.pro_id=:pro_id',[':pro_id'=>$pro_id]);
        if($type==1){
            $tModel->andWhere('oa_task.status!=0');
        }
        $res = $tModel->asArray()->all();
        return $res;
    }

    /**
     * 获取项目成员
     */
    public static function getProMem($pro_id)
    {
        $res = (new Query())->select('pm.*,m.real_name,m.head_img')->from('oa_project_member pm')
            ->leftJoin('oa_members m', 'm.u_id=pm.u_id')
            ->where('pm.pro_id=:pro_id',[':pro_id'=>$pro_id])
            ->all();
        return $res;
    }

    /**
     * 根据任务状态获取项目成员在改项目中的任务信息
     * 1总任务   2进行中   3已完成
    */
    public static function getProMemStatusTask($uid,$pro_id,$status=1,$limit,$offset,$field=[])
    {
        $tModel = TaskModel::find()->select($field)->where('charger=:u_id and pro_id=:pro_id',[':u_id'=>$uid,':pro_id'=>$pro_id])
                    ->leftJoin('oa_members','oa_members.u_id=oa_task.creater')->andWhere('oa_task.status!=0 and oa_task.status!=1 and oa_task.status!=6');
        if($status==2){//进行中
            $tModel->andWhere('oa_task.status=2');
        }
        if($status==3){//已完成
            $tModel->andWhere('oa_task.status=4');
        }
        //$res['list'] = $tModel->offset($offset)->limit($limit)->asArray()->all();
        $res['list'] = $tModel->asArray()->all();
        $res['count'] = $tModel->count();
        return $res;
    }

    /**
     * 删除项目
    */
    public static function delPro($pro_id,$u_id)
    {
        $res =Yii::$app->db->createCommand()->delete('oa_project','pro_id=:pro_id and u_id=:u_id and begin_time>:time',[':pro_id'=>$pro_id,':u_id'=>$u_id,':time'=>time()])->execute();
        return $res;
    }

    /**
     * 删除项目成员
     */
    public static function delProMem($pro_id)
    {
        $res =Yii::$app->db->createCommand()->delete('oa_project_member','pro_id=:pro_id',[':pro_id'=>$pro_id])->execute();
        return $res;
    }

    /**
     * 项目归档
    */
    public static function setProComp($pro_id,$u_id)
    {
        $data = ['complete'=>1,'update_time'=>time()];
        $res = Yii::$app->db->createCommand()->update('oa_project',$data,'pro_id=:pro_id and u_id=:u_id and complete=0 and begin_time<:time',[':pro_id'=>$pro_id,':u_id'=>$u_id,':time'=>time()])->execute();
        return $res;
    }

    /**
     * 将项目中的任务设置为已完成状态（除已完成和已关闭的）
    */
    public static function setProTaskComp($pro_id)
    {
        $taskInfo = self::getProTaskUnfinish($pro_id);
        if(count($taskInfo) > 0){
            $data = ['status'=>4];
            $res = Yii::$app->db->createCommand()->update('oa_task',$data,'pro_id=:pro_id and status!=:status1 and status!=:status2',[':pro_id'=>$pro_id,':status2'=>4,':status1'=>5])->execute();
            return $res;
        }else{
            return true;
        }
    }

    /**
     * 设置项目延期
    */
    public static function setProDelaytime($pro_id,$u_id,$delay_time)
    {
        $data = ['delay_time'=>$delay_time,'update_time'=>time()];
        $res = Yii::$app->db->createCommand()->update('oa_project',$data,'pro_id=:pro_id and u_id=:u_id and complete=0',[':pro_id'=>$pro_id,':u_id'=>$u_id])->execute();
        return $res;
    }

    /**
     * 添加项目日志
    */
    public static function addLog($u_id,$content,$pro_id)
    {
        $plModel = new ProjectLogModel();
        $plModel->u_id = $u_id;
        $plModel->create_time = time();
        $plModel->content = $content;
        $plModel->pro_id = $pro_id;
        $res = $plModel->save(false);
        return $res;
    }

    /**
     * 获取操作日志
     */
    public static function getLog($pro_id,$limit,$offset)
    {
        $res =['log'=>[],'count'=>0];
        $M = ProjectLogModel::find()->select('oa_project_log.*,oa_members.real_name,oa_members.head_img')
            ->leftJoin('oa_members','oa_members.u_id=oa_project_log.u_id')
            ->where('pro_id=:pro_id',[':pro_id'=>$pro_id]);
        //$res['proLog'] = $M->orderBy('create_time DESC')->offset($offset)->limit($limit)->asArray()->all();
        $res['proLog'] = $M->orderBy('create_time DESC')->asArray()->all();
        $res['count'] = $M->count();
        return $res;
    }

    /**
     * 添加项目成员
    */
    public static function addProMem($arrMem,$pro_id)
    {
        ProjectMemberModel::deleteAll('pro_id=:pro_id',array(':pro_id'=>$pro_id));
        $res = Yii::$app->db->createCommand()->batchInsert('oa_project_member',['u_id','owner','add_time','pro_id'],$arrMem)->execute();
        return $res;
    }

    /**
     * 判断项目是否存在
    */
    public static function is_project($pro_name,$pro_id=0)
    {
        $M = ProjectModel::find()->where('pro_name=:pro_name',[':pro_name'=>$pro_name]);
        if($pro_id>0){
            $M->andWhere('pro_id!=:pro_id',[':pro_id'=>$pro_id]);
        }
        $info = $M->asArray()->one();
        if($info['pro_id']){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 统计工作日
    */
    public static function getWorkday($begin_time,$end_time)
    {
        $res = HolidayModel::find()->where('iswork=:iswork and day>=:begin_time and day<=:end_time',[':iswork'=>1,':begin_time'=>$begin_time,':end_time'=>$end_time])->count();
        return $res;
    }

    /**
     * 获取项目创建人信息
    */
    public static function getProCreateMemInfo($u_id){
        $res = MembersModel::find()->select('u_id,real_name,head_img')->where('u_id=:u_id',[':u_id'=>$u_id])->asArray()->one();
        $res['head_img'] = Tools::getHeadImg($res['head_img']);
        return $res;
    }

    /**
     * 获取项目成员工作报告(日报)
    */
    public static function getProMemWorkReport($limit,$offset,$data)
    {
        //获取项目成员
        $pmModel =  ProjectMemberModel::find()->select('oa_project_member.u_id,oa_members.real_name,oa_members.head_img,oa_members.position')->leftJoin('oa_members','oa_members.u_id=oa_project_member.u_id')->where('oa_project_member.pro_id=:pro_id',[':pro_id'=>$data['pro_id']]);
        $res['list'] = $pmModel->offset($offset)->limit($limit)->asArray()->all();
        $res['page']['sumPage'] = ceil($pmModel->count()/$limit);
        foreach($res['list'] as $key=>$val){
            $wsModel = WorkStatementModel::find()->select('oa_work_statement.work_id,oa_work_statement.work_content,oa_work_statement.plan_content')
                ->where('oa_work_statement.u_id='.$val['u_id'])
                ->andWhere('oa_work_statement.type=1');
            $wsModel->andWhere("FROM_UNIXTIME( oa_work_statement.create_time, '%Y-%m-%d')=:date",[':date'=>$data['date']]);
            $temp = $wsModel->asArray()->one();
            $res['list'][$key]['work_content'] = $temp['work_content'];
            $res['list'][$key]['plan_content'] = $temp['plan_content'];
        }
        return $res;
    }

    /**
     * 获取项目成员日报/周报
    */
    public static function getProMemReport($uid)
    {
        $query = WorkStatementModel::find()
            ->select('oa_work_statement.*, oa_members.real_name')
            ->leftJoin('oa_members', 'oa_work_statement.u_id=oa_members.u_id')
            ->where('oa_work_statement.u_id=:uid', [':uid' => $uid]);
        $query->andWhere( 'oa_work_statement.status!=0');

        $workList = $query->orderBy(['oa_work_statement.create_time' => SORT_DESC])->asArray()->all();

/*        $workList = $query->limit($pageSize)
            ->offset(($page - 1) * $pageSize)
//           ->orderBy(['cycle' => SORT_DESC], ['oa_work_statement.u_id' => SORT_ASC])
            ->orderBy(['oa_work_statement.create_time' => SORT_DESC])
            ->asArray()
            ->all();
        $totalCount = $query->count();
        $totalPage = ceil($totalCount / $pageSize);
        $pageData = ['list' => $workList, 'total_page' => $totalPage, 'page' => $page];*/
        $pageData = ['list' => $workList];
        return $pageData;
    }

    /**
     * 判断文字长度
     * str
     * length
    */
    public static function isStrlen($str,$length)
    {
        //$tmp = @iconv('gbk', 'utf-8', $str);
        $str = trim($str);
        $tmp = $str;
        if(!empty($tmp)){
            $str = $tmp;
        }
        preg_match_all('/./us', $str, $match);
        if(count($match[0])>$length){
            return false;
        }else{
            return true;
        }
    }

    public static function trimall($str)//删除空格
    {
        $qian=array(" ","　","\t","\n","\r");
        $hou=array("","","","","");
        return str_replace($qian,$hou,$str);
    }

    /**
     * 获取项目新成员
    */
    public static function getProNewAddMem($arrMem,$oldMem)
    {
        $res = [];
        foreach($arrMem as $key=>$val){
            $tempOldMem = array_column($oldMem,'u_id');
            if(!in_array($val['u_id'],$tempOldMem)){
                $res[$key]['u_id'] = $val['u_id'];
                $res[$key]['owner'] = $val['owner'];
            }
        }
        return $res;
    }

    /**
     * 获取项目删除的成员
     */
    public static function getProDelMem($arrMem,$oldMem)
    {
        $res = [];
        foreach($oldMem as $key=>$val){
            $tempOldMem = array_column($arrMem,'u_id');
            if(!in_array($val['u_id'],$tempOldMem)){
                $res[$key]['u_id'] = $val['u_id'];
                $res[$key]['owner'] = $val['owner'];
            }
        }
        return $res;
    }

    /**
     * 添加日志消息
     */
    public static function addProMsg($u_id,$title,$pro_id,$pro_name,$data)
    {
        $memInfo = MembersModel::findOne($u_id);
        $temp = [];
        $res = false;
        if(count($data)>0){
            foreach($data as $key=>$val){
                $temp[$key]['u_id'] = $val['u_id'];
                $temp[$key]['operator'] = $u_id;
                $temp[$key]['project_id'] = $pro_id;
                $temp[$key]['title'] = $title;
                $temp[$key]['project_name'] = $pro_name;
                $temp[$key]['create_time'] = time();
                $temp[$key]['menu'] = $val['menu'];
            }
            //极光推送
            $jPushMem = array_column($temp,'u_id');
            //获取项目信息
            $info = ProjectModel::find()->select('complete')->where('pro_id=:pro_id',[':pro_id'=>$pro_id])->asArray()->one();
            Tools::msgJpush(2,$pro_id,$memInfo->real_name.$title.$pro_name,$jPushMem,$info);
            $res = Yii::$app->db->createCommand()->batchInsert('oa_project_msg',['uid','operator','project_id','title','project_name','create_time','menu'],$temp)->execute();
        }
        return $res;
    }

}