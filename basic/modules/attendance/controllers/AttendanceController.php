<?php

namespace app\modules\attendance\controllers;

use app\controllers\BaseController;
use app\lib\Tools;
use app\models\WorkSetModel;
use app\modules\attendance\delegate\AttendanceDelegate;
use app\modules\attendance\helper\AttendanceHelper;
use app\modules\vacation\helper\VacationHelper;
use Yii;

/**
 * Default controller for the `attendance` module
 */
class AttendanceController extends BaseController
{
    public $modelClass = 'app\models\ChecktimeModel';

    /**
     * 我的考勤
    */
    public function actionMyAttend()
    {
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata,true);
        $page = isset($postdata['page']) ? $postdata['page'] : 1;
        //获取翻页参数
        $pageParam = AttendanceHelper::setPage(1,$page);
        //获取工作日时间设置
        $timeSet = AttendanceDelegate::getTimeSet();
        //获取我的考勤
        $myAttend = AttendanceDelegate::getMyAttend($this->userInfo['u_id'], $pageParam['limit'], $pageParam['offset'], $postdata, $timeSet);
        //处理我的考勤数据
        if(count($myAttend['myattendList'])>0){
            $myAttend['myattendList'] = AttendanceHelper::setMyAttend($timeSet,$myAttend['myattendList']);
        }
        $myAttend['page']['curPage'] = $page;
        return ['code'=>1,'data'=>$myAttend];
    }

    /**
     * 员工考勤
    */
    public function actionAllAttend()
    {
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata,true);
        $page = isset($postdata['page']) ? $postdata['page'] : 1;
        //获取翻页参数
        $pageParam = AttendanceHelper::setPage(1,$page);
        $arrAttend = AttendanceDelegate::getAllAttend($pageParam['limit'], $pageParam['offset'], $postdata);
        $arrAttend['attendList'] = AttendanceHelper::setStatus($arrAttend['attendList']);
        $arrAttend['page']['curPage'] = $page;
        return ['code'=>1,'data'=>$arrAttend];
    }

    /**
     * 获取搜索时用的组信息
     * $search_org_name
    */
    public function actionOrgInfo()
    {
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata,true);
        $keyword = isset($postdata['search_org_name']) ? $postdata['search_org_name'] : '';
        $orgList = AttendanceDelegate::getOrgList($keyword);
        return ['code'=>1,'data'=>$orgList];
    }

    /**
     * 获取搜索时用的用户信息信息
     * $search_real_name
     * $search_org_id
     */
    public function actionMemberInfo()
    {
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata,true);
        $keyword = isset($postdata['search_real_name']) ? $postdata['search_real_name'] : '';
        $org_id = isset($postdata['search_org_id']) ? $postdata['search_org_id'] : 0;
        $memList = AttendanceDelegate::getMemberList($keyword,$org_id);
        foreach($memList as $key => $value) {
            $memList[$key]['head_img'] = Tools::getHeadImg($value['head_img']);
        }
        return ['code'=>1,'data'=>$memList];
    }

    /**
     * 考勤统计
    */
    public function actionAttendCount()
    {
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata,true);
        $page = isset($postdata['page']) ? $postdata['page'] : 1;
        //获取翻页参数
        $pageParam = AttendanceHelper::setPage(1,$page);
        $overtime = AttendanceDelegate::getAttendCount($pageParam['limit'], $pageParam['offset'], $postdata);
        $overtime['attendList'] = AttendanceHelper::setAttendCountData($overtime['attendList']);
        $overtime['page']['curPage'] = $page;
        return ['code'=>1,'data'=>$overtime];
    }

    /**
     * 考勤统计导出
    */
    public function actionAttendCountExp()
    {
        $postdata = Yii::$app->request->get('args');
        $postdata = json_decode($postdata,true);
        //获取考勤统计数据
        $attendCountData = AttendanceDelegate::getAttendCountExp($postdata);
        if(count($attendCountData)>20000){
            die('导出数据超过限制,请输入查询条件，再导出数据');
        }
        $attendCountData = AttendanceHelper::setAttendCountData($attendCountData);
        $filename='加班统计_'.date('Ymd',time()).'.xls';
        $arrHead = [
          '姓名',
         '部门',
         '申请加班开始时间',
         '申请加班结束时间'
        ];
        VacationHelper::getExcel($filename,$arrHead,$attendCountData,$postdata['is_check']);
    }

    /**
     * 考勤设置
    */
    public function actionAttendSet()
    {
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata,true);
        if(strlen($postdata['begin_time'])<=0 || strlen($postdata['end_time'])<=0 || strlen($postdata['workday_time'])<=0 || strlen($postdata['workday_lose'])<=0 || strlen($postdata['unworkday_time'])<=0){
            return ['code'=>-1,'msg'=>'设置失败,数据不能为空'];
        }
        if(!preg_match("/^((0?[0-9]|1[0-9]|2[0-3])\:(0?[0-9]|[1-5][0-9]))?$/",$postdata['begin_time'])){
            return ['code'=>-1,'msg'=>'设置失败,上班时间格式错误'];
        }
        if(!preg_match("/^((0?[0-9]|1[0-9]|2[0-3])\:(0?[0-9]|[1-5][0-9]))?$/",$postdata['end_time'])){
            return ['code'=>-1,'msg'=>'设置失败,下班时间格式错误'];
        }
        if(!preg_match("/^((0?[0-9]|1[0-9]|2[0-3])\:(0?[0-9]|[1-5][0-9]))?$/",$postdata['workday_time'])){
            return ['code'=>-1,'msg'=>'设置失败,工作日加班起算时间格式错误'];
        }
        if(!preg_match("/^[0-9]*[1-9][0-9]*$/",$postdata['workday_lose'])){
            return ['code'=>-1,'msg'=>'设置失败,工作日加班调休失效时间格式错误'];
        }
        if(!preg_match("/^(([0-9]+\.[0-9]{1})|([0-9]*[1-9][0-9]*\.[0-9]{1})|([0-9]*[1-9][0-9]*))$/",$postdata['unworkday_time'])){
            return ['code'=>-1,'msg'=>'设置失败,非工作日加班休息时间格式错误'];
        }
        $worksetModel = WorkSetModel::findOne(1);
        if(isset($worksetModel->id)){
            $worksetModel->begin_time=$postdata['begin_time'];
            $worksetModel->end_time=$postdata['end_time'];
            $worksetModel->workday_time=$postdata['workday_time'];
            $worksetModel->workday_lose=$postdata['workday_lose'];
            $worksetModel->unworkday_time=$postdata['unworkday_time'];
            if($worksetModel->save(false)){
                return ['code'=>1,'msg'=>'保存成功'];
            }else{
                return ['code'=>-1,'msg'=>'保存失败'];
            }
        }else{
            $worksetModel = new WorkSetModel();
            $worksetModel->begin_time=$postdata['begin_time'];
            $worksetModel->end_time=$postdata['end_time'];
            $worksetModel->workday_time=$postdata['workday_time'];
            $worksetModel->workday_lose=$postdata['workday_lose'];
            $worksetModel->unworkday_time=$postdata['unworkday_time'];
            if($worksetModel->save(false)){
                return ['code'=>1,'msg'=>'保存成功'];
            }else{
                return ['code'=>-1,'msg'=>'保存失败'];
            }
        }
    }

    /**
     * 获取考勤设置
     */
    public function actionGetAttendSet()
    {
        $attendSet = AttendanceDelegate::getAttendSet();
        return ['code'=>1,'data'=>$attendSet];
    }

}
