ApplymobileMod.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider, $q) {
    
    var basePath = 'appnews/modules/applymobile/view/';

    //任务主路由
    $stateProvider.state('main.applymobile', {
        url: '/applymobile',
        module: 'private',  //需要授权的
        data: {
            css: [
                'css/applymobile.css',
                'css/apply.css',
                'css/lib/mobiledatetimepicker.css'
            ]
        },
        views: {
            'applyContent@main': {
                templateUrl: basePath + 'apply_content.html',
                controller: 'ApplyMobileController'
            }
        }
    })
    
        //文章列表
        .state('main.applymobile.application', {
            url: '/application',
            module: 'private',  //需要授权的
            views: {
                'apply_application@main.applymobile': {
                    templateUrl: basePath + 'apply_model_list.html',
                    controller: 'ApplymobileAppController'
                }
            }
        })
        //加班申请
        .state('main.applymobile.application.overtimecreate', {
            url: '/overtimecreate/:model_id',
            module: 'private',  //需要授权的
            data: {
                css: [
                    'css/lib/mobiledatetimepicker.css'
                ]
            },
            views: {
                'overtime_apply@main.applymobile': {
                    templateUrl: basePath + 'overtime_apply_create.html',
                    controller: 'OvertimeApplymobileCreateCtrl'
                }
            }
        })
        //请假申请
        .state('main.applymobile.application.leaveapplycreate', {
            url: '/leaveapplycreate',
            module: 'private',  //需要授权的
            views: {
                'leave_apply@main.applymobile': {
                    templateUrl: basePath + 'leave_apply_create.html',
                    controller: 'LeaveApplymobileCreateCtrl'
                }
            }
        })
        //漏打卡申请
        .state('main.applymobile.application.drainPunchApply', {
            url: '/drainPunchApply',
            module: 'private',  //需要授权的
            views: {
                'apply_drainPunchApply@main.applymobile': {
                    templateUrl: basePath + 'drainPunch_apply.html',
                    controller: 'drainPunchmobileCtrl'
                }
            }
        })
        //职级申请页面
        .state('main.applymobile.application.rankApply', {
            url: '/rankApply',
            module: 'private',  //需要授权的
            views: {
                'apply_rankApply@main.applymobile': {
                    templateUrl: basePath + 'rank_apply.html',
                    controller: 'rankApplymobileCtrl'
                }
            }
        })
        //弹性上班申请
        .state('main.applymobile.application.flexibleWork', {
            url: '/flexibleWork',
            module: 'private',  //需要授权的
            views: {
                'apply_flexibleWork@main.applymobile': {
                    templateUrl: basePath + 'flexible_work_apply.html',
                    controller: 'flexibleWorkmobileCtr'
                }
            }
        })
    //     //自定义表单
    //     .state('main.applymobile.application.customcreate', {
    //         url: '/customcreate/:model_id',
    //         module: 'private',  //需要授权的
    //         data: {
    //             css: [
    //                 'css/apply.css',
    //                 'css/lib/datetimepicker.css'
    //             ]
    //         },
    //         views: {
    //             'leave_apply@main.applymobile': {
    //                 templateUrl: basePath + 'apply_custom_create.html',
    //                 controller: 'CustomApplyCreateCtrl'
    //             }
    //         }
    //     })

        //列表编辑暂测试用
        .state('main.applymobile.mine', {
            url: '/mine/:apply_id/:model_id/:action_type',
            module: 'private',  //需要授权的
            views: {
                'apply_mine@main.applymobile': {
                    templateUrl: basePath + 'apply_myapply_list.html',
                    controller: 'MyApplyListmobileController'
                }
            }
        })
        //列表暂测试用

        .state('main.applymobile.application.customcreate', {
            url: '/customcreate/:model_id',
            module: 'private',  //需要授权的 
            views: {
                'leave_apply@main.applymobile': {
                    templateUrl: basePath + 'apply_custom_create.html',
                    controller: 'CustomApplymobileCreateCtrl'
                }
            }
        })
 
       .state('main.applymobile.agent', {
            url: '/agent/:apply_id/:model_id/:modeltype',
            module: 'private',  //需要授权的
            views: {
                'apply_agent@main.applymobile': {
                    templateUrl: basePath + 'apply_agent_list.html',
                    controller: 'AgentApplyListmobileController'
                }
            }
        })


}]);