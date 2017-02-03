<?php
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/8/16
 * Time: 10:51
 */

namespace app\modules\attendance\controllers;

use app\models\VacationLogModel;
use yii;
use yii\web\Controller;
use app\models\MembersModel;
use app\models\OrgModel;
use app\models\OrgMemberModel;
use app\models\VacationUsedModel;
use app\lib\Tools;
use app\lib\FResponse;
use app\lib\OutExcel;
use app\modules\attendance\delegate\VacationMgnDelegate;
use app\controllers\BaseController;


class VacationMgnController extends BaseController
{
    public $modelClass = 'app\models\MembersModel';
    /**
     * 年假库存展示
     */
    public function actionAnnualVacations()
    {
        $orgId = !empty(Yii::$app->request->post('orgId')) ? Yii::$app->request->post('orgId') : '2';
        if(Yii::$app->request->post('userName')== '全部'){
            $userName = '';
        }else{
            $userName = !empty(Yii::$app->request->post('userName')) ? Yii::$app->request->post('userName') : '';
        }

        $pageSize = '10';
        $curPage = !empty(Yii::$app->request->post('curPage')) ? Yii::$app->request->post('curPage') : '0';
        $vacationData = MembersModel::getOrgMemberVacation($orgId, $userName, $pageSize, $curPage);
        FResponse::output(['code'=>20000, 'msg'=> 'ok', 'data'=>$vacationData]);
    }
    /*
    *年假导出
    */
    public function actionVacationExcel()
    {
        $orgId = !empty(Yii::$app->request->get('orgId')) ? Yii::$app->request->get('orgId') : '2';
        if(Yii::$app->request->get('userName')=='全部' || strlen(Yii::$app->request->get('userName'))==0){
            $userName = '';
        }else{
            $userName = Yii::$app->request->get('userName');
        }
        $pageSize = '100000';
        $curPage = '0';
        $vacationData = MembersModel::getOrgMemberVacation($orgId, $userName, $pageSize, $curPage);
        $data = array();
        foreach ($vacationData['vacationData'] as $key => $val){
            $data[$key]['u_id'] = $val['u_id'];
            $data[$key]['real_name'] = $val['real_name'];
            $data[$key]['org_name'] = $val['org_name'];
            $data[$key]['annualLeave'] = $val['annualLeave'];
            foreach ($val['workDays'] as $k => $v){
                $data[$key]['workDays'] = $v;
            }
        }
        $headArr = ['ID', '姓名', '所属部门', '年假（天）','调休假（天）'];
        $filename = '假期数据';
        $excelValue = new OutExcel();
        if(empty($data)){
            die();
        }
        //导出年假
        $excelValue->getExcel($filename,$headArr,$data);
        die();
    }
    

    /**
     * 修改年假
     * @return object
     * @throws yii\base\InvalidConfigException
     */
    public function actionEditAnnualVacation()
    {
        $uid = Yii::$app->request->post('uid');
        $increment = Yii::$app->request->post('increment');
        $valueBefore = Yii::$app->request->post('valueBefore');
        $operatorId = $this->userInfo['u_id'];
        $reason = Yii::$app->request->post('reason');
        VacationMgnDelegate::editAnnualLeaveDelegate($uid, $increment, $valueBefore, $operatorId,$reason);
    }
    /**
     * 修改调休假
     * @return object
     * @throws yii\base\InvalidConfigException
     */
    public function actionEditTuneVacation()
    {
        $uid = Yii::$app->request->post('uid');
        $increment = Yii::$app->request->post('increment');
        $valueBefore = Yii::$app->request->post('valueBefore');
        $operatorId = $this->userInfo['u_id'];
        $reason = Yii::$app->request->post('reason');
        VacationMgnDelegate::editTuneDelegate($uid, $increment, $valueBefore, $operatorId,$reason);
    }

    /**
     * 变更记录
     */
    public function actionChangeRecord()
    {
        $uid = Yii::$app->request->post('uid');
        $logType = Yii::$app->request->post('logType');
        $changeRecord = VacationLogModel::getChangeRecord($uid,$logType);
        foreach ($changeRecord['changeRecord'] as $key => $val){
            $changeRecord['changeRecord'][$key]['head_img'] = Tools::getHeadImg($val['head_img']);
            switch (date('w',$val['create_time'])) {
                case 0:$changeRecord['changeRecord'][$key]['weekDay']="星期天";break;
                case 1:$changeRecord['changeRecord'][$key]['weekDay']="星期一";break;
                case 2:$changeRecord['changeRecord'][$key]['weekDay']="星期二";break;
                case 3:$changeRecord['changeRecord'][$key]['weekDay']="星期三";break;
                case 4:$changeRecord['changeRecord'][$key]['weekDay']="星期四";break;
                case 5:$changeRecord['changeRecord'][$key]['weekDay']="星期五";break;
                case 6:$changeRecord['changeRecord'][$key]['weekDay']="星期六";break;
            }
        }
        FResponse::output(['code'=>20000, 'msg'=> 'ok', 'data'=> $changeRecord]);
    }
}