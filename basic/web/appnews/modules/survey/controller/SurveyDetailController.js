//我的发布
SurveyMod.controller('SurveyDetailCtrl',function($scope,$http,$rootScope,$timeout,surveyModel,$stateParams,$state,permissionService){
    if (!permissionService.checkPermission('SurveyList')) {
        $state.go('main.index', {},{'reload': false});
        return false;
    }
    var survey = $scope.survey = {};
    //我发布的调研信息
    survey.surveyInfo = '';
    var survey_param = $scope.survey_param = {};
    survey_param.survey_id = $stateParams.survey_id ? $stateParams.survey_id : 0;
    if(survey_param.survey_id==0){
        alert('参数错误');
        $state.go('^');
    }
    survey_param.reply_content = '';

    /*//编辑器参数配置
    $scope.config = {
        'initialFrameWidth':'100%'
    }*/

    //获取我发布的调研
    surveyModel.getSurveyDetail($scope);

    //回复调研
    survey.replySurveyBtn = function(){
        if(survey.surveyInfo.status==1){
            surveyModel.setReplySurvey($scope);
        }else{
            alert('该调研已经结束，无法回复！');
        }
    }

    //返回
    survey.returnGo = function(){
        $state.go('^',{isInit:0});
    }

});



