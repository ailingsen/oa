AttendanceMod.factory('attendanceModel',function($http,$state){
    var  service={};

    //获取我的考勤
    service.getMyAttend=function($scope){
        $http.post('/index.php?r=attendance/attendance/my-attend', JSON.stringify($scope.param_attend))
            .success(function(data, status) {
                $scope.attend.attendList = data.data.myattendList;
                $scope.page.curPage = data.data.page.curPage;
                $scope.page.sumPage = data.data.page.sumPage;
        });
    };

    //获取所有员工考勤
    service.getAllAttend=function($scope){
        $http.post('/index.php?r=attendance/attendance/all-attend', JSON.stringify($scope.param_attend))
            .success(function(data, status) {
                $scope.attend.attendList = data.data.attendList;
                $scope.page.curPage = data.data.page.curPage;
                $scope.page.sumPage = data.data.page.sumPage;
            });
    };

    //获取考勤统计
    service.getAttendCount=function($scope){
        $http.post('/index.php?r=attendance/attendance/attend-count', JSON.stringify($scope.param_attend))
            .success(function(data, status) {
                $scope.attend.attendList = data.data.attendList;
                $scope.page.curPage = data.data.page.curPage;
                $scope.page.sumPage = data.data.page.sumPage;
            });
    };

    //获取考勤设置
    service.getAttenSet=function($scope){
        $http.post('/index.php?r=attendance/attendance/get-attend-set', {})
            .success(function(data, status) {
                //上班时间
                $scope.attend.begin_time = data.data.begin_time;
                //下班时间
                $scope.attend.end_time = data.data.end_time;;
                //工作日加班起算时间
                $scope.attend.workday_time = data.data.workday_time;;
                //工作日加班调休失效时间
                $scope.attend.workday_lose = data.data.workday_lose;;
                //非工作日加班休息时间
                $scope.attend.unworkday_time = data.data.unworkday_time;;
            });
    };

    //考勤设置
    service.saveAttenSet=function($scope){
        $http.post('/index.php?r=attendance/attendance/attend-set', JSON.stringify($scope.attend))
            .success(function(data, status) {
                if(data.code==1){
                    alert(data.msg);
                }else if(data.code==-1){
                    alert(data.msg);
                    $state.go('main.attendance.attendset',{},{reload:true});
                }
            });
    };

    //搜索时用的组信息
    service.getOrgInfo=function($scope,type){
        $http.post('/index.php?r=attendance/attendance/org-info', {search_org_name:$scope.attend.search_org_name})
            .success(function(data, status) {
                $scope.attend.orgInfo = data.data;
                if(type == 1){
                    var temp=[];
                    temp['org_id'] = 0;
                    temp['org_name'] = '部门';
                    $scope.attend.orgInfo.unshift(temp);
                }
                $scope.attend.isOrgWin = true;
            });
    };

    //搜索时用的用户信息
    service.getUserInfo=function($scope){
        $http.post('/index.php?r=attendance/attendance/member-info', {search_org_id:$scope.attend.search_org_id,search_real_name:$scope.attend.search_real_name})
            .success(function(data, status) {
                $scope.attend.userInfo = data.data;
                $scope.attend.isMemWin = true;
            });
    };

    //获取假期统计
    service.getVacationStat=function($scope){
        $http.post('/index.php?r=vacation/vacation/statistic', JSON.stringify($scope.param_attend))
            .success(function(data, status) {
                $scope.vacation.vacationList = data.data.vacation_list;
                $scope.page.curPage = data.data.page;
                $scope.page.sumPage = data.data.total_page;
            });
    };

    return service;
});