//发布调研

SurveyMod.controller('CreateSurveyCtrl',function($scope,$http,$rootScope,$timeout,surveyModel,Publicfactory){
    var survey = $scope.survey = {};
    var survey_param = $scope.survey_param = {};
    //调研标题
    survey_param.title ='';
    //调研说明
    survey_param.explain ='';
    //调研内容
    survey_param.content ='';

    //编辑器配置
    $scope.config = {
        'initialFrameWidth':'100%'
    }

    //创建调研
    survey.createSurveyBtn = function(){
        var ue = UE.getEditor('container');
        survey_param.content = ue.getContent();
        if(Publicfactory.checkEnCnstrlen(ue.getContentTxt())>10000)
        {
            alert('公告内容不能超过5000字');
            return false;
        }
        surveyModel.createSurvey($scope);
    }

});



