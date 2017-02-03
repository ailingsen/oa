<?php
/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2016/11/15
 * Time: 16:12
 */

namespace app\modules\v1\delegate;

use app\models\ApplyOvertimeModel;
use app\models\ApplyLeaveModel;
use app\models\ApplyCheckoutModel;
use app\models\ApplyFlexworkModel;
use yii\base\Object;

class AppApplyDelegate
{
    /**
     * 获取申请列表申请基本信息
     * @param $apply
     * @return $this|array
     */
    public static function getBaseDetail($apply) {
        $query = '';
        switch($apply['model_id']) {
            //加班
            case 1:
                $query = ApplyOvertimeModel::find()->select(['type','begin_time','end_time']);
                break;
            //请假
            case 2:
                $query = ApplyLeaveModel::find()->select(['type','begin_time','end_time']);
                break;
            //忘打卡
            case 3:
                $query = ApplyCheckoutModel::find()->select(['check_date','is_am']);
                break;
            //弹性
            case 4:
                $query = ApplyFlexworkModel::find()->select(['begin_time','end_time']);
                break;
//            default:
//                break;
        }
        if($query) {
            return $query->where(['id' => $apply['detail_id']])->asArray()->one();
        }else {
            return [];
        }
    }
}