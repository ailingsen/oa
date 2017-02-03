
//我的工作报告
workStatementMod.controller('workDetailCtrl',function($scope,$http,$rootScope,$state,$stateParams,workModel,$timeout){
    var work = $scope.work = {};
    if(!$stateParams.work_id){
        // alert('参数错误');
        // $state.go('^');
        // return false;
    }
    work.work_detail = {};
    work.work_id = $stateParams.work_id;
    $('#masklayer1').show();
    workModel.getWorkDetail($stateParams.work_id, false, $scope);
    
    work.approve = function(){
        workModel.approveWork(work.work_id, $state);
    };

    work.back = function(){
        $('#masklayer1').hide();
        $state.go('^', {}, {'reload' : false});
    }

});



