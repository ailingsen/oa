TaskMod.factory('doTaskModel', function($http,$timeout,$state,filtersModel,myReleaseTaskModel,myRewardTaskModel,myTaskModel,rewardTaskModel){
    var  service={};

    //获取任务详情
    service.getTaskDetail = function($scope,task_id,type,$state) {
        timeNumber = '';
        $http.get('/index.php?r=tasklist/taskdetail&task_id='+task_id+"&type="+type).success(function (data) {

            if(data.code == -1) {
                $scope.taskFunc.noticeService(data.msg);
                $state.go('^');
                return false;
            }
            if(data.code == 0) {
                $scope.taskFunc.noticeService(data.msg,'',0,{url:'^', params:{}, params2:{reload:false}});
                $state.go("^");
                return false;
            }
            $scope.task = data.data;
            service.getCreateUser(data.data.create_uid,$scope);

            $scope.clearcuontdown = function(){

                var qzchaoshi = angular.element('.detailbox-content-left .task-grant-apply');
                qzchaoshi.addClass("chaoshi").find(".grant-over").remove();
                qzchaoshi.find(".grant-apply-title").after('<span class="chaoshis">已超时</span>');

            };



            $timeout(function(){



                if( angular.element("#days").length > 0) {


                    //倒计时
                    $scope.cuontdown = {
                        'interval': 1000,
                        'around' : function(years,months,days,hours,minutes,seconds){
                            var num = window.setInterval(
                                function(){
                                    var timespan = countdown(new Date(),
                                        new Date(years,months,days,hours,minutes,seconds),
                                        countdown.DAYS|countdown.HOURS|countdown.MINUTES|countdown.SECONDS);
                                    document.getElementById("days").innerHTML=timespan.days;
                                    document.getElementById("hours").innerHTML=timespan.hours;
                                    document.getElementById("minutes").innerHTML=timespan.minutes;
                                    document.getElementById("seconds").innerHTML=timespan.seconds;

                                    //清除倒计时
                                    var nowDateClear =  Date.parse(new Date());
                                    if(parseInt($scope.task.task_end_time)*1000 - nowDateClear == 0){
                                        window.clearInterval(num);
                                        $scope.clearcuontdown();
                                    }

                                },this.interval
                            );
                            return num;
                        }
                    }




                    //window.clearInterval($scope.cuontdown.around);


                    $scope.getnewtime = function (endTime){
                        //结束时间
                        var  s1 = new Date(endTime * 1000);
                        //当前时间
                        var s2 = new Date();
                        //当前时间减去结束时间 得到 超期时间
                        var days = s2.getTime() - s1.getTime();
                        //超期时间转换
                        var time = parseInt(days / (1000 * 60 * 60 * 24));


                        //总共的天数
                        var day = Math.floor(days/(60*60*24*1000));
                        //总共的小时
                        var hour = Math.floor(days/(60*60*1000))%24;
                        //总共的分钟
                        var minute = Math.floor(days/(60*1000))%60;
                        //总共的秒数
                        var second = Math.floor(days/(1000))%60;




                        angular.element("#days").html(day);
                        angular.element("#hours").html(hour);
                        angular.element("#minutes").html(minute);
                        angular.element("#seconds").html(second);



                    }

                    var nowDate =  Date.parse(new Date());
                    var myDate = '';

                    if (timeNumber != '') {
                        timeNumber = window.clearInterval(timeNumber);//离开视图移除悬赏计时器
                    }

                    if($scope.task.task_start_time > 0 && $scope.task.task_end_time > 0) {

                        $scope.isbegin = true;
                        //距任务结束时间
                        if(parseInt($scope.task.task_end_time)*1000 - nowDate > 0) {
                            myDate = new Date($scope.task.task_end_time*1000);
                            $scope.grantOver=true;
                            $scope.timeOut = 2;

                            timeNumber =  $scope.cuontdown.around(myDate.getFullYear(),myDate.getMonth() , myDate.getDate(),myDate.getHours(),myDate.getMinutes(),myDate.getSeconds());

                            //console.log($scope.cuontdown.around.num);
                        }else if(nowDate - parseInt($scope.task.task_end_time)*1000 > 0) {          //任务超时
                            $scope.isdelay = true;
                            //myDate = new Date($scope.task.task_end_time*1000);
                            $scope.grantOver=true;
                            $scope.timeOut = 2;

                            /*
                             timeNumber = window.setInterval(function(){
                             $scope.getnewtime($scope.task.task_end_time);
                             },1000);
                             /**/
                        }else {
                            $scope.grantOver=false;
                            $scope.days=0;
                            $scope.hours=0;
                            $scope.minutes=0;
                            $scope.seconds=0;
                            $scope.timeOut = 1;
                        }
                        //}
                    }
                }
//初始化时间 年月日
                $scope.tm.Taskdetails_Starttime = filtersModel.filterTime($scope.task.task_start_time*1000);
                $scope.tm.Taskdetails_Endtime = filtersModel.filterTime($scope.task.task_end_time*1000);
                //初始化时间 时 分
                $scope.tm.Taskdetails_StarttimeHHmm = filtersModel.filterTimeHHmm($scope.task.task_start_time*1000);
                $scope.tm.Taskdetails_EndtimeHHmm = filtersModel.filterTimeHHmm($scope.task.task_end_time*1000);
                angular.element("#Taskdetails_start_time").val($scope.tm.Taskdetails_StarttimeHHmm);
                angular.element("#Taskdetails_end_time").val($scope.tm.Taskdetails_EndtimeHHmm);

            },1000);
        });
    }

    service.backList = function($scope){
        $scope.detailPopWin = false;
        $scope.showCheck = true;
        $('#masklayer1').hide();
        $("#masklayer2").hide();
        myReleaseTaskModel.getReleaseTaskList($scope,$scope.taskobj.proId, $scope.taskobj.taskType, $scope.taskobj.status, $scope.taskobj.btime, $scope.taskobj.etime, $scope.taskobj.taskTitle,$scope.taskobj.overtime,$scope.taskobj.num,$scope.page.curPage);
    }

    service.backToMytask = function($scope){
        $scope.taskobj.taskDetailWin = false;
        $scope.showDelay = false;
        $('#masklayer1').hide();
        $("#masklayer2").hide();
        myTaskModel.getMyTaskList($scope, $scope.taskobj.proId, $scope.taskobj.taskType, $scope.taskobj.status, $scope.taskobj.btime, $scope.taskobj.etime,$scope.taskobj.taskTitle, $scope.taskobj.overtime, $scope.taskobj.pagesize, $scope.page.curPage);
    }

    service.backToRewardList = function($scope){
        $scope.detailPopWin = false;
        $('#masklayer1').hide();
        $("#masklayer2").hide();
        rewardTaskModel.getRewardTaskList($scope,$scope.taskobj.status,$scope.taskobj.taskTitle,$scope.taskobj.pageSize,$scope.page.curPage,$scope.taskobj.orgId);
    }

    service.backToMyRewardList = function($scope){
        $scope.detailPopWin = false;
        $scope.pointWin = false;
        $('#masklayer1').hide();
        $("#masklayer2").hide();
        myRewardTaskModel.getMyRewardTaskList($scope,$scope.taskobj.status,$scope.taskobj.taskTitle,$scope.taskobj.pageSize,$scope.page.curPage);
    }

    //接受任务
    service.acceptTask=function(taskId,$scope){
        $http.get('/index.php?r=task/dotask/accept-task&taskId='+taskId).success(function (data) {
            if(data.code==20000){
                alert(data.msg);

                service.backToMytask($scope);
            }else{
                alert(data.msg);
            }
        });
    }

    //拒绝任务
    service.refuseTask=function(taskId,reason,$scope){
        $http.post('/index.php?r=task/dotask/refuse-task',{'task_id':taskId,'reason':reason}).success(function (data) {
            if(data.code==20000){
                alert(data.msg);
                service.backToMytask($scope);
            }else{
                alert(data.msg)
            }
        });
    }
    //拒绝并重新指派
    service.redoTask=function(taskId,reason,rePoint,$scope){
        $http.post('/index.php?r=task/dotask/done-task',{'task_id':taskId,'reason':reason,'pointer':rePoint,'type':1}).success(function (data) {
            if(data.code==20000){
                alert(data.msg);
                service.backList($scope);
                $scope.selectedMember = {};
                $scope.taskobj.refuse_reason = '';
            }else{
                alert(data.msg);
            }
        });
    }

    //重新指派
    service.submitRepoint=function(taskId,uid,$scope){
        $http.post('/index.php?r=task/dotask/submit-repoint',{'task_id':taskId,'uid':uid}).success(function (data) {
            if(data.code==20000){
                alert(data.msg);
                service.backList($scope);
            }else{
                alert(data.msg);
            }
        });
    }
    //确定完成
    service.doneTask=function(taskId,doneData,type,$scope){
        $http.post('/index.php?r=task/dotask/done-task', {'task_id':taskId,'type':type,'data':doneData}).success(function (data) {
            if(data.code==20000){
                alert(data.msg);

                service.backList($scope);
            }else{
                alert(data.msg);
            }
        });
    }
    //获取任务技能
    service.getTaskSkill=function($scope,taskId){
        $http.post('/index.php?r=task/dotask/get-task-skill', {'task_id':taskId}).success(function (data) {
            $scope.skills = data.data;
        });
    }

    //提交审核
    service.auditTask=function(taskId,workNote,$scope){
        $http.post('/index.php?r=task/dotask/commit-task', {'task_id':taskId, 'work_note':workNote}).success(function (data) {
            if(data.code==20000){
                alert(data.msg);
                service.backToMytask($scope);
            }else{
                alert(data.msg);
            }
        });
    }
    //认领悬赏任务
    service.claimTask=function(taskId,$scope){
        $http.post('/index.php?r=task/rewardtask/claim-task', {'task_id':taskId}).success(function (data) {
            if(data.code==20000){
                alert(data.msg);
                service.backToRewardList($scope);
            }else{
                alert(data.msg);
            }
        });
    }
    //取消认领
    service.cancelTask=function(taskId,$scope){
        $http.post('/index.php?r=task/rewardtask/cancel-task&task_id='+taskId).success(function (data) {
            if(data.code==20000){
                alert(data.msg);
                service.backToRewardList($scope);
            }else{
                alert(data.msg);
            }
        });
    }
    //指派给他
    service.pointTask=function(taskId,uid,$scope){
        $http.post('/index.php?r=task/rewardtask/point-task',{"task_id":taskId, "point_uid":uid}).success(function (data) {
            if(data.code==20000){
                alert(data.msg);
                $scope.pointWin = false;
                $('#masklayer2').hide();
                service.backToMyRewardList($scope);
            }else{
                alert(data.msg);
            }
        });
    }
    //删除任务
    service.deleteTask=function(taskId,taskType,$scope){
        $http.post('/index.php?r=task/dotask/delete-task', {'task_id':taskId, 'task_type': taskType}).success(function (data) {
            if(data.code==20000){
                alert(data.msg);
                if (1 == taskType) {
                    service.backList($scope);
                } else {
                    $scope.detailPopWin = false;
                    $('#masklayer1').hide();
                    $("#masklayer2").hide();
                    myRewardTaskModel.getMyRewardTaskList($scope,$scope.taskobj.status,$scope.taskobj.taskTitle,$scope.taskobj.pageSize,$scope.taskobj.curPage);
                    // service.backToRewardList($scope);
                }
            }else{
                alert(data.msg);
            }
        });
    }
    //关闭任务
    service.closeTask=function(taskId,taskType,$scope){
        $http.post('/index.php?r=task/dotask/close-task', {'task_id':taskId, 'task_type': taskType}).success(function (data) {
            if(data.code==20000){
                alert(data.msg);
                if (1 == taskType) {
                    service.backList($scope);
                } else {
                    service.backToMyRewardList($scope);
                }
            }else{
                alert(data.msg);
            }
        });
    }
    //延期任务
    service.delayTask=function(taskId,taskType,delayTime,delayReason,$scope){
        $http.post('/index.php?r=task/dotask/delay-task', {'task_id':taskId, 'task_type': taskType, 'delay_time':delayTime, 'reason':delayReason}).success(function (data) {
            if(data.code==20000){
                alert(data.msg);
                $scope.showDelay  = false;
                if (1 == taskType) {
                    service.backList($scope);
                } else {
                    service.backToMyRewardList($scope);
                }
            }else{
                alert(data.msg);
            }
        });
    }
    //发布任务
    service.publishTask=function(taskId,taskType,$scope){
        $http.post('/index.php?r=task/dotask/publish', {'task_id':taskId, 'task_type': taskType}).success(function (data) {
            if(data.code==20000){
                alert(data.msg);
                if (1 == taskType) {
                    service.backList($scope);
                } else {
                    service.backToMyRewardList($scope);
                }
            }else{
                alert(data.msg);
            }
        });
    }

    return service;
});
