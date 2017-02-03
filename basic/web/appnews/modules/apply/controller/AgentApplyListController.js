/**
 * Created by nielixin on 2016/9/1.
 */
ApplyMod.controller('AgentApplyListController',function($scope,$http,$state,$rootScope,$stateParams,Publicfactory,applyModel,leaveApplyModel,noticeModel,permissionService){
    if (!permissionService.checkPermission('ApplyMyapprove')) {
        $state.go('main.index', {}, {'reaload':false});
        return false;
    }
    $scope.isAgent = true;
    var apply = $scope.apply = {};
    var applyParams = $scope.applyParams = {};
    var apply_approve = $scope.apply_approve = {};
    //最后一步审批临时存储天数
    apply.tempDay = '';
    apply.status = 0;
    apply.rankDetail = [];
    apply.rankLevel = '';
    applyParams.status = 0;
    applyParams.model_id = 0;
    applyParams.begin = '';
    applyParams.end = '';
    applyParams.serach = '';
    apply.apply_id = '';
    apply.rankDetailPopup = false;
    apply.drainPunchPopup = false;
    //是否显示职级同意弹窗
    apply.showVieryRankPopup = false;
    apply.showRefuseRankPopup = false;
    apply.showVieryLastRankPopup = false;
    //是否显示忘打卡同意弹窗
    apply.showVieryDrainPopup = false;
    apply.showRefuseDrainPopup = false;
    //弹性上班
    apply.flexibleWorkPopup = false;
    apply.flexibleWorkDetailList = [];
    apply.flexibleWorkVieryPopup = false;
    apply.flexibleWorkRefusePopup = false;
    applyParams.page = 1;
    //临时附件存储
    $scope.att = [];
    //附件基础路径
    apply.file_root = '';
    //是否显示批量审批同意窗口
    apply.showVieryWin = false;
    //是否显示批量审批驳回窗口
    apply.showRefuseWin = false;
    //批量审批结果显示窗口
    apply.batchResWin = false;

    apply.level_rank = '';
    apply.comment = '';
    apply.score = 0;
    //是否显示加班申请最后一步窗口
    apply.isOvertimeLastStepWin = false;
    //存储申请的类型  1为定制申请   0为自定义申请
    apply.modeltype = '';

    //审批参数
    //审批申请ID
    apply_approve.apply_id = '';
    //原因
    apply_approve.comment = '';
    //请假最后一步审批填写的天数
    apply_approve.leave_sum = '';
    //加班申请最后一步填写的时间
    apply_approve.real_hours = '';
    //加班申请最后一步填写的开始时间
    apply_approve.begin_time = '';
    //加班申请最后一步填写的开始时间
    apply_approve.end_time = '';

    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };

    //状态值设置
    apply.arrStatus = [
        {'text':'待审批','value':0},
        {'text':'已审批','value':1},
        {'text':'已拒绝','value':2}
    ];
    //是否显示状态下拉列表
    apply.isStatusWin = false;
    //显示状态选中框
    apply.statusWinButton = function () {
        //apply.isStatusWin = !apply.isStatusWin;
        $("#applyType").hide();
        if(angular.element("#projectInfo").is(":hidden")){
            angular.element("#projectInfo").show();
        }else{
            angular.element("#projectInfo").hide();
        }
    }

    //设置选中的查询状态
    apply.selectSearchStatus = function (status) {
        applyParams.status = status;
        angular.element.each(apply.arrStatus, function (key, val) {
            if(val.value==status){
                angular.element('#status').html(val.text);
            }
        });
        apply.isStatusWin = false;
        angular.element("#projectInfo").hide();
    }

    //获取审批单名称选项
    apply.modelList = [];
    applyModel.getApplyModel($scope);
    //显示状态选中框
    apply.modelWinButton = function () {
        //apply.isModelWin = !apply.isModelWin;
        $("#projectInfo").hide();
        if(angular.element("#applyType").is(":hidden")){
            angular.element("#applyType").show();
        }else{
            angular.element("#applyType").hide();
        }
    }

    //设置选中的查询状态
    apply.selectSearchModel = function (modelId) {
        applyParams.model_id = modelId;
        angular.element.each(apply.modelList, function (key, val) {
            if(val.model_id==modelId){
                angular.element('#model').html(val.title);
            }
        });
        apply.isModelWin = false;
        $("#applyType").hide();
    }

    //获取我的申请列表
    applyModel.getMyAgentList($scope);

    //全选
    apply.selected = [];
    apply.selectAll = function($event) {
        apply.selected = [];
        var checkbox = $event.target;
        if(checkbox.checked) {
            angular.forEach(apply.list,function(tmpObj){
                if(tmpObj.allowBatch) {
                    apply.selected.push(parseInt(tmpObj.apply_id));
                }
            });
        }
    }

    //清除全选状态，清空选项值
    apply.clearAll = function() {
        angular.element('#selectAll').prop('checked',false);
        apply.selected = [];
    }

    apply.updateSelected = function(action,id){
        var id = parseInt(id);
        if(action == 'add' && apply.selected.indexOf(id) == -1){
            apply.selected.push(id);
        }
        if(action == 'remove' && apply.selected.indexOf(id)!=-1){
            var idx = apply.selected.indexOf(id);
            apply.selected.splice(idx,1);
        }
    }

    apply.updateSelection = function($event, id){
        var id = parseInt(id);
        var checkbox = $event.target;
        var action = (checkbox.checked?'add':'remove');
        apply.updateSelected(action,id);
    }

    apply.isSelected = function(id){
        var id = parseInt(id);
        return apply.selected.indexOf(id)>=0;
    }

    //批量审批按钮事件
    apply.clickBatchVerify = function() {
        var checkeds = $(".applytablelistbor li .checkbox:checked").length;
        //console.log(checkeds);
        if(checkeds <= 0) {
            alert('请选择申请单');
            return false;   
        }
        apply.showVieryWin=true;
        apply.verifyComment='';
        apply.showmask();
    }

    //批量驳回按钮事件
    apply.clickBatchRefuse = function() {
        var checkeds = $(".applytablelistbor li .checkbox:checked").length;
        //console.log(checkeds);
        if(checkeds <= 0) {
            alert('请选择申请单');
            return;
        }
        apply.showRefuseWin=true;
        apply.refuseComment = '';
        apply.showmask();
    }

    //批量审批
    apply.verifyComment = '';
    apply.verifyBatch = function() {

        if(apply.selected.length <= 0) {
            alert('请选择申请单');
            return;
        }
        if( Publicfactory.checkEnCnstrlen(apply.verifyComment) > 100){
            alert('内容不能超过50个字!');
            return;
        }else{
            applyModel.verifyBatch($scope);
            apply.clearAll();
        }
    }

    //批量驳回
    apply.refuseComment = '';
    apply.refuseBatch = function() {
        
        if(apply.selected.length <= 0) {
            alert('请选择申请单');
            return;
        }
        if('' == apply.refuseComment) {
            alert('请填写驳回意见');
            return;
        }
        if( Publicfactory.checkEnCnstrlen(apply.refuseComment) > 100){
            alert('内容不能超过50个字!');
            return;
        }else{
            applyModel.refuseBatch($scope);
            apply.clearAll();
        }
    }

    //关闭批量审批结果窗口
    apply.closeBatchResWin = function(){
        $scope.apply.successNum = '';
        $scope.apply.failNum = '';
        $scope.apply.failList = [];
        apply.batchResWin = false;
        $("#masklayer1").hide();
    }

    //查询我的申请列表
    apply.search = function() {
        apply.status = applyParams.status;
        apply.clearAll();
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };
        $scope.applyParams.page = 1;
        applyModel.getMyAgentList($scope);
    }

    //翻页方法
    $scope.page_fun = function () {
        apply.clearAll();
        $scope.applyParams.page = $scope.page.tempcurPage;
        applyModel.getMyAgentList($scope);
    };

    //审批查看详情
    apply.detailApply = function(model_id,apply_id,modeltype){
        $("#masklayer1").show();
        apply.modeltype = modeltype;
        switch (model_id){
            case '1'://加班申请
                leave_apply.apply_id = apply_id;
                leave_apply.model_id = model_id;
                applyModel.applyDetail($scope,apply_id);
                apply.overtimeShowWin = true;
                break;
            case '2'://请假申请
                leave_apply.apply_id = apply_id;
                leave_apply.model_id = model_id;
                //获取申请详情
                leaveApplyModel.getLeaveApplyDetail($scope,false);
                break;
            case '3'://忘打卡
                apply.drainPunchPopup = true;
                apply.apply_id = apply_id;
                applyModel.rankDetail($scope,apply.apply_id);
                break;
            case '4'://弹性上班
                apply.flexibleWorkPopup = true;
                apply.apply_id = apply_id;
                applyModel.applyDetail($scope,apply.apply_id);
                break;
            case '5':
                apply.rankDetailPopup = true;
                apply.apply_id = apply_id;
                applyModel.rankDetail($scope,apply.apply_id);
                break;
            default :
                leave_apply.apply_id = apply_id;
                leave_apply.model_id = model_id;
                apply.customShowWin = true;
                apply.apply_id = apply_id;
                applyModel.applyDetail($scope,apply_id,0);
        }
    };

    /*请假申请编辑和查看详情页  开始----------------------------------------------------------------------------------------------------------------------*/

    var leave_apply = $scope.leave_apply = {};
    var apply_param = $scope.apply_param = {};

    //表单数据参数
    apply_param.apply_id = '';//申请ID
    apply_param.type = '';//请假类型
    apply_param.begin_time = '';//请假开始时间
    apply_param.end_time = '';//请假结束时间
    apply_param.leave_sum = '';//请假天数
    apply_param.content = '';//详细说明
    apply_param.att = '';//附件

    //要编辑或查看的申请的ID
    leave_apply.apply_id = '';
    //要编辑或查看的申请的表单类型
    leave_apply.model_id = '';
    //申请详情
    leave_apply.leaveApplyInfo = '';
    leave_apply.typestr = '';//请假类型说明
    //是否显示请假申请编辑页
    leave_apply.isLeaveEditApplyWin = false;
    //是否显示请假申请查看详情页
    apply.isLeaveDetailApplyWin = false;
    //查看详情是否显示操作按钮,如果是则显示同意和驳回按钮
    apply.isDetailBtn = true;
    //是否显示请假申请最后一步同意窗口
    leave_apply.isLeaveLastStepWin = false;
    //最后一步窗口显示的开始时间
    leave_apply.last_begin_time = '';
    //最后一步窗口显示的结束时间
    leave_apply.last_end_time = '';
    //是否显示请假申请审批同意窗口
    leave_apply.isShowVieryWin = false;
    //是否显示请假申请审批驳回窗口
    leave_apply.isShowRefuseWin = false;
    //保存审批意见
    leave_apply.comment = '';

    //请假类型
    apply.leaveType = [];
    //年假、调休和带薪病假可使用的假期天数
    apply.typeSum = '';

    //关闭详情页
    leave_apply.closeEditLeaveApplyBtn = function(){
        leaveApplyModel.closeEditLeaveApply($scope);
        $("#masklayer1").hide();
        leave_apply.isShowRefuseWin=false;
        apply.showVieryDrainPopup= false;
    }

    //下载附件
    apply.downFileBtn = function(obj){
        noticeModel.fileDownload($scope,obj.file_path,obj.real_name,obj.file_name,obj.file_size);
    }

    //显示同意窗口
    leave_apply.openVerifyWin = function(){
        leave_apply.comment = '';
        //是否显示审批同意窗口
        leave_apply.isShowVieryWin = true;
        //是否显示审批驳回窗口
        leave_apply.isShowRefuseWin = false;
    }
    //显示请假申请最后一步窗口
    leave_apply.openLeaveLastStepWin = function(){
        leave_apply.comment = '';
        apply.tempDay = apply_param.leave_sum;
        leaveApplyModel.getLeaveClockTime($scope);
    }

    //点击同意按钮显示同意框
    leave_apply.showAgreeWin = function(){
        //加班申请最后一步填写的时间
        apply_approve.real_hours = '';
        //加班申请最后一步填写的开始时间
        apply_approve.begin_time = '';
        //加班申请最后一步填写的开始时间
        apply_approve.end_time = '';
        //请假申请最后一步窗口显示的开始时间
        leave_apply.last_begin_time = '';
        //请假申请最后一步窗口显示的结束时间
        leave_apply.last_end_time = '';
        if(apply.modeltype == 1){//定制申请
            if(leave_apply.model_id==1){//加班申请
                if($scope.apply.detail.last_step){//最后一步
                    leave_apply.openOvertimeLastStepWin();
                }else{
                    leave_apply.openVerifyWin();
                }
            }else if(leave_apply.model_id==2){//请假申请
                if(leave_apply.leaveApplyInfo.last_step){//最后一步
                    leave_apply.openLeaveLastStepWin();
                }else{
                    leave_apply.openVerifyWin();
                }
            }
        }else{//自定义申请
            leave_apply.openVerifyWin();
        }
    }

    //审批通过申请
    leave_apply.verifyBtn = function(){
        if(leave_apply.model_id==2){//请假申请
            if(!leave_apply.leaveApplyInfo.last_step){//最后一步
                apply_approve.leave_sum = '';
            }else{
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
                apply_approve.leave_sum = apply_param.leave_sum;
            }
        }

        leave_apply.comment = leave_apply.comment.replace(/(^\s*)/g, "");
        // if('' == leave_apply.comment) {
        //     alert('请填写同意原因！');
        //     return false;
        // }
        if (Publicfactory.checkEnCnstrlen(leave_apply.comment) > 100) {
            alert("同意原因不能超过50个汉字！");
            return false;
        }else{
            leave_apply.isLeaveLastStepWin = false;
            apply.isOvertimeLastStepWin = false;
            leave_apply.isShowVieryWin = false;
            apply_approve.apply_id = leave_apply.apply_id;
            apply_approve.comment = leave_apply.comment;
            applyModel.verify($scope);
        }
        
    }
    //显示驳回窗口
    leave_apply.openRefuseWin = function(){
        leave_apply.comment = '';
        //是否显示审批同意窗口
        leave_apply.isShowVieryWin = false;
        //是否显示审批驳回窗口
        leave_apply.isShowRefuseWin = true;
    }
    //审批驳回申请
    leave_apply.refuseBtn = function(){
        
        leave_apply.comment = leave_apply.comment.replace(/(^\s*)/g, "");
        if('' == leave_apply.comment) {
            alert('请填写驳回意见');
            return false;
        }
        if (Publicfactory.checkEnCnstrlen(leave_apply.comment) > 100) {
            alert("驳回意见不能超过50个汉字");
            return false;
        }else{
            leave_apply.isShowRefuseWin = false;
            apply_approve.apply_id = leave_apply.apply_id;
            apply_approve.comment = leave_apply.comment;
            applyModel.refuse($scope);

        }

    }

    /*请假申请编辑和查看详情页  结束-----------------------------------------------------------------------------------------------------------------------------*/

    $scope.addAttBtn = function(Uploader){
        Uploader.url = "/index.php?r=apply/apply/upload";
        Uploader.onCompleteItem = function (fileItem, response, status, headers) {
            if(response.code==1){
                response.data.data.file_size = (response.data.data.file_size/1024).toFixed(2);
                $scope.att.push(response.data.data);
                leave_apply.leaveApplyInfo.file_root = response.file_root;
            }else if(response.code==0){
                fileItem.remove();
                alert(response.msg);
            }
        }
    }

    //删除附件
    apply.delFileBtn = function(index){
        $scope.att.splice(index,1);
    };

    //显示加班申请最后一步窗口
    leave_apply.openOvertimeLastStepWin = function(){
        leave_apply.comment = '';
        apply.tempDay = apply_param.leave_sum;
        applyModel.getOvertimeClockTime($scope);
    }

    apply.popupCtr = function(statusCtr) {
        switch (statusCtr){
            case '1'://加班申请
                break;
            case '2'://请假申请
                break;
            case '3'://忘打卡申请
                apply.drainPunchPopup = false;
                break;
            case '5'://职级申请
                apply.rankDetailPopup = false;
                break;
            case '7'://职级申请同意弹窗
                apply.showVieryRankPopup = true;
                break;
            case '8'://职级申请同意弹窗
                apply.showVieryRankPopup = false;
                break;
            case '9'://职级拒绝弹窗
                apply.showRefuseRankPopup = true;
                break;
            case '10'://职级拒绝取消弹窗
                apply.showRefuseRankPopup = false;
                break;
            case '11':
                apply.showVieryLastRankPopup = true;
                $http.post('/index.php?r=apply/apply/get-rank-level',{detailId:apply.rankDetail.detail_id})
                    .success(function(data) {
                            if(data.code==20000){
                                apply.level_rank = data.data.rank_level;
                            }
                    });
                break;
            case '12':
                apply.showVieryLastRankPopup = false;
                break;
            case '13'://忘打卡同意弹窗
                apply.showVieryDrainPopup = true;
                break;
            case '14'://忘打卡拒绝弹窗
                apply.showRefuseDrainPopup = true;
                break;
            case '15'://忘打卡同意 取消
                apply.showVieryDrainPopup = false;
                break;
            case '16'://忘打卡拒绝 取消
                apply.showRefuseDrainPopup = false;
                break;
        }
        
        if(apply.comment.length>0){
            apply.comment = '';
        }
    };

    apply.popupCtrClose = function(){
        $("#masklayer1").hide();
        apply.showVieryDrainPopup = false;
        apply.showRefuseRankPopup = false;
        apply.showRefuseDrainPopup = false;
        apply.showVieryLastRankPopup = false;

    }

    apply.customHideWin = function(){
         $("#masklayer1").hide();
         leave_apply.isShowRefuseWin = false;
         leave_apply.isShowVieryWin = false;
    }
    //职级审批
    apply.rankVieryCtr = function() {
        apply_approve.apply_id = apply.apply_id;
        apply_approve.score = $scope.apply.score;
        apply_approve.comment = $scope.apply.comment;
        apply_approve.level_rank = $scope.apply.level_rank;
        if(Publicfactory.checkEnCnstrlen(apply_approve.level_rank)<=0){
            alert('请填写职级评定');
            return;
        }
        if( Publicfactory.checkEnCnstrlen(apply_approve.comment) > 100 || Publicfactory.checkEnCnstrlen(apply_approve.comment)==0){
            alert('请输入同意原因，且不能超过50个汉字!');
            return;
        }else{
            applyModel.verify($scope);
        }
        
    };
    apply.rankVieryLastCtr = function(step, last_step) {
        var isNub = /^([1-9]\d*|[0]{1,1})$/;
        apply_approve.apply_id = apply.apply_id;
        apply_approve.score = $scope.apply.score;
        apply_approve.level_rank = $scope.apply.level_rank;
        apply_approve.comment = $scope.apply.comment;
        if(last_step == true){
            if(Publicfactory.checkEnCnstrlen(apply_approve.level_rank) ==0 || Publicfactory.checkEnCnstrlen(apply_approve.level_rank)>20){
                alert('请填写职级评定,且最多只能输入10个字!');
                return;
            }
            if(!isNub.test(apply_approve.score) || apply_approve.score>=100000000){
                alert('请填写积分奖励,且为不能超过100000000的正整数!');
                return;
            }
        } else if(step == 1 && last_step == false){
            if(Publicfactory.checkEnCnstrlen(apply_approve.level_rank) ==0 || Publicfactory.checkEnCnstrlen(apply_approve.level_rank)>20){
                alert('请填写职级评定,且最多只能输入10个字!');
                return;
            }
            if(Publicfactory.checkEnCnstrlen(apply_approve.comment)>100){
                alert('请填写同意原因，且最多只能输入50个字!');
                return;
            }

        } else if(step != 1 && last_step == false){
            if(Publicfactory.checkEnCnstrlen(apply_approve.comment)>100){
                alert('请填写同意原因，且最多只能输入50个字!');
                return;
            }
        }
        applyModel.verify($scope);
    };
    //职级审批拒绝
    apply.rankRefuseCtr = function () {
        apply_approve.apply_id = apply.apply_id;
        apply_approve.comment = $scope.apply.comment;
        if(apply_approve.comment.length<=0 || Publicfactory.checkEnCnstrlen(apply_approve.comment) > 100){
            alert('请填写驳回原因,且不能超过50个字!');
            return;
        }else{
            applyModel.refuse($scope)
        }
        
    }
    //忘打卡同意
    apply.drainVieryCtr = function () {
        apply_approve.apply_id = apply.apply_id;
        apply_approve.comment = $scope.apply.comment;
        if( Publicfactory.checkEnCnstrlen(apply_approve.comment) > 100  ){
            //alert('请输入同意原因，且不能超过50个字!');
            alert('同意原因不能超过50个字!');
            return;
        } else{
            applyModel.verify($scope);
            apply.drainPunchPopup = false;
            apply.showVieryDrainPopup = false;
            $("#masklayer1").hide();
        }
    };
    //忘打卡拒绝
    apply.drainRefuseCtr = function () {
        apply_approve.apply_id = apply.apply_id;
        apply_approve.comment = $scope.apply.comment;
        if(apply_approve.comment.length<=0 || Publicfactory.checkEnCnstrlen(apply_approve.comment) > 100){
            alert('请填写驳回原因,且不能超过50个字!');
            return;
        }else{
            applyModel.refuse($scope)
        }
        
    };
    //弹性工作申请同意
    apply.flexibleWorkComment = '';
    apply.flexibleWorkViery = function () {
        apply_approve.apply_id = apply.apply_id;
        apply_approve.comment = apply.flexibleWorkComment;
        //if( Publicfactory.checkEnCnstrlen(apply_approve.comment) > 100 || Publicfactory.checkEnCnstrlen(apply_approve.comment)==0){
        if( Publicfactory.checkEnCnstrlen(apply_approve.comment) > 100 ){
            alert('请输入同意原因，且不能超过50个字!');
            return;
        }else{
            applyModel.verify($scope);
        }
        
    };
    //弹性工作拒绝
    apply.flexibleWorkRefuseCtr = function () {
        apply_approve.apply_id = apply.apply_id;
        apply_approve.comment = apply.flexibleWorkComment;
        if(apply_approve.comment.length==0 || Publicfactory.checkEnCnstrlen(apply_approve.comment) > 100){
            alert('请填写驳回原因，且不能超过50个字!');
            return;
        }else{
            applyModel.refuse($scope);
        }
        
    };

    //工作台跳转弹窗
    if(parseInt($stateParams.apply_id) && parseInt($stateParams.model_id) && typeof $stateParams.model_type != 'undefined' && $stateParams.model_type != '') {
        apply.detailApply($stateParams.model_id,$stateParams.apply_id,$stateParams.model_type);
    }

    angular.element(document).bind("click",function(event){
    
        if(angular.element(event.target).parents(".selectbor").length==0){
             angular.element(".selectbor  ul").hide();
        } 

    });

    angular.element(".selecttimebor").bind("click",function(event){
        $(".selectbor ul").hide();
    });

    apply.showmask = function(){
       $("#masklayer1").show();
    };

    apply.hidemask = function(){
       $("#masklayer1").hide();
    };

});