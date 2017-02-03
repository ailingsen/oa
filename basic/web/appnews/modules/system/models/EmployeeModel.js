systemMod.factory('employeeModel', function($http,$timeout,$cookieStore,$cookies,$state,filtersModel){
    var  service={};

    //获取员工列表数据
    service.getEmployeeList = function ($scope) {
        $http.post('/index.php?r=management/employee/list',JSON.stringify($scope.employee_param))
            .success(function (data) {
                if(data.code == 1){
                    $scope.employee.memList = data.data.memList;
                    $scope.page.curPage = data.data.page.curPage;
                    $scope.page.sumPage = data.data.page.sumPage;
                }
            });
    };

    //获取所有的角色
    service.getAllPerm = function ($scope,type) {
        $http.post('/index.php?r=management/employee/allperm',{})
            .success(function (data) {
                if(data.code == 1){
                    $scope.employee.allPerm = data.data;
                    if(type){//员工管理列表查询
                        var temp=[];
                        temp['group_id'] = 0;
                        temp['group_name'] = '角色';
                        $scope.employee.allPerm.unshift(temp);
                    }else{//员工权限设置界面
                        $scope.employee.allPerm.push({group_id:0,group_name:'无',permission:"[]"})
                        angular.element.each($scope.employee.allPerm, function (key, val) {
                            $scope.employee.allPerm[key].arrpermission = eval('(' + val.permission + ')');
                        });
                    }
                }
            });
    };

    //添加员工
    service.addEmployee = function ($scope) {
        $http.post('/index.php?r=management/employee/save-emp-info',JSON.stringify($scope.employee_param))
            .success(function (data) {
                if(data.code == 1){
                    if($scope.employee.isSavePer){//保存并设置权限
                        if($scope.employee_param.type == 'edit'){
                            $state.go('main.system.employeelist.employeepermission',{u_id:data.data.u_id,is_create:false});
                        }else{
                            $state.go('main.system.employeelist.employeepermission',{u_id:data.data.u_id,is_create:true});
                        }
                    }else{
                        $state.go('main.system.employeelist',{isInit:0},{reload:true});
                        alert(data.msg);
                    }
                }else{
                    alert(data.msg);
                }
            });
    };

    //获取员工信息
    service.getEmpInfo = function ($scope) {
        $http.post('/index.php?r=management/employee/emp-info',{u_id:$scope.employee_param.u_id})
            .success(function (data) {
                if(data.code == 1){
                    $scope.employee_param.real_name = data.data.real_name;
                    $scope.employee_param.email = data.data.email;
                    $scope.employee_param.position = data.data.position;
                    $scope.employee_param.entry_time = data.data.entry_time;
                    $scope.employee_param.is_formal = data.data.is_formal;
                    $scope.employee_param.org_id = data.data.org_id;
                    $scope.employee_param.is_manager = data.data.is_manager;
                    $scope.employee_param.card_no = data.data.card_no;
                    $scope.employee_param.phone = data.data.phone;
                    $scope.employee_param.resumeId = data.data.resumeId;
                    $scope.attend.search_org_name=data.data.org_name;
                    $scope.attend.search_org_name_temp=data.data.org_name;
                    $scope.employee.pwd = '!!!******!!!';
                    $scope.depart = {value:data.data.org_id,label:data.data.org_name};
                    console.log($scope.depart);
                    //获取部门管理员信息
                    service.getOrgManagerInfo($scope);
                }else{
                    alert(data.msg);
                }
            });
    };

    //获取所有权限
    service.getAllPermission = function ($scope) {
        $http.post('/index.php?r=management/employee/all-permission',{u_id:$scope.employee_param.u_id,is_create:$scope.employee.is_create})
            .success(function (data) {
                if(data.code == 1){
                    $scope.employee.allPermission = data.data.permissionList;
                    $scope.employee.memInfo = data.data.memInfo;
                }else{
                    alert(data.msg);
                }
            });
    };

    //删除员工
    service.delEmp = function($scope){
        $http.post('/index.php?r=management/employee/del-emp',{u_id:$scope.employee.u_id})
            .success(function (data) {
                if(data.code == 1){
                    $scope.employee.u_id = '';
                    service.getEmployeeList($scope);
                    alert(data.msg);
                }else{
                    $state.go('main.system.employeelist',{isInit:0},{reload:true});
                    alert(data.msg);
                }
            });
    }

    //保存员工权限和角色信息
    service.savePermission = function($scope){
        $http.post('/index.php?r=management/employee/save-user-perm',JSON.stringify($scope.employee_param))
            .success(function (data) {
                if(data.code == 1){
                    $state.go('main.system.employeelist',{isInit:0},{reload:true});
                    alert(data.msg);
                }else{
                    alert(data.msg);
                }
            });
    }

    //员工管理列表参数保存
    service.setMyCreateListCookie=function($scope){
        var MyCreateList = {};
        MyCreateList.page = $scope.employee_param.page;
        MyCreateList.search_org_name = $scope.employee.org_name;
        MyCreateList.org_id = $scope.employee_param.org_id;
        MyCreateList.real_name = $scope.employee_param.real_name;
        MyCreateList.perm_gourp_id = $scope.employee_param.perm_gourp_id;
        MyCreateList.role_name = $scope.employee.role_name;
        $cookieStore.put('EmployeeList',MyCreateList);
    }

    //员工管理列表参数保存
    service.setInitMyCreateListCookie=function($scope){
        var MyCreateList = {};
        MyCreateList.page = 1;
        MyCreateList.search_org_name = '';
        MyCreateList.org_id = '';
        MyCreateList.real_name = '';
        MyCreateList.perm_gourp_id = '';
        MyCreateList.role_name = '角色';
        $cookieStore.put('EmployeeList',MyCreateList);
    }

    //根据部门ID获取部门负责人信息
    service.getOrgManagerInfo = function($scope){
        $http.post('/index.php?r=management/employee/org-manager-info',{org_id:$scope.employee_param.org_id})
            .success(function (data) {
                if(data.code == 1){
                    $scope.employee.isSetManager = true;
                    $scope.employee.manager_real_name = data.managerInfo.real_name;
                }else{
                    $scope.employee.isSetManager = false;
                    $scope.employee.manager_real_name = '';
                }
            });
    }


    return service;
});
