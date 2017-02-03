//项目进度
ProjectMod.controller('ProProgressListCtrl',function($scope,$http,$rootScope,Publicfactory,projectModel,$stateParams,$state){
    var project = $scope.project={};
    var project_param = $scope.project_param={};

    project_param.pro_id = $stateParams.pro_id ? $stateParams.pro_id : 0;
    if(project_param.pro_id==0){
        alert('参数错误');
        $state.go('^');
    }
    //项目信息
    project.proInfo = '';
    //项目成员信息
    project.proMember = [];
    //项目任务信息
    project.proTask=[];
    //设置项目任务信息参数
    project_param.u_id = 0;
    project_param.status = 0;
    //翻页长度
    project_param.page = 0;
    //是否显示任务框
    project.isTaskWin = false;

    //获取项目信息
    projectModel.getProProgressList($scope);

    //根据项目成员、任务状态获取任务信息
    project.checkTask = function(u_id,status) {
        project_param.page=0;
        project_param.u_id = u_id;
        project_param.status = status;
        $scope.project.proTask = [];
         projectModel.getProProgressTask($scope);
    };

    //关闭任务窗口
    project.closeTaskWin = function(){
        project.isTaskWin = false;
    }







    //返回
    project.returnGo = function(){
        if(project.position==1){
            $state.go('^',{isInit:0,list_status:0},{reload:true});
        }else{
            if(project.type==1){
                $state.go('main.project.mycreatepro.prodetail',{pro_id:project_param.pro_id});
            }else if(project.type==2){
                $state.go('main.project.myinvoepro.prodetail',{pro_id:project_param.pro_id});
            }else{
                $state.go('main.project.openpro.prodetail',{pro_id:project_param.pro_id});
            }
        }
    }

});



