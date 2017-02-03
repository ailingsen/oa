<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_score_log".
 *
 * @property integer $score_id
 * @property integer $u_id
 * @property integer $score
 * @property integer $score_before
 * @property integer $score_after
 * @property string $content
 * @property integer $create_time
 * @property integer $operator
 */
class ScoreLogModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_score_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['u_id', 'score', 'score_before', 'score_after', 'content', 'create_time'], 'required'],
            [['u_id', 'score_before', 'score_after', 'create_time'], 'integer'],
            [['content'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'score_id' => 'Score ID',
            'u_id' => 'U ID',
            'score' => 'Score',
            'score_before' => 'Score Before',
            'score_after' => 'Score After',
            'content' => 'Content',
            'create_time' => 'Create Time',
            'operator' => 'Operator'
        ];
    }

//    /*
//     * 积分榜相关数据
//     */
//    public static function getIntegral($beginData, $endData,$pageSize,$curPage)
//    {
//        return self::find()->select('sum(oa_score_log.score) as integral, oa_members.real_name, oa_members.head_img')
//            ->leftJoin('oa_members', 'oa_members.u_id=oa_score_log.u_id')
//            ->andWhere(['>', 'oa_score_log.create_time', $beginData])
//            ->andWhere(['<', 'oa_score_log.create_time', $endData])
//            ->andWhere([ 'oa_score_log.type' => 1])
//            ->groupBy('oa_score_log.u_id')
//            ->orderBy(['integral' => SORT_DESC])
//            ->limit($pageSize)
//            ->offset($pageSize*($curPage-1))
//            ->asArray()
//            ->all();
//
//    }

    /**
     * @param $data
     * @return int
     * @throws \yii\db\Exception
     */
    public static function insertScoreLog($data)
    {
        return Yii::$app->db->createCommand()
            ->insert('oa_score_log', $data)
            ->execute();
    }

    /**
     * @param $uid
     * @param $type
     * @param int $page
     * @param int $pageSize
     * @param string $fields
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getScoreList($uid, $type = 0, $page = 1, $pageSize = 100, $fields = 'oa_score_log.create_time,oa_score_log.content,oa_score_log.score_before,oa_score_log.score_after,oa_members.real_name,oa_members.head_img')
    {
        $query = self::find()->select($fields)
            ->leftJoin('oa_members', 'oa_members.u_id=oa_score_log.operator');

        $query->where(['oa_score_log.u_id' => $uid]);
        if ($type) {
            $query->andWhere(['oa_score_log.type' => $type]);
        }

        if(!empty($page)) {
            $query->limit($pageSize)->offset(($page-1)*$pageSize);
        }
        $query->orderBy(['oa_score_log.create_time' => SORT_DESC]);
        return $query->asArray()->all();
    }
}
