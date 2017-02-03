   var personalInfoMod=angular.module('personalInfoMod',[]);
//个人信息
personalInfoMod.controller('personalInfoCtrl',function($scope,$state,$http,$rootScope,userModel,$window,$cookieStore){
    var userInfo = $scope.userInfo = {};
    var user_param = $scope.user_param = {};
    var param = $scope.param = {};
    userInfo.detail = {};
    userInfo.skills = '';

    user_param.phone = '';
    param.pwd = user_param.pwd = '';
    param.newpass = user_param.newpass = '';
    param.newpass2 = user_param.newpass2 = '';
    user_param.option = '';
    user_param.val = '';

    userInfo.pwdWin = false;
    userInfo.mobileWin = false;

    userModel.getMyUserInfo($scope);
    $scope.filename = function() {
        return angular.element("#usershowimg");
    }
    clipPic("showimgcho", "usershowimg", function(data) {
        $http.post('/index.php?r=userinfo/userinfo/setheadimg', {
                data: data
            }).success(function(data, status) {
                angular.element('#usershowimg').attr('src', data.filename + '?' + Date.parse(new Date()));
                angular.element('.user_img img').attr('src', data.filename + '?' + Date.parse(new Date()));
                //修改cookie
                var userInfoCookie = $cookieStore.get('userInfo');
                userInfoCookie.head_img = data.data + '?' + Math.random();
                $cookieStore.put('userInfo', userInfoCookie);
                userInfo.detail.head_img = $rootScope.rootUserInfo.head_img = data.data + '?' + Math.random();
                location.reload();
            })
    }, 50);

    userInfo.isChecked = function(value){
        if (0 == value) {
            return false;
        }
        return true;
    }
    
    //修改手机号码
    userInfo.editeMobile = function(){
        user_param.option = 'phone';
        user_param.val = user_param.phone;
        var reg = /^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/i;
        if (!reg.test(user_param.phone)) {
            alert("手机号码格式不正确");
            return;
        }
        userModel.editeUserInfo($scope, 0);
    }

    //修改密码
    userInfo.editePwd = function(){
        param.option = 'pwd';

        if(typeof user_param.pwd == 'undefind' || user_param.pwd == null || user_param.pwd == '') {
            alert('原密码不能为空');
            return;
        }
        if(typeof user_param.newpass == 'undefind' || user_param.newpass == null || user_param.newpass == '') {
            alert('新密码不能为空');
            return;
        }
        if(typeof user_param.newpass2 == 'undefind' || user_param.newpass2 == null || user_param.newpass2 == '') {
            alert('重复新密码不能为空');
            return;
        }
        var pwd_reg =  /^[A-Za-z0-9]{6,16}$/;
        if(!pwd_reg.test(user_param.newpass) || !pwd_reg.test(user_param.newpass2)) {
            alert('密码应为6至16位的字母数字或组合');
            return;
        }
        if (user_param.newpass != user_param.newpass2) {
            alert('两次密码不一致');
            return;
        }
        if (user_param.newpass.length < 6) {
            alert('密码至少6位');
            return;
        }
        if(user_param.pwd === user_param.newpass) {
            alert('新密码不能与旧密码一致');
            return;
        }

        param.val = angular.element.md5(user_param.pwd);
        param.newpass = angular.element.md5(user_param.newpass);
        param.newpass2 = angular.element.md5(user_param.newpass2);
        userModel.editeUserInfo($scope, 1);
    }

    userInfo.cancelEdite = function(){
        user_param.pwd = '';
        user_param.newpass = '';
        user_param.newpass2 = '';
        userInfo.pwdWin = false;
    }

    //修改推送
    userInfo.editePush = function(option, val){
        user_param.option = option;
        user_param.val = val == 0 ? 1 : 0;
        userModel.editeUserInfo($scope, 2);
    }
    //返回
    userInfo.back = function(){
        $window.history.back();
    }
    //退出登录
    $scope.logout = function(){
        $cookieStore.remove('userInfo');
        //$cookieStore.remove('allper');
        //$cookieStore.remove('userper');
        $state.go("login",{},{reload:true});
    };
});



