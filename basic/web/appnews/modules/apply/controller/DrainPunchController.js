/**
 * Created by pengyanzhang on 2016/9/1.
 */
ApplyMod.controller('drainPunchCtrl',function($scope,$http,applyModel,$timeout,Publicfactory){

    //局部
    var drain = $scope.drainPunch = {};
    $scope.apply_param = {};
    drain.myDate = new Date();
    drain.beginTime =  drain.myDate.getFullYear()+"-"+(drain.myDate.getMonth()+1)+"-"+drain.myDate.getDate();
    drain.curTime =  drain.myDate.valueOf();

    drain.desc = '';
    drain.is_am = '1';
    drain.note = '';

    $("#masklayer1").show();

    
    drain.drainTimePoint = function(timePoint) {
        drain.is_am = timePoint;
    };
    drain.drainPunch = function() {
        drain.beginTime = $scope.drainPunch.beginTime;
        drain.desc = $scope.drainPunch.desc;
        if(drain.beginTime>drain.curTime){
           alert('忘打卡时间超期，请重新选择！');
            return;
        }
        if(drain.is_am.length<=0){
            alert('请选择上午还是下午忘打卡！');
            return;
        }
        if(drain.desc.length<=0){
            alert('请输入忘打卡说明！');
            return;
        }
        //判断任务描述长度
        if (drain.desc.YLstringcheck()!='') {
            if (Publicfactory.checkEnCnstrlen(drain.desc) > 200) {
                alert('忘打卡说明长度不能大于100个字');
                return false;
            }else{
                $scope.apply_param.check_date = $scope.drainPunch.beginTime;
                $scope.apply_param.note = drain.desc;
                $scope.apply_param.is_am = drain.is_am;
                $scope.apply_param.model_id = 3;
                applyModel.createApply($scope);
            }
        }
    }


});