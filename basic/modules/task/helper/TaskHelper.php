<?php
/**
 * Created by PhpStorm.
 * User: liaoshuochao
 * Date: 2016/7/15
 * Time: 10:30
 */
namespace app\modules\task\helper;

use app\lib\FileUploadHelper;
use app\lib\FResponse;
use app\lib\Tools;
use app\models\MembersModel;
use app\models\RewardTaskModel;
use app\models\TaskAttachmentModel;
use app\models\TaskLogModel;
use app\models\TaskModel;
use app\modules\workmate\delegate\WorkmateDelegate;
use yii;

Class TaskHelper {
    /**
     * 检查参数
     */
    public static function checkParams($type)
    {
        if($type==1){
            $postData = json_decode(file_get_contents("php://input"),true);
        }else{
            $postData = Yii::$app->request->post();
        }
        if (!isset($postData['title']) || !isset($postData['type']) || !isset($postData['startTime']) || !isset($postData['endTime'])) {
            FResponse::output(['code' => 20004, 'msg' => "参数不全"]);
        }

        if (isset($postData['skills']) && !is_array($postData['skills'])) {
            FResponse::output(['code' => 20003, 'msg' => "skills必须为数组"]);
        }


        if (isset($postData['attr'])) {
            if (!is_array($postData['attr'])) {
                FResponse::output(['code' => 20004, 'msg' => "attr必须为数组"]);
            }
        }
    }

    /**
     * 检查积分是否合法
     * @param $uid
     */
    public static function checkPoints($point, $userInfo){
//        if (!isset($data['point']) || $data['point'] > 100 || $data['point'] <= 0) {
//            FResponse::output(['code' => 20001, 'msg' => "积分必须大于0小于等于100"]);
//        }
        if (!(isset($point) && $point >= 0)) {
            FResponse::output(['code' => 20001, 'msg' => "积分必须大于等于0"]);
        }

        //积分
        if ($userInfo['leave_points'] < $point) {
            FResponse::output(['code' => 20003, 'msg' => "用户可分配纳米币不足"]);
        }
    }

    /**
     * 检查标题是否存在
     * @param $title
     * @return bool
     */
    public static function isOfferExist($title)
    {
        $isExist = TaskModel::find()->select("task_title")->where('task_title=:task_title', [':task_title' => $title])->one();
        if ($isExist) {
            FResponse::output(['code' => 20061, 'msg' => "该任务标题已存在"]);
        }
        $isExist = RewardTaskModel::find()->select("task_title")->where('task_title=:task_title', [':task_title' => $title])->one();
        if ($isExist) {
            FResponse::output(['code' => 20061, 'msg' => "该任务标题已存在"]);
        }
        return true;
    }

    /**
     * 附件上传
     * @param $userInfo
     * @param $taskId
     * @param $type
     * @param $taskType
     * @return bool|string
     */
    public static function upload($userInfo, $taskId, $type, $taskType)
    {
        if (empty($type)) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error"]);
        }

        $file = FileUploadHelper::fileUpload(Yii::getAlias('@task'));

//        TaskLogModel::insertTaskLog($userInfo, $userInfo['real_name'] . "上传了附件", $taskId);

        if (1 == $file['code']) {
            $data = array(
                'create_time' => time(),
                'file_size' => $file['data']['file_size'],
                'file_name' => $file['data']['real_name'],
                'type' => $type,
                'file_type' => $file['data']['file_type'],
                'real_name' => $file['data']['file_name'],
                'task_id' => $taskId,
                'task_type' => $taskType,
                'file_path' => $file['data']['file_path']
            );
        } else {
            return ['task_att_id' => 0];
        }

        //保存到数据库
        if (TaskAttachmentModel::createX($data)) {
            $attId = Yii::$app->db->getLastInsertID();
        } else {
            return false;
        }

       return ['task_att_id' => $attId, 'real_name' => $file['data']['file_name'], 'file_size' => $file['data']['file_size'], 'file_type' => $file['data']['file_type']];
    }
    
    public static function getGroupMember($orgId, $lately = '', $search = '') {
        $rs = [];
        if (trim($search) || $search === 0) {
            $rs = WorkmateDelegate::searchMember($search);
        } else {
            $rs = WorkmateDelegate::getOrgMemberList($orgId, $lately);
        }
        foreach ($rs as $key => $item) {
            if (!isset($item['u_id'])) {
                unset($rs[$key]);
                continue;
            }
            $rs[$key]['head_img'] = Tools::getHeadImg($item['head_img']);
        }
        return $rs;
    }

    //删除已上传附件
    public function actionDelatt() {
        $fileName = urldecode(Yii::$app->request->post('filename'));
        $realpath = urldecode(Yii::$app->request->post('filepath'));
        $type = Yii::$app->request->post('type');      //上传类型 1附件 2图片

        $filepath = WEB_ROOT . $realpath;

        $realFile = $filepath . $fileName;

        if (file_exists($realFile)) {
            if (unlink($realFile)) {
                echo json_encode(array('status' => 1, 'msg' => '删除成功'));
            } else {
                echo json_encode(array('status' => 0, 'msg' => '删除失败'));
            }
        } else {
            echo json_encode(array('status' => 0, 'msg' => '该文件不存在'));
        }
    }

    /**
     * 处理任务列表数据
     * @param $taskList
     * @return mixed
     */
    public static function doTaskListData($taskList) {
        $nowTime = time();
        foreach ($taskList as $key => $val){
            //相关责任人信息
            if( $val['end_time'] < $nowTime){
                $taskList[$key]['overtime'] = 1;
            }
        }
        return $taskList;
    }
}

