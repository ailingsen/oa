
//悬赏任务
TaskMod.controller('rewardTaskCtr',function($scope,$http,rewardTaskModel,doTaskModel,$cookieStore,myTaskModel,$state,$stateParams){
    var taskObj = $scope.taskobj={};
    $scope.rewardList = [];
    $scope.taskDetail = [];
    taskObj.operationLogList = [];
    taskObj.operationLog = false;
    taskObj.rewardPoupeCtr = false;
    taskObj.applicationRecord = 'ui-widget-winbor tablelistwin of minscroll none';
    taskObj.status = '';
    taskObj.task_id = '';
    taskObj.statusName = '状态';
    taskObj.taskTitle = '';
    taskObj.num = '';
    taskObj.current = '';
    taskObj.type = 1;
    taskObj.userInfo = $cookieStore.get('userInfo');
    taskObj.orgId = taskObj.userInfo.org.org_id;
    $scope.detailPopWin = false;
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };

    //操作日志弹窗
    taskObj.operationLogPopup = function(taskId) {
        taskObj.operationLog = true;
        myTaskModel.getOperationTask($scope, taskId,2);
    };
    //任务状态点击
    taskObj.taskStatusClick = function () {
       // taskObj.rewardPoupeCtr = !taskObj.rewardPoupeCtr;
       if(angular.element("#taskStatusClickbor").is(":hidden")){
             
            angular.element("#taskStatusClickbor").show();
        }else{
            
            angular.element("#taskStatusClickbor").hide();
        }
    };
    rewardTaskModel.getRewardTaskList($scope,taskObj.status,taskObj.taskTitle,taskObj.num,taskObj.current,taskObj.orgId);
    taskObj.rewardSearch = function () {
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };
        taskObj.taskTitle = $scope.taskobj.taskTitle;
        rewardTaskModel.getRewardTaskList($scope,taskObj.status,taskObj.taskTitle,taskObj.num,taskObj.current,taskObj.orgId);
    };
    //悬赏任务分页
    $scope.taskPaging = function(){
        taskObj.currentPage = $scope.page.tempcurPage;
        rewardTaskModel.getRewardTaskList($scope,taskObj.status,taskObj.taskTitle,taskObj.num,taskObj.currentPage,taskObj.orgId);
    };
    taskObj.selectStatus=function(statusName, status){
        taskObj.statusName = statusName;
        taskObj.status = status;
        //taskObj.rewardPoupeCtr = false;
        angular.element("#taskStatusClickbor").hide();
    };

    //悬赏详情页面
    taskObj.getRewardDetail = function(taskId) {
        rewardTaskModel.getRewardDetailList($scope,taskId);
        // angular.element("#taskDetailPopup").show();
        $('#masklayer1').show();
        $scope.detailPopWin = true;
    };

    //如果传了task_id,打开详情弹窗
    if ($stateParams.task != undefined && $stateParams.task != '') {
        taskObj.getRewardDetail($stateParams.task )
    }

    //隐藏弹窗
    taskObj.taskDetailPopup = function() {
        taskObj.pointer = '';
        taskObj.pointerName = '';
        $('#masklayer1').hide();
        $('#masklayer2').hide();
        $scope.detailPopWin = false;
    };

    //认领悬赏任务
    taskObj.claimTask = function(taskId){
        taskObj.task_id = taskId;
        doTaskModel.claimTask(taskId,$scope);
    };
    //取消认领
    taskObj.cancelTask = function(taskId){
        doTaskModel.cancelTask(taskId,$scope);
    };
    //选择指派人
    taskObj.pointer = '';
    taskObj.pointerName = '';
    taskObj.selectPointer = function(pointer,pointerName,$event){
        // if ($scope.taskobj.taskDetailList.status == 2)return;
        var el = $event.target;
        if($scope.taskobj.taskId>0){
            taskObj.pointer = pointer;
            taskObj.pointerName = pointerName;
            // angular.element('#pointTaskPop').show();
            angular.element(el).addClass('select');
            angular.element(el).prevAll().removeClass('select');
            angular.element(el).nextAll().removeClass('select');
        }else{
            alert("请选择任务！");
        }
    };

    //指派给他 type=1:确定 type=2:取消
    taskObj.pointTask = function(type){
        console.log(type);
        if (2 == type) {
            angular.element('#pointTaskPop').hide();
            $('#masklayer1').hide();
            $('#masklayer2').hide();
            return;
        }
        if ($scope.taskobj.taskDetail.status == 2)return;
        if(taskObj.pointer>0){
            doTaskModel.pointTask($scope.taskobj.taskId,pointer,$scope);
        }else{
            alert("请选择任务！");
        }
    };

     angular.element(document).bind("click",function(event){
        
        if(angular.element(event.target).parents(".selectbor ").length==0){
             angular.element(".selectbor  ul").hide();
        } 

    });
});


//我的悬赏
TaskMod.controller('myRewardTaskCtr',function($scope,$http,$cookieStore,myRewardTaskModel,doTaskModel,myTaskModel,$state,$timeout,$stateParams){
    var taskObj = $scope.taskobj={};
    $scope.rewardList = [];
    $scope.myRewardDetailList = [];
    taskObj.operationLogList = [];
    taskObj.operationLog = false;
    taskObj.applicationRecord = 'ui-widget-winbor tablelistwin of minscroll none';
    taskObj.status = '';
    taskObj.task_id = '';
    taskObj.statusCtr = false;
    taskObj.statusName = '状态';
    taskObj.taskTitle = '';
    taskObj.pageSize = '';
    taskObj.curPage = '';
    taskObj.type = 2;
    $scope.detailPopWin = false;
    $scope.pointWin = false;
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    taskObj.userInfo = $cookieStore.get('userInfo');
    myRewardTaskModel.getMyRewardTaskList($scope,taskObj.status,taskObj.taskTitle,taskObj.pageSize,taskObj.curPage);
    //操作日志弹窗
    taskObj.operationLogPopup = function(taskId) {
        taskObj.operationLog = true;
        myTaskModel.getOperationTask($scope, taskId,2);
    };
    taskObj.myRewardSearch = function () {
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };
        taskObj.taskTitle = $scope.taskobj.taskTitle;
        myRewardTaskModel.getMyRewardTaskList($scope,taskObj.status,taskObj.taskTitle,taskObj.pageSize,taskObj.curPage);
    };
    taskObj.taskSearchPopupCtr = function () {
        
        if(angular.element("#taskSearchPopupCtr").is(":hidden")){
             
            angular.element("#taskSearchPopupCtr").show();
        }else{
            
            angular.element("#taskSearchPopupCtr").hide();
        }
    };
    //
    taskObj.selectStatus=function(statusName, status){
        taskObj.statusName = statusName;
        taskObj.status = status;
        //taskObj.statusCtr = false;
        angular.element("#taskSearchPopupCtr").hide();
    };
    //我的悬赏任务分页
    $scope.taskPaging = function(){
        taskObj.currentPage = $scope.page.tempcurPage;
        myRewardTaskModel.getMyRewardTaskList($scope,taskObj.status,taskObj.taskTitle,taskObj.pageSize,taskObj.currentPage);
    };
    //悬赏详情页面
    taskObj.getMyRewardDetail = function(taskId) {
        myRewardTaskModel.getMyRewardDetailList($scope,taskId);
        $scope.detailPopWin = true;
        $('#masklayer1').show();
    };

    //如果传了task_id,打开详情弹窗
    if ($stateParams.task != undefined && $stateParams.task != '') {
        taskObj.getMyRewardDetail($stateParams.task )
    }

    //隐藏弹窗
    taskObj.taskDetailPopup = function() {
        taskObj.pointer = '';
        taskObj.pointerName = '';
        $scope.detailPopWin = false;
        $('#masklayer1').hide();
    };

    //选择指派人
    taskObj.pointer = '';
    taskObj.pointerName = '';
    taskObj.selectPointer = function(pointer,pointerName,$event){
        var el = $event.target;
        if (!angular.element(el).hasClass('pointer')) {
            el = angular.element(el).parents('li').eq(0);
        }

        if($scope.taskDetail.task_id>0){
            taskObj.pointer = pointer;
            taskObj.pointerName = pointerName;
            el.addClass('selected');
            el.prevAll().removeClass('selected');
            el.nextAll().removeClass('selected');
        }else{
            alert("请先选择任务！");
        }
    };
    //指派给他 type=1:确定 type=2:取消
    taskObj.pointTask = function(type){
        if (2 == type) {
            taskObj.pointer = '';
            taskObj.pointerName = '';
            $scope.pointWin = false;
            $('#masklayer2').hide();
            return;
        }
        if ($scope.taskDetail.status == 2)return;
        if(taskObj.pointer>0){
            doTaskModel.pointTask($scope.taskDetail.task_id,taskObj.pointer,$scope);
        }else{
            alert("请先选择认领人！");
        }
    };
    //确定
    taskObj.conffirm = function(){

        if (taskObj.pointer == '') {
            alert('请先选择认领人', 'success', 2000);
            return;
        }
        $scope.pointWin = true;
        $('#masklayer2').show();
    }
    //取消
    taskObj.cancel = function(){
        taskObj.pointer = '';
        taskObj.pointerName = '';
        $scope.pointWin = false;
        $('#masklayer2').hide();
    }

    //删除任务
    // taskObj.deleteTask = function(taskId){
    //     doTaskModel.deleteTask(taskId,2,$scope);
    // };
    $scope.deletecloseWin = false;
    $scope.deletecloseWinindex = '';
    taskObj.deleteTask = function(taskId){
        $('#masklayer2').show();
        $scope.deletecloseWin = true;
        $scope.deletecloseWinindex = taskId;
    }
    taskObj.deleteTaskgo = function(){
        doTaskModel.deleteTask($scope.deletecloseWinindex, 2, $scope);
        $('#masklayer2').hide();
        $scope.deletecloseWin = false;
    }
    taskObj.deleteTaskexit = function(){
        $('#masklayer2').hide();
        $scope.deletecloseWin = false;
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
        doTaskModel.closeTask(taskId, 2, $scope);
    }

    //编辑任务
    taskObj.editeTask = function(taskId){
        $state.go('main.task.myRewardTask.editer', {'task_id':taskId, 'task_type':2,'taskType':2}, {'reload':true});
    }

    //发布任务
    taskObj.publishTask = function(taskId){
        doTaskModel.publishTask(taskId, 2, $scope);
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
