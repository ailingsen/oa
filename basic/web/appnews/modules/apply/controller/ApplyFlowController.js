//设置审批流程

ApplyMod.controller('applyFlowCtrl', function($scope, $http, $rootScope, Publicfactory, applyModel, $timeout, $stateParams, $state) {
    $scope.readOnly = ($stateParams.readOnly == 1)
    var model_id = $stateParams.id
    var modeltype = $stateParams.type
    var selectTypes = [{
            label: "无",
            value: "-1"
        },
        // { label: "休假时间", value: "0" },
        // { label: "加班时间", value: "1" }
    ]
    if (model_id == 2) {
        selectTypes = [{
            label: "无",
            value: "-1"
        }, {
            label: "休假时间",
            value: "leave_sum"
        }, ]
    }
    if (model_id == 1) {
        selectTypes = [{
                label: "无",
                value: "-1"
            },
            // { label: "加班时间", value: "1" },
        ]
    }
    $scope.hideFilter = (model_id == 3 || model_id == 4 || model_id == 5 || model_id == 1)
    var selectExprs = [{
        label: "无",
        value: "0"
    }, {
        label: ">",
        value: "1"
    }, {
        label: "<",
        value: "2"
    }, {
        label: ">=",
        value: "3"
    }, {
        label: "<=",
        value: "4"
    }, {
        label: "=",
        value: "5"
    }]
    var limitTypes = [{
        label: "无",
        value: "0"
    }, {
        label: "年",
        value: "4"
    }, {
        label: "月",
        value: "3"
    }, {
        label: "周",
        value: "2"
    }, {
        label: "日",
        value: "1"
    }, ]
    $scope.limitTypes = limitTypes
    $scope.selectTypes = selectTypes
    $scope.selectExprs = selectExprs
    var defaultGroup = {
        selectedDeparts: [],
        selectedMembers: [],
        scopeType: 0,
        conditions: {
            type: $scope.selectTypes[0],
            expression: selectExprs[1],
            value: 0
        },
        flows: [{
            level: 1,
            auditors: []
        }]
    }
    $scope.defaultGroup = defaultGroup
    var _params = {
        model_id: model_id,
        modeltype: modeltype,
        limit_num: 6,
        limit_type: limitTypes[0],
        groups: [defaultGroup]
    }
    $scope.params = _params
    $scope.supervisor = true

    $scope.validateLimitNumber = function(e) {
        console.log("e", e)
        if (/^\d+$/g.test($scope.params.limit_num) === false) return false
    }


    //添加审批组
    $scope.addGroup = function() {
            $scope.params.groups.push({
                selectedDeparts: [],
                selectedMembers: [],
                scopeType: 0,
                conditions: {
                    type: $scope.selectTypes[0],
                    expression: $scope.selectExprs[0],
                    value: 0
                },
                flows: [{
                    level: 1,
                    auditors: []
                }]
            })
        }
        //复制审核组
    $scope.copyGroup = function(index) {
            var copiedGroup = _.cloneDeep($scope.params.groups[index])
            $scope.params.groups.push(copiedGroup)
        }
        //删除审批组

        $scope.deleteGroupTC = false;
        $scope.deleteGroupINDEX = 0;
    $scope.deleteGroup = function(index) {
            $scope.deleteGroupTC = true;
            $("#masklayer2").show();
            $scope.deleteGroupINDEX=index;
          
        }


        $scope.deleteGroupTrue = function(){
            $scope.params.groups.splice($scope.deleteGroupINDEX, 1);
             $scope.deleteGroupTC = false;
            $("#masklayer2").hide();
        }
        $scope.deleteGroupFalse = function(){
           $scope.deleteGroupTC = false;
            $("#masklayer2").hide();
        }
        //删除审批流程
    $scope.deleteFlow = function(index, subIndex) {
            $scope.params.groups[index].flows.splice(subIndex, 1)
        }
        //添加审批流程
    $scope.addFlow = function(index) {
            var level = $scope.params.groups[index].flows.length + 1
            $scope.params.groups[index].flows.push({
                level: level,
                auditors: []
            })
        }
        //添加审批人
    $scope.addAuditor = function(path) {
        if ($scope.memberDialogVisble) return
            // if($scope.selectedMembers.length == 1)return
        $scope.onlyOne = true
        $scope.supervisor = true
        $scope.selectedMembersPath = path
        $scope.selectedMembers = _.get($scope.params, path + '.auditors')
        $scope.memberDialogVisble = true
    }

    //删除审批人
    $scope.deleteAuditor = function(path) {
        //已选人员清空
        _.update($scope.params, path, function(v) {
            v.auditors = []
            return v
        })
    }

    $scope.selectedDepartsPath = ""
    $scope.selectedMembersPath = ""
    $scope.selectedDeparts = []
    $scope.departDialogVisble = false
    $scope.onlyOne = true
        //打开部门选择弹窗
    $scope.openDepartDialog = function(path) {
        if ($scope.departDialogVisble) return
            //已选人员置空
        _.update($scope.params, path, function(v) {
            v.selectedMembers = []
            return v
        })
        $scope.selectedDepartsPath = path
        $scope.selectedDeparts = _.get($scope.params, path + '.selectedDeparts')
        $scope.departDialogVisble = true
    }
    $scope.closeDepartDialog = function() {
        $scope.departDialogVisble = false
    }

    $scope.selectedMembers = []
    $scope.memberDialogVisble = false
        //打开人员选择弹窗
    $scope.openMemberDialog = function(path) {
        if ($scope.memberDialogVisble) return
            //已选部门清空
        _.update($scope.params, path, function(v) {
            v.selectedDeparts = []
            return v
        })
        $scope.onlyOne = false
        $scope.supervisor = false
        $scope.selectedMembersPath = path
        $scope.selectedMembers = _.get($scope.params, path + '.selectedMembers')
        $scope.memberDialogVisble = true
    }
    $scope.closeMemberDialog = function() {
        $scope.memberDialogVisble = false
    }

    //更新对应path下的部门列表
    $scope.$watch("selectedDeparts", function(newValue, oldValue) {
            if (!$scope.selectedDepartsPath) return
            _.update($scope.params, $scope.selectedDepartsPath, function(v) {
                v.selectedDeparts = newValue
                v.scopeType = 1
                return v
            })
        })
        //更新对应path下的人员列表
    $scope.$watch("selectedMembers", function(newValue, oldValue) {
        if (!$scope.selectedMembersPath) return
        _.update($scope.params, $scope.selectedMembersPath, function(v) {
            v.scopeType = 0
            if (v.selectedMembers) {
                v.selectedMembers = newValue
            } else if (v.auditors) {
                v.auditors = newValue
            }
            return v
        })
        _.update($scope.params, "groups[0]", function(v) {
            return v
        })
    })

    $scope.deleteDepart = function(depart, index) {
        $scope.params.groups[index].selectedDeparts = _.reject($scope.params.groups[index].selectedDeparts, {
            value: depart.value
        })
    }
    $scope.deleteMember = function(member, index) {
        $scope.params.groups[index].selectedMembers = _.reject($scope.params.groups[index].selectedMembers, {
            value: member.value
        })
    }
    $scope.handleLimitChange = function(index, type) {
        if (type.value === "-1") {
            $scope.params.groups[index].conditions.expression = $scope.selectExprs[0]
            $scope.params.groups[index].conditions.value = 0
        }
    }

    //读取审核流程
    // if($scope.readOnly){
    $http.get('index.php?r=apply/apply-flow/show-flow&model_id=' + model_id).success(function(ret) {
            if (ret.code === 1) {
                if ($scope.params.modeltype == 0) {
                    selectTypes = ret.data.fieldList.map(function(v) {
                        return {
                            value: v.field,
                            label: v.title
                        }
                    })
                    selectTypes.unshift({
                        label: "无",
                        value: "-1"
                    })
                    $scope.selectTypes = selectTypes
                        // $scope.defaultGroup.conditions.type = selectTypes[0]
                }
                $scope.params.limit_num = Number(ret.data.limit_num)
                // console.log("limitTypes", ret.data)
                $scope.params.limit_type = _.find(limitTypes, {
                    value: ret.data.limit_type
                })
                $scope.params.groups = ret.data.flow.map(function(v, i) {
                    var conditionType = _.find(selectTypes, {
                        value: v.item
                    })
                    var conditionExpr = _.find(selectExprs, {
                        value: v.condition
                    })

                    var selectedMembers = [],
                        selectedDeparts = []
                    if (v.type == 1) {
                        selectedDeparts = v.visiblemanInfo.map(function(depart) {
                            return {
                                label: depart.org_name,
                                value: depart.org_id
                            }
                        })
                    }
                    if (v.type == 0) {
                        selectedMembers = v.visiblemanInfo.map(function(member) {
                            return {
                                value: member.u_id,
                                label: member.real_name,
                                avatar: member.head_img
                            }
                        })
                    }
                    var group = {
                        scopeType: v.type,
                        conditions: {
                            type: conditionType,
                            expression: conditionExpr,
                            value: Number(v.value)
                        },
                        selectedMembers: selectedMembers,
                        selectedDeparts: selectedDeparts,
                    }
                    group.flows = []
                    _.forEach(v.flow, function(v, k) {
                            group.flows.push({
                                level: k,
                                auditors: [{
                                    label: v.real_name,
                                    value: v.u_id,
                                    avatar: v.head_img
                                }]
                            })
                        })
                        // console.log("flows",group.flows)
                    if (group.flows.length === 0) {
                        $scope.addFlow(i)
                    }
                    // console.log('group',group)
                    return group
                })
                if (ret.data.flow.length === 0) {
                    $scope.addGroup()
                }
            }
        })
        // }

    //提交表单
    $scope.submit = function() {
        var reg =/^([1-9]\d*|0)$/;

        if (!reg.test(angular.element(".limitnumber").val())) {
            alert("发起次数限制必须为正整数或0");
            return;
        }

        if (!reg.test(angular.element(".numberday").val())) {
            alert("判断条件必须为正整数或0");
            return;
        }

        var payload = _.pick($scope.params, ["model_id", "modeltype", "limit_num"])
            // console.log($scope.params)
        payload.limit_type = $scope.params.limit_type.value
        if (payload.limit_num > 0 && payload.limit_type == "0") {
            alert("请将限制次数与限制周期填写完整")
            return
        }
        payload.flow = []
        var isValid = true
        $scope.params.groups.forEach(function(group) {
            var _flows = {}
            _.forEach(group.flows, function(v, i) {
                if (v.auditors.length == 0) {
                    alert("请填写审批人")
                    isValid = false
                    return
                }
                _flows[i + 1] = v.auditors[0].value
            })
            if (group.selectedDeparts.length == 0 && group.selectedMembers.length == 0) {
                alert("指定部门或指定人不能为空")
                isValid = false
                return
            }
            var flow = {
                type: group.scopeType,
                visibleman: group.scopeType == 0 ? group.selectedMembers.map(function(v) {
                    return v.value
                }).join(",") : group.selectedDeparts.map(function(v) {
                    return v.value
                }).join(","),
                condition: group.conditions.expression.value,
                item: group.conditions.type.value,
                value: group.conditions.value,
                flow: _flows
            }
            payload.flow.push(flow)
        })
        if (!isValid) {
            return
        }
        $http.post("index.php?r=apply/apply-flow/create-flow", payload).then(function(ret) {
            if (ret.data.code == 1) {
                alert("设置成功")
                $timeout(function() {
                    $state.go("main.apply.manage")
                }, 1000)
            } else {
                alert(ret.data.msg)
            }
        })
    }
    $scope.goback = function() {
        $state.go("main.apply.manage")
    }

    // $scope.keyFilterString = function(e) {
    //     if ((e.keyCode < 48 || e.keyCode > 57) && e.keyCode != 8) {
    //         e.preventDefault();
    //     }
    // }

    // $scope.pasteFilterString = function(e) {
    //     e.preventDefault();
    // }
});

ApplyMod.directive("applyFlow", function($http) {
    return {
        scope: {
            modelid: "=",
            modeltype: "=",
            status: "@"
        },
        replace: true,
        restrict: "E",
        templateUrl: "appnews/modules/apply/view/apply_flow_view.html",
        link: function($scope, element, attrs) {
            var model_id = $scope.modelid
            var modeltype = $scope.modeltype
            var selectTypes = [{
                label: "无",
                value: "-1"
            }, {
                label: "休假时间",
                value: "0"
            }, {
                label: "加班时间",
                value: "1"
            }]
            if (model_id === 2) {
                selectTypes = [{
                    label: "无",
                    value: "-1"
                }, {
                    label: "休假时间",
                    value: "leave_sum"
                }, ]
            }
            if (model_id === 1) {
                selectTypes = [{
                    label: "无",
                    value: "-1"
                }, {
                    label: "加班时间",
                    value: "1"
                }, ]
            }
            $scope.hideFilter = (model_id == 3 || model_id == 4 || model_id == 5)
            var selectExprs = [{
                label: "无",
                value: "0"
            }, {
                label: ">",
                value: "1"
            }, {
                label: "<",
                value: "2"
            }, {
                label: ">=",
                value: "3"
            }, {
                label: "<=",
                value: "4"
            }, {
                label: "=",
                value: "5"
            }]
            var limitTypes = [{
                label: "无",
                value: "0"
            }, {
                label: "年",
                value: "4"
            }, {
                label: "月",
                value: "3"
            }, {
                label: "周",
                value: "2"
            }, {
                label: "日",
                value: "1"
            }, ]
            $scope.limitTypes = limitTypes
            $scope.selectTypes = selectTypes
            $scope.selectExprs = selectExprs
            var defaultGroup = {
                selectedDeparts: [],
                selectedMembers: [],
                scopeType: 0,
                conditions: {
                    type: $scope.selectTypes[0],
                    expression: selectExprs[1],
                    value: 0
                },
                flows: []
            }
            var _params = {
                model_id: model_id,
                modeltype: modeltype,
                limit_num: 6,
                limit_type: limitTypes[0],
                groups: [defaultGroup]
            }
            $scope.params = _params

            $scope.selectedDepartsPath = ""
            $scope.selectedMembersPath = ""
            $scope.selectedDeparts = []
            $scope.departDialogVisble = false

            $scope.selectedMembers = []

            $scope.isFetched = false

            //读取审核流程
            $scope.$watch("status", function(newValue, oldValue) {
                // console.log("modelid",$scope.modelid,$scope.modeltype)
                var selectTypes = [{
                    label: "无",
                    value: "-1"
                }, {
                    label: "休假时间",
                    value: "0"
                }, {
                    label: "加班时间",
                    value: "1"
                }]
                if ($scope.modelid == 2) {
                    selectTypes = [{
                        label: "无",
                        value: "-1"
                    }, {
                        label: "休假时间",
                        value: "leave_sum"
                    }, ]
                }
                if ($scope.modelid == 1) {
                    selectTypes = [{
                        label: "无",
                        value: "-1"
                    }, {
                        label: "加班时间",
                        value: "1"
                    }, ]
                }
                // if ($scope.status != 1) {
                //     return
                // }
                // // console.log('modelid',$scope.modelid,selectTypes)
                // if ($scope.isFetched) {
                //     return
                // }
                // if (model_id !== 0) {
                $http.get('index.php?r=apply/apply-flow/show-flow&model_id=' + $scope.modelid).success(function(ret) {
                        if (ret.code === 1) {
                            // console.log("ret",ret.data)
                            // $scope.isFetched = true
                                // $scope.$apply(function(){
                            if ($scope.modeltype == 0) {
                                selectTypes = ret.data.fieldList.map(function(v) {
                                    return {
                                        value: v.field,
                                        label: v.title
                                    }
                                })
                                selectTypes.unshift({
                                        label: "无",
                                        value: "-1"
                                    })
                                    // $scope.defaultGroup.conditions.type = selectTypes[0]
                            }
                            $scope.selectTypes = selectTypes
                            $scope.params.limit_num = ret.data.limit_num
                            $scope.params.limit_type = _.find(limitTypes, {
                                value: ret.data.limit_type
                            })
                            if (ret.data.flow) {
                                $scope.params.groups = ret.data.flow.map(function(v) {
                                    var conditionType = _.find(selectTypes, {
                                        value: v.item
                                    })
                                    var conditionExpr = _.find(selectExprs, {
                                            value: v.condition
                                        })
                                        // console.log("group", $scope.modelid, group, $scope.selectTypes)
                                    var selectedMembers = [],
                                        selectedDeparts = []
                                    if (v.type == 1) {
                                        selectedDeparts = v.visiblemanInfo.map(function(depart) {
                                            return {
                                                label: depart.org_name,
                                                value: depart.org_id
                                            }
                                        })
                                    }
                                    if (v.type == 0) {
                                        selectedMembers = v.visiblemanInfo.map(function(member) {
                                            return {
                                                value: member.u_id,
                                                label: member.real_name,
                                                avatar: member.head_img
                                            }
                                        })
                                    }
                                    var group = {
                                            scopeType: v.type,
                                            conditions: {
                                                type: conditionType,
                                                expression: conditionExpr,
                                                value: v.value
                                            },
                                            selectedMembers: selectedMembers,
                                            selectedDeparts: selectedDeparts,
                                        }
                                        // console.log($scope.selectTypes,ret)
                                    group.flows = []
                                    _.forEach(v.flow, function(v, k) {
                                            group.flows.push({
                                                level: k,
                                                auditors: [{
                                                    label: v.real_name,
                                                    value: v.u_id,
                                                    avatar: v.head_img
                                                }]
                                            })
                                        })
                                        // console.log('group',group)
                                    return group
                                })
                            }
                        }
                    })
                    // }
            })
        }
    }
})