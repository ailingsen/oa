TaskMod.factory('skillModel', function($http,$timeout,$cookieStore,$cookies,$state,filtersModel){
    var  service={};
    //获取所有项目, 并初始化数据
    service.getSkillList = function ($scope) {
        $http.get('/index.php?r=management/skill/skilllist').success(function(data, status) {
            $scope.skill.skill_list = data.data;         
        });
    };

    //添加技能
    service.addSkill = function ($scope, $state){
        $http.post('/index.php?r=management/skill/addskill', JSON.stringify($scope.skill_param))
            .success(function(data,status) {
                if(data.code==20000){
                    service.getSkillList($scope);
                }else{
                    alert(data.msg)
                }
            })
    }

    //编辑技能
    service.editSkill = function ($scope, $state){
        $http.post('/index.php?r=management/skill/editskill', JSON.stringify($scope.skill_param))
            .success(function(data,status) {
                if(data.code==20000){
                    service.getSkillList($scope);
                }else{
                    alert(data.msg);
                }
            })
    }

    //删除技能
    service.delSkill = function ($scope, $state){
        $http.post('/index.php?r=management/skill/delskill', JSON.stringify($scope.skill_param))
            .success(function(data,status) {
                if(20000 == data.code){
                    service.getSkillList($scope);
                }else{
                    alert(data.msg);
                }
            })
    }

    //获取所有技能等级, 并初始化数据
    service.getSkillLevelList = function ($scope) {
        $http.get('/index.php?r=management/skill/skill-level').success(function(data, status) {
            $scope.skillLevel.skillLevel_list = data.data;
            angular.forEach(data.data, function(item, index){
                $scope.skillLevel.editeskillLevelWin[index] = false; 
            });
        });
    };

    //添加技能等级
    service.addSkillLevel = function ($scope, $state){
        $http.post('/index.php?r=management/skill/add-skilllevel', JSON.stringify($scope.skillLevel_param_add))
            .success(function(data,status) {
                if(data.code=="20000"){
                    service.getSkillLevelList($scope);
                    // $scope.skillLevel_param_add.title = '';
                    // $scope.skillLevel_param_add.point = '';
                    $scope.skillLevel.addWin = false;
                    // $state.go('main.system.gradeSettingList',{},{reload:true});
                }else{
                    alert(data.msg)
                }
            })
    }

    //编辑技能等级
    service.editSkillLevel = function ($scope, $state){
        $http.post('/index.php?r=management/skill/edite-skilllevel', JSON.stringify($scope.skillLevel_param))
            .success(function(data,status) {
                if(data.code==20000){
                    service.getSkillLevelList($scope);
                    // $state.go('main.system.gradeSettingList',{},{reload:true});
                }else{
                    alert(data.msg);
                }
            })
    }

    //删除技能等级
    service.delSkillLevel = function ($scope, $state){
        $http.post('/index.php?r=management/skill/del-skilllevel', JSON.stringify($scope.skillLevel_param))
            .success(function(data,status) {
                if(20000 == data.code){
                    service.getSkillLevelList($scope);
                    // $state.go('main.system.gradeSettingList',{},{reload:true});
                }else{
                    alert(data.msg);
                }
            })
    }


    return service;
});
