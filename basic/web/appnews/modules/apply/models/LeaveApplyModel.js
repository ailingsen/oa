ApplyMod.factory('leaveApplyModel', function($http,$timeout,$cookieStore,$cookies,$state,filtersModel,applyModel){
    var  service={};

    //发布或编辑请假申请时，选择年假、调休或带薪病假时显示可用天数
    service.getLeaveApplyTypeSum=function($scope){
        $http.post('/index.php?r=apply/apply/leave-apply-sum',JSON.stringify($scope.apply_param))
            .success(function(data, status) {
                $scope.apply.typeSum = data.data;
                /*angular.element.each(data.data.proList, function (key, val) {
                 $scope.project.projectlist.push(val);
                 });
                 $scope.project.pageLength = $scope.project.projectlist.length;*/
            });
    };

    //获取请假类型
    service.getLeaveApplyType=function($scope){
        $http.post('/index.php?r=apply/apply/leave-apply-type',{})
            .success(function(data, status) {
                $scope.apply.leaveType = data.data;
                //处理查看申请页要显示的请假类型文字
                if(typeof($scope.leave_apply) != "undefined"){
                    if(typeof($scope.leave_apply.typestr) != "undefined"){
                        angular.element.each($scope.apply.leaveType, function (key, val) {
                            if($scope.apply_param.type == val.statuskey){
                                $scope.leave_apply.typestr = val.statusstr;
                            }
                        });
                    }
                }
            });
    };

    //保存发起请假申请
    service.createLeaveApply=function($scope){
        $http.post('/index.php?r=apply/apply/create-apply',JSON.stringify($scope.apply_param))
            .success(function(data, status) {
                if(data.code == 1){
                    $state.go('main.apply.mine',{apply_id:0,model_id:0});
                }else{
                    alert(data.msg);
                }
            });
    };

    //获取申请详情
    service.getLeaveApplyDetail=function($scope,isEdit){
        $http.get('/index.php?r=apply/apply/show-detail&apply_id='+$scope.leave_apply.apply_id).success(function(data) {
            if(data.code == 1) {
                $scope.apply_param.apply_id = data.data.apply_id;//表示请假申请
                $scope.apply_param.type = data.data.data.type;//请假类型
                $scope.apply_param.begin_time = data.data.data.begin_time;//请假开始时间
                $scope.apply_param.end_time = data.data.data.end_time;//请假结束时间
                $scope.apply_param.leave_sum = data.data.data.leave_sum;//请假天数
                $scope.apply_param.content = data.data.data.content;//详细说明
                $scope.att = [];
                $scope.att = data.data.data.att;//附件
                $scope.leave_apply.leaveApplyInfo = data.data;
                $scope.apply.file_root = data.data.file_root;

                if(isEdit){//编辑申请
                    $scope.leave_apply.isLeaveEditApplyWin = true;
                    $scope.apply.isLeaveDetailApplyWin = false;
                    //获取可用假的天数
                    if($scope.apply_param.type == 1 || $scope.apply_param.type ==2 || $scope.apply_param.type == 3){
                        service.getLeaveApplyTypeSum($scope);
                    }
                }else{//查看详情
                    $scope.apply.isLeaveDetailApplyWin = true;
                    service.getLeaveApplyType($scope);
                }

            }else{
                alert(data.msg);
            }
        });
    };

    //保存编辑请假申请
    service.editLeaveApply=function($scope){
        $http.post('/index.php?r=apply/apply/edit-apply',JSON.stringify($scope.apply_param))
            .success(function(data, status) {
                if(data.code == 1){
                    service.closeEditLeaveApply($scope);
                    applyModel.getMyApplyList($scope);
                    alert(data.msg);
                }else{
                    alert(data.msg);
                }
            });
    };

    //关闭编辑页面
    service.closeEditLeaveApply = function($scope){
        //清空表单数据
        $scope.apply_param.apply_id = '';//申请ID
        $scope.apply_param.type = '';//请假类型
        $scope.apply_param.begin_time = '';//请假开始时间
        $scope.apply_param.end_time = '';//请假结束时间
        $scope.apply_param.leave_sum = '';//请假天数
        $scope.apply_param.content = '';//详细说明
        $scope.apply_param.att = '';//附件
        $scope.att = [];

        $scope.leave_apply.isLeaveEditApplyWin = false;
        $scope.apply.isLeaveDetailApplyWin = false;

        $("#masklayer1").hide();

        if(typeof($scope.leave_apply.typestr) != "undefined"){
            $scope.leave_apply.typestr = '';
        }
    }

    //获取请假申请打卡时间
    service.getLeaveClockTime=function($scope){
        $http.post('/index.php?r=apply/apply/leave-clock-time',{apply_id:$scope.leave_apply.apply_id,date:$scope.apply_param.begin_time})
            .success(function(data, status) {
                if(data.code == 1){
                    $scope.leave_apply.last_end_time = data.data.offTime;
                    $scope.leave_apply.last_begin_time = data.data.onTime;
                    $scope.leave_apply.isLeaveLastStepWin = true;
                }else{
                    alert(data.msg);
                }
            });
    };

    service.closeMark = function($scope){
        $("#masklayer1").hide();
        //是否显示请假申请查看详情页
        $scope.apply.isLeaveDetailApplyWin = false;
    }

    return service;
});
