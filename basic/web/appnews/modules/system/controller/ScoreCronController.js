//积分脚本设置
systemMod.controller('scoreCronCtrl',function($scope,$http,$state,$stateParams,scoreModel,$window,noticeService) {
    var score = $scope.score = {};
    score.noticeService = noticeService;
    score.scoreCronlist = [];
    var score_param = $scope.score_param = {};
    var score_param2 = $scope.score_param2 = {};
    //调整周期
    score.cycle = [
        {label: '年', nums:1},
        {label: '半年', nums:2},
        {label: '季度', nums:3},
        {label: '月', nums:4},
        {label: '日', nums:5}
    ];
    //判断是否为整数
    function isInteger(obj) {
        return Math.floor(obj) == obj
    }

    score.selectedCycle1 = score.cycle[0];
    score.selectedCycle2 = score.cycle[0];
    score_param.params = '';
    score_param2.params = '';
    
    scoreModel.viewSet($scope);

    score.saveEdite = function(){
        var score_max_limit = 99999999;
        if (!isInteger($scope.score.scoreCronlist[0].params) || !isInteger($scope.score.scoreCronlist[1].params) || $scope.score.scoreCronlist[0].params < 0 || $scope.score.scoreCronlist[1].params < 0 ) {
            alert('调整数量必须为正整数');
            return;
        }
        if($scope.score.scoreCronlist[0].params > score_max_limit || $scope.score.scoreCronlist[1].params > score_max_limit) {
            alert('调整数量不能大于'+score_max_limit);
            return;
        }
        $scope.score.scoreCronlist[0].run_cycle = $scope.score.selectedCycle1.nums;
        $scope.score.scoreCronlist[1].run_cycle = $scope.score.selectedCycle2.nums;
        scoreModel.saveEditeSet($scope, $scope.score.scoreCronlist);
    }
});