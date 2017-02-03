NoticeMod.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider,$q) {
    var basePath = 'appnews/modules/notice/view/';

    //任务主路由
    $stateProvider.state('main.notice', {
        url: '/notice',
        module: 'private',  //需要授权的
        data: {
            css: [
                'css/lib/datetimepicker.css',
                'css/attendance.css',
                'css/notice.css',
                'css/task.css'
            ]
        },
        views: {
            'noticeContent@main': {
                templateUrl: basePath + 'notice_content.html'
            }
        }
    })

    //公告查询
    .state('main.notice.noticeQuery', {
        url: '/noticeQuery/:notice_id',
        module: 'private',  //需要授权的
        views: {
            'noticeQuery@main.notice': {
                templateUrl: basePath + 'noticeQuery.html',
                controller: 'NoticeListCtrl'
            }
        }
    })

    //公告管理
    .state('main.notice.noticeManage', {
        url: '/noticeManage',
        module: 'private',  //需要授权的
        views: {
            'noticeManage@main.notice': {
                templateUrl: basePath + 'noticeManage.html',
                controller: 'NoticeListCtrl'
            }
        }
    })

    //创建公告
    .state('main.notice.noticecreate', {
        url: '/noticecreate',
        module: 'private',  //需要授权的
        views: {
            'noticecreate@main.notice': {
                templateUrl: basePath + 'noticecreate.html',
                controller: 'NoticeCreateCtrl'
            }
        }
    })
	
}]);




