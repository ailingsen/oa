//技能管理
systemMod.controller('skillCtrl', function ($scope,$stateParams, $rootScope,skillModel, $http, $cookieStore,$cookies,$state,noticeService,Publicfactory) {
    var skill = $scope.skill = {};
    var skill_param = $scope.skill_param = {};
    skill.noticeService = noticeService;
    //添加技能窗口
    skill.addSkillWin = false;
    //删除技能弹出框
    skill.delSkillWin = false;
    //编辑技能弹出框
    skill.editeSkillWin = false;

    //技能列表
    skill.skill_list = [];

    //技能名称
    skill_param.skill_name = '';
    //技能id
    skill_param.skill_id = '';
    //父技能id
    skill_param.group_id = 0;


    //usersetModel.getAllSkillList($scope,$rootScope);
    skillModel.getSkillList($scope);


    //添加技能
    skill.addSkill = function(parentId){
        skill_param.group_id = parentId;
        skill_param.skill_name = '';
        skill.addSkillWin = true;
        $('#masklayer1').show();
    }
    skill.cancelAddSkill = function(){
        skill_param.skill_id = skill_param.group_id = '';
        $scope.skill_param.skill_name = '';
        skill.addSkillWin = false;
         $('#masklayer1').hide();
    }
    //保存技能
    skill.saveAdd = function(){
        if(typeof $scope.skill_param.skill_name == 'undefined' || $scope.skill_param.skill_name==''){
            if (0 == skill_param.group_id) {
                alert('请填写类型名称！');
            } else {
                alert('请填写技能名称！');
            }
            return;
        }
        if (Publicfactory.checkEnCnstrlen($scope.skill_param.skill_name) > 40) {
            if (0 == skill_param.group_id) {
                alert('类型名称不能超过20个字！');
            } else {
                alert('技能名称不能超过20个字！');
            }
            return false;
        }

        skillModel.addSkill($scope, $state);
        skill.addSkillWin = false;
        $('#masklayer1').hide();
        
    }

    //编辑技能
    skill.editeSkill = function(skillName,skillId,groupId){
        skill_param.skill_id = skillId;
        skill_param.group_id = groupId;
        skill_param.skill_name = skillName;
        skill.editeSkillWin = true;
        $('#masklayer1').show();
    }

    skill.cancelEditeSkill = function(){
        skill.editeSkillWin = false;
        //技能id
        skill_param.skill_id = '';
        //父技能id
        skill_param.group_id = 0;
        $('#masklayer1').hide();
    }
    //提交编辑技能 
    skill.saveEdite = function(skill){
        if(typeof skill_param.skill_name == 'undefined' || skill_param.skill_name==''){
            if (0 == skill_param.group_id) {
                alert('请填写类型名称！');
            } else {
                alert('请填写技能名称！');
            }
            return;
        }
        if (Publicfactory.checkEnCnstrlen(skill_param.skill_name) > 40) {
            if (0 == skill_param.group_id) {
                alert('类型名称不能超过20个字！');
            } else {
                alert('技能名称不能超过20个字！');
            }
            return false;
        }
        skillModel.editSkill($scope, $state);
        $('#masklayer1').hide();
        $scope.skill.editeSkillWin = false;       	
    }

	//删除技能
    skill.isDelSkill = function(skillId, groupId){
        skill_param.skill_id = skillId;
        skill_param.group_id = groupId;
        skill.delSkillWin = true;
        $('#masklayer1').show();
    }

    skill.cancelDelSkill = function(){
        skill_param.skill_id = skill_param.group_id = '';
        skill.delSkillWin = false;
        $('#masklayer1').hide();
    }

    skill.delSkill = function(){
        skill.delSkillWin = false;
        skillModel.delSkill($scope,$state);
        $('#masklayer1').hide();
    }

});
