//发布调研
MsgMod.controller('ProjectMsgCtrl',function($scope,$http,$rootScope,$timeout,msgModel,Publicfactory,$location,$state){
    var msg = $scope.msg = {};
    var msg_param = $scope.msg_param = {};
    //翻页对象
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    msg_param.page = 1;
    msg_param.type = 5;
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
        }else if(obj.menu==1){
            $state.go('main.project.mycreatepro.prodetail',{isInit:1,list_status:0,pro_id:obj.project_id});
        }else if(obj.menu==2){
            $state.go('main.project.myinvoepro.prodetail',{isInit:1,list_status:0,pro_id:obj.project_id});
        }
    }
});



