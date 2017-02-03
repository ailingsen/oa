<?php
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/11/9
 * Time: 16:47
 */

namespace app\modules\v1\controllers;

use app\lib\FResponse;
use app\modules\task\helper\RewardTaskHelper;

class RewardtaskController extends BaseController
{

    public function actionClaimTask()
    {
        $postData = json_decode(file_get_contents("php://input"),true);
        if (!$postData['task_id']) {
            FResponse::output(['code' => 20001, 'msg' => "task_id不能为空"]) ;
        }
        $userInfo = $this->userInfo;
        $rs = RewardTaskHelper::claimTask($userInfo, $postData['task_id']);
        if ($rs) {
            FResponse::output(['code' => 20000, 'msg' => '认领成功']);
        }
        FResponse::output( ['code' => 20001, 'msg' => '认领失败']);
    }
    /**
     * 确认任务-指派人员
     */
    public function actionPointTask()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata,true);
        if (!$request['task_id']) {
            FResponse::output(['code' => 20001, 'msg' => "task_id不能为空"]);
        }
        if (!isset($request['point_uid'])) {
            FResponse::output(['code' => 20001, 'msg' => "请选择指派给谁"]);
        }
        $rs = RewardTaskHelper::pointTask($request, $this->userInfo);
        FResponse::output(['code' => 20000, 'msg' => '确认成功!']);
    }
}