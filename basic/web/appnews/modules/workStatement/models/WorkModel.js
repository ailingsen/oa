TaskMod.factory('workModel', function($http,$timeout,$cookieStore,$cookies,$state,filtersModel){
    var  service={};

    //获取我的工作报告
    service.getMyWorkList=function($scope){
        $http.post('/index.php?r=work/work/my-work', JSON.stringify($scope.param_work))
            .success(function(data, status) {
                $scope.work.workList = data.data.list;
                $scope.page.curPage = data.data.page;
                $scope.page.sumPage = data.data.total_page;
            });
    };
    //获取审阅工作报告
    service.getApproveList=function($scope){
        $http.post('/index.php?r=work/work/work-approvelist', JSON.stringify($scope.param_work))
            .success(function(data, status) {
                $scope.work.workList = data.data.list;
                $scope.page.curPage = data.data.page;
                $scope.page.sumPage = data.data.total_page;
            });
    };
    

    //搜索时用的用户信息
    service.getUserInfo=function($scope){
        $http.post('/index.php?r=work/work/member-list', {search_real_name:$scope.work.search_real_name})
            .success(function(data, status) {
                if (!$scope.work.search_real_name) {
                    $scope.work.allMem = data.data;
                }
                $scope.work.userInfo = data.data;
                $scope.work.isMemWin = true;
            });
    };

    //修改工作报告
    service.updateWork=function($scope, $state){
        
        $http.post('/index.php?r=work/work/update-work', JSON.stringify($scope.param_work))
            .success(function(data, status) {
                if(data.code==20000){
                    $scope.work.editeSuccess = true;
                    $state.go('main.workStatement.myWorkStatementTable', {}, {"reload": true});
                }else{
                    alert(data.msg);
                }
            });
    };

    //新增工作报告
    service.addWork=function($scope, $state){
        $http.post('/index.php?r=work/work/add-work', JSON.stringify($scope.param_work))
            .success(function(data, status) {
                if(data.code==20000){
                    $scope.work.addSuccess = true;

                    var timeout=setTimeout(function() {
                        $state.go('main.workStatement.myWorkStatementTable', {}, {"reload": true});
                    }, 1000);
                    if (timeout) $timeout.cancel(timeout);
                }else{
                    alert(data.msg);
                }
            });
    };

    //审阅工作报告
    service.approveWork=function(workId, $scope){
        $http.post('/index.php?r=work/work/approve-work', {work_id:workId,commit_time:$scope.work.work_detail.commit_time})
            .success(function(data, status) {
                if(data.code==20000){
                    alert(data.msg);
                    //获取审阅工作报告列表
                    // service.getWorkDetail(workId, false, $scope);
                    $scope.work.work_detail.status = 2;
                    service.getApproveList($scope);
                }else{
                    alert(data.msg);
                    service.getWorkDetail(workId, false, $scope);
                }
            });
    };

    //工作报告详情
    service.getWorkDetail=function(workId, isAdd, $scope){
        $http.post('/index.php?r=work/work/detail', {'work_id':workId, 'is_add':isAdd})
            .success(function(data, status) {
                $scope.work.work_detail = data.data;
                if (data.data.status == 0 && !isAdd) {
                    $state.go('main.workStatement.myWorkStatementTable.write', {'work_id': data.data.work_id}, {"reload": true});
                }
                $scope.work.work = data.data.work_content;
                $scope.work.plan = data.data.plan_content;
                $scope.work.work_id = data.data.work_id;
            });
    };

    return service;
});
