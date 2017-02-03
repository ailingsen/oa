//我创建的项目

var workStatementMod=angular.module('workStatementMod',[])

workStatementMod.controller('workStatementCtrl',function($scope,$http,$rootScope,Publicfactory,projectModel,$location,$state,$stateParams,filtersModel,$cookieStore){
    var workStatement = $scope.workStatement={};
   
    //查询状态数组
    workStatement.arrSearchStatus = [
        {status:0,statusstr:'状态'},
        {status:1,statusstr:'未开始'},
        {status:2,statusstr:'进行中'},
        {status:3,statusstr:'已完成'}
    ]

    //翻页对象
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };

    //设置初始查询参数
    var isInit = $stateParams.isInit ? $stateParams.isInit : 1;
    if(isInit == 1 || typeof($cookieStore.get('ProList')) == "undefined"){
        var ProList = {};
        //按状态查询
        ProList.search_status = 0
        //按开始时间查询
        ProList.search_begin_time= '';
        //按结束时间查询
        ProList.search_end_time= '';
        //按项目名称查询
        ProList.search_pro_name= '';
        ProList.page = 1;
        ProList.type = 1;
        $cookieStore.put('ProList',ProList);
    }

    //初始化数据------------------------------------------------------
    //按状态查询
    var ProListCookie = $cookieStore.get('ProList');
    param_project.search_status = ProListCookie.search_status ? ProListCookie.search_status : 0;
    workStatement.search_status = param_project.search_status;
    angular.element.each(workStatement.arrSearchStatus, function (key, val) {
        if(val.status == param_project.search_status){
            angular.element('#status').html(val.statusstr);
        }
    });
    //显示状态下拉框
    workStatement.statusWinButton = function () {
        workStatement.isStatusWin = !workStatement.isStatusWin;
    }

    //设置选中的查询状态
    workStatement.selectSearchStatus = function (obj) {
        workStatement.search_status = obj.status;
        angular.element('#status').html(obj.statusstr);
        workStatement.isStatusWin = false;
    }

    //切换图表显示还是列表显示
    workStatement.switchButton = function(type){
        if(type==1){//修改样式
            angular.element(".changei a").eq(0).addClass('selected');
            angular.element(".changei a").eq(1).removeClass('selected');
        }else{
            angular.element(".changei a").eq(1).addClass('selected');
            angular.element(".changei a").eq(0).removeClass('selected');
        }
        param_project.type = type;
    }

});



