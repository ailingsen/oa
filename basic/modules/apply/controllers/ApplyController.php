<?php
/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2016/8/2
 * Time: 17:23
 */

namespace app\modules\apply\controllers;

use app\models\ApplyRankModel;
use app\models\AttendanceModel;
use yii;
use app\controllers\BaseController;
use app\lib\FileUploadHelper;
use yii\web\Controller;
use app\lib\Tools;
use app\lib\FResponse;
use app\models\AnnualLeaveModel;
use app\models\ApplyAttachmentModel;
use app\models\ApplyBaseModel;
use app\models\ApplyLogModel;
use app\models\ApplyModel;
use app\models\MembersModel;
use app\models\ApplyOvertimeModel;
use app\models\FlexworkStoreModel;
use app\modules\apply\delegate\CheckOutDelegate;
use app\modules\apply\delegate\FlexWorkDelegate;
use app\modules\apply\delegate\RankDelegate;
use app\modules\apply\helper\ApplyHelper;
use app\modules\apply\helper\LeaveHelper;
use app\modules\apply\delegate\ApplyDelegate;
use app\modules\apply\delegate\OverTimeDelegate;
use app\modules\apply\delegate\LeaveDelegate;
use yii\db\Query;
use app\models\WorkSetModel;

class ApplyController extends BaseController
{
    public $modelClass = 'app\models\ApplyBaseModel';
    const PAGE_SIZE = 10;

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
                return ['data'=>$res];
            }
            $arrAnnLeaveSum = AnnualLeaveModel::getAnnualLeave($this->userInfo['u_id']);
            if(!isset($arrAnnLeaveSum['normal_leave']) || empty($arrAnnLeaveSum['normal_leave'])){
                $res['sum1'] = 0;
            }else{
                $res['sum1'] = $arrAnnLeaveSum['normal_leave'];
            }
            if(!isset($arrAnnLeaveSum['delay_leave']) ||empty($arrAnnLeaveSum['delay_leave'])){
                $res['sum2'] = 0;
            }else{
                $res['sum2'] = $arrAnnLeaveSum['delay_leave'];
            }
            $res['sum3'] = $arrAnnLeaveSum['normal_leave']+$arrAnnLeaveSum['delay_leave'];
        }else if($postdata['type']==2){//调休
            $res['sum1'] = LeaveDelegate::getInventorySum($this->userInfo['u_id']);
        }else if($postdata['type']==3){//带薪病假
            $res['sum1'] = LeaveDelegate::getYearSickLeaveSum($this->userInfo['u_id'],time());
        }
        return ['data'=>$res];
    }

    /**
     * 获取请假申请的类型
    */
    public function actionLeaveApplyType()
    {
        $data = LeaveDelegate::getLeaveApplyType();
        return ['data'=>$data];
    }

    //发起申请
    public function actionCreateApply()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        $postdata = file_get_contents("php://input");
        $data = json_decode($postdata,true);
        $model_id = $data['model_id'];
        unset($data['model_id']);
        $json_data = json_encode($postdata);

        //申请人
        $uid = $this->userInfo['u_id'];
        //申请人所属组
        $org_id = $this->userInfo['org']['org_id'];

        //获取表单设置
        $applyModel = ApplyModel::find()->where(['model_id' => $model_id, 'status' => 1])->asArray()->one();
        if (empty($applyModel)) {
            return ['code' => 0, 'msg' => '该申请单不存在或已停用'];
        }
        //判断是否达到申请上限
        if (!empty($applyModel['limit_type']) && !empty($applyModel['limit_num'])) {
            if (ApplyDelegate::isUpLimit($model_id, $uid, $applyModel['limit_type'], $applyModel['limit_num'])) {
                return ['code' => 0, 'msg' => '已达到申请上限'];
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
            return $arrData;
        }

        //获取流程配置
        $applyFlows = ApplyDelegate::getModelFlow($model_id, $uid, $org_id, ['condition','item','value','flow']);
        //获取该申请流程
        $flow = ApplyDelegate::getApplyFlow($arrData, $applyFlows);
        if (empty($flow)) {
            $transaction->rollBack();
            return ['code' => 0, 'msg' => '不满足申请条件，请重新填写'];
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
                        return ['code' => 0, 'msg' => '申请失败，请重试！'];
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
                            return $usedRes;
                        }
                    }
                    break;
            }
            //写入消息
            Tools::addApprovalMsg($applyBase->handler,$applyBase->apply_id,$applyBase->applyer,$applyBase->title,$this->userInfo['real_name']);
            $transaction->commit();
            return ['code' => 1, 'msg' => '发起申请成功'];
        } else {
            $transaction->rollBack();
            return ['code' => 0, 'msg' => '发起申请失败'];
        }
    }

    /*
     * 职级申请文件上传
     */
    public function actionRankApplyFile()
    {
        $ext=[];
        $filePath = Yii::getAlias('@file_root');
        $fileInfo = FileUploadHelper::fileUpload($filePath,50,$ext);
        FResponse::output(['code'=>20000,'msg' => 'ok', 'data'=>$fileInfo]);
    }
    /*
     * 职级删除附件
     */
    public function actionDelRankFile()
    {
        $attId = \Yii::$app->request->get('attId');
        $delFile = ApplyAttachmentModel::deleteAll(['apply_att_id'=>$attId]);
        if($delFile){
            FResponse::output(['code'=>20000,'msg' => 'ok']);
        }else{
            FResponse::output(['code'=>0,'msg' => '删除失败！']);
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
        $json_data = json_encode($postdata);

        if(empty($apply_id)) {
            return ['code' => 0, 'msg' => '参数错误'];
        }

        $apply = ApplyBaseModel::findOne($apply_id);
        if(empty($apply)) {
            return ['code' => 0, 'msg' => '申请单不存在'];
        }

        //申请人
        $uid = $this->userInfo['u_id'];
        //申请人所属组
        $org_id = $this->userInfo['org']['org_id'];

        if($apply->applyer != $uid) {
            return ['code' => 0, 'msg' => '非该申请发起人不能进行修改'];
        }

        //获取表单设置
        $applyModel = ApplyModel::find()->where(['model_id' => $apply->model_id, 'status' => 1])->asArray()->one();
        if (empty($applyModel)) {
            return ['code' => 0, 'msg' => '该申请单不存在或已停用'];
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
            return $arrData;
        }

        //获取流程配置
        $applyFlows = ApplyDelegate::getModelFlow($apply->model_id, $uid, $org_id, ['condition','item','value','flow']);
        //获取该申请流程
        $flow = ApplyDelegate::getApplyFlow($arrData, $applyFlows);
        if (empty($flow)) {
            return ['code' => 0, 'msg' => '不满足申请条件，请重新填写'];
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
                return ['code' => 0, 'msg' => '申请失败，请重试！'];
            }
            //保存附件
            if(isset($data['att']) && !empty($data['att'])) {
                if (count($data['att']) > 0) {
                    //设置附件格式
                    $arrLeaveAtt = LeaveHelper::leaveAttFormat($data['att'], $apply_id);
                    //将附件插入oa_apply_attachment表
                    //修改对应字段-----------------------------------------------------------------------------------------------------------
                    $attRes = LeaveDelegate::saveLeaveAtt($arrLeaveAtt);
                    if (!$attRes) {
                        $transaction->rollBack();
                        return ['code' => 0, 'msg' => '申请失败，请重试！'];
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
                            return $usedRes;
                        }
                    }
                    break;
            }
            //写入消息
            Tools::addApprovalMsg($apply->handler,$apply->apply_id,$apply->applyer,$apply->title,$this->userInfo['real_name']);
            $transaction->commit();
            return ['code' => 1, 'msg' => '修改申请成功'];
        } else {
            $transaction->rollBack();
            return ['code' => 0, 'msg' => '修改申请失败'];
        }
    }

    //我的申请列表
    public function actionApplyList()
    {
        $data = json_decode(file_get_contents("php://input"));

        //申请人
        $uid = $this->userInfo['u_id'];

        $page = 1;
        if (isset($data->page) && !empty($data->page)) {
            $page = $data->page;
        }

        $con1 = $con2 = $con3 = $con4 = '1=1';
        $arr1 = $arr2 = array();
        //搜索
        if (isset($data->begin) && !empty($data->begin)) {
            $con1 = 'create_time>=:begin';
            $arr1 = [':begin' => strtotime($data->begin)];
        }
        if (isset($data->end) && !empty($data->end)) {
            $con2 = 'create_time<:end';
            $arr2 = [':end' => strtotime('+1 day', strtotime($data->end))];
        }
        if (isset($data->model_id) && !empty($data->model_id)) {
            $con3 = ['model_id' => $data->model_id];
        }
        if (isset($data->status) && $data->status != -1) {
            $con4 = ['status' => $data->status];
        }

        $current = ($page - 1) * self::PAGE_SIZE;
        $list = ApplyBaseModel::find()->select('apply_id,model_id,title,status,is_press,is_attachment,create_time')
            ->where(['applyer' => $uid])
            ->andWhere($con1, $arr1)
            ->andWhere($con2, $arr2)
            ->andWhere($con3)
            ->andWhere($con4)
            ->orderBy(['create_time' => SORT_DESC,'apply_id' => SORT_DESC])
            ->limit(self::PAGE_SIZE)
            ->offset($current)
            ->all();
        $num = ApplyBaseModel::find()->where(['applyer' => $uid])
            ->andWhere($con1, $arr1)
            ->andWhere($con2, $arr2)
            ->andWhere($con3)
            ->andWhere($con4)
            ->count();
        $sumPage = ceil($num / self::PAGE_SIZE);
        return ['code' => 1, 'msg' => 'ok', 'list' => $list, 'sumPage' => $sumPage, 'curPage' => $page];
    }

    //我的待办
    public function actionApplyAgent()
    {
        $data = json_decode(file_get_contents("php://input"));

        //审批人
        $uid = $this->userInfo['u_id'];

        $page = 1;
        if (isset($data->page) && !empty($data->page)) {
            $page = $data->page;
        }

        $con1 = $con2 = $con3 = $con4 = $con5 = '1=1';
        $arr1 = $arr2 = array();
        //搜索
        if (isset($data->begin) && !empty($data->begin)) {
            $con1 = 'c.create_time>=:begin';
            $arr1 = [':begin' => strtotime($data->begin)];
        }
        if (isset($data->end) && !empty($data->end)) {
            $con2 = 'c.create_time<:end';
            $arr2 = [':end' => strtotime('+1 day', strtotime($data->end))];
        }
        if (isset($data->model_id) && !empty($data->model_id)) {
            $con3 = ['c.model_id' => $data->model_id];
        }
        if (isset($data->status) && $data->status == 0) {
            $con4 = ['c.status' => $data->status];
        }
        if (isset($data->status) && $data->status != 0) {
            $con4 = ['a.status' => $data->status];
        }
        if (isset($data->search) && $data->search != '') {
            $uids = MembersModel::find()->select('u_id')->where(['like', 'real_name', $data->search])->column();
            $con5 = ['c.applyer' => $uids];
        }

        $current = ($page - 1) * self::PAGE_SIZE;
        //我的待办
        $list = [];
        if ($data->status == 0) {
            $res = (new Query())->select('c.apply_id,c.model_id,c.title,c.status,c.flow,c.step,c.is_attachment,c.create_time,b.real_name,a.modeltype')
                ->from('oa_apply_base c')
                ->leftJoin('oa_members b', 'c.applyer=b.u_id')
                ->leftJoin('oa_apply_model a', 'c.model_id=a.model_id')
                ->where(['c.handler' => $uid, 'c.status' => 0])
                ->andWhere($con1, $arr1)
                ->andWhere($con2, $arr2)
                ->andWhere($con3)
                ->andWhere($con5)
                ->orderBy(['c.create_time' => SORT_DESC,'apply_id' => SORT_DESC])
                ->limit(self::PAGE_SIZE)
                ->offset($current)
                ->all();
            //处理列表数据
            foreach($res as $key => $value) {
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
                unset($list[$key]['flow']);
                unset($list[$key]['step']);
            }
            $num = (new Query())->from('oa_apply_base c')
                ->where(['c.handler' => $uid, 'c.status' => 0])
                ->andWhere($con1, $arr1)
                ->andWhere($con2, $arr2)
                ->andWhere($con3)
                ->andWhere($con5)
                ->count();
        } else {
            $list = (new Query())->select('c.apply_id,c.model_id,c.title,c.status,c.create_time,c.is_attachment,b.real_name')
                ->from('oa_apply_log a')
                ->leftJoin('oa_apply_base c', 'a.apply_id=c.apply_id')
                ->leftJoin('oa_members b', 'c.applyer=b.u_id')
                ->where(['a.handler' => $uid])
                ->andWhere($con1, $arr1)
                ->andWhere($con2, $arr2)
                ->andWhere($con3)
                ->andWhere($con4)
                ->andWhere($con5)
                ->groupBy('c.apply_id')
                ->orderBy(['c.create_time' => SORT_DESC,'apply_id' => SORT_DESC])
                ->limit(self::PAGE_SIZE)
                ->offset($current)
                ->all();
            $num = (new Query())->select('a.apply_id')->from('oa_apply_log a')
                ->leftJoin('oa_apply_base c', 'a.apply_id=c.apply_id')
                ->distinct()
                ->where(['a.handler' => $uid])
                ->andWhere($con1, $arr1)
                ->andWhere($con2, $arr2)
                ->andWhere($con3)
                ->andWhere($con4)
                ->andWhere($con5)
                ->count();
        }
        $sumPage = ceil($num / self::PAGE_SIZE);
        return ['code' => 1, 'msg' => 'ok', 'list' => $list, 'sumPage' => $sumPage, 'curPage' => $page];
    }

    //审批
    public function actionVerify()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        $data = json_decode(file_get_contents("php://input"));
        if(!isset($data->apply_id) || empty($data->apply_id)) {
            return ['code' => 0, 'msg' => '参数错误'];
        }
        $apply = ApplyBaseModel::findOne($data->apply_id);
        if (empty($apply)) {
            return ['code' => 0, 'msg' => '该申请单不存在'];
        }
        if($apply->status != 0) {
            return ['code' => 0, 'msg' => '当前申请单已被处理,或已被撤回'];
        }
        $uid = $this->userInfo['u_id'];
        if($uid != $apply->handler) {
            return ['code' => 0, 'msg' => '您不是当前审核人，没有审核权限'];
        }
        //如果是职级申请的第一步审批
        if($apply->model_id == 5 && $apply->step == 1) {
            if(!isset($data->level_rank) || empty($data->level_rank)) {
                return ['code' => 0, 'msg' => '请填写职级'];
            }
        }
        $res = ApplyDelegate::verify($uid,$apply,$data,$this->userInfo);
        if(isset($res['code']) && $res['code'] == 0) {
            $transaction->rollBack();
            return $res;
        }
        if($res === true) {
            $transaction->commit();
            return ['code' => 1, 'msg' => '审批通过成功'];
        }else {
            $transaction->rollBack();
            return ['code' => 0, 'msg' => '审批通过失败'];
        }
    }

    //驳回
    public function actionRefuse()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        $data = json_decode(file_get_contents("php://input"));
        if(!isset($data->apply_id) || empty($data->apply_id)) {
            return ['code' => 0, 'msg' => '参数错误'];
        }
        if(!isset($data->comment) || $data->comment == '') {
            return ['code' => 0, 'msg' => '未填写审批意见'];
        }
        $apply = ApplyBaseModel::findOne($data->apply_id);
        if (empty($apply)) {
            return ['code' => 0, 'msg' => '该申请单不存在'];
        }
        if($apply->status != 0) {
            return ['code' => 0, 'msg' => '当前申请单已被处理,或已被撤回'];
        }
        $uid = $this->userInfo['u_id'];
        if($uid != $apply->handler) {
            return ['code' => 0, 'msg' => '您不是当前审核人，没有审核权限'];
        }
        if(ApplyDelegate::refuse($uid,$apply,$data->comment)) {
            $transaction->commit();
            return ['code' => 1, 'msg' => '审批驳回成功'];
        }else {
            $transaction->rollBack();
            return ['code' => 0, 'msg' => '审批驳回失败'];
        }
    }

    //撤回
    public function actionRevoke($apply_id)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        $apply = ApplyBaseModel::findOne($apply_id);
        if (empty($apply)) {
            return ['code' => 0, 'msg' => '该申请单不存在'];
        }
        if($apply->status != 0) {
            return ['code' => 0, 'msg' => '当前申请单该状态不允许撤回'];
        }
        $uid = $this->userInfo['u_id'];
        if($apply->applyer != $uid) {
            return ['code' => 0, 'msg' => '无权限进行该操作'];
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
                return ['code' => 1, 'msg' => '申请撤回成功'];
            }else{
                $transaction->rollBack();
                return ['code' => 0, 'msg' => '申请撤回失败'];
            }
        } else {
            $transaction->rollBack();
            return ['code' => 0, 'msg' => '申请撤回失败'];
        }
    }

    //催办
    public function actionPress($apply_id)
    {
        $apply = ApplyBaseModel::findOne($apply_id);
        if (empty($apply)) {
            return ['code' => 0, 'msg' => '该申请单不存在'];
        }
        if($apply->is_press != 0) {
            return ['code' => 0, 'msg' => '该申请单已催办过'];
        }
        $uid = $this->userInfo['u_id'];
        if($apply->applyer != $uid) {
            return ['code' => 0, 'msg' => '无权限进行该操作'];
        }
//        $apply->is_press = 1;
//        if($apply->save(false)) {
            $content = $this->userInfo['real_name']."正向您催办".date('Y年m月d日',$apply->create_time)."提交的".$apply->title.",请您尽快处理";
            $subject = 'OA申请催办';
            $receiver = MembersModel::find()->select('username')->where(['u_id' => $apply->handler])->column();
            Tools::asynSendMail($subject, $content, $receiver[0]);
        //极光推送
        $modeltype = ApplyBaseModel::find()->leftJoin('oa_apply_model','oa_apply_base.model_id=oa_apply_model.model_id')
            ->select('oa_apply_model.model_id,modeltype')->where(['oa_apply_base.apply_id' => $apply->apply_id])->asArray()->one();
        Tools::msgJpush(5,$apply->apply_id,$this->userInfo['real_name'].'正向您催办'.date('Y-m-d H:i:s').'提交的'.$apply->title.'，请您尽快处理',[$apply->handler],$modeltype);
            return ['code' => 1, 'msg' => '催办成功'];
//        }else {
//            return ['code' => 0, 'msg' => '催办失败'];
//        }
    }

    //删除申请
    public function actionApplyDel($apply_id)
    {
        $apply = ApplyBaseModel::findOne($apply_id);
        if (empty($apply)) {
            return ['code' => 0, 'msg' => '该申请单不存在'];
        }
        if ($apply->status != 3 && $apply->status != 2) {
            return ['code' => 0, 'msg' => '非撤回或已驳回状态申请单不允许删除'];
        }
        $uid = $this->userInfo['u_id'];
        if($apply->applyer != $uid) {
            return ['code' => 0, 'msg' => '无权限进行该操作'];
        }
        ApplyBaseModel::deleteAll(['apply_id' => $apply_id]);
        ApplyLogModel::deleteAll(['apply_id' => $apply_id]);
        ApplyAttachmentModel::deleteAll(['apply_id' => $apply_id]);
        //删除详情
        $applyModel = ApplyModel::findOne($apply->model_id);
//        $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name='" . $applyModel->tablename ."' AND COLUMN_KEY='PRI'";
//        $res = Yii::$app->db->createCommand($sql)->execute();
        $sql = 'DELETE FROM ' . $applyModel->tablename . ' WHERE id=' . $apply->detail_id;
        Yii::$app->db->createCommand($sql)->execute();
        return ['code' => 1, 'msg' => '申请单删除成功'];
    }

    //批量审批通过
    public function actionBatchVerify()
    {
        $data = json_decode(file_get_contents("php://input"));
        if(!isset($data->apply_ids) || empty($data->apply_ids)) {
            return ['code' => 0, 'msg' => '参数错误'];
        }
        $uid = $this->userInfo['u_id'];
        $successNum = 0;
        $failNum = 0;
        $failList = [];
        foreach($data->apply_ids as $key => $value) {
            $apply = ApplyBaseModel::findOne($value);
            if (empty($apply)) {
                continue;
            }
            if($apply->status != 0) {
                $failNum ++;
                $applyer = MembersModel::find()->select('real_name')->where(['u_id' => $apply->applyer])->column();
                $failList[$key] = ['title' => $apply->title,'applyer' => $applyer[0],'create_time' => date('Y-m-d H:i:s',$apply->create_time)];
                continue;
            }
            if($uid != $apply->handler) {
                $failNum ++;
                $applyer = MembersModel::find()->select('real_name')->where(['u_id' => $apply->applyer])->column();
                $failList[$key] = ['title' => $apply->title,'applyer' => $applyer[0],'create_time' => date('Y-m-d H:i:s',$apply->create_time)];
                continue;
            }
            if(ApplyDelegate::verify($uid,$apply,$data,$this->userInfo) === true) {
                $successNum ++;
            }else {
                $failNum ++;
                $applyer = MembersModel::find()->select('real_name')->where(['u_id' => $apply->applyer])->column();
                $failList[$key] = ['title' => $apply->title,'applyer' => $applyer[0],'create_time' => date('Y-m-d H:i:s',$apply->create_time)];
            }
        }
        return ['code' => 1,'data' => ['successNum' => $successNum,'failNum' => $failNum,'failList' => $failList]];
    }

    //批量驳回
    public function actionBatchRefuse()
    {
        $data = json_decode(file_get_contents("php://input"));
        if(!isset($data->apply_ids) || empty($data->apply_ids)) {
            return ['code' => 0, 'msg' => '参数错误'];
        }
        if(!isset($data->comment) || $data->comment == '') {
            return ['code' => 0, 'msg' => '未填写审批意见'];
        }
        $uid = $this->userInfo['u_id'];
        $successNum = 0;
        $failNum = 0;
        $failList = [];
        foreach($data->apply_ids as $key => $value) {
            $apply = ApplyBaseModel::findOne($value);
            if (empty($apply)) {
                continue;
            }
            if($apply->status != 0) {
                $failNum ++;
                $applyer = MembersModel::find()->select('real_name')->where(['u_id' => $apply->applyer])->column();
                $failList[$key] = ['title' => $apply->title,'applyer' => $applyer,'create_time' => date('Y-m-d H:i:s',$apply->create_time)];
                continue;
            }
            if($uid != $apply->handler) {
                $failNum ++;
                $applyer = MembersModel::find()->select('real_name')->where(['u_id' => $apply->applyer])->column();
                $failList[$key] = ['title' => $apply->title,'applyer' => $applyer,'create_time' => date('Y-m-d H:i:s',$apply->create_time)];
                continue;
            }
            if(ApplyDelegate::refuse($uid,$apply,$data->comment)) {
                $successNum ++;
            }else {
                $failNum ++;
                $applyer = MembersModel::find()->select('real_name')->where(['u_id' => $apply->applyer])->column();
                $failList[$key] = ['title' => $apply->title,'applyer' => $applyer,'create_time' => date('Y-m-d H:i:s',$apply->create_time)];
            }
        }
        return ['code' => 1,'data' => ['successNum' => $successNum,'failNum' => $failNum,'failList' => $failList]];
    }

    //详情展示
    public function actionShowDetail($apply_id)
    {
        $apply = ApplyBaseModel::find()->where(['apply_id' => $apply_id])->asArray()->one();
        if(empty($apply)) {
            return ['code' => 0,'msg' => '申请不存在'];
        }
        $apply['flow'] = json_decode($apply['flow'],true);
        $apply['last_step'] = false;
        if(count($apply['flow']) == $apply['step']) {
            $apply['last_step'] = true;
        }
        $applyModel = ApplyModel::findOne($apply['model_id']);
        $apply['modeltype'] = $applyModel->modeltype;
        if($applyModel->modeltype == 1) {

            $apply['data'] = Yii::$app->db->createCommand('select * from '.$applyModel->tablename.' where id='.$apply['detail_id'])->queryOne();
            $apply['data']['isShowPage'] = false;
            if($apply['model_id']==4){
                $flexibleWorkTime = FlexworkStoreModel::find()->where(['id'=>$apply['data']['store_id']])->asArray()->one();
                $apply['data']['flexibleWorkTime'] = date("Y-m-d H:i:s", $flexibleWorkTime['begin_time']).'~'.date("Y-m-d H:i:s", $flexibleWorkTime['end_time']).'(弹性时长：'.$flexibleWorkTime['hours'].')';
                $apply['data']['att'] = LeaveDelegate::getAtt($apply_id,['apply_att_id','file_name','real_name','file_size','file_path','file_type','create_time']);
            }
            //请假申请或职级申请读取附件
            if($apply['model_id'] == 2 || $apply['model_id'] == 5){
                $apply['data']['isShowPage'] = false;
                $apply['data']['att'] = LeaveDelegate::getAtt($apply_id,['apply_att_id','file_name','real_name','file_size','file_path','file_type','create_time']);
            }
            //定制申请内容数据格式设置
            if($apply['model_id'] == 2) {
                $apply = ApplyHelper::applyFormat($apply);
            }
        }
        //申请人信息
        $tempApplyerInfo= MembersModel::find()->select('real_name,head_img')->where(['u_id' => $apply['applyer']])->one();
        $apply['applyer_name'] = $tempApplyerInfo['real_name'];
        $apply['head_img'] = Tools::getHeadImg($tempApplyerInfo['head_img']);
        //当前审批人信息
        if($apply['status'] == 0) {
            $tempApplyerInfo= MembersModel::find()->select('real_name,head_img')->where(['u_id' => $apply['handler']])->one();
            $apply['handler_name'] = $tempApplyerInfo['real_name'];
            $apply['handler_head_img'] = Tools::getHeadImg($tempApplyerInfo['head_img']);
        }
        //判断是否是申请人
        $apply['is_applyer'] = 0;
        if($this->userInfo['u_id'] == $apply['applyer']) {
            $apply['is_applyer'] = 1;
        }
        //判断是否是当前处理人
        $apply['current_handler'] = 0;
        if ($this->userInfo['u_id'] == $apply['handler']) {
            $apply['current_handler'] = 1;
        }
        //审批记录
        $apply['verifyRecorders'] = (new Query())->select('d.handler,d.comment,d.reply_time,d.apply_id,d.status,a.head_img,a.real_name')
            ->from('oa_apply_log d')
            ->leftJoin('oa_members a', 'a.u_id=d.handler')
            ->where(['d.apply_id' => $apply_id])
            ->orderBy(['d.reply_time' => SORT_DESC])
            ->all();
        //审批记录时间格式设置
        $apply['verifyRecorders'] = ApplyHelper::leaveRecordFormat($apply['verifyRecorders']);
        //审批人头像处理
        foreach($apply['verifyRecorders'] as $key => $value) {
            $apply['verifyRecorders'][$key]['head_img'] = Tools::getHeadImg($value['head_img']);
        }
        $apply['form_json'] = json_decode($apply['form_json']);
        $apply['file_root'] = \Yii::getAlias('@file_root');
        return ['code' => 1,'data' => $apply];
    }

    //加班申请最后一步审批回显
    public function actionOvertime($apply_id)
    {
        $apply = ApplyBaseModel::findOne($apply_id);
        if (empty($apply)) {
            return ['code' => 0, 'msg' => '该申请单不存在'];
        }
        if($apply->status != 0) {
            return ['code' => 0, 'msg' => '当前申请单已被处理,或已被撤回'];
        }
        $uid = $this->userInfo['u_id'];
        if($uid != $apply->handler) {
            return ['code' => 0, 'msg' => '您不是当前审核人，没有审核权限'];
        }
        $detail = ApplyOvertimeModel::findOne($apply->detail_id);
        if (empty($detail)) {
            return ['code' => 0, 'msg' => '数据不存在'];
        }
        $workDate = date('Y-m-d',$detail->begin_time);
        //获取考勤记录
        $atten = AttendanceModel::find()->where(['u_id' => $apply->applyer,'workDate' => strtotime($workDate)])->one();
        if(empty($atten)) {
            return ['code' => 0, 'msg' => '尚未生成打卡记录，不能作最后一步审批'];
        }
        if(empty($atten->onTime) || empty($atten->offTime)) {
            return ['code' => 1,'data' => ['begin_time' => '--','end_time' => '--','real_hours' => 0]];
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
        return ['code' => 1,'data' => ['begin_time' => $begin_time,'end_time' => $end_time,'real_hours' => $real_hours]];
    }

    /**
     * 附件上传
     */
    public function actionUpload()
    {
        $postdata = json_decode(file_get_contents("php://input"), true);
        $data = FileUploadHelper::fileUpload(Yii::getAlias('@apply'));
        if($data){
            echo json_encode(array('code'=>1,'data'=>$data,'file_root'=>Yii::getAlias('@file_root')));
        }else{
            echo json_encode(array('code'=>-1,'msg'=>'上传失败,请重试！'));
        }
        exit;
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
            return ['code'=>1,'data'=>$res];
        }else{
            return ['code' => 0, 'msg' => '尚未生成打卡记录，不能作最后一步审批'];
        }
    }

    public function actionFlexibleWork()
    {
        $timeData = array();
        $workTime = FlexWorkDelegate::getUsefulStore($this->userInfo['u_id']);
        foreach ($workTime as $key => $val){
            $timeData['beginTime'] = date("Y-m-d H:i:s", $val['begin_time']);
            $timeData['endTime'] = date("Y-m-d H:i:s", $val['end_time']);
            $timeData['hours'] = $val['hours'];
            $workTime[$key]['timeOutData'] = $timeData['beginTime'].'~'.$timeData['endTime'].'(弹性时长：'.$timeData['hours'].')';
        }
        FResponse::output(['code'=>20000,'msg'=>'ok','data' => $workTime]);
    }

    public function actionGetRankLevel()
    {
        $detailId = Yii::$app->request->post('detailId');
        $rankLevel = ApplyRankModel::find()->select('rank_level')->where(['id'=>$detailId])->asArray()->one();
        FResponse::output(['code'=>20000,'msg'=>'ok','data' => $rankLevel]);
    }
}
