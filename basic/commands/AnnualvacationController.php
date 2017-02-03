<?php

/**
 * Created by PhpStorm.
 * User: liaoshuochao
 * Date: 2016/05/24
 * Time: 17:35
 */

namespace app\commands;

use app\models\CalVacationModel;
use app\models\MembersModel;
use yii\console\Controller;
use Yii;

class AnnualvacationController extends Controller
{
    private $_calModel;
    const PER_COUNT = 100;
    const STATUS_EFFECTIVE = 1;
    const STATUS_IS_DEDETE = 0;
    public function init()
    {
        $this->_calModel = new CalVacationModel();
    }

    //入口
    public function actionIndex($action){
        $action = strtolower($action);
        if (!in_array($action, ['calvacation', 'delovertime', 'flush', 'calvacationall', 'manualdelovertime', 'manalflush'])) {
            echo 'action is not exists';
            exit();
        }
        switch ($action) {
            case 'calvacation':
                $this->actionCalvacation('not_all');
                break;
            case 'delovertime':
                $this->actionDelOverTime('');
                break;
            case 'manualdelovertime':
                $this->actionDelOverTime('manual');
                break;
            case 'flush':
                $this->actionFlushVacation('');
                $this->actionCalvacation('all');
                break;
            case 'manalflush':
                $this->actionFlushVacation('manual');
                $this->actionCalvacation('all');
                break;
            case 'calvacationall':
                $this->actionCalvacation('all');
                break;
        }
    }

    /**
     * 计算年假，每天凌晨
     * @param $type :'all',处理所有有效用户;其他，处理未满一年的员工
     * */
    public function actionCalvacation($type){
        $member = new MembersModel();
        $entryTimeStart = (date('Y', time()) - 1) . date('md', time());
       // $entryTimeStart = (date('Y', time()) - 1) . '-1-1';
        $conditions = ['status' => self::STATUS_EFFECTIVE, 'is_del' => self::STATUS_IS_DEDETE, 'entry_time' => $entryTimeStart];
        $conditionType = 2;
        if ('all' == $type) {
            $conditions = ['status' => self::STATUS_EFFECTIVE, 'is_del' => self::STATUS_IS_DEDETE];
            $conditionType = 1;
        }
        $totalCount = $member::getMembersCount('u_id, entry_time', $conditions, $conditionType);
        $totalCalTimes = ceil($totalCount / self::PER_COUNT);

        //每次操作self::PER_COUNT条，执行$totalCalTimes次
        for ($num = 1; $num <= $totalCalTimes; $num ++) {
            $users = $member::getMembersByCondition('u_id, entry_time', $conditions, $conditionType, self::PER_COUNT + 1, $num);
            foreach ($users as $key => $val) {
                //获取当前年假数（减去已休年假）
                if (!$val['entry_time'] || $val['entry_time'] == 'null') {
                    $val['entry_time'] = date('Y-m-d', time());
                }
                $annualVacation = $this->_calModel->calculateAnnualVacation($val['u_id'], $val['entry_time']);
                /*if($val['u_id'] == 3) {
                    file_put_contents('aaa.txt',var_export($annualVacation),FILE_APPEND);
                }*/
                $msg = true;
                if($annualVacation['annual_vacation']){
                    $msg = $this->_calModel->addAnnualLeave($val['u_id'], $annualVacation['annual_vacation']);
                }
                if (true != $msg) {
                    Yii::error($msg);
                }
            }
        }

        Yii::info('CalVacation over!');
    }

    /**
     * 清空过期顺延年假，每年4月1日凌晨
     * @param $type :'manual',手动处理;
     * */
    public function actionDelOverTime($type)
    {
        if ('0401' != date('md') && 'manual' != $type) {
            echo '不是4月1号';
            exit();
        }
        $totalCount = $this->_calModel->find()->count();
        $totalCalTimes = ceil($totalCount / self::PER_COUNT);
        for ($num = 1; $num <= $totalCalTimes; $num ++) {
            $annualLeaveModels = $this->_calModel->getAnnualLeaveList('*', [], self::PER_COUNT, $num);
            foreach ($annualLeaveModels as $key => $val) {
                $this->_calModel->delDelayLeave($val);
            }
        }

        Yii::info('DelOverTime over!');
    }

    /**
     * 清空过期顺延年假，每年1月1日凌晨
     * @param $type :'manual',手动处理;
     * */
    public function actionFlushVacation($type)
    {
        if ('0101' != date('md') && 'manual' != $type) {
            echo '不是1月1号';
            exit();
        }
        $totalCount = $this->_calModel->find()->count();
        $totalCalTimes = ceil($totalCount / self::PER_COUNT);
        for ($num = 1; $num <= $totalCalTimes; $num ++) {
            $annualLeaveModels = $this->_calModel->getAnnualLeaveList('*', [], self::PER_COUNT, $num);
            foreach ($annualLeaveModels as $key => $val) {
                $this->_calModel->flushAnnualLeave($val);
            }
        }
        Yii::info('FlushVacation over!');
    }


}