TaskMod.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider,$q) {
    var basePath = 'appnews/modules/colleague/view/';

    //同事主路由
    $stateProvider.state('main.colleague', {
        url: '/colleague',
        module: 'private',  //需要授权的
        data: {
            css: [
                'css/task.css',
                'css/lib/datetimepicker.css',
                'css/colleague.css',
                'css/attendance.css',
                'css/meeting.css'
            ]
        },
        views: {
            'colleagueContent@main': {
                templateUrl: basePath + 'colleague_content.html'
            }
        }
    })
    //工作情况
    .state('main.colleague.work', {
        url: '/work',
        module: 'private',  //需要授权的
        views: {
            'work@main.colleague': {
                templateUrl: basePath + 'work.html',
                controller: 'WorkCtr'
            }
        }
    })
    //我的同事
    .state('main.colleague.myColleague', {
        url: '/myColleague',
        module: 'private',  //需要授权的
        views: {
            'myColleague@main.colleague': {
                templateUrl: basePath + 'myColleague.html',
                controller: 'MyColleagueCtr'
            }
        }
    })
    //积分榜
    .state('main.colleague.scoreboard', {
        url: '/scoreboard',
        module: 'private',  //需要授权的
        views: {
            'scoreboard@main.colleague': {
                templateUrl: basePath + 'scoreboard.html',
                controller: 'ScoreboardCtr'
            }
        }
    })
}]);
