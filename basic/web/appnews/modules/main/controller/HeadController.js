
IndexMod.controller('HeadCtrl',function($scope,$http,deskModel,$rootScope,$cookieStore,$state){
    var userInfo = $scope.userInfo = {};
    $rootScope.rootUserInfo = $cookieStore.get('userInfo');

    // 移动申请遮罩
    userInfo.mLayer = false;
    if (location.href.indexOf('applymobile') > -1) {
        userInfo.mLayer = true;
    }

    //用户信息
    userInfo.userInfo = '';
    //技能信息
    userInfo.skillInfo = '';

    //顶部显示个人信息
    $(".infosbor").click(function(){
        $(".infoslist").stop().slideToggle(200);
        $(this).find(".infosbtn i").toggleClass("rotate");
        deskModel.getUserInfo($scope);
    });
    $(document).bind('click',function(e){
        var b1=$(e.target).parents('.infoslist').length==0&&!$(e.target).hasClass('infoslist');
        var b2=$(e.target).parents('.infobor').length==0&&!$(e.target).hasClass('infobor');
        if(b1&&b2){
            $(".infoslist").stop().slideUp(200);
        }
    })
    userInfo.hideUser=function(){
        $(".infoslist").stop().slideUp(200);
    }

    //退出登录
    userInfo.logout = function(){
        $cookieStore.remove('userInfo');
        //$cookieStore.remove('allper');
        //$cookieStore.remove('userper');
        $state.go('login',{},{reload:true});
        $http.post('/index.php?r=login/login/logout', {})
            .success(function(data, status) {
            });
    }

    //模块设置按钮的显示与隐藏
    $scope.isShowSet=true;
    $scope.$on('$stateChangeSuccess',function(event, toState, toParams, fromState, fromParams){
        if(toState.url=='/index'){
            $scope.isShowSet=true;
        }else{
            $scope.isShowSet=false;
        }
    })



});