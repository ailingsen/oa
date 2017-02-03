
  SurveyMod.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider,$q) {
         
            var basePath = 'appnews/modules/survey/view/';

            //主路由
            $stateProvider.state('main.survey', {
                url: '/survey',
                module: 'private',  //需要授权的
                data: {
                    css: [
                           'css/userResearch.css',
                           'css/lib/datetimepicker.css'
                         ]
                },
                views: {
                    'surveyContent@main': {
                        templateUrl: basePath + 'survey_content.html'
                    }
                }
            })
            //调研列表
            .state('main.survey.surveylist', {
                    url: '/surveylist/:isInit',
                    module: 'private',  //需要授权的
                    views: {
                        'survey@main.survey': {
                        templateUrl: basePath + 'mycreatelist.html',
                        controller: 'SurveyListCtrl'
                        }
                    }
            })
            //用户调研列表详情
            .state('main.survey.surveylist.surveydetail', {
                url: '/surveydetail/:survey_id',
                module: 'private',  //需要授权的
                views: {
                    'survey@main.survey': {
                        templateUrl: basePath + 'surveydetail.html',
                        controller: 'SurveyDetailCtrl'
                    }
                }
            })
            //我的发布
            .state('main.survey.mycreatelist', {
                    url: '/mycreatelist/:isInit',
                    module: 'private',  //需要授权的
                    views: {
                        'survey@main.survey': {
                            templateUrl: basePath + 'mycreatelist.html',
                            controller:'MyCreateListCtrl'
                        }
                    }
            })
            //我的发布调研详情
            .state('main.survey.mycreatelist.mycreatedetail', {
                url: '/mycreatedetail/:survey_id',
                module: 'private',  //需要授权的
                views: {
                    'survey@main.survey': {
                        templateUrl: basePath + 'mycreatedetail.html',
                        controller:'MyCreateDetailCtrl'
                    }
                }
            })
             //发布调研
            .state('main.survey.createsurvey', {
                    url: '/createsurvey',
                    module: 'private',  //需要授权的
                    views: {
                        'survey@main.survey': {
                            templateUrl: basePath + 'createsurvey.html',
                            controller:'CreateSurveyCtrl'
                        }
                    }
            })
      
}]);


