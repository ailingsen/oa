<?php

namespace app\modules\management\delegate;

use app\lib\FResponse;
use app\lib\Tools;
use app\models\CrontabModel;
use app\models\OrgMemberModel;
use app\models\OrgModel;
use app\models\ScoreLogModel;
use Yii;
use app\models\MembersModel;


class ScoreDelegate
{
    /**
     * 追加群组积分
     * @param $groupId
     * @param $point
     * @param $reason
     * @param $userInfo
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function addGroupPoint($groupId, $point, $reason, $userInfo, $isBatch = false)
    {
        //查询部门负责人
        $groupManager = OrgMemberModel::getGroupMember($groupId);
        if (empty($groupManager)) {
            !$isBatch && FResponse::output(['code' => 20001, 'msg' => '该部门没有负责人，无法分配积分']);
            return false;
        } else {
            $member = MembersModel::findOne($groupManager->u_id);
            $member->leave_points = $member->leave_points + $point;
            $group = OrgModel::findOne($groupId);
            $group->org_points = $group->org_points + $point;
            if ($group->org_points < 0) {
                $group->org_points = 0;
            }
            $group->org_all_points = $group->org_all_points + $point;
            if ($group->org_all_points < 0) {
                $group->org_all_points = 0;
            }
            if(intval($group->org_points)>999999){
                !$isBatch && FResponse::output(['code' => 20001, 'msg' => '积分不能超过999999']);
                return false;
            }
            $transaction = Yii::$app->db->beginTransaction();
            $logInfo = ['u_id' => $groupManager->u_id,
                'type' => 2,
                'content' => $reason,
                'score' => $point,
                'score_before' => $group->oldAttributes['org_points'],
                'score_after' => $group->org_points,
                'create_time' => time(),
                'operator' => $userInfo['u_id']
            ];

            ScoreLogModel::insertScoreLog($logInfo);
            if ($group->save(false) && $member->save(false)) {
                $transaction->commit();
                return  true;
            } else {
                $transaction->rollback();
                return  false;
            }
        }
    }

    /**
     * 增加个人积分
     * @param $memberId
     * @param $point
     * @param $reason
     * @param $userInfo
     * @return bool
     */
    public static function addPoint($memberId, $point, $reason, $userInfo)
    {
        //查询用户
        $member = MembersModel::findOne($memberId);
        if (empty($member)) {
            FResponse::output(['code' => 20001, 'msg' => '没有该用户']);
        }
        $member->points = $member->points + $point;
        if ($member->points < 0) {
            $member->points = 0;
        }
        if ($member->points > 999999) {
            FResponse::output(['code' => 20001, 'msg' => '纳米币数量不能超过999999']);
        }
        $logInfo = ['u_id' => $member->u_id,
            'type' => 1,
            'content' => $reason,
            'score' => $point,
            'score_before' => $member->oldAttributes['points'],
            'score_after' => $member->points,
            'create_time' => time(),
            'operator' => $userInfo['u_id']
        ];
        $res = ScoreLogModel::insertScoreLog($logInfo);
        if(!$res){
            return false;
        }
        if ($member->save(false)) {
            return  true;
        } else {
            return  false;
        }
    }

    /**
     * 批量增加个人积分
     * @param $memberIds
     * @param $point
     * @param $reason
     * @param $userInfo
     * @return bool
     */
    public static function batchAddPoint($memberIds, $point, $reason, $userInfo)
    {
        if (!is_array($memberIds)) {
            $memberIds = [$memberIds];
        }
        foreach ($memberIds as $memberId) {
            $res = self::addPoint($memberId, $point, $reason, $userInfo);
            if(!$res){
                return false;
            }
        }
        return true;
    }

    /**
     * 查询积分列表
     * @param $params
     * @return array
     */
    public static function getScoreList($params)
    {
        $orgId = isset($params['org_id']) ? $params['org_id'] : '';
        $uname = isset($params['uname']) ? $params['uname'] : '';
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['page_size']) ? $params['page_size'] : 10;
        $list = self::getMembers($orgId, $uname, $page, $pageSize);
        //获取用户技能积分等级  分组信息
        $list = $list['list'];
        foreach ($list as $key => $value) {
            $list[$key]['org_info'] =  OrgModel::getOrgNameStr($value['org_id']) . $list[$key]['org_name'];
        }
        $count = self::getMembersCount($orgId, $uname);
        $totalPage = ceil($count / $pageSize);
        return ['list' => $list, 'page' => $page, 'total_page' => $totalPage];
    }

    /**
     * 查询部门积分列表
     * @param $params
     * @return array
     */
    public static function getGroupScorelist($params)
    {
        $keyWords = isset($params['org_name']) ? $params['org_name'] : '';
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['page_size']) ? $params['page_size'] : 10;
        $list = OrgModel::getOrgInfoList($keyWords, 'oa_org.org_id,oa_org.org_name,oa_org.org_points', 1, $page, $pageSize);
        //获取用户技能积分等级  分组信息
        foreach ($list as $key => $value) {
            $list[$key]['org_info'] =  OrgModel::getOrgNameStr($value['org_id']) . $list[$key]['org_name'];
        }
        $count = OrgModel::getOrgInfoListCount($keyWords);
        $totalPage = ceil($count / $pageSize);
        return ['list' => $list, 'page' => $page, 'total_page' => $totalPage];
    }

    /**
     * 增加部门积分
     * @param $orgIds
     * @param $point
     * @param $reason
     * @param $userInfo
     * @return bool
     */
    public static function batchAddGroupPoint($orgIds, $point, $reason, $userInfo)
    {
        if (!is_array($orgIds)) {
            $orgIds = [$orgIds];
        }
        $isBatch = true;//是否批量
        if (1 == count($orgIds)) {
            $isBatch = false;
        }
        $errors = [];
        foreach ($orgIds as $orgId) {
            if (!self::addGroupPoint($orgId, $point, $reason, $userInfo, $isBatch)) {
                $orgInfo = OrgModel::findOne($orgId);
                if ($orgInfo) {
                    $errors[] = $orgInfo->org_name;
                }
            }
        }
        
        $failed = count($errors);
        $success = count($orgIds) - $failed;
        if (!empty($errors)) {
            FResponse::output(['code' => 20006, 'msg' => '', 'error_list' => $errors, 'success' => $success, 'failed' => $failed]);
        }
        return true;
    }

    public static function getMembers($orgId, $uname, $page = 1, $pageSize = 10)
    {
        $fields = 'oa_org_member.org_id,oa_org.org_name,oa_members.points,oa_members.u_id,oa_members.real_name,';
        return OrgMemberModel::getOrgMemberList($orgId, $page, $pageSize, $fields, $uname);
    }

    public static function getMembersCount($orgId, $uname)
    {
        return  OrgMemberModel::getOrgMemberListCount($orgId, $uname);
    }

    /**
     * 查询积分日志列表
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getLogList($params)
    {
        $searchId = isset($params['search_id']) ? $params['search_id'] : '';
        if (!$searchId) {
            return [];
        }
        $type = isset($params['type']) ? $params['type'] : 1;
        $page = isset($params['page']) ? $params['page'] : 1;
        if (2 == $params['type']) {
            //查询部门负责人
            $groupManager = OrgMemberModel::getGroupMember($searchId);
            if (!$groupManager) {
                return [];
            }
            $searchId = $groupManager['u_id'];
        }

        $pageSize = isset($params['page_size']) ? $params['page_size'] : 200;
        $scoreList = ScoreLogModel::getScoreList($searchId, $type, $page, $pageSize);
        $weekArray = array("日","一","二","三","四","五","六");
        foreach ($scoreList as $key => $val) {
            $scoreList[$key]['head_img'] = Tools::getHeadImg($scoreList[$key]['head_img']);
            $scoreList[$key]['weekday'] = "周" . $weekArray[date('w', $scoreList[$key]['create_time'])]; ;
            $scoreList[$key]['create_time'] = date('Y/m/d H:i:s', $scoreList[$key]['create_time']);
        }
        return $scoreList;
    }

    /**
     * 查看积分设置
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getScoreSet()
    {
        return CrontabModel::find()
            ->select('crontab_id,run_cycle,params')
            ->where(['cron_name' => ['add_personal_score', 'add_group_score']])
            ->orderBy('crontab_id ASC')
            ->asArray()
            ->all();
    }

    public static function setScoreCron($params)
    {
        $cronModelP = CrontabModel::findOne($params[0]['crontab_id']);
        $cronModelG = CrontabModel::findOne($params[1]['crontab_id']);
        if (!isset($params[0]) || !is_array($params[0]) || !isset($params[1]) || !is_array($params[1])) {
            FResponse::output(['code' => 20001, 'msg' => '设置失败']);
        }

        $cronModelP->setAttributes($params[0]);
        $cronModelG->setAttributes($params[1]);

        //设置下次任务执行时间
        $cronModelP->run_time = self::updateRuntime($params[0]['run_cycle']);
        $cronModelG->run_time = self::updateRuntime($params[1]['run_cycle']);
        if ($cronModelP->save(false) && $cronModelG->save(false)) {
            return true;
        }
        return false;
    }

    /**
     * 计算任务执行时间
     * @param $crontabId
     * @param $runCycle
     */
    public static function updateRuntime($runCycle) {
        $currTime = time();
        $currYear = date('Y');
        $currMonth = date('n');
        $currDay = date('j');
        $runTime = 0;
        switch ($runCycle) {
            case '1':
                //每年跑一次
                if($currTime >= strtotime($currYear.'0101')) {
                    $runTime = strtotime(($currYear + 1).'0101');
                }
                break;
            case '2':
                //每半年跑一次
                if($currTime < strtotime($currYear.'0101')) {
                    $runTime = strtotime(($currYear).'0101');
                }else if(strtotime($currYear.'0101') <= $currTime && $currTime < strtotime($currYear.'0701')) {
                    $runTime = strtotime(($currYear).'0701');
                }else if($currTime >= strtotime($currYear.'0701')) {
                    $runTime = strtotime(($currYear + 1).'0101');
                }
                break;
            case '3':
                //每季度跑一次
                if($currTime < strtotime($currYear.'0101')) {
                    $runTime = strtotime(($currYear).'0101');
                }else if(strtotime($currYear.'0101') <= $currTime && $currTime < strtotime($currYear.'0401')) {
                    $runTime = strtotime(($currYear).'0401');
                }else if(strtotime($currYear.'0401') <= $currTime && $currTime < strtotime($currYear.'0701')) {
                    $runTime = strtotime(($currYear).'0701');
                }else if(strtotime($currYear.'0701') <= $currTime && $currTime < strtotime($currYear.'1001')) {
                    $runTime = strtotime(($currYear).'0401');
                }else if($currTime >= strtotime($currYear.'1001')) {
                    $runTime = strtotime(($currYear + 1).'0101');
                }
                break;
            case '4':
                //每月跑一次
                if($currTime >= strtotime($currYear.'-'.$currMonth.'-01')) {
                    $month = $currMonth + 1;
                    if($month > 12) {
                        $year = $currYear + 1;
                        $month = 1;
                    }else {
                        $year = $currYear;
                        $month = $currMonth + 1;
                    }
                    $runTime = strtotime($year.'-'.$month.'-01');
                }
                break;
            case '5':
                //每天跑一次
                if($currTime >= strtotime($currYear.'-'.$currMonth.'-'.$currDay)) {
                    $runTime = strtotime('+1 day',strtotime($currYear.'-'.$currMonth.'-'.$currDay));
                }
                break;
        }
        return $runTime;
    }
}
