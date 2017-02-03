NanoCloudMod.factory('nanoCloudModel',function($http,$state,$cookieStore,$sce){
    var  service={};

    //主页面模拟数据
    service.getMyFiles=function($scope){
        var arr=[
            {fileType:'pdf',fileName:'示范文档1.pdf',modifyTime:'2016-10-24 16:19',size:'13.5',isSelected:false,isRenaming:false},
            {fileType:'ppt',fileName:'示范文档2.pdf',modifyTime:'2016-10-24 16:19',size:'3.5',isSelected:false,isRenaming:false},
            {fileType:'ai',fileName:'示范文档3.pdf',modifyTime:'2016-10-24 16:19',size:'80.5',isSelected:false,isRenaming:false}
        ];
        $scope.nanocloud.myFiles=arr;
    }

    //回收站模拟数据
    service.getRecycleContent=function($scope){
        var arr=[
            {fileType:'pdf',fileName:'示范文档1.pdf',modifyTime:'2016-10-24 16:19',size:'13.5',isSelected:false,isRenaming:false},
            {fileType:'ppt',fileName:'示范文档2.pdf',modifyTime:'2016-10-24 16:19',size:'3.5',isSelected:false,isRenaming:false},
            {fileType:'ai',fileName:'示范文档3.pdf',modifyTime:'2016-10-24 16:19',size:'80.5',isSelected:false,isRenaming:false}
        ];
        $scope.nanocloud.recycleContent=arr;
    }

    return service;
});

