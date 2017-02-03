
    
IndexMod.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider,$q) {
           
    $stateProvider.state('main', {
        //url: '/login',
        module: 'private',  //不需要授权的
        data: {
            css: ['']
        },
        views: {
            //主页面
            '': {
                templateUrl: 'appnews/modules/main/view/index.html'
                //controller:  'indexCtr'

            },
            //顶部控制区域
            'header@main': {
                templateUrl: 'appnews/modules/common/view/' + 'header.html',
                controller: 'HeadCtrl'
            },
            //左部菜单区域
            'nav@main': {
                templateUrl: 'appnews/modules/common/view/' + 'nav.html',
                controller: 'navCtr'
            },
            //中心内容区域
            'mainContent@main': {
                templateUrl: 'appnews/modules/common/view/' + 'main_content.html',
                controller: ''
            },
        }
    })
        //权限错误提示
        .state('main.error', {
            url: '/error',
            module: 'private',
            views: {
                'errorContent@main': {
                    templateUrl: 'appnews/modules/common/view/error_content.html',
                    controller: ''
                }
            }

        })
    // 首页状态 板块拖拽
    .state('main.index', {
        url: '/index',
        module: 'private',  //需要授权的
        data: {
            css: [
                    'css/default.css',
                    'css/colleague.css'
            ]
        },
        views: {
            'indexContent@main': {
                templateUrl: 'appnews/modules/common/view/' + 'index-content.html',
                controller: 'indexCtr'
            }
        }
    })
    // 首页状态 板块拖拽
    .state('main.index.set', {
        url: '/set',
        module: 'private',  //需要授权的
        data: {
            css: [
                'css/default.css',
                'css/colleague.css'
            ]
        },
        views: {
            'indexSet@main': {
                templateUrl: 'appnews/modules/common/view/' + 'index-set.html',
                controller: 'indexSetCtr'
            }
        }
    })

    // 首页任务详情弹窗
    .state('main.index.taskdetail', {
        url: '/taskdetail/:task_id',
        module: 'private',  //需要授权的
        data: {
            css: [
                'css/task.css',
                'css/datetimepicker.css'
            ]
        },
        views: {
            'task_detail@main.index': {
                templateUrl: 'appnews/modules/task/view/' + 'task_detail.html',
                controller: 'myTaskCtr'
            }
        }
    })

    // 首页公告详情弹窗
    .state('main.index.noticedetail', {
        url: '/noticedetail/:notice_id',
        module: 'private',  //需要授权的
        data: {
            css: [
                'css/notice.css',
            ]
        },
        views: {
            'notice_detail@main.index': {
                templateUrl: 'appnews/modules/notice/view/noticedetail.html',
                controller: 'NoticeListCtrl'
            }
        }
    })

    // 首页我的申请详情
    .state('main.index.applydetail', {
        url: '/applydetail/:apply_id/:model_id',
        module: 'private',  //需要授权的
        data: {
            css: [
                'css/apply.css',
            ]
        },
        views: {
            'apply_detail@main.index': {
                templateUrl: 'appnews/modules/common/view/apply_detail.html',
                controller: 'MyApplyListController'
            }
        }
    })

    // 首页我的申请待审批详情
    .state('main.index.agentdetail', {
        url: '/agentdetail/:apply_id/:model_id/:model_type',
        module: 'private',  //需要授权的
        data: {
            css: [
                'css/apply.css',
                'css/lib/datetimepicker.css'
            ]
        },
        views: {
            'apply_agent@main.index': {
                templateUrl: 'appnews/modules/common/view/agent_detail.html',
                controller: 'AgentApplyListController'
            }
        }
    })

    //首页工作报告审阅详情
    .state('main.index.workdetail', {
        url: '/workdetail/:work_id',
        module: 'private',  //需要授权的
        data: {
            css: [
                'css/workStatement.css',
                'css/lib/datetimepicker.css'
            ]
        },
        views: {
            'workstate_detail@main.index': {
                templateUrl: 'appnews/modules/workStatement/view/workStatement_checked.html',
                controller: 'approveWorkCtrl'
            }
        }
    })

    //首页调研详情
    .state('main.index.surveydetail', {
        url: '/surveydetail/:survey_id',
        module: 'private',  //需要授权的
        data: {
            css: [
                'css/userResearch.css',
                'css/lib/datetimepicker.css'
            ]
        },
        views: {
            'survey_detail@main.index': {
                templateUrl: 'appnews/modules/survey/view/surveydetail.html',
                controller: 'SurveyDetailCtrl'
            }
        }
    })
            
}]);

