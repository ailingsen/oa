/**
 * Created by nielixin on 2016/9/8.
 */
ApplyMod.controller('applyModelCreateCtrl',function($scope,$http,$rootScope,applyModel,applySetModel,formshowfactory){
    //局部
    var apply = $scope.apply = {};

    //获取自定义申请单模型列表
    applySetModel.getCustomList($scope);

    apply.addfilesbtn = function(){
        angular.element(".modbor").css({"visibility":"visible"});
        angular.element(".modbor .applyindexeditbor").addClass("md-show");
    };

    //自定义表单选择
    apply.changeforms = function(model_id){
        if(model_id==0) {
            $(".createforms .scrollbor").html('');
            $(".formseditbor .scrollbor").html('');
        }else {
            applySetModel.getModelHTML(model_id);
        }
    };

    //表单预览
    apply.formPreview = function(){
        var formtitle = $(".createforms .titlebor input").val();
        var formhtml = $(".createforms .scrollbor").html();

        $(".formPreview .formtitles").html(formtitle);
        $(".formPreview .scrollbor").html(formhtml);
        $(".formPreview .borderbor").removeClass("field");

        $(".formPreview .borderbor.SelectSelect select").removeAttr("disabled");

        $(".formPreview,#masklayer1").show();
    };

    //退出预览
    apply.formPreviewClose = function(){
        $(".formPreview .scrollbor").html('');
        $(".formPreview,#masklayer1").hide();
    };

});