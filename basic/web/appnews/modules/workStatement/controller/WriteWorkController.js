
//我的工作报告
workStatementMod.controller('writeWorkCtrl',function($scope,$http,$rootScope,$state,$stateParams,workModel,permissionService,Publicfactory){
    if (!permissionService.checkPermission('WorkstateMyWorkstateWrite')) {
        $state.go('main.index');
        return false;
    }
    var work = $scope.work = {};
    if(!$stateParams.work_id){
        // alert('参数错误');
        $state.go('^');
        return false;
    }
    //工作项
    var param_work = $scope.param_work = {};
    //任务完成状态
    work.task_status = [
        {'status':0, 'statusstr':'请选择完成状态'},
        {'status':1, 'statusstr':'已完成'},
        {'status':2, 'statusstr':'未完成'}
    ];
    work.selected_task_status = [];
    work.tasks = [];

    work.work = '';
    work.plan = '';
    
    
    //任务详情
    work.work_detail = {};
    work.work_id = $stateParams.work_id;
    workModel.getWorkDetail($stateParams.work_id, true, $scope);

    //保存成功提示
    work.addSuccess = false;
    
    work.addWork = function(){
        param_work.work_data = {'work_content':work.work, 'plan_content':work.plan, 'status':1};
        param_work.work_id = work.work_id;
        if (work.work == null || Publicfactory.checkEnCnstrlen(work.work) == 0) {
            alert("工作项不能为空");
            return;
        }
        if (Publicfactory.checkEnCnstrlen(work.work) > 2000) {
            alert("工作项不能超过1000个汉字");
            return;
        }
        if (work.plan == null || Publicfactory.checkEnCnstrlen(work.plan) == 0) {
            alert("工作计划不能为空");
            return;
        }
        if (Publicfactory.checkEnCnstrlen(work.plan) > 2000) {
            alert("工作计划不能超过1000个汉字");
            return;
        }
        // param_work.tasks = [];
        //
        // angular.forEach(work.work_detail.tasks, function(data,index,array){
        //     param_work.tasks.push({'task_id':data.task_id, 'status':work.selected_task_status[index].status});
        // });
        workModel.addWork($scope, $state);
    };
    work.back = function(){
        $state.go('main.workStatement.myWorkStatementTable', {}, {'reload': false});
    }

});



