//发布调研
MsgMod.controller('ApplyMsgCtrl',function($scope,$http,$rootScope,$timeout,msgModel,Publicfactory,$location,$state){
    var msg = $scope.msg = {};
    var msg_param = $scope.msg_param = {};
    //翻页对象
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    msg_param.page = 1;
    msg_param.type = 2;
    msg.msgInfo = {};

    msgModel.getMsgListInfo($scope);

    //翻页方法
    $scope.page_fun = function () {
        msg_param.page = $scope.page.tempcurPage;
        msgModel.getMsgListInfo($scope);
    };

    msg.goDetail = function(obj) {
        if(obj.model_id == null) {
            alert('数据不存在了~');
            return;
        }
        $state.go('main.apply.mine',{apply_id:obj.apply_id,model_id:obj.model_id});
    }

});



