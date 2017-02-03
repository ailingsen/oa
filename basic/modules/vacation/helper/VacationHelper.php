<?php
/**
 * Created by PhpStorm.
 * User: liaoshuochao
 * Date: 2016/8/1
 * Time: 15:41
 */
namespace app\modules\vacation\helper;

use app\lib\FResponse;
use app\models\ApplyBaseModel;
use app\models\AttendanceModel;
use app\models\VacationUsedModel;
use app\modules\vacation\delegate\VacationDelegate;
use yii;

Class VacationHelper
{
    /**
     * @param $params
     * @param string $type
     * @return array
     */
    public static function statistic($params, $type = '')
    {
        //设置时区
        date_default_timezone_set('Asia/Shanghai');

        //超时设置
        set_time_limit(0);

        $orgId = isset($params['org_id']) ? $params['org_id'] : '';
        $searchName = isset($params['search_name']) ? $params['search_name'] : '';
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['page_size']) ? $params['page_size'] : 10;
        $starTime = isset($params['start_time']) ? $params['start_time'] : date('Ymd H:i', mktime(0, 0, 0, date("m"), 1, date("Y")));
        $endTime = isset($params['end_time']) ? $params['end_time'] : date('Ymd H:i', time());
        $starTime = strtotime($starTime);
        $endTime = strtotime($endTime);
        if ($endTime && $endTime < $starTime) {
            FResponse::output(['code' => 20066, 'msg' => "结束时间不能小于开始时间"]);
        }

        //要统计的人
        $members = VacationDelegate::getMembers($orgId, $page, $pageSize, $searchName);
        $totalPage = $members['totalPage'];
        $members = $members['list'];

        //开始统计
        foreach ($members as $key => $member) {
            //考勤统计
            $attendanceData = AttendanceModel::statistic($member['u_id'], $starTime, $endTime);
            $members[$key]['attendance'] = self::doAttendanceData($attendanceData);
            $members[$key]['attendance']['unpunch'] = self::getUnpunch($member['u_id'], $starTime, $endTime);//漏打卡

            //年假(天)	事假(天)	调休假(天)	带薪病假(天)	病假(天)	哺乳假(天)	婚假(天)	产假(天)	陪产假(天)	丧产假(天)
            $vacationData = AttendanceModel::statisticVacation($member['u_id'], $starTime, $endTime);
            $members[$key]['vacation'] = self::doVacationData($vacationData);

            if ($type == 'excel') {
                $members[$key] = self::doExcelData($members[$key]);
            }
        }
        return ['vacation_list' => $members, 'page' => $page, 'total_page' => $totalPage];
    }

    /**
     * 整理考勤数据
     * @param $attendanceData
     * @return array
     */
    public static function doAttendanceData($attendanceData)
    {
        $attendanceName = ['1' => 'normal', '2' => 'later', '3' => 'leave_early', '4' => 'later_early', '5' => 'absent', '6' => 'holiday', '7' => 'leave', '8' => 'exception', '9' => 'unpunch', '10' => 'absent'];
        $attendance = ['normal' => 0, 'unpunch' => 0, 'later' => 0, 'leave_early' => 0, 'later_early' => 0, 'absent' => 0, 'holiday' => 0, 'leave' => 0, 'exception' => 0];
        //考勤状态 1 正常 2 迟到 3 早退 4 迟到早退 5旷工 6 节假日  7请假  8异常
        foreach ($attendanceData as $item) {
            foreach ($attendanceName as $key => $val) {
                if ($item['status'] == $key) {
                    $attendanceName[$item['status']] = $item['num'];
                    $attendance[$val] = $item['num'];
                    if ($item['status'] == 4) {//当状态为迟到早退时，迟到、早退都增加相应的数量
                        $attendance['later'] += $item['num'];
                        $attendance['leave_early'] += $item['num'];
                    }
                }
            }
        }
        return $attendance;
    }

    /**
     * 整理假期数据
     * @param $vacationData
     * @return array
     */
    public static function doVacationData($vacationData)
    {
        $vacationName = ['1' => 'annual', '5' => 'leave', '2' => 'overtime', '3' => 'paid_sick_leave', '4' => 'sick_leave', '10' => 'lactation_leave', '7' => 'marriage_leave', '8' => 'maternity_leave', '12' => 'accompany_leave', '13' => 'mourning_leave'];
        $vacation = ['annual' => 0, 'leave' => 0, 'overtime' => 0, 'paid_sick_leave' => 0, 'sick_leave' => 0, 'lactation_leave' => 0, 'marriage_leave' => 0, 'maternity_leave' => 0, 'accompany_leave' => 0, 'mourning_leave' => 0];

        //substatus:休假类型  1年假 2调休 3带薪病假 4病假  5事假 7婚假 8产假 10哺乳假  12陪产假  13丧假
        foreach ($vacationData as $item) {
            foreach ($vacationName as $key => $val) {
                if ($item['type'] == $key) {
                    $vacationName[$item['type']] = $item['num'];
                    $vacation[$val] = $item['num'];
                }
            }
        }
        return $vacation;
    }

    /**
     * 漏打卡
     * @param $uid
     * @return int
     */
    public static function getUnpunch($uid, $starTime, $endTime)
    {
        $query = ApplyBaseModel::find()->leftJoin('oa_apply_checkout', 'oa_apply_base.detail_id=oa_apply_checkout.id')
            ->where(['oa_apply_base.model_id' => 3, 'oa_apply_base.status' => 1, 'oa_apply_base.applyer' => $uid]);
        if ($starTime && $endTime) {
            $query->andWhere(['between', 'oa_apply_checkout.check_date', $starTime, $endTime]);
        }
        $unpunch = $query->count();
        return $unpunch;
    }

    public static function doExcelData($statisticData)
    {
        $attendance = ['normal' => 0, 'unpunch' => 0, 'later' => 0, 'leave_early' => 0];
        $vacation = ['annual' => 0, 'leave' => 0, 'overtime' => 0, 'paid_sick_leave' => 0, 'sick_leave' => 0, 'lactation_leave' => 0, 'marriage_leave' => 0, 'maternity_leave' => 0, 'accompany_leave' => 0, 'mourning_leave' => 0];
        $attendanceData = $statisticData['attendance'];
        $vacationeData = $statisticData['vacation'];
        unset($statisticData['attendance']);
        unset($statisticData['vacation']);

        return array_merge($statisticData, array_intersect_key($attendanceData, $attendance), array_intersect_key($vacationeData, $vacation));
    }

    /**
     * 获取Excel导出数据
     */
    public static function getExportexcel()
    {

        $postdata = Yii::$app->request->get('args');
        $data = json_decode($postdata, true);
        $starTime = isset($data['start_time']) ? $data['start_time'] : date('Ymd H:i', mktime(0, 0, 0, date("m"), 1, date("Y")));
        $endTime = isset($data['end_time']) ? $data['end_time'] : date('Ymd H:i', time());
        $data['search_name'] = isset($data['search_name']) ? $data['search_name'] : '';
        $data['page_size'] = $pageSize = 65536;
        $starTime = strtotime($starTime);
        $endTime = strtotime($endTime);
        $differMonth = date('m', $endTime - $starTime); //整数月
        /*if ($differMonth > 6) {
            die($differMonth . '起止时间不能超过6个月');
        }*/

        $statisticList = self::statistic($data, 'excel');
        $tableName = '考勤统计';
        $date = date("Ymd", time());
        $tableName .= "_{$date}.xls";
        $headArr = ['序号',
            '姓名',
            '正常上班天数',
            '忘打卡次数',
            '迟到次数',
            '早退次数',
            '年假(天)',
            '事假(天)',
            '调休假(天)',
            '带薪病假(天)',
            '病假(天)',
            '哺乳假(天)',
            '婚假(天)',
            '产假(天)',
            '陪产假(天)',
            '丧假(天)'];
        self::getExcel($tableName, $headArr, $statisticList['vacation_list']);
    }

    /**
     * 输出Excel
     * @param $filename
     * @param $headArr
     * @param $data
     */
    public static function getExcel($filename, $headArr, $data, $is_check=0)
    {
        if (empty($data) || !is_array($data)) {
            die("没有需要导出的内容");
        }
        if($is_check==1){
            die('有数据');
        }

        require FILE_ROOT . '/vendor/phpexcel/PHPExcel.php';
        //创建新的PHPExcel对象
        $objPHPExcel = new \PHPExcel();
        //设置列名
        $key = ord("A");
        $key2 = ord("@");
        foreach ($headArr as $value) {
            if ($key > ord("Z")) {
                $key2 += 1;
                $key = ord("A");
                $colum = chr($key2) . chr($key);//超过26个字母时才会启用
            } else {
                if ($key2 >= ord("A")) {
                    $colum = chr($key2) . chr($key);//超过26个字母时才会启用
                } else {
                    $colum = chr($key);
                }
            }
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $value);
            $key += 1;
        }
        //设置列值
        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach ($data as $key => $rows) { //行写入
            $span = ord("A");
            $span2 = ord("@");
            foreach ($rows as $k => $value) {// 列写入
                if ($span > ord("Z")) {
                    $span2 += 1;
                    $span = ord("A");
                    $j = chr($span2) . chr($span);//超过26个字母时才会启用
                } else {
                    if ($span2 >= ord("A")) {
                        $j = chr($span2) . chr($span);//超过26个字母时才会启用
                    } else {
                        $j = chr($span);
                    }
                }
                if ($k !== 'u_id') {
                    $objActSheet->setCellValue($j . $column, $value);
                } else {
                    $objActSheet->setCellValue($j . $column, $column - 1);
                }
                $span++;
            }
            $column++;
        }
        require FILE_ROOT . '/vendor/phpexcel/PHPExcel/IOFactory.php';
        //输出文件
        $excelWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        $ua = $_SERVER["HTTP_USER_AGENT"];
        $encoded_filename = urlencode($filename);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);

        // 从浏览器直接输出$filename
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type: application/vnd.ms-excel;");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=" . $filename);
        header("Content-Transfer-Encoding:binary");
        $excelWriter->save("php://output");
    }
}