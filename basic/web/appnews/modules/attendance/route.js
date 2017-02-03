AttendanceMod.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider,$q) {
    var basePath = 'appnews/modules/attendance/view/';

    //考勤主路由
    $stateProvider.state('main.attendance', {
        url: '/attendance',
        module: 'private',  //需要授权的
        data: {
            css: [
                'css/attendance.css',
                'css/lib/datetimepicker.css'
            ]
        },
        views: {
            'attendanceContent@main': {
                templateUrl: basePath + 'attendance_content.html'
            }
        }
    })

    //我的考勤
    .state('main.attendance.myattend', {
        url: '/myattend',
        module: 'private',  //需要授权的
        views: {
            'myattend@main.attendance': {
                templateUrl: basePath + 'myattend.html',
                controller: 'MyAttendCtrl'
            }
        }
    })

    //员工考勤
    .state('main.attendance.allattend', {
        url: '/allattend',
        module: 'private',  //需要授权的
        views: {
            'allattend@main.attendance': {
                templateUrl: basePath + 'allattend.html',
                controller: 'AllAttendCtrl'
            }
        }
    })

    //假期统计
    .state('main.attendance.holidaySta', {
        url: '/holidaySta',
        module: 'private',  //需要授权的
        views: {
            'holidaySta@main.attendance': {
                templateUrl: basePath + 'holidaySta.html',
                controller: 'vacationStat'
            }
        }
    })
    //假期管理
    .state('main.attendance.holidayManage', {
        url: '/holidayManage',
        module: 'private',  //需要授权的
        views: {
            'holidayManage@main.attendance': {
                templateUrl: basePath + 'holidayManage.html',
                controller: 'VacationMgnCtrl'
            }
        }
    })
    //考勤设置
    .state('main.attendance.attendset', {
        url: '/attendset',
        module: 'private',  //需要授权的
        views: {
            'attendset@main.attendance': {
                templateUrl: basePath + 'attendset.html',
                controller: 'AttendSetCtrl'
            }
        }
    })
    //考勤统计
    .state('main.attendance.attendcount', {
        url: '/attendcount',
        module: 'private',  //需要授权的
        views: {
            'attendSta@main.attendance': {
                templateUrl: basePath + 'attendcount.html',
                controller: 'AttendCountCtrl'
            }
        }
    })
	
}]);




