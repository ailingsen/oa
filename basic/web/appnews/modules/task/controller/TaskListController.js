var TaskMod=angular.module('TaskMod',[]);
//我接受的任务
TaskMod.controller('myTaskCtr',function($scope,$http,myTaskModel,doTaskModel,$cookieStore,$state,$stateParams,Publicfactory,permissionService){
    if (!permissionService.checkPermission('TaskMytask')) {
        $state.go('main.index', {},{'reload': false});
        return false;
    }
    var taskObj = $scope.taskobj={};
    taskObj.cookieUid = $cookieStore.get('userInfo').u_id;
    taskObj.operationLogList = [];
    taskObj.operationLog = false;
    //翻页对象
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    taskObj.proId = '';
    taskObj.taskType = '';
    taskObj.btime = '';
    taskObj.etime = '';
    taskObj.taskTypeName = '类型';
    taskObj.taskProCtr = false;
    taskObj.taskStatusName = '状态';
    taskObj.taskProName = '项目';
    taskObj.status = '';
    taskObj.taskTitle = '';
    taskObj.overtime = '';
    taskObj.pagesize = '';
    taskObj.current = '';
    $scope.myTaskList = [];
    $scope.taskDetail = [];
    $scope.myObject = [];
    $scope.taskDetail.workNoteFiles = [];
    taskObj.taskDetailWin = false;
    //临时存储任务类型
    $scope.temp_task_type=1;
    //星级评价
    taskObj.starArr = [1,2,3,4,5];
    //提交任务工作笔记
    $scope.taskDetail.work_note ='';

    myTaskModel.getProjectInfo($scope,2);
    myTaskModel.getMyTaskList($scope, taskObj.proId, taskObj.taskType, taskObj.status, taskObj.btime, taskObj.etime,taskObj.taskTitle, taskObj.overtime, taskObj.pagesize, taskObj.current);

    //我接受的任务列表详情页面
    taskObj.taskDetail = function(taskId) {
        myTaskModel.getTaskDetailInfo($scope,taskId);
        // angular.element("#taskDetailPopup").show();
        
    };
    //隐藏弹窗
    taskObj.taskDetailPopup = function() {
        // angular.element("#taskDetailPopup").hide();
        $("#masklayer1").hide();
        taskObj.taskDetailWin = false;
    };


    //操作日志弹窗
    taskObj.operationLogPopup = function(taskId) {
        taskObj.operationLog = true;
        myTaskModel.getOperationTask($scope, taskId,1);
    };

    //如果传了task_id,打开详情弹窗
    if ($stateParams.task_id != undefined && $stateParams.task_id != '') {
        taskObj.taskDetail($stateParams.task_id )
    }
    taskObj.showTaskTypeis = true;
    taskObj.showTaskType = function ($event) {
        //angular.element(".selectbor  ul").hide();
        //var abc = angular.element($event.target).parent().parent().find("ul");
        $("#taskStatus,#projectInfo").hide();
        if(angular.element("#taskType").is(":hidden")){
            angular.element("#taskType").show();
        }else{
            angular.element("#taskType").hide();
        }  
    };
    taskObj.selectTaskType = function (taskTypeName,taskType) {
        taskObj.taskTypeName = taskTypeName;
        taskObj.taskType = taskType;
        angular.element("#taskType").hide();
    };

    //任务完成状态、
    taskObj.showStatusNameis = true;
    taskObj.showStatusName = function () {
        //angular.element(".selectbor  ul").hide();
        $("#taskType,#projectInfo").hide();
        if(angular.element("#taskStatus").is(":hidden")){
            angular.element("#taskStatus").show();
        }else{
            angular.element("#taskStatus").hide();
        }
    };
    //任务完成状态
    taskObj.selectStatusName = function (taskStatusName,status) {
        taskObj.taskStatusName = taskStatusName;
        taskObj.status = status;
        angular.element("#taskStatus").hide();
    };
    //
    taskObj.proStatusCtr = function() {
        taskObj.taskProCtr = false;
        angular.element("#taskType").hide();
        angular.element("#taskStatus").hide();
    };
    //项目下拉状态控制
    taskObj.showStatus=function(){
        $("#taskStatus,#taskType").hide();
        if(angular.element("#projectInfo").is(":hidden")){
             
            angular.element("#projectInfo").show();
        }else{
            
            angular.element("#projectInfo").hide();
        }
    };
    //相关项目选择
    taskObj.getSelectPro=function(proId,proName) {
        taskObj.proId = proId;
        // taskObj.taskProCtr=false;
        taskObj.taskProName = proName;
        angular.element("#projectInfo").hide();
    };

    taskObj.selectProWin = function(a) {
        taskObj.aaa=[{a:1,b:'ceshi'},{'a':2,b:'sdfsdfsd'}];
    };

    taskObj.taskSearch = function () {
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

        if(taskObj.btime>taskObj.etime && taskObj.btime && taskObj.etime){
            alert('开始时间不能大于结束时间', 'success', 2000);
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
        myTaskModel.getMyTaskList($scope,taskObj.proId, taskObj.taskType, taskObj.status, taskObj.btime, taskObj.etime, taskObj.taskTitle,taskObj.overtime,taskObj.num,taskObj.current);
    };


    angular.element(document).bind("click",function(event){

        if(angular.element(event.target).parents(".selectbor ").length==0){
             angular.element(".selectbor  ul").hide();
        } 

    });

    angular.element(".selecttimebor").bind("click",function(event){
        $(".selectbor ul").hide();
    });


    //分页
    $scope.taskPaging = function(){
        taskObj.currentPage = $scope.page.tempcurPage;
        myTaskModel.getMyTaskList($scope, taskObj.proId, taskObj.taskType, taskObj.status, taskObj.btime, taskObj.etime,taskObj.taskTitle, taskObj.overtime, taskObj.pagesize, taskObj.currentPage);
    };
    //接受任务
    taskObj.acceptTask = function(taskId){
        doTaskModel.acceptTask(taskId, $scope);
    };
    //接受人拒绝任务
    taskObj.refuseTaskPop = function(show) {
        taskObj.refuse_reason = '';
        $scope.showRefuse = show;
        $("#masklayer2").show();
        if (!show) {
            $("#masklayer2").hide();
        }
    };
    //接受人拒绝任务
    taskObj.refuse_reason = '';
    $scope.showRefuse = false;
    taskObj.refuseTask = function(taskId){
        
        if( Publicfactory.checkEnCnstrlen(taskObj.refuse_reason)>100||taskObj.refuse_reason.length<=0 || taskObj.refuse_reason.trim()=='' || typeof taskObj.refuse_reason == 'undefined'){
            alert('拒绝理由不能为空且长度不能大于50个字');
            return false;
        }else{
            taskObj.refuse_reason = taskObj.refuse_reason.replace(/\n/g,"<br/>");
            taskObj.refuse_reason = taskObj.refuse_reason.replace(/\s/g,"&nbsp;");
            doTaskModel.refuseTask(taskId,taskObj.refuse_reason,$scope);
            taskObj.refuseTaskPop(false);
        }

    }
    //添加附件
    $scope.addFileBtn = function(uploader){
        uploader.url = '/index.php?r=task/task/upload&type=2&taskType='+$scope.temp_task_type+'&taskId='+$scope.taskDetail.task_id;
        uploader.onCompleteItem = function (fileItem, response, status, headers) {
            if(response.code!=20000 && response.msg){
                alert(response.msg, 'success', 2000);
                return false;
            }
            if(response.data !=undefined || response.data.name != undefined){
                $scope.taskDetail.workNoteFiles.push(response.data);
            }
        };

    };
    //删除上传的附件
    taskObj.delFiles = function(index, attId){
        $http.get('/index.php?r=task/task/del-att&attId='+attId).success(function(data) {
            if(data.code == 20000) {

                if($scope.taskDetail.workNoteFiles){
                    $scope.taskDetail.workNoteFiles.splice(index,1);
                }
            }
        });
    };

     $scope.taskDetail.work_note = '';
    //提交审核
    taskObj.auditTask = function(taskId){
        if( $scope.taskDetail.work_note!=null && Publicfactory.checkEnCnstrlen($scope.taskDetail.work_note)>2000 ){
            alert('工作笔记内容不能超过1000字！');
            return false;
        }else{
            doTaskModel.auditTask(taskId,$scope.taskDetail.work_note, $scope);
        }
    }
});


//我的认领记录
TaskMod.controller('myClaimRecordCtr',function($scope,$http,$state,myClaimRecordModel){
    var taskObj = $scope.taskobj={};
    taskObj.myClaimRecorList = [];
    taskObj.task_id = '';
    taskObj.pageSize = '';
    taskObj.curPage = '';
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    // taskObj.type = $stateParams.type ? $stateParams.type : 1;
    // taskObj.userInfo = $cookieStore.get('userInfo');

    myClaimRecordModel.getMyClaimRecordList($scope,taskObj.pageSize,taskObj.curPage);
    $scope.taskPaging = function(){
        taskObj.currentPage = $scope.page.tempcurPage;
        myClaimRecordModel.getMyClaimRecordList($scope,taskObj.pageSize,taskObj.currentPage);
    };
    // rewardTaskModel.getRewardTaskList($scope,taskObj.status,taskObj.taskTitle,taskObj.num,taskObj.current);
    taskObj.myClaimRecordDetail = function (taskId) {
        taskObj.task_id = taskId ;
        // angular.element("#taskDetailPopup").show();
        taskObj.taskDetailWin = true;
        //rewardTaskModel.getRewardTaskList($scope,taskObj.status,taskObj.taskTitle,taskObj.num,taskObj.current);
    };
    //隐藏弹窗
    taskObj.taskDetailPopup = function() {
        taskObj.taskDetailWin = false;
        // angular.element("#taskDetailPopup").hide();
    };

});