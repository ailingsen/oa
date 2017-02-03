var commonMod = angular.module('commonMod', [])

commonMod.directive("departDialog", function ($timeout, $http) {
    return {
        restrict: "E",
        replace: true,
        transclude: true,
        scope: {
            "selectedDeparts": "=",
            "visble": "=",
            "updateDeparts": "&",
            "single": "@"
        },
        link: function (scope, element, attrs) {
            function fetchDeparts(keyword) {
                $http.post("/index.php?r=attendance/attendance/org-info", {
                    search_org_name: keyword
                }).success(function (ret) {
                    if (ret.code === 1) {
                        scope.departs = ret.data.map(function (v) {
                            var departSelected = false
                            if (_.some(scope.selectedDeparts, { value: v.org_id })) {
                                departSelected = true
                            }
                            return {
                                value: v.org_id, label: v.org_name, selected: departSelected
                            }
                        })
                    }
                })
            }
            scope.keyword = ""
            scope.search = function () {
                $timeout(function () {
                    if (scope.keyword) {
                        fetchDeparts(scope.keyword)
                    } else {
                        fetchDeparts("")
                    }
                }, 500)
            }
            scope.departs = []
            scope.selectedDeparts = scope.selectedDeparts || []
            scope.selectDepart = function (depart, index) {
                depart.selected = !depart.selected
                scope.departs[index] = depart
                if (depart.selected) {
                    scope.selectedDeparts.push(depart)
                }else {
                    scope.selectedDeparts.forEach(function(item, i) {
                        if (item.value == depart.value) {
                            scope.selectedDeparts.splice(i, 1)
                            return
                        }
                    })
                }
                // scope.selectedDeparts = _.unionBy(scope.selectedDeparts,_.filter(scope.departs, { selected: true }),function(v){
                //     return v.value
                // })
            }
            scope.$watch("selectedDeparts", function (newValue, oldValue) {
                _.map(scope.departs, function (v) {
                    var departSelected = false
                    if (_.some(newValue, { value: v.value })) {
                        departSelected = true
                    }
                    v.selected = departSelected
                    return v
                })
                scope.updateDeparts({ departs: newValue })
            })
            scope.submit = function () {
                scope.visble = false
            }

            scope.cancel = function () {
                scope.visble = false;
            }
            fetchDeparts("")
        },
        templateUrl: 'appnews/modules/common/dialog/depart_dialog.html'
    }
})

commonMod.directive("departCombo", function ($http, $timeout) {
    return {
        restrict: "E",
        replace: true,
        templateUrl: 'appnews/modules/common/dialog/depart_combo.html',
        scope: {
            "handleSelect": "&",
            "depart":"="
        },
        link: function (scope, elment, attrs) {
            function fetchDeparts(keyword) {
                $http.post("/index.php?r=attendance/attendance/org-info", {
                    search_org_name: keyword
                }).success(function (ret) {
                    if (ret.code === 1) {
                        scope.departs = ret.data.map(function (v) {
                            var departSelected = false
                            if (_.some(scope.selectedDeparts, { value: v.org_id })) {
                                departSelected = true
                            }
                            return {
                                value: v.org_id, label: v.org_name, selected: departSelected
                            }
                        })
                    }
                })
            }
            scope.keyword = ""
            scope.search = function () {
                $timeout(function () {
                    if (scope.keyword) {
                        fetchDeparts(scope.keyword)
                    } else {
                        fetchDeparts("")
                    }
                }, 500)
            }
            scope.departs = []
            // setTimeout(function(){
            //     scope.selectedDepart = scope.depart || null
            // }, 1000)
            scope.$watch("depart", function(){
                scope.selectedDepart = scope.depart
            })
            // scope.selectedDeparts = scope.selectedDeparts || []
            scope.selectDepart = function (depart, index) {
                _.map(scope.departs, function (v) {
                    v.selected = false
                    return v
                })
                depart.selected = !depart.selected
                scope.departs[index] = depart
                scope.selectedDepart = depart
                scope.handleSelect({ depart: depart })
                $('.visible1').css('display','none');
            }
            scope.toggle = function ($event) {
                var div=$($event.target).parent().next();
                $('.visible2').css('display','none');
                div.toggle();
                scope.keyword='';
                fetchDeparts(scope.keyword);
            }
            fetchDeparts("")
            angular.element(document).bind('click',function(e){
                setTimeout(function(){
                    scope.$apply(function(){
                        if(angular.element(e.target).parents(".depart-combo").length==0){
                            $('.visible1').css('display','none');
                            $('.visible2').css('display','none');
                        }
                    })
                })
            })
            $('.selecttimebor').bind('click',function(e){
                setTimeout(function(){
                    scope.$apply(function(){
                        $('.visible1').css('display','none');
                        $('.visible2').css('display','none');
                    })
                })
            })
        }
    }
})

commonMod.directive("memberDialog", function ($http, $timeout) {
    return {
        restrict: "E",
        replace: true,
        scope: {
            selectedMembers: "=",
            visble: "=",
            single: "=",
            updateMembers: "&",
            cancelAttr: "&cancelAttr",
            supervisor: "="
        },
        link: function (scope, element, attrs) {
            var selectedFn = null;
            scope.keyword = ""
            scope.search = function () {
                $timeout(function () {
                    if (scope.keyword) {
                        $http.post("index.php?r=attendance/attendance/member-info", { search_real_name: scope.keyword })
                            .success(function (ret) {
                                if (ret.code === 1) {
                                    //ret.data.unshift({head_img:'/static/head-img/defaultHead.png',real_name:'全部',u_id:0});
                                    scope.members = ret.data.map(function (v) {
                                        return { value: v.u_id, label: v.real_name, avatar: v.head_img }
                                    })
                                    if (scope.supervisor) {
                                        scope.members.unshift({
                                            value: "-1", label: "直属上级", avatar: "/static/head-img/defaultHead.png"
                                        })
                                    }
                                    _.map(scope.members, function (member) {
                                        if (_.some(scope.selectedMembers, { value: member.value })) {
                                            member.selected = true
                                        }
                                        return member
                                    })
                                }
                            })
                    }
                }, 500)
            }
            
            scope.selectedMembers = scope.selectedMembers || []
            _.map(scope.members, function (member) {
                if (_.some(scope.selectedMembers, { value: member.value })) {
                    member.selected = true
                }
                return member
            })

            scope.$watch("visble", function (newValue, oldValue) {
                // console.log("visble",newValue)
                if (newValue) {
                    scope.members = scope.supervisor ? [{
                        value: "-1", label: "直属上级", avatar: "/static/head-img/defaultHead.png"
                    }] : []
                }
            })

            scope.selectMember = function (member, index) {
                if (scope.single && _.some(scope.members, { selected: true })) {
                    return
                }
                if (member.selected) return
                member.selected = true
                scope.members[index] = member
                if (!scope.supervisor) {
                    scope.selectedMembers.push(member);
                }else {
                    scope.selectedMembers = _.filter(scope.members, { selected: true });
                }
            }

            var cancelAttr = scope.cancelAttr();

            scope.cancel = function () {
                if (_.isFunction(cancelAttr)) {
                    cancelAttr();
                }
                scope.members = [{
                    value: "-1", label: "直属上级", avatar: "/static/head-img/defaultHead.png"
                }]
                scope.keyword = "";
                scope.visble = false
            }
        },
        controller: function ($scope) {
            $scope.$watch("selectedMembers", function (newValue, oldValue) {
                _.map($scope.members, function (member) {
                    if (_.some(newValue, { value: member.value })) {
                        member.selected = true
                    } else {
                        member.selected = false
                    }
                    return member
                })
                $scope.updateMembers({ members: newValue })
            })
        },
        template: '<div class="memPartPop pfixed"><div class="memPartPop-header"><span>人员选择</span><i class="iconMemPart" ng-click="cancel()">&#xe603;</i></div><div class="memPartPop-search"><i class="iconMemPart">&#xe612;</i><input type="text" ng-model="keyword" placeholder="搜索" ng-change="search()"/></div><div class="memPartPop-data mem-data"><ul><li ng-repeat="item in members"><img ng-src="{{item.avatar}}" alt="" class="memHead-img"/><span>{{item.label}}</span><span class="fr addPrin-btn" ng-click="selectMember(item,$index)">{{item.selected?"已添加":"添加"}}</span></li></ul></div></div>'
    }
})


commonMod.directive("departDialogForm", function ($timeout, $http) {
    return {
        restrict: "E",
        replace: true,
        transclude: true,
        scope: {
            "selectedDeparts": "=",
            "visble": "=",
            "updateDeparts": "&",
            'cancelAttr': "&cancelAttr"
        },
        link: function (scope, element, attrs) {
            function fetchDeparts(keyword) {
                $http.post("/index.php?r=attendance/attendance/org-info", {
                    search_org_name: keyword
                }).success(function (ret) {
                    if (ret.code === 1) {
                        scope.departs = ret.data.map(function (v) {
                            return {
                                value: v.org_id, label: v.org_name
                            }
                        })
                    }
                })
            }
            scope.keyword = ""
            scope.search = function () {
                $timeout(function () {
                    if (scope.keyword) {
                        fetchDeparts(scope.keyword)
                    }
                }, 700);
            }
            fetchDeparts("")
            scope.departs = []
            scope.selectedDeparts = scope.selectedDeparts || []
            scope.selectDepart = function (depart) {
                if (scope.selectedDeparts.indexOf(depart) == -1) {
                    scope.selectedDeparts.push(depart);
                }
            }
            scope.$watch("selectedDeparts", function (newValue, oldValue) {
                scope.updateDeparts({ departs: newValue })
            })
            scope.submit = function () {
                scope.visble = false
            }

            var cancelAttr = scope.cancelAttr();
            scope.cancel = function () {
                scope.visble = false;
                scope.keyword = "";
                if(!$("#masklayer2").is(":hidden")){
                    $("#masklayer2").hide();
                }else{
                    $("#masklayer1").hide();
                }
                
                scope.selectedDeparts = [];
            }
            scope.submit = function () {
                scope.visble = false;
                cancelAttr();
                scope.selectedDeparts = [];
                scope.departs = [];
                scope.keyword = "";
            }
            scope.close = function () {
                scope.visble = false;
                scope.keyword = "";
                if(!$("#masklayer2").is(":hidden")){
                    $("#masklayer2").hide();
                }else{
                    $("#masklayer1").hide();
                }
                scope.selectedDeparts = [];
            }
        },
        template: '<div class="memPartPop pfixed apply-member-pop"><div class="memPartPop-header"><span>部门选择</span><i class="iconMemPart" ng-click="cancel()">&#xe603;</i></div><div ng-transclude></div><div class="memPartPop-search"><i class="iconMemPart">&#xe612;</i><input type="text" placeholder="搜索" ng-model="keyword" ng-change="search()"/></div><div class="memPartPop-data"><ul><li ng-repeat="item in departs"><input type="checkbox" name="part" ng-click="selectDepart(item)" ng-checked="selectedDeparts[0].value==item.value" class="chbStyle"/><span>{{item.label}}</span></li></ul></div><div class="memPartPop-bottom"><button class="btns blue" ng-click="submit()">确定</button><button class="btns gray ml20" ng-click="close()">取消</button></div></div>'
    }
})

commonMod.directive("memberCombo", function ($http, $timeout) {
    return {
        restrict: "E",
        replace: true,
        scope: {
            handleSelect: "&",
            member:"=",
            depart:"="
        },
        link: function (scope, element, attrs) {
            function fetchMembers(keyword) {
                $http.post("index.php?r=attendance/attendance/member-info", { search_real_name: scope.keyword ,search_org_id:scope.depart})
                    .success(function (ret) {
                        if (ret.code === 1) {
                            ret.data.unshift({head_img:'/static/head-img/defaultHead.png',real_name:'全部',u_id:0});
                            scope.members = ret.data.map(function (v) {
                                return { value: v.u_id, label: v.real_name, avatar: v.head_img, selected: false }
                            })
                            if (scope.supervisor) {
                                scope.members.unshift({
                                    value: "-1", label: "直属上级", avatar: "/static/head-img/defaultHead.png"
                                })
                            }
                        }
                    })
            }
            scope.$watch("depart",function(newValue,oldValue){
                if(newValue !== undefined && newValue !== oldValue){
                    fetchMembers("",newValue)
                    scope.selectedMember = null
                }
            })
            scope.keyword = ""
            scope.search = function () {
                $timeout(function () {
                    if (scope.keyword) {
                        fetchMembers(scope.keyword,"")
                    } else {
                        fetchMembers("","")
                    }
                }, 700);
            }
            scope.members = []
            scope.visble = false
            scope.selectedMember = scope.member || null
            scope.selectMember = function (member, index) {
                _.map(scope.members, function (v) {
                    v.selected = false
                    return v
                })
                member.selected = true
                scope.members[index] = member
                scope.selectedMember = member
                scope.handleSelect({ member: member })
                $('.visible2').css('display','none');
            }
            scope.toggle = function ($event) {
                var div=$($event.target).parent().next();
                $('.visible1').css('display','none');
                div.toggle();
                scope.keyword=''
                fetchMembers(scope.keyword)
            }
            fetchMembers("","")
        },
        templateUrl: "appnews/modules/common/dialog/member_combo.html"
    }
})

commonMod.directive("cascadeMember",function(){
    return {
        restrict:"E",
        replace:true,
        scope:{
            handleDepartSelect:"&",
            handleMemberSelect:"&",
            // member:"=",
            // depart:"="
        },
        templateUrl:"appnews/modules/common/dialog/cascade_combo.html",
        link:function(scope,element,attrs){
            scope.depart = scope.depart || {}
            scope.member = scope.member || {}
            scope.selectMember = function(member){
                scope.handleMemberSelect({member:member})
            }
            scope.selectDepart = function(depart){
                scope.departId = depart.value
                scope.handleDepartSelect({depart:depart})
            }
        }
    }
})




