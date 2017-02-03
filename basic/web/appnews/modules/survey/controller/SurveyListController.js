//调研列表
var SurveyMod=angular.module('SurveyMod',[]);

SurveyMod.controller('SurveyListCtrl',function($scope,$http,$rootScope,$timeout,$stateParams,$state,surveyModel,$cookieStore,permissionService){
    if (!permissionService.checkPermission('SurveyList')) {
        $state.go('main.index', {},{'reload': false});
        return false;
    }
    var survey = $scope.survey = {};
    //是否为我发布的调研列表
    survey.isMySurveyList = false;
    //我发布的调研信息
    survey.surveyInfoList = '';
    var survey_param = $scope.survey_param = {};
    //当前页
    survey_param.page = 1;
    //是否显示关闭调研确认框
    survey.isCloseSurveyWin = false;

    //翻页对象
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };

    //初始化数据------------------------------------------------------
    survey.isInit = $stateParams.isInit ? $stateParams.isInit : 1;
    if(survey.isInit==0){
        var MyCreateListCookie = $cookieStore.get('MyCreateList');
        if(typeof(MyCreateListCookie) != "undefined"){
            //初始化页数
            survey_param.page = MyCreateListCookie.page ? MyCreateListCookie.page : 1;
            $scope.page.tempcurPage = survey_param.page;

            //初始化参数数据
            var MyCreateList = {};
            MyCreateList.page = 1;
            $cookieStore.put('MyCreateList',MyCreateList);
        }
    }

    //获取我发布的调研
    surveyModel.getSurveyList($scope);

    //翻页方法
    $scope.page_fun = function () {
        $scope.survey_param.page = $scope.page.tempcurPage;
        surveyModel.getSurveyList($scope);
    };

    //跳转到详情页
    survey.detailGo = function(survey_id){
        surveyModel.setMyCreateListCookie($scope);
        $state.go('main.survey.surveylist.surveydetail',{survey_id:survey_id});
    }
});



