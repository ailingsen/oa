<?php

namespace app\modules\desk\delegate;


//工作台
use app\lib\Tools;
use app\models\ApplyBaseModel;
use app\models\ApplyMsgModel;
use app\models\ApprovalMsgModel;
use app\models\ChecktimeModel;
use app\models\DeskModel;
use app\models\MeetingMsgModel;
use app\models\MembersModel;
use app\models\ProjectModel;
use app\models\ProjectMsgModel;
use app\models\ReportMsgModel;
use app\models\SkillMemberModel;
use app\models\TaskModel;
use app\models\TaskMsgModel;
use app\modules\attendance\delegate\AttendanceDelegate;
use app\modules\project\helper\ProjectHelper;
use app\modules\work\delegate\WorkDelegate;
use app\modules\workmate\delegate\WorkmateDelegate;
use Yii;
use yii\db\Query;

class DeskDelegate {

    /**
     * 工作模板
     * @param $uid
     * @return array
     */
    public static function getTempletList($uid)
    {
        $deskTemplet = DeskModel::findOne(['u_id' => $uid]);

        //初始化模块
        $bigTemplet = [
            'mytask',
            'mywork',
            'attendance',
            'project',
            'notice',
            'shortcut',
        ];
        $smallTpl = [
            ['code' => 's-workstate', 'url' => 'main.workStatement.myWorkStatementTable.edite', 'class'=>'bg-blue', 'title' => '工作报告', 'content' => '汇报一下工作情况吧'],
            ['code' => 's-apply', 'url' => 'main.apply.application', 'class'=>'bg-green','title' => '发起申请', 'content' => '请假/报销/设备等申请'],
            ['code' => 's-project', 'url' => 'main.project.createpro', 'class'=>'bg-ltgreen','title' => '创建项目', 'content' => '创建一个项目'],
            ['code' => 's-task', 'url' => 'main.task.create', 'class'=>'bg-blue','title' => '创建任务', 'content' => '任务创建快捷入口'],
            ['code' => 's-meeting', 'url' => 'main.meeting.reserve', 'class'=>'bg-sblue','title' => '会议室预定', 'content' => '开会请提前预定哦！'],
            ['code' => 's-workmate', 'url' => 'main.colleague.myColleague', 'class'=>'bg-blue bg-yel','title' => '我的同事', 'content' => '查看所有同事'],
        ];
        $smallTemplet = array_slice($smallTpl, 0, 4);
        if ($deskTemplet) {
            $deskTemplet = $deskTemplet->attributes;
            $bigTemplet = [$deskTemplet['templet_id1'], $deskTemplet['templet_id2'], $deskTemplet['templet_id3'], $deskTemplet['templet_id4'], $deskTemplet['templet_id5'], $deskTemplet['templet_id6']];

            $smallTemplet = [
                self::getTpl($deskTemplet['templet_id7']),
                self::getTpl($deskTemplet['templet_id8']),
                self::getTpl($deskTemplet['templet_id9']),
                self::getTpl($deskTemplet['templet_id10'])
            ];
        }

        return ['big' => $bigTemplet, 'small' => $smallTemplet];
    }

    public static function getTplSet($uid)
    {
        $deskTemplet = DeskModel::findOne(['u_id' => $uid]);
        $bigTemplet = [
            'mytask',
            'mywork',
            'attendance',
            'project',
            'notice',
            'shortcut'
        ];
        $smallTpl = [
            's-workstate',
            's-apply',
            's-project',
            's-task',
            's-meeting',
            's-workmate'
        ];
        $smallTemplet = array_slice($smallTpl, 0, 4);
        if ($deskTemplet) {
            $deskTemplet = $deskTemplet->attributes;
            $bigTemplet = [
                $deskTemplet['templet_id1'],
                $deskTemplet['templet_id2'],
                $deskTemplet['templet_id3'],
                $deskTemplet['templet_id4'],
                $deskTemplet['templet_id5'],
                $deskTemplet['templet_id6']
            ];

            $smallTemplet = [
                $deskTemplet['templet_id7'],
                $deskTemplet['templet_id8'],
                $deskTemplet['templet_id9'],
                $deskTemplet['templet_id10']
            ];
        }

        return ['big' => $bigTemplet, 'small' => $smallTemplet];
    }

    /**
     * 获取小模板
     * @param $tplCode
     * @return array|string
     */
    public static function getTpl($tplCode) {
        $tpl = '';

        switch ($tplCode) {
            case 's-workstate':
                $tpl = ['code' => 's-workstate', 'url' => 'main.workStatement.myWorkStatementTable.edite', 'icon' =>'&#xe618;', 'class'=>'bg-blue', 'title' => '工作报告', 'content' => '汇报一下工作情况吧'];
                break;
            case 's-apply':
                $tpl = ['code' => 's-apply', 'url' => 'main.apply.application', 'icon' =>'&#xe617;','class'=>'bg-green','title' => '发起申请', 'content' => '请假/报销/设备等申请'];
                break;
            case 's-project':
                $tpl = ['code' => 's-project', 'url' => 'main.project.createpro', 'icon' =>'&#xe60a;','class'=>'bg-ltgreen','title' => '创建项目', 'content' => '创建一个项目'];
                break;
            case 's-task':
                $tpl = ['code' => 's-task', 'url' => 'main.task.create', 'icon' =>'&#xe60a;','class'=>'bg-blue','title' => '创建任务', 'content' => '任务创建快捷入口'];
                break;
            case 's-meeting':
                $tpl = ['code' => 's-meeting', 'url' => 'main.meeting.reserve', 'icon' =>'&#xe605;','class'=>'bg-sblue','title' => '会议室预定', 'content' => '开会请提前预定哦！'];
                break;
            case 's-workmate':
                $tpl = ['code' => 's-workmate', 'url' => 'main.colleague.myColleague', 'icon' =>'&#xe620;', 'class'=>'bg-blue bg-yel','title' => '我的同事', 'content' => '查看所有同事'];
                break;
        }
        return $tpl;
    }

    /**
     * 修改工作台模板位置
     * @param $uid
     * @param $templetInfo
     * @return bool
     */
    public static function editeDeskTemplet($uid, $templetInfo, $editeBig = false)
    {
        $deskTemplet = DeskModel::findOne(['u_id' => $uid]);
        //如果没找到就新增
        if (!$deskTemplet) {
            $deskTemplet = new DeskModel();
            $smallTpl = [
                's-workstate',
                's-apply',
                's-project',
                's-task'
            ];
            if ($editeBig) {
                for($key = 0; $key < 6; $key++) {
                    $deskTemplet->setAttribute('templet_id' . ($key + 1), $templetInfo[$key]);
                }

                for ($key = 0; $key < 4; $key++) {
                    $deskTemplet->setAttribute('templet_id' . ($key + 7), $smallTpl[$key]);
                }
            } else {
                $isShortcut = false;
                for ($key = 0; $key < 6; $key++) {
                    $tplName = isset($templetInfo['bigSelected'][$key]) ? $templetInfo['bigSelected'][$key] : 'blanktpl';
                    if ($tplName == 'shortcut') {
                        $isShortcut = true;
                    }
                    $deskTemplet->setAttribute('templet_id' . ($key + 1), $tplName);
                }

                if (!$isShortcut) {
                    $templetInfo['smallSelected'] = $smallTpl;
                }
                for ($key = 0; $key < 4; $key++) {
                    $deskTemplet->setAttribute('templet_id' . ($key + 7), $templetInfo['smallSelected'][$key]);
                }
            }
        } else {
            if ($editeBig) {
                for($key = 0; $key < 6; $key++) {
                    $tplName = isset($templetInfo[$key]) ? $templetInfo[$key] : 'blanktpl';
                    $deskTemplet->setAttribute('templet_id' . ($key + 1), $tplName);
                }
            } else {
                $hasBlank = false;
                if (sizeof($templetInfo['bigSelected']) < 6) {
                    $hasBlank = true;
                }
                if ($hasBlank) {
                    $deskTemplet->setAttributes(self::getDeskTemplet2($deskTemplet->attributes, $templetInfo));
                } else {
                    $deskTemplet->setAttributes(self::getDeskTemplet($deskTemplet->attributes, $templetInfo));
                }
            }
        }
        $deskTemplet->setAttribute('u_id', $uid);
        return $deskTemplet->save(false);
    }

    /**
     * 修改模板替换算法
     * @param $oldTpl
     * @param $newTpl
     * @return mixed
     */
    public static function getDeskTemplet($oldTpl, $newTpl)
    {
        //新加的大模板
        $bigNewAdd = array_diff($newTpl['bigSelected'], $oldTpl);

        //原来要被替换的大模板
        $oldReplaceKey = [];

        for ($key = 1; $key < 7; $key++) {
            $isFind = false;
            foreach ($newTpl['bigSelected'] as $item) {
                if ($oldTpl['templet_id' . $key] == $item) {
                    $isFind = true;
                    break;
                }
            }
            if (!$isFind) {
                $oldReplaceKey[] = 'templet_id' . $key;
            }
        }
        foreach ($bigNewAdd as $item) {
            $oldTpl[array_pop($oldReplaceKey)] = $item;
        }

        //替换小模板
        if(count($newTpl['smallSelected'])==4){
            for ($key = 0; $key < 4; $key++) {
                $oldTpl['templet_id' . ($key + 7)] = $newTpl['smallSelected'][$key];
            }
        }
        return $oldTpl;
    }

    /**
     * 修改模板替换算法(有空模板)
     * @param $oldTpl
     * @param $newTpl
     * @return mixed
     */
    public static function getDeskTemplet2($oldTpl, $newTpl)
    {
        //原来要被替换的大模板
        $tplArr = [];
        //新加的大模板
        $bigNewAdd = array_diff($newTpl['bigSelected'], $oldTpl);
        for ($key = 1; $key < 7; $key++) {
            $isFind = false;
            foreach ($newTpl['bigSelected'] as $item) {
                if ($oldTpl['templet_id' . $key] == $item) {
                    $isFind = true;
                    break;
                }
            }
            if ($isFind) {
                $tplArr[] = $oldTpl['templet_id' . $key];
            }
        }

        $tplArr = array_merge($tplArr, $bigNewAdd);
        for ($key = 1; $key <= sizeof($tplArr); $key++) {
            $oldTpl['templet_id' . $key] = isset($tplArr[$key - 1]) ? $tplArr[$key - 1] : 'blanktpl';
        }

        for ($key = sizeof($tplArr) + 1; $key < 7; $key++) {
            $oldTpl['templet_id' . $key] = 'blanktpl';
        }


        //替换小模板
        if (isset($newTpl['smallSelected']) && count($newTpl['smallSelected']) > 0) {
            for ($key = 0; $key < 4; $key++) {
                $oldTpl['templet_id' . ($key + 7)] = $newTpl['smallSelected'][$key];
            }
        }

        return $oldTpl;
    }

    /**
     * 我的任务
     * @param $uid
     * @return $this
     */
    public static function getMytask($uid)
    {
        $taskList = TaskModel::find()->select('task_id,task_title,task_type,status,task_level')
            ->where(['charger' => $uid])
            ->andWhere('status>0')
            ->limit(6)
            ->orderBy(['oa_task.status' => SORT_ASC, 'oa_task.update_time' => SORT_DESC,'oa_task.create_time' => SORT_DESC])
            ->asArray()
            ->all();
        return $taskList;
    }

    /**
     * 我的动态
     * @return array
     */
    public static function getMywork()
    {
        return [];
    }

    /**
     * 考勤
     * @param $uid
     * @return \yii\db\ActiveRecord
     */
    public static function getMyAttendance($uid)
    {
        $t = time();
//        $t = 1469581006;
        $start = mktime(0, 0, 0, date("m", $t), date("d", $t), date("Y", $t));
        $end = mktime(23, 59, 59, date("m", $t), date("d", $t), date("Y", $t));
        $checmModel = ChecktimeModel::find()->select('MAX(oa_checktime.checktime) as offTime,MIN(oa_checktime.checktime) as onTime,oa_checktime.day as showDate,oa_members.real_name ')
            ->leftJoin('oa_members', 'card_no=oa_checktime.badgenumber')
            ->where('oa_members.u_id=:u_id', [':u_id' => $uid])
            ->andWhere(['>=', 'oa_checktime.day', $start])
            ->andWhere(['<=', 'oa_checktime.day', $end]);

        //获取工作日时间设置
        $timeSet = AttendanceDelegate::getTimeSet();
        if ($timeSet) {
            $timeSet['begin_time'] = $timeSet['begin_time'] != '' ? $timeSet['begin_time'] : '9:10';
            $arrBegin = explode(':', $timeSet['begin_time']);

            $beginUnix = $arrBegin[0] * 60 * 60 + $arrBegin[1] * 60 + 60;
        } else {
            $beginUnix = 9 * 3600 + 660;
        }

        $checmModel->groupBy('oa_checktime.day,oa_members.real_name');
        $rs = $checmModel->orderBy('oa_checktime.day desc')->asArray()->all();
        if ($rs) {
            $rs[0]['status'] = 0;//迟到
            $rs[0]['today'] = date('Y-m-d', $t);

            if ($rs[0]['onTime'] < $rs[0]['showDate'] + $beginUnix) {
                $rs[0]['status'] = 1;//正常
            }
            return $rs[0];
        }
        $rs['status'] = 2;//漏打卡
        $rs['today'] = date('Y-m-d', $t);
        return $rs;
    }

    /**
     * 项目
     * @param $uid
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getProject($uid)
    {
        //获取项目
        $proModel = ProjectModel::find()->select('oa_project.*')->with('task');
        $proModel->joinWith('projectmember');
        $proModel->where('oa_project_member.u_id=:u_id',[':u_id'=>$uid]);
        $proModel->andWhere('oa_project.u_id!=:u_id',[':u_id'=>$uid]);//不包括我创建的
        $projectList = $proModel->asArray()->all();
        $projectList = ProjectHelper::setProData($projectList);
        $projectList = ProjectHelper::getStatisticCount($projectList,1);
        $list = $projectList['list'];
        unset($projectList['list']);
        return ['project_count' => $projectList, 'project_list' => $list];
    }

    /**
     * 我的申请
     * @param $uid
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getMyApply($uid)
    {
        $list = ApplyBaseModel::find()->select('oa_apply_base.apply_id,oa_apply_base.model_id,oa_apply_base.title,oa_apply_base.handler,oa_apply_base.status,oa_apply_base.is_press,oa_apply_base.create_time,oa_members.real_name')
            ->leftJoin('oa_members', 'oa_apply_base.handler=oa_members.u_id')
            ->where(['oa_apply_base.applyer' => $uid])
            ->orderBy(['oa_apply_base.update_time' => SORT_DESC, 'oa_apply_base.apply_id' => SORT_DESC])
            ->limit(6)
            ->asArray()
            ->all();
        return $list;
    }

    /**
     * 我的审批
     * @param $uid
     * @return array
     */
    public static function getMyApproval($uid)
    {
        //我的待办
        $list = (new Query())->select('c.apply_id,c.model_id,c.title,c.status,c.create_time,b.real_name,a.modeltype')
            ->from('oa_apply_base c')
            ->leftJoin('oa_members b', 'c.applyer=b.u_id')
            ->leftJoin('oa_apply_model a', 'c.model_id=a.model_id')
            ->where(['c.handler' => $uid, 'c.status' => 0])
            ->orderBy(['c.update_time' => SORT_DESC, 'apply_id' => SORT_DESC])
            ->limit(6)
            ->all();

        return $list;
    }

    /**
     * 工作报告审阅
     * @param $orgId
     * @return array
     */
    public static function getWorkstateApprove($orgId,$u_id)
    {
        $condition = ['status' => [1, 2]];
        return WorkDelegate::workApproveList($orgId, $condition, 1, 6, $u_id);
    }

    /**
     *  土豪积分榜
     */
    public static function scoreBoard()
    {
        $richIntegral = MembersModel::find()->select('real_name, points, head_img')->where(['is_del'=>0])->orderBy(['points'=> SORT_DESC])->limit(5)->asArray()->all();
        foreach ($richIntegral as $key => $val){
            $richIntegral[$key]['head_img'] = Tools::getHeadImg($val['head_img']);
        }
        return $richIntegral;
    }

    /**
     * 积分周榜
     */
    public static function scoreBoardweek($data)
    {
        return WorkmateDelegate::integralDelegate($data, 5, 1);
    }

    /**
     * 获取最高技能等级信息
    */
    public static function getHighSkill($u_id)
    {
        $highSkillInfo = SkillMemberModel::find()->where('member_id=:u_id',[':u_id'=>$u_id])->orderBy('point desc')->asArray()->one();
        return $highSkillInfo;
    }

    //审批消息列表
    public static function approvalMsg($u_id,$limit,$offset) {
        $query = ApprovalMsgModel::find()->leftJoin('oa_members','oa_approval_msg.applyer=oa_members.u_id')
            ->select(['oa_approval_msg.apply_id','oa_approval_msg.title','oa_approval_msg.create_time','real_name','head_img','oa_apply_base.model_id','oa_apply_model.modeltype'])
            ->leftJoin('oa_apply_base','oa_apply_base.apply_id = oa_approval_msg.apply_id')
            ->leftJoin('oa_apply_model','oa_apply_base.model_id = oa_apply_model.model_id')
            ->where(['oa_approval_msg.uid' => $u_id])->orderBy('oa_approval_msg.create_time DESC');
        $res['list'] = $query->limit($limit)->offset($offset)->asArray()->all();
        $res['page']['sumPage']  = ceil($query->count() / $limit);
        foreach($res['list'] as $key => $value) {
            $res['list'][$key]['head_img'] = Tools::getHeadImg($value['head_img']);
            $res['list'][$key]['create_time'] = date('Y-n-j H:i',$value['create_time']);
        }

        return $res;
    }

    //申请消息列表
    public static function applyMsg($u_id,$limit,$offset)
    {
        $query = ApplyMsgModel::find()->leftJoin('oa_members','oa_apply_msg.handler=oa_members.u_id')
            ->select(['oa_apply_msg.apply_id','oa_apply_msg.title','oa_apply_msg.status','oa_apply_msg.create_time','real_name','head_img','oa_apply_base.model_id'])
            ->leftJoin('oa_apply_base','oa_apply_base.apply_id = oa_apply_msg.apply_id')
            ->where(['oa_apply_msg.uid' => $u_id])->orderBy('oa_apply_msg.create_time DESC');
        $res['list'] = $query->limit($limit)->offset($offset)->asArray()->all();
        $res['page']['sumPage']  = ceil($query->count() / $limit);
        foreach($res['list'] as $key => $value) {
            $res['list'][$key]['head_img'] = Tools::getHeadImg($value['head_img']);
            $res['list'][$key]['create_time'] = date('Y-n-j H:i',$value['create_time']);
            if($value['status'] == 1) {
                $res['list'][$key]['status'] = '同意';
            }else if($value['status'] == 2) {
                $res['list'][$key]['status'] = '已拒绝';
            }else if($value['status'] == 3) {
                $res['list'][$key]['status'] = '已审批';
            }
        }
        return $res;
    }

    //会议室消息列表
    public static function meetingMsg($u_id,$limit,$offset) {
        $query = MeetingMsgModel::find()->leftJoin('oa_members','oa_meeting_msg.sponsor=oa_members.u_id')
            ->select(['res_id','title','meeting_name','room_name','begin_time','end_time','create_time','real_name','head_img'])
            ->where(['oa_meeting_msg.uid' => $u_id])->orderBy('oa_meeting_msg.create_time DESC');
        $res['list'] = $query->limit($limit)->offset($offset)->asArray()->all();
        $res['page']['sumPage']  = ceil($query->count() / $limit);
        foreach($res['list'] as $key => $value) {
            $res['list'][$key]['head_img'] = Tools::getHeadImg($value['head_img']);
            $res['list'][$key]['create_time'] = date('Y-n-j H:i',$value['create_time']);
            $res['list'][$key]['begin_time'] = date('Y-m-d H:i:s',$value['begin_time']);
            $res['list'][$key]['end_time'] = date('Y-m-d H:i:s',$value['end_time']);
        }
        return $res;
    }

    //任务消息列表
    public static function taskMsg($u_id,$limit,$offset) {
        $query = TaskMsgModel::find()->leftJoin('oa_members','oa_task_msg.operator=oa_members.u_id')
            ->select(['task_id','title','task_title','create_time','real_name','head_img','menu'])
            ->where(['oa_task_msg.uid' => $u_id])->orderBy('oa_task_msg.create_time DESC,oa_task_msg.msg_id DESC');
        $res['list'] = $query->limit($limit)->offset($offset)->asArray()->all();
        $res['page']['sumPage']  = ceil($query->count() / $limit);
        foreach($res['list'] as $key => $value) {
            $res['list'][$key]['head_img'] = Tools::getHeadImg($value['head_img']);
            $res['list'][$key]['create_time'] = date('Y-m-d H:i',$value['create_time']);
        }
        return $res;
    }

    //项目消息列表
    public static function projectMsg($u_id,$limit,$offset) {
        $query = ProjectMsgModel::find()->leftJoin('oa_members','oa_project_msg.operator=oa_members.u_id')
            ->select(['project_id','title','project_name','create_time','real_name','head_img','menu'])
            ->where(['oa_project_msg.uid' => $u_id])->orderBy('oa_project_msg.create_time DESC');
        $res['list'] = $query->limit($limit)->offset($offset)->asArray()->all();
        $res['page']['sumPage']  = ceil($query->count() / $limit);
        foreach($res['list'] as $key => $value) {
            $res['list'][$key]['head_img'] = Tools::getHeadImg($value['head_img']);
            $res['list'][$key]['create_time'] = date('Y-m-d H:i',$value['create_time']);
        }
        return $res;
    }

    //工作报告消息列表
    public static function reportMsg($u_id,$limit,$offset) {
        $query = ReportMsgModel::find()->leftJoin('oa_members','oa_report_msg.operator=oa_members.u_id')
            ->select(['work_id','title','work_title','create_time','real_name','head_img','menu'])
            ->where(['oa_report_msg.uid' => $u_id])->orderBy('oa_report_msg.create_time DESC');
        $res['list'] = $query->limit($limit)->offset($offset)->asArray()->all();
        $res['page']['sumPage']  = ceil($query->count() / $limit);
        foreach($res['list'] as $key => $value) {
            $res['list'][$key]['head_img'] = Tools::getHeadImg($value['head_img']);
            $res['list'][$key]['create_time'] = date('Y-m-d H:i',$value['create_time']);
        }
        return $res;
    }

}