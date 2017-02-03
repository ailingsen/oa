<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_rank".
 *
 * @property integer $id
 * @property string $rank_level
 * @property integer $score
 * @property string $note
 */
class ApplyRankModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_rank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'score'], 'integer'],
            [['note'], 'string'],
            [['rank_level'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rank_level' => 'Rank Level',
            'score' => 'Score',
            'note' => 'Note',
        ];
    }
}
