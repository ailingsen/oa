<?php

namespace app\modules\management\controllers;

use app\modules\management\delegate\SkillDelegate;
use app\modules\project\delegate\ProjectDelegate;
use Yii;
use app\modules\management\helper\SkillHelper;
use app\controllers\BaseController;

class SkillController extends BaseController
{
    public $modelClass = 'app\models\SkillModel';
    /**
     * 追加群组积分
     * @throws \yii\db\Exception
     */
    public function actionAddPoint()
    {
        $data = SkillDelegate::addPoint(Yii::$app->request->post('group_id'), Yii::$app->request->post('points'));
        return ['code' => '20000', 'msg' => 'ok', 'data' => $data];
    }

    /**
     * 获取技能列表（权限）
     */
    public function actionSkilllist()
    {
        return ['code' => 20000, 'msg' => 'ok', 'data' => SkillDelegate::getSkillList()];
    }

    /**
     * 添加技能
     */
    public function actionAddskill()
    {
        /*if (!Yii::$app->request->post('skill_name')) {
            return false;
        }*/
        //判断技能重复
        $data['skill_name'] = Yii::$app->request->post('skill_name');
        $data['group_id'] = Yii::$app->request->post('group_id', 0);

        if (SkillHelper::isSkillUnique(Yii::$app->request->post('skill_name'))) {
            if (0 == $data['group_id']) {
                return ['code' => 20001, 'msg' => '技能类型重复'];
            }
            return ['code' => 20001, 'msg' => '技能重复'];
        }


        if (SkillDelegate::addSkill($data)) {
            return ['code' => 20000, 'msg' => '添加成功'];
        }
        return ['code' => 20002, 'msg' => '添加失败'];
    }

    /**
     * 修改技能
     */
    public function actionEditskill()
    {
        $data['skill_id'] = Yii::$app->request->post('skill_id');
        $data['skill_name'] = Yii::$app->request->post('skill_name');


        if (SkillHelper::isSkillUnique(Yii::$app->request->post('skill_name'), Yii::$app->request->post('skill_id'))) {
            return ['code' => 20001, 'msg' => '技能重复'];
        }

        if (SkillDelegate::editeSkill($data)) {
            return ['code' => '20000', 'msg' => '编辑成功'];
        }
        return ['code' => 20002, 'msg' => '编辑失败'];
    }

    /**
     * 删除技能
     */
    public function actionDelskill()
    {
        $skillId = Yii::$app->request->post('skill_id');
        $groupId = Yii::$app->request->post('group_id');

        //判断技能是否已使用
        if (SkillHelper::isSkillUsed($groupId, $skillId)) {
            if ($groupId) {
                return ['code' => 20001, 'msg' => '该技能已使用无法删除'];
            }
            return ['code' => 20001, 'msg' => '请先删除该类型下所有技能'];
        }

        $res = SkillDelegate::deleteSkill($groupId, $skillId);

        if ($res) {
            $jsonRet = ['code' => 20000, 'msg' => '删除成功'];
        }  else {
            $jsonRet = ['code' => 20000,'msg' => '删除失败，请重试'];
        }

        return $jsonRet;
    }

    /**
     * 获取技能等级列表
     */
    public function actionSkillLevel()
    {
        return ['code' => 20000, 'msg' => 'ok', 'data' => SkillDelegate::getSkillLevel()];
    }

    /**
     * 添加技能等级
     */
    public function actionAddSkilllevel()
    {
        if (!Yii::$app->request->post('title')) {
            return ['code' => 20001, 'msg' => '添加失败'];
        }
        if(!ProjectDelegate::isStrlen(Yii::$app->request->post('title'),10)){
            return ['code' => 20001, 'msg' => '头衔长度不能超过10个字'];
        }
        //判断技能等级重复
        if (SkillHelper::isSkillLevelExit(Yii::$app->request->post('title'), Yii::$app->request->post('skill_level_id'))) {
            return ['code' => 20001, 'msg' => '技能头衔重复'];
        }
        $point = Yii::$app->request->post('point');
        if(empty($point) && $point===''){
            return ['code' => 20001, 'msg' => '技能分数不能为空'];
        }

        //判断技能分数重复
        if (SkillHelper::isSkillScoreExit(Yii::$app->request->post('point'), Yii::$app->request->post('skill_level_id'))) {
            return ['code' => 20001, 'msg' => '技能分数重复'];
        }

        if (SkillDelegate::addSkillLevel(Yii::$app->request->post())) {
            return ['code' => 20000, 'msg' => '添加成功'];
        }
        return ['code' => 20002, 'msg' => '添加失败'];
    }

    /**
     * 编辑技能等级
     */
    public function actionEditeSkilllevel()
    {
        if(!ProjectDelegate::isStrlen(Yii::$app->request->post('title'),10)){
            return ['code' => 20001, 'msg' => '头衔长度不能超过10个字'];
        }
        //判断技能等级重复
        if (Yii::$app->request->post('title') && SkillHelper::isSkillLevelExit(Yii::$app->request->post('title'), Yii::$app->request->post('skill_level_id'))) {
            return ['code' => 20001, 'msg' => '技能头衔重复'];
        }

        //判断技能分数重复
        $point = Yii::$app->request->post('point');
        if(empty($point) && $point===''){
            return ['code' => 20001, 'msg' => '技能分数不能为空'];
        }
        if (SkillHelper::isSkillScoreExit(Yii::$app->request->post('point'), Yii::$app->request->post('skill_level_id'))) {
            return ['code' => 20001, 'msg' => '技能分数重复'];
        }

        if (SkillDelegate::editeSkillLevel(Yii::$app->request->post())) {
            return ['code' => 20000, 'msg' => '修改成功'];
        }
        return ['code' => 20002, 'msg' => '修改失败'];
    }

    /**
     * 删除技能等级
     */
    public function actionDelSkilllevel()
    {
        if (SkillDelegate::deleteSkillLevel(Yii::$app->request->post('skill_level_id'))) {
            return ['code' => 20000, 'msg' => '删除成功'];
        }
        return ['code' => 20002, 'msg' => '删除失败'];
    }
}
