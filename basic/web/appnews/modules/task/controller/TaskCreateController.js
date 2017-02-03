TaskMod.controller('taskCreateCtrl', function ($scope, $http, $state, $cookieStore,$cookies, $stateParams, $rootScope, $document,taskModel,Publicfactory,permissionService,$timeout) {
    if (!permissionService.checkPermission('TaskCreate')) {
        $state.go('main.index');
        return false;
    }

    var system = $scope.system = {};
    system.random = new Date().getTime();
    // $rootScope.user =eval('('+$cookieStore.get('userInfo')+')');
    $rootScope.user = $cookieStore.get('userInfo');

    //初始化参数
    var taskFunc = $scope.task = {};
    var task = $scope.task;
    $scope.taskNotice = false;
    $scope.taskNoticesave = false;
    task.leavePoints = '';
    task.taskProjectSelectName = '';
    task.taskProjectSelect = {};
    taskModel.getLeavePoints($scope);
    //判断是否从创建项目跳转过来
    task.init_pro_id = $stateParams.pro_id?$stateParams.pro_id:0;

    //项目
    taskModel.getProject($scope, 1);
    task.setTaskTimeRange=function(){
        // task.taskProjectSelect = project;
        // task.taskProjectSelectName = project.pro_name;
        if (task.taskProjectSelect.length > 0) {
            task.taskStarttimeRange = task.taskProjectSelect.begin_time;
            task.taskEndtimeRange = task.taskProjectSelect.end_time;
        }
    }

    //1表示指派任务，2表示悬赏任务
    task.taskType = [
        {label: '指派任务', nums:1},
        {label: '悬赏任务', nums:2}
    ];

    //默认是指派任务
    task.taskTypeDefaultSelect = task.taskType[0];


    //任务级别
    task.taskLevel = [
        {label: '低', nums:3},
        {label: '中', nums:2},
        {label: '高', nums:1}
    ];
    //默认是低级别
    task.levelDefaultSelect = task.taskLevel[0];
    //开始时间
    task.begin_time = '';
    //结束时间
    task.end_time = '';
    //任务标题
    task.task_title = '';
    //任务内容
    task.task_desc = '';
    //任务积分
    task.point = '';


    var taskpoint = $(".taskpoint").val().replace(/(^\s*)/g, "");
    var taskpointReg = /^[0-9]*$/;
    task.pointkeyup = function(){
        if (!taskpointReg.test($(".taskpoint").val())) {
            alert("积分只能是大于0的整数，最大值为自己的总积分!");
            $(".taskpoint").val('');
            return false;
        }
    };

    //创建任务收缩
    function taskupdown(){
        var taskhight = parseInt($(".taskcreatewin").css('height'))-107,
            iclass = $(".taskcreatewin .morebtn.ischange");
        if( iclass.length ){
            $(".taskcreatewin .btnbor").css({"top":taskhight+"px","height":"100%"});
        }else{
            $(".taskcreatewin .btnbor").css({"top":"335px","height":"100%"});
        }

    }
    $(".taskcreatewin .morebtn").click(function(){
        var taskhight = parseInt($(".taskcreatewin").css('height'))-107,
            iclass = $(".taskcreatewin .morebtn.ischange");

        if( !iclass.length ){
            $(this).addClass("ischange").find('i').addClass("rotate180");
            $(this).find('span').css({'left':'73px'}).html("收起")
            $(".scrollbor").addClass("scroll");
            $(".hidebor").stop().slideDown();
            $(".taskcreatewin .btnbor").css({"top":taskhight+"px","height":"100%"});
        }else{
            $(this).removeClass("ischange").find('i').removeClass("rotate180");
            $(this).find('span').css({'left':'30px'}).html("点击补充更多任务信息");
            $(".scrollbor").removeClass("scroll");
            $(".hidebor").stop().slideUp();
            $(".taskcreatewin .btnbor").css({"top":"335px","height":"100%"});
        }

    });

    $(window).resize(function(){
        taskupdown();
    });

    //选择任务类型
    task.windowselect = function(){
        
    }


    //指派人
    task.groupMembers = [];
    task.selectedMember = {};
    //指派任务成员
    
    //打开选人
    task.openSelectedP=function(){
        $(".menbersearchbor").show();
        if($scope.task.groupMembers.length == 0)
         taskModel.getMembers($scope, '');
    }

    task.closeSelectedP=function() {
        $(".menbersearchbor").hide();
        $scope.task.searchMemberRealName='';
    }

    var timer='';
    task.allott=function(issearch){
        clearTimeout(timer);

        //获取所有的组织架构和人员
        timer=setTimeout(function(){
            var search='';
            if(issearch==1){
                search=$scope.task.searchMemberRealName;
            }
            if(issearch!=1){
                $scope.task.searchMemberRealName='';
            }
            taskModel.getMembers($scope, $scope.task.searchMemberRealName);
        },500);
    };
    task.selectMember=function($selecedMember){
       task.selectedMember = $selecedMember;
       task.closeSelectedP();
    }
    task.delmember=function(){
        task.selectedMember = {};
    }

    //上传附件
    task.files = [];
    task.upload = {};
    task.attId = [];

    //添加附件
    $scope.addFileBtn = function(uploader){
        uploader.url = '/index.php?r=task/task/upload&type=1&taskType='+task.taskTypeDefaultSelect.nums;
        uploader.onCompleteItem = function (fileItem, response, status, headers) {
            if(response.code!=20000 && response.msg){
                alert(response.msg);
                return false;
            }
            if(response.data !=undefined || response.data.name != undefined){
                task.attId.push(response.data.task_att_id);
                var temporary_att={};
                temporary_att.task_att_id=response.data.task_att_id;
                temporary_att.real_name=response.data.real_name;
                temporary_att.file_type=response.data.file_type;
                temporary_att.file_size=response.data.file_size;
                task.files.push(temporary_att);
            }

        };

    };

    //删除上传的附件
    task.delFiles = function(index, attId){
        $http.get('/index.php?r=task/task/del-att&attId='+attId).success(function(data) {
            if(data.code == 20000) {
                if($scope.attr){
                    $scope.attr.splice(index,1);
                }
                var temFile = $scope.task.files[index];
                if($scope.task.files){
                    $scope.task.files.splice(index,1);
                    $scope.task.attId.splice(index,1);
                }
            }
        });
    }

    //选择技能
    taskFunc.skillList = task.skillList = [];
    task.selecteSkill = [];
   
    task.openSelecteSkill=function(){
        
        $(".skillbor").show();
        var elem = $(".taskcreatewin .scrollbor");
        var elemmath = Math.max(elem.prop('scrollHeight'), elem.prop('offsetHeight'));
        $(".taskcreatewin .scrollbor").scrollTop(700+elemmath);
        if(task.skillList.length == 0) {
            taskModel.getAllSkillList($scope);
            $(".taskcreatewin .scrollbor").scrollTop(700+elemmath);
        }
         
    }

    task.closeSelecteSkill=function() {
        $(".skillbor").hide();
    }
    task.selectSkill=function($skill){
        task.selecteSkill.push($skill);
    }
    task.delSkill=function(index){
        task.selecteSkill.splice(index,1);
    }
    task.showList=function(index,isShow){
        task.skillList[index].show=isShow;
    }

    //技能选择框
    var updateSelected = function (action, id) {
        if (action == 'add' && task.selecteSkill.indexOf(id) == -1) task.selecteSkill.push(id);
        if (action == 'remove' && task.selecteSkill.indexOf(id) != -1) task.selecteSkill.splice(task.selecteSkill.indexOf(id), 1);
    }

    task.updateSelection = function ($event, id) {
        var checkbox = $event.target;
        var action = (checkbox.checked ? 'add' : 'remove');
        updateSelected(action, id);
    };

    task.isSelected = function (id) {
        return task.selecteSkill.indexOf(id) >= 0;
    };


    //悬赏范围
    task.allGroupsTree = [];
    //获取默认悬赏范围
    task.selectedGroup=[];
    //打开悬赏范围
    task.openSelecteGroup=function(){
        $(".offerbor").show();
        if($scope.task.allGroupsTree.length == 0)
            taskModel.getAllGroupTree($scope);
    }

    task.closeSelecteGroup=function() {
        $(".offerbor").hide();
    }
    task.getAllGroup=function(){
        //显示悬赏范围
        task.offerRange = !task.offerRange;
        //获取组织数据
        taskModel.getAllGroupTree($scope);
    }
    //悬赏的部门选择全公司
    task.selectAll=function($event){
        var checkbox = $event.target;
        if(checkbox.checked){
            angular.element(checkbox).parent().parent().parent('.nbors').nextAll('div').find(':checkbox').prop("checked", false);
        }else{
            angular.element(checkbox).parent().parent().parent('.nbors').nextAll('div').find(':checkbox').prop("checked", true);
        }
    };
    //判断选中一级
    var isSelectedAll=function(){
        var c=true;
        task.selectedGroup=[];
        angular.element('.nbors').find('.title').find(':checkbox').each(function(){
            if($(this).prop("checked")){
                var tmp ={'org_id':$(this).attr('gid'),'org_name':$(this).attr('gname')};
                task.selectedGroup.push(tmp);
            }
        })
        angular.element('.nbors').find('ul').find(':checkbox').each(function(){
            if($(this).prop("checked")==false){
                c=c&&false;
            }else{
                var id = $(this).attr('gid');
                var tmp ={'org_id':$(this).attr('gid'),'org_name':$(this).attr('gname')};
                task.selectedGroup.push(tmp);
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
    task.selectAllSubGroup=function($event){
        var checkbox = $event.target;
        if(checkbox.checked){
            angular.element(checkbox).parent().parent().next().find(':checkbox').prop("checked", true);
        }else{
            angular.element(checkbox).parent().parent().next().find(':checkbox').prop("checked", false);
            angular.element('.nbors').eq(0).find(':checkbox').prop("checked", false);
        }
        isSelectedAll();
    };
    task.selectAllSubGroup2=function($event){
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

    task.selectGroup=function($event){
        selectAllsub($event.target);
        isSelectedAll();
    }
    task.showGroupList=function(index,isShow){
        task.allGroupsTree[index].show=isShow;
        if(isShow){
            
        }
    }
    

    $scope.$watchCollection('task.selectedGroup', function (newVal, oldVal) {
        var listNode = angular.element('.taskxsbor .xslistbor');
        setTimeout(function() {
            if( listNode.length > 0 && listNode[0].clientHeight > 57 ){ 
                if ( !$(".taskcreatewin .scroll").length ){
                     $(".taskcreatewin .morebtn").click();
                }
            }
        }, 100)
    });


   

    //保存并发布
    task.createTask = function(isPublish) {
        if (typeof task.task_title == 'undefined' || task.task_title.length == 0) {
            alert('请设置任务标题');
            $scope.$broadcast('error', '请设置任务标题');
            return;
        }
        //任务标题空格过滤
        task.task_title = task.task_title.replace(/(^\s*)/g, "");
        if (Publicfactory.checkEnCnstrlen(task.task_title) > 100) {
            alert("任务标题不能超过50个字!");
            return;
        }

        //判断任务描述长度
        if (typeof task.task_desc != 'undefined') {
            if (Publicfactory.checkEnCnstrlen(task.task_desc) > 1000) {
                alert('任务内容长度不能大于500个字');
                return false;
            }
        }

        if ((typeof  task.begin_time == 'undefined' || task.begin_time == '' || task.end_time == '' || typeof task.end_time == 'undefined')) {
            alert('请选择任务时间');
            return;
        }

        if (task.point > 0 && (task.leavePoints - task.point) < 0) {
            task.point = 0;
            alert('可分配纳米币不足');
            return;
        }
        if ((!task.selectedMember || task.selectedMember.u_id == undefined) && task.taskTypeDefaultSelect.nums == 1) {
            alert("请选择指派人");
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

        task.begin_time = $('#task_datestart').val();
        task.end_time = $('#task_dateend').val();

        // if (Today_date_s >= task.begin_time) {
        //     alert('开始时间不能小于今天当前时间')
        //     return;
        // }
        if (task.begin_time >= task.end_time) {
            alert('结束时间不能小于等于开始时间！')
            return;
        }
        //切换项目时间，若项目结束时间小于开始时间则执行
        if (task.taskProjectSelect) {
            if (Today_date_s > task.taskEndtimeRange) {
                alert('项目时间已过期，无法创建任务！');
                return;
            }
        }

        if($scope.task.point>100000){
            alert('积分不能大于100000');
            return;
        }
        if(task.point<0){
            alert('积分不能为负数');
            return;
        }
        if(!task.point){
            task.point=0;
        }
        //任务级别

        //悬赏任务
        if (task.taskTypeDefaultSelect.nums == 2) {
            if (task.selectedGroup.length == 0) {
                alert('请选择悬赏范围');
                return;
            }
        }

        var postData = {
            attr: task.attId,
            title: task.task_title,
            desc: task.task_desc,
            startTime: task.begin_time,
            endTime: task.end_time,
            skills: task.selecteSkill,
            group: task.selectedGroup,
            point: task.point,
            task_level: task.levelDefaultSelect.nums,
            type: task.taskTypeDefaultSelect.nums,
            pro_id: task.taskProjectSelect.pro_id,
            is_publish: isPublish,
            charger: task.selectedMember
        };
 

        //提交数据库
        $http.post('/index.php?r=task/task/createtask', postData).success(function (data) {

            if (20000 != data.code) {
                alert(data.msg);
                return;
            }
            if (20000 == data.code) {
                //清空数据
                task.attId = [];
                task.files = [];
                task.selectedGroup1 = [];
                task.startLabel = '';
                task.task_desc = '';
                task.title = '';
                //更新个人cookie
                if (task.point > 0) {
                    task.updateCookie();
                }
                $("#masklayer2").show();
                
                if(isPublish==1){
                   $scope.taskNotice = true;
                }else{
                   $scope.taskNoticesave = true;
                }
                
            }
        });
    }

    //更新用户cookie
    task.updateCookie = function(){
        //$rootScope.user.leave_points=$rootScope.user.leave_points-task.point;
        task.leavePoints = task.leavePoints -task.point;
        //$cookieStore.put('userInfo',$rootScope.user);
        task.point = '';
    }
    //继续创建
    task.goOnCreate = function(){
        //清空数据
        task.attId = [];
        task.files = [];
        task.point = '';
        task.selectedGroup1 = [];
        task.selectedGroup = [];
        task.selecteSkill = [];
        task.taskProjectSelect = {};
        task.startLabel = '';
        task.task_desc = '';
        task.title = '';
        //默认是低级别
        task.levelDefaultSelect = task.taskLevel[0];
        //开始时间
        task.begin_time = '';
        //结束时间
        task.end_time = '';
        //任务标题
        task.task_title = '';
        //任务内容
        task.task_desc = '';
        //任务积分
        task.point = '';
        task.selectedMember = {};
        $("#masklayer2").hide();
        $state.go("main.task.create",{}, {reload: true});
    }
    //跳转至我的列表
    task.toMyList = function(){
        if (task.taskTypeDefaultSelect.nums == 2) {
            //悬赏任务跳转
            $state.go("main.task.myRewardTask", {task_status: 3}, {reload: true});
        } else {
            //指派任务跳转
            $state.go("main.task.myReleaseTask", {task_status: 1}, {reload: true});
        }
    }
    
});
