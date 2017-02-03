
  SurveyMod.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider,$q) {
         
            var basePath = 'appnews/modules/msg/view/';

            //主路由
            $stateProvider.state('main.msg', {
                url: '/msg',
                module: 'private',  //需要授权的
                data: {
                    css: [
                        'css/msg.css'
                         ]
                },
                views: {
                    'msgContent@main': {
                        templateUrl: basePath + 'msg_content.html'
                    }
                }
            })
            //审核消息列表
            .state('main.msg.approvalmsglist', {
                url: '/approvalmsglist',
                module: 'private',  //需要授权的
                views: {
                    'msg@main.msg': {
                        templateUrl: basePath + 'approvalmsglist.html',
                        controller: 'ApprovalMsgCtrl'
                    }
                }
            })
            //申请消息列表
            .state('main.msg.applymsglist', {
                    url: '/applymsglist',
                    module: 'private',  //需要授权的
                    views: {
                        'msg@main.msg': {
                        templateUrl: basePath + 'applymsglist.html',
                        controller: 'ApplyMsgCtrl'
                        }
                    }
            })
            //会议消息列表
            .state('main.msg.meetingmsglist', {
                url: '/meetingmsglist',
                module: 'private',  //需要授权的
                views: {
                    'msg@main.msg': {
                        templateUrl: basePath + 'meetingmsglist.html',
                        controller: 'MeetingMsgCtrl'
                    }
                }
            })
            //任务消息列表
            .state('main.msg.taskmsglist', {
                url: '/taskmsglist',
                module: 'private',  //需要授权的
                views: {
                    'msg@main.msg': {
                        templateUrl: basePath + 'taskmsglist.html',
                        controller: 'TaskMsgCtrl'
                    }
                }
            })
            //项目消息列表
            .state('main.msg.projectmsglist', {
                url: '/projectmsglist',
                module: 'private',  //需要授权的
                views: {
                    'msg@main.msg': {
                        templateUrl: basePath + 'projectmsglist.html',
                        controller: 'ProjectMsgCtrl'
                    }
                }
            })
            //报告消息列表
            .state('main.msg.reportmsglist', {
                url: '/reportmsglist',
                module: 'private',  //需要授权的
                views: {
                    'msg@main.msg': {
                        templateUrl: basePath + 'reportmsglist.html',
                        controller: 'ReportMsgCtrl'
                    }
                }
            })

      
}]);


