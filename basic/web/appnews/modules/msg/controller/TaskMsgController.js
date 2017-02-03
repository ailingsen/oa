//发布调研
MsgMod.controller('TaskMsgCtrl',function($scope,$http,$rootScope,$timeout,msgModel,Publicfactory,$location,$state){
    var msg = $scope.msg = {};
    var msg_param = $scope.msg_param = {};
    //翻页对象
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    msg_param.page = 1;
    msg_param.type = 4;
    msg.msgInfo = {};

    msgModel.getMsgListInfo($scope);

    //翻页方法
    $scope.page_fun = function () {
        msg_param.page = $scope.page.tempcurPage;
        msgModel.getMsgListInfo($scope);
    };

    msg.goDetail = function(obj) {
        if(obj.menu==0){//不跳转
            alert('数据不存在了~');
        }else if(obj.menu==1){//我接受的任务
            $state.go('main.task.myTask',{task_id:obj.task_id});
        }else if(obj.menu==2){//我发布的任务
            $state.go('main.task.myReleaseTask',{task:obj.task_id});
        }else if(obj.menu==3){//悬赏专区
            $state.go('main.task.rewardTask',{task:obj.task_id});
        }else if(obj.menu==4){//我的悬赏
            $state.go('main.task.myRewardTask',{task:obj.task_id});
        }
    }

});



