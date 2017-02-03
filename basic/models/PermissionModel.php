<?php
namespace app\models;

use app\lib\errors\ValidateException;
use app\lib\Tools;
use Yii;
/**
 * This is the model class for table "{{permission}}".
 *
 * The followings are the available columns in table '{{permission}}':
 * @property integer $pid
 * @property string $code
 * @property integer $is_contoller
 * @property string $p_name
 * @property integer $parent_id
 */
class PermissionModel extends \yii\db\ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public static function tableName()
	{
		return 'oa_permission';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return [
			[['is_contoller', 'parent_id'], 'integer'],
			[['code','p_name'], 'string', 'max'=>50],
            [['p_router'],'string','max'=>100],
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			[['pid', 'code', 'is_contoller', 'p_name', 'p_router', 'parent_id'], 'safe', 'on'=>'search'],
			[['code', 'is_contoller', 'p_name',  'parent_id'], 'required', 'on'=>'create'],
		];
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return [];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return [
			'pid' => 'Pid',
			'code' => 'Code',
			'is_contoller' => 'Is Contoller',
			'p_name' => 'P Name',
			'parent_id' => 'Parent',
            'p_router'=>'p_router'
		];
	}

	/**
	 * @param $params
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public static function search($params)
	{
		return self::find()->where($params)->asArray()->all();
	}

	/**
	 * 获取所有配置权限信息
	 * @return array|\yii\db\ActiveRecord[]
	 */
    public static function getConfigPermission()
    {
        $temarr = self::find()->asArray()->all();
        return $temarr;
    }

	public static function updateX($pid, $data)
	{
		$permission = static::findOne($pid);
		isset($data['parent_id']) && $permission->parent_id = $data['parent_id'];
		isset($data['p_name']) && $permission->p_name = $data['p_name'];
		isset($data['p_router']) && $permission->p_router = $data['p_router'];

		return $permission->save();
	}

	/**
	 * 创建群组
	 * @param array $arr
	 * @return bool
	 * @throws ValidateException
	 */
	public static function addPermission($arr = array()){
		if(empty($arr)) return false;
		$permission = new self;
		$permission->scenario = 'create';
		$permission->attributes = $arr;
		if (!$permission->save() && $permission->hasErrors()) {
			throw new ValidateException($permission);
		}

		return $permission;
	}
}
