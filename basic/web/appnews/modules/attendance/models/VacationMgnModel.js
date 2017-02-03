/**
 * Created by pengyanzhang on 2016/8/16.
 */
AttendanceMod.factory('VacationMgnModel',function($http,$state){
    var vacationService = {};
    //获取所有组
    vacationService.getDepartmentInfo = function($scope,orgName) {
        $http.post('/index.php?r=attendance/attendance/org-info',{search_org_name:orgName})
            .success(function(data) {
                $scope.department = data.data;
            });
    };
    //搜索时用的用户信息
    vacationService.getMemberListInfo = function($scope, orgId, searchName) {
        $http.post('/index.php?r=attendance/attendance/member-info',{search_org_id:orgId ,search_real_name:searchName})
            .success(function(data) {
                $scope.memberListInfo = data.data;
                $scope.vacationMgn.searchName = '';
            });
    };
    //获取假期相关数据
    vacationService.getVacationDataInfo = function ($scope, orgId, userName, pageSize, curPage) {
        $http.post('/index.php?r=attendance/vacation-mgn/annual-vacations',{orgId:orgId ,userName:userName, pageSize:pageSize, curPage:curPage})
            .success(function(data) {
                $scope.vacationDataList = data.data.vacationData;
                $scope.page.sumPage =data.data.totalPage ;
                $scope.page.curPage =$scope.page.tempcurPage ;
            });
    };

    //导出假期
    vacationService.outGetVacationData = function ($scope, orgId, userName) {
        window.location.href = '/index.php?r=attendance/vacation-mgn/vacation-excel&orgId='+orgId+'&userName='+userName;
    };
    //修改年假
    vacationService.modifyVacationLeave= function ($scope, uid, increment, valueBefore, reason) {
        $http.post('/index.php?r=attendance/vacation-mgn/edit-annual-vacation',{uid:uid ,increment:increment, valueBefore:valueBefore, reason:reason,p_code:'AttendanceVacationmgn'})
            .success(function(data) {
                if(data.code==0){
                    alert(data.msg);
                    return;
                }else if(data.code==20000){
                    alert(data.msg);
                    $scope.vacationMgn.vacationLeave = '';
                    $scope.vacationMgn.vacationLeaveReason = '';
                    $scope.vacationMgn.popupCtr = false;
                    $('#masklayer1').hide();
                    if($scope.vacationMgn.pageType==1){
                        vacationService.getVacationDataInfo($scope, $scope.vacationMgn.orgId, $scope.vacationMgn.searchName, '',$scope.page.tempcurPage);
                    }else {
                        vacationService.getVacationDataInfo($scope, $scope.vacationMgn.orgId, $scope.vacationMgn.searchName);
                        $scope.page={
                            curPage : 1,//当前页
                            tempcurPage : 1,//临时当前页
                            sumPage : 0//总页数
                        };
                    }
                }
            });
    };
    //修改调休假
    vacationService.modifyTuneVacation= function ($scope, uid, increment, valueBefore, reason) {
        $http.post('/index.php?r=attendance/vacation-mgn/edit-tune-vacation',{uid:uid ,increment:increment, valueBefore:valueBefore, reason:reason,p_code:'AttendanceVacationmgn'})
            .success(function(data) {
                if(data.code==0){
                    alert(data.msg);
                    return;
                }else if(data.code==20000){
                    alert(data.msg);
                    $scope.vacationMgn.tuneVacation = '';
                    $scope.vacationMgn.vacationLeave = '';
                    $scope.vacationMgn.vacationLeaveReason = '';
                    $scope.vacationMgn.popupTuneCtr = false;
                    $('#masklayer1').hide();
                    if($scope.vacationMgn.pageType==2){
                        vacationService.getVacationDataInfo($scope, $scope.vacationMgn.orgId, $scope.vacationMgn.searchName, '',$scope.page.tempcurPage);
                    }else {
                        vacationService.getVacationDataInfo($scope, $scope.vacationMgn.orgId,$scope.vacationMgn.searchName);
                        $scope.page={
                            curPage : 1,//当前页
                            tempcurPage : 1,//临时当前页
                            sumPage : 0//总页数
                        };
                    }
                }
            });
    };
    
    vacationService.getChangeRecord = function($scope, uid, logType) {
        $http.post('/index.php?r=attendance/vacation-mgn/change-record',{uid:uid ,logType:logType})
            .success(function(data) {
                $scope.changeRecordList = data.data.changeRecord;
            });
    };

    //获取假期设置数据
    vacationService.getVacationSet = function ($scope) {
        $http.get('/index.php?r=vacation/vacation/view-set')
            .success(function(data) {
                $scope.vacationMgn.vacationSet = data.data;
            });
    };
    //假期设置
    vacationService.saveVacationSet = function ($scope,ini_annual_vacation,overtime_expire) {
        $http.post('/index.php?r=vacation/vacation/vacation-set', {'ini_annual_vacation':ini_annual_vacation, 'overtime_expire':overtime_expire})
            .success(function(data) {
                if(data.code == 20000) {
                    $scope.vacationMgn.showVacationSet = false;
                    $("#masklayer1").hide();
                }else{
                    alert(data.msg);
                }
            });
    };
    return vacationService;


});
