TaskMod.factory('taskModel', function($http,$timeout,$cookieStore,$cookies,$state,filtersModel, util){
    var  service={};
    //获取所有项目, 并初始化数据
    service.getProject = function ($scope, page) {
        $http.post('/index.php?r=task/task/getmyproject',{'type':5, 'public':2, 'page':page}).success(function (data) {
            $scope.task.allProject = data.data.proList;
            //设置第一个选项
            // $scope.task.allProject.unshift({'pro_name':'无', 'pro_id':''});
            //初始化所选项目
            if($scope.task.pro_id) {
                for (var i = 0; i < data.data.proList.length; i++) {
                    if($scope.task.pro_id == data.data.proList[i].pro_id) {
                        $scope.task.taskProjectSelect = data.data.proList[i];
                        break;
                    }
                }
                if($scope.task.taskProjectSelect == '') {
                    $scope.task.allProject.push({'pro_name':$scope.task.pro_name, 'pro_id':$scope.task.pro_id});
                    $scope.task.taskProjectSelect =  $scope.task.allProject[$scope.task.allProject.length - 1];
                }
            }else{

            }

            //创建任务设置初始项目
            if($scope.task.init_pro_id>0){
                for(var i=0; i<$scope.task.allProject.length; i++){
                    if($scope.task.allProject[i].pro_id==$scope.task.init_pro_id){
                        $scope.task.taskProjectSelect = $scope.task.allProject[i];
                        break;
                    }
                }
            }
        });
    };
    //获取所有部门, 并初始化数据
    service.getAllGroupTree = function ($scope) {
        $http.post('/index.php?r=workmate/workmate/all-group', {'selected':$scope.task.selectedGroup}).success(function (data) {
            $scope.task.allGroupsTree = data.data;
            //一级组织
            angular.forEach($scope.task.allGroupsTree, function (group1, groupKey1) {
                    $scope.task.allGroupsTree[groupKey1].show = 0;
                    // angular.forEach($scope.task.selecteSkill, function (select1, key1) {
                    //     if (select1.org_id == group1.org_id) {
                    //         $scope.task.allGroupsTree[groupKey1].is_select = true;
                    //     }
                    // });
                    // //二级组织
                    // if ($scope.task.allGroupsTree[groupKey1].children != undefined && $scope.task.allGroupsTree[groupKey1].children.length > 0) {
                    //     angular.forEach($scope.task.allGroupsTree[groupKey1].children, function (group2, groupKey2) {
                    //         angular.forEach($scope.task.selecteSkill, function (select2, key2) {
                    //             if (select2.org_id == group2.org_id) {
                    //                 $scope.task.allGroupsTree[groupKey1]['children'][key].is_select = true;
                    //             }
                    //         });
                    //         //三级组织
                    //         if (data.data[i].children[key] != undefined && data.data[i].children.length > 0) {
                    //             angular.forEach(data.data[i].children[key].children, function (group3, groupKey3) {
                    //                 angular.forEach($scope.task.selecteSkill, function (select3, key3) {
                    //                     if (value.org_id == group.org_id) {
                    //                         $scope.task.allGroupsTree[groupKey1]['children'][groupKey3]['children'][groupKey3].is_select = true;
                    //                     }
                    //                 });
                    //             });
                    //         }
                    //     });
                    // }
                });
            $scope.task.allGroupsTree[0].show = 1;
        });

    };
    //获取成员列表
    service.getMembers = function ($scope, search) {
        $scope.$broadcast('loadOpt', 'block');
        var issearch = 0;
        if (search) issearch = 1;
        $http.post('/index.php?r=task/task/allgroupmember',{'search' : search, 'lately':$cookieStore.get('lately')}).success(function (data, status, headers, config) {
            
            data.data = util.setImgRand(data.data);

            $scope.task.groupMembers = data.data;

            if (data.data.length == 0) {
                $scope.is_member = false;
            } else {
                $scope.is_member = true;
            }
            if (issearch != 1) {
                $scope.searchMemberRealName = '';
            }
        });
    }
    service.getAllSkillList = function ($scope) {
        $http.get('/index.php?r=management/skill/skilllist').success(function(data, status) {
            $scope.task.skillList = data.data;
            for (var i = 0; i < data.data.length; i++) {
                $scope.task.skillList[i].show = 1;
                angular.forEach(data.data[i].children, function(skill, key) {
                    angular.forEach($scope.task.selecteSkill, function (value) {
                        if (value.skill_id == skill.skill_id && $scope.task.skillList[i]['children'][key] != undefined) {
                            $scope.task.skillList[i]['children'][key].is_select = true;
                        }
                    });
                });
            }
        });
    };

    //悬赏任务列表
    service.getOfferTaskList=function($rootScope,order,title,page,task_status,$scope){
        $http.post('/index.php?r=tasklist/offertasklist',{order:order,title:title,page:page,task_status:task_status}).success(function(data, status) {

            for (var i = 0; i < data.data.length; i++) {
                $rootScope.OfferTask.push(data.data[i]);
            };

            $scope.loadTips=false;
        });
    };

    service.getTask = function ($scope,projectId,name) {
        $scope.$broadcast('loadOpt', 'block');
        $http.get('/index.php?r=task/gettask&projectid='+projectId+'&name='+encodeURI(name)).success(function (data) {
            $scope.mainTask.allTask = data.data;
            $scope.$broadcast('loadOpt', 'none');
        });
    }
    
    //获取任务责任人
    service.getOwner=function(taskId,$scope){
        $http.get('/index.php?r=tasklist/get-task-member&task_id='+taskId).success(function (data) {
            $scope.charger_name = data.real_name;
            $scope.charger_uid = data.u_id;
        });
    }
    //获取创建人
    service.getCreateUser=function(uid,$scope){
        $http.get('/index.php?r=members/userinfo&uid='+uid).success(function (data) {
            $scope.CreateUser = data.items;
        });
    }

    //获取任务详情
    service.getTaskDetail = function($scope,task_id,type,$state) {
        $http.get('/index.php?r=task/task/task-detail&taskId='+task_id+'&type='+type).success(function (data) {
            if(data.code != 20000){
                alert(data.msg);
                $state.go('^');
                return false;
            }
            $scope.task = data.data;
            $scope.tempPoint = parseInt(data.data.point);
            $scope.task.temp_task_type = data.temp_task_type;
           //$scope.task.selecteSkill = data.data.selecteSkill;
            //初始化项目下拉框
            if($scope.task.pro_id) {
                $scope.task.taskProjectSelect = {'pro_name': $scope.task.pro_name, 'pro_id': $scope.task.pro_id};
            }
            service.getProject($scope, 1);


            $scope.task.taskType = [
                {label: '指派任务', nums:1},
                {label: '悬赏任务', nums:2}
            ];
            if($scope.task.task_type == 1){
                $scope.task.taskTypeDefaultSelect = $scope.task.taskType[0];
            }else{
                $scope.task.taskTypeDefaultSelect = $scope.task.taskType[1];
            }
            //任务级别
            $scope.task.taskLevel = [
                {label: '低', nums:3},
                {label: '中', nums:2},
                {label: '高', nums:1}
            ];
            //默认是低级别
            switch($scope.task.task_level){
                case '1':
                    $scope.task.levelDefaultSelect = $scope.task.taskLevel[2];
                    break;
                case '2':
                    $scope.task.levelDefaultSelect = $scope.task.taskLevel[1];
                    break;
                case '3':
                    $scope.task.levelDefaultSelect = $scope.task.taskLevel[0];
                    break;
            }

            //指派人
            $scope.task.selectedMember = {'u_id':$scope.task.charger,'real_name':$scope.task.real_name,'head_img':$scope.task.head_img};
            
            
            $scope.task.groupMembers=[];
            $scope.task.taskProjectSelect = '';
            $scope.task.skillList = [];
            //悬赏范围
            $scope.task.allGroupsTree = [];
            $scope.task.isSkillModified = false;
            $scope.task.isGroupModified = false;

            //任务内容
            if ($scope.task.task_desc == undefined)
                $scope.task.task_desc = '';
        });
    }

    service.getLeavePoints=function($scope){
        $http.get('/index.php?r=task/task/get-leave-points').success(function (data) {
            $scope.task.leavePoints = data.data;
        });
    }

    return service;
});
