<?php

/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2015/10/23
 * Time: 17:35
 */

namespace app\commands;

use app\models\AttendanceModel;
use app\models\HolidayModel;
use app\modules\attendance\delegate\AttendanceDelegate;
use yii\console\Controller;
use Yii;
use yii\db\Query;

class AttendanceController extends Controller
{
    //同步sqlserver考勤记录到本地（每天6点同步一次）
    public function actionSync()
    {
//        $link = mssql_connect('ser2005', 'OA', 'zqlt2015');
//        if (!$link) {
//            echo 'sqlserver connect error';
//            die;
//        }
//        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        // $yesterday = date('Y-m-d', strtotime('-2 month'));
//        $yesterday = date("Y-m-d", strtotime("2016-2-26"));
//        $today = date("Y-m-d", strtotime("2016-3-26"));
//        $sql = " SELECT CONVERT(varchar(100), a.CHECKTIME, 20) as CHECKTIME,b.BADGENUMBER FROM CHECKINOUT a LEFT JOIN USERINFO b ON a.USERID=b.USERID WHERE a.CHECKTIME>='" . $yesterday . "' AND a.CHECKTIME<'" . $today . "'";
//        //查询出前一天的打卡记录
//        $query = mssql_query($sql, $link);
//        if (!$query) {
//            echo 'query result is empty';
//            die;
//        }
//        //插入oa_checktime表
//        while ($rowData = mssql_fetch_array($query, MSSQL_ASSOC)) {
//            if (!$rowData['BADGENUMBER']) {
//                continue;
//            }
//            $checktime = strtotime($rowData['CHECKTIME']);
//            $daystr = date("Y-m-d", $checktime);
//            $day = strtotime($daystr);
//            //如果在0点至早6点打卡  视为前一天的下班打卡时间
//            if ($checktime >= $day && $checktime < strtotime($daystr . ' 6:00:00')) {
//                $day = strtotime('-1 day', $day);
//            }
//            $sql = 'insert into oa_checktime values(NULL,' . $rowData['BADGENUMBER'] . ',' . $checktime . ',' . $day . ')';
//            Yii::$app->db->createCommand($sql)->execute();
//        }

        //处理同步数据，联合工作日表查询
        $res = (new Query())->select('MIN(c.checktime) AS onTime,MAX(c.checktime) AS offTime,a.u_id,a.card_no,a.real_name,b.weekDay,b.iswork,b.day')
            ->from('oa_members a')
            ->join('JOIN', 'oa_holiday b')
            ->leftJoin('oa_checktime c', 'a.card_no=c.badgenumber AND b.day=c.day')
            ->where('b.day=:begin', array(':begin' => strtotime($yesterday)))
//            ->where('b.day>=:begin AND b.day<:end', array(':begin' => strtotime($yesterday), ':end' => strtotime($today)))
            ->groupBy('a.u_id,b.day')
            ->orderBy(['a.u_id' => SORT_ASC])
            ->all();

        //插入考勤表
        foreach ($res as $key => $value) {
            if (!$value['card_no']) {
                continue;
            }
            $status = 1;
            //获取工作日时间设置
            $timeSet = AttendanceDelegate::getTimeSet();
            //正常上班时间
            $normalOnTime = strtotime(date('Y-m-d',$value['day']) .' '.$timeSet['begin_time'])+59;
            //$normalOnTime = strtotime(date('Y-m-d', $value['day']) . ' 9:10:59');
            //正常下班时间
            $normalOffTime = strtotime(date('Y-m-d',$value['day']) .' '.$timeSet['end_time']);
            //$normalOffTime = strtotime(date('Y-m-d', $value['day']) . ' 18:00:00');
            //正常上班日
            if ($value['iswork'] == 1) {
                //是否旷工
                if (empty($value['onTime']) && empty($value['offTime'])) {
                    $status = 5;
                } else {
                    //是否迟到且早退
                    if (!empty($value['onTime']) && $value['onTime'] > $normalOnTime && !empty($value['offTime']) && $value['offTime'] < $normalOffTime) {
                        $status = 4;
                    } else {
                        //是否迟到
                        if (!empty($value['onTime']) && $value['onTime'] > $normalOnTime) {
                            $status = 2;
                        }
                        //是否早退
                        if (!empty($value['offTime']) && $value['offTime'] < $normalOffTime) {
                            $status = 3;
                        }
                    }
                }
            } else {
                //节假日
                $status = 6;
            }

            //查询所属大组名
            $org_res = (new Query())->select('b.all_parent_id')->from('oa_org_member a')->leftJoin('oa_org b', 'a.org_id=b.org_id')->where(['a.u_id' => $value['u_id']])->one();
            $org_ids = explode(',', $org_res['all_parent_id']);
            $org_name = (new Query())->select('org_name')->from('oa_org')->where(['org_id' => $org_ids])->column();
            unset($org_name[0]);

            $atdc = new AttendanceModel();
            $atdc->u_id = $value['u_id'];
            $atdc->real_name = $value['real_name'];
            $atdc->onTime = $value['onTime'];
            $atdc->offTime = $value['offTime'];
            $atdc->workDate = $value['day'];
            $atdc->weekDay = $value['weekDay'];
            $atdc->status = $status;
            $atdc->card_no = $value['card_no'];
            $atdc->org_name = implode('-', $org_name);
            $res = $atdc->save(false);
            if (!$res) {
                echo 'attendance failed';
            }
        }
    }

    //每年生成一次节假日表  每年12月31号执行
    public function actionYear()
    {
//        $nextYear = date('Y');          //今年
        $nextYear = date('Y') + 1;      //明年
        $weekArr = array(
            1 => '星期一',
            2 => '星期二',
            3 => '星期三',
            4 => '星期四',
            5 => '星期五',
            6 => '星期六',
            7 => '星期日',
        );
        //计算明年一共有多少天
        $days = abs(strtotime($nextYear . '-01-01') - strtotime($nextYear . '-12-31')) / 24 / 60 / 60 + 1;
        for ($i = 0; $i < $days; $i++) {
            $day = strtotime($nextYear . '-01-01') + $i * 24 * 60 * 60;
            $n = date('N', $day);
            $iswork = 1;
            if ($n == 6 || $n == 7) {
                $iswork = 0;
            }
            $holiday = new HolidayModel();
            $holiday->day = $day;
            $holiday->weekDay = $weekArr[$n];
            $holiday->iswork = $iswork;
            $res = $holiday->save(false);
            if (!$res) {
                echo 'holiday failed';
            }
        }
    }

    //checktime表时间重算
    public function actionReexecute()
    {
        $yesterday = date("Y-m-d", strtotime("2016-2-26"));
        $today = date("Y-m-d", strtotime("2016-3-26"));
        $res = (new Query())->select('*')->from('oa_checktime')
            ->where('day>=:begin AND day<:end', array(':begin' => strtotime($yesterday), ':end' => strtotime($today)))
            ->all();

        foreach ($res as $key => $value) {
            $checktime = $value['checktime'];
            $daystr = date("Y-m-d", $checktime);
            $day = strtotime($daystr);
            //如果在0点至早6点打卡  视为前一天的下班打卡时间
            if ($checktime >= $day && $checktime < strtotime($daystr . ' 6:00:00')) {
                $day = strtotime('-1 day', $day);
                Yii::$app->db->createCommand()->update('oa_checktime',['day' => $day],['id' => $value['id']])->execute();
            }
        }
    }

    //重新生成考勤
    public function actionReatten($begin,$end)
    {
        $yesterday = date("Y-m-d", strtotime($begin));
        $today = date("Y-m-d", strtotime($end));

        //处理同步数据，联合工作日表查询
        $res = (new Query())->select('MIN(c.checktime) AS onTime,MAX(c.checktime) AS offTime,a.u_id,a.card_no,a.real_name,b.weekDay,b.iswork,b.day')
            ->from('oa_members a')
            ->join('JOIN', 'oa_holiday b')
            ->leftJoin('oa_checktime c', 'a.card_no=c.badgenumber AND b.day=c.day')
            ->where('b.day>=:begin AND b.day<:end', array(':begin' => strtotime($yesterday), ':end' => strtotime($today)))
            ->groupBy('a.u_id,b.day')
            ->orderBy(['a.u_id' => SORT_ASC])
            ->all();

        //插入考勤表
        foreach ($res as $key => $value) {
            if (!$value['card_no']) {
                continue;
            }
            $status = 1;
            //正常上班时间
            $normalOnTime = strtotime(date('Y-m-d', $value['day']) . ' 9:10:59');
            //正常下班时间
            $normalOffTime = strtotime(date('Y-m-d', $value['day']) . ' 18:00:00');
            //正常上班日
            if ($value['iswork'] == 1) {
                //是否旷工
                if (empty($value['onTime']) && empty($value['offTime'])) {
                    $status = 5;
                } else {
                    //是否迟到且早退
                    if (!empty($value['onTime']) && $value['onTime'] > $normalOnTime && !empty($value['offTime']) && $value['offTime'] < $normalOffTime) {
                        $status = 4;
                    } else {
                        //是否迟到
                        if (!empty($value['onTime']) && $value['onTime'] > $normalOnTime) {
                            $status = 2;
                        }
                        //是否早退
                        if (!empty($value['offTime']) && $value['offTime'] < $normalOffTime) {
                            $status = 3;
                        }
                    }
                }
            } else {
                //节假日
                $status = 6;
            }

            //查询所属大组名
            $org_res = (new Query())->select('b.all_parent_id')->from('oa_org_member a')->leftJoin('oa_org b', 'a.org_id=b.org_id')->where(['a.u_id' => $value['u_id']])->one();
            $org_ids = explode(',', $org_res['all_parent_id']);
            $org_name = (new Query())->select('org_name')->from('oa_org')->where(['org_id' => $org_ids])->column();
            unset($org_name[0]);

            $atdc = new AttendanceModel();
            $atdc->u_id = $value['u_id'];
            $atdc->real_name = $value['real_name'];
            $atdc->onTime = $value['onTime'];
            $atdc->offTime = $value['offTime'];
            $atdc->workDate = $value['day'];
            $atdc->weekDay = $value['weekDay'];
            $atdc->status = $status;
            $atdc->card_no = $value['card_no'];
            $atdc->org_name = implode('-', $org_name);
            $res = $atdc->save(false);
            if (!$res) {
                echo 'attendance failed';
            }
        }
    }

    //手动同步前一天考勤
    public function actionChecktime($begin,$end)
    {
        $link = mssql_connect('ser2005', 'OA', 'zqlt2015');
        if (!$link) {
           echo 'sqlserver connect error';
           die;
        }
        $today = $end.' 6:00:00';
        $yesterday = $begin.' 6:00:00';
        $sql = " SELECT CONVERT(varchar(100), a.CHECKTIME, 20) as CHECKTIME,b.BADGENUMBER FROM CHECKINOUT a LEFT JOIN USERINFO b ON a.USERID=b.USERID WHERE a.CHECKTIME>='" . $yesterday . "' AND a.CHECKTIME<'" . $today . "'";
        //查询出前一天的打卡记录
        $query = mssql_query($sql, $link);
        if (!$query) {
            echo 'query result is empty';
            die;
        }
        //插入oa_checktime表
        while ($rowData = mssql_fetch_array($query, MSSQL_ASSOC)) {
            if (!$rowData['BADGENUMBER']) {
                continue;
            }
            $checktime = strtotime($rowData['CHECKTIME']);
            $daystr = date("Y-m-d", $checktime);
            $day = strtotime($daystr);
            //如果在0点至早6点打卡  视为前一天的下班打卡时间
            if ($checktime >= $day && $checktime < strtotime($daystr . ' 6:00:00')) {
                $day = strtotime('-1 day', $day);
            }
            $sql = 'insert into oa_checktime values(NULL,' . $rowData['BADGENUMBER'] . ',' . $checktime . ',' . $day . ')';
            Yii::$app->db->createCommand($sql)->execute();
        }
    }

}