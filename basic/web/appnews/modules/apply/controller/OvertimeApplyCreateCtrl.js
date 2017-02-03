/**
 * Created by nielixin on 2016/9/5.
 */
ApplyMod.controller('OvertimeApplyCreateCtrl',function($scope,$http,$state,$stateParams,applyModel,filtersModel,checkModel,Publicfactory){
    var overtime = $scope.overtime = {};
    var apply_param = $scope.apply_param = {};

    apply_param.model_id = $stateParams.model_id;
    apply_param.type = "0";
    apply_param.currentDay = '';
    apply_param.begin_time_str = '';
    apply_param.end_time_str = '';
    apply_param.note = '';
    // 是否跨天
    apply_param.isNextDay = false;

    $("#masklayer1").show();

    overtime.doSubmit = function() {
        
        if(apply_param.type == 0) {
            alert('请选择加班类型');
            return;
        }

        if('' == apply_param.currentDay || '' == apply_param.begin_time_str || '' == apply_param.end_time_str) {
            alert('请选择加班开始结束时间');
            return;
        }

        var beginDateStr = filtersModel.filterDateTime(apply_param.currentDay).replace(/(00:00)/, apply_param.begin_time_str);
        var endDateStr = filtersModel.filterDateTime(apply_param.currentDay).replace(/(00:00)/, apply_param.end_time_str);
        var bDate = new Date(Date.parse(beginDateStr));
        var eDate = new Date(Date.parse(endDateStr))

        if (bDate > eDate && !apply_param.isNextDay) {
            alert('同一天加班开始时间不可大于结束时间');
            return;
        }

        if (apply_param.isNextDay && apply_param.end_time_str > '06:00') {
            alert('加班跨天，结束时间必须小于次日06:00');
            return;
        }

        //时间判断
        // var beginDateStr = filtersModel.filterTime(apply_param.currentDay);
        // var endDateStr = filtersModel.filterTime(apply_param.end_time);

  
        //var beginHour = parseInt(filtersModel.filterTimeHH(apply_param.currentDay));
        //var endHour = parseInt(filtersModel.filterTimeHH(apply_param.end_time));
        // if(apply_param.currentDay >= apply_param.end_time) {
        //     alert('加班结束时间必须大于开始时间');
        //     return;
        // }


        //时分秒格式化
        // var beginDateStr2 = filtersModel.filterDateTime(apply_param.currentDay);
        // var endDateStr2 = filtersModel.filterDateTime(apply_param.end_time);
        // var d2 = new Date(Date.parse(beginDateStr2));
        // var d1 = new Date(Date.parse(endDateStr2));
        // if ( (d1 - d2)/(1000 * 60 * 60) > 23 ){
        //     alert('加班开始和结束时间必须为同一天,即24小时内！');
        //     return;
        // }
 

        if('' == apply_param.note) {
            alert('请填写加班工作说明');
            return;
        }
        //该验证失效 需要一个汉字字符长度不超过500的验证
        if(Publicfactory.checkEnCnstrlen(apply_param.note) > 200) {
            alert('工作说明必须100字以内');
            return false;
        }else{
            //提交数据
            apply_param.currentDay = filtersModel.filterTime(apply_param.currentDay);
            applyModel.createApply($scope);
        }
        
    }

    overtime.cancel = function() {
        $state.go('^');
        $("#masklayer1").hide();
    }

});