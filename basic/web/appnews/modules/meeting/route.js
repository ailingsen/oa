MeetingMod.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider,$q) {
    var basePath = 'appnews/modules/meeting/view/';
    //会议室预定主路由
    $stateProvider.state('main.meeting', {
        url: '/meeting',
        module: 'private',  //需要授权的
        data: {
            css: [
                'css/meeting.css',
                'css/lib/datetimepicker.css',
                'css/notice.css',
                'css/attendance.css'
            ]
        },
        views: {
            'meetingContent@main': {
                templateUrl: basePath + 'meeting_content.html'
            }
        }
    })
    //预定
    .state('main.meeting.reserve', {
        url: '/reserve',
        module: 'private',  //需要授权的
        views: {
            'reserve@main.meeting': {
                templateUrl: basePath + 'reserve.html',
                controller: 'Reserve'
            }
        }
    })
    //管理
    .state('main.meeting.reserveManage', {
        url: '/reserveManage',
        module: 'private',  //需要授权的
        views: {
            'reserve@main.meeting': {
                templateUrl: basePath + 'reserveManage.html',
                controller: 'ReserveManage'
            }
        }
    })
}]);
