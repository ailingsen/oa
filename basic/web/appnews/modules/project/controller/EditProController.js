//编辑项目
ProjectMod.controller('EditProCtrl',function($scope,$http,$rootScope,Publicfactory,projectModel,$stateParams,$state){
    $("#masklayer1").hide();
    var project = $scope.project={};
    project.param_pro_id = $stateParams.pro_id ? $stateParams.pro_id : 0;
    if(project.param_pro_id==0){
        $state.go('^');
    }

    if($stateParams.type!=1 && $stateParams.type!=2 && $stateParams.type!=3){
        $state.go('^');
        alert('错误的链接！');
    }
    project.type=$stateParams.type;

    var paramobj = $scope.paramobj={};
    //项目Id
    paramobj.pro_id = 0;
    //项目名称
    paramobj.pro_name = '';
    //公开状态
    paramobj.public_type = 3;
    //开始时间
    paramobj.begin_time = '';
    //结束时间
    paramobj.end_time = '';
    //项目成员
    paramobj.proMember = [];

    //是否显示项目成员选择窗口
    project.isDispMemWin = false;
    //项目成员选择窗口显示的成员
    project.allMember = [];
    projectModel.getMembers($scope, '');
    //查询成员
    project.search_mem = '';
    //查询成员临时值
    project.search_mem_temp = '';
    //工作日统计
    project.workDay = '';
    //判断是否可编辑时间
    project.isEditTime = false;

    //获取项目信息
    projectModel.getProDetailSimp($scope);

    //显示或隐藏选择成员窗口
    project.SelectMemWin = function(){
        project.isDispMemWin = !project.isDispMemWin;
        if(project.isDispMemWin && project.allMember.length==0){
            projectModel.getMembers($scope);
        }
    }

    //搜索要添加的项目成员
    project.searchAddMem = function(){
        if(project.search_mem_temp != project.search_mem){
            project.search_mem_temp = project.search_mem;
            projectModel.getMembers($scope);
        }
    }

    //选择项目成员
    project.selectMemButton = function (obj){
        var isMember=1;
        angular.element.each(paramobj.proMember, function (key, val) {
            if(val['u_id'] == obj.u_id){
                isMember=0;//已经是项目成员
            }
        });
        if(isMember==1){
            obj.owner=0;
            paramobj.proMember.push(obj);
        }
    }

    //删除已添加的项目成员
    project.delProMem = function(index){
        paramobj.proMember.splice(index,1);
    }

    //编辑保存项目
    project.editButton = function() {
        projectModel.editPro($scope);
    }

    //获取工作日
    project.getWordDay = function(){
        if(paramobj.begin_time == '' || paramobj.end_time==''){
            return false;
        }
        if(paramobj.begin_time > paramobj.end_time){
            project.workDay = 0;
        }else{
            projectModel.getWorkDay($scope);
        }
    }

    //返回
    project.returnGo = function(){
        if(project.type==1){
            $state.go('main.project.mycreatepro.prodetail',{isInit:0,list_status:0,pro_id:project.param_pro_id});
        }else if(project.type==2){
            $state.go('main.project.myinvoepro.prodetail',{isInit:0,list_status:0,pro_id:project.param_pro_id});
        }else{
            $state.go('main.project.openpro.prodetail',{isInit:0,list_status:0,pro_id:project.param_pro_id});
        }
    }

});



