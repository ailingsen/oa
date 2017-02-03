/**
 * Created by pengyanzhang on 2016/8/26.
 */
ColleagueMod.factory('ColleagueModel',function($http,$state,$cookieStore,util){
    var colleagueService = {};
    //全公司所有部门
    colleagueService.getAllDepartment = function($scope,parentGroupId){
        $http.get('/index.php?r=workmate/workmate/list&&parent_group_id='+parentGroupId)
            .success(function(data) {
                $scope.colleague.companyNubSum = data.data[0].count;
                
            });
    };

    //我的团队
    colleagueService.myTeamMemberList = function($scope, orgId,curPage,type) {
        $http.post('/index.php?r=workmate/workmate/get-my-dep-member',{orgId:orgId,curPage:curPage})
            .success(function(data) {
                $scope.colleague.isLeader = orgId ;
                data.data.list = util.setImgRand(data.data.list);
                $scope.myTeamList = data.data;
                $scope.page.sumPage =data.data.totalPage ;
                $scope.page.curPage =$scope.page.tempcurPage ;
                $scope.colleague.memberInfoMess = data.data.list;
                if(type == 1) {
                    angular.forEach($scope.colleague.memberInfoMess, function (one, index) {
                        if (one.org_id == orgId && one.is_manager == 1) {
                            $scope.colleague.sectorDepLeader = one.real_name;
                            $scope.colleague.gMid = one.org_u_id;
                        }
                    });
                }
            });
    };
    colleagueService.myTeamMemberListOne = function($scope, orgId) {
        $http.post('/index.php?r=workmate/workmate/get-my-dep-member',{orgId:orgId})
            .success(function(data) {
                $scope.colleague.isLeader = orgId ;
                data.data.list = util.setImgRand(data.data.list);
                $scope.myTeamList = data.data;
                $scope.colleague.myTeamNubSum = data.data.sum;
                $scope.page.sumPage =data.data.totalPage ;
                $scope.page.curPage =$scope.page.tempcurPage ;
            });
    };
    //左边导航我的团队及人数
    colleagueService.myTeamListSum = function($scope, parentGroupId) {
        $http.post('/index.php?r=workmate/workmate/list',{parent_group_id:parentGroupId})
            .success(function(data) {
                $scope.colleague.myTeamList = data.data;
            });
    };
    //获取所有公司成员
    colleagueService.getAllComMeb= function($scope,$realName){
        $http.post('/index.php?r=workmate/workmate/get-all-members',{realName:$realName})
            .success(function(data) {
                // data.data.list = util.setImgRand(data.data.list);
                $scope.allMembers = data.data;
            });
    };
    
    //获取工作情况相关信息
    colleagueService.getWorkingConditions = function($scope,realName, pageSize, curPage) {
        $http.post('/index.php?r=workmate/workmate/working-situation',{realName:realName,num:pageSize, current:curPage})
            .success(function(data) {
                $scope.WorkingConditions = data.data.work;
                $scope.page.sumPage =data.data.totalPage ;
                $scope.page.curPage =$scope.page.tempcurPage ;
                
            });
    };
    //搜索时用的组信息
    colleagueService.getOrgInfo=function($scope,orgName){
        $http.post('/index.php?r=attendance/attendance/org-info', {search_org_name:orgName})
            .success(function(data) {
                $scope.orgNameList = data.data;
            });
    };

    //添加部门
    colleagueService.addDepartment = function($scope,groups,gname) {
        $http.post('/index.php?r=workmate/workmate/create-org', {groups:groups,gname:gname})
            .success(function(data) {
                $scope.colleague.errorInfo.code = data.code;
                $scope.colleague.errorInfo.msg = data.msg;
                if($scope.colleague.errorInfo.code==0 && $scope.colleague.errorInfo.msg=="该部门已存在"){
                    alert('该部门已存在!');
                    return;
                }else {
                    alert('添加成功！');
                    $state.go('main.colleague.myColleague', {}, {'reload':true});
                }
            });
    };



    //土豪积分榜
    colleagueService.getRichData = function($scope) {
        $http.post('/index.php?r=workmate/workmate/rich-integral')
            .success(function(data) {
                data.data = util.setImgRand(data.data);
                $scope.scoreBoard.richIntegralList = data.data;
            });
    };
    //积分榜
    colleagueService.getIntegral = function($scope, selectData) {
        $http.post('/index.php?r=workmate/workmate/integral',{data:selectData})
            .success(function(data) {
                data.data = util.setImgRand(data.data);
                $scope.scoreBoard.integral = data.data;
            });
    };
    
    //部门设置
    colleagueService.getSectorSettings = function ($scope, orgId) {
        $http.post('/index.php?r=workmate/workmate/org-info',{orgId:orgId})
            .success(function(data) {
                colleagueService.myTeamMemberList($scope,orgId,'',1);
                $scope.colleague.sectorSettingsList = data.data;
                $scope.colleague.sectorSuperiorDepName = $scope.colleague.sectorSettingsList.parent_org_name;
                $scope.colleague.higherDepartment = $scope.colleague.sectorSettingsList.parent_org_name;
                $scope.colleague.sectorDepName = $scope.colleague.sectorSettingsList.org_name;
                $scope.colleague.sectorDepOrgId = $scope.colleague.sectorSettingsList.org_id;
                $scope.colleague.setSectorParentId = $scope.colleague.sectorSettingsList.parent_org_id;
                $scope.colleague.sectorSettingRemovalDepName = $scope.colleague.sectorSettingsList.org_name;
            });
    };
    //设置部门领导人成员列表
    colleagueService.SectorSettingsMemberList = function($scope, orgId, realName,type) {
        $http.post('/index.php?r=workmate/workmate/memberlist',{orgId:orgId,realName:realName,type:type})
            .success(function(data) {
                $scope.colleague.sectorMemberList = data.data.list;
            });
    };
    //部门设置
    colleagueService.setSectorDep = function($scope,groupName, parentId, orgId, gMid, flag) {
        $http.post('/index.php?r=workmate/workmate/update-org',{groupName:groupName,parentId:parentId,orgId:orgId,gMid:gMid,flag:flag})
            .success(function(data) {
                if(data.code==20000){
                    alert(data.msg);
                    $scope.colleague.sectorDepLeader = '';
                    $scope.colleague.gMid = '';
                    var userInfo = $cookieStore.get('userInfo');
                    userInfo.leave_points = data.data;
                    $cookieStore.put('userInfo', userInfo);
                    colleagueService.myTeamMemberList($scope,$scope.colleague.sectorDepOrgId,$scope.page.tempcurPage);
                    $scope.colleague.isShowPartSet=false;
                    $('#masklayer1').hide();
                    //$state.go('main.colleague.myColleague', {}, {'reload':true});
                }else if(data.code==0){
                    alert(data.msg);
                }
            });
    };
    // //设置部门负责人
    // colleagueService.setLeader = function(orgId,gMid) {
    //     $http.post('/index.php?r=workmate/workmate/set-leader',{orgId:orgId,gMid:gMid})
    //         .success(function(data) {
    //         });
    // };
    //转移部门
    colleagueService.deleteOrgDep = function(orgId) {
        $http.post('/index.php?r=workmate/workmate/delete-org',{orgId:orgId})
            .success(function(data) {
                if(data.code==20000){
                    alert(data.msg);
                    $state.go('main.colleague.myColleague', {}, {'reload':true});
                } else if(data.code==0){
                    alert(data.msg);
                    return;
                }
            });
    };
    //转移部门成员
    colleagueService.TransferDepartmentMem = function ($scope,orgUid, orgId) {
        $http.post('/index.php?r=workmate/workmate/transfer-department',{orgUid:orgUid,orgId:orgId})
            .success(function(data) {
                if(data.code==20000){
                    if($cookieStore.get('userInfo').u_id == $scope.colleague.Uid){
                        var userInfo = $cookieStore.get('userInfo');
                        userInfo.org.org_id = orgId;
                        userInfo.leave_points = 0;
                        $cookieStore.put('userInfo', userInfo);
                    }
                    alert("转移成功");
                    $scope.colleague.isShowTransfer = false;
                    $('#masklayer1').hide();
                    colleagueService.myTeamMemberList($scope,$scope.colleague.sectorPageOrgId);
                    // $state.go('main.colleague.myColleague', {}, {'reload':true});
                    // //$state.go('main.colleague.myColleague', {}, {'reload':true});
                } else if(data.code==0){
                    alert(data.msg);
                    return;
                }

            });
    };
    //获取转移部门组织名称
    colleagueService.getTransferDepOrgName = function ($scope,orgUid) {
        $http.post('/index.php?r=workmate/workmate/get-personal-dep-info',{orgUid:orgUid})
            .success(function(data) {
                $scope.colleague.originalDep = data;
            });
    };
    
    //选择部门负责人成员列表
    colleagueService.setSectorDepMember = function ($scope,orgId, searchName, type) {
        $http.post('/index.php?r=workmate/workmate/select-department-member',{orgId:orgId, searchName:searchName,type:type})
            .success(function(data) {
                $scope.colleague.sectorMemberList = data.data;
            });
    };
    return colleagueService;

});