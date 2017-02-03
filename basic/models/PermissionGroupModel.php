<?php

namespace app\models;

use app\lib\errors\ClientException;
use app\lib\errors\ErrorCode;
use app\lib\errors\ValidateException;
use app\lib\EventBehavior;
use app\modules\permission\helper\PermissionHelper;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "oa_permission_group".
 *
 * @property integer $group_id
 * @property string $group_name
 * @property string $permission
 */
class PermissionGroupModel extends \yii\db\ActiveRecord
{
    const EVENT_AFTER_CHANGE_PERMISSION = 'change-permission';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_permission_group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_name'], 'string', 'max' => 20],
            [['permission'], 'string', 'max' => 500],
            [['group_name', 'permission'], 'required', 'on' => ['create']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => EventBehavior::className(),
                'events' => [
                    self::EVENT_AFTER_CHANGE_PERMISSION => function() {
                        MembersModel::updatePermission($this->group_id, $this->permission);
                    }
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'group_id' => 'Group ID',
            'group_name' => 'Group Name',
            'permission' => 'Permission',
        ];
    }

    /**
     * 创建群组
     * @param array $arr
     * @return bool
     * @throws ValidateException
     */
    public static function addGroup($arr = array()){
        if(empty($arr)) return false;
        $perGroup = new self;
        $perGroup->scenario = 'create';
        $perGroup->attributes = $arr;
        if (!$perGroup->save() && $perGroup->hasErrors()) {
            throw new ValidateException($perGroup);
        }

        return $perGroup;
    }

    /**
     * @param $groupId
     * @param $data
     * @return bool
     * @throws ClientException
     * @throws ValidateException
     */
    public static function editGroup($groupId, $data){
        $perGroup = self::findOne(['group_id' => $groupId]);
        if (!$perGroup) {
            throw new ClientException(ErrorCode::E_DATA_NOT_FOUND);
        }

        isset($data['group_name']) && $perGroup->group_name = $data['group_name'];
        isset($data['permission']) && $perGroup->permission = json_encode($data['permission']);

        if (!$perGroup->save() && $perGroup->hasErrors()) {
            throw new ValidateException($perGroup);
        }
        if (isset($data['permission'])) {
            $perGroup->trigger(self::EVENT_AFTER_CHANGE_PERMISSION);
        }

        return true;
    }

    /**
     * @param $pageSize
     * @param $page
     * @return ActiveDataProvider
     */
    public static function getPerGroup($pageSize, $page)
    {
        $query = static::find()
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->asArray()
            ->all();

        return $query;
    }

    /**
     * @return mixed
     */
    public static function getPerGroupCount()
    {
        return static::find()->count();
    }

    /**
     * @param $name
     * @return ActiveDataProvider
     */
    public static function queryGroup($name)
    {
        return self::findOne(['group_name' => $name]);
    }
    
    public static function deleteX($groupId)
    {
        $model = static::findOne(['group_id' => $groupId]);
        if (!$model) {
            throw new ClientException(ErrorCode::E_DATA_NOT_FOUND);
        }

        return $model->delete();
    }

}
