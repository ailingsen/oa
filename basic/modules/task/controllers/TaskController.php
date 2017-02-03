<?php
/**
 * Created by PhpStorm.
 * User: liaoshuochao
 * Date: 2016/7/15
 * Time: 10:26
 */
namespace app\modules\task\controllers;
use app\lib\FResponse;
use app\models\MembersModel;
use app\models\OrgMemberModel;
use app\models\RewardTaskModel;
use app\models\TaskAttachmentModel;
use app\models\TaskModel;
use app\lib\Tools;
use app\models\TaskMsgModel;
use app\modules\notice\helper\NoticeHelper;
use app\modules\project\helper\ProjectHelper;
use app\modules\task\delegate\TaskDelegate;
use app\modules\task\helper\TaskHelper;
use app\models\ProjectModel;
use app\models\TaskMemberModel;
use Yii;

Class TaskController extends \app\controllers\BaseController{
    public $modelClass = 'app\models\TaskModel';
    /**
     * 创建任务
     */
    public function actionCreatetask()
    {
        $postData = Yii::$app->request->post();
        TaskHelper::checkParams(2);
//        if($postData['point'] > MembersModel::find()->where(['u_id'=> $this->userInfo['u_id']])->asArray()->one()['leave_points']){
//            return ['code' => 20001, 'msg' => "纳米币不足！"];
//        }
        if($postData['type']==1){
            if(empty($postData['pro_id'])){
                FResponse::output(['code'=>20001,'msg' => '请选择项目！']);
            }
            $proEndTime = ProjectModel::find()->where(['pro_id'=>$postData['pro_id']])->asArray()->one();
            if (strtotime($postData['startTime']) < $proEndTime['begin_time']){
                FResponse::output(['code'=>20001,'msg' => '任务开始时间不能小于该项目开始时间，请重新选择！']);
            }
            if(!empty($proEndTime['delay_time']) && (strtotime($postData['endTime']) > $proEndTime['delay_time'])){
                FResponse::output(['code'=>20001,'msg' => '任务结束时间不能大于该项目结束时间，请重新选择！']);
            }elseif (empty($proEndTime['delay_time']) && (strtotime($postData['endTime']) > $proEndTime['end_time'])){
                FResponse::output(['code'=>20001,'msg' => '任务结束时间不能大于该项目结束时间，请重新选择！']);
            }
        }

        if (isset($postData['point']) && $postData['point'] > 0) {
            $uInfo = MembersModel::getUserMessage($this->userInfo['u_id'],'leave_points');
            TaskHelper::checkPoints($postData['point'],$uInfo);
        }

        if (isset($postData['point']) && $postData['point'] < 0) {
            return ['code' => 20060, 'msg' => "纳米币必须为整数"];
        }
        !isset($postData['projectId']) && $postData['projectId'] = 0;

        if ($taskId = TaskDelegate::createTask($postData, $this->userInfo)) {
            //更新用户信息
            return ['code' => 20000, 'msg' => "ok", 'data' => ['taskId' => $taskId]];
        } else {
            return ['code' => 20060, 'msg' => "新增失败，请检查参数"];
        }

    }

    /**
     * @return array
     */
    public function actionAllgroupmember() {
        $lately = Yii::$app->request->post('lately', '');
        $search = Yii::$app->request->post('search','');
        $org_id = Yii::$app->request->post('org_id');
        if(empty($org_id)){
            $orgInfo = OrgMemberModel::getMemberOrgInfo($this->userInfo['u_id']);
            $org_id = $orgInfo['org_id'];
        }
        $memberList = TaskHelper::getGroupMember($org_id, $lately, $search);
        return ['code' => 20000, 'msg' => 'ok', 'data' => $memberList];
    }

    /**
     * 任务详情
     * @param $taskId
     * @param $type
     * @return array
     */
    public function actionTaskDetail($taskId, $type)
    {
        $data = TaskDelegate::getTaskDetail($taskId, $type);
        return ['code' => 20000, 'msg' => "ok", 'data' => $data, 'temp_task_type'=>$type];
    }

    /**
     * 查询我的项目列表
     */
    public function actionGetmyproject()
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
        $postdata['complete'] = isset($postdata['complete']) ? $postdata['complete'] : 0;
        $data = TaskDelegate::getPro($public, $this->userInfo['u_id'], $pageParam['limit'], $pageParam['offset'], $postdata);
        //处理项目数据
        $data['proList'] = ProjectHelper::setProData($data['proList']);
        $data['page']['curPage'] = $page;
        return  ['code'=>1, 'data'=>$data];
    }


    /**
     * 修改任务
     */
    public function actionUpdateTask()
    {
        $postData = Yii::$app->request->post();
        $charger = TaskModel::find()->where(['task_id'=>$postData['task_id']])->asArray()->one()['charger'];
//        if($postData['point'] > MembersModel::find()->where(['u_id'=> $this->userInfo['u_id']])->asArray()->one()['leave_points']){
//            FResponse::output( ['code' => 20001, 'msg' => "纳米币不足！"]);
//        }
        if($postData['taskType']==1){
            $proEndTime = ProjectModel::find()->where(['pro_id'=>$postData['pro_id']])->asArray()->one();
            if(strtotime($postData['begin_time']) < $proEndTime['begin_time']){
                FResponse::output(['code'=>20001,'msg' => '任务开始时间不能小于该项目开始时间，请重新选择！']);
            }
            if(!empty($proEndTime['delay_time']) && (strtotime($postData['end_time']) > $proEndTime['delay_time'])){
                FResponse::output(['code'=>20001,'msg' => '任务结束时间不能大于该项目结束时间，请重新选择！']);
            }elseif (empty($proEndTime['delay_time']) && (strtotime($postData['end_time']) > $proEndTime['end_time'])){
                FResponse::output(['code'=>20001,'msg' => '任务结束时间不能大于该项目结束时间，请重新选择！']);
            }
        }
        if (!isset($postData['task_id'])) {
            return ['code' => 20071, 'msg' => "task_id不能为空"];
        }
        $rs = TaskDelegate::updateTask($this->userInfo, $postData);
        if ($rs) {
            $leavePoints = MembersModel::find()->where(['u_id'=>$this->userInfo['u_id']])->asArray()->one()['leave_points'];
            if($postData['taskType']==1 && $charger == $postData['charger']['u_id']){
                TaskDelegate::taskMsg($postData['charger']['u_id'],$this->userInfo['u_id'],$postData['task_id'],'编辑了任务',$postData['task_title'],1,1);
                Tools::msgJpush(3,$postData['task_id'],$this->userInfo['real_name'].'编辑了任务'.$postData['task_title'],[$postData['charger']['u_id']],['taskType'=>1]);
            }
            if($postData['taskType']==1 && $charger!=$postData['charger']['u_id']){
                TaskDelegate::taskMsg($postData['charger']['u_id'],$this->userInfo['u_id'],$postData['task_id'],'给你指派了任务',$postData['task_title'],1,1);
                TaskDelegate::taskMsg($charger,$this->userInfo['u_id'],$postData['task_id'],'重新指派了你的任务',$postData['task_title'],0,1);
                Tools::msgJpush(3,$postData['task_id'],$this->userInfo['real_name'].'给你指派了任务'.$postData['task_title'],[$postData['charger']['u_id']],['taskType'=>1]);
                Tools::msgJpush(3,$postData['task_id'],$this->userInfo['real_name'].'重新指派了你的任务'.$postData['task_title'],[$charger],['taskType'=>0]);
            }
            if($postData['taskType']==2){
                $taskId = RewardTaskModel::find()->where(['task_title'=>TaskModel::find()->where(['task_id'=>$postData['task_id']])->asArray()->one()['task_title']])->asArray()->one()['task_id'];
                $memberInfo = TaskMemberModel::find()->where(['task_id'=>$taskId,'is_charge'=>1])->asArray()->all();
                if(!empty($memberInfo)){
                    foreach ($memberInfo as $key => $val){
                        TaskDelegate::taskMsg($val['u_id'],$this->userInfo['u_id'],$postData['task_id'],'编辑了任务',$postData['task_title'],1,2);
                        Tools::msgJpush(3,$postData['task_id'],$this->userInfo['real_name'].'编辑了任务'.$postData['task_title'],[$val['u_id']],['taskType'=>1]);
                    }
                }
            }
            return ['code' => 20000, 'msg' => '编辑任务保存成功','data' => $leavePoints];
        }
        return ['code' => 20001, 'msg' => '编辑任务保存失败'];
    }

    /**
     * 获取任务开始时间,结束时间
     */
    public function actionGetDate()
    {
        $id = Yii::$app->request->get("id");
        $taskInfo = TaskModel::findOne($id)->toArray();
        $taskInfo['task_start_time'] = date("Y-m-d H:i", $taskInfo['task_start_time']);
        $taskInfo['task_end_time'] = date("Y-m-d H:i", $taskInfo['task_end_time']);
        return ['code' => 20000, 'msg' => 'ok', 'data' => ["taskInfo" => $taskInfo]];
    }

    /**
     * 文件上传
     * @param $type
     * @param $taskType
     * @return array
     */
    public function actionUpload($type, $taskType, $taskId = 0)
    {
        if($taskType==1 && TaskModel::find()->where(['task_id'=>$taskId])->asArray()->one()['status'] == 3){
            return ['code' => '20001', 'msg' => '待审核任务，不能上传附件！'];
        }
        $rs = TaskHelper::upload($this->userInfo, $taskId, $type, $taskType);
        if ($rs['task_att_id'] > 0) {
            FResponse::output(['code' => '20000', 'msg' => 'ok', 'data' => $rs]);
        }
        return ['code' => '20001', 'msg' => '上传失败', 'data' => new \stdClass()];
    }

    /**
     * 删除已上传附件
     * @param $attId
     * @return array'
     */
    public function actionDelAtt($attId) {
        $rs = TaskDelegate::delAttachment($attId, $this->userInfo);
        return $rs;
    }

    /**
     * 附件下载
     */
    public function actionDownload() {
        $fileId = urldecode(Yii::$app->request->get('task_att_id'));

        $taskAttach = TaskAttachmentModel::findOne($fileId);

        if(empty($taskAttach)) {
            return ['code' => 0, 'msg' =>'文件不存在'];
        }

        $status = NoticeHelper::getDownFile(Yii::getAlias('@upload').'/'.$taskAttach['file_path'].'/'.$taskAttach['file_name'],$taskAttach['real_name']);
        //$status=NoticeHelper::getDownFile("D:/www/oa4/file/".$taskAttach['file_path'].'/'.$taskAttach['file_name'],$taskAttach['real_name']);
        if($status == 1){
            return ['code' => 0, 'msg' =>'文件不存在'];
        }

        /*$fullPath = Yii::getAlias('@upload') . '/'. $taskAttach['file_path'] . '/' . $taskAttach['file_name'];
        if (!$taskAttach || !file_exists($fullPath)) {
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        }
        $file = fopen($fullPath, "r");
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: " . filesize($fullPath));
        Header("Content-Disposition: attachment; filename=" . $taskAttach['real_name']);
        echo fread($file, filesize($fullPath) );
        fclose($file);
        exit;*/
    }

    public function actionGetLeavePoints()
    {
        $leavePoints = MembersModel::find()->where(['u_id'=>$this->userInfo['u_id']])->asArray()->one()['leave_points'];
        FResponse::output(['code'=>20000,'msg'=>'ok','data'=>$leavePoints]);
    }
}