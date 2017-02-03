systemMod.factory('scoreModel',function($http,$state){
    var scoreService = {};
    //搜索时用的组信息
    scoreService.getOrgInfo=function($scope){
        $http.post('/index.php?r=attendance/attendance/org-info', {search_org_name:$scope.score.search_org_name})
            .success(function(data, status) {
                $scope.score.orgInfo = data.data;
            });
    };

    //获取积分列表
    scoreService.getScoreList=function($scope){
        $http.post('/index.php?r=management/score/scorelist', JSON.stringify($scope.score_param))
            .success(function(data, status) {
                $scope.score.scoreList = data.data.list;
                $scope.page.curPage = data.data.page;
                $scope.page.sumPage = data.data.total_page;
            });
    };

    //修改积分
    scoreService.addScore = function ($scope, $state){
        $http.post('/index.php?r=management/score/add-point', JSON.stringify($scope.score_param))
            .success(function(data,status) {
                if(data.code==20000){
                    $scope.score.isAdjustWin = false;
                    scoreService.getScoreList($scope);
                    $scope.score_param.u_id = [];
                    $scope.score_param.points = 0;
                    $scope.score_param.reason = '';
                    angular.element('.personalNano_table').find(':checkbox').prop("checked", false);
                    $("#masklayer1").hide();
                }else{
                    alert(data.msg);
                }
            })
    }

    //查看详情
    scoreService.viewLog = function ($scope, score_param){
        $http.post('/index.php?r=management/score/log-list', JSON.stringify(score_param))
            .success(function(data,status) {
                if(data.code==20000){
                    $scope.score.logList = data.data;
                }else{
                    alert(data.msg);
                }
            })
    }

    //获取部门积分列表
    scoreService.getGroupScoreList=function($scope){
        $http.post('/index.php?r=management/score/group-scorelist', JSON.stringify($scope.score_param))
            .success(function(data, status) {
                $scope.score.scoreList = data.data.list;
                $scope.page.curPage = data.data.page;
                $scope.page.sumPage = data.data.total_page;
            });
    };

    //修改部门积分
    scoreService.addGroupScore = function ($scope, $state){
        $http.post('/index.php?r=management/score/add-grouppoint', JSON.stringify($scope.score_param))
            .success(function(data,status) {
                if(data.code==20000){
                    scoreService.getOrgInfo($scope);
                    $scope.score.isAdjustWin = false;
                    scoreService.getGroupScoreList($scope);
                    $scope.score_param.org_ids = [];
                    $scope.score_param.points = 0;
                    $scope.score_param.reason = '';
                    angular.element('.personalNano_table').find(':checkbox').prop("checked", false);
                    $("#masklayer1").hide();
                }else if(data.code==20002){
                    alert(data.msg);
                }else if(data.code==20006){
                    $scope.score.isAdjustWin = false;
                    scoreService.getGroupScoreList($scope);
                    $scope.score_param.org_ids = [];
                    $scope.score_param.points = 0;
                    $scope.score_param.reason = '';
                    angular.element('.personalNano_table').find(':checkbox').prop("checked", false);

                    $scope.score.isErrorWin = true;
                    $scope.score.error_info.list = data.error_list;
                    $scope.score.error_info.success = data.success;
                    $scope.score.error_info.failed = data.failed;
                }else{
                    alert(data.msg);
                }
            })
    }

    //查看详情
    scoreService.viewSet = function ($scope){
        $http.get('/index.php?r=management/score/viewset')
            .success(function(data,status) {
                if(data.code==20000){
                    $scope.score.scoreCronlist = data.data;
                    angular.forEach($scope.score.cycle, function(cycle, index){
                       if (cycle.nums == $scope.score.scoreCronlist[0].run_cycle) {
                           $scope.score.selectedCycle1 = $scope.score.cycle[index];
                       }
                        if (cycle.nums == $scope.score.scoreCronlist[1].run_cycle) {
                            $scope.score.selectedCycle2 = $scope.score.cycle[index];
                        }
                    });
                }else{
                    alert(data.msg);
                }
            })
    }

    //修改积分脚本设置
    scoreService.saveEditeSet = function ($scope, param){
        $http.post('/index.php?r=management/score/set-scorecron', JSON.stringify(param))
            .success(function(data,status) {
                if(data.code==20000){
                    alert(data.msg);
                }else{
                    alert(data.msg);
                }
            })
    }
    return scoreService;


});
