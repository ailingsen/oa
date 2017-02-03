<?php

namespace app\models;

use app\modules\userinfo\helper\UserHelper;
use Yii;
use app\models\SkillLevelModel;
/**
 * This is the model class for table "oa_skill_member".
 *
 * @property integer $member_skill_id
 * @property integer $member_id
 * @property integer $skill_id
 * @property integer $point
 */
class SkillMemberModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_skill_member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'skill_id'], 'required'],
            [['member_id', 'skill_id', 'point'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'member_skill_id' => 'Member Skill ID',
            'member_id' => 'Member ID',
            'skill_id' => 'Skill ID',
            'point' => 'Point',
        ];
    }

    public function getSkill()
    {
        // 第一个参数为要关联的子表模型类名，
        // 第二个参数指定 通过子表的customer_id，关联主表的id字段
        return $this->hasOne(SkillModel::className(), ['skill_id' => 'skill_id']);
    }

    /**
     * 获取用户技能
     * @param $uid
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getMemberSkilllist($uid)
    {
        return SkillMemberModel::find()->joinWith('skill')->where('oa_skill_member.member_id=:member_id', array(':member_id' => $uid))->asArray()->all();
    }

    /**
     * 获取用户技能积分等级
     * @param $member_id
     * @param int $limit
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getMemberSkill($member_id, $limit = 0)
    {
        $query = self::find()->select(['oa_skill_member.point', 'oa_skill.skill_name'])
            ->leftJoin('oa_skill', 'oa_skill_member.skill_id=oa_skill.skill_id')
            ->where(['oa_skill_member.member_id' => $member_id])
            ->orderBy(['oa_skill_member.point' => SORT_DESC]);
        if ($limit) {
            $query->limit($limit);
        }
        $skilllist = $query->asArray()->all();
        $skilllist = UserHelper::doSkill($skilllist);
        return $skilllist;
    }

    /**
     * 更新个人用户技能积分
     * @param $skills
     * @param $skillSorce
     * @param $uid
     * @throws \yii\db\Exception
     */
    public static function updateMemberSkillSorce($skills, $skillSorce, $uid)
    {
        foreach($skills as $k => $v){
            //判断个人是否有该技能
            if(self::checkMemberSkill($uid, $v)){
                //insert
                Yii::$app->db->createCommand()->insert('oa_skill_member', ['member_id'=>$uid, 'skill_id'=>$v['skill_id'], 'point' => $skillSorce[$k]])->execute();
            }else{
                //update
                $sql = "update oa_skill_member set point=point+'" . $skillSorce[$k] . "' where member_id='" . $uid . "' and skill_id='" . $v['skill_id'] . "'";
                Yii::$app->db->createCommand($sql)->execute();
            }
        }
    }

    /**
     * 更新个人用户技能积分
     * @param $skillList
     * @param $uid
     * @param $score
     * @throws \yii\db\Exception
     * @return bool
     */
    public static function upMemberSkillSorce($skillList, $uid, $score)
    {
        if (!is_array($skillList)) {
            return false;
        }
        foreach($skillList as $k => $v){
            //判断个人是否有该技能
            if(self::checkMemberSkill($uid, $v['skill_id'])){
                //insert
                Yii::$app->db->createCommand()->insert('oa_skill_member', ['member_id' => $uid, 'skill_id' => $v['skill_id'], 'point' => $score])->execute();
            } else {
                //update
                $sql = "update oa_skill_member set point=point+'" . $score . "' where member_id='" . $uid . "' and skill_id='" . $v['skill_id'] . "'";
                Yii::$app->db->createCommand($sql)->execute();
            }
        }
    }

    /**
     * 检查用户技能积分
     * @param $memberId
     * @param $skillId
     * @return bool
     */
    public static function checkMemberSkill($memberId, $skillId)
    {
        $res = self::find()->select('member_skill_id')->where(['member_id' => $memberId, 'skill_id' => $skillId])->asArray()->one();
        if(!$res || empty($res)){
            return true;
        }else{
            return false;
        }
    }
}
