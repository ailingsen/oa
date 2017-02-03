var ApplymobileMod=angular.module('ApplymobileMod',['commonMod']);

ApplymobileMod.controller('ApplymobileAppController',function($location,$scope,$http,$state,$rootScope,Publicfactory,applyModel,permissionService){
    //if (!permissionService.checkPermission('ApplyApply')) {
    //    $state.go('main.index', {},{'reload': false});
    //    return false;
    //}
    var applyApp = $scope.applyApp = {};
    applyModel.getUsefulModel($scope);
    $(".mainsbar").css({"overflow-y":"auto"});
   
});