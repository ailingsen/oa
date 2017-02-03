<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_crontab".
 *
 * @property integer $crontab_id
 * @property string $cron_name
 * @property integer $run_cycle
 * @property integer $last_run_time
 * @property string $params
 * @property string $script_path
 * @property integer $add_time
 */
class CrontabModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_crontab';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cron_name', 'params'], 'required'],
            [['run_cycle', 'last_run_time', 'add_time'], 'integer'],
            [['cron_name'], 'string', 'max' => 20],
            [['params', 'script_path'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'crontab_id' => 'Crontab ID',
            'cron_name' => 'Cron Name',
            'run_cycle' => 'Run Cycle',
            'last_run_time' => 'Last Run Time',
            'params' => 'Params',
            'script_path' => 'Script Path',
            'add_time' => 'Add Time',
        ];
    }
}
