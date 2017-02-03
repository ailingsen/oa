<?php
/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2016/8/26
 * Time: 15:17
 */

namespace app\modules\apply\delegate;


use app\models\ApplyBaseModel;
use app\models\ApplyRankModel;
use app\models\MembersModel;
use app\models\ScoreLogModel;

class RankDelegate
{
    /**
     * 过滤职级申请数据
     * @param array $data
     * @return array
     */
    public static function filterData(array $data) {
        $tmpData = $data;
        unset($tmpData['att']);
        return $tmpData;
    }
    /**
     * 职级申请第一步审批
     * @param $data
     * @param $detail_id
     * @return mixed
     */
    public static function fisrtVerify($data,$detail_id)
    {
        $data->comment = '职级:'.$data->level_rank .';'. $data->comment;
        $detail = ApplyRankModel::findOne($detail_id);
        $detail->rank_level = $data->level_rank;
        $detail->save(false);
        return $data;
    }

    /**
     * 职级申请最后一步审批
     * @param ApplyBaseModel $apply
     * @param $data
     * @param $uid
     * @return bool
     */
    public static function doneRank(ApplyBaseModel $apply,&$data,$uid)
    {
        $detail = ApplyRankModel::findOne($apply->detail_id);
        //审批日志
        $data->comment = '职级:'.$data->level_rank .';'.'积分:'.$data->score.';'. $data->comment;
        $detail->rank_level = $data->level_rank;
        $detail->score = $data->score;
        $detail->save(false);
        //修改申请人职级,添加用户积分
        $member = MembersModel::findOne($apply->applyer);
        $member->rank_level = $data->level_rank;
        $old_point = $member->points;
        $member->points = $old_point + $data->score;
        $member->save(false);
        //添加用户积分日志
        $logInfo = ['u_id' => $member->u_id,
            'type' => 1,
            'content' => '职级申请奖励纳米币',
            'score' => $data->score,
            'score_before' => $old_point,
            'score_after' => $member->points,
            'create_time' => time(),
            'operator' => $uid
        ];
        ScoreLogModel::insertScoreLog($logInfo);
        return true;
    }
}