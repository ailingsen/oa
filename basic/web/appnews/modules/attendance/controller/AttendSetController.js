
//我的考勤
AttendanceMod.controller('AttendSetCtrl',function($scope,$http,$rootScope,Publicfactory,$cookies,$cookieStore,$state,attendanceModel){
    var attend = $scope.attend = {};
    //上班时间
    attend.begin_time = '';
    //下班时间
    attend.end_time = '';
    //工作日加班起算时间
    attend.workday_time = '';
    //工作日加班调休失效时间
    attend.workday_lose = '';
    //非工作日加班休息时间
    attend.unworkday_time = '';

    //获取考勤设置
    attendanceModel.getAttenSet($scope);

    //保存设置
    $scope.saveAttenSet = function () {
        //数据验证-------------------------------------------------------
        attendanceModel.saveAttenSet($scope);
    };

    //重置
    $scope.resetButton = function(){
        //上班时间
        attend.begin_time = '';
        //下班时间
        attend.end_time = '';
        //工作日加班起算时间
        attend.workday_time = '';
        //工作日加班调休失效时间
        attend.workday_lose = '';
        //非工作日加班休息时间
        attend.unworkday_time = '';
    }

});




