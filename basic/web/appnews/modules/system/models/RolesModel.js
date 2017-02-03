
systemMod.factory('rolesModel',function($http){
    var  service={};

    //获取 角色 数据
    service.getAllRoles = function($scope){
        $http.post('/index.php?r=permission/permissiongroup/group-list', JSON.stringify($scope.roles_param))
            .success(function(data,status) {
                if(data.code==20000){
                    $scope.roles.rolesList = data.data.permission_group;
                    $scope.page.curPage =  data.data.page;
                    $scope.page.sumPage = data.data.totalPage;
                }else{
                    alert(data.msg);
                }
            })
    }

    //添加 角色 数据
    service.addRoles = function($scope, $state){
        $http.post('/index.php?r=permission/permissiongroup/add', JSON.stringify($scope.roles_param))
            .success(function(data,status) {
                if(data.code==20000){
                    $state.go('main.system.roleList',{},{reload:true});
                }else{
                    alert(data.msg);
                }
            })
    }
    service.editeRoles = function($scope, $state){
        $http.post('/index.php?r=permission/permissiongroup/edite', JSON.stringify($scope.roles_param))
            .success(function(data,status) {
                if(data.code==20000){
                    $state.go('main.system.roleList',{},{reload:true});
                }else{
                    alert(data.msg);
                }
            })
    }

    //获取所有权限数据
    service.getAllPermission = function($scope){
        $http.post('/index.php?r=permission/permissiongroup/permissionlist', JSON.stringify($scope.roles_param))
            .success(function(data,status) {
                if(data.code==20000){
                    $scope.permission.permissionList = data.data.permission;
                    $scope.roles_param.group_name = data.data.group_name;
                }
            })
    }
    
    return service;
})