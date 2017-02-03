<?php
/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2016/11/15
 * Time: 10:40
 */

namespace app\modules\v1\controllers;

use app\lib\FileUploadHelper;
use app\lib\Tools;
use app\models\AnnualLeaveModel;
use app\models\ApplyAttachmentModel;
use app\models\ApplyFlowModel;
use app\models\ApplyLogModel;
use app\models\ApplyBaseModel;
use app\models\ApplyModel;
use app\models\ApplyMsgModel;
use app\models\ApplyOvertimeModel;
use app\models\ApplyRankModel;
use app\models\ApprovalMsgModel;
use app\models\AttendanceModel;
use app\models\FlexworkStoreModel;
use app\models\MembersModel;
use app\models\WorkSetModel;
use app\modules\apply\delegate\ApplyModelDelegate;
use app\modules\apply\delegate\CheckOutDelegate;
use app\modules\apply\delegate\FlexWorkDelegate;
use app\modules\apply\delegate\LeaveDelegate;
use app\modules\apply\delegate\OverTimeDelegate;
use app\modules\apply\delegate\RankDelegate;
use app\modules\apply\helper\ApplyHelper;
use app\modules\apply\helper\LeaveHelper;
use Yii;
use yii\base\Object;
use yii\db\Query;
use app\modules\v1\delegate\AppApplyDelegate;
use app\modules\apply\delegate\ApplyDelegate;
use app\lib\FResponse;

class ApplyController extends BaseController
{
    //我的申请列表
    public function actionMineList() {
        $perm = $this->isPermStatus('ApplyMyapply');
        if(!$perm) {
            FResponse::output(['code' => 20050, 'msg' => "您无访问此功能权限，请找管理员开通~", 'data'=>['perm' => $perm]]);
        }
        $data = json_decode(file_get_contents("php://input"));
        if( empty($data->page) || empty($data->pageSize) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $uid = $this->userInfo['u_id'];
        $page = $data->page;
        $pageSize = $data->pageSize;
        $offset = ($page-1)*$pageSize;
        $num = ApplyBaseModel::find()->where(['applyer' => $uid])->count();
        $list = ApplyBaseModel::find()->select('apply_id,oa_apply_base.model_id,oa_apply_base.title,oa_apply_base.status,create_time,handler,modeltype')
            ->leftJoin('oa_apply_model','oa_apply_base.model_id=oa_apply_model.model_id')
            ->where(['applyer' => $uid])
            ->orderBy(['create_time' => SORT_DESC,'apply_id' => SORT_DESC])
            ->limit($pageSize)
            ->offset($offset)
            ->asArray()
            ->all();
        $totalPage = ceil($num / $pageSize);
        foreach($list as $key => $value) {
            if($value['status'] == 3) {
                $list[$key]['oprator'] = $this->userInfo['real_name'];
            }else if($value['status'] == 0) {
                $res = MembersModel::find()->select('real_name')->where(['u_id' => $value['handler']])->asArray()->one();
                $list[$key]['oprator'] = empty($res['real_name']) ? "" : $res['real_name'];
            }else {
                $res = ApplyLogModel::find()->leftJoin('oa_members','oa_apply_log.handler=oa_members.u_id')->select(['oa_members.real_name'])
                    ->where(['oa_apply_log.apply_id' => $value['apply_id']])->orderBy('oa_apply_log.reply_time DESC')->asArray()->one();
                $list[$key]['oprator'] = empty($res['real_name']) ? "" : $res['real_name'];
            }
            unset($list[$key]['handler']);
        }

        //我的申请消息置为已读
        ApplyMsgModel::setRead($uid);

        FResponse::output(['code' => 20000, 'msg' => "ok", 'data'=>['list' => $list,'totalPage' => $totalPage,'perm' => $perm]]);
    }

    //待审批列表
    public function actionApprovalList() {
        $perm = $this->isPermStatus('ApplyMyapprove');
        if(!$perm) {
            FResponse::output(['code' => 20050, 'msg' => "您无访问此功能权限，请找管理员开通~", 'data'=>['perm' => $perm]]);
        }
        $data = json_decode(file_get_contents("php://input"));
        if( empty($data->page) || empty($data->pageSize) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $uid = $this->userInfo['u_id'];
        $page = $data->page;
        $pageSize = $data->pageSize;
        $offset = ($page-1)*$pageSize;
        $res = (new Query())->select('c.apply_id,c.model_id,c.detail_id,c.title,c.flow,c.step,c.create_time,b.real_name,b.head_img,a.modeltype')
            ->from('oa_apply_base c')
            ->leftJoin('oa_members b', 'c.applyer=b.u_id')
            ->leftJoin('oa_apply_model a', 'c.model_id=a.model_id')
            ->where(['c.handler' => $uid, 'c.status' => 0])
            ->orderBy(['c.create_time' => SORT_DESC,'apply_id' => SORT_DESC])
            ->limit($pageSize)
            ->offset($offset)
            ->all();

        //处理列表数据
        $list = [];
        foreach($res as $key => $value) {
            //头像处理
            $value['head_img'] = $this->getUserHeadimg($value['head_img']);
            $list[$key] = $value;
            //自定义表单可以批量审批
            if($value['modeltype'] == 0) {
                $list[$key]['allowBatch'] = true;
            }else {
                //非职级申请 非定制化表单的最后一步审批可以批量审批
                $tempFlow = json_decode($value['flow'],true);
                if($value['model_id'] != 5 && count($tempFlow) > $value['step']) {
                    $list[$key]['allowBatch'] = true;
                }else {
                    $list[$key]['allowBatch'] = false;
                }
            }
            //获取对应申请的部分详情
            $list[$key]['detail'] = AppApplyDelegate::getBaseDetail($value);
            $list[$key]['detail'] = empty($list[$key]['detail']) ? new Object() : $list[$key]['detail'];
            unset($list[$key]['detail_id']);
            unset($list[$key]['flow']);
            unset($list[$key]['step']);
        }

        $num = ApplyBaseModel::find()->where(['handler' => $uid, 'status' => 0])->count();
        $totalPage = ceil($num / $pageSize);

        //待审批消息置为已读
        ApprovalMsgModel::setRead($uid);

        FResponse::output(['code' => 20000, 'msg' => "ok", 'data'=>['list' => $list,'totalPage' => $totalPage,'perm' => $perm]]);
    }

    //已审批列表
    public function actionVerifyList() {
        $perm = $this->isPermStatus('ApplyMyapprove');
        if(!$perm) {
            FResponse::output(['code' => 20050, 'msg' => "您无访问此功能权限，请找管理员开通~", 'data'=>['perm' => $perm]]);
        }
        $data = json_decode(file_get_contents("php://input"));
        if( empty($data->page) || empty($data->pageSize) ) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }

        $con = [];
        if(isset($data->status) && !empty($data->status)) {
            $con = ['a.status' => $data->status];
        }

        $uid = $this->userInfo['u_id'];
        $page = $data->page;
        $pageSize = $data->pageSize;
        $offset = ($page-1)*$pageSize;

        $res = (new Query())->select('c.apply_id,c.model_id,c.detail_id,c.title,a.status,c.create_time,b.real_name,b.head_img')
            ->from('oa_apply_log a')
            ->leftJoin('oa_apply_base c', 'a.apply_id=c.apply_id')
            ->leftJoin('oa_members b', 'c.applyer=b.u_id')
            ->where(['a.handler' => $uid])
            ->andWhere($con)
            ->groupBy('c.apply_id')
            ->orderBy(['c.create_time' => SORT_DESC,'apply_id' => SORT_DESC])
            ->limit($pageSize)
            ->offset($offset)
            ->all();
        $list = [];
        foreach($res as $key => $value) {
            //头像处理
            $value['head_img'] = $this->getUserHeadimg($value['head_img']);
            $list[$key] = $value;
            //获取对应申请的部分详情
            $list[$key]['detail'] = AppApplyDelegate::getBaseDetail($value);
            $list[$key]['detail'] = empty($list[$key]['detail']) ? new Object() : $list[$key]['detail'];
        }

        $num = (new Query())->select('a.apply_id')->from('oa_apply_log a')
            ->leftJoin('oa_apply_base c', 'a.apply_id=c.apply_id')
            ->distinct()
            ->where(['a.handler' => $uid])
            ->andWhere($con)
            ->count();
        $totalPage = ceil($num / $pageSize);

        FResponse::output(['code' => 20000, 'msg' => "ok", 'data'=>['list' => $list,'totalPage' => $totalPage,'perm' => $perm]]);
    }

    //可使用申请列表
    public function actionUsefulList()
    {
        $perm = $this->isPermStatus('ApplyApply');
        if(!$perm) {
            FResponse::output(['code' => 20050, 'msg' => "您无访问此功能权限，请找管理员开通~", 'data'=>['perm' => $perm]]);
        }
        //申请人
        $uid = $this->userInfo['u_id'];
        //申请人所属组
        $org_id = $this->userInfo['org']['org_id'];
        $model_id = ApplyFlowModel::find()->select(['model_id'])
            ->where("(type=0 and visibleman like :uid) or (type=1 and visibleman like :org_id)")
            ->addParams([':uid' => '%,'.$uid.',%',':org_id' => '%,'.$org_id.',%'])
            ->orderBy('model_id ASC')
            ->groupBy('model_id')
            ->column();
        $list = ApplyModelDelegate::getApplyModelList(['model_id','title','modeltype'],['status' => 1,'model_id' => $model_id]);

        FResponse::output(['code' => 20000, 'msg' => "ok", 'data'=> ['list' => $list, 'perm' => $perm]]);
    }

    //申请详情
    public function actionApplyDetail() {
        $data = json_decode(file_get_contents("php://input"),true);
        if( empty($data['apply_id'])) {
            FResponse::output(['code' => 20001, 'msg' => "Params Error", 'data'=>new Object()]);
        }
        $apply_id = $data['apply_id'];
        $apply = ApplyBaseModel::find()->where(['apply_id' => $apply_id])->asArray()->one();
        if(empty($apply)) {
            FResponse::output(['code' => 20016, 'msg' => "数据不存在了~", 'data'=>new Object()]);
        }
        $apply['flow'] = json_decode($apply['flow'],true);
        $apply['last_step'] = false;
        if(count($apply['flow']) == $apply['step']) {
            $apply['last_step'] = true;
        }
        $applyModel = ApplyModel::findOne($apply['model_id']);
        $apply['modeltype'] = $applyModel->modeltype;
        if($applyModel->modeltype == 1) {
            $apply['detail'] = Yii::$app->db->createCommand('select * from '.$applyModel->tablename.' where id='.$apply['detail_id'])->queryOne();
//            $apply['data']['isShowPage'] = false;
            if($apply['model_id']==4){
                $flexibleWorkTime = FlexworkStoreModel::find()->where(['id'=>$apply['detail']['store_id']])->asArray()->one();
                $apply['detail']['flexibleWorkTime'] = date("Y-m-d H:i:s", $flexibleWorkTime['begin_time']).'~'.date("Y-m-d H:i:s", $flexibleWorkTime['end_time']).'(弹性时长：'.$flexibleWorkTime['hours'].')';
                $apply['detail']['att'] = LeaveDelegate::getAtt($apply_id,['apply_att_id','file_name','real_name','file_size','file_path','file_type','create_time']);
            }
            //请假申请或职级申请读取附件
            if($apply['model_id'] == 2 || $apply['model_id'] == 5){
//                $apply['data']['isShowPage'] = false;
                $apply['detail']['att'] = LeaveDelegate::getAtt($apply_id,['apply_att_id','file_name','real_name','file_size','file_path','file_type','create_time']);
            }
            //定制申请内容数据格式设置
//            if($apply['model_id'] == 2) {
//                $apply = ApplyHelper::applyFormatApp($apply);
//            }
        }
        //附件处理
        if(isset($apply['detail']['att']) && !empty($apply['detail']['att'])) {
            foreach($apply['detail']['att'] as $key => $value) {
                $apply['detail']['att'][$key]['full_path'] = Yii::getAlias('@file_root').'/'.$value['file_path'].'/'.$value['real_name'];
            }
        }
        //申请人信息
        $tempApplyerInfo= MembersModel::find()->select('real_name,head_img')->where(['u_id' => $apply['applyer']])->one();
        $apply['applyer_name'] = $tempApplyerInfo['real_name'];
        $apply['head_img'] = $this->getUserHeadimg($tempApplyerInfo['head_img']);
        //当前审批人信息
        if($apply['status'] == 0) {
            $tempHandlerInfo= MembersModel::find()->select('real_name,head_img')->where(['u_id' => $apply['handler']])->one();
//            $apply['handler_name'] = $tempApplyerInfo['real_name'];
//            $apply['handler_head_img'] = Tools::getHeadImg($tempApplyerInfo['head_img']);
        }
        //判断是否是申请人
//        $apply['is_applyer'] = 0;
//        if($this->userInfo['u_id'] == $apply['applyer']) {
//            $apply['is_applyer'] = 1;
//        }
        //判断是否是当前处理人
        $apply['current_handler'] = 0;
        if ($this->userInfo['u_id'] == $apply['handler']) {
            $apply['current_handler'] = 1;
        }
        //审批记录
        $apply['verifyRecorders'] = (new Query())->select('d.comment,d.reply_time,d.status,a.head_img,a.real_name')
            ->from('oa_apply_log d')
            ->leftJoin('oa_members a', 'a.u_id=d.handler')
            ->where(['d.apply_id' => $apply_id])
            ->orderBy(['d.reply_time' => SORT_ASC])
            ->all();
        //加入创建人
        array_unshift($apply['verifyRecorders'],['comment' => '发起申请','reply_time' => $apply['create_time'],'status' => 1,
            'head_img' => $tempApplyerInfo['head_img'],'real_name' => $tempApplyerInfo['real_name']]);
        if(!empty($tempHandlerInfo)) {
            //加入当前审批人
            $apply['verifyRecorders'][] = ['comment' => '审批中','reply_time' => 0,'status' => 3,'head_img' => $tempHandlerInfo['head_img'],'real_name' => $tempHandlerInfo['real_name']];
        }

        if($apply['status'] == 3) {
            //撤回状态加入审批记录
            $apply['verifyRecorders'][] = ['comment' => '已撤回','reply_time' => 0,'status' => 4,'head_img' => $tempApplyerInfo['head_img'],'real_name' => $tempApplyerInfo['real_name']];
        }

        //审批记录时间格式设置
//        $apply['verifyRecorders'] = ApplyHelper::leaveRecordFormat($apply['verifyRecorders']);
        //审批人头像处理
        foreach($apply['verifyRecorders'] as $key => $value) {
            $apply['verifyRecorders'][$key]['head_img'] = $this->getUserHeadimg($value['head_img']);
            $apply['verifyRecorders'][$key]['reply_time'] = $value['reply_time'] > 0 ? date('Y-m-d H:i:s',$value['reply_time']) : '';
        }
//        $apply['form_json'] = json_decode($apply['form_json']);
//        $apply['file_root'] = \Yii::getAlias('@file_root');

        unset($apply['form_json']);
        unset($apply['model_id']);
        unset($apply['detail_id']);
        unset($apply['applyer']);
        unset($apply['handler']);
        unset($apply['flow']);
//        unset($apply['step']);
        unset($apply['create_time']);
        unset($apply['update_time']);
        unset($apply['is_attachment']);
        unset($apply['modeltype']);
        FResponse::output(['code' => 20000, 'msg' => "ok", 'data'=> $apply]);
    }

    /**
     * 发起请假申请显示年假、调休和带薪病假的天数
     * $type 请假类型
     */
    public function actionLeaveApplySum()
    {
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata,true);
        $res = ['sum1'=>0];
        if($postdata['type']==1){//年假
            //判断员工是否转正
            $info = MembersModel::getUserMessage($this->userInfo['u_id'],'is_formal');
            if($info['is_formal']!=1){
                $res = ['sum1'=>0,'sum2'=>0,'sum3'=>0];
                FResponse::output(['code' => 20000, 'msg' => "ok", 'data'=> $res]);
            }
            $arrAnnLeaveSum = AnnualLeaveModel::getAnnualLeave($this->userInfo['u_id']);
            $res['sum1'] = $arrAnnLeaveSum['normal_leave'];
            $res['sum2'] = $arrAnnLeaveSum['delay_leave'];
            $res['sum3'] = $arrAnnLeaveSum['normal_leave']+$arrAnnLeaveSum['delay_leave'];
        }else if($postdata['type']==2){//调休
            $res['sum1'] = LeaveDelegate::getInventorySum($this->userInfo['u_id']);
        }else if($postdata['type']==3){//带薪病假
            $res['sum1'] = LeaveDelegate::getYearSickLeaveSum($this->userInfo['u_id'],time());
        }
        FResponse::output(['code' => 20000, 'msg' => "ok", 'data'=> $res]);
    }

    //发起申请
    public function actionCreateApply() {
        $transaction = \Yii::$app->db->beginTransaction();
        $postdata = file_get_contents("php://input");
        $data = json_decode($postdata,true);
        $model_id = $data['model_id'];
        unset($data['model_id']);
        unset($data['u_id']);
        unset($data['accessToken']);
        $json_data = json_encode($postdata);

        //申请人
        $uid = $this->userInfo['u_id'];
        //申请人所属组
        $org_id = $this->userInfo['org']['org_id'];

        //获取表单设置
        $applyModel = ApplyModel::find()->where(['model_id' => $model_id, 'status' => 1])->asArray()->one();
        if (empty($applyModel)) {
            FResponse::output(['code' => 20001, 'msg' => "该申请单不存在或已停用"]);
        }
        //判断是否达到申请上限
        if (!empty($applyModel['limit_type']) && !empty($applyModel['limit_num'])) {
            if (ApplyDelegate::isUpLimit($model_id, $uid, $applyModel['limit_type'], $applyModel['limit_num'])) {
                FResponse::output(['code' => 20016, 'msg' => "已达到申请上限"]);
            }
        }
        //处理过滤特殊字段(自定义表单)
        if ($applyModel['modeltype'] == 0) {
            $arrData = ApplyDelegate::filterField($data);
        } else {
            switch($model_id) {
                //加班
                case 1:
                    $arrData = OverTimeDelegate::filterData($data,$uid);
                    break;
                //请假
                case 2:
                    $arrData = LeaveHelper::setCreateLeaveApplyData($data);
                    if(!(isset($arrData['code']) && $arrData['code'] == 0)) {
                        $arrLeave = LeaveDelegate::saveLeaveApply($this->userInfo,$arrData);
                        if(isset($arrLeave['code']) && $arrLeave['code'] == 0){
                            $arrData = $arrLeave;
                        }else{
                            //请假申请附件
//                            $arrLeaveAtt = $arrLeave['data']['att'];
                            unset($arrLeave['data']['att']);
                            $arrData = $arrLeave['data'];
                        }
                    }
                    break;
                //忘打卡
                case 3:
                    $arrData = CheckOutDelegate::filterData($data);
                    break;
                //弹性
                case 4:
                    $arrData = FlexWorkDelegate::filterData($data);
                    break;
                //职级申请
                case 5:
                    $arrData = RankDelegate::filterData($data);
                    break;
            }
        }

        //判断数据是否有误
        if(isset($arrData['code']) && $arrData['code'] == 0) {
            $transaction->rollBack();
            FResponse::output(['code' => 20003, 'msg' => $arrData['msg']]);
        }

        //获取流程配置
        $applyFlows = ApplyDelegate::getModelFlow($model_id, $uid, $org_id, ['condition','item','value','flow']);
        //获取该申请流程
        $flow = ApplyDelegate::getApplyFlow($arrData, $applyFlows);
        if (empty($flow)) {
            $transaction->rollBack();
            FResponse::output(['code' => 20004, 'msg' => '不满足申请条件，请重新填写']);
        }
        $uidFlow = ApplyDelegate::getFlow2Uid($uid, $flow, 1);
        //获取下一步审核人
        $nextArr = ApplyDelegate::getNextHandler($uid, $uidFlow, 1);
        //插入详细信息表
        $insRes = \Yii::$app->db->createCommand()->insert($applyModel['tablename'], $arrData)->execute();
        $detail_id = \Yii::$app->db->getLastInsertID();

        $applyBase = new ApplyBaseModel();
        $applyBase->model_id = $model_id;
        $applyBase->detail_id = $detail_id;
        $applyBase->title = $applyModel['title'];
        $applyBase->applyer = $uid;
        $applyBase->handler = $nextArr['nextHand'];
        $applyBase->form_json = $json_data;
        $applyBase->flow = json_encode($uidFlow);
        $applyBase->step = $nextArr['step'];
        $applyBase->create_time = time();
        $applyBase->update_time = time();
        if(isset($data['att']) && !empty($data['att'])) {
            $applyBase->is_attachment = 1;
        }
        //下一步审核人为假时 该申请单已完成
        if(!$nextArr['nextHand']) {
            $applyBase->status = 1;
        }
        if ($applyBase->save(false)) {
            $apply_id = $applyBase->apply_id;
            if(isset($data['att']) && !empty($data['att'])) {
                //保存附件
                if(count($data['att']) > 0){
                    //设置附件格式
                    $arrLeaveAtt = LeaveHelper::leaveAttFormat($data['att'],$apply_id);
                    //将附件插入oa_apply_attachment表
                    //修改对应字段-----------------------------------------------------------------------------------------------------------
                    $attRes = LeaveDelegate::saveLeaveAtt($arrLeaveAtt);
                    if (!$attRes) {
                        $transaction->rollBack();
                        FResponse::output(['code' => 20005, 'msg' => '申请失败，请重试！']);
                    }
                }
            }
            switch($model_id) {
                //请假
                case 2:
                    //保存使用日志
                    if(count($arrLeave['usedData']) > 0){
                        $arrLeave['usedData'] = LeaveHelper::leaveUsedFormat($arrLeave['usedData'],$arrData,$this->userInfo['u_id'],$apply_id);
                        $usedRes = LeaveDelegate::saveLeaveUsed($arrLeave['usedData']);
                        if($usedRes['code']==0){
                            $transaction->rollBack();
                            FResponse::output(['code' => 20005, 'msg' => '申请失败，请重试！']);
                        }
                    }
                    break;
            }
            //写入消息
            Tools::addApprovalMsg($applyBase->handler,$applyBase->apply_id,$applyBase->applyer,$applyBase->title,$this->userInfo['real_name']);
            $transaction->commit();
            FResponse::output(['code' => 20000, 'msg' => 'ok']);
        } else {
            $transaction->rollBack();
            FResponse::output(['code' => 20005, 'msg' => '申请失败，请重试！']);
        }
    }

    //发起申请(编辑)
    public function actionEditApply()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        $postdata = file_get_contents("php://input");
        $data = json_decode($postdata,true);
        $apply_id = $data['apply_id'];
        unset($data['apply_id']);
        unset($data['u_id']);
        unset($data['accessToken']);
        $json_data = json_encode($postdata);

        if(empty($apply_id)) {
            FResponse::output(['code' => 20001, 'msg' => "参数错误"]);
        }

        $apply = ApplyBaseModel::findOne($apply_id);
        if(empty($apply)) {
            FResponse::output(['code' => 20016, 'msg' => "申请单不存在"]);
        }

        //申请人
        $uid = $this->userInfo['u_id'];
        //申请人所属组
        $org_id = $this->userInfo['org']['org_id'];

        if($apply->applyer != $uid) {
            FResponse::output(['code' => 20003, 'msg' => "非该申请发起人不能进行修改"]);
        }

        //获取表单设置
        $applyModel = ApplyModel::find()->where(['model_id' => $apply->model_id, 'status' => 1])->asArray()->one();
        if (empty($applyModel)) {
            FResponse::output(['code' => 20004, 'msg' => "该申请单不存在或已停用"]);
        }

        //判断是否达到申请上限
//        if (!empty($applyModel['limit_type']) && !empty($applyModel['limit_num'])) {
//            if (ApplyDelegate::isUpLimit($apply->model_id, $uid, $applyModel['limit_type'], $applyModel['limit_num'])) {
//                return ['code' => 0, 'msg' => '已达到申请上限'];
//            }
//        }

        //处理过滤特殊字段(自定义表单)
        if ($applyModel['modeltype'] == 0) {
            $arrData = ApplyDelegate::filterField($data);
        } else {
            switch($apply->model_id) {
                //加班
                case 1:
                    $arrData = OverTimeDelegate::filterData($data,$uid,'edit',$apply->detail_id);
                    break;
                //请假
                case 2:
                    $arrData = LeaveHelper::setCreateLeaveApplyData($data);
                    if(!(isset($arrData['code']) && $arrData['code'] == 0)) {
                        $arrLeave = LeaveDelegate::saveLeaveApply($this->userInfo,$arrData);
                        if(isset($arrLeave['code']) && $arrLeave['code'] == 0){
                            $arrData = $arrLeave;
                        }else{
                            //请假申请附件
                            $arrLeaveAtt = $arrLeave['data']['att'];
                            unset($arrLeave['data']['att']);
//                            unset($arrLeave['data']['apply_id']);
                            $arrData = $arrLeave['data'];
                        }
                    }
                    break;
                //忘打卡
                case 3:
                    $arrData = CheckOutDelegate::filterData($data);
                    break;
                //弹性
                case 4:
                    $arrData = FlexWorkDelegate::filterData($data,'edit',$apply->detail_id);
                    break;
                //职级申请
                case 5:
                    $arrData = RankDelegate::filterData($data);
                    break;
            }
        }

        //判断数据是否有误
        if(isset($arrData['code']) && $arrData['code'] == 0) {
            $transaction->rollBack();
            FResponse::output(['code' => 20005, 'msg' => $arrData['msg']]);
        }

        //获取流程配置
        $applyFlows = ApplyDelegate::getModelFlow($apply->model_id, $uid, $org_id, ['condition','item','value','flow']);
        //获取该申请流程
        $flow = ApplyDelegate::getApplyFlow($arrData, $applyFlows);
        if (empty($flow)) {
            FResponse::output(['code' => 20006, 'msg' => "不满足申请条件，请重新填写"]);
        }
        $uidFlow = ApplyDelegate::getFlow2Uid($uid, $flow, 1);
        //获取下一步审核人
        $nextArr = ApplyDelegate::getNextHandler($uid, $uidFlow, 1);
        //更新详细信息表
        \Yii::$app->db->createCommand()->update($applyModel['tablename'], $arrData, ['id' => $apply->detail_id])->execute();

        $apply->handler = $nextArr['nextHand'];
        $apply->form_json = $json_data;
        $apply->flow = json_encode($uidFlow);
        $apply->step = $nextArr['step'];
        $apply->status = 0;
        $apply->update_time = time();
        if(isset($data['att']) && !empty($data['att'])) {
            $apply->is_attachment = 1;
        }else {
            $apply->is_attachment = 0;
        }
        //下一步审核人为假时 该申请单已完成
        if(!$nextArr['nextHand']) {
            $apply->status = 1;
        }
        if ($apply->save(false)) {
            //删除旧附件
            $resDelOldAtt = LeaveDelegate::delOldAtt($apply_id);
            if (!$resDelOldAtt) {
                $transaction->rollBack();
                FResponse::output(['code' => 20007, 'msg' => '申请失败，请重试！']);
            }
            //保存附件
            if(isset($data['att']) && !empty($data['att'])) {
                //移动端不能修改职级申请附件
                if (count($data['att']) > 0 && $apply->model_id != 5) {
                    //设置附件格式
                    $arrLeaveAtt = LeaveHelper::leaveAttFormat($data['att'], $apply_id);
                    //将附件插入oa_apply_attachment表
                    //修改对应字段-----------------------------------------------------------------------------------------------------------
                    $attRes = LeaveDelegate::saveLeaveAtt($arrLeaveAtt);
                    if (!$attRes) {
                        $transaction->rollBack();
                        FResponse::output(['code' => 20007, 'msg' => '申请失败，请重试！']);
                    }
                }
            }
            switch($apply->model_id) {
                //请假
                case 2:
                    //保存使用日志
                    if(count($arrLeave['usedData']) > 0){
                        $arrLeave['usedData'] = LeaveHelper::leaveUsedFormat($arrLeave['usedData'],$arrData,$this->userInfo['u_id'],$apply_id);
                        $usedRes = LeaveDelegate::saveLeaveUsed($arrLeave['usedData'],$apply_id);
                        if($usedRes['code']==0){
                            $transaction->rollBack();
                            FResponse::output(['code' => 20007, 'msg' => '申请失败，请重试！']);
                        }
                    }
                    break;
            }
            //写入消息
            Tools::addApprovalMsg($apply->handler,$apply->apply_id,$apply->applyer,$apply->title,$this->userInfo['real_name']);
            $transaction->commit();
            FResponse::output(['code' => 20000, 'msg' => '修改申请成功']);
        } else {
            $transaction->rollBack();
            FResponse::output(['code' => 20007, 'msg' => '申请失败，请重试！']);
        }
    }

    //审批
    public function actionVerify()
    {
        $perm = $this->isPermStatus('ApplyMyapprove');
        if(!$perm) {
            FResponse::output(['code' => 20050, 'msg' => "您无访问此功能权限，请找管理员开通~", 'data'=>['perm' => $perm]]);
        }
        $transaction = \Yii::$app->db->beginTransaction();
        $data = json_decode(file_get_contents("php://input"));
        if(!isset($data->apply_id) || empty($data->apply_id)) {
            FResponse::output(['code' => 20001, 'msg' => "参数错误"]);
        }
        $apply = ApplyBaseModel::findOne($data->apply_id);
        if (empty($apply)) {
            FResponse::output(['code' => 20016, 'msg' => "该申请单不存在"]);
        }
        if($apply->status != 0) {
            FResponse::output(['code' => 20003, 'msg' => "当前申请单已被处理,或已被撤回"]);
        }
        $uid = $this->userInfo['u_id'];
        if($uid != $apply->handler) {
            FResponse::output(['code' => 20004, 'msg' => "您不是当前审核人，没有审核权限"]);
        }
        //如果是职级申请的第一步审批
        if($apply->model_id == 5 && $apply->step == 1) {
            if(!isset($data->level_rank) || empty($data->level_rank)) {
                FResponse::output(['code' => 20005, 'msg' => "请填写职级"]);
            }
        }
        $res = ApplyDelegate::verify($uid,$apply,$data,$this->userInfo);
        if(isset($res['code']) && $res['code'] == 0) {
            $transaction->rollBack();
            FResponse::output(['code' => 20006, 'msg' => $res['msg']]);
        }
        if($res === true) {
            $transaction->commit();
            FResponse::output(['code' => 20000, 'msg' => 'ok']);
        }else {
            $transaction->rollBack();
            FResponse::output(['code' => 20007, 'msg' => '审批通过失败']);
        }
    }

    //驳回
    public function actionRefuse()
    {
        $perm = $this->isPermStatus('ApplyMyapprove');
        if(!$perm) {
            FResponse::output(['code' => 20050, 'msg' => "您无访问此功能权限，请找管理员开通~", 'data'=>['perm' => $perm]]);
        }
        $transaction = \Yii::$app->db->beginTransaction();
        $data = json_decode(file_get_contents("php://input"));
        if(!isset($data->apply_id) || empty($data->apply_id)) {
            FResponse::output(['code' => 20001, 'msg' => "参数错误"]);
        }
        if(!isset($data->comment) || $data->comment == '') {
            FResponse::output(['code' => 20016, 'msg' => "未填写审批意见"]);
        }
        $apply = ApplyBaseModel::findOne($data->apply_id);
        if (empty($apply)) {
            FResponse::output(['code' => 20003, 'msg' => "该申请单不存在"]);
        }
        if($apply->status != 0) {
            FResponse::output(['code' => 20004, 'msg' => "当前申请单已被处理,或已被撤回"]);
        }
        $uid = $this->userInfo['u_id'];
        if($uid != $apply->handler) {
            FResponse::output(['code' => 20005, 'msg' => "您不是当前审核人，没有审核权限"]);
        }
        if(ApplyDelegate::refuse($uid,$apply,$data->comment)) {
            $transaction->commit();
            FResponse::output(['code' => 20000, 'msg' => "ok"]);
        }else {
            $transaction->rollBack();
            FResponse::output(['code' => 20006, 'msg' => "审批驳回失败"]);
        }
    }

    //撤回
    public function actionRevoke()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        $data = json_decode(file_get_contents("php://input"));
        if(!isset($data->apply_id) || empty($data->apply_id)) {
            FResponse::output(['code' => 20001, 'msg' => "参数错误"]);
        }
        $apply = ApplyBaseModel::findOne($data->apply_id);
        if (empty($apply)) {
            FResponse::output(['code' => 20016, 'msg' => "该申请单不存在"]);
        }
        if($apply->status != 0) {
            FResponse::output(['code' => 20003, 'msg' => "当前申请单该状态不允许撤回"]);
        }
        $uid = $this->userInfo['u_id'];
        if($apply->applyer != $uid) {
            FResponse::output(['code' => 20004, 'msg' => "无权限进行该操作"]);
        }
        $apply->handler = 0;
        $apply->status = 3;
        if ($apply->save(false)) {
            $res = true;
            //弹性工作撤回修改弹性库存
            if($apply->model_id == 4) {
                $res = FlexWorkDelegate::updateUsefulStore($apply->detail_id);
            }
            //请假申请驳回数据处理
            if($apply->model_id == 2){
                $res = LeaveDelegate::returnLeaveData($uid,$apply,'撤回');
            }

            if($res){
                $transaction->commit();
                FResponse::output(['code' => 20000, 'msg' => "申请撤回成功"]);
            }else{
                $transaction->rollBack();
                FResponse::output(['code' => 20005, 'msg' => "申请撤回失败"]);
            }
        } else {
            $transaction->rollBack();
            FResponse::output(['code' => 20005, 'msg' => "申请撤回失败"]);
        }
    }

    //催办
    public function actionPress()
    {
        $data = json_decode(file_get_contents("php://input"));
        if(!isset($data->apply_id) || empty($data->apply_id)) {
            FResponse::output(['code' => 20001, 'msg' => "参数错误"]);
        }
        $apply = ApplyBaseModel::findOne($data->apply_id);
        if (empty($apply)) {
            FResponse::output(['code' => 20016, 'msg' => "该申请单不存在"]);
        }
        if($apply->is_press != 0) {
            FResponse::output(['code' => 20003, 'msg' => "该申请单已催办过"]);
        }
        $uid = $this->userInfo['u_id'];
        if($apply->applyer != $uid) {
            FResponse::output(['code' => 20004, 'msg' => "无权限进行该操作"]);
        }
//        $apply->is_press = 1;
//        if($apply->save(false)) {
            $content = $this->userInfo['real_name']."正向您催办".date('Y年m月d日',$apply->create_time)."提交的".$apply->title.",请您尽快处理";
            $subject = 'OA申请催办';
            $receiver = MembersModel::find()->select('username')->where(['u_id' => $apply->handler])->column();
            Tools::asynSendMail($subject, $content, $receiver[0]);
        //极光推送
        $modeltype = ApplyBaseModel::find()->leftJoin('oa_apply_model','oa_apply_base.model_id=oa_apply_model.model_id')
            ->select('oa_apply_model.model_id,modeltype')->where(['oa_apply_base.apply_id' => $data->apply_id])->asArray()->one();
            Tools::msgJpush(5,$apply->apply_id,$this->userInfo['real_name'].'正向您催办'.date('Y-m-d H:i:s').'提交的'.$apply->title.'，请您尽快处理',[$apply->handler],$modeltype);
            FResponse::output(['code' => 20000, 'msg' => "ok"]);
//        }else {
//            FResponse::output(['code' => 20005, 'msg' => "催办失败"]);
//        }
    }

    //删除申请
    public function actionApplyDel()
    {
        $data = json_decode(file_get_contents("php://input"));
        if(!isset($data->apply_id) || empty($data->apply_id)) {
            FResponse::output(['code' => 20001, 'msg' => "参数错误"]);
        }
        $apply_id = $data->apply_id;
        $apply = ApplyBaseModel::findOne($apply_id);
        if (empty($apply)) {
            FResponse::output(['code' => 20016, 'msg' => "该申请单不存在"]);
        }
        if ($apply->status != 3 && $apply->status != 2) {
            FResponse::output(['code' => 20003, 'msg' => "非撤回或已驳回状态申请单不允许删除"]);
        }
        $uid = $this->userInfo['u_id'];
        if($apply->applyer != $uid) {
            FResponse::output(['code' => 20004, 'msg' => "无权限进行该操作"]);
        }
        ApplyBaseModel::deleteAll(['apply_id' => $apply_id]);
        ApplyLogModel::deleteAll(['apply_id' => $apply_id]);
        ApplyAttachmentModel::deleteAll(['apply_id' => $apply_id]);
        //删除详情
        $applyModel = ApplyModel::findOne($apply->model_id);
        $sql = 'DELETE FROM ' . $applyModel->tablename . ' WHERE id=' . $apply->detail_id;
        Yii::$app->db->createCommand($sql)->execute();
        FResponse::output(['code' => 20000, 'msg' => "ok"]);
    }

    //批量审批通过
    public function actionBatchVerify()
    {
        $data = json_decode(file_get_contents("php://input"));
        if(!isset($data->apply_ids) || empty($data->apply_ids)) {
            FResponse::output(['code' => 20001, 'msg' => "参数错误", 'data' => new Object()]);
        }
        $uid = $this->userInfo['u_id'];
        $successNum = 0;
        $failNum = 0;
//        $failList = [];
        foreach($data->apply_ids as $key => $value) {
            $apply = ApplyBaseModel::findOne($value);
            if (empty($apply)) {
                continue;
            }
            if($apply->status != 0) {
                $failNum ++;
//                $applyer = MembersModel::find()->select('real_name')->where(['u_id' => $apply->applyer])->column();
//                $failList[$key] = ['title' => $apply->title,'applyer' => $applyer[0],'create_time' => date('Y-m-d H:i:s',$apply->create_time)];
                continue;
            }
            if($uid != $apply->handler) {
                $failNum ++;
//                $applyer = MembersModel::find()->select('real_name')->where(['u_id' => $apply->applyer])->column();
//                $failList[$key] = ['title' => $apply->title,'applyer' => $applyer[0],'create_time' => date('Y-m-d H:i:s',$apply->create_time)];
                continue;
            }
            if(ApplyDelegate::verify($uid,$apply,$data,$this->userInfo) === true) {
                $successNum ++;
            }else {
                $failNum ++;
//                $applyer = MembersModel::find()->select('real_name')->where(['u_id' => $apply->applyer])->column();
//                $failList[$key] = ['title' => $apply->title,'applyer' => $applyer[0],'create_time' => date('Y-m-d H:i:s',$apply->create_time)];
            }
        }
        FResponse::output(['code' => 20000, 'msg' => "ok", 'data' => ['successNum' => $successNum,'failNum' => $failNum]]);
    }

    //批量驳回
    public function actionBatchRefuse()
    {
        $data = json_decode(file_get_contents("php://input"));
        if(!isset($data->apply_ids) || empty($data->apply_ids)) {
            FResponse::output(['code' => 20001, 'msg' => "参数错误", 'data' => new Object()]);
        }
        if(!isset($data->comment) || $data->comment == '') {
            FResponse::output(['code' => 20016, 'msg' => "未填写审批意见", 'data' => new Object()]);
        }
        $uid = $this->userInfo['u_id'];
        $successNum = 0;
        $failNum = 0;
//        $failList = [];
        foreach($data->apply_ids as $key => $value) {
            $apply = ApplyBaseModel::findOne($value);
            if (empty($apply)) {
                continue;
            }
            if($apply->status != 0) {
                $failNum ++;
//                $applyer = MembersModel::find()->select('real_name')->where(['u_id' => $apply->applyer])->column();
//                $failList[$key] = ['title' => $apply->title,'applyer' => $applyer,'create_time' => date('Y-m-d H:i:s',$apply->create_time)];
                continue;
            }
            if($uid != $apply->handler) {
                $failNum ++;
//                $applyer = MembersModel::find()->select('real_name')->where(['u_id' => $apply->applyer])->column();
//                $failList[$key] = ['title' => $apply->title,'applyer' => $applyer,'create_time' => date('Y-m-d H:i:s',$apply->create_time)];
                continue;
            }
            if(ApplyDelegate::refuse($uid,$apply,$data->comment)) {
                $successNum ++;
            }else {
                $failNum ++;
//                $applyer = MembersModel::find()->select('real_name')->where(['u_id' => $apply->applyer])->column();
//                $failList[$key] = ['title' => $apply->title,'applyer' => $applyer,'create_time' => date('Y-m-d H:i:s',$apply->create_time)];
            }
        }
        FResponse::output(['code' => 20000, 'msg' => "ok", 'data' => ['successNum' => $successNum,'failNum' => $failNum]]);
    }

    //加班申请最后一步审批回显
    public function actionOvertime()
    {
        $data = json_decode(file_get_contents("php://input"));
        if(!isset($data->apply_id) || empty($data->apply_id)) {
            FResponse::output(['code' => 20001, 'msg' => "参数错误",'data' => new Object()]);
        }
        $apply = ApplyBaseModel::findOne($data->apply_id);
        if (empty($apply)) {
            FResponse::output(['code' => 20016, 'msg' => "该申请单不存在",'data' => new Object()]);
        }
        if($apply->status != 0) {
            FResponse::output(['code' => 20003, 'msg' => "当前申请单已被处理,或已被撤回",'data' => new Object()]);
        }
        $uid = $this->userInfo['u_id'];
        if($uid != $apply->handler) {
            FResponse::output(['code' => 20004, 'msg' => "您不是当前审核人，没有审核权限",'data' => new Object()]);
        }
        $detail = ApplyOvertimeModel::findOne($apply->detail_id);
        if (empty($detail)) {
            FResponse::output(['code' => 20005, 'msg' => "数据不存在",'data' => new Object()]);
        }
        $workDate = date('Y-m-d',$detail->begin_time);
        //获取考勤记录
        $atten = AttendanceModel::find()->where(['u_id' => $apply->applyer,'workDate' => strtotime($workDate)])->one();
        if(empty($atten)) {
            FResponse::output(['code' => 20006, 'msg' => "尚未生成打卡记录，不能作最后一步审批",'data' => new Object()]);
        }
        if(empty($atten->onTime) || empty($atten->offTime)) {
            FResponse::output(['code' => 20000, 'msg' => "ok",'data' => ['begin_time' => '--','end_time' => '--','real_hours' => 0]]);
        }
        //获取加班起算时间
        $workDayConfig = WorkSetModel::findOne(1);
        //工作日加班
        if($detail->type == 1) {
            $real_hours = round(($atten->offTime - strtotime($workDate.$workDayConfig->workday_time)) / 3600,1);
//            $begin_time = $workDate.' '.$workDayConfig->workday_time;
            $begin_time = date('Y-m-d H:i:s',$atten->onTime);
            $end_time = date('Y-m-d H:i:s',$atten->offTime);
        }
        //节假日加班
        if($detail->type == 2) {
            $real_hours = round(($atten->offTime - $atten->onTime) / 3600,1) - $workDayConfig->unworkday_time;
            $begin_time = date('Y-m-d H:i:s',$atten->onTime);
            $end_time = date('Y-m-d H:i:s',$atten->offTime);
        }
        //计算实际加班时长
        if($real_hours > 0) {
            if(round($real_hours) <= $real_hours) {
                $real_hours = floor($real_hours);
            }else {
                $real_hours = floatval(floor($real_hours).'.5');
            }
        }else {
            $real_hours = 0;
        }
        FResponse::output(['code' => 20000, 'msg' => "ok", 'data' => ['begin_time' => $begin_time,'end_time' => $end_time,'real_hours' => $real_hours]]);
    }

    /**
     * 附件上传
     */
    public function actionUpload()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        $data = FileUploadHelper::fileUpload(Yii::getAlias('@apply'));
        if($data['data']){
            unset($data['data']['full_path']);
            $data['data']['path'] =Yii::getAlias('@file_root') . '/' . $data['data']['file_path'] . '/'.$data['data']['real_name'];
            FResponse::output(['code' => 20000, 'msg' => "ok",'data' => $data['data']]);
        }else{
            FResponse::output(['code' => 20001, 'msg' => "上传失败,请重试！",'data' => new Object()]);
        }
    }
    /**
     * 请假申请最后一步审批获取打卡时间
     * $apply_id
     */
    public function actionLeaveClockTime()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        $res = LeaveDelegate::getClockTime($postdata['apply_id']);
        if(count($res)>0){
            FResponse::output(['code' => 20000, 'msg' => "ok", 'data' => $res]);
        }else{
            FResponse::output(['code' => 20001, 'msg' => "尚未生成打卡记录，不能作最后一步审批",'data' => new Object()]);
        }
    }

    //获取可用弹性库存
    public function actionFlexibleWork()
    {
        $timeData = array();
        $workTime = FlexWorkDelegate::getUsefulStore($this->userInfo['u_id']);
        $res = [];
        foreach ($workTime as $key => $val){
            $timeData['beginTime'] = date("Y-m-d H:i:s", $val['begin_time']);
            $timeData['endTime'] = date("Y-m-d H:i:s", $val['end_time']);
            $timeData['hours'] = $val['hours'];
            $res[$key]['id'] = $val['id'];
            $res[$key]['timeOutData'] = $timeData['beginTime'].'~'.$timeData['endTime'].'(弹性时长：'.$timeData['hours'].')';
        }
        FResponse::output(['code'=>20000,'msg'=>'ok','data' => $res]);
    }

    //职级最后一步审批回显
    public function actionGetRankLevel()
    {
        $data = json_decode(file_get_contents("php://input"));
        if(!isset($data->apply_id) || empty($data->apply_id)) {
            FResponse::output(['code' => 20001, 'msg' => "参数错误"]);
        }
        $info = ApplyBaseModel::find()->select(['detail_id'])->where(['apply_id' => $data->apply_id])->asArray()->one();
        $rankLevel = ApplyRankModel::find()->select('rank_level')->where(['id'=>$info['detail_id']])->asArray()->one();
        FResponse::output(['code'=>20000,'msg'=>'ok','data' => $rankLevel]);
    }
}