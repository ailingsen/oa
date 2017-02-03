//我的发布
SurveyMod.controller('MyCreateDetailCtrl',function($scope,$http,$rootScope,$timeout,surveyModel,$stateParams,$state){
    var survey = $scope.survey = {};
    //我发布的调研信息
    survey.surveyInfo = '';
    //调研回复
    survey.surveyReplayList = '';
    var survey_param = $scope.survey_param = {};
    survey_param.survey_id = $stateParams.survey_id ? $stateParams.survey_id : 0;
    if(survey_param.survey_id==0){
        alert('参数错误');
        $state.go('^');
    }
    //当前页
    survey_param.page = 1;

    //翻页对象
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };

    //获取我发布的调研
    surveyModel.getMyCreateDetail($scope);

    //翻页方法
    $scope.page_fun = function () {
        $scope.survey_param.page = $scope.page.tempcurPage;
        surveyModel.getMyCreateDetail($scope);
    };

    //返回
    survey.returnGo = function(){
        $state.go('^',{isInit:0});
    }

});



