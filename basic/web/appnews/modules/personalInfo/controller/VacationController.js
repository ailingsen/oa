//技能与积分
personalInfoMod.controller('vacationCtrl',function($scope,$http,$cookieStore,$window,$rootScope,userModel,$timeout){
    var userInfo = $scope.userInfo = {};
    var vacation = $scope.vacation = {};
    userInfo.detail = $cookieStore.get('userInfo');
    
    userModel.getMyVacation($scope);

    //返回
    userInfo.back = function(){
        $window.history.back();
    }
});



