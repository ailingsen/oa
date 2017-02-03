<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_desk".
 *
 * @property integer $desk_id
 * @property integer $u_id
 * @property integer $templet_id1
 * @property integer $templet_id2
 * @property integer $templet_id3
 * @property integer $templet_id4
 * @property integer $templet_id5
 * @property integer $templet_id6
 * @property integer $templet_id7
 * @property integer $templet_id8
 * @property integer $templet_id9
 * @property integer $templet_id10
 * @property integer $templet_id11
 * @property integer $templet_id12
 * @property integer $templet_id13
 */
class DeskModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_desk';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['u_id', 'templet_id1', 'templet_id2', 'templet_id3', 'templet_id4', 'templet_id5', 'templet_id6', 'templet_id7', 'templet_id8', 'templet_id9', 'templet_id10', 'templet_id11', 'templet_id12', 'templet_id13'], 'required'],
            [['u_id', 'templet_id1', 'templet_id2', 'templet_id3', 'templet_id4', 'templet_id5', 'templet_id6', 'templet_id7', 'templet_id8', 'templet_id9', 'templet_id10', 'templet_id11', 'templet_id12', 'templet_id13'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'desk_id' => 'Desk ID',
            'u_id' => 'U ID',
            'templet_id1' => 'Templet Id1',
            'templet_id2' => 'Templet Id2',
            'templet_id3' => 'Templet Id3',
            'templet_id4' => 'Templet Id4',
            'templet_id5' => 'Templet Id5',
            'templet_id6' => 'Templet Id6',
            'templet_id7' => 'Templet Id7',
            'templet_id8' => 'Templet Id8',
            'templet_id9' => 'Templet Id9',
            'templet_id10' => 'Templet Id10',
            'templet_id11' => 'Templet Id11',
            'templet_id12' => 'Templet Id12',
            'templet_id13' => 'Templet Id13',
        ];
    }
}
