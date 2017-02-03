NoticeMod.factory('noticeModel',function($http,$state,$sce){
    var  service={};

    //获取公告列表
    service.getNoticeList=function($scope,isdel){
        $http.post('/index.php?r=notice/notice/get-notice', JSON.stringify($scope.search_notice))
            .success(function(data, status) {
                $scope.notice.noticelist = data.data.notList;
                $scope.page.curPage = data.data.page.curPage;
                $scope.page.sumPage = data.data.page.sumPage;
                if(isdel){
                    alert('删除成功');
                }
        });
    };

    //获取公告详情
    service.getNoticeDetail=function($scope){
        //是否需要设置已读
        var isSetRead = $scope.notice.is_manage ? 1 : 0;
        $http.post('/index.php?r=notice/notice/notice-detail', {notice_id:$scope.notice.notice_id,is_manager:isSetRead})
            .success(function(data, status) {
                if(data.code==1){
                    $scope.notice.noticeDetail = data.data;
                    $scope.notice.noticeDetail.content = $sce.trustAsHtml($scope.notice.noticeDetail.content);
                    $scope.notice.isNoticeDetailWin = true;
                    $('#masklayer1').show();
                }else{
                    service.getNoticeList($scope,false);
                    alert(data.msg);
                }
            });
    };

    //添加公告
    service.addNotice=function($scope){
        $http.post('/index.php?r=notice/notice/create-notice', JSON.stringify($scope.paramobj))
            .success(function(data, status) {
                if(data.code==1){
                    $state.go('main.notice.noticeManage');
                    alert(data.msg);
                }else{
                    alert(data.msg);
                }
            });
    };

    //删除公告
    service.delNotice=function($scope,notice_id){
        $http.post('/index.php?r=notice/notice/del-notice', {notice_id:notice_id})
            .success(function(data, status) {
                service.getNoticeList($scope,true);
                $scope.notice.isNoticeDetailWin = false;
                $('#masklayer1').hide();
            });
    };

    //附件下载
    //file_path  文件路径和名称
    //real_name  文件上传后的名称
    //file_name  文件上传前的名称
    //file_size  文件大小
    service.fileDownload = function($scope,file_path,real_name,file_name,file_size){
        window.location.href="/index.php?r=notice/notice/downfile&filepath="+encodeURI('/'+file_path+'/'+real_name)+'&file_name='+file_name+'&file_size='+file_size;
    }

    return service;
});