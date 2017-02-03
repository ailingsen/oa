
NanoCloudMod.controller('NanoCloudRecycleCtrl',function($scope,$http,$state,nanoCloudModel,$rootScope,$stateParams,$timeout,$filter,$state,Publicfactory){
    var nanocloud = $scope.nanocloud = {};
    //全选框
    nanocloud.isAllSelected=false;
    //选中项目的数量
    nanocloud.selecteds=0;
    //列表内容头部操作菜单
    nanocloud.isShowOperatorMenu=false;
    //回收站列表内容循环数据
    nanocloud.recycleContent=[];


    //获取数据
    nanoCloudModel.getRecycleContent($scope);


    //右键菜单
    nanocloud.indexs =-1;
    $scope.getindex = function($index){
        nanocloud.indexs=$index;
        angular.forEach(nanocloud.recycleContent,function(data){
            data.isSelected=false;
        })
        nanocloud.selected();
    };
    $scope.fuzhi = function () {
        if(nanocloud.indexs>=0){
            alert(nanocloud.indexs);
        }else{
            console.log(nanocloud.indexs);
        }
    };


    //复选框
    nanocloud.selected=function(index,i){
        if(i==0){
            nanocloud.isAllSelected=!nanocloud.isAllSelected;
            if(nanocloud.isAllSelected){
                angular.forEach(nanocloud.recycleContent,function(data){
                    data.isSelected=true;
                })
            }else{
                angular.forEach(nanocloud.recycleContent,function(data){
                    data.isSelected=false;
                })
            }
        }else if(i==1){
            nanocloud.recycleContent[index].isSelected=!nanocloud.recycleContent[index].isSelected;
        }else{
            nanocloud.recycleContent[nanocloud.indexs].isSelected=true;
        }
        var arr=nanocloud.recycleContent.filter(function(data){return data.isSelected==true;});
        nanocloud.selecteds=arr.length;
        nanocloud.isShowOperatorMenu=arr.length>0?true:false;
        nanocloud.isAllSelected=arr.length==nanocloud.recycleContent.length?true:false;
    }
    /*nanocloud.contextMenuClose=function(index){
        nanocloud.recycleContent[index].isSelected=false;
        var arr=nanocloud.recycleContent.filter(function(data){return data.isSelected==true;});
        nanocloud.selecteds=arr.length;
        nanocloud.isShowOperatorMenu=arr.length>0?true:false;
        nanocloud.isAllSelected=arr.length==nanocloud.recycleContent.length?true:false;
    }*/


    //回到纳米云主体
    nanocloud.backToNanocloudSection=function(){
        $state.go('main.nanoCloud.nanoCloudSection');
    }


    //删除文件
    nanocloud.deleteFile=function(event){
        var arr= _.remove(nanocloud.recycleContent,function(data){
            return data.isSelected==true;
        })
        event&&angular.element(event.target).parents('.right-click').removeClass('open');
    }
})

















