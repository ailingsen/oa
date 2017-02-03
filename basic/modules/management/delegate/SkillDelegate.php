<?php

namespace app\modules\management\delegate;

use app\lib\Tools;
use app\models\OrgMemberModel;
use app\models\OrgModel;
use app\models\SkillLevelModel;
use app\models\SkillModel;
use Yii;
use app\models\MembersModel;


class SkillDelegate
{
    /**
     * 产线技能列表
     * @return array
     */
    public static function getSkillList($skillName='')
    {
        $data = array();
        $data['child'] = array();
        $skill = new SkillModel();
        $result = $skill->getSkillList($skillName);
        if(empty($result)){
            $data = [];
        }else{
            $data = Tools::createTreeArr($result, $result[0]['group_id'], 'group_id', 'skill_id');
        }
        return $data;
    }

    /**
     * @param $arry
     * @return mixed
     * 删除空children
     */
    public static function bianli($arry)
    {
        foreach ($arry as $key => $val){
            if(empty($val['children'])){
                unset($arry[$key]['children']);
            }
            $arry[$key]['children'] = self::bianli($val['children']);
            if (empty($arry[$key]['children']) ) {
                unset($arry[$key]['children']);
            }
        }
        return $arry;
    }
    /**
     * 添加技能
     * @param $skillData
     * @return int
     */
    public static function addSkill($skillData){
        $skill = new SkillModel();
        $ret = $skill->addSkill($skillData);

        return $ret;
    }

    /**
     * 修改技能
     * @param $skillData
     * @return bool|int
     */
    public static function editeSkill($skillData)
    {
        $skill = new SkillModel();

        return $skill->editSkill($skillData);
    }

    /**
     * 删除技能
     * @param $groupId
     * @param $skillId
     * @return array|bool|int
     */
    public static function deleteSkill($groupId, $skillId)
    {
        $skill = new SkillModel();
        if(0 == $groupId){
            $skill->deleteAll(['group_id' => $skillId]);
        }
        return $skill->delSkill($skillId);
    }

    public static function getSkillLevel()
    {
        return SkillLevelModel::find()
            ->orderBy(['point' => SORT_ASC])
            ->asArray()->all();
    }

    /**
     * 删除技能等级
     * @param $levelId
     * @return int
     */
    public static function deleteSkillLevel($levelId)
    {
        return SkillLevelModel::deleteAll(['skill_level_id' => $levelId]);
    }

    /**
     * 添加技能等级
     * @param $levelData
     * @return int
     */
    public static function addSkillLevel($levelData){
        unset($levelData['level']);
        $levelData['point'] = empty($levelData['point']) ? 0 : $levelData['point'];
        $ret = SkillLevelModel::createX($levelData);

        return $ret;
    }

    /**
     * 修改技能等级
     * @param $skillData
     * @return bool|int
     */
    public static function editeSkillLevel($skillData)
    {
        $skillLevelModel = SkillLevelModel::findOne($skillData['skill_level_id']);
        if (isset($skillData['point'])) {
            $skillLevelModel->point = $skillData['point'];
        }
        if (isset($skillData['title']) && $skillData['title'] != '') {
            $skillLevelModel->title = $skillData['title'];
        }
        return $skillLevelModel->save(false);
    }

}
