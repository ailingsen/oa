<?php

namespace app\modules\v1\delegate;


//模型委托类。 处理控制器和动作列表

use app\models\SurveyModel;
use app\models\SurveyReplyModel;
use Yii;

class SurveyDelegate
{

    /**
     * 获取调研列表
     * $type 1所有调研  2我发布的调研
     */
    public static function getSurveyList($type=1,$u_id,$limit,$offset)
    {
        $surveyM = SurveyModel::find()->select('oa_survey.*,oa_members.real_name')->leftJoin('oa_members','oa_members.u_id=oa_survey.u_id');
        if($type==2){
            $surveyM->where('oa_survey.u_id=:u_id',[':u_id'=>$u_id]);
        }
        $res['surList'] = $surveyM->offset($offset)->limit($limit)->orderBy('create_time DESC')->asArray()->all();
        $res['totalPage'] = ceil($surveyM->count()/$limit);
        return $res;
    }

    /**
     * 获取调研回复
     */
    public static function getMySurveyReply($survey_id, $limit, $offset)
    {
        $replyM = SurveyReplyModel::find()->select('oa_survey_reply.*,oa_members.real_name')->where('survey_id=:survey_id',[':survey_id'=>$survey_id])
            ->leftJoin('oa_members','oa_members.u_id=oa_survey_reply.u_id')
            /*->leftJoin('oa_org_member','oa_org_member.u_id=oa_members.u_id')
            ->leftJoin('oa_org','oa_org.org_id=oa_org_member.org_id')*/;
        $res['repList'] = $replyM->offset($offset)->limit($limit)->orderBy('oa_survey_reply.create_time DESC')->asArray()->all();
        $res['totalPage'] = ceil($replyM->count()/$limit);
        return $res;
    }


}