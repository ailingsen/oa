//项目成员工作报告
ProjectMod.controller('ProMemReportCtrl',function($scope,$http,$rootScope,Publicfactory,projectModel,$stateParams,$state,$location){
    var project = $scope.project={};
    var param_project = $scope.param_project={};
    param_project.pro_id = $stateParams.pro_id ? $stateParams.pro_id : 0;
    if(project.pro_id==0){
        $state.go('^');
    }
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
    //查看时间
    param_project.date = '';
    //初始页数
    param_project.page = 1;
    //工作报告信息
    project.reportInfo='';

    //翻页对象
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };

    //获取报告
    projectModel.getProMemWorkReport($scope);

    //根据时间查询报告
    project.getReport = function(){
        //翻页对象
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };
        //初始页数
        param_project.page = 1;
        //获取报告
        projectModel.getProMemWorkReport($scope);
    }

    $scope.page_fun = function () {
        param_project.page= $scope.page.tempcurPage;
        projectModel.getProMemWorkReport($scope);
    };

    //返回
    project.returnGo = function(){
        if(project.type==1){
            $state.go('main.project.mycreatepro.gantt',{pro_id:param_project.pro_id,type:project.type,position:project.position,isInit:0,list_status:0});
        }else if(project.type==2){
            $state.go('main.project.myinvoepro.gantt',{pro_id:param_project.pro_id,type:project.type,position:project.position,isInit:0,list_status:0});
        }else{
            $state.go('main.project.openpro.gantt',{pro_id:param_project.pro_id,type:project.type,position:project.position,isInit:0,list_status:0});
        }
    }

});



