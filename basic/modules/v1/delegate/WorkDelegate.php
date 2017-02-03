<?php

namespace app\modules\v1\delegate;


//模型委托类。 处理控制器和动作列表

use app\models\OrgModel;
use app\models\WorkStatementModel;
use Yii;
use app\models\FResponse;
use Yii\base\Object;

class WorkDelegate
{
    /**
     * 我的工作报告列表
     * @param $uid
     * @param $condition
     * @param $offset
     * @param $limit
     * @return array
     */
    public static function workList($uid, $condition, $offset, $limit = 10)
    {
        $query = WorkStatementModel::find()
            ->select('oa_work_statement.work_id,oa_work_statement.u_id,oa_work_statement.cycle,oa_work_statement.type,oa_work_statement.status,oa_work_statement.create_time, oa_members.real_name')
            ->leftJoin('oa_members', 'oa_work_statement.u_id=oa_members.u_id')
            ->where('oa_work_statement.u_id=:uid', [':uid' => $uid]);
        if (isset($condition->type) && $condition->type > 0 && $condition->type != '') {
            $query->andWhere('oa_work_statement.type=:type', [':type' => $condition->type]);
        }
        $workList = $query->limit($limit)
            ->offset($offset)
            ->orderBy(['oa_work_statement.create_time' => SORT_DESC])
            ->asArray()
            ->all();
        $totalCount = $query->count();
        $totalPage = ceil($totalCount / $limit);
        $pageData = ['list' => $workList, 'totalPage' => $totalPage];
        return $pageData;
    }

    /**
     * 获取工作报告审阅列表
     * @param int $orgId
     * @param array $condition
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public static function workApproveList($orgId, $condition, $offset, $limit = 10, $u_id=0)
    {
        $orgIds = OrgModel::getAllChildrenOrgId($orgId);//只有直属上级才能看到
        $orgIds[] = $orgId;
        $query = WorkStatementModel::find()
            ->select('oa_work_statement.work_id,oa_work_statement.u_id,oa_work_statement.cycle,oa_work_statement.type,oa_work_statement.status,oa_work_statement.commit_time, oa_members.real_name')
            ->leftJoin('oa_org_member', 'oa_org_member.u_id=oa_work_statement.u_id')
            ->leftJoin('oa_members', 'oa_org_member.u_id=oa_members.u_id');

        if ($orgId != 0) {
            $query->where(['oa_org_member.org_id' => $orgIds]);
            //->andWhere('oa_org_member.is_manager=0');
        }

        //不能审核自己的工作报告
        if($u_id>0){
            $query->andWhere('oa_work_statement.u_id!=:u_id', [':u_id'=>$u_id]);
        }

        if (isset($condition['type']) && $condition['type'] > 0 && $condition['type'] != '') {
            $query->andWhere('oa_work_statement.type=:type', [':type' => $condition['type']]);
        }

        $workList = $query->limit($limit)
            ->offset($offset)
            ->orderBy(['oa_work_statement.commit_time' => SORT_DESC])
            ->asArray()
            ->all();
        $totalCount = $query->count();

        $totalPage = ceil($totalCount / $limit);
        $pageData = ['list' => $workList, 'totalPage' => $totalPage];
        return $pageData;

    }

    /**
     * 工作详情
     * @param $workId
     * @return array
     */
    public static function workDetail($workId, $uid = 0)
    {
        $stateMent = WorkStatementModel::find()
            ->select('oa_work_statement.work_id,oa_work_statement.work_content,oa_work_statement.plan_content,oa_work_statement.status,oa_work_statement.type,oa_work_statement.cycle,oa_members.u_id, oa_members.real_name,oa_work_statement.commit_time')
            ->leftJoin('oa_members', 'oa_work_statement.u_id=oa_members.u_id')
            ->where('oa_work_statement.work_id=:work_id', [':work_id' => $workId])
            ->asArray()
            ->one();
        return $stateMent;
    }

    /**
     * 审阅工作报告
     * @param $uid
     * @param $workId
     * @param $commitTime
     * @return bool
     */
    public static function approveWork($uid, $workId, $commitTime)
    {
        $workModel = WorkStatementModel::findOne($workId);
        if($workModel->status == 2){
            FResponse::output(['code' => 20003, 'msg' => "该工作报告已审阅", 'data'=>new Object()]);
        }
        //消息
        $data[0]['u_id']=$workModel->u_id;
        $data[0]['menu']=2;
        $is_approveMsg = \app\modules\work\delegate\WorkDelegate::addWorkMsg($uid,'审阅了你提交的',$workId,$data, $workModel->type);
        if(!$is_approveMsg){
            FResponse::output(['code' => 20003, 'msg' => "审阅失败", 'data'=>new Object()]);
        }
        if(!isset($workModel->commit_time)){
            FResponse::output(['code' => 20007, 'msg' => "失败，该报告不存在", 'data'=>new Object()]);
        }
        if ($workModel->commit_time != $commitTime) {
            FResponse::output(['code' => 20007, 'msg' => "失败，工作报告已经更新", 'data'=>new Object()]);
        }
        return WorkStatementModel::updateWorkStatement($workId, ['status' => 2, 'approver' => $uid, 'approve_time' => time()]);
    }


}