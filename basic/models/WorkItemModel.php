<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_work_item".
 *
 * @property integer $item_id
 * @property integer $work_id
 * @property integer $type
 * @property string $content
 * @property integer $status
 * @property integer $create_time
 */
class WorkItemModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_work_item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_id', 'work_id', 'content'], 'required'],
            [['item_id', 'work_id', 'type', 'status', 'create_time'], 'integer'],
            [['content'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'item_id' => 'Item ID',
            'work_id' => 'Work ID',
            'type' => 'Type',
            'content' => 'Content',
            'status' => 'Status',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 插入
     * @param $data
     * @return bool
     */
    public static function createX($data)
    {
        $taskModel = new self;
        $taskModel->attributes = $data;
        return $taskModel->save(false);
    }
}
