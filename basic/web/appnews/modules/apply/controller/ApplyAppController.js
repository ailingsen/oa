/**
 * Created by nielixin on 2016/9/1.
 */
ApplyMod.controller('ApplyAppController',function($scope,$http,$state,$rootScope,Publicfactory,applyModel,permissionService){
    if (!permissionService.checkPermission('ApplyApply')) {
        $state.go('main.index', {},{'reload': false});
        return false;
    }
    var applyApp = $scope.applyApp = {};
    applyModel.getUsefulModel($scope);
});