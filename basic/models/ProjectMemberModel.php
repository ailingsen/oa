<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_project_member".
 *
 * @property integer $pro_mem_id
 * @property integer $pro_id
 * @property integer $u_id
 * @property integer $add_time
 * @property integer $owner
 */
class ProjectMemberModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_project_member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pro_id', 'u_id', 'add_time', 'owner'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pro_mem_id' => 'Pro Mem ID',
            'pro_id' => 'Pro ID',
            'u_id' => 'U ID',
            'add_time' => 'Add Time',
            'owner' => 'Owner',
        ];
    }

    /**
     * 获取指定项目成员关系
     * @param $conditionArr
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getProMemberInfo($conditionArr, $select = '')
    {
        $query = self::find();
        if ($select) {
            $query->select($select);
        }

        self::load($conditionArr);

        if (!self::validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return false;
        }

        $query->andFilterWhere([
            'pro_mem_id' => $this->pro_mem_id,
            'pro_id' => $this->pro_id,
            'u_id' => $this->u_id,
            'owner' => $this->owner,
        ]);
        
        return $query->asArray()->all();
    }

    /**
     * 插入项目成员关系
     * @param $memberId
     * @param $projectId
     * @return int|void
     * @throws \yii\db\Exception
     */
    public static function insertProMember($memberId, $projectId) {
        $info = self::find()->where('u_id=:member_id and pro_id=:project_id',[':member_id'=>$memberId,':project_id'=>$projectId])->asArray()->one();
        if(isset($info['u_id']) && $info['u_id']>0){
            return false;
        }
        return Yii::$app->db->createCommand()->insert(self::tableName(), [
            'pro_id' => $projectId,
            'u_id' => $memberId,
        ])->execute();
    }

    /**
     * 获取指定项目成员关系
     * @param $memberId
     * @param $projectId
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getProMemberByProAndMember($memberId, $projectId) {
        return self::find()->where("pro_id=:pro_id AND u_id=:u_id",[':pro_id'=>$projectId,':u_id'=>$memberId])->asArray()->one();
    }

    /**
     * @param $uid
     * @return array|null|\yii\db\ActiveRecord
     * 我参与的项目数
     */
    public static function getCuntMyPartakePro($uid)
    {
        return self::find()->select('count(pro_id) as proNub')->where(['u_id'=>$uid])->groupBy('u_id')->asArray()->one();
    }
}
