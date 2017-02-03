<?php

namespace app\modules\management\helper;

use app\models\SkillLevelModel;
use app\models\SkillModel;
use app\models\TaskSkillModel;

class SkillHelper
{
    /**
     * 技能是否存在
     * @param $skillName
     * @return bool
     */
    public static function isSkillUnique($skillName, $skillId = 0)
    {

        $query = SkillModel::find()->where('skill_name=:skill_name', array(':skill_name' => $skillName));
        if ($skillId) {
            $query->andWhere(['not in', 'skill_id', [$skillId]]);
        }
        $info = $query ->asArray()->one();
        if ($info) {
            return true;
        }
        return false;
    }

    /**
     * 技能是否已经使用
     * @param $groupId
     * @param $skillId
     * @return int
     */
    public static function isSkillUsed($groupId, $skillId)
    {
        //判断技能是否已使用
        if ($groupId == 0) {
            $skillId = SkillModel::find()->select('skill_id')->where(['group_id' => $skillId])->asArray()->all();
            if ($skillId) {
                return true;
            }
        }
        return self::checkSkillUsed($skillId);
    }

    public static function checkSkillUsed($skillId)
    {
        $info = TaskSkillModel::find()->where(['skill_id' => $skillId])->asArray()->all();
        if ($info) {
            return true;
        }
        return false;
    }

    /**
     * 技能头衔是否存在
     * @param $title
     * @return bool
     */
    public static function isSkillLevelExit($title, $skillLevelId)
    {
        $info = SkillLevelModel::find()->where(['title' => $title])
            ->andWhere(['not in', 'skill_level_id', [$skillLevelId]])
            ->asArray()->one();
        if ($info) {
            return true;
        }
        return false;
    }

    public static function isSkillScoreExit($point, $skillLevelId)
    {
        $info = SkillLevelModel::find()->where(['point' => $point])
            ->andWhere(['not in', 'skill_level_id', [$skillLevelId]])
            ->asArray()->one();
        if ($info) {
            return true;
        }
        return false;
    }
}
