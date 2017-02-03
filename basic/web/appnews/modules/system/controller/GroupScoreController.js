
systemMod.controller('groupScoreCtrl',function($scope,$http,$state,scoreModel,noticeService,Publicfactory){
    var score = $scope.score = {};
    var score_param = $scope.score_param = {};
    score.noticeService = noticeService;
    //翻页对象
    $scope.page={
        curPage : 1,//当前页
        tempcurPage : 1,//临时当前页
        sumPage : 0//总页数
    };
    //查询部门
    score.search_org_name = '';
    //查询部门临时数据
    score.search_org_name_temp = '';

    //部门下拉列表显示
    score.isOrgWin = false;
    //查看详情
    score.isLogWin = false;

    //是否显示调整框
    score.isAdjustWin = false;

    //是否显示错误信息
    score.isErrorWin = false;
    score.error_info = {};

    //调整的部门ID
    score_param.org_ids = [];


    //部门下拉列表数据
    score.orgInfo = [];
    score.allOrgInfo = [];

    //参数
    score_param.org_name = '';
    score_param.points = 0;
    score_param.reason = '';
    //部门
    score_param.org_id = '';

    //当前页
    score_param.page = score.page = 1;
    $scope.score.scoreList = [];


    //获取列表
    scoreModel.getGroupScoreList($scope);

    //查询按钮
    score.searchButton = function () {
        score_param.org_name = score.search_org_name;
        score_param.page = 1;
        $scope.page={
            curPage : 1,//当前页
            tempcurPage : 1,//临时当前页
            sumPage : score.total_page//总页数
        };
        scoreModel.getGroupScoreList($scope);
        angular.element('.personalNano_table').find(':checkbox').prop("checked", false);
    };

    //获取所有搜索部门
    score.getSearchAllOrgInfo = function(){
        score.isMemWin = false;
        score.search_org_name = '';
        score.search_org_id = '';
        // if(score.allOrg.length > 0){
        //     score.isOrgWin = true;
        //     $scope.score.orgInfo = score.allOrg;
        //     return;
        // }   

        scoreModel.getOrgInfo($scope);
        score.allOrg = $scope.score.orgInfo;
    }

    var timer='';
    //获取搜索部门数据
    score.searchOrgInfo = function () {
        clearTimeout(timer);
        timer=setTimeout(function() {
            scoreModel.getOrgInfo($scope);
        },500);
        score.isOrgWin = true;
        score.search_org_name_temp = '';
    }

    //保存选中的查询部门ID
    score.selectOrg = function (obj) {
        score_param.org_id = score.search_org_id = obj.value;
        score.search_org_name = obj.label;
        $scope.score.isOrgWin = false;
    }

    //查询所有部门
    score.getAllOrgInfo = function () {
        score.isOrgWin = true;
        score.search_org_name = '';
        console.log(score.allOrgInfo.length);
        if(score.allOrgInfo.length > 0){
            $scope.score.orgInfo = score.allOrgInfo;
            return;
        }

        scoreModel.getOrgInfo($scope);
        $scope.score.allOrgInfo = score.orgInfo;
    }

    //选择框-选择全部
    score.selectAll=function($event){
        var checkbox = $event.target;
        if(checkbox.checked){
            angular.element('.system_list_status').find(':checkbox').prop("checked", true);
        }else{
            angular.element('.system_list_status').find(':checkbox').prop("checked", false);
        }
    };

    score.getSelected = function(){
        score_param.org_ids = [];
        angular.element('.system_list_status').find(':checkbox').each(function(){
            if($(this).prop("checked")){
                score_param.org_ids.push($(this).attr('org_id'));
            }
        });
    };

    //+积分
    score.increasePoint = function () {
        if ($scope.score_param.points >= 999999) {
            alert("不能再加了");
            return;
        }
        $scope.score_param.points ++;
    }

    //-积分
    score.decreasePoint = function () {
        if ($scope.score_param.points <= -1000) {
            alert("不能再减了");
            return;
        }
        $scope.score_param.points --;
    }
	 //判断是否为整数
    function isInteger(obj) {
        return Math.floor(obj) == obj;
    }
    //确认调整
    score.adjust = function () {
//      if (isNaN($scope.score_param.points)) {
//          alert("积分只能为数字");
//          return;
//      }
        if (!isInteger($scope.score_param.points)) {
            alert("积分只能为整数");
            return;
        }
        if ($scope.score_param.points > 999999) {
            alert("积分不能超过999999");
            return;
        }
//      if (Publicfactory.checkEnCnstrlen($scope.score_param.reason) > 50) {
//          alert('原因不能超过25个字');
//          return false;
//      }
        scoreModel.addGroupScore($scope, $state);
        
    }

    //单个调整
    score.addSingle = function (orgId) {
        score_param.org_ids = [];
        score_param.org_ids.push(orgId);
        score_param.points = 0;
        score_param.reason = '';
        score.isAdjustWin = true;
        $("#masklayer1").show();
    }
	score.hide=function(){
		score.isAdjustWin=false;
		$("#masklayer1").hide();
	}
    //批量调整
    score.addBatch = function () {
        score_param.org_ids = [];
        score.getSelected();
        if (score_param.org_ids.length < 1) {
            alert('请选择调整项');
            return ;
        }
        score_param.points = 0;
        score_param.reason = '';
        score.isAdjustWin = true;
        $("#masklayer1").show();
    }

    //查看详情
    score.viewDetail = function (uid) {
        score_param.u_id = uid;
        scoreModel.viewLog($scope);
    };

    //关闭错误提示
    score.closeError = function () {
        score.isErrorWin = false;
        score.error_info = {};
        $("#masklayer1").hide();
    };

    //翻页方法
    $scope.page_fun = function () {
        angular.element('.personalNano_table').find(':checkbox').prop("checked", false);
        $scope.score_param.page = $scope.page.tempcurPage;
        scoreModel.getGroupScoreList($scope);
    };
});