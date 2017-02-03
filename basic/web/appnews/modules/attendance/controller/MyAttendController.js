
var AttendanceMod=angular.module('AttendanceMod',['commonMod'])
//我的考勤
AttendanceMod.controller('MyAttendCtrl',function($scope,$http,$rootScope,Publicfactory,$cookies,$cookieStore,$state,attendanceModel,permissionService){
    if (!permissionService.checkPermission('AttendanceMine')) {
        $state.go('main.index');
        return false;
    }
    var attend = $scope.attend = {};
    var param_attend = $scope.param_attend = {};
    //翻页对象
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    //查询状态
    attend.status = 0;
    //查询开始时间
    attend.begin_time = '';
    //查询结束时间
    attend.end_time = '';
    //是否显示状态下拉列表
    attend.isStatusWin = false;
    //查询状态数组
    attend.arrSearchStatus = [
        {status:0,statusstr:'状态'},
        {status:1,statusstr:'正常'},
        {status:2,statusstr:'异常'},
    ]

    //参数
    //查询状态
    param_attend.status = 0;
    //查询开始时间
    param_attend.begin_time = '';
    //查询结束时间
    param_attend.end_time = '';
    //当前页
    param_attend.page = 1;
    $scope.attend.attendList = [];

    //获取我的考勤
    attendanceModel.getMyAttend($scope);

    //查询按钮
    attend.searchButton = function () {
        if(attend.end_time!='' && attend.begin_time!='' && attend.end_time-attend.begin_time<=0){
            alert('结束时间必须大于开始时间');
            return;
        }
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };
        param_attend.page = 1;
        //查询状态
        param_attend.status = attend.status;
        //查询开始时间
        param_attend.begin_time = attend.begin_time;
        //查询结束时间
        param_attend.end_time = attend.end_time;
        attendanceModel.getMyAttend($scope);
    };

    //显示状态选中框
    attend.statusWinButton = function () {
            $('#sta').toggle();
    }

    //设置选中的查询状态
    attend.selectSearchStatus = function (status) {
        attend.status = status;
        angular.element.each(attend.arrSearchStatus, function (key, val) {
            if(val.status==status){
                angular.element('#status').html(val.statusstr);
            }
        });
        $('#sta').hide();
    }

    //翻页方法
    $scope.page_fun = function () {
        $scope.param_attend.page = $scope.page.tempcurPage;
        attendanceModel.getMyAttend($scope);
    };
    $(document).bind("click",function(event){
        if(angular.element(event.target).parents(".selectbor ").length==0){
            $('#sta').hide();
        }
    });
});




