personalInfoMod.factory('userModel', function($http){
    var  service={};

    //获取我的个人信息
    service.getMyUserInfo=function($scope){
        $http.get('/index.php?r=userinfo/userinfo/user-info')
            .success(function(data, status) {
                data.data.memberinfo.head_img = data.data.memberinfo.head_img + '?' + Math.random();
                $scope.userInfo.detail = data.data.memberinfo;
                $scope.user_param.phone = $scope.userInfo.detail.phone;
                $scope.userInfo.skills = data.data.skills;
            });
    };
    //获取我的技能与积分
    service.getSkillScore=function($scope){
        $http.get('/index.php?r=userinfo/userinfo/getmemberskill')
            .success(function(data, status) {
                data.data.memberinfo.head_img = data.data.memberinfo.head_img + '?' + Math.random();
                $scope.userInfo.detail = data.data.memberinfo;
                $scope.userInfo.skills = data.data.skill_list;
                $scope.userInfo.skill_rulls = data.data.skill_rulls;
            });
    };

    //获取我的假期
    service.getMyVacation=function($scope){
        $http.get('/index.php?r=userinfo/userinfo/get-vacation')
            .success(function(data, status) {
                $scope.vacation.annual_leave = data.data.vacation;
                $scope.vacation.over_time = data.data.over_time;
            });
    };
    

    //修改密码 修改手机号 修改推送
    service.editeUserInfo = function($scope, type){
        var str = JSON.stringify($scope.user_param);
        if (type == 1) {
            str = JSON.stringify($scope.param)
        }
        $http.post('/index.php?r=userinfo/userinfo/set', str)
            .success(function(data, status) {
                if(data.code==20000){
                    $scope.user_param.pwd = '';
                    $scope.user_param.newpass = '';
                    $scope.user_param.newpass2 = '';
                    $scope.userInfo.pwdWin = false;
                    $scope.userInfo.mobileWin = false;
                    alert(data.msg);
                    service.getMyUserInfo($scope);
                }else{
                    alert(data.msg);
                }
            });
    };

    //修改技能
    service.editeSkill=function($scope){
        $http.post('/index.php?r=userinfo/userinfo/skill-set', JSON.stringify($scope.user_param))
            .success(function(data, status) {
                if(data.code==20000){
                    alert(data.msg);
                }else{
                    alert(data.msg);
                }
            });
    };

    //上传头像
    service.uploadHead=function($scope){
        $http.post('/index.php?r=userinfo/userinfo/setheadimg', JSON.stringify($scope.user_param))
            .success(function(data, status) {
                if(data.code==20000){
                    alert(data.msg);
                }else{
                    alert(data.msg);
                }
            });
    };

    return service;
});

