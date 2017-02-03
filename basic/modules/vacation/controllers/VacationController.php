<?php

namespace app\modules\vacation\controllers;

use app\controllers\BaseController;
use app\modules\vacation\delegate\VacationDelegate;
use app\models\VacationInventoryModel;
use app\config\Dict;
use app\modules\vacation\helper\VacationHelper;

use app\modules\vacation\Vacation;
use yii;
/**
 * Default controller for the `vacation` module
 */
class VacationController extends BaseController
{
    public $modelClass = 'app/models/VacationSetModel';
    /**
     * 假期设置
     * @return array
     */
    public function actionVacationSet()
    {
        if (VacationDelegate::vacationSet(Yii::$app->request->post())) {
            return ['code' => '20000', 'msg' => 'ok', 'data' => new \stdClass()];
        }
        return ['code' => '20001', 'msg' => '设置失败', 'data' => new \stdClass()];
    }

    /**
     * 查看假期设置
     * @return array
     */
    public function actionViewSet()
    {
        $data = VacationDelegate::getVacationSet();
        return ['code' => '20000', 'msg' => 'ok', 'data' => $data];
    }

    
    /*
     * 查看调休假
     */
    public function actionGetWorkDay()
    {
        $uid = 291;
        VacationInventoryModel::getVaInventory($uid);
    }

    /**
     * 假期统计
     * @return array
     */
    public function actionStatistic()
    {
        $data = VacationHelper::statistic(Yii::$app->request->post());
        return ['code' => '20000', 'msg' => 'ok', 'data' => $data];
    }


    /**
     * 导出
     */
    public function actionExportexcel()
    {
        VacationHelper::getExportexcel();
    }

    /**
     * 判断是否有导出内容
     */
    public function actionHasContent() {
        $postdata = Yii::$app->request->get('args');
        $data = json_decode($postdata, true);
        $starTime = isset($data['start_time']) ? $data['start_time'] : date('Ymd H:i', mktime(0, 0, 0, date("m"), 1, date("Y")));
        $endTime = isset($data['end_time']) ? $data['end_time'] : date('Ymd H:i', time());
        $data['search_name'] = isset($params['search_name']) ? $params['search_name'] : '';
        $data['page_size'] = $pageSize = 65536;
        $starTime = strtotime($starTime);
        $endTime = strtotime($endTime);
        $differMonth = date('m', $endTime - $starTime); //整数月
        /*if ($differMonth > 6) {
            die($differMonth . '起止时间不能超过6个月');
        }*/

        $statisticList = VacationHelper::statistic($data, 'excel');
        if(empty($statisticList['vacation_list'])) {
            return ['code' => 0,'msg' => '没有需要导出的内容'];
        }else {
            return ['code' => 1,'msg' => 'OK'];
        }
    }
}
