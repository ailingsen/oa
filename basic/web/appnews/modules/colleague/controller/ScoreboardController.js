ColleagueMod.controller('ScoreboardCtr',function($scope,$state,ColleagueModel,permissionService){
    if (!permissionService.checkPermission('WorkmateScoreboard')) {
        $state.go('main.index', {},{'reload': false});
        return false;
    }
    var scoreboard = $scope.scoreBoard = [];
    scoreboard.richIntegralList = [];
    scoreboard.dataSelect = 1;
    scoreboard.searchTime = 'week';
    scoreboard.integral = [];
    scoreboard.dataSelectCtr = function(status,selectTime) {
        scoreboard.dataSelect = status;
        scoreboard.searchTime = selectTime;
        ColleagueModel.getIntegral($scope,scoreboard.searchTime);
    };
    ColleagueModel.getIntegral($scope,scoreboard.searchTime);
    ColleagueModel.getRichData($scope);
});