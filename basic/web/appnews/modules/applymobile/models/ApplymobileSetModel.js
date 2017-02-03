/**
 * Created by nielixin on 2016/9/8.
 */
ApplyMod.factory('applySetModel', function ($http, $state, filtersModel,formshowfactory,$compile) {
    var service = {};

    //展示申请模型详情
    service.showModel = function($scope,model_id,action, callback) {
        action = action || 0;
        $http.get('/index.php?r=apply/apply-model/model-show&model_id=' + model_id).success(function (data) {
            $scope.model.formshowdata = data.data;
            //表单还原
            formshowfactory.formshow(data.data,$compile,$scope,action);

            typeof callback == 'function' && callback();
        });
    }

    service.getCustomList = function($scope) {
        $http.get('/index.php?r=apply/apply-model/custom-model-list').success(function (data) {
            $scope.apply.customList = data.data;
        });
    }

    service.getModelHTML = function(model_id) {
        $http.get('/index.php?r=apply/apply-model/model-show&model_id=' + model_id).success(function (data) {
            $(".createforms .scrollbor").html(data.data.html);
            $(".createforms .field:first").trigger("click");
        });
    }

    return service;
});