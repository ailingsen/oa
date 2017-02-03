LoginMod.controller('ForgetPasswordCtrl',function($scope,$http,$rootScope,Publicfactory,$cookies,$cookieStore,$state,$stateParams){
    //获取token和uid
    var uid = $stateParams.uid;
    var password_token = $stateParams.password_token;
    if(uid=='' || password_token==''){
        $state.go("login",{},{reload:true});
    }
    var loginForm = $scope.loginForm = {};
    //修改密码
    loginForm.newPass = '';
    loginForm.againnewPass = '';



    //修改密码
    loginForm.updatePassword = function(){
        if($.trim(loginForm.newPass).length==0){
            alert("请先输入新密码");
            return false;
        }
        if($.trim(loginForm.newPass).length<6){
            alert("新密码长度不能小于6位！");
            return false;
        }
        if($.trim(loginForm.newPass).length>16){
            alert("新密码长度不能大于16位!");
            return false;
        }
        if( $.trim(loginForm.newPass) != $.trim(loginForm.againnewPass) ){
            alert("两次密码不一致!");
            return false;
        }
        $http.post('/index.php?r=login/login/resetpassword', {pwd1:loginForm.newPass,pwd2:loginForm.againnewPass,password_token:password_token,uid:uid})
            .success(function(data, status) {
                if(data.code == 1){
                    alert(data.msg);
                    $state.go("login",{},{reload:true});
                }else{
                    alert(data.msg);
                }
            });
    };

});




