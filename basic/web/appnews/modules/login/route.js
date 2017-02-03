LoginMod.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider, $q) {
    $stateProvider.state('login', {
        url: '/login',
        module: 'public',  //不需要授权的
        data: {
            css: ['css/login.css']
        },
        views: {
            '': {
                templateUrl: 'appnews/modules/login/view/' + 'login.html',
                controller: 'LoginCtrl'
            }
        }
    })
        .state('forgetPassword', {
            url: '/forgetPassword/:password_token/:uid',
            module: 'public',  //不需要授权的
            data: {
                css: ['css/login.css']
            },
            views: {
                '': {
                    templateUrl: 'appnews/modules/login/view/' + 'psdreset.html',
                    controller: 'ForgetPasswordCtrl'
                }
            }
        })
        .state('aboutwe', {
            url: '/aboutwe',
            module: 'public',  //不需要授权的
            data: {
                css: ['css/login.css']
            },
            views: {
                '': {
                    templateUrl: 'appnews/modules/login/view/' + 'aboutwe.html',
                }
            }
        })
        .state('mobile', {
            url: '/mobile',
            module: 'public',  //不需要授权的
            data: {
                css: ['css/login.css']
            },
            views: {
                '': {
                    templateUrl: 'appnews/modules/login/view/' + 'mobile.html',
                }
            }
        })
        .state('help', {
            url: '/help',
            module: 'public',  //不需要授权
            data: {
                css: ['css/login.css']
            },
            views: {
                '': {
                    templateUrl: 'appnews/modules/login/view/' + 'help.html',
                    controller: 'LoginCtrl'
                }
            }
        });

}]);




