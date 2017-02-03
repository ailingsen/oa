//发起请假申请

ApplyMod.controller('LeaveApplyCreateCtrl',function($scope,$http,$rootScope,Publicfactory,applyModel,$timeout,checkModel,leaveApplyModel,FileUploader,$state){
    var apply = $scope.apply = {};
    var apply_param = $scope.apply_param = {};

    //表单数据参数
    apply_param.model_id = 2;//表示请假申请
    apply_param.type = '';//请假类型
    apply_param.begin_time = '';//请假开始时间
    apply_param.end_time = '';//请假结束时间
    apply_param.leave_sum = '';//请假天数
    apply_param.content = '';//详细说明
    apply_param.att = [];//附件

    //请假类型
    apply.leaveType = '';

    //显示年假、调休和带薪病假可使用天数
    apply.typeSum = '';

    $scope.att = [];
    apply.file_root = '';

    $("#masklayer1").show();

    //获取请假类型
    leaveApplyModel.getLeaveApplyType($scope);

    //根据请假类型判断年假、调休和带薪病假可使用天数
    apply.getLeaveApplyTypeSum = function(){
        if(apply_param.type == 1 || apply_param.type ==2 || apply_param.type == 3){
            apply.typeSum = '';
            leaveApplyModel.getLeaveApplyTypeSum($scope);
        }
    }


    $scope.addAttBtn = function(Uploader){
        Uploader.url = "/index.php?r=apply/apply/upload";
        Uploader.onCompleteItem = function (fileItem, response, status, headers) {
            if(response.code==1){
                //response.data.data.file_size = (response.data.data.file_size/1024).toFixed(2);
                $scope.att.push(response.data.data);
                //console.log($scope.att);
                apply.file_root = response.file_root;
            }else if(response.code==0){
                fileItem.remove();
                alert(response.msg);
            }
        }
    }


    //删除附件
    apply.delFileBtn = function(index){
        $scope.att.splice(index,1);
    }

    //保存请假申请
    apply.saveLeaveApplyBtn = function(){
        reg = /^[0-9]\d*(\.\d+)?$/;
        if (!reg.test(apply_param.leave_sum)) {
            alert('请输入正确的休假天数！');
            return false;
        }
        if (apply_param.leave_sum<=0) {
            alert('请输入正确的休假天数！');
            return false;
        }
        if(!((apply_param.leave_sum*10)%5==0)){
            alert('休假必须以0.5天为最小单位！');
         return false;
        }
        apply_param.att = $scope.att;
        if(checkModel.checkStrLen(apply_param.content) > 200) {
            alert('详细说明必须100字以内');
            return;
        }
        leaveApplyModel.createLeaveApply($scope);
    }

    apply.cancelBtn = function(){
        $state.go('^');
        $("#masklayer1").hide();
    }


});