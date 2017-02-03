<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_permission_member".
 *
 * @property integer $perm_u_id
 * @property integer $pid
 * @property integer $u_id
 */
class PermissionMemberModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_permission_member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pid', 'u_id'], 'required'],
            [['pid', 'u_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'perm_u_id' => 'Perm U ID',
            'pid' => 'Pid',
            'u_id' => 'U ID',
        ];
    }
}
