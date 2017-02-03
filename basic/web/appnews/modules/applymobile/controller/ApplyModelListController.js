/**
 * Created by nielixin on 2016/9/1.
 */
ApplyMod.controller('ApplyModelListController',function($scope,$http,$rootScope,Publicfactory,applyModel,applySetModel){
    var model = $scope.model = {};
    var modelParam = $scope.modelParam = {};
    $scope.modelid = 1
    $scope.modeltype =2
    //获取所有表单
    applyModel.getAllModel($scope);

    modelParam.modelId = 0;
    modelParam.title='';

    //修改表单标题弹窗
    model.editTitlePop = function(modelId,oldTitle) {
        model.showTitleWin = true;
        modelParam.modelId = modelId;
        modelParam.title = oldTitle;
        model.showMask();
    }

    //确定修改表单标题
    model.doEditTitle = function() {

        if('' == modelParam.title) {
            alert('请填写标题');
            return false;
        }
        if('' == modelParam.title.YLstringcheck()) {
            alert('标题不能为空！');
            return false;
        }
        var patt=/[^\u4e00-\u9fa5a-zA-Z\d]/g;
        if( modelParam.title.YLstringcheck().match(patt) ){
            alert("表单标题只能包含中文、字母、数字");
            return false;
        }
        if (Publicfactory.checkEnCnstrlen(modelParam.title) > 40) {
            alert("请填写标题不能超过20个汉字");
            return false;
        }else{
            applyModel.editTitle(modelParam.modelId,modelParam.title,$scope);
            model.showTitleWin = false;
            model.closeMask();
        }

    }

    //停用流程弹窗
    model.stopModelPop = function(modelId,name) {
        model.showStopWin = true;
        modelParam.modelId = modelId;
        model.tipTitle = name;
        model.showMask();
    }

    //确定停用流程
    model.doStop = function() {
        applyModel.doStop(modelParam.modelId,0,$scope);
        model.showStopWin = false;
        model.closeMask();
    }

    //启用流程
    model.turnOnModel = function(modelId) {
        applyModel.doStop(modelId,1,$scope);
    }

    //删除表单弹窗
    model.delModelPop = function(modelId,name) {
        model.showDelWin = true;
        modelParam.modelId = modelId;
        model.tipTitle = name;
        model.showMask();
    }

    //确定删除表单
    model.doDel = function() {
        applyModel.doDel(modelParam.modelId,$scope);
        model.showDelWin = false;
        model.closeMask();
    }

    //表单预览栏目切换
    model.tabSwitch = function($event,status) {
        var target = angular.element($event.target);
        target.addClass("selected");
        if(status == 0) {
            target.next().removeClass('selected');
            target.parents('.ui-widget-winbor').find('.scrollbor').eq(0).show();
            target.parents('.ui-widget-winbor').find('.scrollbor').eq(1).removeClass('block');
        }else {
            $scope.status = 1
            target.prev().removeClass('selected');
            target.parents('.ui-widget-winbor').find('.scrollbor').eq(1).addClass('block');
            target.parents('.ui-widget-winbor').find('.scrollbor').eq(0).hide();
        }
    }

    //启用遮罩
    model.showMask = function(model_id,model_type) {
        var _tempStatus = new Date();
        $scope.status = _tempStatus.getTime();
        $scope.modelid = model_id
        $scope.modeltype = model_type
        $("#masklayer1").show();
    };
    //关闭遮罩
    model.closeMask = function(modeltype) {
        $scope.status = 0;
        $("#masklayer1").hide();
        var target = angular.element('#formpresentation .changebtn span');
        if(modeltype == 0) {
            target.eq(0).addClass("selected");
            target.eq(1).removeClass('selected');
            angular.element('#formpresentation .scrollbor').eq(0).show();
            angular.element('#formpresentation .scrollbor').eq(1).removeClass('block');
        }
    };
    
    
    //自定义表单 拿数据
    model.derectiveParam = 0;
    model.customerformshow = function(model_id){
        angular.element('#scroll').html('');
        model.customerform = true
        //自定义表单预览
        applySetModel.showModel($scope,model_id);
        // model.derectiveParam = model_id;
        //表单还原
        //formshowfactory.formshow(model.formshowdata,$compile,$scope,0);
    };


});