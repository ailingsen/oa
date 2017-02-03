//角色
systemMod.controller('rolesCtrl', function ($scope,$stateParams, $rootScope, $http, $cookieStore,$cookies,$state,rolesModel) {
    var roles = $scope.roles = {};
    var roles_param = $scope.roles_param = {};
    roles_param.page = 1;
    roles.rolesList = [];
    
    //确认删除
    roles.isDeleWin = false;

    //翻页对象
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    rolesModel.getAllRoles($scope);

    //删除
    roles.del = function(gitem){
        roles_param.gid = gitem.group_id;
        roles_param.group_name = gitem.group_name;

        roles.isDeleWin = true;
        $('#masklayer1').show();
    }
    //取消
    roles.hide=function(){
    	roles.isDeleWin=false;
    	$('#masklayer1').hide();
    }

    //确认删除
    roles.delRole = function(){
        $http.post('/index.php?r=permission/permissiongroup/del', {group_id:roles_param.gid})
            .success(function(data,status) {
                if (data.code==20000) {
                    roles.isDeleWin = false;
                    $('#masklayer1').hide();
                    rolesModel.getAllRoles($scope);
                } else {
                    alert(data.msg);
                }
            })
    };
    
    //翻页方法
    $scope.page_fun = function () {
        $scope.roles_param.page = $scope.page.tempcurPage;
        rolesModel.getAllRoles($scope);
    };

});
