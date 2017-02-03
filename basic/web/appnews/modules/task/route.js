
  TaskMod.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider,$q) {
         
            var basePath = 'appnews/modules/task/view/';

            //任务主路由
            $stateProvider.state('main.task', {
                url: '/task',
                module: 'private',  //需要授权的
                data: {
                    css: [
                           'css/task.css',
                           'css/lib/datetimepicker.css'
                         ]
                },
                views: {
                    'taskContent@main': {
                        templateUrl: basePath + 'task_content.html'
                    }
                }
            })
            //创建任务
            .state('main.task.create', { 
                    url: '/create/:pro_id',
                    module: 'private',  //需要授权的
                    views: {
                        'task_create@main.task': {
                        templateUrl: basePath + 'task_create.html',
                        controller: 'taskCreateCtrl'
                        }
                    }
            })
            //我接受的任务
            .state('main.task.myTask', { 
                    url: '/myTask/:task_id',
                    module: 'private',  //需要授权的
                    views: {
                        'task_myTask@main.task': {
                            templateUrl: basePath + 'task_myTask.html',
                            controller: 'myTaskCtr'
                        }
                    }
            })
            //我发布的任务
            .state('main.task.myReleaseTask', {
                url: '/myReleaseTask/:task',
                module: 'private',  //需要授权的
                views: {
                    'task_myReleaseTask@main.task': {
                        templateUrl: basePath + 'task_myReleaseTask.html',
                        controller: 'myReleaseTaskCtr'
                    }
                }
            })
            //悬赏任务列表
            .state('main.task.rewardTask', {
                url: '/rewardTask/:task',
                module: 'private',  //需要授权的
                views: {
                    'task_rewardTask@main.task': {
                        templateUrl: basePath + 'task_rewardTask.html',
                        controller: 'rewardTaskCtr'
                    }
                }
            })
            //我的悬赏任务列表
            .state('main.task.myRewardTask', {
                url: '/myRewardTask/:task',
                module: 'private',  //需要授权的
                views: {
                    'task_myRewardTask@main.task': {
                        templateUrl: basePath + 'task_myRewardTask.html',
                        controller: 'myRewardTaskCtr'
                    }
                }
            })
            //我的认领记录任务列表
            .state('main.task.myClaimRecord', {
                url: '/myClaimRecord',
                module: 'private',  //需要授权的
                views: {
                    'task_myClaimRecord@main.task': {
                        templateUrl: basePath + 'task_myClaimRecord.html',
                        controller: 'myClaimRecordCtr'
                    }
                }
            })
            //前端页面demo=======================================
            //前端页面demo=======================================
            //前端页面demo=======================================
            .state('main.task.demo', {
                    url: '/demo/:task_status',
                    module: 'private',  //需要授权的
                    views: {
                        'task_demo@main.task': {
                            templateUrl: basePath + 'task_demo.html',
                            controller: 'rewardTaskCtr'
                        }
                    }
            })
            //编辑悬赏任务
                .state('main.task.myRewardTask.editer', {
                    url: '/editer/:task_id/:task_type/:taskType',
                    module: 'private',  //需要授权的
                    views: {
                        'task_edite@main.task': {
                            templateUrl: basePath + 'task_edite.html',
                            controller: 'taskEditeCtrl'
                        }
                    }
                })
            //编辑任务
             .state('main.task.myReleaseTask.edite', {
                  url: '/edite/:task_id/:task_type/:taskType',
                  module: 'private',  //需要授权的
                  views: {
                      'task_edite@main.task': {
                          templateUrl: basePath + 'task_edite.html',
                          controller: 'taskEditeCtrl'
                      }
                  }
             });
}]);