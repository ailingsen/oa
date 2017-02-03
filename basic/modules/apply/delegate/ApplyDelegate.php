<?php
/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2016/8/4
 * Time: 15:32
 */

namespace app\modules\apply\delegate;


use app\lib\Tools;
use app\models\ApplyBaseModel;
use app\models\ApplyFlowModel;
use app\models\ApplyLogModel;
use app\models\ApplyModel;
use app\models\OrgMemberModel;

class ApplyDelegate
{
    /**
     * 判断申请是否存在
     * @param $condition
     * @return bool
     */
    public static function isApplyExist(array $condition)
    {
        return ApplyBaseModel::find()->where($condition)->exists();
    }

    /**
     * 自定义表单字段过滤
     * @param $data
     * @return array
     */
    public static function filterField($data)
    {
        $arrData = array();
        foreach ($data['field'] as $key => $val) {
            switch ($val['formtype']) {
                case 'RadioBox': //单选按钮
                    // print_r($val['setting']['radio']);die;
                    foreach ($val['setting']['radio'] as $k => $v) {
                        if (!is_object($v) && $v['itemData'] == true) {
                            $arrData[$key] = $k;
                            break;
                        }
                    }
                    break;
                case 'CheckBox':
                    $temp = '';
                    foreach ($val['setting']['checkbox'] as $k => $v) {
                        if (!is_object($v) && $v['itemData'] == true) {
                            $temp .= $k . ',';
                        }
                    }
                    $arrData[$key] = substr($temp, 0, strlen($temp) - 1);
                    break;
                case 'Select':
                    foreach ($val['setting']['select'] as $k => $v) {
                        if (!is_object($v) && $v['itemData'] == true) {
                            $arrData[$key] = $k;
                            break;
                        }
                    }
                    break;
                case 'Employee':
                    $temp = '';
                    foreach ($val['setting']['Employee'] as $k => $v) {
                        if (!is_object($v)) {
                            $temp .= $v['itemId'] . ',';
                        }
                    }
                    $arrData[$key] = substr($temp, 0, strlen($temp) - 1);
                    break;
                case 'Department':
                    $temp = '';
                    foreach ($val['setting']['Employee'] as $k => $v) {
                        if (!is_object($v)) {
                            $temp .= $v['itemId'] . ',';
                        }
                    }
                    $arrData[$key] = substr($temp, 0, strlen($temp) - 1);
                    break;
                case 'FileComponent':
                    $temp = '';
                    foreach ($val['setting']['FileComponent'] as $k => $v) {
                        if (!is_object($v)) {
                            $temp .= $v['FileComponent'] . ',';
                        }
                    }
                    $arrData[$key] = substr($temp, 0, strlen($temp) - 1);
                    break;
                case 'ImageComponent':
                    $temp = '';
                    foreach ($val['setting']['ImageComponent'] as $k => $v) {
                        if (!is_object($v)) {
                            $temp .= $v['itemImgs'] . ',';
                        }
                    }
                    $arrData[$key] = substr($temp, 0, strlen($temp) - 1);
                    break;
//                case 'DataTable':
//                    $temp = array();
//                    foreach ($val['setting']['table'] as $k => $v) {
//                        if (is_object($v)) {
//                            foreach ($v as $k1 => $v1) {
//                                $temp[$val['setting']['tableData'][$k1]['itemTitle']][] = $v1;
//                            }
//                        }
//                    }
//                    //如果表格数据有统计列则保存统计值
//                    if (isset($val['setting']['countNum'])) {
//                        $temp['countNum'] = mb_substr($val['setting']['countNum'], 3);
//                    }
//                    $arrData[$key] = json_encode($temp);
//                    break;
                case 'DateInterval':
                    if (!empty($val['inputvalue']) && !empty($val['dateTimeEnd'])) {
                        $arrData[$key] = $val['inputvalue'] . '至' . $val['dateTimeEnd'];
                    }
                    break;
                case 'DividingLine':
                    break;
                case 'Paragraph':
                    break;
                default:
                    $arrData[$key] = $val['inputvalue'];
                    break;
            }
        }
        return $arrData;
    }

    /**
     * 获取审批单配置流程
     * @param $model_id
     * @param $uid
     * @param $org_id
     * @param array $field
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getModelFlow($model_id, $uid, $org_id, array $field = [])
    {
        //查寻是否有个人特殊配置
        $res = ApplyFlowModel::find()->select($field)->where(['model_id' => $model_id, 'type' => 0])
            ->andWhere(['like', 'visibleman', ',' . $uid . ','])->asArray()->all();
        //没有则查寻所属组配置
        if (empty($res)) {
            $res = ApplyFlowModel::find()->select($field)->where(['model_id' => $model_id, 'type' => 1])
                ->andWhere(['like', 'visibleman', ',' . $org_id . ','])->asArray()->all();
        }

        return $res;
    }

    /**
     * 获取自定义申请审批流程
     * @param $arrData
     * @param $applyFlows
     * @return array
     */
    public static function getApplyFlow($arrData, $applyFlows)
    {
        $res = '';
        foreach ($applyFlows as $key => $value) {
            //若没有设置分流条件 则直接返回本条流程
            if ($value['item'] == -1) {
                $res = $value['flow'];
                break;
            } else {
                if ($value['condition'] == 1 && $arrData[$value['item']] > $value['value']) {
                    $res = $value['flow'];
                    break;
                }
                if ($value['condition'] == 2 && $arrData[$value['item']] < $value['value']) {
                    $res = $value['flow'];
                    break;
                }
                if ($value['condition'] == 3 && $arrData[$value['item']] >= $value['value']) {
                    $res = $value['flow'];
                    break;
                }
                if ($value['condition'] == 4 && $arrData[$value['item']] <= $value['value']) {
                    $res = $value['flow'];
                    break;
                }
                if ($value['condition'] == 5 && $arrData[$value['item']] == $value['value']) {
                    $res = $value['flow'];
                    break;
                }
            }
        }
        return json_decode($res, true);
    }

    /**
     * 申请是否达到发布上限
     * @param $model_id
     * @param $uid
     * @param $limit_type
     * @param $limit_num
     * @return bool
     */
    public static function isUpLimit($model_id, $uid, $limit_type, $limit_num)
    {
        $begin = [];
        $end = [];
        //1 日 2 周 3 月 4 年
        if ($limit_type == 1) {
            $begin = ['>=', 'create_time', strtotime(date('Ymd'))];
            $end = ['<', 'create_time', strtotime('+1 day', strtotime(date('Ymd')))];
        }
        if ($limit_type == 2) {
//            $begin = ['>=', 'create_time', strtotime("+0 week Monday", strtotime(date('Ymd')))];
            $begin = ['>=', 'create_time', strtotime('last Monday')];
//            $end = ['<', 'create_time', strtotime("+1 week Monday", strtotime(date('Ymd')))];
            $end = ['<', 'create_time', strtotime('next Monday')];
        }
        if ($limit_type == 3) {
            $begin = ['>=', 'create_time', strtotime(date('Ym') . '01')];
            $end = ['<', 'create_time', strtotime('+1 month', strtotime(date('Ym') . '01'))];
        }
        if ($limit_type == 4) {
            $begin = ['>=', 'create_time', strtotime(date('Y') . '0101')];
            $end = ['<', 'create_time', strtotime((date('Y') + 1) . '0101')];
        }
        $count = ApplyBaseModel::find()->where(['model_id' => $model_id, 'applyer' => $uid])->andWhere($begin)->andWhere($end)->count();
        if ($count < $limit_num) {
            return false;
        }
        return true;
    }

    /**
     * 获取下一步审核人
     * @param $uid
     * @param array $set
     * @param $step
     * @return array
     */
    public static function getNextHandler($uid, array $set, $step)
    {
        $nextHandlerArr = ['nextHand' => 0, 'step' => $step];
        if (count($set) >= $step) {
            //更新下一步审核人
            $nextHandlerArr['nextHand'] = $set[$step];

            //如果下步审核人是当前审核人跳过或者是false的
            if ($nextHandlerArr['nextHand'] == $uid || !$nextHandlerArr['nextHand']) {
                return self::getNextHandler($uid, $set,$step + 1 );
            }
        }
        return $nextHandlerArr;
    }

    /**
     * 将审批流程转换成审批人UID
     * @param $uid
     * @param array $flow
     * @param $index
     * @return array
     */
    public static function getFlow2Uid($uid, array $flow, $index)
    {
        if (count($flow) >= $index) {
            if ($flow[$index] == -1) {
                $gid = OrgMemberModel::find()->select('org_id')->where(['u_id' => $uid])->column();
                $leader = OrgMemberModel::getLeaderUid($uid, $gid);
                $flow[$index] = $leader;
            }else{
                $leader = $flow[$index];
            }
            $index++;
            return self::getFlow2Uid($leader, $flow, $index);
        }
        return $flow;
    }

    /**
     * 审批通过申请
     * @param $uid
     * @param ApplyBaseModel $apply
     * @param $data
     * @return bool
     */
    public static function verify($uid, ApplyBaseModel $apply,$data,$userInfo)
    {
        //获取下一步审核人
        $nextHandArr = ApplyDelegate::getNextHandler($uid, json_decode($apply->flow, true), $apply->step);
        //如果是职级申请的第一步审批
        if($apply->model_id == 5 && $apply->step == 1) {
            $data = RankDelegate::fisrtVerify($data,$apply->detail_id);
        }
        //判断是否是最后一步审批
        if (empty($nextHandArr['nextHand'])) {
            //取表单类型
            $applyModel = ApplyModel::findOne($apply->model_id);
            //如果定制化表单
            if($applyModel['modeltype'] == 1) {
                $res = true;

                switch($apply->model_id) {
                    //加班
                    case 1:
                        $res = OverTimeDelegate::doneOverTime($apply,$data);
                        break;
                    //请假
                    case 2:
                        $res = LeaveDelegate::setLeaveSum($apply,$data,$userInfo);
                        break;
                    //弹性
//                    case 4:
//                        return false;
//                        break;
                    //忘打卡
                    case 3:
                        $res = CheckOutDelegate::doneCheckout($apply);
                        break;
                    //职级申请
                    case 5:
                        $res = RankDelegate::doneRank($apply,$data,$uid);
                        break;
                }

                if(!$res || (isset($res['code']) && $res['code'] == 0)) {
                    return $res;
                }
            }
            $apply->handler = 0;
            $apply->status = 1;
        } else {
            $apply->handler = $nextHandArr['nextHand'];
            $apply->step = $nextHandArr['step'];
        }
        $apply->update_time = time();
        if ($apply->save(false)) {
            //插入审批日志记录
            $applyLog = new ApplyLogModel();
            $applyLog->apply_id = $apply->apply_id;
            $applyLog->handler = $uid;
            if(isset($data->comment)) {
                $applyLog->comment = $data->comment;
            }
            $applyLog->reply_time = time();
            $applyLog->status = 1;
            $applyLog->step = $apply->step;
            $applyLog->save(false);
            if($apply->handler) {
                //写入审批消息
                Tools::addApprovalMsg($apply->handler,$apply->apply_id,$apply->applyer,$apply->title);
                //写入我的申请消息
                Tools::addApplyMsg($apply->applyer,$apply->apply_id,$uid,1,$apply->title);
            }else {
                //写入我的申请消息
                Tools::addApplyMsg($apply->applyer,$apply->apply_id,$uid,3,$apply->title);
            }
            return true;
        }
        return false;
    }

    /**
     * 审批驳回申请
     * @param $uid
     * @param ApplyBaseModel $apply
     * @param $comment
     * @return bool
     */
    public static function refuse($uid, ApplyBaseModel $apply, $comment)
    {
        $res = true;
        //请假申请驳回数据处理
        if($apply->model_id == 2){
            $res = LeaveDelegate::returnLeaveData($uid,$apply);
        }
        //弹性工作驳回修改弹性库存
        if($apply->model_id == 4) {
            $res = FlexWorkDelegate::updateUsefulStore($apply->detail_id);
        }
        if(!$res){
            return false;
        }
        $apply->handler = 0;
        $apply->status = 2;
        $apply->update_time = time();
        if ($apply->save(false)) {
            //插入审批日志记录
            $applyLog = new ApplyLogModel();
            $applyLog->apply_id = $apply->apply_id;
            $applyLog->handler = $uid;
            $applyLog->comment = $comment;
            $applyLog->reply_time = time();
            $applyLog->status = 2;
            $applyLog->step = $apply->step;
            $applyLog->save(false);
            //写入我的申请消息
            Tools::addApplyMsg($apply->applyer,$apply->apply_id,$uid,2,$apply->title);
            return true;
        }
        return false;
    }
}