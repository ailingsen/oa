/**
 * Created by nielixin on 2016/8/30.
 */
ApplyMod.controller('MyApplyListController',function($scope,$http,$state,$rootScope,$stateParams,Publicfactory,applyModel,leaveApplyModel,noticeModel,filtersModel,checkModel,formshowfactory,permissionService){
    if (!permissionService.checkPermission('ApplyMyapply')) {
        $state.go('main.index', {},{'reload': false});
        return false;
    }
    $scope.isMine = true;
    var apply = $scope.apply = {};
    apply.myDate = new Date();
    var applyParams = $scope.applyParams = {};
    applyParams.status = -1;
    applyParams.model_id = 0;
    applyParams.begin = '';
    applyParams.end = '';
    applyParams.detailRankPagePopup = false;
    applyParams.page = 1;
    apply.curTime =  apply.myDate.valueOf();
    //职级编辑弹窗
    apply.rankPopup = false;
    apply.rankEditPopup = false;
    apply.rankApplyDesc = '';
    apply.rankFiles = [];
    //忘打卡编辑弹窗
    apply.drainPopup = false;
    apply.drainDetailPopup = false;
    apply.drainDesc = '';
    apply.drainCheckAm = false;
    apply.drainCheckPm = false;
    apply.beginTime =  '';
    apply.drainPunchInfo = {};
    //弹性上班
    apply.flexibleWorkPopup = false;
    apply.flexibleDetailWorkPopup = false;
    apply.is_am = 1;
    //临时附件存储
    $scope.att = [];
    //附件基础路径
    apply.file_root = '';
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    //状态值设置
    apply.arrStatus = [
        {'text':'状态','value':-1},
        {'text':'待审批','value':0},
        {'text':'已审批','value':1},
        {'text':'已拒绝','value':2},
        {'text':'已撤回','value':3}
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
        angular.element("#applyType").hide();
    }

    //获取我的申请列表
    applyModel.getMyApplyList($scope);

    //查询我的申请列表
    apply.search = function() {
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };
        $scope.applyParams.page = 1;
        applyModel.getMyApplyList($scope);
    };

    //翻页方法
    $scope.page_fun = function () {
        $scope.applyParams.page = $scope.page.tempcurPage;
        applyModel.getMyApplyList($scope);
    };

    //删除申请弹窗
    apply.delApplyId = 0;
    apply.delApply = function(apply_id) {
        apply.delApplyId = apply_id;
        apply.showDelWin = true;
         $('#masklayer2').show();
    }

    //删除
    apply.doDel = function() {
        applyModel.delApply(apply.delApplyId,$scope);
        apply.showDelWin = false;
        //加班申请详情
        apply.overtimeShowWin = false;
        //忘打卡弹窗
        apply.drainDetailPopup = false;
        //弹性工作
        apply.flexibleDetailWorkPopup = false;
        //职级
        apply.rankPopup = false;
        //自定义
        apply.customShowWin = false;
        //关闭请假申请详情页
        apply.isLeaveDetailApplyWin = false;
    }

    //撤回申请弹窗
    apply.revokeApplyId = 0;
    apply.revoke = function(apply_id) {
        apply.revokeApplyId = apply_id;
        apply.showRevokeWin = true;
        $('#masklayer1').show();
    }

    //撤回
    apply.doRevoke = function() {
        applyModel.revoke(apply.revokeApplyId,$scope);
        apply.rankPopup = false;
        apply.showRevokeWin = false;
        apply.drainDetailPopup = false;
        apply.overtimeShowWin = false;
        apply.flexibleDetailWorkPopup = false;
        apply.customShowWin = false;
        apply.showRevokeWin = false;
        //关闭请假申请详情页
        apply.isLeaveDetailApplyWin = false;
        $('#masklayer1').hide();
    }

    //催办申请
    apply.press = function(apply_id) {
        applyModel.press(apply_id,$scope);
        apply.showRevokeWin = false;

        apply.showDelWin = false;
        //加班申请详情
        apply.overtimeShowWin = false;
        //忘打卡弹窗
        apply.drainDetailPopup = false;
        //弹性工作
        apply.flexibleDetailWorkPopup = false;
        //职级
        apply.rankPopup = false;
        //自定义
        apply.customShowWin = false;
        //关闭请假申请详情页
        apply.isLeaveDetailApplyWin = false;
    }

    //编辑提交参数
    var applyEditParam = $scope.applyEditParam = {};
    applyEditParam.apply_id = 0;

    //编辑
    apply.editApply = function(apply_id,model_id){
        $("#masklayer1").show();
        if(model_id == 2) {
            leave_apply.apply_id = apply_id;
            //获取请假类型
            leaveApplyModel.getLeaveApplyType($scope);
            //获取申请详情
            leaveApplyModel.getLeaveApplyDetail($scope,true);
        }else {
            applyModel.applyDetail($scope,apply_id,1);
            applyEditParam = $scope.applyEditParam = {};
            applyEditParam.apply_id = apply_id;
            model_id = parseInt(model_id);
            switch (model_id){
                case 1://加班申请弹出窗
                    apply.overtimeShowEditWin = true;
                    apply.overtimeShowWin = false;
                    break;
                case 3://忘打卡弹窗
                    //apply.drainDetailPopup = true;
                    apply.drainPopup = true;
                    break;
                case 4://弹性上班
                    //apply.flexibleDetailWorkPopup = true;
                    apply.flexibleWorkPopup = true;
                    break;
                case 5://职级弹窗
                    apply.rankEditPopup = true;
                    break;
                default :   //自定义表单弹窗
                    apply.customShowEditWin = true;
                    apply.customShowWin = false;

                    callback();
            }
        }
    }

    //查看详情
    apply.detailApply = function(apply_id,model_id){
        $("#masklayer1").show();
        if(model_id == 2) {
            leave_apply.apply_id = apply_id;
            //获取申请详情
            leaveApplyModel.getLeaveApplyDetail($scope,false);
        }else {
            applyModel.applyDetail($scope,apply_id,0);
            applyEditParam.apply_id = apply_id;
            switch (model_id){
                case 1://加班申请弹出窗
                    apply.overtimeShowWin = true;
                    break;
                case 3://忘打卡弹窗
                    apply.drainDetailPopup = true;
                    break;
                case 4://弹性上班
                    apply.flexibleDetailWorkPopup = true;
                    break;
                case 5://职级弹窗
                    apply.rankPopup = true;
                    break;
                default ://自定义表单弹窗
                    apply.customShowWin = true;
            }
        }
    }


    apply.customHideWin = function(){
        $("#masklayer1").hide();
        apply.showRevokeWin = false;
    }

    //加班申请提交
    apply.editSubmit = function() {
        applyEditParam.type = apply.detail.data.type;
        //applyEditParam.begin_time = apply.detail.data.begin_time;
        //applyEditParam.end_time = apply.detail.data.end_time;
        applyEditParam.note = apply.detail.data.note;
        applyEditParam.currentDay = apply.detail.data.currentDay;
        applyEditParam.isNextDay = apply.detail.data.isNextDay;
        applyEditParam.begin_time_str = apply.detail.data.begin_time_str;
        applyEditParam.end_time_str = apply.detail.data.end_time_str;

        if(applyEditParam.type == 0) {
            alert('请选择加班类型');
            return;
        }
        /*if('' == applyEditParam.begin_time || '' == applyEditParam.end_time) {
            alert('请选择加班开始结束时间');
            return;
        }
        //时间判断
        var beginDateStr = filtersModel.filterTime(filtersModel.filterStrDate(applyEditParam.begin_time));
        var endDateStr = filtersModel.filterTime(filtersModel.filterStrDate(applyEditParam.end_time));
        if(beginDateStr != endDateStr) {
            alert('加班开始和结束时间必须为同一天');
            return;
        }
        var beginHour = parseInt(filtersModel.filterTimeHH(filtersModel.filterStrDate(applyEditParam.begin_time)));
        var endHour = parseInt(filtersModel.filterTimeHH(filtersModel.filterStrDate(applyEditParam.end_time)));
        if(beginHour >= endHour) {
            alert('加班结束时间必须大于开始时间');
            return;
        }*/
        if('' == applyEditParam.currentDay || '' == applyEditParam.begin_time_str || '' == applyEditParam.end_time_str) {
            alert('请选择加班开始结束时间');
            return;
        }

        var beginDateStr = filtersModel.filterDateTime(applyEditParam.currentDay).replace(/(00:00)/, applyEditParam.begin_time_str);
        var endDateStr = filtersModel.filterDateTime(applyEditParam.currentDay).replace(/(00:00)/, applyEditParam.end_time_str);
        var bDate = new Date(Date.parse(beginDateStr));
        var eDate = new Date(Date.parse(endDateStr))

        if (bDate > eDate && !applyEditParam.isNextDay) {
            alert('同一天加班开始时间不可大于结束时间');
            return;
        }

        if (applyEditParam.isNextDay && applyEditParam.end_time_str > '06:00') {
            alert('加班跨天，结束时间必须小于次日06:00');
            return;
        }
        
        
        //==============杨亮 另写时间判断 开始========================================
        //时间判断
        // var beginDateStr = filtersModel.filterTime(applyEditParam.begin_time);
        // var endDateStr = filtersModel.filterTime(applyEditParam.end_time);
      
  
        //var beginHour = parseInt(filtersModel.filterTimeHH(apply_param.begin_time));
        //var endHour = parseInt(filtersModel.filterTimeHH(apply_param.end_time));
        // if(applyEditParam.begin_time >= applyEditParam.end_time) {
        //     alert('加班结束时间必须大于开始时间');
        //     return;
        // }


        //时分秒格式化
        // var beginDateStr2 = filtersModel.filterDateTime(applyEditParam.begin_time);
        // var endDateStr2 = filtersModel.filterDateTime(applyEditParam.end_time);
        // var d2 = new Date(Date.parse(beginDateStr2));
        // var d1 = new Date(Date.parse(endDateStr2));
        // if ( (d1 - d2)/(1000 * 60 * 60) > 23 ){
        //     alert('加班开始和结束时间必须为同一天,即24小时内！');
        //     return;
        // }
        //======================================================
        //==============杨亮 另写时间判断 结束========================================


        var callBack = function() {
                apply.overtimeShowEditWin = false;
            }

        if('' == applyEditParam.note) {
            alert('请填写加班工作说明');
            return;
        }
        //该验证失效 需要一个汉字字符长度不超过100的验证
        if(Publicfactory.checkEnCnstrlen(applyEditParam.note) > 200) {
            alert('工作说明必须100字以内');
            return false;
        }else{
            apply_param.currentDay = filtersModel.filterTime(apply_param.currentDay);
            applyModel.applyEdit($scope, callBack);
        }

        
    }

    //自定义表单提交
    var closeWin = function() {
        apply.customShowEditWin = false;
    }
    
    //===================
    //===================
    //===================
    apply.abc = function(){
    var group = $scope.group = {};
        $scope.selectedMembers = [];
        $scope.selectedDeparts = [];
        group.memberdialog = false;
        var fields = '',
            currentType = '';
            $scope.att=[];


        //附件，图片上传 公共
        $scope.addFileBtn = function(Uploader, element) {
            Uploader.url = "/index.php?r=apply/apply/upload";
            var ele = element.parents(".borderbor").find("ul");
            
            var filenamelength = ele.find('.filesize'),
                fileaftersize = 0;
            for(var i = 0 ; i<filenamelength.length; i++){
                fileaftersize = fileaftersize + parseFloat(filenamelength.eq(i).html().replace("KB",''));
            }
            
            fileaftersize = fileaftersize.toFixed(2);
            element.parents(".borderbor").find(".none").html(fileaftersize);

            if (fileaftersize > 51200){
                alert("总文件大小已超过上限50MB!");
                return false;
            }else{

                var elesize = element.parents(".borderbor").find(".none").html();
                Uploader.onCompleteItem = function (fileItem, response, status, headers) {
                    if(response.code==1){
                        
                        response.data.data.file_size = (response.data.data.file_size/1024).toFixed(2);
                        elesize = parseFloat(elesize)+parseFloat(response.data.data.file_size);

                        if (elesize > 51200){
                            alert("总文件大小超过50MB，已从上传队列中移除");
                            return false;
                        }
                        else{
                            var file_root = response.file_root;
                            var filefull_path = file_root+'/'+response.data.data.file_path+'/'+response.data.data.real_name;
                            var li  = '<li class="porela">'+
                                            '<i class="poabso icon-'+response.data.data.file_type+'"></i>'+
                                            '<div class="filename fl omit"><a href="'+filefull_path+'" target="_blank">'+response.data.data.file_name+'</a></div>'+
                                            '<div class="filesize fl omit">'+response.data.data.file_size+'KB</div>'+
                                            '<div class="del fr">删除</div>'+
                                      '</li>';
                            ele.append(li);
                            element.parents(".borderbor").find(".none").html(elesize);
                        }
                        //$scope.att.push(response.data.data);
                    }else if(response.code==0){
                        fileItem.remove();
                        alert(response.msg);
                    }

                }
            };
        };
        //添加 部门and人员

        
        //删除附件
        $(document).on('click','.borderbor .del',function(){
            
            $(this).parent().remove();
            var id = $(this).parent().find(".filename").attr("data-member_id");

            $scope.selectedMembers.forEach(function(item, index) {
                if (item.value == parseInt(id)) {
                    $scope.selectedMembers.splice(index, 1);
                }
            });
            $scope.selectedDeparts.forEach(function(item, index) {
                if (item.value == parseInt(id)) {
                    $scope.selectedDeparts.splice(index, 1);
                }
            });

        });

        $scope.addgroup = function($event,index){
           $("#masklayer2").show();
           fields = angular.element($event.target).attr("id");
           currentType = index;
           if(index==0){
                group.departDialogVisble=true;  //是否展示
           }else{
                group.memberDialogVisble=true;  //是否展示
           }
        };
        //关闭弹窗添加已选择的人或部门
        $scope.lishtml = function(i,value,label){
            var li  = '<li class="porela">'+
                           '<div class="filename fl omit" data-member_id="'+value+'">'+label+'</div>'+
                           '<div class="del fr">删除</div>'+
                      '</li>';
            return li;
        };

        $scope.cancels = function(index){
 
            var index = fields;

            group.memberDialogVisble=false;
            $("#masklayer2").hide();

            

            var lis = $('#formpresentation_edit .borderbor').eq(index).find("ul");

           

                var targetArray = currentType ? $scope.selectedMembers : $scope.selectedDeparts;
                //console.log(targetArray,$scope.selectedMembers);

                if(lis.find('li').length<=0){
                    //console.log(000);
                    for( var i = 0; i<targetArray.length; i++){
                         lis.append($scope.lishtml(i,targetArray[i].value,targetArray[i].label));
                    }
                }else{
                    
                    var currentSelectArray = [],
                        selectedArray = [];

                    lis.find("li").each(function(iArray) { 
                         currentSelectArray.push(parseInt(lis.find("li").eq(iArray).find(".filename").attr("data-member_id")));
                    }); 

                    targetArray.forEach(function(item) {
                        selectedArray.push({value:parseInt(item.value), label:item.label});
                    });

                    currentSelectArray = currentSelectArray.sort(function(prev, next) {
                        return next - prev;
                    });

                    selectedArray = selectedArray.sort(function(prev, next) {
                        return next - prev;
                    });

                    //console.log(currentSelectArray,selectedArray);

                    selectedArray.forEach(function(item, index) {
                        //console.log(22343);
                        if (currentSelectArray.indexOf(item.value) == -1) {
                            lis.append($scope.lishtml(undefined, item.value, item.label));
                        }
                    });
               }

            
        };
    
    };

    apply.abc();
    //===================
    //===================
    //===================


    var callback = function () {

        //表单提交保存
        $(".formsave").click(function(){
            if(formshowfactory.formshowsave(JSON.parse($scope.apply.detail.form_json))){
                //拿数据
                var showFormssave = new jQuery.showeditForm();
                var result = showFormssave.saveForm();
                $scope.applyEditParam = result;
                $scope.applyEditParam.apply_id = applyEditParam.apply_id;
                applyModel.applyEdit($scope, closeWin);
            }
        });
    };

    /*请假申请编辑和查看详情页  开始--------------------------------------------------------------------------*/

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
    //申请详情
    leave_apply.leaveApplyInfo = '';
    leave_apply.typestr = '';//请假类型说明
    //是否显示请假申请编辑页
    leave_apply.isLeaveEditApplyWin = false;
    //是否显示请假申请查看详情页
    apply.isLeaveDetailApplyWin = false;
    //查看详情是否显示操作同意和拒绝按钮
    apply.isDetailBtn = false;

    //请假类型
    apply.leaveType = [];
    //年假、调休和带薪病假可使用的假期天数
    apply.typeSum = '';
    //根据请假类型判断年假、调休和带薪病假可使用天数
    leave_apply.getLeaveApplyTypeSum = function(){
        apply.typeSum.sum1 = '';
        apply.typeSum.sum2 = '';
        apply.typeSum.sum3 = '';
        if(apply_param.type == 1 || apply_param.type ==2 || apply_param.type == 3){
            leaveApplyModel.getLeaveApplyTypeSum($scope);
        }
    }

    //编辑保存请假申请
    leave_apply.saveLeaveApplyBtn = function(){
        reg = /^[1-9]\d*(\.\d+)?$/;
        if (!reg.test(apply_param.leave_sum)) {
            alert('请输入正确的休假天数！');
            return false;
        }
        if(!((apply_param.leave_sum*10)%5==0)){
            alert('休假必须以0.5天为最小单位！');
            return false;
        }

        

        if(checkModel.checkStrLen(apply_param.content) > 200) {
            alert('详细说明必须100字以内');
            return false;
        }else{
            apply_param.att = $scope.att;
            leaveApplyModel.editLeaveApply($scope);
        }
        
    }

    //关闭编辑页
    leave_apply.closeEditLeaveApplyBtn = function(){
        leaveApplyModel.closeEditLeaveApply($scope);
        $("#masklayer1").hide();
    }

    //下载附件
    apply.downFileBtn = function(obj){
        noticeModel.fileDownload($scope,obj.file_path,obj.real_name,obj.file_name);
    }

    /*请假申请编辑和查看详情页  结束------------------------------------------------------------------------*/

    $scope.addAttBtn = function(Uploader){
        Uploader.url = "/index.php?r=apply/apply/upload";
        Uploader.onCompleteItem = function (fileItem, response, status, headers) {
            if(response.code==1){
                response.data.data.file_size = (response.data.data.file_size/1024).toFixed(2);
                $scope.att.push(response.data.data);
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

    //职级申请提交
    apply.rankApplySubmit = function() {
        //console.log($scope.apply.rankApplyDesc)
    };


    //添加附件
    apply.addRankFileBtn = function(uploader){
        uploader.url = '/index.php?r=apply/apply/upload';
        uploader.onCompleteItem = function (fileItem, response, status, headers) {
            if(response.code==1){
                response.data.data.file_size = (response.data.data.file_size/1024).toFixed(2);
                apply.rankFiles.push(response.data.data);
                apply.file_root = response.file_root;
                applyEditParam.att = apply.rankFiles;
            }else if(response.code==0){
                fileItem.remove();
                alert(response.msg);
            }
        };

    };

    //删除上传的附件
    apply.rankDelFiles = function(index){
        apply.rankFiles.splice(index,1);
    };
    //职级编辑 提交
    apply.rankEditSubmit = function () {
        var applyEditCancel = function() {
            apply.rankEditPopup  = false;
            $('#masklayer1').hide();
        };
        applyEditParam.note = $scope.apply.rankApplyDesc;
        if(apply.rankApplyDesc == undefined || apply.rankApplyDesc.length == 0){
            alert('请输入职级申请内容！');
            return;
        }

        if(apply.rankApplyDesc != undefined && Publicfactory.checkEnCnstrlen(apply.rankApplyDesc)>1000){
            alert('职级申请内容不能超过500字！');
            return false;
        }
        if(apply.rankFiles.length == 0){
            alert('请添加附件！');
            return false;
        }else{
            applyModel.applyEdit($scope, applyEditCancel);
        }
        
    };
    //编辑弹窗取消按钮
    apply.editPopupCtr = function(popupStatus) {
        switch (popupStatus){
            case '3'://忘打卡编辑
                apply.drainPopup = false;
                apply.drainDetailPopup = false;
                break;
            case '5'://职级编辑
                apply.rankEditPopup = false;
                apply.rankPopup = false;
                break;
            case '6':
                apply.rankPopup = false;
                apply.rankEditPopup = true;
                break;
            case '7'://忘打卡
                apply.drainPopup = true;
                apply.drainDetailPopup = false;
                break;
            case '8'://弹性工作
                apply.flexibleDetailWorkPopup = false;
                apply.flexibleWorkPopup = true;
                break;
            case '9'://
                apply.flexibleDetailWorkPopup = false;
                apply.flexibleWorkPopup = false;
        }
    };
    //忘打卡时间点选择
    apply.drainTimePoint = function(timePoint) {
        apply.is_am = timePoint;
    };
    apply.drainPunchSub = function() {
        apply.drainDesc = $scope.apply.drainDesc;
        if(apply.drainDesc.length==0 || Publicfactory.checkEnCnstrlen(apply.drainDesc)>200){
            alert('请输入忘打卡说明,且最多只能输入100个字！');
            return;
        }
        var applyEditCancel = function() {
            apply.drainPopup = false;
            $('#masklayer1').hide();
        };
        if(apply.beginTime>apply.curTime){
            alert('忘打卡时间超期，请从新选择！');
            return;
        }
        applyEditParam.note = apply.drainDesc;
        applyEditParam.is_am = apply.is_am;
        applyEditParam.check_date = $scope.apply.beginTime;
        console.log(applyEditParam.check_date);
        applyModel.applyEdit($scope, applyEditCancel);
    };

    //弹性上班
    apply.editPopup = false;
    apply.timePoint = [];
    // $scope.flexibleWork = {};
    apply.beginTime = apply.myDate.valueOf();
    apply.endTime = apply.myDate.valueOf();
    apply.workTimePoint = '';
    apply.desc = '';
    apply.files = [];
    apply.info = {};
    apply.id = '';
    $scope.att=[];


    //添加附件
    $scope.addFlexibleBtn = function(uploader){
        uploader.url = '/index.php?r=apply/apply/upload';
        uploader.onCompleteItem = function (fileItem, response, status, headers) {
            if(response.code==1){
                response.data.data.file_size = (response.data.data.file_size/1024).toFixed(2);
                apply.files.push(response.data.data);
                apply.file_root = response.file_root;
            }else if(response.code==0){
                fileItem.remove();
                alert(response.msg);
            }
        };
    
    };

    //删除上传的附件
    apply.delFlexibleFiles = function(index){
        apply.files.splice(index,1);
    };

    //点击提交
    apply.submitFlexibleWorkInfo = function() {
        apply.desc = $scope.apply.desc;
        applyEditParam.begin_time = Date.parse(new Date($scope.apply.beginTime))/1000;
        applyEditParam.end_time = Date.parse(new Date($scope.apply.endTime))/1000;
        applyEditParam.store_id = $scope.apply.flexibleWork.id;
        applyEditParam.att = apply.files;
        applyEditParam.note = apply.desc;
        if(applyEditParam.store_id<=0){
            alert('请选择需抵用的加班时间!');
            return;
        }
        if(applyEditParam.begin_time>=applyEditParam.end_time){
            alert('申请结束时间不能大于等于开始时间，请重新选择！');
            return;
        }
        if(apply.desc.length==0 || Publicfactory.checkEnCnstrlen(apply.desc)>200){
            alert('请输入详细说明,且最多只能输入100个字！');
            return false;
        }else{
            // if(apply.files.length<=0){
            //     alert('请上传附件！');
            //     return;
            // }
            var flexibleWork = function () {
                apply.flexibleWorkPopup = false;
            };
            applyModel.applyEdit($scope, flexibleWork);
            //隐藏关闭窗口
            $("#masklayer1").hide();
            apply.flexibleWorkPopup=false;
        }
    }

    //工作台跳转弹窗
    if(parseInt($stateParams.apply_id) && parseInt($stateParams.model_id)) {
        apply.detailApply($stateParams.apply_id,parseInt($stateParams.model_id));
    }


    angular.element(document).bind("click",function(event){
    
        if(angular.element(event.target).parents(".selectbor").length==0){
             angular.element(".selectbor  ul").hide();
        } 

    });


     apply.showmask = function(){
       $("#masklayer2").show();
    };

    apply.hidemask = function(){
       $("#masklayer2").hide();
    };


     apply.chehuihidemask = function(){
       $("#masklayer1").hide();
    };



});



