
  systemMod.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider,$q) {
         
            var basePath = 'appnews/modules/system/view/';

            //任务主路由
            $stateProvider.state('main.system', {
                url: '/system',
                module: 'private',  //需要授权的
                data: {
                    css: [
                           'css/system.css',
                           'css/lib/datetimepicker.css'
                         ]
                },
                views: {
                    'systemContent@main': {
                        templateUrl: basePath + 'system_content.html'
                    }
                }
            })

            //权限管理（员工管理）-列表页
            .state('main.system.employeelist', {
                    url: '/employeelist/:isInit',
                    module: 'private',  //需要授权的
                    views: {
                        'jurisdictionList@main.system': {
                        //templateUrl: basePath + 'jurisdictionList.html',
                        templateUrl: basePath + 'employeelist.html',
                        controller: 'EmployeeListCtrl'
                        }
                    }
            })

            //权限管理-添加员工
            .state('main.system.employeelist.employeeadd', {
                    url: '/employeeadd',
                    module: 'private',  //需要授权的
                    views: {
                        'add_staff@main.system': {
                        //templateUrl: basePath + 'add_staff.html',
                        templateUrl: basePath + 'employeeadd.html',
                        controller: 'EmployeeAddCtrl'
                        }
                    }
            })

 /*           //权限管理-添加员工-权限设置
            .state('main.system.employeelist.employeeadd.employeepermission', {
                url: '/employeepermission/:u_id',
                module: 'private',  //需要授权的
                views: {
                    'add_staff@main.system': {
                        //templateUrl: basePath + 'jurisdiction_setting.html',
                        templateUrl: basePath + 'employeepermission.html',
                        controller: 'EmployeePermissionCtrl'
                    }
                }
            })*/

            //权限管理-编辑员工
            .state('main.system.employeelist.employeeedit', {
                url: '/employeeedit/:u_id',
                module: 'private',  //需要授权的
                views: {
                    'add_staff@main.system': {
                        //templateUrl: basePath + 'add_staff.html',
                        templateUrl: basePath + 'employeeedit.html',
                        controller: 'EmployeeEditCtrl'
                    }
                }
            })

    /*        //权限管理-编辑员工-权限设置
            .state('main.system.employeelist.employeeedit.employeepermission', {
                url: '/employeepermission/:u_id',
                module: 'private',  //需要授权的
                views: {
                    'add_staff@main.system': {
                        //templateUrl: basePath + 'jurisdiction_setting.html',
                        templateUrl: basePath + 'employeepermission.html',
                        controller: 'EmployeePermissionCtrl'
                    }
                }
            })*/

            //员工管理-权限设置
            .state('main.system.employeelist.employeepermission', {
                    url: '/employeepermission/:u_id/:is_create',
                    module: 'private',  //需要授权的
                    views: {
                        //'jurisdiction_setting@main.system'
                        'add_staff@main.system': {
                        //templateUrl: basePath + 'jurisdiction_setting.html',
                        templateUrl: basePath + 'employeepermission.html',
                        controller: 'EmployeePermissionCtrl'
                        }
                    }
            })

            //控制器动作列表页
            .state('main.system.controllerList', { 
                    url: '/controllerList',
                    module: 'private',  //需要授权的
                    views: {
                        'controllerList@main.system': {
                        templateUrl: basePath + 'controllerList.html',
                        controller: 'actionCtrl'
                        }
                    
                    }
            })
            //角色-列表页
            .state('main.system.roleList', { 
                    url: '/roleList',
                    module: 'private',  //需要授权的
                    views: {
                        'roleList@main.system': {
                        templateUrl: basePath + 'roleList.html',
                        controller: 'rolesCtrl'
                        }
                    
                    }
            })
            //添加角色
            .state('main.system.roleList.add_role', { 
                    url: '/add_role',
                    module: 'private',  //需要授权的
                    views: {
                        'add_role@main.system': {
                        templateUrl: basePath + 'add_role.html',
                        controller: 'rolesAddCtrl'
                        }
                    
                    }
            })
            //编辑角色
            .state('main.system.roleList.edit_role', { 
                    url: '/edit_role/:group_id',
                    module: 'private',  //需要授权的
                    views: {
                        'edit_role@main.system': {
                        templateUrl: basePath + 'edit_role.html',
                        controller: 'rolesEditeCtrl'
                        }
                    
                    }
            })
            //技能管理
            .state('main.system.skillList', { 
                    url: '/skillList',
                    module: 'private',  //需要授权的
                    views: {
                        'skillList@main.system': {
                        templateUrl: basePath + 'skillList.html',
                        controller: 'skillCtrl'
                        }
                    }
            })
            //技能等级设置
            .state('main.system.gradeSettingList', { 
                    url: '/gradeSettingList',
                    module: 'private',  //需要授权的
                    views: {
                        'gradeSettingList@main.system': {
                        templateUrl: basePath + 'gradeSettingList.html',
                        controller: 'skillLevelLevelCtrl'
                        }
                    }
            })
            //个人纳米币管理列表页
            .state('main.system.personalNanoCoinList', { 
                    url: '/personalNanoCoinList',
                    module: 'private',  //需要授权的
                    views: {
                        'personalNanoCoinList@main.system': {
                        templateUrl: basePath + 'personalNanoCoinList.html',
                        controller: 'scoreCtrl'
                        }
                    
                    }
            })
            
            //纳米币管理查看详情
            .state('main.system.personalNanoCoinList.examine_nano_details', { 
                    url: '/examine_nano_details/:search_id/:type',
                    module: 'private',  //需要授权的
                    views: {
                        'examine_nano_details@main.system': {
                        templateUrl: basePath + 'examine_nano_details.html',
                        controller: 'scoreLogCtrl'
                        }
                    
                    }
            })
            
            //部门纳米币管理列表页
            .state('main.system.departmentNanoCoinList', { 
                    url: '/departmentNanoCoinList',
                    module: 'private',  //需要授权的
                    views: {
                        'departmentNanoCoinList@main.system': {
                        templateUrl: basePath + 'departmentNanoCoinList.html',
                        controller: 'groupScoreCtrl'
                        }
                    }
            })
            //部门纳米币管理查看详情
                .state('main.system.departmentNanoCoinList.examine_nano_details', {
                    url: '/examine_nano_details/:search_id/:type',
                    module: 'private',  //需要授权的
                    views: {
                        'examine_nano_details@main.system': {
                            templateUrl: basePath + 'examine_nano_details.html',
                            controller: 'scoreLogCtrl'
                        }

                    }
                })
            //个人纳米币设置
            .state('main.system.personalNanoCoinSet', { 
                    url: '/personalNanoCoinSet',
                    module: 'private',  //需要授权的
                    views: {
                        'personalNanoCoinSet@main.system': {
                        templateUrl: basePath + 'personalNanoCoinSet.html',
                        controller: 'scoreCronCtrl'
                        }
                    
                    }
            })
            //工作日设置
            .state('main.system.workdaySet', { 
                    url: '/workdaySet',
                    module: 'private',  //需要授权的
                    views: {
                        'workdaySet@main.system': {
                        templateUrl: basePath + 'workdaySet.html'
//                      controller: 'WorkdaySetCtrl'
                        }
                    
                    }
            })
}]);