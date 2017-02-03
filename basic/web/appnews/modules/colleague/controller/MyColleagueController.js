
ColleagueMod.controller('MyColleagueCtr',function($scope,$state,$http,$compile,$stateParams,ColleagueModel,noticeService,Publicfactory,$cookieStore,permissionService){
    if (!permissionService.checkPermission('WorkmateMymate')) {
        $state.go('main.index');
        return false;
    }
    var colleague=$scope.colleague={};
    $scope.allDepartmentList = [];
    $scope.allDepartmentChildrenList = [];
    $scope.DepartmentChildrenList = [];
    $scope.DepartmentChildOneList = [];
    $scope.DepartmentChildTwoList = [];
    $scope.myTeamList = [];
    $scope.orgNameList = [];
    colleague.myTeamList = [];
    colleague.myTeamNubSum = '';
    colleague.memberInfoMess = [];
    colleague.noticeService = noticeService;
    colleague.orgId = $cookieStore.get('userInfo').org.org_id;
    colleague.Uid = '';
    colleague.isLeader = '';
    colleague.companyNubSum = '';
    colleague.departmentCtr = false;
    colleague.orgName = '';
    colleague.orgSearchName = '';
    colleague.departmentName = '';
    colleague.departmentNameSearch = '纳米娱乐';
    colleague.sectorSettingsList = [];
    colleague.sectorSuperiorDepName = '';
    colleague.higherDepartment = '';
    colleague.sectorDepName = '';
    colleague.sectorDepLeader = '';
    colleague.sectorDepOrgId = '';
    colleague.sectorPageOrgId = colleague.orgId;
    colleague.sectorMemberList = [];
    colleague.sectorSearchName = '';
    colleague.sectorParentId = '';
    colleague.setSectorParentId = '';
    colleague.gMid = '';
    //转移部门
    colleague.transferDepSearName = '';
    colleague.transferDepOrgUid = '';
    colleague.originalDep = '';
    colleague.errorInfo = {
        code : '',
        msg : ''
    };
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };

    //分页
    $scope.taskPaging = function(){
        ColleagueModel.myTeamMemberList($scope,colleague.sectorPageOrgId,$scope.page.tempcurPage);
    };

    ColleagueModel.myTeamMemberListOne($scope,colleague.orgId);
    //创建部门弹出框
    colleague.createPart=false;
    colleague.showCreatePart=function(){
        colleague.createPart=true;
        $('#masklayer1').show();
    }
    colleague.HideCreatePart=function(){
        $('#masklayer1').hide();
        colleague.createPart=false;
    }


    //设置部门弹出框
    colleague.isShowPartSet=false;

    //解散部门提示框
    colleague.isShowDissmiss=false;

    //转移部门弹出框
    colleague.isShowTransfer=false;

    //添加负责人弹出框
    colleague.isShowAddPrin=false;



    //公司所有部门展示
    ColleagueModel.getAllDepartment($scope,0);
    //技能、工作信息选项卡切换
    colleague.isselected=true;
    colleague.selectMsg=function(i, e){
        e.stopPropagation();
        if(!i)colleague.isselected=true;
        if(i)colleague.isselected=false;
    };

    //下拉、收起效果
    colleague.drop=function(i,ind, e){
        e.stopPropagation();
        if (colleague.isShowTransfer){
            i = 2;
        }
        if(i==1) {
            var key = 0;
            for(key = 0; key < $scope.myTeamList.list.length;key ++){
                if (ind == key)continue;
                $scope.myTeamList.list[key].isShow = false;
            }
            $scope.myTeamList.list[ind].isShow = !$scope.myTeamList.list[ind].isShow;
            if ($scope.myTeamList.list[ind].isShow) {
                $('.msg-detail').show();
            }
        }else if(i==2){
            $scope.myTeamList.list[ind].isShow = false;
        }
    };
    //转移部门成员弹窗控制
    colleague.transferDepartmentMemCtr = function (orgUid,uId) {
        $('#masklayer1').show();
        colleague.Uid = uId;
        colleague.isShowTransfer=true;
        colleague.transferDepOrgUid = orgUid;
        ColleagueModel.getOrgInfo($scope,'');
        ColleagueModel.getTransferDepOrgName($scope,colleague.transferDepOrgUid);
    };
    colleague.HideTransfer=function(){
        $('#masklayer1').hide();
        colleague.isShowTransfer=false;
    }
    //我的团队
    colleague.orgDrop = function(ind) {
        angular.forEach($scope.allDepartmentList , function(one, index) {
            $scope.myTeamList.list[index].isShow = !$scope.myTeamList.list[index].isShow;
            if($scope.myTeamList.list[ind].isShow) {
            }
            if(i==2){
                $scope.myTeamList.list[ind].isShow = false;
            }
        });
    };
    //创建部门点击下拉
    colleague.departmentShowCtr = function() {
        colleague.departmentCtr = true;
        ColleagueModel.getOrgInfo($scope,'');

    };
    //
    colleague.departmentInputCtr = function() {
        colleague.departmentCtr = true;
        ColleagueModel.getOrgInfo($scope,$scope.colleague.departmentNameSearch);
    };

    colleague.superiorOrgId = '2-0';
    //创建部门选择部门
    colleague.selectDepartment = function(orgName,orgID) {
        $scope.colleague.departmentNameSearch = orgName;
        colleague.superiorOrgId = orgID+'-'+'0';
        colleague.departmentCtr = false;
    };
    //添加部门确定操作
    colleague.addDepartment = function() {
        if (Publicfactory.checkEnCnstrlen($scope.colleague.departmentNameSearch) <= 0) {
            alert('上级部门不能为空！');
            return;
        }
        if(colleague.superiorOrgId.length<=0){
            alert('上级部门不存在，请重新选择！');
            return;
        }
        if($scope.colleague.departmentNameSearch == '纳米娱乐'){
            colleague.orgId = '2-0';
        }
        if(colleague.departmentName.length>20){
            alert('部门名称输入不能超过20个字！');
            return;
        }
        if(Publicfactory.checkEnCnstrlen($scope.colleague.departmentName) <= 0){
            alert('部门名称部门为空！');
            return;
        }
        ColleagueModel.addDepartment($scope,colleague.superiorOrgId ,$scope.colleague.departmentName);
    };

//左侧导航下拉控制开始
    colleague.getMyGroup=function($event){
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };
        var target=angular.element($event.target);
        if(target.hasClass('member-num')){
            target=target.parent();
        }
        $('.colleague-nav div[class~="col-active"]').removeClass('col-active');
        target.addClass('col-active');
        var icon=target.next().find('i:first-child');
        icon.html('&#xe60f;').removeClass('down');
        target.next().next().remove();
        colleague.sectorPageOrgId = colleague.orgId;
        ColleagueModel.myTeamMemberList($scope,colleague.orgId);
    };
    colleague.getGroup=function(parent_id,$event) {
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };
        var target=angular.element($event.target);
        if(target.hasClass('icon-trigon')||target.hasClass('member-num')||target.hasClass('overf')){
            target=target.parent();
        }
        $('.colleague-nav div[class~="col-active"]').removeClass('col-active');
        target.parent().addClass('col-active');
        var icon = target.find('i:first-child');
        if (icon.hasClass('down')) {
            icon.html('&#xe60f;').removeClass('down');
            target.parent().next().remove();
        }else{
            icon.html('&#xe60b;').addClass('down');
            target.parent().parent().siblings().children('ul').remove();
            target.parent().parent().siblings().children('div').find('i[class~="down"]').html('&#xe60f;').removeClass('down');
            $http.get('/index.php?r=workmate/workmate/list&parent_group_id=' + parent_id).
                success(function (data, status, headers, config) {
                    var html = "<ul id=" + parent_id + ">";
                    var noHaveData = [];
                    angular.element.each(data.data, function (key, val) {
                        if (!val['key']) {
                            noHaveData.push(val['org_id']);
                        }
                        html += '<li id="s' + val['org_id'] + '">' +
                        '<div class="nav-common nav-allcol" >' +
                        '<span data-ng-click="colleague.getGroup(' + val['org_id'] + ',$event)">' +
                        '<i class="icon-trigon">&#xe60f;</i>' +
                        '<b class="overf">'+val['org_name'] +'</b>'+
                        '&nbsp;<span class="member-num">' + val["count"] + '</span>' +
                        '</span>' +
                        '<i isperm pcode="WorkmateMymateEdite" class="icon-trigon add-dep fr" ng-click="colleague.sectorSettings('+ val['org_id'] +')">&#xe607;</i>' +
                        '</div>' +
                        '</li>';
                    });
                    html += "</ul>";
                    var ele = angular.element(html);
                    $compile(ele)($scope);
                    target.parent().parent().append(ele);
                    var div = target.parent();
                    var left = parseFloat(div.css('padding-left')) + 15;
                    ele.find('div').css('padding-left', left + 'px');
                    for (var i = 0; i < noHaveData.length; i++) {
                        angular.element('#s' + noHaveData[i]).find('div').css('background', '#fff').find('i:first-child').remove();
                    }
                    colleague.sectorPageOrgId = parent_id;
                    ColleagueModel.myTeamMemberList($scope,parent_id);
                });
            $event.stopPropagation();
        };
    };
    //部门设置去重
    colleague.sectorSettingRemovalDepName = '';

    //部门数据设置  弹窗
    colleague.sectorSettings = function(orgId){
        colleague.isShowPartSet=true;
        $('#masklayer1').show();
        ColleagueModel.getSectorSettings($scope,orgId);
    };
    colleague.HidePartSet=function(){
        colleague.sectorDepLeader = '';
        colleague.gMid = '';
        colleague.isShowPartSet=false;
        $('#masklayer1').hide();
    };
    //设置部门负责人 弹窗
    colleague.setSectorLeaderPopup = function (orgId) {
        colleague.isShowAddPrin=true;
        colleague.sectorDepOrgId = orgId;
        ColleagueModel.setSectorDepMember($scope,colleague.sectorDepOrgId,'',1);
    };
    //选择部门负责人
    colleague.getSelectLeader = function(gMid,name) {
        colleague.gMid = gMid;
        colleague.sectorDepLeader = name;
        colleague.isShowAddPrin = false;
        //ColleagueModel.setLeader(colleague.sectorDepOrgId,colleague.gMid)
    };
    //部门设置选择部门
    colleague.selectSectorDepartment = function(orgName,orgID) {
        $scope.colleague.sectorSuperiorDepName = orgName;
        colleague.setSectorParentId = orgID;
        colleague.departmentCtr = false;
    };
    //部门设置  搜索部门
    colleague.selectDepName = function () {
        colleague.departmentCtr = true;
        ColleagueModel.getOrgInfo($scope,$scope.colleague.sectorSuperiorDepName);
    };
    //部门选择 弹窗
    colleague.SectorDepartmentPoupeCtr = function () {
        ColleagueModel.getOrgInfo($scope,'');
        colleague.departmentCtr = !colleague.departmentCtr;
    };

    
    //设置部门
    colleague.setDepartment = function () {
        var flag = '';
        if(colleague.sectorSuperiorDepName.length<=0){
            alert('上级部门不能为空！');
            return;
        }
        if(colleague.setSectorParentId.length <= 0 && colleague.sectorSuperiorDepName != colleague.higherDepartment){
            alert('上级部门不存在，请重新选择！');
            return;
        }
        if(colleague.sectorDepName.length<=0){
            alert('部门名称不能为空！');
            return;
        }
        if(colleague.sectorDepName != colleague.sectorSettingRemovalDepName){
            flag = 1;
        }
        if(colleague.sectorDepName.length>20){
            alert('部门名称输入不能超过20个字！');
            return;
        }
        if(colleague.sectorDepLeader.length==0 ){
            alert('请选择部门负责人！');
            return;
        }
        ColleagueModel.setSectorDep($scope,colleague.sectorDepName, colleague.setSectorParentId, colleague.sectorDepOrgId, colleague.gMid, flag);
    };
    //解散部门
    colleague.divisionDissolution = function () {
        ColleagueModel.deleteOrgDep($scope.colleague.sectorDepOrgId);
    };
    //部门设置 部门成员搜索
    colleague.searchLeader = function() {
        ColleagueModel.setSectorDepMember($scope,colleague.sectorDepOrgId,$scope.colleague.sectorSearchName);
    };
    //部门成员转移
    colleague.transferDepMemSub = function () {
        if(colleague.sectorSuperiorDepName.length<=0){
            alert('请选择转移部门！');
            return;
        }
        ColleagueModel.TransferDepartmentMem($scope, colleague.transferDepOrgUid, colleague.setSectorParentId);
    };
    //转移部门  部门列表
    colleague.transferDepartment = function(parent_id,$event){
        $http.get('/index.php?r=workmate/workmate/list&parent_group_id=' + parent_id).
        success(function (data, status, headers, config) {
            var html = "<ul id=" + parent_id + ">";
            var noHaveData = [];
            angular.element.each(data.data, function (key, val) {
                if (!val['key']) {
                    noHaveData.push(val['org_id']);
                }
                html += '<li id="s' + val['org_id'] + '">' +
                    '<div class="nav-common nav-allcol" >' +
                    '<span data-ng-click="colleague.getGroup(' + val['org_id'] + ',$event)">' +
                    '<i class="icon-trigon">&#xe60f;</i>' +
                    val['org_name'] +
                    '<span class="member-num">' + val["count"] + '</span>' +
                    '</span>' +
                    '<i isperm pcode="WorkmateMymateEdite" class="icon-trigon add-dep fr" ng-click="colleague.sectorSettings('+ val['org_id'] +')">&#xe607;</i>' +
                    '</div>' +
                    '</li>';
            });
            html += "</ul>";
            var ele = angular.element(html);
            $compile(ele)($scope);
            target.parent().parent().append(ele);
            var div = target.parent();
            var left = parseFloat(div.css('padding-left')) + 15;
            ele.find('div').css('padding-left', left + 'px');
            for (var i = 0; i < noHaveData.length; i++) {
                angular.element('#s' + noHaveData[i]).find('div').css('background', '#fff').find('i:first-child').remove();
            }
        });
    };
//左侧导航下拉控制结束


});