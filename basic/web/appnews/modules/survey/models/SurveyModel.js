SurveyMod.factory('surveyModel',function($http,$state,$cookieStore,$sce){
    var  service={};

    //创建调研
    service.createSurvey=function($scope){
        $http.post('/index.php?r=survey/survey/create-survey',JSON.stringify($scope.survey_param)).success(function (data) {
            if(data.code==1){
                $state.go('main.survey.mycreatelist',{isInit:1});
            }
            alert(data.msg);
        });
    }

    //获取我发布的调研信息列表
    service.getMyCreateList=function($scope){
        $http.post('/index.php?r=survey/survey/my-survey-list',JSON.stringify($scope.survey_param)).success(function (data) {
            if(data.code==1){
                $scope.survey.surveyInfoList = data.data.surList;
                $scope.page.curPage = data.data.page.curPage;
                $scope.page.sumPage = data.data.page.sumPage;
            }else{
                alert(data.msg);
            }
        });
    }

    //获取我发布的调研信息详情
    service.getMyCreateDetail=function($scope){
        $http.post('/index.php?r=survey/survey/my-survey-detail',JSON.stringify($scope.survey_param)).success(function (data) {
            if(data.code==1){
                $scope.survey.surveyInfo = data.data.surveyInfo;
                $scope.survey.surveyInfo.content = $sce.trustAsHtml($scope.survey.surveyInfo.content);
                $scope.survey.surveyReplayList = data.data.replyInfo.repList;
                $scope.page.curPage = data.data.replyInfo.page.curPage;
                $scope.page.sumPage = data.data.replyInfo.page.sumPage;
            }else{
                alert(data.msg);
            }
        });
    }

    //我发布的调研参数保存
    service.setMyCreateListCookie=function($scope){
        var MyCreateList = {};
        MyCreateList.page = $scope.survey_param.page;
        $cookieStore.put('MyCreateList',MyCreateList);
    }

    //关闭调研
    service.closeSurvey=function($scope){
        $http.post('/index.php?r=survey/survey/close-survey',{survey_id:$scope.survey.closeSurveyId}).success(function (data) {
            if(data.code==1){
                angular.element.each($scope.survey.surveyInfoList, function (key, val) {
                    if(val.survey_id == $scope.survey.closeSurveyId){
                        $scope.survey.surveyInfoList[key].status=2;
                    }
                });
                alert(data.msg);
                $scope.survey.closeSurveyId = '';
            }else{
                alert(data.msg);
            }
        });
    }

    //调研信息列表
    service.getSurveyList=function($scope){
        $http.post('/index.php?r=survey/survey/survey-list',JSON.stringify($scope.survey_param)).success(function (data) {
            if(data.code==1){
                $scope.survey.surveyInfoList = data.data.surList;
                $scope.page.curPage = data.data.page.curPage;
                $scope.page.sumPage = data.data.page.sumPage;
            }else{
                alert(data.msg);
            }
        });
    }

    //查看调研详情
    service.getSurveyDetail=function($scope){
        $http.post('/index.php?r=survey/survey/survey-detail',JSON.stringify($scope.survey_param)).success(function (data) {
            if(data.code==1){
                $scope.survey.surveyInfo = data.data;
                $scope.survey.surveyInfo.content = $sce.trustAsHtml($scope.survey.surveyInfo.content);
            }else{
                alert(data.msg);
            }
        });
    }

    //回复调研
    service.setReplySurvey=function($scope){
        $http.post('/index.php?r=survey/survey/survey-reply',JSON.stringify($scope.survey_param)).success(function (data) {
            if(data.code==1){
                $state.go('^',{isInit:0});
                alert(data.msg);
            }else{
                alert(data.msg);
            }
        });
    }

    return service;
});
