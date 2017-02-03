var workStatementMod=angular.module('workStatementMod',[]);
//我的工作报告
workStatementMod.controller('workStatementCtrl',function($scope,$http,$rootScope,workModel,$timeout){
    var work = $scope.work = {};
    var param_work = $scope.param_work = {};
    //翻页对象
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    //查询部门ID
    work.search_org_id = '';
    //查询用户ID
    work.search_u_id = '';
    //查询开始时间
    work.begin_time = '';
    //查询结束时间
    work.end_time = '';
    //查询部门
    work.search_org_name = '';
    //查询部门临时数据
    work.search_org_name_temp = '';
    //查询用户
    work.search_real_name = '';
    //查询用户临时数据
    work.search_real_name_temp = ''
    //是否显示状态下拉列表
    work.isOrgWin = false;
    //是否显示类型下拉列表
    work.isMemWin = false;

    //提交参数
    //查询部门ID
    param_work.search_org_id = '';
    //查询用户ID
    param_work.search_u_id = '';
    //查询开始时间
    param_work.begin_time = '';
    //查询结束时间
    param_work.end_time = '';
    //当前页
    param_work.page = 1;

    work.workList = [];
    //搜索部门数据
    work.orgInfo = [];
    //搜索用户数据
    work.userInfo = [];

    //获取员工考勤
    workModel.getAllAttend($scope);

    //查询按钮
    work.searchButton = function () {
        //是否显示部门下拉列表
        work.isOrgWin = false;
        //是否显示人员下拉列表
        work.isMemWin = false;
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };
        param_work.page = 1;
        if(work.search_org_id == ''){
            work.search_org_name = '';
            work.search_org_name_temp = '';
        }
        if(work.search_u_id == ''){
            work.search_real_name = '';
            work.search_real_name_temp = ''
        }
        if ( work.end_time && work.start_time >= work.end_time) {
            alert('结束时间必须大于开始时间');
        }
        param_work.search_org_id = work.search_org_id;
        //查询用户ID
        param_work.search_u_id = work.search_u_id;
        //查询开始时间
        param_work.begin_time = angular.element('#searchstarttime').val();
        //查询结束时间
        param_work.end_time = angular.element('#searchendtime').val();
        workModel.getAllAttend($scope);
    };
    //获取所有搜索部门
    work.getSearchAllOrgInfo = function(){
        work.search_org_name='';
        work.search_org_id = '';
        work.isMemWin = false;
        workModel.getOrgInfo($scope);
    }

    //获取搜索部门数据
    work.searchOrgInfo = function () {
        work.isMemWin = false;
        if(work.search_org_name != work.search_org_name_temp){
            workModel.getOrgInfo($scope);
            work.search_org_name_temp = work.search_org_name;
        }
    }

    //保存选中的查询部门ID
    work.selectOrg = function (obj) {
        work.search_org_id = obj.org_id;
        work.search_org_name = obj.org_name
        work.isOrgWin = false;
    }

    //获取所有搜索用户数据
    work.searchAllUserInfo = function () {
        work.isOrgWin = false;
        work.search_u_id = '';
        work.search_real_name = '';
        workModel.getUserInfo($scope);
    }

    //获取搜索用户数据
    work.searchUserInfo = function () {
        work.isOrgWin = false;
        if(work.search_real_name != work.search_real_name_temp){
            workModel.getUserInfo($scope);
            work.search_real_name_temp = work.search_real_name;
        }
    }

    //保存选中的查询用户ID
    work.selectUser = function (obj) {
        work.search_u_id = obj.u_id;
        work.search_real_name = obj.real_name;
        work.isMemWin = false;
    }

    //翻页方法
    $scope.page_fun = function () {
        $scope.param_work.page = $scope.page.tempcurPage;
        workModel.getAllAttend($scope);
    };

});



