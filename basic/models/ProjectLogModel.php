<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_project_log".
 *
 * @property integer $pro_log_id
 * @property integer $u_id
 * @property integer $pro_id
 * @property integer $create_time
 * @property string $content
 */
class ProjectLogModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_project_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['u_id', 'pro_id', 'create_time'], 'integer'],
            [['content'], 'required'],
            [['content'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pro_log_id' => 'Pro Log ID',
            'u_id' => 'U ID',
            'pro_id' => 'Pro ID',
            'create_time' => 'Create Time',
            'content' => 'Content',
        ];
    }

    /**
     * 插入日志
     * @param $userInfo
     * @param $logContent
     * @param $projectId
     * @return int
     * @throws \yii\db\Exception
     */
    public static function addLog($userInfo, $logContent, $projectId)
    {
        return Yii::$app->db->createCommand()->insert(self::tableName(), ['u_id' => $userInfo['u_id'], 'content' => $logContent, 'create_time' => time(), 'pro_id' => $projectId])->execute();
    }
}
