<?php
namespace app\config;
class Dict
{
    const TYPE_DAILY_WORKSTATEMENT = 1;
    const TYPE_WEEKLY_WORKSTATEMENT = 2;
    
    const DAILY_WORK_ITEM = 1;
    const WEEKLY_WORK_ITEM = 2;
    const TOMORROW_WORK_ITEM = 3;
    const NEXT_WEEKLY_WORK_ITEM = 4;

    public static $workTypeMap = [
        self::DAILY_WORK_ITEM,
        self::WEEKLY_WORK_ITEM,
        self::TOMORROW_WORK_ITEM,
        self::NEXT_WEEKLY_WORK_ITEM
    ];

    const WORK_STATUS_TODO = 1;
    const WORK_STATUS_DONE = 2;

    const TYPE_DAILY_WORK = 1;
    const TYPE_WEEKLY_WORK = 2;
    const TYPE_TOMORROW_WORK = 3;
    const TYPE_NEXT_WEEK_WORK = 4;

    const STATUS_WORK_TO_SUBMIT = 0;
    const STATUS_WORK_TO_APPROVE = 1;
    const STATUS_WORK_IS_APPROVED = 2;

    /**
     * vacation_type常量配置
     */
    const TYPE_ANNUAL_VACATION = 1;//普通年假的vacation_type
    const TYPE_EXTENSION_VACATION = 2;//顺延年假的vacation_type
    const TYPE_TUNE_VACATION = 3;//调休的vacation_type
}
