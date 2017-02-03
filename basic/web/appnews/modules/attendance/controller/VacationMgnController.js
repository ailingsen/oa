/**
 * Created by pengyanzhang on 2016/8/16.
 */
AttendanceMod.controller('VacationMgnCtrl',function($scope,$http,$state,VacationMgnModel,noticeService,Publicfactory,permissionService){
    if (!permissionService.checkPermission('AttendanceVacationmgn')) {
        $state.go('main.index');
        return false;
    }
    var vacation = $scope.vacationMgn = {};
    vacation.popupVacationCtr = false;
    vacation.popupVacationChangeCtr = false;
    vacation.popupTuneCtr = false;
    vacation.searchOrgName = '';
    vacation.vacationLeave = '';
    vacation.vacationLeaveReason = '';
    vacation.orgName = '部门';
    vacation.valueBefore = '';
    vacation.orgId = '';
    vacation.filterOrgName = '';
    vacation.searchName = '';
    vacation.uid = '';
    vacation.name = '成员';
    vacation.pageType = '';
    vacation.logType = '';
    vacation.statusCtr = false;
    vacation.userStatusCtr = false;
    vacation.currentPage = '';
    vacation.vacationSet = '';
    vacation.showVacationSet = false;
    vacation.noticeService = noticeService;
    $scope.department = [];
    $scope.memberListInfo = [];
    $scope.vacationDataList = [];
    $scope.changeRecordList = [];
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    VacationMgnModel.getDepartmentInfo($scope,vacation.searchOrgName);
    VacationMgnModel.getVacationDataInfo($scope, vacation.orgId, vacation.searchName);
    //分页
    $scope.taskPaging = function(){
        vacation.currentPage = $scope.page.tempcurPage;
        VacationMgnModel.getVacationDataInfo($scope, vacation.orgId, vacation.searchName, '',vacation.currentPage);
    };
    vacation.searchOrg = function(){
        vacation.searchOrgName = $scope.vacationMgn.searchOrgName;
        VacationMgnModel.getDepartmentInfo($scope,vacation.searchOrgName);
        vacation.statusCtr = true;
    };
    //部门下拉框控制
    vacation.departmentSelectPopup = function () {
        vacation.statusCtr = !vacation.statusCtr
    };
    //部门下相关成员下拉
    vacation.memberSelectPopup = function () {
        vacation.userStatusCtr = !vacation.userStatusCtr;
        VacationMgnModel.getMemberListInfo($scope,vacation.orgId, vacation.searchName);
    };
    vacation.selectOrgName = function(obj) {
        vacation.searchOrgName = obj.label;
        vacation.orgId = obj.value;
        vacation.name = '成员';
        vacation.uid = '';
        vacation.statusCtr = false;
        VacationMgnModel.getMemberListInfo($scope,vacation.orgId);
    };

    vacation.selectUserName = function(obj) {
        vacation.searchName = obj.label;
        vacation.uid = obj.value;
        vacation.userStatusCtr = false;
    };

    vacation.searchUserName = function() {
        vacation.userStatusCtr = true;
        vacation.searchName = $scope.vacationMgn.searchName;
        VacationMgnModel.getMemberListInfo($scope,vacation.orgId, vacation.searchName);
    };

    vacation.searchVacation = function() {
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };
        VacationMgnModel.getVacationDataInfo($scope, vacation.orgId, vacation.searchName)
    };
    //假期导出
    vacation.outVacationData = function(){
        VacationMgnModel.outGetVacationData($scope, vacation.orgId, vacation.searchName);
    };
    //修改弹窗
    vacation.modifyLeavePopup = function(uid, valueBefore) {
        vacation.uid = uid;
        vacation.valueBefore = valueBefore;
        vacation.popupCtr = true;
        vacation.pageType = 1;
        $('#masklayer1').show();
    };
    vacation.hidePopupCtr=function(){
        $scope.vacationMgn.vacationLeave = '';
        $scope.vacationMgn.tuneVacation = '';
        $scope.vacationMgn.vacationLeaveReason = '';
        vacation.popupCtr = false;
        $('#masklayer1').hide();
    }
    //修改调休弹窗
    vacation.modifyPopup = function(uid, valueBefore) {
        vacation.uid = uid;
        vacation.valueBefore = valueBefore;
        vacation.popupTuneCtr = true;
        vacation.pageType = 2;
        $('#masklayer1').show();
    };
    vacation.hidePopupTuneCtr=function(){
        $scope.vacationMgn.tuneVacation = '';
        $scope.vacationMgn.vacationLeave = '';
        $scope.vacationMgn.vacationLeaveReason = '';
        vacation.popupTuneCtr = false;
        $('#masklayer1').hide();
    }
    //修改年假
    vacation.modifyVacationLeave = function(valueBefore) {
        vacation.vacationLeave = $scope.vacationMgn.vacationLeave;
        vacation.vacationLeaveReason = $scope.vacationMgn.vacationLeaveReason;
        if(vacation.vacationLeave>15 || vacation.vacationLeave<-15){
            alert('输入不能超过15天，请输入年假天数！');
            return ;
        }
        if(vacation.vacationLeave%0.5!=0 || vacation.vacationLeave.length==0){
            alert('输入天数不是0.5的整数倍，请输入年假天数！');
            return ;
        }
        if(Publicfactory.checkEnCnstrlen(vacation.vacationLeaveReason)<=0 || Publicfactory.checkEnCnstrlen(vacation.vacationLeaveReason)>60){
            alert('请输入修改原因,且不能超过30个字！');
            return;
        }
        VacationMgnModel.modifyVacationLeave($scope, vacation.uid, vacation.vacationLeave, vacation.valueBefore, vacation.vacationLeaveReason);
    };
    //调休假修改
    vacation.modifyTuneVacation = function() {
        vacation.vacationLeave = $scope.vacationMgn.tuneVacation;
        vacation.vacationLeaveReason = $scope.vacationMgn.vacationLeaveReason;
        if(vacation.vacationLeave%0.5!=0 || vacation.vacationLeave==0){
            alert('输入天数不是0.5的整数倍，请输入调休天数！');
            return ;
        }
        if(Publicfactory.checkEnCnstrlen(vacation.vacationLeaveReason)<=0 || Publicfactory.checkEnCnstrlen(vacation.vacationLeaveReason)>60){
            alert('请输入修改原因,且不能超过30个字！');
            return;
        }
        VacationMgnModel.modifyTuneVacation($scope, vacation.uid, vacation.vacationLeave, vacation.valueBefore, vacation.vacationLeaveReason);
    };
    
    //查看变更记录
    vacation.getChangeRecord = function(uid, logType) {
        vacation.uid = uid;
        vacation.logType = logType;
        VacationMgnModel.getChangeRecord($scope, vacation.uid, vacation.logType);
        vacation.popupVacationChangeCtr = true;
        $("#masklayer1").show();
    }
    //关闭变更记录
    vacation.closeChangeRecord = function() {
        vacation.popupVacationChangeCtr = false;
        $("#masklayer1").hide();
    }
    //设置假期
    vacation.setVacation = function() {
        VacationMgnModel.getVacationSet($scope);
        vacation.showVacationSet = true;
        $("#masklayer1").show();
    }
    //保存假期设置
    //判断是否为整数
    function isInteger(obj) {
        return Math.floor(obj) == obj
    }
    vacation.saveSet = function() {
        if (vacation.vacationSet.ini_annual_vacation == '' || vacation.vacationSet.overtime_expire == ''){
            alert("假期设置数据不能为空");
            return;
        }
        if (!isInteger(vacation.vacationSet.ini_annual_vacation) || vacation.vacationSet.ini_annual_vacation < 0) {
            alert("初始年假只能为非负整数");
            return;
        }
        if (!isInteger(vacation.vacationSet.overtime_expire) || vacation.vacationSet.overtime_expire < 0) {
            alert("调休假失效时间只能为非负整数");
            return;
        }
        VacationMgnModel.saveVacationSet($scope, vacation.vacationSet.ini_annual_vacation, vacation.vacationSet.overtime_expire);
    }
    //重置
    vacation.reSet = function() {
        vacation.vacationSet.ini_annual_vacation = '';
        vacation.vacationSet.overtime_expire = '';
    }
    //返回
    vacation.back = function() {
        vacation.showVacationSet = false;
        $("#masklayer1").hide();
    }
});



/*//我的考勤
AttendanceMod.controller('VacationMgnCtrl',function($scope,$http,$state,VacationMgnModel,noticeService,Publicfactory,permissionService){
    if (!permissionService.checkPermission('AttendanceVacationmgn')) {
        $state.go('main.index');
        return false;
    }
    var attend = $scope.attend = {};
    var param_attend = $scope.param_attend = {};
    var vacation = $scope.vacationMgn = {};
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
    param_attend.searchOrgName = '';
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

    vacation.vacationList = [];
    $scope.department = [];
    $scope.memberListInfo = [];
    $scope.vacationDataList = [];
    $scope.changeRecordList = [];

    //获取考勤统计
    VacationMgnModel.getDepartmentInfo($scope,param_attend.searchOrgName);
    VacationMgnModel.getVacationDataInfo($scope, param_attend.org_id, param_attend.search_name);
    /!*!//获取搜索部门数据
     attendanceModel.getOrgInfo($scope);
     //获取搜索用户数据
     attendanceModel.getUserInfo($scope);*!/

    //查询按钮
    attend.searchButton = function () {
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
        VacationMgnModel.getVacationDataInfo($scope, param_attend.org_id, param_attend.search_name)
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
    //分页
    $scope.taskPaging = function(){
        $scope.param_attend.page = $scope.page.tempcurPage;
        VacationMgnModel.getVacationDataInfo($scope, param_attend.org_id, param_attend.search_name,'',param_attend.page );
    };

    //假期导出
    vacation.outVacationData = function(){
        VacationMgnModel.outGetVacationData($scope, attend.org_id, attend.search_real_name);
    };
    //修改弹窗
    vacation.modifyLeavePopup = function(uid, valueBefore) {
        vacation.uid = uid;
        vacation.valueBefore = valueBefore;
        vacation.popupCtr = true;
        vacation.pageType = 1;
        $('#masklayer1').show();
    };
    vacation.hidePopupCtr=function(){
        $scope.vacationMgn.vacationLeave = '';
        $scope.vacationMgn.tuneVacation = '';
        $scope.vacationMgn.vacationLeaveReason = '';
        vacation.popupCtr = false;
        $('#masklayer1').hide();
    }
    //修改调休弹窗
    vacation.modifyPopup = function(uid, valueBefore) {
        vacation.uid = uid;
        vacation.valueBefore = valueBefore;
        vacation.popupTuneCtr = true;
        vacation.pageType = 2;
        $('#masklayer1').show();
    };
    vacation.hidePopupTuneCtr=function(){
        $scope.vacationMgn.tuneVacation = '';
        $scope.vacationMgn.vacationLeave = '';
        $scope.vacationMgn.vacationLeaveReason = '';
        vacation.popupTuneCtr = false;
        $('#masklayer1').hide();
    }
    //修改年假
    vacation.modifyVacationLeave = function(valueBefore) {
        vacation.vacationLeave = $scope.vacationMgn.vacationLeave;
        vacation.vacationLeaveReason = $scope.vacationMgn.vacationLeaveReason;
        if(vacation.vacationLeave>15 || vacation.vacationLeave<-15){
            alert('输入不能超过15天，请输入年假天数！');
            return ;
        }
        if(vacation.vacationLeave%0.5!=0 || vacation.vacationLeave.length==0){
            alert('输入天数不是0.5的整数倍，请输入年假天数！');
            return ;
        }
        if(Publicfactory.checkEnCnstrlen(vacation.vacationLeaveReason)<=0 || Publicfactory.checkEnCnstrlen(vacation.vacationLeaveReason)>60){
            alert('请输入修改原因,且不能超过30个字！');
            return;
        }
        VacationMgnModel.modifyVacationLeave($scope, vacation.uid, vacation.vacationLeave, vacation.valueBefore, vacation.vacationLeaveReason);
    };
    //调休假修改
    vacation.modifyTuneVacation = function() {
        vacation.vacationLeave = $scope.vacationMgn.tuneVacation;
        vacation.vacationLeaveReason = $scope.vacationMgn.vacationLeaveReason;
        if(vacation.vacationLeave%0.5!=0 || vacation.vacationLeave==0){
            alert('输入天数不是0.5的整数倍，请输入调休天数！');
            return ;
        }
        if(Publicfactory.checkEnCnstrlen(vacation.vacationLeaveReason)<=0 || Publicfactory.checkEnCnstrlen(vacation.vacationLeaveReason)>60){
            alert('请输入修改原因,且不能超过30个字！');
            return;
        }
        VacationMgnModel.modifyTuneVacation($scope, vacation.uid, vacation.vacationLeave, vacation.valueBefore, vacation.vacationLeaveReason);
    };

    //查看变更记录
    vacation.getChangeRecord = function(uid, logType) {
        vacation.uid = uid;
        vacation.logType = logType;
        VacationMgnModel.getChangeRecord($scope, vacation.uid, vacation.logType);
        vacation.popupVacationChangeCtr = true;
        $("#masklayer1").show();
    }
    //关闭变更记录
    vacation.closeChangeRecord = function() {
        vacation.popupVacationChangeCtr = false;
        $("#masklayer1").hide();
    }
    //设置假期
    vacation.setVacation = function() {
        VacationMgnModel.getVacationSet($scope);
        vacation.showVacationSet = true;
        $("#masklayer1").show();
    }
    //保存假期设置
    //判断是否为整数
    function isInteger(obj) {
        return Math.floor(obj) == obj
    }
    vacation.saveSet = function() {
        if (vacation.vacationSet.ini_annual_vacation == '' || vacation.vacationSet.overtime_expire == ''){
            alert("假期设置数据不能为空");
            return;
        }
        if (!isInteger(vacation.vacationSet.ini_annual_vacation) || vacation.vacationSet.ini_annual_vacation < 0) {
            alert("初始年假只能为非负整数");
            return;
        }
        if (!isInteger(vacation.vacationSet.overtime_expire) || vacation.vacationSet.overtime_expire < 0) {
            alert("调休假失效时间只能为非负整数");
            return;
        }
        VacationMgnModel.saveVacationSet($scope, vacation.vacationSet.ini_annual_vacation, vacation.vacationSet.overtime_expire);
    }
    //重置
    vacation.reSet = function() {
        vacation.vacationSet.ini_annual_vacation = '';
        vacation.vacationSet.overtime_expire = '';
    }
    //返回
    vacation.back = function() {
        vacation.showVacationSet = false;
        $("#masklayer1").hide();
    }

});*/









