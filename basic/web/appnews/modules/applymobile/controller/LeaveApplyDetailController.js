//请假申请详情

ApplyMod.controller('LeaveApplyDetailCtrl',function($scope,$http,$rootScope,Publicfactory,applyModel,$timeout,leaveApplyModel,$window,$stateParams){
    var apply = $scope.apply = {};
    var apply_param = $scope.apply_param = {};
    var apply_param1 = $scope.apply_param1 = {};

    apply.apply_id = $stateParams.apply_id ? $stateParams.apply_id : 0;
    if(apply.apply_id==0){
        alert("参数错误");
        $window.history.back();
    }

    //表单数据参数
    apply_param.apply_id = '';//申请ID
    apply_param.type = '';//请假类型
    apply_param.begin_time = '';//请假开始时间
    apply_param.end_time = '';//请假结束时间
    apply_param.leave_sum = '';//请假天数
    apply_param.content = '';//详细说明

    //申请详情
    apply.leaveApplyInfo = '';
    //请假类型
    apply.leaveType = '';
    //临时附件存储
    $scope.att = [];

    //审批参数
    apply_param1.comment = '';
    apply_param1.apply_id = apply.apply_id;
    apply_param1.leave_sum = '';//审批通过最后一步

    //获取请假类型
    leaveApplyModel.getLeaveApplyType($scope);

    //获取申请详情
    leaveApplyModel.getLeaveApplyDetail($scope);

    //审批通过
    apply.verify = function(){
        leaveApplyModel.verify($scope);
    }

    //审批驳回
    apply.refuse = function(){
        leaveApplyModel.refuse($scope);
    }


});