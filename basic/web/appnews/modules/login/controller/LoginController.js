
var LoginMod=angular.module('LoginMod',[])

LoginMod.controller('LoginCtrl',function($scope,$http,$rootScope,Publicfactory,$cookies,$cookieStore,$state){
    var loginForm = $scope.loginForm = {};
    //登录用户名
    loginForm.userName = '';
    //登录密码
    loginForm.passWord = '';
    //修改密码
    loginForm.newPass = '';
    loginForm.againnewPass = '';
    //忘记密码
    loginForm.findPassUsername= '';
    //是否显示修改密码窗口
    loginForm.isUpdatePwdWin = false;
    //是否显示找回密码窗口
    loginForm.isForgetPwdWin = false;
    //是否显示邮件发送成功框
    loginForm.isForgetPwdSuccessWin = false;
    //判断按钮是否可以点击
    loginForm.isDisabled = false;


    loginForm.checkemail = function(email){
        var reg =  /^(\w-*\.*)+@(\w-?)+(\.\w{2,3})+$/;
        if(!reg.test(email)){
            loginForm.isDisabled = false;
            alert("格式错误，请输入正确的email！");
            return false;
        }else{
            return true;
        }
    }

    //回车登录
    /*$(document).keypress(function(e)
    {
        switch(e.which)
        {
            case 13:
                loginForm.submitButton();
                break;
        }
    });*/
    $scope.myKeyup = function(e){
        var keycode = window.event?e.keyCode:e.which;
        if(keycode==13){
            loginForm.submitButton();
        }
    };

    //用户名密码登陆提交
    loginForm.submitButton = function() {
        if(loginForm.isDisabled){
            return false;
        }
        loginForm.isDisabled = true;

        if (loginForm.userName.length == 0) {
            loginForm.isDisabled = false;
            alert("请输入email");
            return false;
        }
        if(loginForm.checkemail(loginForm.userName)){
            if(loginForm.passWord.length==0){
                loginForm.isDisabled = false;
                alert("请输入密码");
                return false;
            }
            if(loginForm.passWord.length<6){
                loginForm.isDisabled = false;
                alert("密码长度不能小于6位！");
                return false;
            }
            if( loginForm.passWord.length>16 ){
                loginForm.isDisabled = false;
                alert("密码长度不能大于16位!");
                return false;
            }
            $http.post('/index.php?r=login/login/login', {username:loginForm.userName,pwd:loginForm.passWord})
                .success(function(data, status) {
                    if(data.code==2){//第一次登陆修改密码
                        alert(data.msg);
                        loginForm.isUpdatePwdWin = true;
                    }else if(data.code==1){//登录成功
                        $cookieStore.put('userInfo',data.member);
                        //$cookieStore.put('allper',data.allper);
                        //$cookieStore.put('userper',data.userper);
                        window.localStorage.allper = JSON.stringify(data.allper);
                        window.localStorage.userper = JSON.stringify(data.userper);
                        $rootScope.rootUserInfo = data.member;
                        $state.go('main.index');
                        loginForm.userName = '';
                        loginForm.passWord = '';
                    }else if(data.code==-1){//登录失败
                        alert(data.msg);
                        $state.go("login",{});
                    }

                    loginForm.isDisabled = false;
                }).error(function(error){
                    loginForm.isDisabled = false;
                });
        }
    }

    //修改密码
    loginForm.updatePassword = function(){
        if(loginForm.isDisabled){
            return false;
        }
        loginForm.isDisabled = true;

        if($.trim(loginForm.newPass).length==0){
            loginForm.isDisabled = false;
            alert("请先输入新密码");
            return false;
        }
        if($.trim(loginForm.newPass).length<6){
            loginForm.isDisabled = false;
            alert("新密码长度不能小于6位！");
            return false;
        }
        if($.trim(loginForm.newPass).length>16){
            loginForm.isDisabled = false;
            alert("新密码长度不能大于16位!");
            return false;
        }
        if( $.trim(loginForm.newPass) != $.trim(loginForm.againnewPass) ){
            loginForm.isDisabled = false;
            alert("两次密码不一致!");
            return false;
        }
        $http.post('/index.php?r=login/login/modify-pwd', {pwd1:loginForm.newPass,pwd2:loginForm.againnewPass})
            .success(function(data, status) {
                if(data.code == 1 || data.code == -2){
                    alert(data.msg);
                    if(data.code == 1){//修改密码成功后登陆跳转
                        loginForm.isDisabled = false;
                        loginForm.passWord = loginForm.newPass;
                        loginForm.submitButton();
                    }else{
                        $state.go("login",{},{reload:true});
                    }
                }else{
                    alert(data.msg);
                }

                loginForm.isDisabled = false;
            }).error(function(error){
                loginForm.isDisabled = false;
            });
    };

    //找回密码
    loginForm.findPassword = function(){
        if(loginForm.isDisabled){
            return false;
        }
        loginForm.isDisabled = true;

        loginForm.findPassUsername = loginForm.findPassUsername.replace(/\s/g,"");
        if( loginForm.findPassUsername == "" ){
            loginForm.isDisabled = false;
            alert("请先输入email!");
        }else{
            if(loginForm.checkemail(loginForm.findPassUsername)){
                $http.post('/index.php?r=login/login/forgetpassword', {email:loginForm.findPassUsername})
                    .success(function(data, status) {
                        if(data.code == 1){
                            //是否显示修改密码窗口
                            loginForm.isUpdatePwdWin = false;
                            //是否显示找回密码窗口
                            loginForm.isForgetPwdWin = false;
                            if(loginForm.isForgetPwdSuccessWin){
                                alert(data.msg);
                            }
                            //是否显示邮件发送成功框
                            loginForm.isForgetPwdSuccessWin = true;
                        }else{
                            alert(data.msg);
                        }


                        loginForm.isDisabled = false;
                    }).error(function(error){
                        loginForm.isDisabled = false;
                    });
            }
        }
    };

    //返回登录
    loginForm.retLogin = function(){
        if(loginForm.isDisabled){
            return false;
        }

        //是否显示修改密码窗口
        loginForm.isUpdatePwdWin = false;
        //是否显示找回密码窗口
        loginForm.isForgetPwdWin = false;
        //是否显示邮件发送成功框
        loginForm.isForgetPwdSuccessWin = false;
        //修改密码
        loginForm.newPass = '';
        loginForm.againnewPass = '';
        //忘记密码
        loginForm.findPassUsername= '';
    };

    //显示忘记密码窗口
    loginForm.forgetWin = function(){
        if(loginForm.isDisabled){
            return false;
        }

        //是否显示修改密码窗口
        loginForm.isUpdatePwdWin = false;
        //是否显示找回密码窗口
        loginForm.isForgetPwdWin = true;
        //是否显示邮件发送成功框
        loginForm.isForgetPwdSuccessWin = false;
    };


    //帮助文档TAB切换
    $('.help-tab p').click(function(){
        $('.help-content>div').hide();
        $('#'+this.dataset.target).show();
        $('.help-tab p a').removeClass('curTab');
        $(this).find('a').addClass('curTab');
    })
});




