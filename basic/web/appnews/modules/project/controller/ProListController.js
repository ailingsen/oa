//我创建的项目

var ProjectMod=angular.module('ProjectMod',[])

ProjectMod.controller('ProListCtrl',function($scope,$http,$rootScope,Publicfactory,projectModel,$location,$state,$stateParams,filtersModel,$cookieStore,permissionService){
    if (!permissionService.checkPermission('ProjectMypartake')) {
        $state.go('main.index', {},{'reload': false});
        return false;
    }
    var project = $scope.project={};
    var param_project = $scope.param_project={};

    if($location.path().indexOf("/mycreatepro") > 0){//我创建的项目
        project.public = 1;
    }else if($location.path().indexOf("/myinvoepro") > 0){//我参与的项目
        project.public = 2;
    }else if($location.path().indexOf("/openpro") > 0){//公开项目
        project.public = 3;
    }
    if(project.public!=1 && project.public!=2 && project.public!=3){
        $state.go('^');
        alert('没有权限');
    }
 

    //存储我创建的项目数据
    //project.projectlist=[];
    //按状态查询
    project.search_status=0;
    //按开始时间查询
    project.search_begin_time='';
    //按结束时间查询
    project.search_end_time='';
    //按项目名称查询
    project.search_pro_name='';
    //是否显示状态下拉框
    project.isStatusWin=false;
    //查询状态数组
    project.arrSearchStatus = [
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
        ProList.search_status = 0;
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
    project.search_status = param_project.search_status;
    angular.element.each(project.arrSearchStatus, function (key, val) {
        if(val.status == param_project.search_status){
            angular.element('#status').html(val.statusstr);
        }
    });
    //按开始时间查询
    param_project.search_begin_time=ProListCookie.search_begin_time ? ProListCookie.search_begin_time : '';
    project.search_begin_time = param_project.search_begin_time;
    //按结束时间查询
    param_project.search_end_time=ProListCookie.search_end_time ? ProListCookie.search_end_time : '';
    project.search_end_time = param_project.search_end_time;
    //按项目名称查询
    param_project.search_pro_name=ProListCookie.search_pro_name ? ProListCookie.search_pro_name : '';
    project.search_pro_name=param_project.search_pro_name;
    //初始化页数
    param_project.page = ProListCookie.page ? ProListCookie.page : 1;
    $scope.page.tempcurPage = param_project.page;
    //列表显示的格式  type   1图标   2表格
    param_project.type=ProListCookie.type ? ProListCookie.type : 1;
    if(param_project.type==2){
        angular.element(".changei a").eq(1).addClass('selected');
        angular.element(".changei a").eq(0).removeClass('selected');
    }

    //判断是否从工作台进来设置默认状态
    if(typeof ($stateParams.list_status) != 'undefined'){
        if($stateParams.list_status != 0){
            project.search_status=$stateParams.list_status;
            param_project.search_status=project.search_status;
            angular.element.each(project.arrSearchStatus, function (key, val) {
                if(val.status == param_project.search_status){
                    angular.element('#status').html(val.statusstr);
                }
            });
        }
    }

    if($location.path() == '/project/mycreatepro/0/0' || $location.path() == '/project/myinvoepro/0/0' || $location.path() == '/project/openpro/0/0'){
        projectModel.setInit1($scope);
    }

    //获取我创建的项目数据
    projectModel.getPro($scope,project.public);

    $scope.page_fun = function () {
        param_project.page= $scope.page.tempcurPage;
        projectModel.getPro($scope,project.public);
    };

    //查询获取我创建的项目数据
    project.searchButton = function() {
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };
        param_project.page = 1;
        if (project.search_begin_time!='' &&  project.search_end_time!='' &&  project.search_begin_time > project.search_end_time ){
             alert("结束时间必须大于开始时间！");
             return false;
        }else{
            param_project.search_status=project.search_status;
            param_project.search_begin_time=project.search_begin_time;
            param_project.search_end_time=project.search_end_time;
            param_project.search_pro_name=project.search_pro_name;
            projectModel.getPro($scope,project.public);
        }
    };

    //显示状态下拉框
    project.statusWinButton = function () {
        //project.isStatusWin = !project.isStatusWin;
        if(angular.element("#projectInfo").is(":hidden")){
            angular.element("#projectInfo").show();
        }else{
            angular.element("#projectInfo").hide();
        }
    }

    //设置选中的查询状态
    project.selectSearchStatus = function (obj) {
        project.search_status = obj.status;
        angular.element('#status').html(obj.statusstr);
        project.isStatusWin = false;
        angular.element("#projectInfo").hide();
    }

    //切换图表显示还是列表显示
    project.switchButton = function(type){
        if(type==1){//修改样式
            angular.element(".changei a").eq(0).addClass('selected');
            angular.element(".changei a").eq(1).removeClass('selected');
        }else{
            angular.element(".changei a").eq(1).addClass('selected');
            angular.element(".changei a").eq(0).removeClass('selected');
        }
        param_project.type = type;
    }
    
    //画图
    project.draw = function(index,allCount,finishCount){
        var canvas = angular.element('.propiebor li canvas');
        var ctx = canvas[index].getContext("2d");
        ctx.beginPath();
        var degree=(finishCount/allCount).toFixed(2);
        ctx.arc(115,115,102,-0.5*Math.PI,degree*(2*Math.PI) - Math.PI/2,false);
        ctx.lineWidth=25;
        ctx.lineCap="round";
        ctx.strokeStyle="#fff";
        ctx.stroke();
        ctx.closePath();
    };

    //跳转到详情页
    project.goDetail = function(pro_id){
        //设置初始化数据
        projectModel.setInit($scope);
        if($location.path().indexOf("/mycreatepro") > 0){//我创建的项目
            $state.go('main.project.mycreatepro.prodetail',{pro_id:pro_id});
        }else if($location.path().indexOf("/myinvoepro") > 0){//我参与的项目
            $state.go('main.project.myinvoepro.prodetail',{pro_id:pro_id});
        }else if($location.path().indexOf("/openpro") > 0){//公开项目
            $state.go('main.project.openpro.prodetail',{pro_id:pro_id});
        }
    }

    //跳转到甘特图页面
    project.goGantt = function(pro_id){
        //设置初始化数据
        projectModel.setInit($scope);
        if($location.path().indexOf("/mycreatepro") > 0){//我创建的项目
            $state.go('main.project.mycreatepro.gantt',{pro_id:pro_id,type:1,position:1});
        }else if($location.path().indexOf("/myinvoepro") > 0){//我参与的项目
            $state.go('main.project.myinvoepro.gantt',{pro_id:pro_id,type:2,position:1});
        }else if($location.path().indexOf("/openpro") > 0){//公开项目
            $state.go('main.project.openpro.gantt',{pro_id:pro_id,type:3,position:1});
        }
    }

    //跳转到项目进度页面
    project.goProgress = function(pro_id){
        //设置初始化数据
        projectModel.setInit($scope);
        if($location.path().indexOf("/mycreatepro") > 0){//我创建的项目
            $state.go('main.project.progress',{pro_id:pro_id,type:1,position:1});
        }else if($location.path().indexOf("/myinvoepro") > 0){//我参与的项目
            $state.go('main.project.progress',{pro_id:pro_id,type:2,position:1});
        }else if($location.path().indexOf("/openpro") > 0){//公开项目
            $state.go('main.project.progress',{pro_id:pro_id,type:3,position:1});
        }
    }



    angular.element(document).bind("click",function(event){
    
        if(angular.element(event.target).parents(".selectbor").length==0){
             angular.element(".selectbor  ul").hide();
        } 

    });

    angular.element(".selecttimebor").bind("click",function(event){
        $(".selectbor ul").hide();
    });

});



