<?php

namespace app\modules\attendance\delegate;


//模型委托类。 处理控制器和动作列表

use app\models\ApplyOvertimeModel;
use app\models\AttendanceModel;
use app\models\ChecktimeModel;
use app\models\MembersModel;
use app\models\OrgModel;
use app\models\WorkSetModel;
use Yii;

class AttendanceDelegate {

    /**
     * 获取我的考勤
    */
    public static function getMyAttend($u_id,$limit,$offset,$data,$timeSet)
    {
        $checmModel = ChecktimeModel::find()->select('MAX(oa_checktime.checktime) as offTime,MIN(oa_checktime.checktime) as onTime,oa_checktime.day as showDate,oa_members.real_name ')
            ->leftJoin('oa_members','card_no=oa_checktime.badgenumber')->where('oa_members.u_id=:u_id',[':u_id'=>$u_id]);
        $checmCountModel = ChecktimeModel::find()->leftJoin('oa_members','card_no=oa_checktime.badgenumber')->where('oa_members.u_id=:u_id',[':u_id'=>$u_id]);
        if(isset($data['begin_time']) && !empty($data['begin_time'])){//开始时间查询
            $begin_time = strtotime($data['begin_time']);
            $checmModel->andWhere(['>=','oa_checktime.day',$begin_time]);
            $checmCountModel->andWhere(['>=','oa_checktime.day',$begin_time]);
        }
        if(isset($data['end_time']) && !empty($data['end_time'])){//结束时间
            $end_time = strtotime(date('Y-m-d', strtotime('+1 day', strtotime($data['end_time']))));
            $checmModel->andWhere(['<=','oa_checktime.day',$end_time]);
            $checmCountModel->andWhere(['<=','oa_checktime.day',$end_time]);
        }

        $checmModel->groupBy('oa_checktime.day,oa_members.real_name');
        $checmCountModel->groupBy('oa_checktime.day,oa_members.real_name');

        if(isset($data['status']) && !empty($data['status'])){//状态查询  1正常  2异常
            //开始时间戳
            $arrBegin = explode(':',$timeSet['begin_time']);
            $begin_unix =$arrBegin[0]*60*60+$arrBegin[1]*60+60;
            $arrEnd = explode(':',$timeSet['end_time']);
            $end_unix =$arrEnd[0]*60*60+$arrEnd[1]*60;
            if($data['status']==1){//正常
                $checmModel->having("MIN(oa_checktime.checktime) < oa_checktime.day+{$begin_unix} and MAX(oa_checktime.checktime) >= oa_checktime.day+{$end_unix}");
                $checmCountModel->having("MIN(oa_checktime.checktime) < oa_checktime.day+{$begin_unix} and MAX(oa_checktime.checktime) >= oa_checktime.day+{$end_unix}");
            }else if($data['status']==2){//异常
                $checmModel->having("MIN(oa_checktime.checktime) >= oa_checktime.day+{$begin_unix} or MAX(oa_checktime.checktime) < oa_checktime.day+{$end_unix}");
                $checmCountModel->having("MIN(oa_checktime.checktime) >= oa_checktime.day+{$begin_unix} or MAX(oa_checktime.checktime) < oa_checktime.day+{$end_unix}");
            }
        }

        $res['myattendList'] = $checmModel->offset($offset)->limit($limit)->orderBy('oa_checktime.day desc')->asArray()->all();
        $res['page']['sumPage'] = ceil($checmCountModel->count()/$limit);
        return $res;
    }

    /**
     * 获取所有员工考勤
    */
    public static function getAllAttend($limit,$offset,$data)
    {
        $attendModel = AttendanceModel::find()->leftJoin('oa_members','oa_members.u_id=oa_attendance.u_id')->where('oa_attendance.u_id!=1 and oa_members.is_del!=1');
        if(isset($data['begin_time']) && !empty($data['begin_time'])){
            $begin_time = strtotime($data['begin_time']);
            $attendModel->andWhere(['>=','oa_attendance.onTime',$begin_time]);
            //$checmCountModel->andWhere(['>','oa_checktime.checktime',$begin_time]);
        }

        if(isset($data['end_time']) && !empty($data['end_time'])){
            //$end_time = strtotime(date('Y-m-d', strtotime('+1 day', strtotime($data['end_time']))));
            $end_time = strtotime($data['end_time']);
            $attendModel->andWhere(['<=','oa_attendance.offTime',$end_time]);
            //$checmCountModel->andWhere(['<','oa_checktime.checktime',$end_time]);
        }

        if(isset($data['search_u_id']) && !empty($data['search_u_id'])){
            $attendModel->andWhere('oa_attendance.u_id=:u_id',[':u_id'=>$data['search_u_id']]);
        }else{
            if(isset($data['search_org_id']) && !empty($data['search_org_id'])){
                $orgInfo = OrgModel::find()->where('org_id=:org_id',[':org_id'=>$data['search_org_id']])->asArray()->one();
                $arrOrgChildId = explode(',',$orgInfo['all_children_id']);
                $attendModel->leftJoin('oa_org_member','oa_org_member.u_id=oa_attendance.u_id')->andWhere(['in','oa_org_member.org_id',$arrOrgChildId]);
            }
        }

        $res['attendList'] = $attendModel->offset($offset)->limit($limit)->orderBy('oa_attendance.workDate desc,oa_members.u_id asc')->asArray()->all();
        $res['page']['sumPage'] = ceil($attendModel->count()/$limit);
        return $res;
    }

    /**
     * 获取工作日时间设置
    */
    public static function getTimeSet()
    {
        $res = WorkSetModel::find()->where('id=1')->asArray()->one();
        return $res;
    }

    /**
     * 获取搜索组信息
    */
    public static function getOrgList($keyword='')
    {
        $res = OrgModel::getOrgInfoList($keyword, 'oa_org.org_id,oa_org.org_name', 1);
        return $res;
    }

    /**
     * 获取搜索用户信息
    */
    public static function getMemberList($keyword='',$org_id=0)
    {
        $res = MembersModel::getMemberList($keyword,$org_id,'oa_members.u_id,oa_members.h_id, oa_members.real_name,oa_members.head_img,oa_members.h_id');
        return $res;
    }

    /**
     * 获取考勤设置
     */
    public static function getAttendSet()
    {
        $res = WorkSetModel::find()->where('id=1')->asArray()->one();
        return $res;
    }

    /**
     * 获取考勤统计数据
    */
    public static function getAttendCount($limit,$offset,$data)
    {
        $aoModel = ApplyOvertimeModel::find()->select('oa_apply_overtime.id,oa_members.real_name,oa_org.org_name,oa_apply_overtime.begin_time,oa_apply_overtime.end_time')
            ->leftJoin('oa_members','oa_members.u_id=oa_apply_overtime.uid')
            ->leftJoin('oa_org_member','oa_org_member.u_id=oa_members.u_id')
            ->leftJoin('oa_org','oa_org.org_id=oa_org_member.org_id')
            ->leftJoin('oa_apply_base','oa_apply_base.detail_id=oa_apply_overtime.id and oa_apply_base.model_id=1')
            ->where('oa_apply_base.status=1');
        if(isset($data['begin_time']) && !empty($data['begin_time'])){
            $begin_time = strtotime($data['begin_time']);
            $aoModel->andWhere(['>=','oa_apply_overtime.begin_time',$begin_time]);
        }

        if(isset($data['end_time']) && !empty($data['end_time'])){
            $end_time = strtotime($data['end_time']);
            $aoModel->andWhere(['<=','oa_apply_overtime.end_time',$end_time]);
        }

        if(isset($data['search_u_id']) && !empty($data['search_u_id'])){
            $aoModel->andWhere('oa_apply_overtime.uid=:u_id',[':u_id'=>$data['search_u_id']]);
        }else{
            if(isset($data['search_org_id']) && !empty($data['search_org_id'])){
                $orgInfo = OrgModel::find()->where('org_id=:org_id',[':org_id'=>$data['search_org_id']])->asArray()->one();
                $arrOrgChildId = explode(',',$orgInfo['all_children_id']);
                $aoModel->andWhere(['in','oa_org_member.org_id',$arrOrgChildId]);
            }
        }

        $res['attendList'] = $aoModel->offset($offset)->limit($limit)->orderBy('oa_apply_overtime.begin_time desc')->asArray()->all();
        $res['page']['sumPage'] = ceil($aoModel->count()/$limit);
        return $res;
    }

    /**
     * 获取考勤统计数据(导出EXCEL)
     */
    public static function getAttendCountExp($data)
    {
        $aoModel = ApplyOvertimeModel::find()->select('oa_members.real_name,oa_org.org_name,oa_apply_overtime.begin_time,oa_apply_overtime.end_time')
            ->leftJoin('oa_members','oa_members.u_id=oa_apply_overtime.uid')
            ->leftJoin('oa_org_member','oa_org_member.u_id=oa_members.u_id')
            ->leftJoin('oa_org','oa_org.org_id=oa_org_member.org_id')
            ->leftJoin('oa_apply_base','oa_apply_base.detail_id=oa_apply_overtime.id and oa_apply_base.model_id=1')
            ->where('oa_apply_base.status=1');
        if(isset($data['begin_time']) && !empty($data['begin_time'])){
            $begin_time = strtotime($data['begin_time']);
            $aoModel->andWhere(['>=','oa_apply_overtime.begin_time',$begin_time]);
        }

        if(isset($data['end_time']) && !empty($data['end_time'])){
            $end_time = strtotime($data['end_time']);
            $aoModel->andWhere(['<=','oa_apply_overtime.end_time',$end_time]);
        }

        if(isset($data['search_u_id']) && !empty($data['search_u_id'])){
            $aoModel->andWhere('oa_apply_overtime.uid=:u_id',[':u_id'=>$data['search_u_id']]);
        }else{
            if(isset($data['search_org_id']) && !empty($data['search_org_id'])){
                $orgInfo = OrgModel::find()->where('org_id=:org_id',[':org_id'=>$data['search_org_id']])->asArray()->one();
                $arrOrgChildId = explode(',',$orgInfo['all_children_id']);
                $aoModel->andWhere(['in','oa_org_member.org_id',$arrOrgChildId]);
            }
        }

        $res = $aoModel->orderBy('oa_apply_overtime.begin_time desc')->asArray()->all();
        return $res;
    }

}