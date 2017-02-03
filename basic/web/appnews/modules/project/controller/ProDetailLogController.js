//项目详情
ProjectMod.controller('ProDetailLogCtrl',function($scope,$http,$rootScope,Publicfactory,projectModel,$stateParams,$state,$location){
    var project = $scope.project={};

    if(!($location.path().indexOf("/progress") > 0)){
        $("#masklayer1").show();//项目进度页查看日志不显示蒙版
    }
    project.param_pro_id = $stateParams.pro_id ? $stateParams.pro_id : 0;
    if(project.param_pro_id==0){
        $state.go('^');
    }
    project.proLog = [];
    project.pageLength=0;

    //获取项目日志
    projectModel.getProLog($scope);

    //返回
    project.returnGo = function(){
        $state.go('^');
        if(!($location.path().indexOf("/progress") > 0)){
            $("#masklayer1").show();//项目进度页查看日志不显示蒙版
        }
    }

});



