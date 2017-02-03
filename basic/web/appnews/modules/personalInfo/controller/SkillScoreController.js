//技能与积分
personalInfoMod.controller('skillScoreCtrl',function($scope,$http,$cookieStore,$window,$rootScope,userModel,$timeout){
    var userInfo = $scope.userInfo = {};
    // userInfo.detail = $cookieStore.get('userInfo');
    userInfo.detail = {};
    userInfo.skills = '';
    userInfo.skill_rulls = '';

    userModel.getSkillScore($scope);
    $scope.filename = function() {
        return angular.element("#usershowimg");
    }

    //返回
    userInfo.back = function(){
        $window.history.back();
    }
});



