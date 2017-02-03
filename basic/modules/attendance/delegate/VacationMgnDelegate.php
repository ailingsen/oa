<?php
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/8/19
 * Time: 17:33
 */

namespace app\modules\attendance\delegate;

use yii;
use app\models\AnnualLeaveModel;
use app\models\VacationLogModel;
use app\models\InventoryModel;
use app\lib\FResponse;

class VacationMgnDelegate
{
    public static function editAnnualLeaveDelegate($uid, $increment, $valueBefore, $operatorId,$reason)
    {
        if(($increment+$valueBefore)<0){
            FResponse::output(['code'=> 0, 'msg'=> '修改失败，请正确填写修改天数！']);
        }
        //修改年假
        $ret = AnnualLeaveModel::updateAnnualLeave($uid, $increment);
        if($ret === true){
            //记录操作日志
            $model = new VacationLogModel();
            $model->u_id = $uid;
            $model->value_before = $valueBefore;
            $model->operator_id = $operatorId;
            $model->log_content = $reason;
            $model->create_time = time();
            $model->value_after = floatval($valueBefore) + floatval($increment);
            $model->log_type = 2;  //1.调休 2.年假 3.病假
            if(!$model->save()){
                FResponse::output(['code'=> 0, 'msg'=> $model->getErrors()]);
            }else{
                FResponse::output(['code'=> 20000, 'msg'=> '修改成功！']);
//                return Yii::createObject([
//                    'class' => 'yii\web\Response',
//                    'format' => yii\web\Response::FORMAT_JSON,
//                    'data' => [
//                        'message' => 'OK',
//                        'code' => 100,
//                        'data' => new yii\base\Object()
//                    ],
//                ]);
            }
        }else{
            FResponse::output(['code'=> 0, 'msg'=> '修改失败!']);
        }
    }

    /*
     * 修改调休
     */
    public static function editTuneDelegate($uid, $increment, $valueBefore, $operatorId,$reason)
    {
        if(($increment+$valueBefore)<0){
            FResponse::output(['code'=> 0, 'msg'=> '修改失败，请正确填写修改天数！']);
        }
        $inventory_model = new InventoryModel();
        $ret = false;
        //1.增加调休
        if($increment > 0){
            $ret = $inventory_model->addTuneVacation($uid, $increment);
        }
        //2.减少调休
        elseif($increment < 0){
            $ret = $inventory_model->reduceTuneVacation($uid, $increment);
        }
        if($ret === true){
            //记录操作日志
            $model = new VacationLogModel();
            $model->u_id = $uid;
            $model->value_before = $valueBefore;
            $model->operator_id = $operatorId;
            $model->log_content = $reason;
            $model->create_time = time();
            $model->value_after = floatval($valueBefore) + floatval($increment);
            $model->log_type = 1;  //1.调休 2.年假 3.病假
            if(!$model->save()){
                FResponse::output(['code'=> 0, 'msg'=> '修改失败！']);

            }else{
                FResponse::output(['code'=> 20000, 'msg'=> '修改成功！']);
            }
        }else{
            FResponse::output(['code'=> 0, 'msg'=> '修改失败！']);

        }
    }
}