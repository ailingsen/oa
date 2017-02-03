<?php
namespace app\modules\management\controllers;

use yii;
use yii\web\Controller;
use app\models\VacationLogModel;
use app\models\AnnualLeaveModel;
use app\models\OrgModel;
use app\modules\management\delegate\AnnualLeaveDelegate;
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/6/28
 * Time: 14:45
 */
class AnnualLeaveController extends Controller
{
    /**
     * 年假库存展示
     */
    public function actionIndex()
    {
//        $params = json_decode(file_get_contents('php://input'),true);
//        $current = $params['current']; //页数
//        $num = 20;//每页条数
        $data = AnnualLeaveDelegate::getAnnualLeave('','', 5, 20);
        $orgData = OrgModel::getOrgs();
        return $this->render('index',[
            'data'    => $data,
            'orgData' => $orgData
        ]);
    }
    public function actionEditAnnualVacation()
    {
        $u_id = Yii::$app->request->post['u_id'];
        $increment = Yii::$app->request->post['increment'];
        $value_before = Yii::$app->request->post['value_before'];
        $operator_id = Yii::$app->request->post['operator_id'];
        $reason = Yii::$app->request->post['reason'];

        $inventory_model = new AnnualLeaveModel();
        //修改年假
        $ret = $inventory_model->updateAnnualLeave($u_id, $increment);
        AnnualLeaveDelegate::vacationLog($ret, $u_id, $value_before, $operator_id, $reason, $increment);
    }

    /**
     * 查询单个用户日志
     * @return object
     * @throws yii\base\InvalidConfigException
     */
    public function actionUserVacationLog()
    {
        $u_id = Yii::$app->request->post['u_id'];
        $log_type = Yii::$app->request->post['type'];
        $inventory_model = new VacationLogModel();
        $data = $inventory_model->userVacationLog($u_id, $log_type);
        return $this->render('',[
            'data'=>$data
        ]);
    }

}