//创建项目
ProjectMod.controller('CreateProCtrl',function($scope,$http,$rootScope,Publicfactory,projectModel,$cookieStore,$state,permissionService){
    if (!permissionService.checkPermission('ProjectCreate')) {
        $state.go('main.index');
        return false;
    }
    var project = $scope.project={};
    var paramobj = $scope.paramobj={};
    //获取当前用户信息
    project.userInfo = $cookieStore.get('userInfo');
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
    //项目创建者默认为项目成员
    projectModel.getProCreateMemInfo($scope);
    //是否显示项目成员选择窗口
    project.isDispMemWin = false;
    //项目成员选择窗口显示的成员
    project.allMember = [];
    projectModel.getMembers($scope, '');
    //查询成员
    project.search_mem = '';
    //查询成员临时值
    project.search_mem_temp = '';
    //是否显示项目创建成功窗口
    project.isCreateProSuccessWin = false;
    //工作日统计
    project.workDay = 0;
    //创建项目后保存项目ID
    project.pro_id = '';
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

    //创建项目
    project.addButton = function(){
        if( paramobj.pro_name ==''){
            alert("项目名称不能为空");
            return false;
        }
        if( paramobj.begin_time=='' || paramobj.end_time==''){
            alert("开始时间或结束时间不能为空");
            return false;
        }else{
            //数据判断
            projectModel.addPro($scope);
        }
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

    //重置
    project.resetButton = function(){
        //项目名称
        paramobj.pro_name = '';
        //公开状态
        paramobj.public_type = 3;
        //开始时间
        paramobj.begin_time = '';
        //结束时间
        paramobj.end_time = '';
        project.workDay = 0;
        var temp = Array();
        angular.element.each(paramobj.proMember, function (key, val) {
            if(val['owner']==1){
                temp = val;
            }
         });
        paramobj.proMember = [];
        paramobj.proMember.push(temp);
    }

    //项目创建成功跳转
    project.isCreateTask = function(isCreate){
        $("#masklayer2").hide();
        if(isCreate==1){
            $state.go('main.task.create',{pro_id:project.pro_id});
        }else{
            $state.go('main.project.mycreatepro');
        }
    }

});



