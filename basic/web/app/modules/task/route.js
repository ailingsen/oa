define(function(require) {

    return {

        setRoute:function($stateProvider){

            var basePath = 'app/modules/task/view/';

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
                    url: '/create/:task_status',
                    module: 'private',  //需要授权的
                    views: {
                        'task_create@main.task': {
                        templateUrl: basePath + 'task_create.html',
                        controller: function($scope,$http,$rootScope,Publicfactory) {
                               require(['taskCtr'],function(t){
                                   $scope.$apply(t.init.call($scope,$scope,$http,$rootScope,Publicfactory));
                               });
                            }
                        }
                    }
            })
            //我接受的任务
            .state('main.task.mytask', { 
                    url: '/myTask/:task_status',
                    module: 'private',  //需要授权的
                    data: {
                    css: [
                           'css/lib/datetimepicker.css'
                         ]
                    },
                    views: {
                        'task_myTask@main.task': {
                            templateUrl: basePath + 'task_myTask.html'
                        },
                        'task_myTask_top@main.task.mytask': {
                            //templateUrl: 'modules/task/views/task_myTask_top.html',
                            //controller: 'myTaskTopCtrl'
                        },
                        'task_myTask_list@main.task.mytask': {
                            templateUrl: basePath + 'task_myTask_list.html',
                            controller: '' //myTaskListController
                        }
                    }
            });
          }

    };
    
});