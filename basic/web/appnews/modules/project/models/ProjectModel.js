ProjectMod.factory('projectModel',function($http,$state,$cookieStore,$location,filtersModel, util){
    var  service={};
    
    //获取项目
    service.getPro=function($scope,status){
        $http.post('/index.php?r=project/project/getpro',{public:status, page:$scope.param_project.page, type:$scope.param_project.type, status:$scope.param_project.search_status, begin_time:$scope.param_project.search_begin_time, end_time:$scope.param_project.search_end_time, pro_name:$scope.param_project.search_pro_name})
            .success(function(data, status) {
                $scope.project.projectlist = data.data.proList;
                $scope.page.curPage = data.data.page.curPage;
                $scope.page.sumPage = data.data.page.sumPage;

                /*//初始化数据
                var ProList = {};
                //按状态查询
                ProList.search_status = 0
                //按开始时间查询
                ProList.search_begin_time= '';
                //按结束时间查询
                ProList.search_end_time= '';
                //按项目名称查询
                ProList.search_pro_name= '';
                ProList.page = 1;
                ProList.type = 1;
                $cookieStore.put('ProList',ProList);*/
                /*angular.element.each(data.data.proList, function (key, val) {
                    $scope.project.projectlist.push(val);
                });
                $scope.project.pageLength = $scope.project.projectlist.length;*/
        });
    };

    //获取项目进度信息head
    service.getProProgressHead=function($scope){
        $http.post('/index.php?r=project/project/pro-progress-head',{pro_id:$scope.project_param.pro_id})
            .success(function(data, status) {
                if(data.code==1){
                    $scope.chartData = new Array();
                    $scope.chartData[0] = data.data.taskInfo.count-data.data.taskInfo.fcount;
                    $scope.chartData[1] = data.data.taskInfo.fcount;
                    $scope.chartData[2] = data.data.taskInfo.count;
                    if(data.data.taskInfo.count == 0){
                        var temp ='';
                    }else{
                        var temp =100;
                    }
                    $scope.colors = ['#ff6c60', '#57c8f2', '#63de6a'];
                    $scope.chartConfig = {
                        series: [{
                            data: [
                                {
                                    y: parseInt((((data.data.taskInfo.count-data.data.taskInfo.fcount)/data.data.taskInfo.count)*100).toFixed(2)),
                                    color:$scope.colors[0]
                                },
                                {
                                    y: 100-parseInt((((data.data.taskInfo.count-data.data.taskInfo.fcount)/data.data.taskInfo.count)*100).toFixed(2)),
                                    color:$scope.colors[1]
                                },
                                {
                                    y: temp,
                                    color:$scope.colors[2]
                                }
                            ],
                            dataLabels: {
                                enabled: true,
                                formatter: function() {
                                    return this.y +'%';
                                }
                            }
                        }],
                        options: {
                            exporting: {
                                enabled: false
                            },
                            chart: {
                                type: 'bar'
                            },
                            tooltip: {
                                enabled: false
                            },
                            legend : {
                                enabled: false
                            }
                        },
                        title: {
                            text: ''
                        },
                        yAxis: {
                            currentMax: 100,
                            currentMin: 0,
                            title: {
                                text: ''
                            }
                        },
                        size: {
                            width: 975,
                            height: 220
                        },
                        xAxis: {
                            categories: []
                            //categories: ['未完成', '已完成', '总任务']
                        }
                    };
                    //项目信息
                    $scope.project.proInfo = data.data.proInfo;
                }else{
                    $state.go('^');
                }
            });
    };

    //获取项目进度信息list
    service.getProProgressList=function($scope){
        $http.post('/index.php?r=project/project/pro-progress-list',{pro_id:$scope.project_param.pro_id})
            .success(function(data, status) {
                if(data.code==1){
                    //项目成员信息
                    $scope.project.proMember = data.data.proMember;
                    //项目信息
                    $scope.project.proInfo = data.data.proInfo;
                }else{
                    $state.go('^');
                }
            });
    };

    //项目进度中根据项目成员、任务状态来获取任务信息
    service.getProProgressTask=function($scope){
        $http.post('/index.php?r=project/project/get-pro-mem-status-task',JSON.stringify($scope.project_param))
            .success(function(data, status) {
                if(data.code==1){
                    angular.element.each(data.data.list, function (key, val) {
                        $scope.project.proTask.push(val);
                    });
                    $scope.project_param.page=$scope.project.proTask.length;
                    $scope.project.isTaskWin = true;
                }else{
                    $state.go('^');
                }
            });
    };

    //根据状态获取项目任务列表
    service.getProTaskListInfo=function($scope){
        $http.post('/index.php?r=project/project/pro-task-list-info', JSON.stringify($scope.project_param))
            .success(function(data, status) {
                if(data.code==1){
                    $scope.project.taskInfo = data.data.list;
                    $scope.page.curPage = data.data.page.curPage;
                    $scope.page.sumPage = data.data.page.sumPage;
                }else{
                    $state.go('^');
                }
            });
    };

    //获取项目日志
    service.getProLog=function($scope){
        $http.post('/index.php?r=project/project/pro-log',{pro_id:$scope.project.param_pro_id, page:$scope.project.pageLength})
            .success(function(data, status) {
                if(data.code==1){
                    //设置显示格式
                    angular.element.each(data.data.proLog, function (key, val) {
                        var temp=[];
                        temp['date']='';
                        temp['week']='';
                        temp['subCon']=[];
                        var tempCon = [];
                        tempCon['time']='';
                        tempCon['u_id']='';
                        tempCon['real_name']='';
                        tempCon['content']='';
                        var is_set = 1;
                        angular.element.each($scope.project.proLog, function (key1, val1) {
                            if(val1['date']==val['date']){
                                tempCon['time'] = val['time'];
                                tempCon['u_id']=val['u_id'];
                                tempCon['real_name']=val['real_name'];
                                tempCon['content']=val['content'];
                                tempCon['head_img_path']=val['head_img_path'] + '?' + Date.now();
                                $scope.project.proLog[key1]['subCon'].push(tempCon);
                                is_set = 0;
                            }
                        });
                        if(is_set==1){
                            temp['date'] = val['date'];
                            temp['week'] = val['week'];
                            tempCon['time'] = val['time'];
                            tempCon['u_id']=val['u_id'];
                            tempCon['real_name']=val['real_name'];
                            tempCon['content']=val['content'];
                            tempCon['head_img_path']=val['head_img_path'] + '?' + Date.now();
                            temp['subCon'].push(tempCon);
                            $scope.project.proLog.push(temp);
                        }
                    });
                    $scope.project.pageLength=$scope.project.pageLength+data.data.proLog.length;
                }else{
                    $state.go('^');
                }
            });
    };

    //获取项目详情
    service.getProDetail=function($scope){
        $http.post('/index.php?r=project/project/pro-detail',{pro_id:$scope.project.param_pro_id})
            .success(function(data, status) {
                if(data.code==1){
                    data.data.proMember = util.setImgRand(data.data.proMember);
                    $scope.project.proInfo = data.data.proInfo;
                    $scope.project.proMember = data.data.proMember;
                }else{
                    alert(data.msg);
                    $state.go('^')
                }
            });
    };

    //添加项目
    service.addPro=function($scope){
        $http.post('/index.php?r=project/project/create-pro', JSON.stringify($scope.paramobj))
            .success(function(data, status) {
                if(data.code==1){
                    $scope.project.pro_id = data.pro_id;
                    $scope.project.isCreateProSuccessWin = true;
                    $("#masklayer2").show();
                }else{
                    alert(data.msg);
                }
            });
    };

    //项目编辑获取项目信息
    service.getProDetailSimp=function($scope){
        $http.post('/index.php?r=project/project/pro-detail-simp', {pro_id:$scope.project.param_pro_id})
            .success(function(data, status) {
                if(data.code==1){
                    //项目Id
                    $scope.paramobj.pro_id = data.data.proInfo['pro_id'];
                    //项目名称
                    $scope.paramobj.pro_name = data.data.proInfo['pro_name'];
                    //公开状态
                    $scope.paramobj.public_type = data.data.proInfo['public_type'];
                    //开始时间
                    $scope.paramobj.begin_time = data.data.proInfo['begin_time_f'];
                    //结束时间
                    $scope.paramobj.end_time = data.data.proInfo['end_time_f'];
                    //是否可以编辑时间
                    if(data.data.proInfo['status'] != 1){
                        $scope.project.isEditTime = true;
                    }
                    $scope.project.status = data.data.proInfo['status'];
                    //项目成员
                    //$scope.paramobj.proMember = data.data.proMember;
                    angular.element.each(data.data.proMember, function (key, val) {
                        var temp={};
                        temp.u_id = val.u_id;
                        temp.real_name = val.real_name;
                        temp.head_img = val.head_img_path;
                        temp.owner = val.owner;
                        $scope.paramobj.proMember.push(temp);
                    });
                    //设置工作日
                    service.getWorkDay($scope);
                }else{
                    $state.go('^');
                }
            });
    };

    //编辑项目
    service.editPro=function($scope){
        $http.post('/index.php?r=project/project/edit-pro', JSON.stringify($scope.paramobj))
            .success(function(data, status) {
                if(data.code==1){
                    $state.go('main.project.mycreatepro',{isInit:0});
                    alert(data.msg);
                }else{
                    alert(data.msg);
                }
            });
    };

    //删除项目
    service.delPro=function($scope){
        $http.post('/index.php?r=project/project/del-pro', {pro_id:$scope.project.param_pro_id})
            .success(function(data, status) {
                if(data.code==1){
                    $state.go('main.project.mycreatepro',{isInit:0},{reload:true});
                    alert(data.msg);
                }else{
                    alert(data.msg);
                }
            });
    };

    //项目归档
    service.CompletePro=function($scope){
        $http.post('/index.php?r=project/project/complete-pro', {pro_id:$scope.project.param_pro_id})
            .success(function(data, status) {
                if(data.code==1){
                    $scope.project.isCompleteWin=false;
                    $("#masklayer2").hide();
                    service.getProDetail($scope);
                    alert(data.msg);
                }else{
                    alert(data.msg);
                }
            });
    };

    //项目延期
    service.delayPro=function($scope){
        $http.post('/index.php?r=project/project/delay-pro', {pro_id:$scope.project.param_pro_id,delay_time:$scope.project.param_delay_time,delay_reason:$scope.project.param_delay_reason})
            .success(function(data, status) {
                if(data.code==1){
                    $scope.project.proInfo.delay_time_f = data.data.delay_time_f;
                    $scope.project.proInfo.delay_time = data.data.delay_time;
                    $scope.project.isDelayWin=false;
                    $("#masklayer2").hide();
                    alert(data.msg);
                }else{
                    alert(data.msg);
                }
            });
    };

    //项目甘特图页面
    service.getGantt=function($scope){
        $http.post('/index.php?r=project/project/gantt', {pro_id:$scope.project_param.pro_id})
            .success(function(data, status) {
                if(data.code==1){
                    for (var i = 0, len = data.data.proTask.length; i< len; i++ ){
                        temp1=data.data.proTask[i].tasks[0].from.split('-');
                        temp2=data.data.proTask[i].tasks[0].to.split('-');
                        // 根据时间间隔设置显示方式
                        if(temp2[0] - temp1[0] >= 1 || temp2[1] - temp1[1] >= 2){
                            $scope.options.width = true;
                        }
                        data.data.proTask[i].tasks[0].from = new Date(temp1[0], temp1[1]-1, temp1[2], temp1[3], temp1[4], temp1[5]);
                        data.data.proTask[i].tasks[0].to = new Date(temp2[0], temp2[1]-1, temp2[2], temp2[3], temp2[4], temp2[5]);
                    }
                    $scope.data=data.data.proTask;

                    $scope.project.proInfo = data.data.proInfo;
                }else{
                    $state.go('^');
                }
            });
    };

    //获取项目创建人信息
    service.getProCreateMemInfo = function ($scope) {
        $scope.$broadcast('loadOpt', 'block');
        $http.post('/index.php?r=project/project/pro-create-mem-info',{}).success(function (data, status, headers, config) {
            data.data = util.setImgRand(data.data);
            data.data.owner=1;
            $scope.paramobj.proMember.push(data.data);
        });
    }

    //获取成员列表
    service.getMembers = function ($scope) {
        $scope.$broadcast('loadOpt', 'block');
        $http.post('/index.php?r=task/task/allgroupmember',{'search' : $scope.project.search_mem, 'lately':$cookieStore.get('lately')}).success(function (data, status, headers, config) {
            $scope.project.allMember = data.data;
        });
    }

    //获取工作日
    service.getWorkDay = function ($scope) {
        $scope.$broadcast('loadOpt', 'block');
        $http.post('/index.php?r=project/project/workday',{begin_time:$scope.paramobj.begin_time,end_time:$scope.paramobj.end_time}).success(function (data, status, headers, config) {
            $scope.project.workDay = data.count;
        });
    }

    //设置初始化项目列表页session数据
    service.setInit = function($scope){
        var ProList = {};
        //按状态查询
        ProList.search_status = $scope.param_project.search_status;
        //按开始时间查询
        ProList.search_begin_time= $scope.param_project.search_begin_time;
        //按结束时间查询
        ProList.search_end_time= $scope.param_project.search_end_time;
        //按项目名称查询
        ProList.search_pro_name= $scope.param_project.search_pro_name;
        ProList.page = $scope.param_project.page;
        ProList.type = $scope.param_project.type;
        $cookieStore.put('ProList',ProList);
    }

    //设置初始化项目列表页session数据
    service.setInit1 = function($scope){
        //初始化数据
        var ProList = {};
        //按状态查询
        ProList.search_status = 0
        //按开始时间查询
        ProList.search_begin_time= '';
        //按结束时间查询
        ProList.search_end_time= '';
        //按项目名称查询
        ProList.search_pro_name= '';
        ProList.page = 1;
        ProList.type = 1;
        $cookieStore.put('ProList',ProList);
    }

    //处理项目任务也tab标签样式
    service.setTab = function($scope){
        angular.element(".abtn a").eq(0).removeClass('selected');
        angular.element(".abtn a").eq(1).removeClass('selected');
        angular.element(".abtn a").eq(2).removeClass('selected');
        angular.element(".abtn a").eq($scope.project_param.status-1).addClass('selected');
    }

    //获取项目信息
    service.getProInfo = function($scope,pro_id){
        $http.post('/index.php?r=project/project/pro-per',{pro_id:pro_id}).success(function (data, status, headers, config) {
            $scope.project.proInfo = data.data;
        });
    }

    //获取项目成员工作报告
    service.getProMemWorkReport = function($scope){
        $http.post('/index.php?r=project/project/pro-mem-work-report',JSON.stringify($scope.param_project)).success(function (data, status, headers, config) {
            $scope.project.reportInfo = data.data.list;
            $scope.page.curPage = data.data.page.curPage;
            $scope.page.sumPage = data.data.page.sumPage;
            $scope.param_project.date = data.data.date;
        });
    }

    //获取项目成员周报日报
    service.getProMemReport = function($scope){
        $http.post('/index.php?r=project/project/pro-mem-report',JSON.stringify($scope.project.paramPreMenReport)).success(function (data, status, headers, config) {
            if(data.code==1){
                for(var i=0; i<data.data.list.length; i++){
                    data.data.list[i].commit_time_format = filtersModel.filterDateTime(data.data.list[i].commit_time*1000);
                }
                $scope.project.proMemReportList = data.data.list;
                $scope.project.isPreMenReport = true;
            }
            else{
                alert(data.msg);
            }
        });
    }

    service.returnList = function($scope){
         if($location.path().indexOf("/mycreatepro") > 0){//我创建的项目
            $state.go('main.project.mycreatepro',{isInit:0,list_status:0});
        }else if($location.path().indexOf("/myinvoepro") > 0){//我参与的项目
            $state.go('main.project.myinvoepro',{isInit:0,list_status:0});
        }else if($location.path().indexOf("/openpro") > 0){//公开项目
            $state.go('main.project.openpro',{isInit:0,list_status:0});
        }

    }

    return service;
});