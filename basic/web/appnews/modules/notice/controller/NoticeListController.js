
var NoticeMod=angular.module('NoticeMod',[])

NoticeMod.controller('NoticeListCtrl',function($scope,$http,$rootScope,Publicfactory,$cookies,$cookieStore,$state,$location,noticeModel,$stateParams,permissionService){
    if (!permissionService.checkPermission('NoticeView')) {
        $state.go('main.index', {},{'reload': false});
        return false;
    }
    var notice = $scope.notice = {};
    var search_notice = $scope.search_notice = {};
    var userInfo = $cookieStore.get('userInfo');
    //公告信息
    notice.noticelist = [];
    //公告详情
    notice.noticeDetail = [];
    //是否显示删除按钮
    notice.is_manage = false;
    //是否显示详情窗口
    notice.isNoticeDetailWin = false;
    notice.notice_id = '';
    //按公告标题查询
    notice.title = '';
    //开始时间
    notice.begin_time = '';
    //结束时间
    notice.end_time = '';
    //是否显示删除确认框
    notice.isDelConfirmWin = false;
    //删除按钮标志位
    notice.delTag=0;
    //要删除的对象
    notice.delObj = {};

    //按公告标题查询
    search_notice.title = '';
    //开始时间
    search_notice.begin_time = '';
    //结束时间
    search_notice.end_time = '';
    //当前页
    search_notice.page = 1;

    //翻页对象
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };

    //打开详情页
    notice.noticeDetailButton = function(notice_id){
        notice.notice_id = notice_id;
        if(!notice.is_manage){//设置已读
            angular.element.each($scope.notice.noticelist, function (key, val) {
                if(val['notice_id'] == notice.notice_id && !val['notice_read_id']){
                    $scope.notice.noticelist[key]['notice_read_id'] =userInfo.u_id ;
                }
            });
        }
        noticeModel.getNoticeDetail($scope);
    }


    //判断是否是公告管理
    notice.path = $location.path();
    if(notice.path.indexOf("notice/noticeManage") > 0 ){
        notice.is_manage = true;
    }else{//是否打开详情页
        var notice_id = $stateParams.notice_id ? $stateParams.notice_id : 0;
        if(notice_id > 0){
            notice.noticeDetailButton(notice_id);
        }
    }

    //获取通知列表
    noticeModel.getNoticeList($scope,false);

    //查询按钮
    notice.searchButton = function(){
        if(notice.title.length>40){
            alert('公告标题长度最多支持40个字');
            return;
        }
        if(notice.end_time!=''&&notice.begin_time!=''&& notice.end_time-notice.begin_time<0){
            alert('结束时间必须大于等于开始时间');
            return;
        }
        //翻页对象
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };
        //按公告标题查询
        search_notice.title = notice.title;
        //开始时间
        search_notice.begin_time = notice.begin_time;
        //结束时间
        search_notice.end_time = notice.end_time;
        //当前页
        search_notice.page = 1;
        noticeModel.getNoticeList($scope,false);
    }

    //翻页方法
    $scope.page_fun = function () {
        search_notice.page = $scope.page.tempcurPage;
        noticeModel.getNoticeList($scope,false);
    };

    //关闭详情页
    notice.noticeCloseDetail = function(){
        notice.isNoticeDetailWin = false;
        $('#masklayer1').hide();
    }

    //显示删除确认框
    notice.openDelConfirmWin = function(obj,i){
        if(i<0){
            notice.delTag=-1;
        }else{
            notice.delTag=1;
        }
        notice.delObj = obj;
        notice.isDelConfirmWin = true;
        $('#masklayer1').css('z-index',400);
        $('#masklayer1').show();
    }

    //关闭删除确认框
    notice.closeDelConfirmWin = function(){
        notice.isDelConfirmWin = false;
        $('#masklayer1').css('z-index',200);
        if(notice.delTag<0){
            $('#masklayer1').hide();
        }
    }

    //删除公告
    notice.delNotice = function(obj){
        notice.isDelConfirmWin = false;
        $('#masklayer1').css('z-index',200);
        if(notice.delTag<0){
            $('#masklayer1').hide();
        }
        noticeModel.delNotice($scope,obj.notice_id);
    }

    //下载附件
    notice.downFileBtn = function(obj){
        noticeModel.fileDownload($scope,obj.file_path,obj.real_name,obj.file_name,obj.file_size);
        //window.location.href="/index.php?r=notice/notice/downfile&filepath="+encodeURI('/'+obj.file_path+'/'+obj.real_name)+'&file_name='+obj.file_name;
    }

});




