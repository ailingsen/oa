//公告创建
NoticeMod.controller('NoticeCreateCtrl',function($scope,$http,$rootScope,Publicfactory,$cookies,$cookieStore,$state,$location,noticeModel,FileUploader){
    var notice = $scope.notice = {};
    var paramobj = $scope.paramobj = {};
    //公告标题
    paramobj.title = '';
    //公告内容
    paramobj.content = '';
    //公告附件
    paramobj.att = [];
    $scope.att = [];

    //添加公告
    notice.addButton = function(){
        //数据验证-----------------------
        paramobj.att = $scope.att;
        var ue = UE.getEditor('container');
        paramobj.content = ue.getContent();
        if(Publicfactory.checkEnCnstrlen(ue.getContentTxt())>10000)
        {
            alert('公告内容不能超过5000字');
            return false;
        }
        noticeModel.addNotice($scope);
    }

    $scope.addAttBtn = function(Uploader){
        Uploader.url = "/index.php?r=notice/notice/upload";
        Uploader.onCompleteItem = function (fileItem, response, status, headers) {
            console.log(response);
            if(response.code==1){
                //response.data.data.file_size = (response.data.data.file_size/1024).toFixed(2);
                $scope.att.push(response.data.data);
            }else if(response.code==0){
                fileItem.remove();
                console.log(7899999)
                alert(response.msg);
            }
        }
    }


    //删除附件
    notice.delFileBtn = function(index){
        $scope.att.splice(index,1);
       /* $http.post('/index.php?r=notice/notice/del-att',JSON.stringify($scope.att[index])).success(function (data, status, headers, config) {
            if(data.code == 1) {
                $scope.att.splice(index,1);
            }else{
                alert(data.msg);
            }
        });*/
    }

    //重置按钮
    notice.resetButton = function(){
        //公告标题
        paramobj.title = '';
        //公告内容
        paramobj.content = '';
        //公告附件
        paramobj.att = [];
        $scope.att=[];
        ue.setContent('');
    }

});




