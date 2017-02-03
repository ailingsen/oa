<?php
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/6/28
 * Time: 14:54
 */

namespace app\modules\management\delegate;


use yii;
use app\models\MembersModel;
use app\models\OrgModel;
use app\models\VacationLogModel;
use app\models\AnnualLeaveModel;

//处理年假相关数据
class AnnualLeaveDelegate
{
    public static function getAnnualLeave($org_id = '', $u_name = '', $current, $num)
    {
        $inventory_model = new MembersModel();
        if($org_id != 0 && $u_name != ''){
            //获取子组
            $org_model = new OrgModel();
            $selected_org = $org_model::getAllChildrenOrgId($org_id);
            $selected_org[] = $org_id;
            $data = $inventory_model->getUserInformation($u_name, $current, $num);
        }elseif(($org_id == 0) && ($u_name != '')){
            $data = $inventory_model->getUserInformation($u_name, $current, $num);
        }elseif(($org_id != 0) && ($u_name == '')){
            $data = $inventory_model->getUserInformation($org_id, $current, $num);
        }else{
            $data = $inventory_model->getUserInformation($current, $num, '');
        }
        //遍历所有用户，计算可用年假
        $ret = array();
        foreach ($data as $item) {
            //获取已休年假的天数
            $temp = array();
            $vacation_detail = AnnualLeaveModel::getAnnualLeave($item['u_id']);
            $vacation = $vacation_detail['normal_leave']+$vacation_detail['delay_leave'];
            $temp['u_id'] = $item['u_id'];
            $temp['name'] = $item['real_name'];
            $temp['department'] = $item['org_name'];
            $temp['stock'] = $vacation;
            $ret[] = $temp;
        }
        return $ret;
    }
    /*
     * 记录操作日志
     * @param $ret
     */
    public static function vacationLog($ret, $u_id, $value_before, $operator_id, $reason, $increment)
    {
        if($ret === true){
            //记录操作日志
            $model = new VacationLogModel();
            $model->u_id = $u_id;
            $model->value_before = $value_before;
            $model->operator_id = $operator_id;
            $model->log_content = $reason;
            $model->create_time = time();
            $model->value_after = floatval($value_before) + floatval($increment);
            $model->log_type = 2;  //1.调休 2.年假 3.病假
            if(!$model->save()){
                return $model->getErrors();
            }else{
                return new yii\base\Object();
            }
        }else{
            return $ret;
        }
    }
}