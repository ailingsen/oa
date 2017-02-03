//员工管理列表页
systemMod.controller('EmployeeListCtrl',function($scope,$http,$rootScope,$timeout,employeeModel,attendanceModel,$cookieStore,$stateParams,$state){
    var employee=$scope.employee = {};
    var employee_param=$scope.employee_param = {};
    var attend = $scope.attend = {};

    //参数
    employee.perm_gourp_id = '';//角色ID
    employee.org_id = '';//部门ID
    employee.real_name = ''//员工姓名
    //员工列表数据
    employee.memList = '';
    //所有角色
    employee.allPerm = '';
    //所有组
    attend.orgInfo = '';
    //保存选中的角色名称
    employee.role_name = '角色';
    //保存选中的部门名称
    employee.org_name = '';
    //部门查询
    attend.search_org_name='';
    attend.search_org_name_temp='';
    //是否显示删除确认框
    employee.isDelConfirmWin = false;
    //要删除的用户ID
    employee.u_id = '';
    //是否显示角色下拉框
    employee.isRoleWin = false;
    //是否显示部门下拉列表
    attend.isOrgWin = false;

    //查询参数
    employee_param.perm_gourp_id = '';//角色ID
    employee_param.org_id = '';//部门ID
    employee_param.real_name = '';//员工姓名
    employee_param.page = 1;//当前页数

    //翻页对象
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };

   employee.isInit = $stateParams.isInit ? $stateParams.isInit : 1;
    if(employee.isInit==0 && typeof($cookieStore.get('EmployeeList')) != "undefined"){
        var MyCreateListCookie = $cookieStore.get('EmployeeList');
        //初始化页数
        employee_param.page = MyCreateListCookie.page ? MyCreateListCookie.page : 1;
        $scope.page.tempcurPage = employee_param.page;

        attend.search_org_name=MyCreateListCookie.search_org_name;
        attend.search_org_name_temp=attend.search_org_name;
        employee.org_name = attend.search_org_name;
        employee_param.org_id = MyCreateListCookie.org_id;
        employee.org_id =employee_param.org_id;
        $scope.depart = {value:MyCreateListCookie.org_id,label:MyCreateListCookie.search_org_name};

        employee.real_name = MyCreateListCookie.real_name;
        employee_param.real_name = MyCreateListCookie.real_name;

        employee_param.perm_gourp_id = MyCreateListCookie.perm_gourp_id;
        employee.perm_gourp_id = employee_param.perm_gourp_id;
        employee.role_name = MyCreateListCookie.role_name;
        angular.element('#role').html(employee.role_name);
    }
    employeeModel.setInitMyCreateListCookie($scope);

    //获取所有角色
    employeeModel.getAllPerm($scope,true);

    //获取员工列表数据
    employeeModel.getEmployeeList($scope);

    //查询员工
    employee.searchBtn = function(){
        if(employee.real_name.length>20){
            alert('员工姓名最长支持20个字');
            return false;
        }
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };
        employee_param.perm_gourp_id = employee.perm_gourp_id;//角色ID
        employee_param.org_id = employee.org_id;//部门ID
        employee_param.real_name = employee.real_name;//员工姓名
        employee_param.page = 1;//当前页数
        employeeModel.getEmployeeList($scope);
    }

    //显示角色下拉框
    employee.roleWinButton = function () {
//      employee.isRoleWin = !employee.isRoleWin;
		if(angular.element("#roleList").is(":hidden")){
            angular.element("#roleList").show();
        }else{
            angular.element("#roleList").hide();
        }
    }

    //设置选中的角色
    employee.selectSearchRole = function (perm_gourp_id) {
        employee.perm_gourp_id = perm_gourp_id;
        angular.element.each(employee.allPerm, function (key, val) {
            if(val.group_id==perm_gourp_id){
                employee.role_name = val.group_name;
                angular.element('#role').html(val.group_name);
            }
        });
        employee.isRoleWin = false;
         $("#roleList").hide();
    }

    //获取所有搜索部门
    attend.getSearchAllOrgInfo = function(){
        attend.search_org_name='';
        employee.org_id = '';
        attendanceModel.getOrgInfo($scope,1);
    }

    //获取搜索部门数据
    attend.searchOrgInfo = function () {
        if(attend.search_org_name != attend.search_org_name_temp){
            attendanceModel.getOrgInfo($scope,0);
            attend.search_org_name_temp = attend.search_org_name;
        }
    }

    //保存选中的查询部门ID
    attend.selectOrg = function (obj) {
        employee.org_id = obj.value;
        attend.search_org_name = obj.label;
        employee.org_name = obj.label;
        attend.isOrgWin = false;
    }

    //显示删除确认框
    employee.openDelEmpWin = function(u_id){
        employee.u_id = u_id;
        employee.isDelConfirmWin = true;
        $('#masklayer1').show();
    }

    //关闭删除确认框
    employee.closeDelEmpWin = function(){
        employee.u_id = '';
        employee.isDelConfirmWin = false;
        $('#masklayer1').hide();
    }

    //删除员工
    employee.delEmp = function(){
        employee.isDelConfirmWin = false;
        employeeModel.delEmp($scope);
        $('#masklayer1').hide();
    }

    //翻页方法
    $scope.page_fun = function () {
        $scope.employee_param.page = $scope.page.tempcurPage;
        employeeModel.getEmployeeList($scope);
    };

    //跳转到添加用户
    employee.addEmp = function(){
        employeeModel.setMyCreateListCookie($scope);
        $state.go('main.system.employeelist.employeeadd');
        $('#masklayer1').show();
    }

    //跳转到编辑页
    employee.editEmp = function(u_id){
        employeeModel.setMyCreateListCookie($scope);
        $state.go('main.system.employeelist.employeeedit',{u_id:u_id});
        $('#masklayer1').show();
    }

    //跳转到权限设置按钮
    employee.setPerm = function(u_id){
        employeeModel.setMyCreateListCookie($scope);
        $state.go('main.system.employeelist.employeepermission',{u_id:u_id,is_create:false});
        $('#masklayer1').show();
    }
    //点击隐藏
 	angular.element(document).bind("click",function(event){
        if(angular.element(event.target).parents(".selectbor ").length==0){
            angular.element(".selectbor  ul").hide();
        } 
    });
});



