//技能等级管理
systemMod.controller('skillLevelLevelCtrl', function ($scope,$stateParams, $rootScope,skillModel, $http, $cookieStore,$cookies,$state,noticeService,Publicfactory) {
    var skillLevel = $scope.skillLevel = {};
    var skillLevel_param = $scope.skillLevel_param = {};
    var skillLevel_param_add = $scope.skillLevel_param_add = {};
    skillLevel.noticeService = noticeService;
    //删除技能等级弹出框
    skillLevel.delskillLevelWin = false;
    //添加技能等级框
    skillLevel.addWin = false;
    //编辑技能等级框
    skillLevel.editeskillLevelWin = [];

    //技能等级列表
    skillLevel.skillLevel_list = [];

    skillLevel_param_add.level = skillLevel_param.level = '';
    //技能等级名称
    skillLevel_param_add.title = skillLevel_param.title = '';
    //技能等级id
    skillLevel_param_add.skill_level_id = skillLevel_param.skill_level_id = '';
    //积分
    skillLevel_param_add.point = skillLevel_param.point = '';


    skillModel.getSkillLevelList($scope);


    //添加技能等级
    skillLevel.addskillLevel = function(){
        if ($scope.skillLevel.skillLevel_list.length != undefined && $scope.skillLevel.skillLevel_list.length > 0) {
            $scope.skillLevel_param_add.level = $scope.skillLevel.skillLevel_list.length + 1;
        } else {
            $scope.skillLevel_param_add.level = 1;
        }
        $scope.skillLevel_param_add.title = '';
        $scope.skillLevel_param_add.point = '';
        $scope.skillLevel.addWin = true;
    }
    skillLevel.cancelAddskillLevel = function(){
        skillLevel_param = {};
        skillLevel.addskillLevelWin = false;
    }
    //保存技能等级
    skillLevel.saveAdd = function(){
        if ($scope.skillLevel_param_add.point.length<=0) {
            alert("技能分数不能为空");
            return;
        }
        if (!isInteger($scope.skillLevel_param_add.point)) {
            alert("积分只能为非负整数");
            return;
        }
        console.log($scope.skillLevel_param_add.point);
        if(typeof $scope.skillLevel_param_add.title == 'undefined' || $scope.skillLevel_param_add.title==''){
            alert('请填写头衔！');
            return;
        }
        if (Publicfactory.checkEnCnstrlen($scope.skillLevel_param_add.title > 16)) {
            alert('头衔不能超过8个字');
            return false;
        }

        skillModel.addSkillLevel($scope, $state);
        
    }

    //编辑技能等级
    skillLevel.editeSkillLevel = function(skillLevel, index){
        skillLevel_param.skill_level_id = skillLevel.skill_level_id;
        skillLevel_param.title = skillLevel.title;
        skillLevel_param.point = skillLevel.point;
        angular.forEach($scope.skillLevel.editeskillLevelWin, function(one, key){
            $scope.skillLevel.editeskillLevelWin[key] = false;
        })
        $scope.skillLevel.editeskillLevelWin[index] = true;
    }

    skillLevel.cancelEditeskillLevel = function(index){
        skillLevel_param = {};
        $scope.skillLevel.editeskillLevelWin[index] = false;
    }
    //判断是否为整数
    function isInteger(obj) {
        return Math.floor(obj) == obj
    }
    //提交编辑技能等级 
    skillLevel.saveEdite = function(skillLevel){
        if ($scope.skillLevel_param.point.length<=0) {
            alert("技能分数不能为空");
            return;
        }
        if (!isInteger($scope.skillLevel_param.point)) {
            alert("积分只能为非负整数");
            return;
        }
        if(typeof skillLevel_param.title == 'undefined' || skillLevel_param.title==''){
            alert('请填写头衔！');
            return;
        }
        if (Publicfactory.checkEnCnstrlen(skillLevel_param.title) > 16) {
            alert('头衔不能超过8个字');
            return false;
        }
        skillModel.editSkillLevel($scope, $state);
        
    }

	//删除技能等级
    skillLevel.isDelskillLevel = function(skillLevelId){
        skillLevel_param.skill_level_id = skillLevelId;
        $scope.skillLevel.delskillLevelWin = true;
        $('#masklayer1').show();
    }

    skillLevel.cancelDelskillLevel = function(){
        skillLevel_param.skillLevel_id = skillLevel_param.group_id = '';
        skillLevel.delskillLevelWin = false;
        $('#masklayer1').hide();
    }

    skillLevel.delskillLevel = function(){
        skillLevel.delskillLevelWin = false;
        skillModel.delSkillLevel($scope,$state);
        $('#masklayer1').hide();
    }
    
    skillLevel.hide = function(){
    	skillLevel.delskillLevelWin=false;
    	$('#masklayer1').hide();
    }

});
