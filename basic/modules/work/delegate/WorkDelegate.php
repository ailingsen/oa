<?php
/**
 * Created by PhpStorm.
 * User: liaoshuochao
 * Date: 2016/7/28
 * Time: 9:48
 */
namespace app\modules\work\delegate;
use app\config\Dict;
use Yii;
use app\lib\FResponse;
use app\models\MembersModel;
use app\models\OrgMemberModel;
use app\models\OrgModel;
use app\models\TaskModel;
use app\models\WorkItemModel;
use app\models\WorkStatementModel;
use app\modules\work\helper\WorkHelper;

Class WorkDelegate
{
    /**
     * 新增工作项
     * @param $workId
     * @param $type
     * @param $workItem
     * @return bool
     */
    public static function addWorkItem($workId, $type, $workItem)
    {
        $workItemData['content'] = $workItem['content'];
        $workItemData['work_id'] = $workId;
        $workItemData['type'] = $type;
        $workItemData['create_time'] = time();
        $workItemData['status'] = isset($workItem['status']) ? isset($workItem['status']) : 2;//默认已完成
        $workItemData = array_intersect_key($workItemData, array_flip(['work_id', 'type', 'content', 'status', 'create_time']));
        return WorkItemModel::createX($workItemData);
    }

    /**
     * 修改工作报告
     * @param $itemId
     * @param $workItem
     * @return bool
     */
    public static function updateWorkItem($itemId, $workItem)
    {
        $workItemModel = WorkItemModel::findOne($itemId);
        if (!$workItemModel) {
            return false;
        }
        if (isset($workItem['status'])) {
            $workItemModel->status = $workItem['status'];
        }
        if (isset($workItem['content'])) {
            $workItemModel->content = $workItem['content'];
        }
        return $workItemModel->save();
    }

    /**
     * 修改工作报告
     * @param $workId
     * @param $workData
     * @return bool
     */
    public static function updateWorkState($workId, $workData)
    {
        $workModel = WorkStatementModel::findOne($workId);
        if (!$workModel) {
            FResponse::output(['code' => 20001, 'msg' => "工作报告不存在"]);
        }
        if (2 == $workModel->status) {
            FResponse::output(['code' => 20001, 'msg' => "工作报告已被审阅"]);
        }
        if (0 == $workModel->status) {
            $workModel->status = 1;
        }
        if (isset($workData['work_content'])) {
            $workModel->work_content = $workData['work_content'];
        }
        if (isset($workData['plan_content'])) {
            $workModel->plan_content = $workData['plan_content'];
        }
        $workModel->commit_time = time();
        return $workModel->save(false);
    }

    /**
     * 工作报告列表
     * @param $uid
     * @param $condition
     * @param $page
     * @param $pageSize
     * @return array
     */
    public static function workList($uid, $condition, $page, $pageSize = 10)
    {
        $query = WorkStatementModel::find()
            ->select('oa_work_statement.*, oa_members.real_name')
            ->leftJoin('oa_members', 'oa_work_statement.u_id=oa_members.u_id')
            ->where('oa_work_statement.u_id=:uid', [':uid' => $uid]);
        if (isset($condition['type']) && $condition['type'] > 0 && $condition['type'] != '') {
            $query->andWhere('oa_work_statement.type=:type', [':type' => $condition['type']]);
        }
        $condition['status'] = $condition['status'] == -1 ? '' : $condition['status'];
        if (isset($condition['status']) && ($condition['status'] === 0 || $condition['status'] != '')) {
            $query->andWhere(['oa_work_statement.status' => $condition['status']]);
        }
        if ($condition['begin_time']) {
            $query->andWhere(['>=', 'oa_work_statement.commit_time', strtotime($condition['begin_time'])]);
        }
        if ($condition['end_time']) {
            $query->andWhere(['<=', 'oa_work_statement.commit_time', strtotime($condition['end_time'])]);
        }

       $workList = $query->limit($pageSize)
           ->offset(($page - 1) * $pageSize)
//           ->orderBy(['cycle' => SORT_DESC], ['oa_work_statement.u_id' => SORT_ASC])
           ->orderBy(['oa_work_statement.create_time' => SORT_DESC])
           ->asArray()
            ->all();
        $totalCount = $query->count();
        $totalPage = ceil($totalCount / $pageSize);
        $pageData = ['list' => $workList, 'total_page' => $totalPage, 'page' => $page];
        return $pageData;
    }

    /**
     * @param int $orgId
     * @param array $condition
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public static function workApproveList($orgId, $condition, $page, $pageSize = 10, $u_id = 0)
    {
        $orgIds = OrgModel::getAllChildrenOrgId($orgId);//只有直属上级才能看到
        $orgIds[] = $orgId;
        $query = WorkStatementModel::find()
            ->select('oa_org_member.u_id,oa_members.real_name, oa_work_statement.work_id, oa_work_statement.type, oa_work_statement.cycle, oa_work_statement.status, oa_work_statement.commit_time')
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
        $condition['status'] = $condition['status'] == -1 ? '' : $condition['status'];
        if (isset($condition['status']) && ($condition['status'] === 0 || $condition['status'] != '')) {
            $query->andWhere(['oa_work_statement.status' => $condition['status']]);
        }
        if (isset($condition['u_id']) && $condition['u_id'] != '') {
            $query->andWhere('oa_work_statement.u_id=:uid', [':uid' => $condition['u_id']]);
        }
        if (isset($condition['begin_time']) && $condition['begin_time']) {
            $condition['begin_time'] = strtotime($condition['begin_time']);
            $query->andWhere(['>=', 'oa_work_statement.commit_time', $condition['begin_time']]);
        }
        if (isset($condition['end_time']) && $condition['end_time']) {
            $condition['end_time'] = strtotime($condition['end_time']);
            $query->andWhere(['<=', 'oa_work_statement.commit_time', $condition['end_time']]);
        }

       $workList = $query->limit($pageSize)
           ->offset(($page - 1) * $pageSize)
           ->orderBy(['oa_work_statement.commit_time' => SORT_DESC])
           ->asArray()
           ->all();
        $totalCount = $query->count();

        $totalPage = ceil($totalCount / $pageSize);
        $pageData = ['list' => $workList, 'total_page' => $totalPage, 'page' => $page];
        return $pageData;

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
            FResponse::output(['code' => 20001, 'msg' => "该工作报告已审阅", 'data'=>new Object()]);
        }
        //消息
        $data[0]['u_id']=$workModel->u_id;
        $data[0]['menu']=2;
        $is_approveMsg = WorkDelegate::addWorkMsg($uid,'审阅了你提交的',Yii::$app->request->post('work_id'),$data, $workModel->type);
        if(!$is_approveMsg){
            FResponse::output(['code' => 20001, 'msg' => "审阅失败"]);
        }
        if ($workModel->commit_time != $commitTime) {
            FResponse::output(['code' => 20001, 'msg' => "工作报告已经更新"]);
        }
        return WorkStatementModel::updateWorkStatement($workId, ['status' => 2, 'approver' => $uid, 'approve_time' => time()]);
    }

    /**
     * 生成工作报告
     * @param $uid
     * @param $type
     * @param $dateTime
     * @return bool
     */
    public static function createWorkStatement($uid, $type, $dateTime)
    {
        $workstate = [
            'u_id' => $uid,
            'type' => $type,
            'cycle' => WorkHelper::getWorkCycle($type, $dateTime),
            'create_time' => $dateTime,
            'commit_time' => 0,
            'approve_time' => 0,
            'approver' => 0
        ];

        return WorkStatementModel::createX($workstate);
    }

    /**
     * 工作详情
     * @param $workId
     * @return array
     */
    public static function workDetail($workId, $uid = 0)
    {
        if ($workId) {
            $stateMent = WorkStatementModel::find()
                ->select('oa_work_statement.work_id,oa_work_statement.work_content,oa_work_statement.plan_content,,oa_work_statement.status,oa_work_statement.type,oa_members.u_id, oa_members.real_name,oa_work_statement.commit_time')
                ->leftJoin('oa_members', 'oa_work_statement.u_id=oa_members.u_id')
                ->where('oa_work_statement.work_id=:work_id', [':work_id' => $workId])
                ->asArray()
                ->one();
        } else if($uid) {
            $stateMent = WorkStatementModel::find()
                ->select('oa_work_statement.work_id,oa_work_statement.work_content,oa_work_statement.plan_content,,oa_work_statement.status,oa_work_statement.type,oa_members.u_id, oa_members.real_name,oa_work_statement.commit_time')
                ->leftJoin('oa_members', 'oa_work_statement.u_id=oa_members.u_id')
                ->where(['oa_work_statement.u_id' => $uid])
                ->limit(1)
                ->orderBy(['create_time' => SORT_DESC])
                ->asArray()
                ->one();
        }

        return $stateMent;
    }
    
    public static function getMemberList($uid, $keyword, $orgId)
    {
        $memList = MembersModel::getMemberList($keyword, $orgId);
        if (is_array($memList)) {
            foreach ($memList as $key => $item) {
                if ($item['u_id'] == $uid){
                    unset($memList[$key]);
                    break;
                }
            }
        }
        return $memList;
    }

    /**
     * 添加日志消息
     */
    public static function addWorkMsg($u_id,$title,$work_id,$data,$type=1)
    {
        $temp = [];
        $res = false;
        $work_title = ($type==1) ? '日报': '周报';
        if (count($data) > 0) {
            foreach ($data as $key => $val) {
                $temp[$key]['u_id'] = $val['u_id'];
                $temp[$key]['operator'] = $u_id;
                $temp[$key]['work_id'] = $work_id;
                $temp[$key]['work_title'] = $work_title;
                $temp[$key]['title'] = $title;
                $temp[$key]['create_time'] = time();
                $temp[$key]['menu'] = $val['menu'];
            }
            $res = Yii::$app->db->createCommand()->batchInsert('oa_report_msg', ['uid', 'operator', 'work_id','work_title', 'title', 'create_time','menu'], $temp)->execute();
        }
        return $res;
    }

    //获取所有上级部门负责人信息(不包括自己)
    public static function getMsgUserInfo($org_id,$u_id=0)
    {
        $arrParOrg = OrgModel::getAllParentOrg($org_id);
        $arrParOrg[] = $org_id;
        $manageModel = OrgMemberModel::find()->select('u_id')->where(['in','org_id',$arrParOrg])->andWhere(['is_manager'=>1]);
        if($u_id>0){
            $manageModel->andWhere('u_id!=:u_id',[':u_id'=>$u_id]);
        }
        $manageUser = $manageModel->asArray()->all();
        return $manageUser;
    }


}