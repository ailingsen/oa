<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_skill_level".
 *
 * @property integer $skill_level_id
 * @property integer $level
 * @property string $title
 * @property integer $point
 */
class SkillLevelModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_skill_level';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['level', 'title', 'point'], 'required'],
            [['level', 'point'], 'integer'],
            [['title'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'skill_level_id' => 'Skill Level ID',
            'level' => 'Level',
            'title' => 'Title',
            'point' => 'Point',
        ];
    }
    /**
     * 插入
     * @param $data
     * @return bool
     */
    public static function createX($data)
    {
        $model = new self;
        $model->attributes = $data;
        return $model->save(false);
    }
}
