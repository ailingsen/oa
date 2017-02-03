<?php

/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2015/10/23
 * Time: 17:35
 */

namespace app\commands;

use app\models\MembersModel;
use app\models\VacationInventoryModel;
use app\modules\apply\delegate\LeaveDelegate;
use yii\console\Controller;
use Yii;

class InventoryLogController extends Controller
{
    public function actionOverTimeLog() {
        $memberInfo = MembersModel::find()->select('u_id,entry_time')->asArray()->all();
        $insertLog=[];
        foreach($memberInfo as $key=>$val){
            $expireOverTime=array();
            $expireOverTime = VacationInventoryModel::find()->where('is_valid=0 and expire_time<'.time().' and u_id='.$val['u_id'])->asArray()->all();
            if(count($expireOverTime)>0){
                //获取当前可用调休数
                $overtimeSum = LeaveDelegate::getInventorySum($val['u_id']);
                $insertLog[]=[
                    'u_id'=>$val['u_id'],
                    'log_type'=>1,
                    'value_before'=>(count($expireOverTime)/2)+$overtimeSum,
                    'value_after'=>$overtimeSum,
                    'log_content'=>'调休过期',
                    'create_time'=>time(),
                    'operator_id'=>1
                ];
                foreach($expireOverTime as $ok=>$ov){
                    $data=[];
                    $data=['is_valid'=>2];
                    Yii::$app->db->createCommand()->update('oa_vacation_inventory', $data, 'id=:id',[':id'=>$ov['id']] )->execute();
                }
            }
        }

        //插入调休过期日志
        if(count($insertLog)>0){
            Yii::$app->db->createCommand()->batchInsert('oa_vacation_log',['u_id', 'log_type', 'value_before','value_after','log_content','create_time','operator_id' ],$insertLog)->execute();
        }

    }

}