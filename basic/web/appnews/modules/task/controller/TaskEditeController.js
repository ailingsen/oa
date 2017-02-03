TaskMod.controller('taskEditeCtrl', function ($scope, $http, $state, $cookieStore,$cookies, $stateParams, $rootScope, $document,taskModel,Publicfactory) {
    var system = $scope.system = {};
    system.random = new Date().getTime();
    // $rootScope.user =eval('('+$cookieStore.get('userInfo')+')');
    $rootScope.user = $cookieStore.get('userInfo');
    //$rootScope.user.leave_points = parseInt($rootScope.user.leave_points);
    //蒙层显示
    $('#masklayer1').show();

    // $('.taskeditwin').show();
    if(!$stateParams.task_id || !$stateParams.task_type){
        // alert('参数错误');
        $state.go('^');
        return false;
    }

    //获取任务
    var task = $scope.task = {};
    $scope.leavePoints = 0;
    task.point = 0;
    $scope.isEdit = false;
    $scope.isEditPopUp = true;
    //临时存储任务类型
    task.temp_task_type=1;
    //编辑时临时保存任务现有的积分
    $scope.tempPoint = 0;
    var taskFunc = $scope.taskFunc = {};
    taskModel.getTaskDetail($scope,$stateParams.task_id,$stateParams.task_type,$state);
    $http.get('/index.php?r=task/task/get-leave-points').success(function (data) {
        $scope.leavePoints = parseInt(data.data);
    });
    //项目
    // taskModel.getProject($scope, 1);
    taskFunc.setTaskTimeRange=function(project){
         // $scope.task.taskProjectSelect=project;
         // $scope.task.taskStarttimeRange=project.begin_time;
         // $scope.task.taskEndtimeRange=project.end_time;
    }

    //1表示指派任务，2表示悬赏任务
    $scope.task.taskType = [
        {label: '指派任务', nums:1},
        {label: '悬赏任务', nums:2}
    ];

    //任务级别
      $scope.task.taskLevel = [
        {label: '低', nums:3},
        {label: '中', nums:2},
        {label: '高', nums:1}
    ];

    taskFunc.back = function(taskId){
        $state.go('main.task.myReleaseTask', {'task':taskId}, {'reload':true});
        // $('#masklayer1').hide();
        // $('.taskeditwin').hide();
        // $state.go("^");
    }
    var taskpoint = $(".taskpoint").val().replace(/(^\s*)/g, "");
    var taskpointReg = /^[0-9]*$/;
    taskFunc.pointkeyup = function(){
        if (!taskpointReg.test($(".taskpoint").val())) {
            alert("积分只能是大于0的正整数，最大值为自己的总积分!")
            $(".taskpoint").val('');
            return false;
        }
    };

    taskFunc.isSkillSelected=function(id) {
        if ($scope.task.selecteSkill != null) {
            angular.forEach($scope.task.selecteSkill,function(value, key){
                if(value.skill_id == id){
                    return true;
                }
            });
        } else {
            return false;
        }
    }
    taskFunc.isGroupSelected=function(id) {
        if (  $scope.task.selectedGroup != null) {
            angular.forEach(  $scope.task.selectedGroup,function(value, key){
                if(value.org_id == id){
                    return true;
                }
            });
        }
        else
            return false;
    }

    //指派任务成员
    //打开选人
    taskModel.getMembers($scope, '');
    taskFunc.openSelectedP=function(){
        $(".menbersearchbor").show();
        if($scope.task.groupMembers.length == 0)
           taskModel.getMembers($scope, '');
    }

    taskFunc.closeSelectedP=function() {
        $(".menbersearchbor").hide();
         $scope.task.searchMemberRealName='';
    }

    var timer='';
    taskFunc.allott=function(issearch){
        clearTimeout(timer);

        //获取所有的组织架构和人员
        timer=setTimeout(function(){
            var search='';
            if(issearch==1){
                search= $scope.task.searchMemberRealName;
            }
            if(issearch!=1){
                 $scope.task.searchMemberRealName='';
            }
            taskModel.getMembers($scope,  $scope.task.searchMemberRealName);
        },500);
    };
    taskFunc.selectMember=function($selecedMember){
         $scope.task.selectedMember = $selecedMember;
        taskFunc.closeSelectedP();
    }
    taskFunc.delmember=function(){
         $scope.task.selectedMember = {};
    }

    //添加附件
    $scope.addFileBtn = function(uploader){
        console.log($scope.task.temp_task_type);
        uploader.url = '/index.php?r=task/task/upload&type=1&taskType='+ $scope.task.temp_task_type+'&taskId='+ $scope.task.task_id;
        //uploader.url = '/index.php?r=task/task/upload&type=1&taskType=1&taskId='+ $scope.task.task_id;
        uploader.onCompleteItem = function (fileItem, response, status, headers) {
            if(response.code!=20000 && response.msg){
                alert(response.msg)
                return false;
            }
            if(response.data !=undefined ){
                var temporary_att={};
                temporary_att.task_att_id=response.data.task_att_id;
                temporary_att.name=response.data.name;
                temporary_att.real_name=response.data.real_name;
                temporary_att.file_type=response.data.file_type;
                temporary_att.file_size=response.data.file_size;
                $scope.task.files.push(response.data);
            }
        };
    };

    //删除上传的附件
    taskFunc.delFiles = function(index, attId){

        $http.get('/index.php?r=task/task/del-att&attId='+attId).success(function(data) {
            if(data.code == 20000) {
                if($scope.task.files){
                    $scope.task.files.splice(index,1);
                }
            } else {
                alert(data.msg)
            }
        });
    }
    //选择技能
    taskFunc.openSelecteSkill=function(){
        $(".skillbor").show();
        $(".taskeditwin .scrollbor").scrollTop(700);
        if($scope.task.skillList.length == 0) {
            taskModel.getAllSkillList($scope);
            $(".taskeditwin .scrollbor").scrollTop(700);
        }
    }

    taskFunc.closeSelecteSkill=function(){
        $(".skillbor").hide();
    }
    taskFunc.selectSkill=function($skill){
        $scope.task.selecteSkill.push($skill);
    }
    taskFunc.delSkill=function(index){
        $scope.task.selecteSkill.splice(index,1);
    }
    taskFunc.showList=function(index,isShow){
        $scope.task.skillList[index].show=isShow;
    }

    //技能选择框
    var updateSelected = function (action, id) {
        if(action == 'add'){
            var temp=-1;
            angular.element.each($scope.task.selecteSkill, function (key, val) {
                if(val['skill_id']==id['skill_id']){
                    temp=1;
                }
            });
            if(temp==-1){
                $scope.task.selecteSkill.push(id);
            }
        }else{
            for(var i=0; i<$scope.task.selecteSkill.length; i++){
                if($scope.task.selecteSkill[i]['skill_id']==id['skill_id']){
                    $scope.task.selecteSkill.splice(i, 1);
                    break;
                }
            }
        }
        //if (action == 'add' &&  $scope.task.selecteSkill.indexOf(id) == -1)   $scope.task.selecteSkill.push(id);
        //if (action == 'remove' &&   $scope.task.selecteSkill.indexOf(id) != -1)   $scope.task.selecteSkill.splice(  $scope.task.selecteSkill.indexOf(id), 1);
    }

    $scope.taskFunc.isSkillModified = false;
    taskFunc.updateSelection = function ($event, id) {
        var checkbox = $event.target;
        var action = (checkbox.checked ? 'add' : 'remove');
        $scope.taskFunc.isSkillModified = true;
        updateSelected(action, id);
    };

    taskFunc.isSelected = function (id) {
        return $scope.task.selecteSkill.indexOf(id) >= 0;
    };

    if($scope.task.task_type == 2){
        //悬赏范围
          $scope.task.allGroupsTree = [];
    }
    //打开悬赏范围
    taskFunc.openSelecteGroup=function(){
        $(".offerbor").show();
        if( $scope.task.allGroupsTree.length == 0)
            taskModel.getAllGroupTree($scope);
    }

    taskFunc.closeSelecteGroup=function() {
        $(".offerbor").hide();
    }
    taskFunc.getAllGroup=function(){
        //显示悬赏范围
        $scope.task.offerRange = !$scope.task.offerRange;
        //获取组织数据
        taskModel.getAllGroupTree($scope);
    }
    //悬赏的部门选择全公司
    taskFunc.selectAll=function($event){
        var checkbox = $event.target;
        if(checkbox.checked){
            angular.element(checkbox).parent().parent().parent('.nbors').nextAll('div').find(':checkbox').prop("checked", true);
        }else{
            angular.element(checkbox).parent().parent().parent('.nbors').nextAll('div').find(':checkbox').prop("checked", false);
        }
    };
    //悬赏的部门选择全公司
    task.selectAll=function($event){
        var checkbox = $event.target;
        if(checkbox.checked){
            angular.element(checkbox).parent().parent().parent('.nbors').nextAll('div').find(':checkbox').prop("checked", false);
        }else{
            angular.element(checkbox).parent().parent().parent('.nbors').nextAll('div').find(':checkbox').prop("checked", true);
        }
    };
    taskFunc.isGroupModified = false;
    //判断选中一级
    var isSelectedAll=function(){
        var c=true;
        $scope.task.selectedGroup=[];
        taskFunc.isGroupModified = true;
        angular.element('.nbors').find('.title').find(':checkbox').each(function(){
            if($(this).prop("checked")){
                var tmp ={'org_id':$(this).attr('gid'),'org_name':$(this).attr('gname')};
                $scope.task.selectedGroup.push(tmp);
            }
        });
        angular.element('.nbors').find('ul').find(':checkbox').each(function(){
            if($(this).prop("checked")==false){
                c=c&&false;
            }else{
                var id = $(this).attr('gid');
                var tmp ={'org_id':$(this).attr('gid'),'org_name':$(this).attr('gname')};
                $scope.task.selectedGroup.push(tmp);
            }
        });
        if(c){
            angular.element('.nbors').eq(0).find(':checkbox').prop("checked", true);
        }else{
            // angular.element('.nbors').eq(0).find('.title').find(':checkbox').prop("checked", false);
        }
    };
    //判断选中二级
    var selectAllsub=function(el){
        var c2=true;
        angular.element(el).parents('.second_li').find('ul').find(':checkbox').each(function(){
            if($(this).prop("checked")==false){
                c2=false;
            }
        });
        if(c2){
            angular.element(el).parents('.second_li').find('.cbox2').prop("checked", true);
        }else{
            // angular.element(el).parents('.second_li').find('.cbox2').prop("checked", false);
        }
    }

    var recursionEl=function(el){
        var li=true;
        if(angular.element(el).parents('.child_li').eq(0).parent('ul').find(':checkbox').length>0){
            angular.element(el).parent().parent('li').parent('ul').find(':checkbox').each(function(){
                if($(this).prop("checked")==false){
                    li=false;
                    return false;
                }
            });
            if(li){
                angular.element(el).parents('.child_li').eq(1).find(':checkbox').eq(0).prop("checked", true);
                if(angular.element(el).parent().parent('li').parent('ul').prevAll(':checkbox').length>0)
                    recursionEl(angular.element(el).parent().parent('li').parent('ul').prevAll(':checkbox'));
            }else{
                angular.element(el).parents('.child_li').eq(1).find(':checkbox').eq(0).prop("checked", false);
            }
        }
    }
    taskFunc.selectAllSubGroup=function($event){
        var checkbox = $event.target;
        if(checkbox.checked){
            angular.element(checkbox).parent().parent().next().find(':checkbox').prop("checked", true);
        }else{
            angular.element(checkbox).parent().parent().next().find(':checkbox').prop("checked", false);
            angular.element('.nbors').eq(0).find(':checkbox').prop("checked", false);
        }
        isSelectedAll();
    };
    taskFunc.selectAllSubGroup2=function($event){
        var checkbox = $event.target;
        if(checkbox.checked){
            angular.element(checkbox).parent().next().find(':checkbox').prop("checked", true);
            angular.element('.nbors').find('ul').find(':checkbox').each(function(){
                recursionEl(this);
            });
        }else{
            angular.element(checkbox).parent().next().find(':checkbox').prop("checked", false);
            // angular.element(checkbox).parent().parents('span').prev().find(':checkbox').prop("checked", false);
            // angular.element(checkbox).parent().parents('span').prev().find(':checkbox').prop("checked", false);
            // angular.element(checkbox).parents('.nbors').find('.title').find(':checkbox').prop("checked", false);
        }
        selectAllsub(checkbox);
        isSelectedAll();
    };

    taskFunc.selectGroup=function($event){
        selectAllsub($event.target);
        isSelectedAll();
    }
    taskFunc.showGroupList=function(index,isShow){
          $scope.task.allGroupsTree[index].show=isShow;
    }
    
    //保存
    taskFunc.saveTask = function () {
        $scope.isEdit = !$scope.isEdit;
        if ((!$scope.task.selectedMember || $scope.task.selectedMember.u_id == undefined) && $scope.task.taskTypeDefaultSelect.nums == 1) {
            alert("请选择指派人");
            $scope.isEdit = !$scope.isEdit;
            return;
        }
        if (typeof $scope.task.task_title == 'undefined' || $scope.task.task_title.length == 0) {
            alert('请设置任务标题');
            $scope.$broadcast('error', '请设置任务标题');
            $scope.isEdit = !$scope.isEdit;
            return;
        }
        //任务标题空格过滤
        $scope.task.task_title = $scope.task.task_title.replace(/(^\s*)/g, "");
        if (Publicfactory.checkEnCnstrlen($scope.task.task_title) > 100) {
            alert("任务标题不能超过50个字!");
            $scope.isEdit = !$scope.isEdit;
            return;
        }

        //判断任务描述长度
        if (typeof $scope.task.task_desc != 'undefined') {
            if (Publicfactory.checkEnCnstrlen($scope.task.task_desc) > 1000) {
                alert('任务内容长度不能大于500个字');
                $scope.isEdit = !$scope.isEdit;
                return false;
            }
        }

        if ((typeof $scope.task.begin_time == 'undefined' ||   $scope.task.begin_time == '' ||   $scope.task.end_time == '' || typeof   $scope.task.end_time == 'undefined')) {
            alert('请选择任务时间');
            $scope.isEdit = !$scope.isEdit;
            return;
        }
        var reg =/^([1-9]\d*|0)$/;
        if (!reg.test($scope.task.point) ) {
            $scope.task.point = 0;
            alert('请输入正确的纳米币');
            $scope.isEdit = !$scope.isEdit;
            return;
        }
        if ($scope.task.point > 0 && (parseInt($scope.leavePoints) + parseInt($scope.tempPoint) -  parseInt($scope.task.point)) < 0) {
            $scope.task.point = 0;
            alert('纳米币不足');
            $scope.isEdit = !$scope.isEdit;
            return;
        }

        var Today_date = new Date();
        var year_date = Today_date.getFullYear();
        var month_date = Today_date.getMonth() + 1;
        var day_date = Today_date.getDate();
        var hour_date = Today_date.getHours();
        var minute_date = Today_date.getMinutes();
        month_date < 10 ? month_date = 0 + '' + month_date : month_date;
        day_date < 10 ? day_date = 0 + '' + day_date : day_date;
        hour_date < 10 ? hour_date = 0 + '' + hour_date : hour_date;
        minute_date < 10 ? minute_date = 0 + '' + minute_date : minute_date;
        var Today_date_s = year_date + "-" + month_date + "-" + day_date + " " + hour_date + ":" + minute_date;
        $scope.task.begin_time = $('#task_datestart').val();
        $scope.task.end_time = $('#task_dateend').val();
        if($scope.task.begin_time>=$scope.task.end_time){
            alert('结束时间不能少于等于开始时间！');
            $scope.isEdit = !$scope.isEdit;
            return;
        }
        //切换项目时间，若项目结束时间小于开始时间则执行
        // if ($scope.task.taskProjectSelect) {
        //     if (Today_date_s > $scope.task.taskEndtimeRange) {
        //         alert('项目时间已过期，无法创建任务！');
        //         return;
        //     }
        // }

        if($scope.task.point>10000){
            alert('积分不能大于10000');
            $scope.isEdit = !$scope.isEdit;
            return;
        }
        if(task.point<0){
            alert('积分不能为负数');
            $scope.isEdit = !$scope.isEdit;
            return;
        }
        //任务级别

        //悬赏任务
        if ($scope.task.taskTypeDefaultSelect.nums == 2 && $scope.task.status < 2 ) {
            if ($scope.task.selectedGroup.length == undefined || $scope.task.selectedGroup.length == 0) {
                alert('请选择悬赏范围');
                $scope.isEdit = !$scope.isEdit;
                return;
            }
        }

        // if (!$scope.taskFunc.isSkillModified) {
        //     $scope.task.selecteSkill = [];
        // }
        if (!$scope.taskFunc.isGroupModified) {
            $scope.task.selectedGroup = [];
        }

        if ($scope.task.status >= 2) {
            $scope.task.task_type = 1;
        }
        var postData = {
            task_id: $scope.task.task_id,
            task_title: $scope.task.task_title,
            task_desc: $scope.task.task_desc,
            begin_time: $scope.task.begin_time,
            end_time: $scope.task.end_time,
            skills: $scope.task.selecteSkill,
            group: $scope.task.selectedGroup,
            point: $scope.task.point,
            task_level: $scope.task.levelDefaultSelect.nums,
            type: $scope.task.task_type,
            pro_id: $scope.task.taskProjectSelect.pro_id,
            charger: $scope.task.selectedMember,
            taskType: $stateParams.taskType
        };


        //提交数据库
        $http.post('/index.php?r=task/task/update-task', postData).success(function (data) {
            if (20000 != data.code) {
                $scope.isEdit = !$scope.isEdit;
                alert(data.msg);
                $scope.$broadcast('error', data.msg);
                return;
            }
            if (20000 == data.code) {
                alert(data.msg);
                //更新个人cookie
                //$rootScope.user.leave_points=parseInt($rootScope.user.leave_points) + parseInt($scope.tempPoint) -  parseInt(postData.point);
                $scope.leavePoints = parseInt(data.data);
                //$cookieStore.put('userInfo',$rootScope.user);
                $scope.task.point=0;
                // $rootScope.close_task();
                if ($scope.task.taskTypeDefaultSelect.nums == 2) {
                    //添加悬赏任务跳转
                    $state.go("^", {task_status: 3}, {reload: true});
                } else {
                    //添加指派任务跳转
                    $state.go("^", {task_status: 1}, {reload: true});
                }
                $scope.isEdit = !$scope.isEdit;
            }
        });
    }


});
