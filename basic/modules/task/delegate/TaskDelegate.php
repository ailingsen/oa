<?php
/**
 * Created by PhpStorm.
 * User: liaoshuochao
 * Date: 2016/7/15
 * Time: 10:30
 */
namespace app\modules\task\delegate;

use app\lib\FResponse;
use app\lib\Tools;
use app\models\MembersModel;
use app\models\OrgMemberModel;
use app\models\OrgModel;
use app\models\ProjectLogModel;
use app\models\ProjectMemberModel;
use app\models\ProjectModel;
use app\models\RewardTaskModel;
use app\models\ScoreLogModel;
use app\models\TaskAttachmentModel;
use app\models\TaskLogModel;
use app\models\TaskModel;
use app\models\TaskRangeModel;
use app\models\TaskSkillModel;
//use app\models\TaskMsgModel;
use app\modules\task\helper\TaskHelper;
use app\models\TaskMsgModel;
use yii;

Class TaskDelegate{

    /**
     * 插入任务技能
     * @param $skills
     * @param $taskId
     * @param int $taskType
     */
    public static function insertSkill($skills, $taskId, $taskType = 1)
    {
        foreach ($skills as $key => $val) {
            $data = array(
                'task_id' => $taskId,
                'skill_id' => $val['skill_id'],
                'task_type' => $taskType
            );
            Yii::$app->db->createCommand()->insert('oa_task_skill', $data)->execute();
        }
    }

    /**
     * 插入悬赏范围
     * @param $groups
     * @param $taskId
     * @return bool
     */
    public static function insertOfferGroup($groups, $taskId)
    {
        $rs = true;
        foreach ($groups as $group) {
            if (!empty($group)) {
                $data = array(
                    'task_id' => $taskId,
                    'org_id' => $group['org_id'],
                    'org_name' => $group['org_name']
                );
                $rs = $rs && TaskRangeModel::createX($data);
            }
        }
        return $rs;
    }

    /**
     * 更新用户积分
     * @param $memberId
     * @param $points
     * @param $taskTitle
     * @return bool
     */
    public static function updateMemberPointDesc($memberId, $points, $taskTitle)
    {
        return MembersModel::updateMemberInfo($memberId, ['leave_points' => -$points], '任务创建：' . $taskTitle);
    }

    /**
     * 创建任务
     * @param $data
     * @return int
     */
    public static function createTask($data, $userInfo)
    {
        //判断标题是否存在
        TaskHelper::isOfferExist($data['title']);
        //判断类型，1为指派任务
        if ($data['type'] == 1) {
            //插入主任务
            return self::insertTask($data, $userInfo);
        }
        //悬赏任务
        if ($data['type'] == 2) {
            return self::insertRewardTask($data, $userInfo);
        }
    }

    /**
     * 插入任务
     * @param $data
     * @param $userInfo
     * @return bool|int|string
     * @throws yii\db\Exception
     */
    public static function insertTask($data, $userInfo)
    {
        $transRs = true;
        try {
            $transaction = Yii::$app->db->beginTransaction();//事务开始
            if ($taskId = self::createMainTask($data, $userInfo)) {
                if (isset($data['pro_id']) && $data['pro_id'] > 0) {
//                    ProjectLogModel::addLog($userInfo, $userInfo['real_name'] . "创建了任务'" . $data['title'] . "'", $data['pro_id']);
                } else {
                    $transaction->rollback();
                    FResponse::output(['code' => 20066, 'msg' => "指派任务必须选择项目"]);
                }
//                if (isset($data['charger']['allow_task_email']) && $data['charger']['allow_task_email'] == 1) {
//                    $url = urlencode($_SERVER['HTTP_HOST'] . '#/task/myTask/1/mytaskDetail/' . $taskId . '/1');
//                    Tools::asynSendMail($data['charger']['email'], $userInfo['real_name'] . '指派了您一条新任务 ' . $data['title'] . $url, $data['charger']['real_name'], $userInfo['real_name']);
//                }

                //插入技能
                if (isset($data['skills'])) {
                    self::insertSkill($data['skills'], $taskId, 1);
                }
                //修改个人积分
                if (isset($data['point']) && $data['point'] > 0) {
                    $transRs = $transRs && self::updateMemberPointDesc($userInfo['u_id'], $data['point'], $data['title']);
                }

                //判断当前登陆人是否是部门负责人
                if ($userInfo['org']['is_manager'] == 1) {
                    //扣除
                    $transRs = OrgModel::updateOrgInfo($userInfo['org']['org_id'], ['org_points' => -$data['point']]);
                }
                //修改附件表
                if (isset($data['attr']) && is_array($data['attr'])) {
                    foreach ($data['attr'] as $at) {
                        if(!$at)continue;
                        self::updateAttr($at, $taskId, 1);
                    }
                }
            }

            if ($transRs) {
                if($data['is_publish'] == 1){
                    self::taskMsg($data['charger']['u_id'],$userInfo['u_id'],$taskId,'给你指派了任务',$data['title'],1,1);
                    Tools::msgJpush(3,$taskId,$userInfo['real_name'].'给你指派了任务'.$data['title'],[$data['charger']['u_id']],['taskType'=>1]);
                }
                $transaction->commit();//事物结束
                //self::taskMsg($data['charger']['u_id'],$userInfo['u_id'],$taskId,'给您指派了任务',$data['title'],1);
                return $taskId;
            } else {
                $transaction->rollback();//回滚函数
                return false;
            }
        } catch (Exception $e) {
            $transaction->rollback();//回滚函数
            $rs['info'] = $e->getMessage();//异常信息
            FResponse::output(['code' => 20066, 'msg' => "指派任务新增失败" . $rs['info']]);
        }
    }

    /**
     * 插入指定任务
     * @param $data
     * @return int|string
     * @throws yii\db\Exception
     */
    public static function createMainTask($data, $userInfo)
    {
        //格式化数据
        $insertData = array(
            'task_title' => $data['title'],
            'task_level' => $data['task_level'],
            'task_type' => $data['type'],
            'point' => $data['point'],
            'creater' => $userInfo['u_id'],
            'status' => $data['is_publish'],
            'is_publish' => $data['is_publish'],
            'create_time' => time(),
            'update_time' => time()
        );
        $insertData['pro_id'] = isset($data['pro_id']) ? $data['pro_id'] : 0;
        if(isset($data['desc'])) $insertData['task_desc'] = $data['desc'];
        if (isset($data['startTime'])) {
            $insertData['begin_time'] = strtotime($data['startTime']);
        }
        if (isset($data['endTime'])) {
            $insertData['end_time'] = strtotime($data['endTime']);
        }
        if (isset($data['charger'])) {
            $insertData['charger'] = $data['charger']['u_id'];
        }
        if(!TaskModel::createX($insertData)){
            FResponse::output(['code' => 20064, 'msg' => "创建任务失败"]);
        }
        $taskId = Yii::$app->db->getLastInsertID();
        //获取用户信息
        TaskLogModel::insertTaskLog($userInfo,  "创建了任务 ".$data['title'], $taskId);
        if($data['is_publish']==1){
            TaskLogModel::insertTaskLog($userInfo, "发布了任务 ".$data['title'], $taskId);
        }
        return $taskId;
    }

    /**
     * 插入悬赏任务
     * @param $data
     * @param $userInfo
     * @return int|string
     * @throws yii\db\Exception
     */
    public static function insertRewardTask($data, $userInfo)
    {
        if (!isset($data['group']) || empty($data['group'])) {
            FResponse::output(['code' => 20003, 'msg' => "Params 'group' Error"]);
        }
        try {
            $rs = true;
            $transaction = Yii::$app->db->beginTransaction();//事务开始
            //插入数据库
            $rs = self::insertRewardTable($data, $userInfo['u_id']);
            $taskId = Yii::$app->db->getLastInsertID();

            if ($taskId) {
                //插入任务日志
                TaskLogModel::insertTaskLog($userInfo, "创建了".$data['title'], $taskId, 2);
                if($data['is_publish'] == 1){
                    TaskLogModel::insertTaskLog($userInfo, "发布了".$data['title'], $taskId, 2);
                }
                //插入技能
                self::insertSkill($data['skills'], $taskId, 2);
                //插入悬赏范围
                self::insertOfferGroup($data['group'], $taskId);
                if (isset($data['point']) && $data['point'] > 0) {
                    //修改个人积分
                    $rs = $rs && self::updateMemberPointDesc($userInfo['u_id'], $data['point'], $data['title']);

                    //判断当前登陆人是否是部门负责人
                    if ($userInfo['org']['is_manager'] == 1) {
                        //扣除
                        $rs = OrgModel::updateOrgInfo($userInfo['org']['org_id'], ['org_points' => -$data['point']]);
                    }
                }

                //修改附件表
                if (isset($data['attr']) && is_array($data['attr'])) {
                    foreach ($data['attr'] as $at) {
                        if(!$at)continue;
                        self::updateAttr($at, $taskId, 1, 2);
                    }
                }
                if ($rs) {
                    $transaction->commit();//事物结束
                } else {
                    $taskId = 0;
                    $transaction->rollback();//回滚函数
                }
            }
        } catch (Exception $e) {
            $transaction->rollback();//回滚函数
            $rs['info'] = $e->getMessage();//异常信息
            FResponse::output(['code' => 20063, 'msg' => "创建任务失败" . $rs['info']]);
        }
        return $taskId;
    }

    /**
     * 插入悬赏任务表
     * @param $data
     * @param $uid
     * @return bool
     */
    public static function insertRewardTable($data, $uid)
    {
        //格式化数据
        $insertData = [
            'task_title' => $data['title'],
            'begin_time' => strtotime($data['startTime']),
            'end_time' => strtotime($data['endTime']),
            'task_level' => $data['task_level'],
            'point' => $data['point'],
            'creater' => $uid,
            'status' => $data['is_publish'],
            'is_publish' => $data['is_publish'],
            'create_time' => time(),
            'update_time' => time()
        ];
        if(isset($data['desc'])) $insertData['task_desc'] = $data['desc'];

        return RewardTaskModel::createX($insertData);
    }

    /**
     * 修改附件表里面的附件信息
     * @param $attId
     * @param $taskId
     * @param $type
     * @param int $taskType
     * @return int
     * @throws yii\db\Exception
     */
    public static function updateAttr($attId, $taskId, $type, $taskType = 1)
    {
        $data = [
            'task_id' => $taskId,
            'type' => $type,
            'task_type' => $taskType
        ];
        return Yii::$app->db->createCommand()->update('oa_task_attachment', $data, 'task_att_id=:att_id', [':att_id' => $attId])->execute();
    }

    /**
     * 更新任务
     * @param $userInfo
     * @param $postData
     * @return bool
     */
    public static function updateTask($userInfo, $postData)
    {
        if ($postData['type'] == 1) {
            $taskBase = TaskModel::findOne(['task_id' => $postData['task_id']]);
        } else {
            $taskBase = RewardTaskModel::findOne(['task_id' => $postData['task_id']]);
        }
        if ($taskBase->creater != $userInfo['u_id']) {
            FResponse::output(['code' => 20075, 'msg' => "只能修改自己创建的任务"]);
        }

        //只能修改待审核和进行中的任务
        if ($taskBase->status > 2 && $taskBase->status != 6) {
            FResponse::output(['code' => 20075, 'msg' => "只能修改待发布、待接受、进行中和已拒绝的任务"]);
        }

        $logInfo = '编辑了任务 ' . $taskBase->task_title;
        $rs = false;

        if ($taskBase->status == 6) {
            $taskBase->status = 1;
            $rs = true;
        }
        if (isset($postData['task_title']) && $taskBase->task_title != $postData['task_title']) {
//            $logInfo .= "修改了任务标题(由" . $taskBase->task_title . "到" . $postData['task_title'] . '),';
            $taskBase->task_title = $postData['task_title'];
            $rs = true;
        }

        $insProMemRes = true;

        if (isset($postData['charger']) && $postData['type'] == 1 && $taskBase->charger != $postData['charger']['u_id']) {
            //项目内发布指定任务若指定人不在该项目内则添加项目成员关系
            /*if (!empty($postData['pro_id']) && isset($postData['charger']) && !empty($postData['charger']['u_id'])) {
                $proMember = ProjectMemberModel::getProMemberByProAndMember($postData['charger']['u_id'], $postData['pro_id']);
                if (empty($proMember)) {
                    //更新项目成员信息
                    $insProMemRes = ProjectMemberModel::insertProMember($postData['charger']['u_id'], $postData['pro_id']);
                }
            }*/

            $chargerInfo = MembersModel::findOne($taskBase->charger);
//            $logInfo .= "重新指派了任务(由" . $chargerInfo['real_name'] . "到" . $postData['charger']['real_name'] . '),';
            $taskBase->charger = $postData['charger']['u_id'];
            $taskBase->status = 1;
            $rs = true;
        }

        if ($taskBase->status != 2 && isset($postData['begin_time'])) {
            $postData['begin_time'] = strtotime($postData['begin_time']);
            if ($taskBase->begin_time != $postData['begin_time']) {
//                $logInfo .= "修改了任务开始时间(由" . date("Y-m-d h:i", $taskBase->begin_time) . "到" . date("Y-m-d h:i", $postData['begin_time']) . '),';
                $taskBase->begin_time = $postData['begin_time'];
                $rs = true;
            }
        }

        if ($taskBase->status != 2 && isset($postData['end_time'])) {
            $postData['end_time'] = strtotime($postData['end_time']);
            if ($taskBase->end_time != $postData['end_time']) {
//                $logInfo .= "修改了任务结束时间(由" . date("Y-m-d h:i", $taskBase->end_time) . "到" . date("Y-m-d h:i", $postData['end_time']) . '),';
                $taskBase->end_time = $postData['end_time'];
                $rs = true;
            }
        }
        if (isset($postData['task_desc']) && $taskBase->task_desc != $postData['task_desc']) {
//            $logInfo .= "修改了任务内容(由" . $taskBase->task_desc . "到" . $postData['task_desc'] . '),';
            $taskBase->task_desc = $postData['task_desc'];
            $rs = true;
        }
        if (isset($postData['task_level']) && $taskBase->task_level != $postData['task_level']) {
//            $logInfo .= "修改了任务重要性(由" . $taskBase->task_level . "到" . $postData['task_level'] . '),';
            $taskBase->task_level = $postData['task_level'];
            $rs = true;
        }
        if (isset($postData['skills'])) {
            TaskSkillModel::deleteAll(['task_id' => $taskBase->task_id]);
            if(count($postData['skills'])>0){
                self::insertSkill($postData['skills'], $taskBase->task_id, $postData['type']);
            }
            $rs = true;
        }
        if (isset($postData['pro_id']) && $postData['type'] == 1 && $taskBase->pro_id != $postData['pro_id']) {
//            $logInfo .= "项目(由" . $taskBase->task_desc . "到" . $postData['task_desc'] . '),';
            $taskBase->pro_id = $postData['pro_id'];
            $rs = true;
        }
        if (isset($postData['point']) && $postData['point'] != $taskBase->point) {
//            $logInfo .= "修改了任务积分(由" . $taskBase->point . "到" . $postData['point'] . '),';
            //返回任务积分
            $point = $taskBase->point - $postData['point'];
            $taskBase->point = $postData['point'];
            //(任务编辑和产品沟通后取消积分返还，只扣除积分)
            $member = MembersModel::findOne($taskBase->creater);
            $member->leave_points = $member->leave_points + $point;
            if ($member->leave_points < 0) {
                FResponse::output(['code' => 20076, 'msg' => "用户可分配纳米币不足"]);
            }
            //判断当前登陆人是否是部门负责人
            if ($userInfo['org']['is_manager'] == 1) {
                //扣除组积分
                $orgInfo = OrgMemberModel::getMemberOrgInfo($userInfo['u_id']);
                $orgM = OrgModel::findOne($orgInfo['org_id']);
                $orgM ->org_points = $orgM ->org_points + $point;
                $orgM->save(false);
            }
            $scoreLogInfo = ['u_id' => $taskBase->creater,
                'type' => 2,
                'content' => '编辑任务-修改积分',
                'score' => $point,
                'operator' => $taskBase->creater,
                'score_before' => $member->oldAttributes['leave_points'],
                'score_after' => $member->leave_points,
                'create_time' => time()
            ];
            ScoreLogModel::insertScoreLog($scoreLogInfo);
            Yii::$app->db->createCommand('update oa_members set leave_points=leave_points+' . $point . " where u_id=" . $taskBase->creater)->execute();
            $member->save(false);
            $rs = true;
        }

        if ($postData['type'] == 2 && !empty($postData['group'])) {
            TaskRangeModel::deleteAll(['task_id' => $taskBase->task_id]);
            self::insertOfferGroup($postData['group'], $postData['task_id']);
            $rs = true;
        }

        $taskBase->update_time = time();
        if ($rs && $taskBase->save(false) && $insProMemRes) {
//            $logInfo = substr($logInfo, 0, strlen($logInfo) - 1);
            TaskLogModel::insertTaskLog($userInfo, $logInfo, $postData['task_id'], $postData['type']);
            $rs = true;
        } else {
            $rs = false;
        }
        return true;
    }
    
    /**
     * 查询我的项目列表
     * @param $uid
     * @return array|yii\db\ActiveRecord[]
     */
    public static function getMyProject($uid, $search)
    {
        $data = ProjectModel::getMyProject($uid);
        array_unshift($data, ['label' => "请选择", 'nums' => 0, 'start_time' => 0, 'end_time' => 0]);
        foreach ($data as $k => $v) {
            $data[$k]['start_time'] = date("Y-m-d H:i", $v['start_time']);
            $data[$k]['end_time'] = date("Y-m-d H:i", $v['end_time']);
        }
        return $data;
    }

    /**
     * 我参与的项目
     * @param int $type 1我创建的项目  2我参与的项目  3公开项目
     * @param $u_id
     * @param $limit
     * @param $offset
     * @param $data
     * @return mixed
     * status 1未开始   2进行中   3已完成
     */
    public static function  getPro($type=3,$u_id,$limit,$offset,$data)
    {
        $proModel = ProjectModel::find()->select('oa_project.*')->with('taskall');

        if($type==1){//我创建的项目
            $proModel->where('u_id=:u_id',[':u_id'=>$u_id]);
        }else if($type==2){//我参与的项目(不包括我创建的)
            $proModel->joinWith('projectmember');
            $proModel->where('oa_project_member.u_id=:u_id',[':u_id'=>$u_id]);
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
        if(isset($data['complete']) && 0 == $data['complete']){
            $proModel->andWhere(['oa_project.complete' => $data['complete']]);
        }

        $proModel->orderBy('oa_project.create_time desc');
        $res['proList'] = $proModel->offset($offset)->limit($limit)->asArray()->all();
        $res['page']['sumPage'] = ceil($proModel->count()/$limit);
        return $res;
    }

    /**
     * 任务详情
     * @param $taskId
     * @param $taskType
     * @return array|null|yii\db\ActiveRecord
     */
    public static function getTaskDetail($taskId, $taskType)
    {
        if (1 ==  $taskType) {
            $data = TaskModel::getTaskListDetails($taskId);
            $data['point'] = empty($data['point']) ? 0 : $data['point'];
            $data['head_img'] = Tools::getHeadImg($data['head_img']);
        } else {
            $data = RewardTaskModel::getRewardTaskDetails($taskId);
            $data['point'] = empty($data['point']) ? 0 : $data['point'];
            //悬赏范围相关数据
            $data['selectedGroup'] = TaskRangeModel::getRewardRangeInfo($taskId);
            $data['task_type'] = 2;
        }
        //任务技能列表
        $data['selecteSkill'] = TaskSkillModel::getTaskSkillRange($taskId, $taskType);
        //附件相关信息
        $data['files'] = TaskAttachmentModel::getAttachmentFileInfo($taskId, 1, $taskType);

        $data['begin_time'] = date('Y-m-d H:i', $data['begin_time']);
        $data['end_time'] = date('Y-m-d H:i', $data['end_time']);

        return $data;
    }

    public static function delAttachment($attId, $userInfo)
    {
        //查询数据库附件
        $attInfo = TaskAttachmentModel::findOne($attId);

        if (empty($attInfo)) {
            FResponse::output(['code' => 20001, 'msg' => '该附件不存在']);
        }
        if(TaskModel::find()->where(['task_id'=>$attInfo->task_id])->asArray()->one()['status']==3){
            FResponse::output(['code' => 20001, 'msg' => '待审核任务，不能删除附件！']);
        }
        //删除数据库记录
        $res = Yii::$app->db->createCommand()->delete('oa_task_attachment', 'task_att_id=:id', array(':id' => $attId))->execute();

        if (!$res) {
            FResponse::output(['code' => 20001, 'msg' => '删除失败']);
        } else {
            //插入删除附件的log日志
//            TaskLogModel::insertTaskLog($userInfo, "删除了附件:" . $attInfo['file_name'], $attInfo['task_id']);
        }

        //删除相应文件
        $fullPath = Yii::getAlias('@upload') . '/'. $attInfo['file_path'] . '/' . $attInfo['file_name'];
        if (file_exists($fullPath)) {
            if (unlink($fullPath)) {
                FResponse::output(['code' => 20000, 'msg' => '删除成功']);
            } else {
                FResponse::output(['code' => 20001, 'msg' => '删除失败']);
            }
        } else {
            FResponse::output(['code' => 20001, 'msg' => '该文件不存在']);
        }
    }
    /**
     * @param $uid
     * @param $operator
     * @param $task_id
     * @param $title
     * @param $task_title
     * 任务消息
     */
    public static function taskMsg($uid,$operator,$task_id,$title,$task_title,$skipType,$taskType)
    {
        \Yii::$app->db->createCommand()->insert('oa_task_msg', [
            'uid'       => $uid,
            'operator'    => $operator,
            'task_id'   => $task_id,
            'title'     => $title,
            'task_title'=> $task_title,
            'create_time' =>  time(),
            'menu' => $skipType,
            'task_type'  =>  $taskType,
        ])->execute();
    }

    /**
     * @param $data
     * @param $taskId
     * @throws yii\db\Exception
     * 悬赏任务日志进库
     */
    public static function taskLogData($data,$taskId){
        \Yii::$app->db->createCommand()->insert('oa_task_log', [
            'u_id'       => $data['u_id'],
            'task_id'    => $taskId,
            'task_type'     => 2,
            'content'=> $data['content'],
            'create_time' =>  $data['create_time'],
        ])->execute();
    }
    /**
     * @param $uid
     * @param $operator
     * @param $task_id
     * @param $title
     * @param $task_title
     * 任务消息
     */
//    public static function taskMsg($uid,$operator,$task_id,$title,$task_title,$skipType)
//    {
//        $taskInfo = new TaskMsgModel;
//        $taskInfo->uid = $uid;
//        $taskInfo->operator = $operator;
//        $taskInfo->task_id = $task_id;
//        $taskInfo->title = $title;
//        $taskInfo->task_title = $task_title;
//        $taskInfo->create_time = time();
//        $taskInfo->menu = $skipType;
//        $taskInfo->save(false);
//    }
}