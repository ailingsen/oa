
  personalInfoMod.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider,$q) {
         
            var basePath = 'appnews/modules/personalInfo/view/';

            //主路由
            $stateProvider.state('main.personalInfo', {
                url: '/personalInfo',
                module: 'private',  //需要授权的
                data: {
                    css: [
                           'css/personal_info.css',
                           'css/lib/datetimepicker.css'
                         ]
                },
                views: {
                    'personalInfoContent@main': {
                        templateUrl: basePath + 'personalInfo_content.html'
                    }
                }
            })
            //个人信息-我的信息列表页
            .state('main.personalInfo.myInfoList', { 
                    url: '/myInfoList',
                    module: 'private',  //需要授权的
                    views: {
                        'personalInfo_myInfoList@main.personalInfo': {
                        templateUrl: basePath + 'personalInfo_myInfoList.html',
                        controller: 'personalInfoCtrl'
                        }
                    }
            })
            
            //个人信息-积分与技能列表页
            .state('main.personalInfo.skillList', { 
                    url: '/skillList',
                    module: 'private',  //需要授权的
                    views: {
                        'personalInfo_skillList@main.personalInfo': {
                        templateUrl: basePath + 'personalInfo_skillList.html',
                        controller: 'skillScoreCtrl'
                        }
                    }
            })
            //个人信息-假期列表页
            .state('main.personalInfo.vacationList', { 
                    url: '/vacationList',
                    module: 'private',  //需要授权的
                    views: {
                        'personalInfo_vacationList@main.personalInfo': {
                        templateUrl: basePath + 'personalInfo_vacationList.html',
                        controller: 'vacationCtrl'
                        }
                    }
            })
           
}]);


