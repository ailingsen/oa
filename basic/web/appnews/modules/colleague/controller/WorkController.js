var ColleagueMod=angular.module('ColleagueMod',[]);
ColleagueMod.controller('WorkCtr',function($scope,$rootScope,$state,ColleagueModel){
    var work = $scope.Work={};
    $scope.allMembers = [];
    $scope.WorkingConditions = [];
    work.searchName = '';
    work.currentPage = '';
    work.searchMember = '姓名';
    work.memberShow = false;
    ColleagueModel.getAllComMeb($scope,'');
    ColleagueModel.getWorkingConditions($scope,work.searchName);
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    //根据姓名查询
    work.searchInputMember = function () {
        // $scope.page={
        //     curPage : 1,//当前页
        //     tempcurPage : 1,//临时当前页
        //     sumPage : 0//总页数
        // };
        work.searchName = $scope.Work.searchName;
        work.memberShow = true;
        ColleagueModel.getAllComMeb($scope,work.searchName);
        // ColleagueModel.getWorkingConditions($scope,work.searchName);
        
    };
    //点击查询
    work.clickInputMember = function () {
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };
        ColleagueModel.getWorkingConditions($scope,work.searchName);
    };
    //根据选择的姓名查询
    work.getSelectName = function(name) {
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : 0//总页数
        };
        work.searchName = name;
        work.memberShow = false;
    };
    work.dropDownStatusCtr = function () {
        work.memberShow = !work.memberShow;
    };

    $(document).bind("click",function(event){
        if(angular.element(event.target).parents(".mems ").length==0){
            work.memberShow = false;
            // $scope.$apply();
        }
    });
    //分页
    $scope.workPaging = function(){
        work.currentPage = $scope.page.tempcurPage;
        ColleagueModel.getWorkingConditions($scope,work.searchName,'',work.currentPage);
    };
});
