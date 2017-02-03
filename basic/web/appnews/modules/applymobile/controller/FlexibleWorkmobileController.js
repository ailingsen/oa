/**
 * Created by pengyanzhang on 2016/9/9.
 */
ApplymobileMod.controller('flexibleWorkmobileCtr',function($scope,$http,checkModel,applyModel,Publicfactory,$state){

    //局部
    var flexibleWork = $scope.flexibleWork = {};
    $scope.apply_param = {};
    flexibleWork.timePoint = [];
    flexibleWork.myDate = new Date();
    flexibleWork.beginTime = flexibleWork.myDate.valueOf();
    flexibleWork.endTime = flexibleWork.myDate.valueOf();
    flexibleWork.desc = '';
    flexibleWork.files = [];
    flexibleWork.info = {};
    flexibleWork.id = '';
    flexibleWork.file_root = '';
    applyModel.getFlexibleWorkTimePoint($scope,1);
    $scope.att={};

    flexibleWork.selectTimePoint = function(id) {
        flexibleWork.id = id;
    };

    $("#masklayer1").show();
    $(".mainsbar").css({"overflow-y":"hidden"});

    //添加附件
    $scope.addFileBtn = function(uploader){
        uploader.url = '/index.php?r=apply/apply/upload';
        uploader.onCompleteItem = function (fileItem, response, status, headers) {
            if(response.code==1){
                flexibleWork.files.push(response.data.data);
                var result = [], isRepeated;
                var len = flexibleWork.files.length;
                for (var i = 0; i < len; i++) {
                    isRepeated = false;
                    var resultLen = result.length;
                    for (var j = 0; j < resultLen; j++) {
                        if (flexibleWork.files[i].file_name == result[j].file_name) {
                            isRepeated = true;
                            break;
                        }
                    }
                    if (!isRepeated) {
                        result.push(flexibleWork.files[i]);
                    }
                }
                flexibleWork.files = result;
                flexibleWork.file_root = response.file_root;
            }else if(response.code==0){
                fileItem.remove();
                alert(response.msg);
            }
        };

    };

    //删除上传的附件
    flexibleWork.delFiles = function(index){
        flexibleWork.files.splice(index,1);
    };

    //点击提交
    flexibleWork.submitRankInfo = function() {
        flexibleWork.desc = $scope.flexibleWork.desc;
        $scope.apply_param.begin_time = Date.parse(new Date($scope.flexibleWork.beginTime))/1000;
        $scope.apply_param.end_time = Date.parse(new Date($scope.flexibleWork.endTime))/1000;
        $scope.apply_param.model_id = 4;
        $scope.apply_param.store_id = $scope.flexibleWork.id;
        $scope.apply_param.att = flexibleWork.files;
        $scope.apply_param.note = flexibleWork.desc;
        if($scope.apply_param.begin_time >= $scope.apply_param.end_time){
            alert('申请结束时间不能小于等于开始时间，请重新选择！');
            return;
        }
        if($scope.apply_param.store_id<=0){
            alert('请选择需抵用的加班时间!');
            return;
        }
        if(flexibleWork.desc.length<=0 || Publicfactory.checkEnCnstrlen(flexibleWork.desc)>200){
            alert('请输入详细说明,且不能超过100个字！');
            return false;
        }else{
            applyModel.createApply($scope);
        }
        
    }


    flexibleWork.cancel = function() {
        $("#masklayer1").hide();
        $(".mainsbar").css({"overflow-y":"auto"});
        $state.go('^');
    }

});
