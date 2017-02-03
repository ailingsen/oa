<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_skill".
 *
 * @property integer $skill_id
 * @property string $skill_name
 * @property integer $parent_id
 * @property integer $company_id
 */
class SkillModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_skill';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id', 'company_id'], 'integer'],
            [['skill_name'], 'string', 'max' => 25]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'skill_id' => 'Skill ID',
            'skill_name' => 'Skill Name',
            'group_id' => 'Group ID',
            'company_id' => 'Company ID',
        ];
    }

    /**
     * 获取所有技能
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getSkillList($skillName)
    {
        $query = self::find();
        if(strlen($skillName)>0){
            $query->where(['>','group_id',0]);
        }
        $query->andFilterWhere(['like','skill_name',$skillName]);
        $list = $query->asArray()->all();
        return $list;
    }

    /**
     * @param array $arr
     * @return int
     * @throws \yii\db\Exception创建我的技能
     */
    public function addSkill($arr=array())
    {
        $data['skill_name'] = $arr['skill_name'];
        $data['group_id'] = isset($arr['group_id'])?$arr['group_id']:0;

        $res=Yii::$app->db->createCommand()->insert('oa_skill',$data)->execute();
        return $res;
    }

    /**
     * 删除技能
     * @param $skillid
     * @return bool|int
     * @throws \yii\db\Exception
     */
    public function delSkill($skillid)
    {
        if(empty($skillid)) return false;

        $res=Yii::$app->db->createCommand()->delete('oa_skill','skill_id=:skill_id',[':skill_id'=>$skillid])->execute();
        return $res;
    }

    /**
     * 检测父类下面是否有子类
     * @param $skillid
     * @return bool|int|string
     */
    public function checkSkill($skillid)
    {
        if(empty($skillid)) return false;

        $num = SkillModel::find()->where('group_id=:group_id',[':group_id'=>$skillid])->count();
        return $num;
    }

    /**
     * 编辑技能
     * @param $data
     * @return bool|int
     */
    public function editSkill($data)
    {
        if(empty($data)) return false;

        $skillM = SkillModel::findOne($data['skill_id']);
        $skillM->skill_name = $data['skill_name'];
        return $skillM->save();
    }

}
