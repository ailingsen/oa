
//我的工作报告
workStatementMod.controller('myWorkCtrl',function($scope,$http,$rootScope,workModel,$timeout,permissionService){
    var work = $scope.work = {};
    var param_work = $scope.param_work = {};
    //翻页对象
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    //查询状态
    work.status = {status:'',statusstr:'状态'};
    //查询类型
    work.type = {status:'',statusstr:'类型'};
    //查询开始时间
    work.begin_time = '';
    //查询结束时间
    work.end_time = '';
    //是否显示状态下拉列表
    work.isStatusWin = false;
    //是否显示类型下拉列表
    work.isTypeWin = false;

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


    //获取我的工作报告
    workModel.getMyWorkList($scope);

    //查询按钮
    work.searchButton = function () {
        //查询开始时间
        param_work.begin_time = work.begin_time;
        //查询结束时间
        param_work.end_time = work.end_time;
        if ( param_work.end_time != '' && param_work.begin_time > param_work.end_time) {
            alert('结束时间必须大于或者等于开始时间');
            //查询开始时间
            param_work.begin_time ='';
            //查询结束时间
            param_work.end_time = '';
            work.begin_time='';
            //查询结束时间
            work.end_time='';
            return;
        }
        // if (!permissionService.checkPermission('WorkstateMyWorkstateQuery')) {
        //     return false;
        // }
        //是否显示部门下拉列表
        work.isStatusWin = false;
        //是否显示人员下拉列表
        work.isTypeWin = false;
        $scope.page = {
            curPage : work.workList.page,//当前页
            tempcurPage :  work.workList.page,//临时当前页
            sumPage :  work.workList.total_page//总页数
        };
        param_work.page = 1;

        param_work.status = work.status.status;
        //查询类型
        param_work.type = work.type.status;

        workModel.getMyWorkList($scope);
    };


    //保存选中状态
    work.selectStatus = function (obj) {
        work.status = obj;
        work.isStatusWin = false;
        $("#workStatementStatus").hide();
    }


    //保存选中的查询类型
    work.selectType = function (obj) {
        work.type = obj;
        work.isTypeWin = false;
        $("#workStatementType").hide();
    }

    //翻页方法
    $scope.page_fun = function () {
        $scope.param_work.page = $scope.page.tempcurPage;
        workModel.getMyWorkList($scope);
    };
    
    //显示状态下拉框
    work.workStatementStatusButton = function () {
    	$("#workStatementType").hide();
		if(angular.element("#workStatementStatus").is(":hidden")){
            angular.element("#workStatementStatus").show();
        }else{
            angular.element("#workStatementStatus").hide();
        }
    }
    
    //显示类型下拉框
    work.workStatementTypeButton = function () {
    	$("#workStatementStatus").hide();
		if(angular.element("#workStatementType").is(":hidden")){
            angular.element("#workStatementType").show();
        }else{
            angular.element("#workStatementType").hide();
        }
    }
    //点击隐藏
 	angular.element(document).bind("click",function(event){
        if(angular.element(event.target).parents(".selectbor ").length==0){
            angular.element(".selectbor  ul").hide();
        } 
    });

});



