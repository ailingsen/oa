<?php
/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2016/8/26
 * Time: 15:16
 */

namespace app\modules\apply\delegate;


use app\models\ApplyFlexworkModel;
use app\models\FlexworkStoreModel;

class FlexWorkDelegate
{
    /**
     * 弹性工作申请入库数据预处理
     * @param array $data
     * @param string $action
     * @param $detail_id
     * @return array
     */
    public static function filterData(array $data,$action = '',$detail_id = '')
    {
        unset($data['att']);
        $data['begin_time'] = strtotime($data['begin_time']);
        $data['end_time'] = strtotime($data['end_time']);
        //将使用的弹性库存改为已使用
        if(!isset($data['store_id']) || empty($data['store_id'])) {
            return ['code' => 0, 'msg' => '参数错误，未选择使用的弹性库存'];
        }
        if($action == 'edit') {
            $detail = ApplyFlexworkModel::findOne($detail_id);
            //若修改调休使用的弹性库存，则将原来使用的弹性库存修改为未使用
            if($data['store_id'] != $detail->store_id) {
                FlexworkStoreModel::updateAll(['valid' => 0],['id' => $detail->store_id]);
                $store = FlexworkStoreModel::findOne($data['store_id']);
                if($store->valid != 0) {
                    return ['code' => 0, 'msg' => '该弹性库存已被使用'];
                }
                $store->valid = 1;
                if($store->save()) {
                    return $data;
                }else {
                    return ['code' => 0, 'msg' => '系统错误，更新弹性库存失败'];
                }
            }else {
                return $data;
            }
        }else {
            $store = FlexworkStoreModel::findOne($data['store_id']);
            if(!isset($store->valid)){
                return ['code' => 0, 'msg' => '请选择正确的弹性库存'];
            }
            if($store->valid != 0) {
                return ['code' => 0, 'msg' => '该弹性库存已被使用'];
            }
            $store->valid = 1;
            if($store->save()) {
                return $data;
            }else {
                return ['code' => 0, 'msg' => '系统错误，更新弹性库存失败'];
            }
        }
    }

    /**
     * 获取可用弹性库存
     * @param $uid
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getUsefulStore($uid)
    {
        $list = FlexworkStoreModel::find()->where(['uid' => $uid,'valid' => 0])->andWhere(['>=', 'expire_time', time()])->asArray()->all();
        return $list;
    }

    /**
     * 修改弹性库存为未使用
     * @param $detail_id
     * @return bool
     */
    public static function updateUsefulStore($detail_id)
    {
        $flexWork = ApplyFlexworkModel::findOne($detail_id);
        $flexStore = FlexworkStoreModel::findOne($flexWork->store_id);
        $flexStore->valid = 0;
        return $flexStore->save(false);
    }
}