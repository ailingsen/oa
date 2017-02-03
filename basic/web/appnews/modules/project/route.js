ProjectMod.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider,$q) {

    var basePath = 'appnews/modules/project/view/';

    //任务主路由
    $stateProvider.state('main.project', {
        url: '/project',
        module: 'private',  //需要授权的
        data: {
            css: [
                   'css/project.css',
                   'css/task.css',
                   'css/lib/datetimepicker.css',
                   'appnews/lib/angular-gantt/dist/angular-gantt.css',
                   'appnews/lib/angular-gantt/dist/angular-gantt-plugins.css',
                   'appnews/lib/angular-ui-tree/dist/angular-ui-tree.min.css'
                 ]
        },
        views: {
            'projectContent@main': {
                templateUrl: basePath + 'project_content.html'
            }
        }
    })

    //我创建的项目
    .state('main.project.mycreatepro', {
            url: '/mycreatepro/:isInit/:list_status',
            module: 'private',  //需要授权的
            views: {
                'mycreatepro@main.project': {
                templateUrl: basePath + 'prolist.html',
                controller: 'ProListCtrl'
                }
            }
    })

    //我创建的项目-项目详情
    .state('main.project.mycreatepro.prodetail', {
        url: '/prodetail/:pro_id',
        module: 'private',  //需要授权的
        views: {
            'prodetail@main.project': {
                templateUrl: basePath + 'prodetail.html',
                controller: 'ProDetailCtrl'
            }
        }
    })

    //我创建的项目-项目详情-日志
    .state('main.project.mycreatepro.prodetail.prolog', {
        url: '/prolog/:pro_id',
        module: 'private',  //需要授权的
        views: {
            'prolog@main.project': {
                templateUrl: basePath + 'prodetaillog.html',
                controller: 'ProDetailLogCtrl'
            }
        }
    })

    //我创建的项目-项目甘特图
    .state('main.project.mycreatepro.gantt', {
        url: '/gantt/:pro_id/:type/:position',//type 1我创建的项目进入  2我参与的   2公开的        position  1列表进入   2详情进入
        module: 'private',  //需要授权的
        views: {
            'mycreatepro@main.project': {
                templateUrl: basePath + 'progantt.html',
                controller: 'ProGanttCtrl'
            }
        }
    })

    //参与的项目
    .state('main.project.myinvoepro', {
        url: '/myinvoepro/:isInit/:list_status',
        module: 'private',  //需要授权的
        views: {
            'mycreatepro@main.project': {
                templateUrl: basePath + 'prolist.html',
                controller: 'ProListCtrl'
            }
        }
    })

    //我参与的项目-项目详情
    .state('main.project.myinvoepro.prodetail', {
        url: '/prodetail/:pro_id',
        module: 'private',  //需要授权的
        views: {
            'prodetail@main.project': {
                templateUrl: basePath + 'prodetail.html',
                controller: 'ProDetailCtrl'
            }
        }
    })

    //我创建的项目-项目详情-日志
    .state('main.project.myinvoepro.prodetail.prolog', {
        url: '/prolog/:pro_id',
        module: 'private',  //需要授权的
        views: {
            'prolog@main.project': {
                templateUrl: basePath + 'prodetaillog.html',
                controller: 'ProDetailLogCtrl'
            }
        }
    })

    //我参与的项目-项目甘特图
    .state('main.project.myinvoepro.gantt', {
        url: '/gantt/:pro_id/:type/:position',//type 1我创建的项目进入  2我参与的   2公开的        position  1列表进入   2详情进入
        module: 'private',  //需要授权的
        views: {
            'mycreatepro@main.project': {
                templateUrl: basePath + 'progantt.html',
                controller: 'ProGanttCtrl'
            }
        }
    })

    //我参与的项目-项目进度
    .state('main.project.myinvoepro.progress', {
        url: '/progress/:pro_id/:type/:position',//type 1我创建的项目进入  2我参与的   2公开的        position  1列表进入   2详情进入
        module: 'private',  //需要授权的
        views: {
            'mycreatepro@main.project': {
                templateUrl: basePath + 'proprogress.html',
                controller: 'ProProgressCtrl'
            }
        }
    })

    //公开项目
    .state('main.project.openpro', {
        url: '/openpro/:isInit/:list_status',
        module: 'private',  //需要授权的
        views: {
            'mycreatepro@main.project': {
                templateUrl: basePath + 'prolist.html',
                controller: 'ProListCtrl'
            }
        }
    })

    //公开项目-项目详情
    .state('main.project.openpro.prodetail', {
        url: '/prodetail/:pro_id',
        module: 'private',  //需要授权的
        views: {
            'prodetail@main.project': {
                templateUrl: basePath + 'prodetail.html',
                controller: 'ProDetailCtrl'
            }
        }
    })

    //公开项目-项目详情-日志
    .state('main.project.openpro.prodetail.prolog', {
        url: '/prolog/:pro_id',
        module: 'private',  //需要授权的
        views: {
            'prolog@main.project': {
                templateUrl: basePath + 'prodetaillog.html',
                controller: 'ProDetailLogCtrl'
            }
        }
    })

    //公开项目-项目甘特图
    .state('main.project.openpro.gantt', {
        url: '/gantt/:pro_id/:type/:position',//type 1我创建的项目进入  2我参与的   2公开的        position  1列表进入   2详情进入
        module: 'private',  //需要授权的
        views: {
            'mycreatepro@main.project': {
                templateUrl: basePath + 'progantt.html',
                controller: 'ProGanttCtrl'
            }
        }
    })

    //创建项目
    .state('main.project.createpro', {
        url: '/createpro',
        module: 'private',  //需要授权的
        views: {
            'mycreatepro@main.project': {
                templateUrl: basePath + 'createpro.html',
                controller: 'CreateProCtrl'
            }
        }
    })

    //编辑项目
    .state('main.project.editpro', {
        url: '/editpro/:pro_id/:type',
        module: 'private',  //需要授权的
        views: {
            'mycreatepro@main.project': {
                templateUrl: basePath + 'editpro.html',
                controller: 'EditProCtrl'
            }
        }
    })

    //项目进度
    .state('main.project.progress', {
        url: '/progress/:pro_id/:type/:position',//type 1我创建的项目进入  2我参与的   2公开的        position  1列表进入   2详情进入
        module: 'private',  //需要授权的
        views: {
            'progress_head@main.project': {
                templateUrl: basePath + 'proprogress_head.html',
                controller: 'ProProgressHeadCtrl'
            },
            'progress_list@main.project': {
                templateUrl: basePath + 'proprogress_list.html',
                controller: 'ProProgressListCtrl'
            }
        }
    })

    //项目进度list
    .state('main.project.progress.list', {
        url: '/list/:pro_id',
        module: 'private',  //需要授权的
        views: {
            'progress_list@main.project': {
                templateUrl: basePath + 'proprogress_list.html',
                controller: 'ProProgressListCtrl'
            }
        }
    })

    //项目进度log
    .state('main.project.progress.list.log', {
        url: '/log/:pro_id',
        module: 'private',  //需要授权的
        views: {
            'progress_list_log@main.project': {
                templateUrl: basePath + 'proprogress_log.html',
                controller: 'ProDetailLogCtrl'
            }
        }
    })

    //项目任务列表
    .state('main.project.protasklist', {
        url: '/protasklist/:pro_id/:status/:type/:position',
        module: 'private',  //需要授权的
        views: {
            'mycreatepro@main.project': {
                templateUrl: basePath + 'protasklist.html',
                controller: 'ProTaskListCtrl'
            }
        }
    })

    //项目工作报告
    .state('main.project.prowork', {
        url: '/prowork/:pro_id/:type/:position',
        module: 'private',  //需要授权的
        views: {
            'mycreatepro@main.project': {
                templateUrl: basePath + 'prowork.html',
                controller: 'ProMemReportCtrl'
            }
        }
    })
}]);