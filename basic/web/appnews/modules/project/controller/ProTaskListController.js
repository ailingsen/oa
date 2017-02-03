//项目任务列表
ProjectMod.controller('ProTaskListCtrl',function($scope,$http,$rootScope,Publicfactory,projectModel,$stateParams,$state){
    var project = $scope.project={};
    var project_param = $scope.project_param={};
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    //任务信息
    project.taskInfo = '';
    //项目信息
    project.proInfo = '';

    project_param.page = 1;
    project_param.pro_id = $stateParams.pro_id ? $stateParams.pro_id : 0;
    if(project_param.pro_id==0){
        alert('参数错误');
        $state.go('^');
    }
    project_param.status = $stateParams.status ? $stateParams.status : 0;
    if(project_param.status!=1 && project_param.status!=2 && project_param.status!=3){
        alert('参数错误');
        $state.go('^');
    }
    //处理tab标签样式
    projectModel.setTab($scope);

    if($stateParams.type!=1 && $stateParams.type!=2 && $stateParams.type!=3){
        alert('参数错误');
        $state.go('^');
    }
    project.type=$stateParams.type;
    if($stateParams.position!=1 && $stateParams.position!=2){
        alert('参数错误');
        $state.go('^');
    }
    project.position=$stateParams.position;

    //获取项目信息
    projectModel.getProInfo($scope,project_param.pro_id);

    //获取项目任务信息
    projectModel.getProTaskListInfo($scope);

    //查看项目信息
    project.tabButton = function(status) {
        if(status!=1 && status!=2 && status!=3){
            alert("参数错误！");
        }
        project_param.status = status;
        //处理tab标签样式
        projectModel.setTab($scope);

        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };
        project_param.page = 1;

        projectModel.getProTaskListInfo($scope);
    }

    //翻页方法
    $scope.page_fun = function () {
        project_param.page = $scope.page.tempcurPage;
        projectModel.getProTaskListInfo($scope);
    };

});



