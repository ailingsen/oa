// var workStatementMod=angular.module('workStatementMod',[]);
//工作报告审阅
workStatementMod.controller('approveWorkCtrl',function($scope,$http,$rootScope,workModel,$stateParams,$state,permissionService){
    if (!permissionService.checkPermission('WorkstateApprove')) {
        $state.go('main.index');
        return false;
    }
    var work = $scope.work = {};
    var param_work = $scope.param_work = {};
    work.work_detail = {};
    //翻页对象
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    work.user = {'u_id':'', 'real_name':'提交者'};
    work.search_real_name_temp = '';
    work.search_real_name = '';
    //查询状态
    work.status = {status:'',statusstr:'状态'};
    //查询类型
    work.type = {status:'',statusstr:'类型'};
    //查询开始时间
    work.begin_time = '';
    //查询结束时间
    work.end_time = '';
    //是否显示提交者下拉列表
    work.isMemWin = false;
    //是否显示状态下拉列表
    work.isStatusWin = false;
    //是否显示类型下拉列表
    work.isTypeWin = false;
    //详情弹窗
    work.showCheckWin = false;

    //搜索状态下拉列表
    work.arrSearchStatus = [
        {status:-1,statusstr:'状态'},
        {status:0,statusstr:'未提交'},
        {status:1,statusstr:'待审阅'},
        {status:2,statusstr:'已审阅'}
    ]
    //搜索类型下拉列表
    work.arrSearchType = [
        {status:0,statusstr:'类型'},
        {status:1,statusstr:'日报'},
        {status:2,statusstr:'周报'},
    ]


    //提交参数
    //提交者
    param_work.u_id = '';
    //查询状态
    param_work.status = '';
    //查询类型
    param_work.type = '';
    //查询开始时间
    param_work.begin_time = '';
    //查询结束时间
    param_work.end_time = '';
    //当前页
    param_work.page = 1;

    work.workList = [];


    //获取审阅工作报告列表
    workModel.getApproveList($scope);

    work.showDetail = function (workId) {
        workModel.getWorkDetail(workId, false, $scope);
        work.showCheckWin = true;
        $('#masklayer1').show();
    }
    if($stateParams.work_id){
        work.showDetail($stateParams.work_id);
    }

    //查询按钮
    work.searchButton = function () {
        //查询开始时间
        param_work.begin_time = $('#searchstarttime').val();
        //查询结束时间
        param_work.end_time = $('#searchendtime').val();

        if ( param_work.end_time != '' && param_work.begin_time > param_work.end_time) {
            alert('结束时间必须大于或者等于开始时间');
            //查询开始时间
            param_work.begin_time = '';
            //查询结束时间
            param_work.end_time = '';
            $('#searchendtime').val('');
            $('#searchstarttime').val('');
            return;
        }
        //是否显示部门下拉列表
        work.isStatusWin = false;
        //是否显示人员下拉列表
        work.isTypeWin = false;
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };

        param_work.page = 1;

        param_work.u_id = work.user.u_id;
        param_work.status = work.status.status;
        //查询类型
        param_work.type = work.type.status;
        workModel.getApproveList($scope);
    };

    //获取所有搜索用户数据
    work.userInfo = work.allMem = [];
    work.searchAllUserInfo = function () {
        work.isOrgWin = false;
        work.search_u_id = '';
        work.search_real_name = '';
        work.user = {};
        if(work.allMem.length > 0){
            work.isMemWin = true;
            $scope.work.userInfo = work.allMem;
            return;
        }

        workModel.getUserInfo($scope);
        // work.isMemWin = true;
    }

    var timer = '';
    //获取搜索用户数据
    work.searchUserInfo = function () {
        clearTimeout(timer);
        timer=setTimeout(function() {
            workModel.getUserInfo($scope);
        }, 500);
        work.search_real_name_temp = work.search_real_name;
        work.isMemWin = true;
    }

    //保存选中的查询用户ID
    work.selectUser = function (obj) {
        work.user = obj;
        work.search_real_name = obj.real_name;
        work.isMemWin = false;
        $("#submitter").hide();
    }

    //保存选中状态
    work.selectStatus = function (obj) {
        work.status = obj;
        work.isStatusWin = false;
        $("#workStatus").hide();
    }


    //保存选中的查询类型
    work.selectType = function (obj) {
        work.type = obj;
        work.isTypeWin = false;
        $("#workType").hide();
    }


    //审阅
    work.approve = function(){
        workModel.approveWork(work.work_detail.work_id, $scope);
        //获取审阅工作报告列表
        // $('#masklayer1').hide();
        // workModel.getApproveList($scope);
    };

    work.back = function(){
        $('#masklayer1').hide();
        work.showCheckWin = false;
    }

    if($stateParams.work_id != undefined && $stateParams.work_id != ''){
        work.showDetail($stateParams.work_id);
    }

    //翻页方法
    $scope.page_fun = function () {
        $scope.param_work.page = $scope.page.tempcurPage;
        workModel.getApproveList($scope);
    };
    
    //显示状态下拉框
    work.workStatusButton = function () {
    	$("#workType").hide();
		if(angular.element("#workStatus").is(":hidden")){
            angular.element("#workStatus").show();
        }else{
            angular.element("#workStatus").hide();
        }
    }
    
    //显示类型下拉框
    work.workTypeButton = function () {
    	$("#workStatus").hide();
		if(angular.element("#workType").is(":hidden")){
            angular.element("#workType").show();
        }else{
            angular.element("#workType").hide();
        }
    }
    //点击隐藏
 	angular.element(document).bind("click",function(event){
        if(angular.element(event.target).parents(".selectbor ").length==0){
            angular.element(".selectbor  ul").hide();
        } 
    });
    
    //显示提交者下拉框
    work.workSubmitterButton = function () {
		if(angular.element("#submitter").is(":hidden")){
            angular.element("#submitter").show();
        }else{
            angular.element("#submitter").hide();
        }
   }
	//点击隐藏提交者
 	angular.element(document).bind("click",function(event){
        if(angular.element(event.target).parents(".submitterBox ").length==0){
            angular.element(".submitterBox  ul").hide();
        } 
    });
});



