IndexMod.factory('deskModel',function($http,$state) {
    var service = {};
    service.getDeskTpl = function($scope){
        $http.get('/index.php?r=desk/desk/desktemplet')
            .success(function(data, status) {
                if (data.code == 20000) {
                    if (data.data.big.length > 0) {
                        $scope.index.bigTpl = data.data.big;
                    }
                    if (data.data.small.length > 0) {
                        $scope.index.smallTpl = data.data.small;
                    }
                } else {
                    //默认模板
                    $scope.index.bigTpl = [
                        'mytask',
                        'survey',
                        'attendance',
                        'project',
                        'notice',
                        'shortcut'
                    ];
                    $scope.index.smallTpl = [
                        {'url': 'main.workStatement.myWorkStatementTable.edite', 'class':'bg-blue', 'title' : '工作报告', 'content' : '汇报一下工作情况吧'},
                        {'url' : 'main.apply.application', 'class':'bg-green', 'title' : '发起申请', 'content' : '请假/报销/设备等申请'},
                        {'url' : 'main.project.createpro', 'class':'bg-ltgreen','title' : '创建项目', 'content' : '创建一个项目'},
                        {'url' : 'main.task.create', 'class':'bg-blue', 'title' : '创建任务', 'content' : '任务创建快捷入口'}
                    ];
                }

                angular.forEach($scope.index.bigTpl, function(item, index){
                    switch(item){
                        case 'mytask':
                            service.getMyTask($scope);
                            break;
                        case 'mywork':
                            service.getMyWork($scope);
                            break;
                        case 'attendance':
                            service.getAttendance($scope);
                            break;
                        case 'project':
                            service.project($scope);
                            break;
                        case 'notice':
                            service.notice($scope);
                            break;
                        case 'applyproval':
                            service.myApproval($scope);
                            break;
                        case 'myapply':
                            service.myApply($scope);
                            break;
                        case 'scoreboard':
                            service.scoreBoard($scope);
                            break;
                        case 'survey':
                            service.survey($scope);
                            break;
                        case 'workstate':
                            service.workstatApproval($scope);
                            break;
                    }
                })
                //工作台背景图显示隐藏控制
                $scope.index.isShowWorkbenchBg=$scope.index.bigTpl.every(function(item){
                    return (item=='blanktpl');
                });

                service.drag();
            });
    }
    //拖拽
    service.drag=function(){
        var wrap=angular.element('.sortable');
        wrap.sortable({
            revert: 1000,
            cursor: 'move',
            update: function(){
                var tplPosition = [];
                //获取模板位置
                angular.element('.big-module').each( function(key, val){
                    tplPosition.push(angular.element(val).attr('tpl_name'));
                });
                if (tplPosition.length < 1) {
                    return false;
                }

                //保存调整
                $http.post('/index.php?r=desk/desk/modify-big', tplPosition)
                    .success(function(data, status) {
                        if (data.code == 20000) {
                            //alert(data.msg);
                        } else {
                            // alert(data.msg);
                        }
                    });
                },
            start:function(event){
                angular.element(event.target).css('pointer-events','none');
            },
            stop: function(event) {
                angular.element(event.target).css('pointer-events','auto');
            }
        });
    }

    //工作台设置
    service.getDeskSet = function($scope){
        $http.get('/index.php?r=desk/desk/desk-set')
            .success(function(data, status) {
                if (data.code == 20000) {
                    if (data.data.big.length > 0) {
                        angular.forEach($scope.index.bigTpl, function(item, index){

                            if(data.data.big.indexOf(item.name) > -1){
                                $scope.index.bigTpl[index].is_select = true;
                            }

                            if(data.data.big.indexOf('shortcut') > -1){
                                angular.element('.col-gray.f16').css('visibility','visible');
                                angular.element('.set-shortcut').css('visibility','visible');
                            }else{
                                angular.element('.col-gray.f16').css('visibility','hidden');
                                angular.element('.set-shortcut').css('visibility','hidden');
                            }
                        });
                    }
                    if (data.data.small.length > 0) {
                        angular.forEach($scope.index.smallTpl, function(item, index){
                            if(data.data.small.indexOf(item.name) > -1){
                                $scope.index.smallTpl[index].is_select = true;
                            }
                        });
                    }
                }
            });
    }
    service.editeDeskTpl = function($scope, $state){
        $http.post('/index.php?r=desk/desk/modify-desk', JSON.stringify($scope.index_param))
            .success(function(data, status) {
                if (data.code == 20000) {
                    $state.go('^', {}, {'reload': true});
                } else {
                    alert(data.msg);
                }
            });
    }

    service.getMyTask = function($scope){
        $http.post('/index.php?r=desk/desk/my-task', JSON.stringify($scope.index_param))
            .success(function(data, status) {
                if (data.code == 20000) {
                    $scope.index.task = data.data;
                }
            });
    }

    service.getMyWork = function($scope){
        $http.post('/index.php?r=desk/desk/msg-list', JSON.stringify($scope.index_param))
            .success(function(data, status) {
                if (data.code == 1) {
                    $scope.index.my_work = data.data;
                }
            });
    }

    service.getAttendance = function($scope){
        $http.post('/index.php?r=desk/desk/my-attendance', JSON.stringify($scope.index_param))
            .success(function(data, status) {
                if (data.code == 20000) {
                    $scope.index.attendance = data.data;
                }
            });
    }

    //项目轮播
    service.project = function($scope){
        $http.post('/index.php?r=desk/desk/project', JSON.stringify($scope.index_param))
            .success(function(data, status) {
                if (data.code == 20000) {
                    $scope.index.project = data.data.project_list;
                    $scope.index.project_count = data.data.project_count;
                }
                if($scope.index.project.length>=3){
                    $('.banner-btnl,.banner-btnr').show();
                }else{
                    $('.banner-btnl,.banner-btnr').hide();
                }
                var ul=angular.element('.banner ul');
                var lis=Math.ceil($scope.index.project.length/2);
                for(var i= 1,signTem='';i<=lis;i++){
                    signTem+='<b></b>&nbsp;';
                }
                angular.element('.sign').append(signTem);
                angular.element('.sign b:first-child').addClass('sign-cur');
                ul.css('width',$scope.index.project.length*240+'px');
                var timeout=true;
                var curPage =1;
                function slider(i){
                    if(timeout){
                        i= i.data;
                        var curLeft=parseFloat(ul.css('left'));
                        var curRight=parseFloat(ul.css('right'));
                        var sumPage = Math.ceil($scope.index.project.length/2);
                        if(i==-1){
                            curPage++;
                            if(curPage>sumPage){
                                curPage--;
                                return;
                            }
                        }
                        if(i==1){
                            curPage--;
                            if(curPage<=0){
                                curPage++;
                                return;
                            }
                        }
                        var left=(i==-1?'-=480px':'+=480px');
                        if(!$('.banner ul').is(":animated")){
                            $('.banner ul').animate({left:left},function(){
                                i==1?angular.element('.sign').find('b[class="sign-cur"]').removeClass('sign-cur').prev().addClass('sign-cur'):
                                    angular.element('.sign').find('b[class="sign-cur"]').removeClass('sign-cur').next().addClass('sign-cur');
                            });
                        }
                    }else{
                        return;
                    }
                    timeout=false;
                    setTimeout(function(){
                        timeout=true;
                    },600)
                }
                $('.banner-btnl').click(1,slider);
                $('.banner-btnr').click(-1,slider);
            });
    }

    service.notice = function($scope){
        $http.post('/index.php?r=desk/desk/notice', JSON.stringify($scope.index_param))
            .success(function(data, status) {
                if (data.code == 20000) {
                    $scope.index.notice = data.data;
                }
            });
    }

    service.myApply = function($scope){
        $http.post('/index.php?r=desk/desk/my-apply', JSON.stringify($scope.index_param))
            .success(function(data, status) {
                if (data.code == 20000) {
                    $scope.index.my_apply = data.data;
                }
            });
    }

    service.myApproval = function($scope){
        $http.post('/index.php?r=desk/desk/my-approval', JSON.stringify($scope.index_param))
            .success(function(data, status) {
                if (data.code == 20000) {
                    $scope.index.my_approval = data.data;
                }
            });
    }

    service.survey = function($scope){
        $http.post('/index.php?r=desk/desk/survey', JSON.stringify($scope.index_param))
            .success(function(data, status) {
                if (data.code == 20000) {
                    $scope.index.survey = data.data;
                }
            });
    }

    service.workstatApproval = function($scope){
        $http.post('/index.php?r=desk/desk/workstate-approve', JSON.stringify($scope.index_param))
            .success(function(data, status) {
                if (data.code == 20000) {
                    $scope.index.workstate_approval = data.data;
                }
            });
    }

    service.scoreBoard = function($scope){
        $http.post('/index.php?r=desk/desk/score-board', JSON.stringify($scope.index_param))
            .success(function(data, status) {
                if (data.code == 20000) {
                    $scope.index.score_board = data.data;
                }
            });
    }

    //获取用户信息
    service.getUserInfo = function($scope){
        $http.post('/index.php?r=desk/desk/user-info', {})
            .success(function(data, status) {
                if (data.code == 1) {
                    $scope.userInfo.userInfo = data.data.memInfo;
                    $scope.userInfo.skillInfo = data.data.skillInfo;
                    if($scope.userInfo.skillInfo != null){
                        if(parseInt($scope.userInfo.skillInfo.point)>parseInt($scope.userInfo.skillInfo.nextpoint)){
                            $scope.userInfo.skillInfo.degree = 100;
                        }else{
                            $scope.userInfo.skillInfo.degree = (parseInt($scope.userInfo.skillInfo.point)/parseInt($scope.userInfo.skillInfo.nextpoint))*100;
                        }
                    }else{
                        $scope.userInfo.skillInfo = {};
                        $scope.userInfo.skillInfo.level = 0;
                        $scope.userInfo.skillInfo.degree = 0;
                    }
                }
            });
    }

    return service;

});