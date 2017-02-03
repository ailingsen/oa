/**
 * Created by pengyanzhang on 2016/8/31.
 */
ApplymobileMod.controller('rankApplymobileCtrl',function($scope,$http,checkModel,applyModel,Publicfactory,$state,$location,$timeout){

    //局部
    var rank = $scope.rankApply = {};
    $scope.att = {};
    rank.rankApplyDesc = '';
    //上传附件
    $scope.apply_param = {};
    rank.files = [];
    rank.modelId = 5;
    rank.file_root = '';

    $("#masklayer1").show();
    
     


    //添加附件
    $scope.addFileBtn = function(uploader){
        uploader.url = '/index.php?r=apply/apply/upload';
        uploader.onCompleteItem = function (fileItem, response, status, headers) {
            if(response.code==1){
                rank.files.push(response.data.data);
                var result = [], isRepeated;
                var len = rank.files.length;
                for (var i = 0; i < len; i++) {
                    isRepeated = false;
                    var resultLen = result.length;
                    for (var j = 0; j < resultLen; j++) {
                        if (rank.files[i].file_name == result[j].file_name) {
                            isRepeated = true;
                            break;
                        }
                    }
                    if (!isRepeated) {
                        result.push(rank.files[i]);
                    }
                }
                rank.files = result;
                rank.file_root = response.file_root;
            }else if(response.code==0){
                fileItem.remove();
                alert(response.msg);
            }
        };

    };

    //删除上传的附件
    rank.delFiles = function(index){
        rank.files.splice(index,1);
    };

    //点击提交
    rank.submitRankInfo = function() {
        rank.rankApplyDesc = $scope.rankApply.rankApplyDesc;
        if(rank.rankApplyDesc.length<=0 || Publicfactory.checkEnCnstrlen(rank.rankApplyDesc)>1000){
            alert('请输入内容,且不能超过500字！');
            return false;
        }
        if(rank.files.length<=0){
            alert('请上传附件！');
            return false;
        }else{
            $scope.apply_param.model_id = rank.modelId;
            $scope.apply_param.att = rank.files;
            $scope.apply_param.note = rank.rankApplyDesc;
            applyModel.createApply($scope);
        }
        
    }

    rank.cancel = function() {
        $("#masklayer1").hide();
        $(".mainsbar").css({"overflow-y":"auto"});
        $state.go('^');
    }


    if($location.path().indexOf("/rankApply") > 0){
        $timeout(function(){
            $(".mainsbar").css({"overflow-y":"hidden"});
        });
    } 

});
