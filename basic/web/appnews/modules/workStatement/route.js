
  workStatementMod.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider,$q) {
         
            var basePath = 'appnews/modules/workStatement/view/';

            //主路由
            $stateProvider.state('main.workStatement', {
                url: '/workStatement',
                module: 'private',  //需要授权的
                data: {
                    css: [
                           'css/workStatement.css',
                           'css/lib/datetimepicker.css'
                         ]
                },
                views: {
                    'workStatementContent@main': {
                        templateUrl: basePath + 'workStatement_content.html'
                    }
                }
            })
            //工作报告审阅表格
            .state('main.workStatement.checkTable', { 
                    url: '/checkTable/:work_id',
                    module: 'private',  //需要授权的
                    views: {
                        'workStatement_checkTable@main.workStatement': {
                        templateUrl: basePath + 'workStatement_checkTable.html',
                        controller: 'approveWorkCtrl'
                        }
                    }
            })
            //工作报告已审阅详情
            .state('main.workStatement.checkTable.checked', { 
                    url: '/checked/:work_id',
                    module: 'private',  //需要授权的
                    views: {
                        'workStatement_checked@main.workStatement': {
                        templateUrl: basePath + 'workStatement_checked.html',
                        controller: 'workDetailCtrl'
                        }
                    }
            })
            //我的工作报告表格
            .state('main.workStatement.myWorkStatementTable', { 
                    url: '/myWorkStatementTable',
                    module: 'private',  //需要授权的
                    views: {
                        'workStatement_myWorkStatementTable@main.workStatement': {
                        templateUrl: basePath + 'workStatement_myWorkStatementTable.html',
                        controller: 'myWorkCtrl'
                        }
                    }
            })
            //编写工作报告
            .state('main.workStatement.myWorkStatementTable.write', { 
                    url: '/write/:work_id',
                    module: 'private',  //需要授权的
                    views: {
                        'workStatement_write@main.workStatement': {
                        templateUrl: basePath + 'workStatement_write.html',
                        controller: 'writeWorkCtrl'
                        }
                    }
            })
             //编写工作报告
            .state('main.workStatement.myWorkStatementTable.edite', {
                    url: '/edit/:work_id',
                    module: 'private',  //需要授权的
                    views: {
                        'workStatement_edit@main.workStatement': {
                        templateUrl: basePath + 'workStatement_edit.html',
                        controller: 'editeWorkCtrl'
                        }
                    }
            })
             //我的工作报告查看详情
            .state('main.workStatement.myWorkStatementTable.TabDetails', { 
                    url: '/TabDetails/:work_id',
                    module: 'private',  //需要授权的
                    views: {
                        'workStatement_TabDetails@main.workStatement': {
                        templateUrl: basePath + 'workStatement_TabDetails.html',
                        controller: 'workDetailCtrl'
                        }
                    }
            })
}]);


