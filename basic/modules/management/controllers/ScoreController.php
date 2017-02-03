<?php

namespace app\modules\management\controllers;

use app\modules\management\delegate\ScoreDelegate;
use Yii;
use app\controllers\BaseController;

class ScoreController extends BaseController
{
    public $modelClass = 'app\models\SkillModel';
    /**
     * 追加群组积分
     * @throws \yii\db\Exception
     */
    public function actionAddGrouppoint()
    {
        if (!Yii::$app->request->post('reason')) {
            return ['code' => '20002', 'msg' => '请填写原因', 'data' => new \stdClass()];
        }
        $data = ScoreDelegate::batchAddGroupPoint(Yii::$app->request->post('org_ids'), Yii::$app->request->post('points'), Yii::$app->request->post('reason'), $this->userInfo);
        if ($data) {
            return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
        }
        return ['code' => '20001', 'msg' => '操作失败', 'data' => $data];
    }

    public function actionScorelist()
    {
        $data = ScoreDelegate::getScoreList(Yii::$app->request->post());
        if ($data) {
            return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
        }
        return ['code' => '20001', 'msg' => '操作失败', 'data' => $data];
    }

    public function actionGroupScorelist()
    {
        $data = ScoreDelegate::getGroupScorelist(Yii::$app->request->post());
        if ($data) {
            return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
        }
        return ['code' => '20001', 'msg' => '操作失败', 'data' => $data];
    }
    /**
     * 追加个人积分
     * @throws \yii\db\Exception
     */
    public function actionAddPoint()
    {
        $transaction = Yii::$app->db->beginTransaction();
        if (!Yii::$app->request->post('reason')) {
            return ['code' => '20001', 'msg' => '请填写原因', 'data' => new \stdClass()];
        }
        $data = ScoreDelegate::batchAddPoint(Yii::$app->request->post('u_id'), Yii::$app->request->post('points'), Yii::$app->request->post('reason'), $this->userInfo);
        if ($data) {
            $transaction->commit();
            return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
        }
        $transaction->rollBack();
        return ['code' => '20001', 'msg' => '操作失败', 'data' => $data];
    }

    /**
     * 查询积分日志列表
     * @return array
     */
    public function actionLogList()
    {
        $data = ScoreDelegate::getLogList(Yii::$app->request->post());
        return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
    }

    /**
     * 查看积分设置
     * @return array
     */
    public function actionViewset()
    {
        $data = ScoreDelegate::getScoreSet();
        if ($data) {
            return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
        }
        return ['code' => '20001', 'msg' => '操作失败', 'data' => $data];
    }

    /**
     * 设置积分脚本
     * @return array
     */
    public function actionSetScorecron()
    {
        $data = ScoreDelegate::setScoreCron(Yii::$app->request->post());
        if ($data) {
            return ['code' => '20000', 'msg' => '操作成功', 'data' => $data];
        }
        return ['code' => '20001', 'msg' => '操作失败', 'data' => $data];
    }

}
