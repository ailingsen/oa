
/**
 * 提示框数据
 */
oaApp.factory('noticeService', ['$timeout', function($timeout) {
    return {
        message : null,
        type : null,
        gourl : null,
        setMessage : function(msg,type,time,goUrl){

            this.message = msg;
            this.type = type;
            this.gourl = goUrl;
            $("#masklayer1").show();

            //提示框显示最多3秒消失
            var _self = this;
            if (time) {
                $timeout(function () {
                    _self.clear();
                }, time);
            } 
        },
        clear : function(){
            this.message = null;
            this.type = null;
            this.gourl = null;
            $("#masklayer1").hide();
        }
    };
}]);

/**
 * 权限验证
 */
oaApp.factory('permissionService',function($http,$cookieStore,$rootScope){
    var  service={};

    //获取所有权限数据
    service.getAllPermission = function($scope){
        $http.post('/index.php?r=permission/permission/permissionlist', JSON.stringify($scope.permission_param))
            .success(function(data,status) {
                if(data.code==20000){
                    $scope.permission.permissionList = data.data;
                }
            })
    }

    //检查权限
    service.checkPermission = function(pcode){
        //if($cookieStore.get('userper') != null || $cookieStore.get('userper') != ''){
            //var permObject = angular.fromJson($cookieStore.get('userper'));
        if(JSON.parse(window.localStorage.userper) != null || JSON.parse(window.localStorage.userper) != ''){
            var permObject = angular.fromJson(JSON.parse(window.localStorage.userper));
            if(pcode != '' && pcode != undefined){
                pcode = pcode.toLowerCase();
                var isFind = false;
                angular.forEach(permObject,function(v,k){
                    if(v.toLowerCase() == pcode){
                        isFind = true;
                    }
                });
            }
        }
        if (isFind) {
            return true;
        }
        alert('没有权限，请联系管理员!');
        return false;
    }


    return service;
})