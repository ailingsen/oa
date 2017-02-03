ApplyMod.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider, $q) {

    var basePath = 'appnews/modules/apply/view/';

    //任务主路由
    $stateProvider.state('main.apply', {
        url: '/apply',
        module: 'private',  //需要授权的
        data: {
            css: [
                'css/apply.css',
                'css/lib/datetimepicker.css'
            ]
        },
        views: {
            'applyContent@main': {
                templateUrl: basePath + 'apply_content.html'
            }
        }
    })
        .state('main.apply.mine', {
            url: '/mine/:apply_id/:model_id',
            module: 'private',  //需要授权的
            data: {
                css: [
                    'css/apply.css',
                    'css/lib/datetimepicker.css'
                ]
            },
            views: {
                'apply_mine@main.apply': {
                    templateUrl: basePath + 'apply_myapply_list.html',
                    controller: 'MyApplyListController'
                }
            }
        })
        .state('main.apply.agent', {
            url: '/agent/:apply_id/:model_id/:model_type',
            module: 'private',  //需要授权的
            data: {
                css: [
                    'css/apply.css',
                    'css/lib/datetimepicker.css'
                ]
            },
            views: {
                'apply_agent@main.apply': {
                    templateUrl: basePath + 'apply_agent_list.html',
                    controller: 'AgentApplyListController'
                }
            }
        })
        .state('main.apply.application', {
            url: '/application',
            module: 'private',  //需要授权的
            data: {
                css: [
                    'css/apply.css',
                    'css/lib/datetimepicker.css'
                ]
            },
            views: {
                'apply_application@main.apply': {
                    templateUrl: basePath + 'apply_model_list.html',
                    controller: 'ApplyAppController'
                }
            }
        })
        .state('main.apply.application.overtimecreate', {
            url: '/overtimecreate/:model_id',
            module: 'private',  //需要授权的
            data: {
                css: [
                    'css/apply.css',
                    'css/lib/datetimepicker.css'
                ]
            },
            views: {
                'overtime_apply@main.apply': {
                    templateUrl: basePath + 'overtime_apply_create.html',
                    controller: 'OvertimeApplyCreateCtrl'
                }
            }
        })
        .state('main.apply.application.leaveapplycreate', {
            url: '/leaveapplycreate',
            module: 'private',  //需要授权的
            data: {
                css: [
                    'css/apply.css',
                    'css/lib/datetimepicker.css'
                ]
            },
            views: {
                'leave_apply@main.apply': {
                    templateUrl: basePath + 'leave_apply_create.html',
                    controller: 'LeaveApplyCreateCtrl'
                }
            }
        })
        .state('main.apply.application.customcreate', {
            url: '/customcreate/:model_id',
            module: 'private',  //需要授权的
            data: {
                css: [
                    'css/apply.css',
                    'css/lib/datetimepicker.css'
                ]
            },
            views: {
                'leave_apply@main.apply': {
                    templateUrl: basePath + 'apply_custom_create.html',
                    controller: 'CustomApplyCreateCtrl'
                }
            }
        })
        .state('main.apply.manage', {
            url: '/manage',
            module: 'private',  //需要授权的
            data: {
                css: [
                    'css/apply.css',
                    'css/lib/datetimepicker.css'
                ]
            },
            views: {
                'apply_manage@main.apply': {
                    templateUrl: basePath + 'apply_manage_list.html',
                    controller: 'ApplyModelListController'
                }
            }, onExit: function () {
                angular.element('.applyindexeditbor').remove();
            } 
        })
        //职级申请页面
        .state('main.apply.rankApply', {
            url: '/rankApply',
            module: 'private',  //需要授权的
            views: {
                'apply_rankApply@main.apply': {
                    templateUrl: basePath + 'rank_apply.html',
                    controller: 'rankApplyCtrl'
                }
            }
        })
        //弹性上班申请
        .state('main.apply.flexibleWork', {
            url: '/flexibleWork',
            module: 'private',  //需要授权的
            views: {
                'apply_flexibleWork@main.apply': {
                    templateUrl: basePath + 'flexible_work_apply.html',
                    controller: 'flexibleWorkCtr'
                }
            }
        })
        //漏打卡申请
        .state('main.apply.drainPunchApply', {
            url: '/drainPunchApply',
            module: 'private',  //需要授权的
            views: {
                'apply_drainPunchApply@main.apply': {
                    templateUrl: basePath + 'drainPunch_apply.html',
                    controller: 'drainPunchCtrl'
                }
            }
        })
        //设置审批流程
        .state('main.apply.flow', {
            url: '/flow/:id/:type/:readOnly',
            module: 'private',  //需要授权的
            views: {
                'apply_flow@main.apply': {
                    templateUrl: basePath + 'apply_flow.html',
                    controller: 'applyFlowCtrl'
                }
            }
        })
        //创建自定义表单
        .state('main.apply.createModel', {
            url: '/createModel',
            module: 'private',  //需要授权的
            views: {
                'apply_createModel@main.apply': {
                    templateUrl: basePath + 'apply_model_create.html',
                    controller: 'applyModelCreateCtrl'
                }
            },onExit:function(){
               // $(document).unbind();
            }
        })
        //前端页面demo=======================================
        //前端页面demo=======================================
        // 前端页面demo=======================================
        .state('main.apply.demo', {
            url: '/demo',
            module: 'private',  //需要授权的
            views: {
                'apply_demo@main.apply': {
                    templateUrl: basePath + 'apply_demo.html',
                    controller: 'applyCtrl'
                }
            }, onExit: function () {
                angular.element('.formindexbor').remove();
            }
        })
        .state('main.apply.demoform', {
            url: '/demoform',
            module: 'private',  //需要授权的
            views: {
                'apply_demoform@main.apply': {
                    templateUrl: basePath + 'apply_demoform.html',
                    controller: 'applyshowCtrl'
                }
            }, onExit: function () {
                angular.element(".formpresentation").html("");
                angular.element('.datetimepicker').remove();
            }
        }).state('main.apply.demoeditform', {
            url: '/demoeditform',
            module: 'private',  //需要授权的
            views: {
                'apply_demoeditform@main.apply': {
                    templateUrl: basePath + 'apply_demoeditform.html',
                    controller: 'applyeditshowCtrl'
                }
            }, onExit: function () {
                angular.element(".formpresentation").html("");
                angular.element('.datetimepicker').remove();
            }
        }).state('main.apply.demoshowform', {
            url: '/demoshowform',
            module: 'private',  //需要授权的
            views: {
                'apply_demoshowform@main.apply': {
                    templateUrl: basePath + 'apply_demoshowform.html',
                    controller: 'applyshowformdataCtrl'
                }
            }, onExit: function () {
                angular.element(".formpresentation").html("");
            }
        });


}]);