//发布调研
MsgMod.controller('MeetingMsgCtrl',function($scope,$http,$rootScope,$timeout,msgModel,Publicfactory,$location){
    var msg = $scope.msg = {};
    var msg_param = $scope.msg_param = {};
    //翻页对象
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    msg_param.page = 1;
    msg_param.type = 3;
    msg.msgInfo = {};

    msgModel.getMsgListInfo($scope);

    //翻页方法
    $scope.page_fun = function () {
        msg_param.page = $scope.page.tempcurPage;
        msgModel.getMsgListInfo($scope);
    };
});



