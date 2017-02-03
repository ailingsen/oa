//员工管理列表页
systemMod.controller('EmployeePermissionCtrl',function($scope,$http,$rootScope,$timeout,employeeModel,$state,$stateParams){
    var employee = $scope.employee = {};
    var employee_param = $scope.employee_param = {};
    var roles = $scope.roles ={};

    employee_param.u_id = $stateParams.u_id ? $stateParams.u_id : 0;
    employee.is_create = $stateParams.is_create ? $stateParams.is_create : false;
    if(employee_param.u_id==0){
        alert("参数错误");
        $window.history.back();
    }

    //保存所有权限信息
    employee.allPermission = '';
    //保存用户信息
    employee.memInfo = '';
    //保存用户信息
    employee.allPerm = '';
    //保存已选权限提交参数
    employee_param.userPermission = '';
    //保存用户的角色参数
    employee_param.perm_groupid = '';
    //保存用户选中的角色权限
    employee.permissionstr = '';
    //已选权限
    roles.selected = [];
    //保存用户基本信息

    //获取所有权限信息
    employeeModel.getAllPermission($scope);
    //获取所有的角色
    //获取所有角色
    employeeModel.getAllPerm($scope,false);

    //判断选中
    var getSelected = function(){
        roles.selected=[];
        angular.element('.jurisdiction_table_box').find(':checkbox').each(function(){
            if($(this).prop("checked")==true){
                roles.selected.push($(this).attr('pid'));
            }
        });

    };

    var isSelectedAll = function(checkbox){
        var c=false;
        roles.selected=[];
        angular.element(checkbox).parents('.jurisdiction_table').find('.td_one').find(':checkbox').each(function(){
            if($(this).prop("checked")==true){
                c=true;
            }
        });

        if (c) {
            angular.element(checkbox).parents('.jurisdiction_table').find(".th_one").find(':checkbox').prop("checked", true);
        } else {
            angular.element(checkbox).parents('.jurisdiction_table').find(".th_one").find(':checkbox').prop("checked", false);
        }

    };

    //选择一级
    roles.selectAllPerm=function($event){
        var checkbox = $event.target;
        if(checkbox.checked){
            angular.element(checkbox).parents('.jurisdiction_table').find(".td_one").find(':checkbox').prop("checked", true);
            angular.element(checkbox).parents('.jurisdiction_table').find(".td_two").find(':checkbox').prop("checked", true);
        }else{
            angular.element(checkbox).parents('.jurisdiction_table').find(".td_one").find(':checkbox').prop("checked", false);
            angular.element(checkbox).parents('.jurisdiction_table').find(".td_two").find(':checkbox').prop("checked", false);
        }
    };
    //选择二级
    roles.selectAllPerm2=function($event){
        var checkbox = $event.target;
        if(checkbox.checked){
            angular.element(checkbox).parent().parent().parent().find(".td_two").find(':checkbox').prop("checked", true);
        }else{
            angular.element(checkbox).parent().parent().parent().find(".td_two").find(':checkbox').prop("checked", false);
        }
        isSelectedAll(checkbox);
    };
    //选中三级
    roles.updateSelection = function ($event, id) {
        var checkbox = $event.target;
        if (checkbox.checked) {
            angular.element(checkbox).parents('.jurisdiction_table').find(".th_one").find(':checkbox').prop("checked", checkbox.checked);
            angular.element(checkbox).parent().parent().parent().find(".td_one").find(':checkbox').prop("checked", checkbox.checked);
        }
    };

    //选择角色
    employee.selectRole = function(){
        roles.selected = [];
        angular.element.each($scope.employee.allPerm, function (key, val) {
            if(val.group_id==employee.memInfo.perm_groupid){
                roles.selected = $scope.employee.allPerm[key].arrpermission;
                employee.permissionstr = $scope.employee.allPerm[key].permission;
            }
        });
        getArray(employee.allPermission);
    }

    function getArray(data)
    {
        for (var i in data) {
            if(employee.permissionstr.indexOf('"'+data[i].pid+'"') > 0){
                data[i].is_selected = true;
            }else{
                data[i].is_selected = false;
            }
            getArray(data[i].children);
        }
    }

    //保存用户权限
    employee.saveBtn = function(){
        getSelected();
        employee_param.userPermission = roles.selected;
        employee_param.perm_groupid = employee.memInfo.perm_groupid
        employeeModel.savePermission($scope);
    }

    //取消
    employee.cancelBtn = function(){
        $state.go('main.system.employeelist',{isInit:0},{reload:true});
    }

});



