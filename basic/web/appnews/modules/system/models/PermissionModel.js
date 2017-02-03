
systemMod.factory('permissionModel',function($http){
    var  service={};

    //获取所有权限数据
    service.getAllPermission = function($scope){
        $http.post('/index.php?r=permission/permission/permissionlist', JSON.stringify($scope.permission_param))
            .success(function(data,status) {
                if(data.code==20000){
                    $scope.permission.permissionList = data.data;
                }
            })
    }


    return service;
})