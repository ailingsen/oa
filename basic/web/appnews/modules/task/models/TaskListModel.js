/**
 * Created by pengyanzhang on 2016/8/4.
 */
TaskMod.factory('myTaskModel',function($http,$state, util){
    var service={};
    //获取我参与的项目相关信息
    service.getProjectInfo = function ($scope,status) {
        $http.post('/index.php?r=task/task/getmyproject',{public:status,type:5,complete:1})
            .success(function(data) {
                $scope.myObject = data.data.proList;
            });
    };
    //获取我接受的任务列表
    service.getMyTaskList = function ($scope, proId, taskType, status, beginTime, endTime, taskTitle, overtime, num, current) {
        $http.post('/index.php?r=task/task-list/my-task-list',{proId:proId,taskType:taskType,status:status,beginTime:beginTime,endTime:endTime,taskTitle:taskTitle,overtime:overtime,num:num,current:current})
            .success(function(data) {
                $scope.myTaskList = data.data.taskListData;
                $scope.page.sumPage = data.data.totalPage;
                $scope.page.curPage = $scope.page.tempcurPage;
            });
    };
    //我接受的任务详情页面
    service.getTaskDetailInfo = function ($scope,taskId) {
        $http.post('/index.php?r=task/task-list/task-details',{taskId:taskId,type:1})
            .success(function(data) {
                if(data.code==20005){
                    alert(data.msg);
                    return;
                } else {
                    data.data = util.setImgRand(data.data);
                    $scope.temp_task_type=data.temp_task_type;
                    $scope.taskDetail = data.data;
                    $("#masklayer1").show();
                    $scope.taskobj.taskDetailWin = true;
                }
            });
    };

    //操作日志
    service.getOperationTask = function($scope,taskId, type) {
        $http.post('/index.php?r=task/task-list/get-operation-task',{taskId:taskId,taskType:type})
            .success(function(data) {
                data.data = util.setImgRand(data.data);
                if(type==1){//我接受的任务
                    $scope.taskobj.operationLogList = data.data;
                }else if(type==2){//我发布的任务
                    $scope.taskobj.operationLogList = data.data;
                }else if(type==3){
                    
                }
                
            });
    };

    return service;
});
TaskMod.factory('myReleaseTaskModel',function($http,util){
    var service={};
    //获取我参与的项目相关信息
    service.getProjectInfo = function ($scope,status) {
        $http.post('/index.php?r=task/task/getmyproject',{public:status,type:5,complete:1})
            .success(function(data) {
                $scope.myObject = data.data;
            });
    };
    //获取我发布的任务列表
    service.getReleaseTaskList = function ($scope,proId,taskType,status,beginTime,endTime,taskTitle,overtime,num,current) {
        $http.post('/index.php?r=task/task-list/my-release-list',{proId:proId,taskType:taskType,status:status,beginTime:beginTime,endTime:endTime,taskTitle:taskTitle,overtime:overtime,num:num,current:current})
            .success(function(data) {
                $scope.myReleaseTaskList = data.data.taskListData;
                $scope.page.sumPage =data.data.totalPage ;
                $scope.page.curPage =$scope.page.tempcurPage ;
            });
    };
    //我发布的任务详情页面
    service.getReleaseTaskDetailInfo = function ($scope,taskId) {
        $http.post('/index.php?r=task/task-list/task-details',{taskId:taskId,type:2})
            .success(function(data) {
                data.data = util.setImgRand(data.data);
                $scope.taskDetail = data.data;
                $scope.taskobj.charge = data.data.charger;
            });
    };
    return service;
});

TaskMod.factory('rewardTaskModel',function($http, util){
    var service={};
    //获取悬赏池的任务列表
    service.getRewardTaskList = function ($scope,status,taskTitle,num,current,orgId) {
        $http.post('/index.php?r=task/task-list/reward-list',{status:status,taskTitle:taskTitle,num:num,current:current,orgId:orgId})
            .success(function(data) {
                $scope.rewardList = data.data.rewardData;
                $scope.page.sumPage =data.data.totalPage ;
                $scope.page.curPage =$scope.page.tempcurPage ;
            });
    };
    service.getRewardDetailList = function ($scope,taskId) {
        $http.post('/index.php?r=task/task-list/reward-list-details',{taskId:taskId})
            .success(function(data) {
                data.data.applicant = util.setImgRand(data.data.applicant);
                $scope.taskDetail = data.data;
            });
    };
    return service;
});

//我的悬赏

TaskMod.factory('myRewardTaskModel',function($http,util){

    var service={};
    //获取悬赏池的任务列表
    service.getMyRewardTaskList = function ($scope,status,taskTitle,pageSize,curPage) {
        $http.post('/index.php?r=task/task-list/my-reward',{taskTitle:taskTitle,status:status,pageSize:pageSize,curPage:curPage})
            .success(function(data) {
                $scope.myRewardDetailList = data.data.myReData;
                $scope.page.sumPage =data.data.totalPage ;
                $scope.page.curPage =$scope.page.tempcurPage ;
            });
    };
    service.getMyRewardDetailList = function ($scope,taskId) {
        $http.post('/index.php?r=task/task-list/reward-list-details',{taskId:taskId})
            .success(function(data) {
                data.data.applicant = util.setImgRand(data.data.applicant);
                $scope.taskDetail = data.data;
            });
    };
    return service;
});
TaskMod.factory('myClaimRecordModel',function($http, util){
    var service={};
    //获取悬赏池的任务列表
    service.getMyClaimRecordList = function ($scope,pageSize,curPage) {
        $http.post('/index.php?r=task/task-list/application-record',{pageSize:pageSize,curPage:curPage})
            .success(function(data) {
                data.data.rewardAppRe = util.setImgRand(data.data.rewardAppRe);
                $scope.taskobj.myClaimRecorList = data.data.rewardAppRe;
                $scope.page.sumPage =data.data.totalPage ;
                $scope.page.curPage =$scope.page.tempcurPage ;
            });
    };
    service.getRewardDetailList = function ($scope,taskId) {
        $http.post('/index.php?r=task/task-list/reward-list-details',{taskId:taskId})
            .success(function(data) {
                $scope.rewardDetailList = data.data.rewardAppRe;
            });
    };

    //操作日志
    service.operationLogData = function($scope,taskId) {
        $http.post('/index.php?r=task/task-list/get-operation-task',{taskId:taskId})
            .success(function(data) {
            });
    };
    return service;
});