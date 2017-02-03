//项目详情
ProjectMod.controller('ProDetailCtrl',function($scope,$http,$rootScope,Publicfactory,projectModel,$stateParams,$state,$location,$cookieStore){
    //显示蒙版
    $("#masklayer1").show();
    var project = $scope.project={};
    var param_project = $scope.param_project={};
    project.param_pro_id = $stateParams.pro_id ? $stateParams.pro_id : 0;
    if(project.param_pro_id==0){
        $state.go('^');
    }
    project.userInfo = $cookieStore.get('userInfo');
    //项目信息
    project.proInfo='';
    //是否显示项目延期窗口
    project.isDelayWin=false;
    //是否显示归档确认框
    project.isCompleteWin=false;
    //是否显示项目删除确认框
    project.isDelWin=false;
    //项目成员信息
    project.proMember='';
     //删除按钮标志位
    project.delTag=0;
    //项目延期设置项目时间
    project.param_delay_time = '';
    //项目延期的原因
    project.param_delay_reason = '';
    //是否显示成员报告
    project.isPreMenReport = false;
    project.paramPreMenReport = {};
    project.paramPreMenReport.u_id = '';
    project.paramPreMenReport.pro_id = '';
    project.proMemReportList = '';

    //获取项目详情
    projectModel.getProDetail($scope);

    //删除项目
    project.delProButton = function() {
        projectModel.delPro($scope);
    }

    //显示项目延期设置界面
    project.proDelayWin = function() {
        $scope.project.param_delay_time = $scope.project.proInfo['delay_time'];
    }

    //项目延期
    project.proDelayButton = function() {
        projectModel.delayPro($scope);
    }

    //项目归档
    project.proCompleteButton = function() {
        projectModel.CompletePro($scope);
    }

    //画图
    project.draw = function(index,allCount,finishCount){
        var canvas = angular.element('.bgw100 canvas');
        var ctx = canvas[index].getContext("2d");
        ctx.beginPath();
        var degree=(finishCount/allCount).toFixed(2);
        ctx.arc(115,115,102,-0.5*Math.PI,degree*(2*Math.PI) - Math.PI/2,false);
        ctx.lineWidth=25;
        ctx.lineCap="round";
        ctx.strokeStyle="#ffffff";
        ctx.stroke();
        ctx.closePath();
    };

    //显示延期窗口
    project.openDelayWinButton = function(){
        //项目延期设置项目时间
        project.param_delay_time = '';
        //项目延期的原因
        project.param_delay_reason = '';
        project.isDelayWin=true;
        $("#masklayer2").show();
    }

    //保存项目延期
    project.saveProDelay = function(){
        projectModel.delayPro($scope);
    }

    //关闭项目延期窗口
    project.closeDelayWinButton = function(){
        project.isDelayWin=false;
        $("#masklayer2").hide();
    }

    //显示归档确认框
    project.openCompleteWinButton = function(){
        project.isCompleteWin=true;
        $("#masklayer2").show();
    }

    //项目归档
    project.proCompleteButton = function(){
        projectModel.CompletePro($scope);
    }

    //关闭归档确认窗口
    project.closeCompleteWinButton = function(){
        project.isCompleteWin=false;
        $("#masklayer2").hide();
    }

    //显示删除确认框
    project.openDelWinButton = function(){
        project.isDelWin=true;
        $("#masklayer2").show();
    }

    //项目删除
    project.delProButton = function(){
        projectModel.delPro($scope);
    }

    //关闭删除确认窗口
    project.closeDelWinButton = function(){
        project.isDelWin=false;
        $("#masklayer2").hide();
    }

    //返回
    project.returnGo = function(){
        $state.go('^',{isInit:0,list_status:0},{reload:true});
        //$state.go('^',{isInit:0,list_status:0},{reload:false});
        //projectModel.returnList($scope);
    }

    //查看日志
    project.goLog = function(){
        if($location.path().indexOf("/mycreatepro") > 0){//我创建的项目
            $state.go('main.project.mycreatepro.prodetail.prolog',{pro_id:project.param_pro_id});
        }else if($location.path().indexOf("/myinvoepro") > 0){//我参与的项目
            $state.go('main.project.myinvoepro.prodetail.prolog',{pro_id:project.param_pro_id});
        }else if($location.path().indexOf("/openpro") > 0){//公开项目
            $state.go('main.project.openpro.prodetail.prolog',{pro_id:project.param_pro_id});
        }
    }

    //跳转到甘特图页面
    project.goGantt = function(pro_id){
        if($location.path().indexOf("/mycreatepro") > 0){//我创建的项目
            $state.go('main.project.mycreatepro.gantt',{pro_id:pro_id,type:1,position:2});
        }else if($location.path().indexOf("/myinvoepro") > 0){//我参与的项目
            $state.go('main.project.myinvoepro.gantt',{pro_id:pro_id,type:2,position:2});
        }else if($location.path().indexOf("/openpro") > 0){//公开项目
            $state.go('main.project.openpro.gantt',{pro_id:pro_id,type:3,position:2});
        }
    }

    //跳转到项目进度页面
    project.goProgress = function(pro_id){
        if($location.path().indexOf("/mycreatepro") > 0){//我创建的项目
            $state.go('main.project.progress',{pro_id:pro_id,type:1,position:2});
        }else if($location.path().indexOf("/myinvoepro") > 0){//我参与的项目
            $state.go('main.project.progress',{pro_id:pro_id,type:2,position:2});
        }else if($location.path().indexOf("/openpro") > 0){//公开项目
            $state.go('main.project.progress',{pro_id:pro_id,type:3,position:2});
        }
    }

    //编辑项目
    project.goEdit = function(pro_id){
        var type=1;
        if($location.path().indexOf("/mycreatepro") > 0){//我创建的项目
            type=1;
        }else if($location.path().indexOf("/myinvoepro") > 0){//我参与的项目
            type=2;
        }else if($location.path().indexOf("/openpro") > 0){//公开项目
            type=3;
        }
        $state.go('main.project.editpro',{pro_id:pro_id,type:type});
    }

     //查看工作报告
    project.openProMemReport = function(u_id){
        project.paramPreMenReport.u_id = u_id;
        project.paramPreMenReport.pro_id = project.param_pro_id;
        projectModel.getProMemReport($scope);
        $('#masklayer2').show();
    }
	
    //关闭工作报告
    project.closeProMemReport = function(){
        project.isPreMenReport = false;
        project.paramPreMenReport = {};
        project.paramPreMenReport.u_id = '';
        project.paramPreMenReport.pro_id = '';
        project.proMemReportList = '';
        $('#masklayer2').hide();
    }

});



