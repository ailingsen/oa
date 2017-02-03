<?php

namespace app\modules\project\helper;

//控制器辅助类

use app\lib\Tools;

class ProjectHelper {
    const PAGE_SIZE1 =9;
    const PAGE_SIZE2 =9;
    const PAGE_SIZE3 =5;
    const PAGE_SIZE4 =10;
    const PAGE_SIZE5 =10000;

    /**
     * 处理翻页数据
    */
    public static function setPage($type,$page)
    {
        $res=['offset'=>0,'limit'=>self::PAGE_SIZE1];
        if($type==2){
            $res['offset'] = self::PAGE_SIZE2 * ($page - 1);
            $res['limit'] =self::PAGE_SIZE2;
        }else if($type==1){
            $res['offset'] = self::PAGE_SIZE1 * ($page - 1);
            $res['limit'] =self::PAGE_SIZE1;
        }else if($type==3){
            $res['offset'] = self::PAGE_SIZE3 * ($page - 1);
            $res['limit'] =self::PAGE_SIZE3;
        }else if($type==4){
            $res['offset'] = self::PAGE_SIZE4 * ($page - 1);
            $res['limit'] =self::PAGE_SIZE4;
        }else if($type==5){
            $res['offset'] = self::PAGE_SIZE5 * ($page - 1);
            $res['limit'] =self::PAGE_SIZE5;
        }
        return $res;
    }

    /**
     * 处理项目数据
    */
    public static function setProData($data)
    {
        if(is_array($data)){
            foreach($data as $key=>$val)
            {
                //处理项目的状态
                $data[$key]['status'] = self::setProStatus($val);
                //处理项目的进度
                if (isset($val['task'])) {
                    $data[$key]['degree'] = self::setProDegree($val['task']);
                } else {
                    $data[$key]['degree']['degree'] = 0;
                }
                //处理项目时间
                $data[$key] = self::setTimeFormat($data[$key]);
            }
        }
        return $data;
    }

    //isStart是否保留未开始的项目
    public static function getStatisticCount($data,$isStart = 0)
    {
        $notStartCount = 0;
        $doingCount = 0;
        $overTimeCount = 0;
        $finishCount = 0;
        if(is_array($data)){
            foreach($data as $key=>$val) {
                //处理项目的状态 1未开始    2进行中-正常   3进行中-超时   4已完成
                switch (self::setProStatus($val)) {
                    case 1:
                        $notStartCount ++;
                        if($isStart == 0){
                            unset($data[$key]);
                        }
                        break;
                    case 2:
                        $doingCount ++;
                        break;
                    case 3:
                        $doingCount ++;
                        break;
                    case 4:
                        $finishCount ++;
                        break;
                }
            }
        }
        return ['not_start' => $notStartCount,'doing' => $doingCount, 'overtime' => $overTimeCount, 'finish' => $finishCount, 'list' => $data];
    }

    /**
     * 处理项目时间格式化
    */
    public static function setTimeFormat($data)
    {
        if($data['delay_time']==0){
            $data['delay_time_f'] = '';
        }else{
            $data['delay_time_f'] = date('Y-m-d H:i',$data['delay_time']);
        }
        $data['begin_time_f'] = date('Y-m-d H:i',$data['begin_time']);
        $data['end_time_f'] = date('Y-m-d H:i',$data['end_time']);
        return $data;
    }

    /**
     * 处理项目成员图像
    */
    public static function setHeadImg($arrProMem)
    {
        foreach($arrProMem as $key=>$val){
            $arrProMem[$key]['head_img_path'] = Tools::getHeadImg($val['head_img']);
        }
        return $arrProMem;
    }
    /**
     * 处理项目的状态
     * $proinfo  array项目信息
     * 1未开始    2进行中-正常   3进行中-超时   4已完成
    */
    public static function setProStatus($proinfo)
    {
        $time = time();
        if($proinfo['complete']==1){
            return 4;
        }
        if($proinfo['begin_time']<=$time && $proinfo['end_time']>=$time){
            return 2;
        }
        if($proinfo['begin_time']>$time){
            return 1;
        }
        if($proinfo['end_time']<$time){
            return 3;
        }
    }

    /**
     * 处理项目的进度
     * $taskinfo  array项目所有任务数据
    */
    public static function setProDegree($taskinfo)
    {
        //完成数
        $fcount=0;
        //任务总数
        $count=count($taskinfo);
        foreach($taskinfo as $k=>$v){
            if($v['status']==4){
                $fcount++;
            }
        }
        //计算项目的进度
        if($count==0){
            return ['count'=>$count,'fcount'=>$fcount,'degree'=>0];
        }else{
            return ['count'=>$count,'fcount'=>$fcount,'degree'=>sprintf("%.2f",$fcount/$count)*100];
        }
    }

    /**
     * 统计项目成员任务数据
    */
    public static function getProMemTaskInfo($proMember,$proTask)
    {
        foreach($proMember as $key=>$val){
            //总任务数
            $proMember[$key]['count1']=0;
            //进行中
            $proMember[$key]['count2']=0;
            //已完成
            $proMember[$key]['count3']=0;
            foreach($proTask as $k=>$v){
                if($val['u_id']==$v['charger'] && $v['status']!=0 && $v['status']!=1 && $v['status']!=6){
                    $proMember[$key]['count1']++;//总任务数
                    if($v['status']==2){//进行中
                        $proMember[$key]['count2']++;
                    }else if($v['status']==4){//已完成
                        $proMember[$key]['count3']++;
                    }
                    unset($proTask[$k]);
                }
            }
        }
        return $proMember;
    }

    /**
     * 处理甘特图要显示的数据
    */
    public static function setGanttData($arrTask)
    {
        $proTaskInfo =[];
        $temp = [];
        foreach($arrTask as $key=>$val){
            $temp = [];
            $temp['name']=$val['task_title'];
            $temp['leader'] = $val['real_name'];
            $temp['from'] =date('Y-m-d-H-i-s',$val['begin_time']);
            $temp['to'] = date('Y-m-d-H-i-s',$val['end_time']);
            //处理进度条颜色
            switch($val['status']){
                case 1://待接受
                    $temp['color']='#9cd6ec';
                    break;
                case 2://进行中
                    $temp['color']='#57c8f2';
                    break;
                case 3://待审核
                    $temp['color']='#6ccac9';
                    break;
                case 4://已完成
                    $temp['color']='#82e481';
                    break;
                case 5://已关闭
                    $temp['color']='#b8b8b8';
                    break;
                case 6://已拒绝
                    $temp['color']='#ff6c60';
                    break;
            }
            $proTaskInfo[$key]['name']=$val['task_title'];
            $proTaskInfo[$key]['tasks'][]=(object)$temp;
        }
        return$proTaskInfo;
    }

    /**
     * 处理项目成员数据(添加和编辑项目)
    */
    public static function setAddProMem($arrMem,$pro_id)
    {
        $time =time();
        foreach($arrMem as $key=>$val){
            unset($arrMem[$key]['real_name']);
            unset($arrMem[$key]['head_img']);
            unset($arrMem[$key]['$$hashKey']);
            $arrMem[$key]['add_time'] = $time;
            $arrMem[$key]['pro_id'] = $pro_id;
        }
        return $arrMem;
    }

    /**
     * 根据时间来判断是周几
    */
    public static function setWeekStatus($arr)
    {
        $weekArr = array(
            '1' => '周一',
            '2' => '周二',
            '3' => '周三',
            '4' => '周四',
            '5' => '周五',
            '6' => '周六',
            '7' => '周日',
        );
        foreach($arr as $key=>$val){
            $n = date('N',$val['create_time']);
            $arr[$key]['week'] = $weekArr[$n];
            $arr[$key]['date'] = date('Y/m/d',$val['create_time']);
            $arr[$key]['time'] = date('H:i:s',$val['create_time']);
        }
        return $arr;
    }

    /**
     * 时间格式化
    */
    public static function setDateFormat($date)
    {
        return date('Y-m-d H:i',$date);
    }

    /**
     *处理项目进度页任务信息
    */
    public static function setTaskInfo($data)
    {
        foreach($data as $key=>$val){
            $data[$key]['create_time'] = self::setDateFormat($val['create_time']);
        }
        return $data;
    }

    /**
     *处理项目任务页任务信息
     */
    public static function setTaskListInfo($data)
    {
        foreach($data as $key=>$val){
            $data[$key]['create_time'] = self::setDateFormat($val['create_time']);
            //$data[$key]['statusstr'] = self::setTaskStatusStr($val['status']);
        }
        return $data;
    }

    /**
     * 根据任务的状态码，返回任务的状态信息
    */
    public static function setTaskStatusStr($status){
        $arrStatusStr =[
            '1'=>'待接受',
            '2'=>'进行中',
            '3'=>'待审核',
            '4'=>'已完成',
            '5'=>'已关闭',
            '6'=>'已拒绝'
        ];
        return $arrStatusStr[$status];
    }

    /**
     * 发消息处理消息菜单
    */
    public static function setMsgMenu($proMember,$type=0)
    {
        foreach($proMember as $key=>$val)
        {
            if($type == 1){
                $proMember[$key]['menu'] = 0;
            }else{
                if($val['owner']==1){
                    $proMember[$key]['menu'] = 1;
                }else{
                    $proMember[$key]['menu'] = 2;
                }
            }
        }
        return $proMember;
    }

}