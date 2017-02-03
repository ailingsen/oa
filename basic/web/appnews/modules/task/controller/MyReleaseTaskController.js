//我发布的任务
TaskMod.controller('myReleaseTaskCtr',function($scope,$rootScope,$cookieStore,$stateParams,$timeout,$http,myReleaseTaskModel,myTaskModel,taskModel,doTaskModel,$state,Publicfactory,noticeService){
    var taskObj = $scope.taskobj={};
    taskObj.cookieUid = $cookieStore.get('userInfo').u_id;
    taskObj.charge = '';
    taskObj.operationLogList = [];
    taskObj.operationLog = false;
    taskObj.proId = '';
    taskObj.taskType = '';
    taskObj.status = '';
    taskObj.btime = '';
    taskObj.etime = '';
    taskObj.taskTitle = '';
    taskObj.overtime = '';
    taskObj.taskTypeName = '类型';
    taskObj.taskStatusName = '状态';
    taskObj.taskProName = '项目';
    taskObj.taskProCtr = false;
    taskObj.num = '';
    taskObj.current = '';
    taskObj.currentPage = '';
    taskObj.noticeService = noticeService;
    $scope.myReleaseTaskList = [];
    $scope.taskDetail = [];
    $scope.myObject = [];
    $scope.refuse_reason = '';
    $scope.showPass = true;
    $scope.showCheck = false;
    $scope.isShow = true;
    $scope.detailPopWin = false;
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    //星级评价
    taskObj.starArr = [1,2,3,4,5];
    taskObj.is_quality_select = false;
    taskObj.is_speed_select = false;
    taskObj.quality_score = 0; // 完成质量值
    taskObj.speed_score = 0;   //完成速度值
    
    myReleaseTaskModel.getProjectInfo($scope,2);
    myReleaseTaskModel.getReleaseTaskList($scope,taskObj.proId,taskObj.taskType,taskObj.status,taskObj.btime ,taskObj.etime,taskObj.taskTitle,taskObj.overtime,taskObj.num,taskObj.current);

    taskObj.selectProWin = function(a) {
        taskObj.aaa=[{a:1,b:'ceshi'},{'a':2,b:'sdfsdfsd'}];
    };
    taskObj.timestamp3 = new Date().getTime();
    //操作日志弹窗
    taskObj.operationLogPopup = function(taskId) {
        taskObj.operationLog = true;
        myTaskModel.getOperationTask($scope,taskId, 1);
    };

    taskObj.releaseTaskSearch = function () {
        if($scope.taskobj.searchstarttime){
            //任务开始时间
            taskObj.btime = Date.parse($scope.taskobj.searchstarttime)/1000;
        }else {
            taskObj.btime = '';
        }
        if($scope.taskobj.searchendtime){
            //任务结束时间
            taskObj.etime = Date.parse($scope.taskobj.searchendtime)/1000;
        }else {
            taskObj.etime  = '';
        }

        if(taskObj.btime>taskObj.etime && taskObj.etime && taskObj.btime){
            alert('开始时间不能大于结束时间！');
            return;
        }
        
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };
        
        //任务标题
        taskObj.taskTitle = $scope.taskobj.taskTitle;
        
        if(taskObj.status==7){
            taskObj.overtime = 7;
            taskObj.status = '';
        }
        myReleaseTaskModel.getReleaseTaskList($scope,taskObj.proId, taskObj.taskType, taskObj.status, taskObj.btime, taskObj.etime, taskObj.taskTitle,taskObj.overtime,taskObj.num,taskObj.current);
    };
    //相关项目选择
    taskObj.getSelectPro=function(proId,proName) {
        taskObj.proId = proId;
        taskObj.taskProName = proName;
        angular.element("#projectInfo").hide();
    };
    //项目下拉状态控制
    taskObj.showStatus=function(){
        $("#releaseTaskStatus,#releaseTaskStatus").hide();
        if(angular.element("#projectInfo").is(":hidden")){
            angular.element("#projectInfo").show();
        }else{
            angular.element("#projectInfo").hide();
        }
    };
    //我发布的任务分页
    $scope.taskPaging = function(){
        taskObj.currentPage = $scope.page.tempcurPage;
        myReleaseTaskModel.getReleaseTaskList($scope,taskObj.proId,taskObj.taskType,taskObj.status,taskObj.btime ,taskObj.etime,taskObj.taskTitle,taskObj.overtime,taskObj.num,taskObj.currentPage);
    };

    //我发布的任务详情
    taskObj.taskDetail = function(taskId) {
        myReleaseTaskModel.getReleaseTaskDetailInfo($scope,taskId);
        $scope.detailPopWin = true;
        $scope.showCheck = false;
        $('#masklayer1').show();
    };

    //如果传了task_id,打开详情弹窗
    if ($stateParams.task != undefined && $stateParams.task != '') {
        taskObj.taskDetail($stateParams.task )
    }

    //隐藏弹窗
    taskObj.taskDetailPopup = function() {
        $scope.detailPopWin = false;
        $scope.showCheck = false;
        $('#masklayer1').hide();
    };

    taskObj.showReleaseTaskType = function () {
        // angular.element("#releaseTaskType").show();

        $("#releaseTaskStatus,#projectInfo").hide();
        if(angular.element("#releaseTaskType").is(":hidden")){
            angular.element("#releaseTaskType").show();
        }else{
            angular.element("#releaseTaskType").hide();
        }  
    };
    taskObj.selectReleaseTaskType = function (taskTypeName,taskType) {
        taskObj.taskTypeName = taskTypeName;
        taskObj.taskType = taskType;
        angular.element("#releaseTaskType").hide();
    };
    //任务完成状态
    taskObj.showReleaseStatusName = function () {
        // angular.element("#releaseTaskStatus").show();

        $("#releaseTaskType,#projectInfo").hide();
        if(angular.element("#releaseTaskStatus").is(":hidden")){
            angular.element("#releaseTaskStatus").show();
        }else{
            angular.element("#releaseTaskStatus").hide();
        }  
    };
    //任务完成状态
    taskObj.selectReleaseStatusName = function (taskStatusName,status) {
        taskObj.taskStatusName = taskStatusName;
        taskObj.status = status;
        angular.element("#releaseTaskStatus").hide();
    };
    //审核通过选择框
    taskObj.selectPass = function(){
        $scope.showPass = true;
        // $scope.showCheck = false;
        angular.element('#show_not_pass').removeClass('selected');
        angular.element('#show_pass').addClass('selected');

    }
    //审核通过选择框
    taskObj.selectNotPass = function(){
        $scope.showPass = false;
        // $scope.showCheck = true;
        taskObj.refuse_reason = '';
        angular.element('#show_not_pass').addClass('selected');
        angular.element('#show_pass').removeClass('selected');
    }
    //星级评价
    function starpj(item,str,$event){
        var parentdiv;
        if ('quality' == str) {
            parentdiv = angular.element("#qualityStar");
        } else {
            parentdiv = angular.element("#speedStar");
        }
        for(var i=0; i<item; i++){
            parentdiv.find("li").eq(i).find('i').addClass("selected");
        }
        for(var i=item; i<5; i++){
            parentdiv.find("li").eq(i).find('i').removeClass("selected");
        }
        if(str=="quality"){taskObj.quality_score = item;}
        if(str=="speed"){taskObj.speed_score = item;}
    }

    taskObj.mouseoverhand = function(item,str,$event){
        if(taskObj.is_quality_select== false){ starpj(item,str,$event);}
    };
    taskObj.mouseoverhand2 = function(item,str,$event){
        if(taskObj.is_speed_select== false){ starpj(item,str,$event); }
    };

    //离开质量评价区域
    taskObj.mleavequality = function(str){
        if(taskObj.is_quality_select==false){
            taskObj.quality_score = 0;
            angular.element("#qualityStar li").find('i').removeClass("selected");
        }
    };
    taskObj.mleavequality2 = function(str){
        if(taskObj.is_speed_select==false){
            taskObj.speed_score = 0;
            angular.element("#speedStar li").find('i').removeClass("selected");
        }
    };

    //点击选中赋值
    taskObj.selectedhand = function(item,str,$event){
        starpj(item,str,$event);
        if(str=="quality"){ taskObj.is_quality_select = true;}
        if(str=="speed"){ taskObj.is_speed_select = true;}
    };
    
    taskObj.refuse_reason = '';
    //拒绝通过，重新指派
    var redo = function (taskId){
        if( Publicfactory.checkEnCnstrlen(taskObj.refuse_reason) > 100 || taskObj.refuse_reason.length<=0 || taskObj.refuse_reason.trim()=='' || typeof taskObj.refuse_reason == 'undefined'){
            alert('拒绝通过理由不能大于50个字且不能为空');
            return false;
        }else{
            taskObj.refuse_reason=taskObj.refuse_reason.replace(/\n/g,"<br/>");
            taskObj.refuse_reason=taskObj.refuse_reason.replace(/\s/g,"&nbsp;");
            var rePointer = '';
            if($scope.selectedMember.u_id != undefined){
                rePointer = $scope.selectedMember.u_id;
            }
            doTaskModel.redoTask(taskId,taskObj.refuse_reason,rePointer,$scope);
        }
    }
    //审核
    taskObj.check = function(isShow) {
        $scope.selectedMember = {};
        $scope.showCheck = isShow;
        $timeout(function(){
            var scrollTopH = $("#taskDetailPopup .scrollbor");
            scrollTopH.scrollTop(5000);
        });
        taskObj.quality_score = 0;
        taskObj.speed_score = 0;
        angular.element(".taskdetailwin .revieweditbor li i").removeClass("selected");
    }
    //评价确认提交
    taskObj.submitViews = function(taskId){
        var scrollborgo = angular.element(".taskdetailwin .scrollbor");
        scrollborgo.scrollTop(scrollborgo.height()+1000);
        if (!$scope.showPass) {
            redo(taskId);
            return;
        }
        var data = {};
        if($scope.taskobj.cookieUid != $scope.taskobj.charge){
            if(!taskObj.quality_score || !taskObj.speed_score){
                angular.element('#qualityStar').focus();
                alert('请选择质量和速度评分');
                return;
            }
            data.quality = taskObj.quality_score; // 完成质量值
            data.speed = taskObj.speed_score;   //完成速度值
        }else {
            data.quality = 0;
            data.speed =0;
        }
        doTaskModel.doneTask(taskId,data,2,$scope);

        taskObj.quality_score = 0;
        taskObj.speed_score = 0;
        angular.element(".taskdetailwin .revieweditbor li i").removeClass("selected");

    };

    //指派人
    var task = $scope.task = {};
    $scope.task.groupMembers = [];
    taskObj.searchMemberRealName = '';
    $scope.selectedMember = {}
    $scope.task.selectedMember = {};
    //指派任务成员

    //打开选人
    taskObj.openSelectedP=function(){
        $(".menbersearchbor").show();
        if( $scope.task.groupMembers.length == 0)
            taskModel.getMembers($scope, '');
    }

    taskObj.closeSelectedP=function() {
        $(".menbersearchbor").hide();
        $scope.searchMemberRealName='';
    }

    var timer='';
    taskObj.allott=function(issearch){
        clearTimeout(timer);

        //获取所有的组织架构和人员
        timer=setTimeout(function(){
            var search='';
            if(issearch==1){
                search=taskObj.searchMemberRealName;
            }
            if(issearch!=1){
                search='';
            }
            taskModel.getMembers($scope, search);
        },500);
    };
    taskObj.selectMember=function($selecedMember){
        $scope.selectedMember = $selecedMember;
        taskObj.closeSelectedP();
    }
    taskObj.delmember=function(){
        $scope.selectedMember = {};
    }

    //编辑任务
    taskObj.editeTask = function(taskId,taskType){
        $state.go('main.task.myReleaseTask.edite', {'task_id':taskId, 'task_type':1,'taskType':taskType}, {'reload':true});
    }

    //关闭任务
    $scope.pointcloseWin = false;
    taskObj.shutDownTask = function(taskId){ 
        $('#masklayer2').show();
        $scope.pointcloseWin = true;
    }
    //关闭任务弹窗
    taskObj.shutDownTaskCloseWin = function(){
        $('#masklayer2').hide();
        $scope.pointcloseWin = false;
    }
    //关闭任务处理数据
    taskObj.shutDownTaskdata = function(taskId){
        $('#masklayer2').hide();
        $scope.pointcloseWin = false;
        doTaskModel.closeTask(taskId, 1, $scope);
    }



    //删除任务
    // taskObj.deleteTask = function(taskId){
    //     doTaskModel.deleteTask(taskId, 1, $scope);
    // }

    $scope.deletecloseWin = false;
    $scope.deletecloseWinindex = '';
    taskObj.deleteTask = function(taskId){
        $('#masklayer2').show();
        $scope.deletecloseWin = true;
        $scope.deletecloseWinindex = taskId;
    }
    taskObj.deleteTaskgo = function(){
        doTaskModel.deleteTask($scope.deletecloseWinindex, 1, $scope);
        $('#masklayer2').hide();
        $scope.deletecloseWin = false;
    }
    taskObj.deleteTaskexit = function(){
        $('#masklayer2').hide();
        $scope.deletecloseWin = false;
    }




    //延期任务
    $scope.showDelay = false;
    $scope.delay_reason = '';
    $scope.delaytime = '';
    taskObj.showDelay = function(show){
        $scope.showDelay = show;
        if (show) {
            $("#masklayer2").show();
        } else {
            $("#masklayer2").hide();
        }
        $scope.delaytime = '';
        $scope.delay_reason = '';
    }
    taskObj.delayTask = function(taskId){
        var delayTime = angular.element('#delaytime').val();
        var timestamp = Date.parse(new Date(delayTime));
        timestamp = timestamp / 1000;
        if(delayTime == undefined || delayTime == ''){
            alert('请选择延迟时间');
            return false;
        }
        if(timestamp < $scope.taskDetail.end_time){
            alert('延期操作时间必须大于原定结束时间');
            return false;
        }
        if(timestamp < $scope.taskDetail.delay_time){
            alert('延期操作时间必须大于上次延期时间');
            return false;
        }
        if (Publicfactory.checkEnCnstrlen($scope.delay_reason) <= 0) {
            alert('延期原因不能为空');
            return false;
        }
        if (Publicfactory.checkEnCnstrlen($scope.delay_reason) > 100) {
            alert('延期任务原因长度不能大于50个字');
            return false;
        }else{
            doTaskModel.delayTask(taskId, 1, $scope.delaytime, $scope.delay_reason,$scope);
        }
    }
    //发布任务
    taskObj.publishTask = function(taskId){
        doTaskModel.publishTask(taskId, 1, $scope);
    }

    angular.element(document).bind("click",function(event){

        if(angular.element(event.target).parents(".selectbor ").length==0){
             angular.element(".selectbor  ul").hide();
        } 

    });


    angular.element(".selecttimebor").bind("click",function(event){
        $(".selectbor ul").hide();
    });


});