<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_vacation_log".
 *
 * @property integer $id
 * @property integer $u_id
 * @property integer $log_type
 * @property string $value_before
 * @property string $value_after
 * @property string $log_content
 * @property integer $create_time
 * @property integer $operator_id
 */
class VacationLogModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_vacation_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['u_id', 'log_type', 'create_time', 'operator_id'], 'integer'],
            [['value_before', 'value_after'], 'number'],
            [['log_content'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'u_id' => 'U ID',
            'log_type' => 'Log Type',
            'value_before' => 'Value Before',
            'value_after' => 'Value After',
            'log_content' => 'Log Content',
            'create_time' => 'Create Time',
            'operator_id' => 'Operator ID',
        ];
    }

    /**
     * 单个用户库存日志
     * @param $u_id
     * @param $log_type
     * @return array
     */
    public function userVacationLog($u_id, $log_type)
    {
        $data = self::find()->select(['oa_vacation_log.u_id', 'oa_vacation_log.log_type', 'oa_vacation_log.value_before', 'oa_vacation_log.value_after', 'oa_vacation_log.log_content', 'oa_vacation_log.create_time', 'oa_vacation_log.operator_id', 'b.real_name'])
            ->leftJoin('oa_members b', 'a.operator_id = b.u_id')
            ->where(['oa_vacation_log.u_id'=>$u_id,'oa_vacation_log.log_type'=>$log_type])
            ->orderBy('id DESC')->asArray()->all();
        foreach ($data as &$item) {
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
            if ($item['operator_id'] == 0)
                $item['real_name'] = '系统';
        }
        return $data;
    }

    public static function getChangeRecord($uid, $logType)
    {
        $changeRecord = self::find()->select('oa_vacation_log.value_before, oa_vacation_log.create_time,oa_vacation_log.value_after,oa_vacation_log.log_content, oa_members.real_name, oa_members.head_img')
                ->leftJoin('oa_members', 'oa_members.u_id=oa_vacation_log.operator_id')
                ->where(['oa_vacation_log.u_id'=>$uid])->andWhere(['log_type'=>$logType]);
        //$changeRecord = $changeRecord->orderBy(['oa_vacation_log.create_time'=> SORT_DESC,'id' => SORT_DESC])->limit($pageSize)->offset($pageSize*($curPage-1))->asArray()->all();
        $changeRecord = $changeRecord->orderBy(['oa_vacation_log.create_time'=> SORT_DESC,'id' => SORT_DESC])->asArray()->all();
        return [
            'changeRecord'=>$changeRecord
        ];
    }

    /**
     * 插入调休日志
     * @param $u_id 用户ID
     * @param $value_before 修改前的值
     * @param $operator_id 操作人ID
     * @param $log_content 日志内容
     * @return array|bool
     */
    public static function addTuneVacationLog($u_id, $value_before, $operator_id, $log_content)
    {
        $model = new VacationLogModel();
        $model->u_id = $u_id;
        $model->value_before = $value_before;
        $model->operator_id = $operator_id;
        $model->log_content = $log_content;
        $model->create_time = time();
        $model->value_after = self::getTuneVacationValueAfter($u_id);
        $model->log_type = 1;  //1.调休 2.年假 3.病假

        if(!$model->save())
        {
            return $model->getErrors();
        }
        return true;
    }

    /**
     * 查询调休假
     * @param $u_id
     * @return float
     */
    public static function getTuneVacationValueAfter($u_id)
    {
        $data = (new \yii\db\Query())
            ->select('u_id')
            ->from('oa_vacation_inventory')
            ->where('u_id=:u_id', [':u_id' => $u_id])
            ->andWhere('is_valid=:is_valid', [':is_valid' => 0])
            ->all();
        return round((count($data) / 2), 1);
    }
}
