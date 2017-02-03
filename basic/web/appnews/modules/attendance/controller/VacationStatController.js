//我的考勤
AttendanceMod.controller('vacationStat',function($scope,$http,$rootScope,Publicfactory,$cookies,$cookieStore,$state,attendanceModel){
    var attend = $scope.attend = {};
    var param_attend = $scope.param_attend = {};
    var vacation = $scope.vacation = {};
    //翻页对象
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    //查询部门ID
    attend.search_org_id = '';
    //查询用户ID
    attend.search_u_id = '';
    //查询开始时间
    attend.begin_time = '';
    //查询结束时间
    attend.end_time = '';
    //查询部门
    attend.search_org_name = '';
    //查询部门临时数据
    attend.search_org_name_temp = '';
    //查询用户
    attend.search_real_name = '';
    //查询用户临时数据
    attend.search_real_name_temp = ''
    //是否显示部门下拉列表
    attend.isOrgWin = false;
    //是否显示人员下拉列表
    attend.isMemWin = false;

    //提交参数
    //查询部门ID
    param_attend.org_id = '';
    //查询用户ID
    param_attend.search_u_id = '';
    //查询开始时间
    param_attend.begin_time = '';
    //查询结束时间
    param_attend.end_time = '';
    param_attend.search_name = '';
    //当前页
    param_attend.page = 1;

    attend.attendList = [];
    //搜索部门数据
    attend.orgInfo = [];
    //搜索用户数据
    attend.userInfo = [];

    $scope.vacation.vacationList = [];

    //获取考勤统计
    attendanceModel.getVacationStat($scope);
    /*//获取搜索部门数据
     attendanceModel.getOrgInfo($scope);
     //获取搜索用户数据
     attendanceModel.getUserInfo($scope);*/

    //查询按钮
    attend.searchButton = function () {
        if(attend.end_time!='' && attend.begin_time!='' && attend.end_time-attend.begin_time<=0){
            alert('结束时间必须大于开始时间');
            return;
        }
        //是否显示部门下拉列表
        attend.isOrgWin = false;
        //是否显示人员下拉列表
        attend.isMemWin = false;
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };
        param_attend.page = 1;
        if(attend.search_org_id == ''){
            attend.search_org_name = '';
            attend.search_org_name_temp = '';
        }
        if(attend.search_u_id == ''){
            attend.search_real_name = '';
            attend.search_real_name_temp = ''
        }
        param_attend.org_id = attend.search_org_id;
        //查询用户ID
        param_attend.search_u_id = attend.search_u_id;
        param_attend.search_name = attend.search_real_name;
        //查询开始时间
        param_attend.begin_time = attend.begin_time;
        //查询结束时间
        param_attend.end_time = attend.end_time;
        attendanceModel.getVacationStat($scope);
    };
    //获取所有搜索部门
    attend.getSearchAllOrgInfo = function(){
        attend.search_org_name='';
        attend.search_org_id = '';
        attend.isMemWin = false;
        attendanceModel.getOrgInfo($scope,1);
    }

    //获取搜索部门数据
    attend.searchOrgInfo = function () {
        attend.isMemWin = false;
        if(attend.search_org_name != attend.search_org_name_temp){
            attendanceModel.getOrgInfo($scope,0);
            attend.search_org_name_temp = attend.search_org_name;
        }
    }

    //保存选中的查询部门ID
    attend.selectOrg = function (obj) {
        attend.search_org_id = obj.value;
        attend.search_org_name = obj.label;
        attend.search_u_id = '';
        attend.search_real_name = '';
        attend.isOrgWin = false;
    }

    //获取所有搜索用户数据
    attend.searchAllUserInfo = function () {
        attend.isOrgWin = false;
        attend.search_u_id = '';
        attend.search_real_name = '';
        attendanceModel.getUserInfo($scope);
    }

    //获取搜索用户数据
    attend.searchUserInfo = function () {
        attend.isOrgWin = false;
        if(attend.search_real_name != attend.search_real_name_temp){
            attendanceModel.getUserInfo($scope);
            attend.search_real_name_temp = attend.search_real_name;
        }
    }

    //保存选中的查询用户ID
    attend.selectUser = function (obj) {
        attend.search_u_id = obj.value;
        attend.search_real_name = obj.label;
        attend.isMemWin = false;
    }

    //翻页方法
    $scope.page_fun = function () {
        $scope.param_attend.page = $scope.page.tempcurPage;
        attendanceModel.getVacationStat($scope);
    };

    attend.leave=function(i){
        if(i==1){
            attend.isOrgWin=false;
        }
        if(i==2){
            attend.isMemWin=false;
        }
    }

    //导出
    vacation.export = function() {
        if(attend.start_time){
            var endM=attend.start_time.getMonth()+6;
            var endY=attend.start_time.getFullYear();
            if(endM>12){
                endM=endM-12;
                endY++;
            }
            var afterSixMonth=new Date(attend.start_time);
            afterSixMonth.setFullYear(endY);
            afterSixMonth.setMonth(endM);
        }
        if(attend.start_time==0||attend.end_time==0){
            alert('导出excel必须输入开始时间与结束时间')
        }else if(attend.end_time>afterSixMonth){
            alert('只能导出时间跨度为6个月内的数据')
        }else if(attend.end_time-attend.start_time<0) {
            alert('结束时间必须大于开始时间')
        }else{
            attend.search_name = attend.search_real_name;
            attend.org_id = attend.search_org_id;
            $http.get("/index.php?r=vacation/vacation/has-content&args=" + JSON.stringify(attend))
                .success(function(data){
                    //if(data=='没有需要导出的内容'){
                    if(data.code == 0) {
                        alert(data.msg);
                    }else{
                        window.location.href = "/index.php?r=vacation/vacation/exportexcel&args=" + JSON.stringify(attend);
                    }
                })
        }
    }

});









