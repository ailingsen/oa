//角色
systemMod.controller('rolesEditeCtrl', function ($scope,$stateParams, $rootScope, $http, $cookieStore,$cookies,$state,rolesModel) {
    var roles_param = $scope.roles_param = {};
    var roles = $scope.roles = {};

    var permission = $scope.permission = {};
    permission.permissionList = [];//权限列表
    roles.selected = [];//已选权限
    roles_param.group_name = '';
    roles_param.group_id = $stateParams.group_id;

    $('#masklayer1').show();
    rolesModel.getAllPermission($scope);


    //新增
    roles.editeRoles =function(){
        getSelected();
        roles_param.permission = roles.selected;
        rolesModel.editeRoles($scope, $state);
    };

    //选择框

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
    roles.updateSelection = function ($event) {
        var checkbox = $event.target;
        if (checkbox.checked) {
            angular.element(checkbox).parents('.jurisdiction_table').find(".th_one").find(':checkbox').prop("checked", checkbox.checked);
            angular.element(checkbox).parent().parent().parent().find(".td_one").find(':checkbox').prop("checked", checkbox.checked);
        }
    };

});
