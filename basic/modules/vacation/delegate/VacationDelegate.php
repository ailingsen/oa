<?php

namespace app\modules\vacation\delegate;

use app\models\MembersModel;
use app\models\OrgMemberModel;
use app\models\VacationSetModel;
use Yii;


class VacationDelegate
{
    /**
     * 假期设置
     * @param $data
     * @return bool
     */
   public static function vacationSet($data)
   {
        $setModel = VacationSetModel::findOne(1);
       if (isset($data['ini_annual_vacation']) && $data['ini_annual_vacation'] < 0){
         return false;
       }
       if (isset($data['ini_annual_vacation']) && $data['ini_annual_vacation'] != $setModel->ini_annual_vacation) {
           $setModel->ini_annual_vacation = $data['ini_annual_vacation'];
       }

       if (isset($data['overtime_expire']) && $data['overtime_expire'] != $setModel->overtime_expire) {
           $setModel->overtime_expire = $data['overtime_expire'];
       }
       $setModel->update_time = time();
       return $setModel->save();
   }

    /**
     * 查看假期设置
     * @return array
     */
    public static function getVacationSet()
    {
        $setModel = VacationSetModel::findOne(1)->toArray();

        if ($setModel) {
            $calCycleStartArr = explode('-', $setModel['cal_cycle_start']);
            $setModel['cal_cycle_start'] = intval($calCycleStartArr[0]) . '月' . intval($calCycleStartArr[1]) . '日';
            $calCycleEndArr = explode('-', $setModel['cal_cycle_end']);
            $setModel['cal_cycle_end'] = intval($calCycleEndArr[0]) . '月' . intval($calCycleEndArr[1]) . '日';
            $vacationExpArr = explode('-', $setModel['vacation_expire']);
            $setModel['vacation_expire'] = intval($vacationExpArr[0]) . '月' . intval($vacationExpArr[1]) . '日';
            $setModel['increase_rules'] = json_decode($setModel['increase_rules'], true);
        }
        
        return $setModel;
    }

    public static function getMembers($orgId, $page = 1, $pageSize = 10, $realName = '')
    {
        $members = OrgMemberModel::getOrgMemberList($orgId, $page, $pageSize, ['oa_org_member.u_id', 'oa_members.real_name'], $realName);

        return $members;
    }

    public static function getMembersCount($orgId, $uid)
    {
        if ($uid) {
            return 1;
        }
        if ($orgId) {
           return  OrgMemberModel::getOrgMemberListCount($orgId);
        }
        return MembersModel::getMembersCount('', ['status' => 1, 'is_del' => 0], 1);
    }
}
