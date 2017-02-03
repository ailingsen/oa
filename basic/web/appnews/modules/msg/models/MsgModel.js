MsgMod.factory('msgModel',function($http,$state,$cookieStore,$sce){
    var  service={};

    //申请消息列表
    service.getMsgListInfo=function($scope){
        $http.post('/index.php?r=desk/desk/msg-list-info',JSON.stringify($scope.msg_param)).success(function (data) {
            if(data.code==1){
                $scope.msg.msgInfo = data.data.list;
                $scope.page.curPage = data.data.page.curPage;
                $scope.page.sumPage = data.data.page.sumPage;
            }
        });
    }


    return service;
});
