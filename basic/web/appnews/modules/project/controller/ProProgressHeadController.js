//项目进度
ProjectMod.controller('ProProgressHeadCtrl',function($scope,$http,$rootScope,Publicfactory,projectModel,$stateParams,$state){
    var project = $scope.project={};
    var project_param = $scope.project_param={};

    project_param.pro_id = $stateParams.pro_id ? $stateParams.pro_id : 0;
    if(project_param.pro_id==0){
        alert('参数错误');
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
    //项目信息
    project.proInfo = '';

    //获取项目信息
    projectModel.getProProgressHead($scope);


    //返回
    project.returnGo = function(){
        if(project.position==1){
            if(project.type==1){
                $state.go('main.project.mycreatepro',{isInit:0,list_status:0});
            }else if(project.type==2){
                $state.go('main.project.myinvoepro',{isInit:0,list_status:0});
            }else{
                $state.go('main.project.openpro',{isInit:0,list_status:0});
            }
        }else{
            if(project.type==1){
                $state.go('main.project.mycreatepro.prodetail',{pro_id:project_param.pro_id,isInit:0,list_status:0});
            }else if(project.type==2){
                $state.go('main.project.myinvoepro.prodetail',{pro_id:project_param.pro_id,isInit:0,list_status:0});
            }else{
                $state.go('main.project.openpro.prodetail',{pro_id:project_param.pro_id,isInit:0,list_status:0});
            }
        }
    }

    //项目任务列表页
    project.goProTaskList = function(pro_id,status,type,position,sum){
        if(sum>0){
            $state.go('main.project.protasklist',{pro_id:pro_id,status:status,type:type,position:position});
        }
    }

});



