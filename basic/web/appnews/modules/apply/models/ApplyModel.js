ApplyMod.factory('applyModel', function ($http, $state, filtersModel,formshowfactory,$compile, util) {
    var service = {};
    //获取所有项目, 并初始化数据

    //我的申请列表
    service.getMyApplyList = function ($scope) {
        $http.post('/index.php?r=apply/apply/apply-list', JSON.stringify($scope.applyParams))
            .success(function (data, status) {
                $scope.apply.list = data.list;
                $scope.page.curPage = data.curPage;
                $scope.page.sumPage = data.sumPage;
            });
    }

    //我的待办列表
    service.getMyAgentList = function ($scope) {
        $http.post('/index.php?r=apply/apply/apply-agent', JSON.stringify($scope.applyParams))
            .success(function (data, status) {
                $scope.apply.list = data.list;
                $scope.page.curPage = data.curPage;
                $scope.page.sumPage = data.sumPage;
            });
    }

    //获取所有可用表单
    service.getApplyModel = function ($scope) {
        //$http.get('/index.php?r=apply/apply-model/useful-list').success(function (data) {
        $http.get('/index.php?r=apply/apply-model/model-list').success(function (data) {
            $scope.apply.modelList = data.data;
            $scope.apply.modelList.unshift({'model_id': 0, 'title': '申请单名称'});
        });
    }

    //删除申请
    service.delApply = function (apply_id,$scope) {
        $http.get('/index.php?r=apply/apply/apply-del&apply_id='+apply_id).success(function (data) {
            alert(data.msg);
            if(data.code == 1) {
                service.closeMark($scope);
                service.getMyApplyList($scope);
            }
        });
    }

    //催办
    service.press = function (apply_id,$scope) {
        $http.get('/index.php?r=apply/apply/press&apply_id='+apply_id).success(function (data) {
            alert(data.msg);
            if(data.code == 1) {
                service.closeMark($scope);
                service.getMyApplyList($scope);
            }
        });
    }

    //撤回
    service.revoke = function (apply_id,$scope,applyStatus) {
        $http.get('/index.php?r=apply/apply/revoke&apply_id='+apply_id).success(function (data) {
            alert(data.msg);
            if(data.code == 1) {
                $("#masklayer1").hide();
                //是否显示请假申请查看详情页
                $scope.apply.isLeaveDetailApplyWin = false;
                service.getMyApplyList($scope);
                if(applyStatus==4){
                    $scope.apply.editPopup = true;
                }
                
            }
        });
    }
    

    //获取所有可用表单(发起申请用)
    service.getUsefulModel = function ($scope) {
        $http.get('/index.php?r=apply/apply-model/useful-list').success(function (data) {
            $scope.applyApp.modelList = data.data;
        });
    }

    //获取所有表单(管理申请用)
    service.getAllModel = function ($scope) {
        $http.get('/index.php?r=apply/apply-model/model-list').success(function (data) {
            $scope.model.modelList = data.data;
        });
    }

    //批量审核
    service.verifyBatch = function ($scope) {
        $http.post('/index.php?r=apply/apply/batch-verify',{apply_ids:$scope.apply.selected,comment:$scope.apply.verifyComment}).success(function(data) {
            $scope.apply.successNum = data.data.successNum;
            $scope.apply.failNum = data.data.failNum;
            $scope.apply.failList = data.data.failList;
             
            $scope.apply.batchResWin = true;//显示批量审核结果框

            $scope.apply.showVieryWin=false;
            $scope.apply.verifyComment = '';
              
            //刷新列表
            service.getMyAgentList($scope);
        });
    }

    //批量审核
    service.refuseBatch = function ($scope) {
        $http.post('/index.php?r=apply/apply/batch-refuse',{apply_ids:$scope.apply.selected,comment:$scope.apply.refuseComment}).success(function(data) {
            $scope.apply.successNum = data.data.successNum;
            $scope.apply.failNum = data.data.failNum;
            $scope.apply.failList = data.data.failList;
            $scope.apply.batchResWin = true;//显示批量审核结果框

            $scope.apply.showRefuseWin=false;
            $scope.apply.refuseComment = '';
            $("#masklayer1").hide();
            //刷新列表
            service.getMyAgentList($scope);
        });
    }

    //通过
    service.verify = function ($scope) {
        $http.post('/index.php?r=apply/apply/verify',JSON.stringify($scope.apply_approve)).success(function(data) {
            if(data.code == 1){
                $("#masklayer1").hide();
                $scope.apply.isLeaveDetailApplyWin = false;//不显示请假申请详情页
                $scope.apply.customShowWin = false;//不显示其他申请详情页
                $scope.apply.overtimeShowWin = false;//不显示加班申请详情页
                $scope.apply.rankDetailPopup = false;//不显示职级申请详情页面
                $scope.apply.drainPunchPopup = false;//不显示忘打卡申请详情页面
                $scope.apply.showRefuseDrainPopup = false;
                $scope.apply.showVieryLastRankPopup = false;
                $scope.apply.showRefuseRankPopup = false;
                $scope.apply.showVieryDrainPopup = false;
                $scope.apply.flexibleWorkVieryPopup = false;//不显示弹性上班详情页面
                $scope.apply.flexibleWorkPopup = false;//不显示弹性上班同意弹窗
                //清空数据
                $scope.apply.score = 0;
                $scope.apply.level_rank = '';
                $scope.apply.comment = '';
                service.getMyAgentList($scope);
                alert(data.msg);
            }else{
                $scope.apply_param.leave_sum = $scope.apply.tempDay;
                alert(data.msg);
            }
        });
    };

    //驳回
    service.refuse = function ($scope) {
        $http.post('/index.php?r=apply/apply/refuse',JSON.stringify($scope.apply_approve)).success(function(data) {
            if(data.code == 1){
                $scope.apply.isLeaveDetailApplyWin = false;//不显示请假申请详情页
                $scope.apply.customShowWin = false;//不显示其他申请详情页
                $scope.apply.overtimeShowWin = false;//不显示加班申请详情页

                $scope.apply.rankDetailPopup = false;//不显示职级申请详情页面
                $scope.apply.drainPunchPopup = false;//不显示忘打卡申请详情页面
                $scope.apply.showRefuseDrainPopup = false;
                $scope.apply.showRefuseRankPopup = false;
                $scope.apply.flexibleWorkPopup = false;//不显示弹性上班详情页面
                $scope.apply.flexibleWorkRefusePopup=false;//不显示弹性上班拒绝弹窗

                service.getMyAgentList($scope);
                alert(data.msg);
                $("#masklayer1").hide();
            }else{
                alert(data.msg);
            }
        });
    }

    //修改model标题
    service.editTitle = function (modelId,title,$scope) {
        $http.post('/index.php?r=apply/apply-model/update-title',{model_id:modelId,title:title}).success(function(data) {
            alert(data.msg);
            if(data.code == 1) {
                service.getAllModel($scope);
            }
        });
    }

    //停用model表单
    service.doStop = function (modelId,status,$scope) {
        $http.post('/index.php?r=apply/apply-model/model-state',{model_id:modelId,status:status}).success(function(data) {
            alert(data.msg);
            if(data.code == 1) {
                service.getAllModel($scope);
            }
        });
    }

    //删除model表单
    service.doDel = function(modelId,$scope) {
        $http.get('/index.php?r=apply/apply-model/model-delete&model_id='+modelId).success(function (data) {
            alert(data.msg);
            if(data.code == 1) {
                service.getAllModel($scope);
            }
        });
    }
    
    //职级申请详情
    service.rankDetail = function($scope,applyId) {
        $http.get('/index.php?r=apply/apply/show-detail&apply_id='+applyId).success(function (data) {
            $scope.apply.rankDetail = data.data;
            $scope.apply.file_root = data.data.file_root;
        });
    };

    //发起申请
    service.createApply = function($scope) {
        $http.post('/index.php?r=apply/apply/create-apply',JSON.stringify($scope.apply_param))
            .success(function(data, status) {
                if(data.code == 1){
                    alert(data.msg);
                    $state.go('main.apply.mine',{apply_id:0,model_id:0});
                }else{
                    alert(data.msg);
                }
            });
    }

    //获取申请详情
    service.applyDetail = function($scope,applyId,action) {
        $http.get('/index.php?r=apply/apply/show-detail&apply_id='+applyId).success(function (data) {
            data.data = util.setImgRand(data.data);
            data.data.verifyRecorders = util.setImgRand(data.data.verifyRecorders);
            $scope.apply.detail = data.data;
            $scope.apply.file_root = $scope.apply.detail.file_root;
            //加班
            if($scope.apply.detail.model_id == 1) {
                //格式化时间
                $scope.apply.detail.data.begin_time = filtersModel.filterDateTime($scope.apply.detail.data.begin_time * 1000);
                $scope.apply.detail.data.end_time = filtersModel.filterDateTime($scope.apply.detail.data.end_time * 1000);
                $scope.apply.detail.data.currentDay = filtersModel.filterTime($scope.apply.detail.data.overtime * 1000);
                $scope.apply.detail.data.isNextDay = $scope.apply.detail.data.is_next_day == 1 ? true: false;//filtersModel.filterDateTime($scope.apply.detail.data.end_time * 1000);
            }
            //忘打卡
            if($scope.apply.detail.model_id==3){
                $scope.apply.beginTime = filtersModel.filterTime($scope.apply.detail.data.check_date*1000);
                if($scope.apply.detail.data.is_am==1){
                    $scope.apply.drainCheckAm = true;
                }else if($scope.apply.detail.data.is_am==2) {
                    $scope.apply.drainCheckPm = true;
                }
                if(action==1){
                    $scope.apply.detail.data.isShowPage = true;
                }
                $scope.apply.drainDesc = $scope.apply.detail.data.note;

            }
            //弹性上班
            if($scope.apply.detail.model_id==4){
                $scope.apply.flexibleWorkDetailList = data.data;
                $scope.apply.beginTime = filtersModel.filterDateTime($scope.apply.detail.data.begin_time * 1000);
                $scope.apply.endTime = filtersModel.filterDateTime($scope.apply.detail.data.end_time * 1000);
                $scope.apply.workTimePoint = $scope.apply.detail.data.flexibleWorkTime;
                $scope.apply.id = $scope.apply.detail.data.store_id;
                $scope.apply.desc = $scope.apply.detail.data.note;
                $scope.apply.files = $scope.apply.detail.data.att;
                service.getFlexibleWorkTimePoint($scope,2); 
                if(action==1){
                    $scope.apply.detail.data.isShowPage = true;
                }
            }
            //职级
            if( $scope.apply.detail.model_id==5){
                if(action==1){
                    $scope.apply.detail.data.isShowPage = true;
                }
                $scope.apply.rankApplyDesc = $scope.apply.detail.data.note;
                $scope.apply.rankFiles = $scope.apply.detail.data.att;
            }

            
            //自定义表单
            if($scope.apply.detail.model_id > 5) {
                //表单还原
                formshowfactory.formshow(JSON.parse(data.data.form_json),$compile,$scope,action);
            }
        });
    };

    //编辑申请详情
    service.applyEdit = function($scope,callBack) {
        $http.post('/index.php?r=apply/apply/edit-apply',JSON.stringify($scope.applyEditParam)).success(function (data) {
            alert(data.msg);
            if(data.code == 1) {
                $("#masklayer1").hide();
                //关闭弹窗
                callBack();
                //刷新数据
                service.getMyApplyList($scope);
            }
        });
    }

    //加班申请最后一步数据读取
    service.getOvertimeClockTime = function($scope){
        $http.get('/index.php?r=apply/apply/overtime&apply_id='+$scope.leave_apply.apply_id).success(function (data) {
            if(data.code == 1) {
                $scope.apply_approve.end_time = data.data.end_time;
                $scope.apply_approve.begin_time = data.data.begin_time;
                $scope.apply_approve.real_hours = data.data.real_hours;
                $scope.apply.isOvertimeLastStepWin = true;
            }else{
                alert(data.msg);
            }
        });
    };
    //弹性上班申请  时间段
    service.getFlexibleWorkTimePoint = function($scope,i) {
        $http.post('/index.php?r=apply/apply/flexible-work').success(function (data) {
            if(i==1){
                $scope.flexibleWork.timePoint = data.data;
            }else if(i==2){
                $scope.apply.timePoint = data.data;
                angular.forEach(data.data, function(val, key){
                    if ($scope.apply.workTimePoint == val.timeOutData){
                        $scope.apply.flexibleWork = val;
                    }
                })
            }
            
        });
    };

    service.closeMark = function($scope){
        $("#masklayer1").hide();
        //是否显示请假申请查看详情页
        $scope.apply.isLeaveDetailApplyWin = false;
    }

    return service;
});
