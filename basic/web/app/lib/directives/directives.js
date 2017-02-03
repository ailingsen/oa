define(['app'],function(oaApp) {


    //去除重数组
    Array.prototype.distincts = function(){
         var self = this;
         var _a = this.concat().sort();
         _a.sort(function(a,b){
             if(a == b){
                 var n = self.indexOf(a);
                 self.splice(n,1);
             }
         });
         return self;
    };



    // 判断字符长度
    oaApp.directive('checkLength', function($timeout) {
        return function(scope, element, attrs){
            element.bind("keyup",function(){
                var str=element.val();
                
                if(strlen(str)>16){
                    alert("技能名称不能超过8个字符");
                    return false;
                }

            });

            function strlen(str){
                var realLength = 0, len = str.length, charCode = -1;
                for (var i = 0; i < len; i++) {
                    charCode = str.charCodeAt(i);
                    if (charCode >= 0 && charCode <= 128) realLength += 1;
                    else realLength += 2;
                }
                return realLength;
            } 
        }
    });

    //文本区域字符限制
    oaApp.directive('checkingNums', function($timeout,Publicfactory) {
        return {
            restrict: 'E',
            replace : true,
            require: '?ngModel',
            transclude : true,
            scope: {
                nums : '=checkingMax',
                place : '=checkingPlaceholder',
                id : '=checkingId'
            },

            controller: function($scope) {
                $scope.text={};
                $scope.text.nums = $scope.nums;
                $scope.text.placeholder = $scope.place;
                $scope.text.id = $scope.id;
            },

            template: '<div><form name="myform"><textarea placeholder="{{text.placeholder}}" id="{{text.id}}" ng-model="textarea"  ng-paste="pastes(event)"  ng-keydown="textarea_change(event)" ng-keyup="textarea_change(event)"></textarea>'+
            '<span class="input_tips" ng-class="{error:text.nums-textarealength<0}">{{text.nums-textarealength<0 ? \'您输入的内容已超出\'+(-(text.nums-textarealength)) : \'您还可输入\'+(text.nums-textarealength)}}个字</span></form></div>',

            // template: '<div><form name="myform"><textarea placeholder="{{text.placeholder}}" id="{{text.id}}" ng-model="textarea"  ng-paste="pastes(event)"  ng-keydown="textarea_change(event)" ng-keyup="textarea_change(event)"></textarea>'+
            //'<span class="input_tips" ng-class="{error:text.nums-textarealength<0}">{{text.nums-textarealength<0 ? \'您输入的内容字数已超出最大允许值 \'+(-(text.nums-textarealength))+\'个字\' : \'您还可输入\'+(text.nums-textarealength)+\'个字\' }}</span></form></div>',



            link : function(scope, element, attrs, ngModel) {

                scope.textarealength = 0;
                scope.textarea_change = function textarea_change(event) {
     
                    scope.$evalAsync(function(){
                        ngModel.$setViewValue(angular.element("#"+scope.id).val());
                    });
                    check_texttarea(angular.element("#"+scope.id).val());
                    scope.textarealength = Math.round(Publicfactory.checkEnCnstrlen(angular.element("#"+scope.id).val())/2);

                };  

                scope.pastes = function pastes(event) {
                    $timeout(function(){
                        scope.$apply(function(){
                            scope.textarea_change(event);
                        });
                    });
                };

                function check_texttarea(str){
                    str = str.replace(/\s/g,""); //过滤所有空格
                    if(str){
                       if(Publicfactory.checkEnCnstrlen(str)>scope.nums*2){
                          //alert("内容长度不能超过"+scope.nums+"个字符！");
                          //angular.element("#"+scope.id).val(angular.element("#"+scope.id).val().substring(0,scope.nums*2));
                          //scope.textarealength = scope.nums*2;
                          //return false;
                       }
                    }else{
                       angular.element("#"+scope.id).val("");
                       scope.textarealength = 0;
                    }
                }

                angular.element(".p2pInfoClose, .apprBtn-cancel").bind("click",function() {
                    scope.$apply(function() {

                        if( (scope.nums-scope.textarealength)<0){
                            return false;
                        }else{
                            scope.textarea = '';
                            angular.element("#"+scope.id).val("");
                            scope.textarealength = 0;
                        }
                        
                    });
                });

            }
        };
    });

    //日历时间格式化
    oaApp.directive('myformDateFormat',['$filter', function($filter) {
        var dateFilter = $filter('date');
        return {
            require: 'ngModel',
            link: function(scope, elm, attrs, ctrl) {
                //$compile(html)($scope);
                function formatter(value) {

                    if(attrs.placeholder=="年-月-日 时:分" || attrs.placeholder==""){
                        return dateFilter(value, 'yyyy-MM-dd hh:mm');
                    }else if(attrs.placeholder=="年-月-日"){
                        return dateFilter(value, 'yyyy-MM-dd');
                    }else if(attrs.placeholder=="年-月"){
                        return dateFilter(value, 'yyyy-MM');
                    }else if(!attrs.placeholder){
                        return dateFilter(value, 'yyyy-MM-dd');
                    }else{
                        return dateFilter(value, value);
                    }

                }

                function parser() {
                    return ctrl.$modelValue;
                }

                ctrl.$formatters.push(formatter);
                //ctrl.$parsers.unshift(parser);

            }
        };
    }]);


    //当无数据时，显示提示暂无数据
    oaApp.directive('showNoData', function() {
        return {
             link: function(scope, elm, attrs, ctrl) {
                   elm.html("此类暂无数据");
             }
        };
    });

    // 附件上传 [多文件]
    oaApp.directive('uploadModal',function(FileUploader){
        // Runs during compile
        return {
            // name: '',
            //priority: 1,
            // terminal: true,
            scope: {
                uploadAttrs:'&uploadAttrs'
            }, // {} = isolate, true = child, false/undefined = no change
            controller: function($scope, $element, $attrs, $transclude) {

                $scope.uploader = new FileUploader();


                $scope.uploader.onAfterAddingFile = function(fileItem) {
                    $scope.uploadModal=true;
                    var regexp = /(.rar)|(.zip)|(.apk)|(.ipa)$/g;
                    // console.log(regexp.test(fileItem._file.name));
                    /*console.log(fileItem);
                    if(fileItem._file.type === "" && !regexp.test(fileItem._file.name)){
                        alert("未知文件，已从上传队列中移除。");
                        fileItem.remove();
                    }*/

                    if (fileItem._file.name.length > 20) {
                        alert("文件名过长，请限制在20字以内。");
                        fileItem.remove();
                    }
                    
                    // 选择文件后判断大小
                    if(fileItem._file.size>52428800){
                        alert(fileItem._file.name+"大小超过50MB，已从上传队列中移除。");
                        fileItem.remove();
                    }

                    // 判断文件是否已经上传
                    // for(var i=0;i<$scope.uploader.queue.length-1;i++){
                    //     if(fileItem._file.name==$scope.uploader.queue[i].file.name){
                    //         alert(fileItem._file.name+"已经上传。");
                    //         fileItem.remove();
                    //     }
                    // }

                    $scope.uploader.autoUpload=true;//自动上传
                    
                };

            },
            // require: 'ngModel', // Array = multiple requires, ? = optional, ^ = check parent elements
            restrict: 'AE', // E = Element, A = Attribute, C = Class, M = Comment
            // template: '',
            templateUrl: '/app/views/templates/default/modal/upload_modal.html',
            // replace: true,
            transclude: true,
            // compile: function(scope){
            // },
            link: function(scope, element, attrs) {

                var uploadAttrs=scope.uploadAttrs();

                element.find(".openModal").bind("click",function(event){

                    /*scope.$apply(function(){
                        scope.uploadModal=!scope.uploadModal;
                    });*/

                    uploadAttrs(scope.uploader);

                });

                element.find(".finish").bind("click",function(event){
                    scope.$apply(function(){
                        scope.uploadModal=!scope.uploadModal;
                    });

                    scope.uploader.clearQueue();
                });

            }
        };
    });


    // 附件上传 [单文件]
    oaApp.directive('uploadModalsingle',function(FileUploader){
        // Runs during compile
       // console.log(FileUploader);
        return {
            // name: '',
            //priority: 1,
            // terminal: true,
            scope: {
                uploadAttrs:'&uploadAttrs'
            }, // {} = isolate, true = child, false/undefined = no change
            controller: function($scope, $element, $attrs, $transclude) {

                $scope.uploader = new FileUploader();


                $scope.uploader.onAfterAddingFile = function(fileItem) {
                    $scope.uploadModal=true;
                    var regexp = /(.rar)|(.zip)|(.apk)|(.ipa)$/g;
                    // console.log(regexp.test(fileItem._file.name));
                    if (fileItem._file.name.length > 20) {
                        alert("文件名过长，请限制在20字以内。");
                        fileItem.remove();
                    }

                    /*if(fileItem._file.type === "" && !regexp.test(fileItem._file.name)){
                        alert("未知文件，已从上传队列中移除。");
                        fileItem.remove();
                    }*/
                    // 选择文件后判断大小
                    if(fileItem._file.size>52428800){
                        alert(fileItem._file.name+"大小超过50MB，已从上传队列中移除。");
                        fileItem.remove();
                    }

                    // 判断文件是否已经上传
                    // for(var i=0;i<$scope.uploader.queue.length-1;i++){
                    //     if(fileItem._file.name==$scope.uploader.queue[i].file.name){
                    //         alert(fileItem._file.name+"已经上传。");
                    //         fileItem.remove();
                    //     }
                    // }

                    $scope.uploader.autoUpload=true;//自动上传
                    
                };

            },
            // require: 'ngModel', // Array = multiple requires, ? = optional, ^ = check parent elements
            restrict: 'AE', // E = Element, A = Attribute, C = Class, M = Comment
            // template: '',
            templateUrl: '/app/modules/window/upload_modal_single.html',
            // replace: true,
            transclude: true,
            // compile: function(scope){
            // },
            link: function(scope, element, attrs) {

                var uploadAttrs=scope.uploadAttrs();

                element.find(".openModal").bind("click",function(event){

                    /*scope.$apply(function(){
                        scope.uploadModal=!scope.uploadModal;
                    });*/

                    uploadAttrs(scope.uploader);

                });

                element.find(".finish").bind("click",function(event){
                    scope.$apply(function(){
                        scope.uploadModal=!scope.uploadModal;
                    });

                    scope.uploader.clearQueue();
                });

            }
        };
    });



    // 附件上传 [单文件]
    oaApp.directive('uploadModalsinglebtn',function(FileUploader){
        // Runs during compile
        return {
            // name: '',
            //priority: 1,
            // terminal: true,
            scope: {
                uploadAttrs:'&uploadAttrs'
            }, // {} = isolate, true = child, false/undefined = no change
            controller: function($scope, $element, $attrs, $transclude) {

                $scope.uploader = new FileUploader();


                $scope.uploader.onAfterAddingFile = function(fileItem) {
                    $scope.uploadModal=true;
                    var regexp = /(.rar)|(.zip)|(.apk)|(.ipa)$/g;
                    // console.log(regexp.test(fileItem._file.name));
                    //console.log(fileItem);
                    /*if(fileItem._file.type === "" && !regexp.test(fileItem._file.name)){
                        alert("未知文件，已从上传队列中移除。");
                        fileItem.remove();
                    }*/
                    if (fileItem._file.name.length > 20) {
                        alert("文件名过长，请限制在20字以内。");
                        fileItem.remove();
                    }
                    // 选择文件后判断大小
                    if(fileItem._file.size>52428800){
                        alert(fileItem._file.name+"大小超过50MB，已从上传队列中移除。");
                        fileItem.remove();
                    }

                    // 判断文件是否已经上传
                    for(var i=0;i<$scope.uploader.queue.length-1;i++){
                        if(fileItem._file.name==$scope.uploader.queue[i].file.name){
                            alert(fileItem._file.name+"已经上传。");
                            fileItem.remove();
                        }
                    }

                    $scope.uploader.autoUpload=true;//自动上传
                    
                };

            },
            // require: 'ngModel', // Array = multiple requires, ? = optional, ^ = check parent elements
            restrict: 'AE', // E = Element, A = Attribute, C = Class, M = Comment
            // template: '',
            templateUrl: '/app/views/templates/default/modal/upload_modal_single3.html',
            // replace: true,
            transclude: true,
            // compile: function(scope){
            // },
            link: function(scope, element, attrs) {

                var uploadAttrs=scope.uploadAttrs();

                element.find(".openModal").bind("click",function(event){

                    /*scope.$apply(function(){
                        scope.uploadModal=!scope.uploadModal;
                    });*/

                    uploadAttrs(scope.uploader);

                });

                element.find(".finish").bind("click",function(event){
                    scope.$apply(function(){
                        scope.uploadModal=!scope.uploadModal;
                    });

                    scope.uploader.clearQueue();
                });

            }
        };
    });

    /*预订会议室选中*/
    oaApp.directive('boardroomReserve', function($timeout) {
        return {
            scope:{},
            controller: function($scope, $element, $attrs, $transclude) {
                $scope.chosenArray=[];
                //给以选中添加样式
                $scope.$on('ngRepeatFinished', function (ngRepeatFinishedEvent) {
                    if ($element.find("p").length>=1){
                        $element.find(".reserve_time_bor").addClass("selected");
                    }
                });
            },
            link:function(scope, element, attrs,$index){
                //预订选择
                element.bind("click",function(){

                    if(!element.find(".reserved").length){
                        scope.$apply(function(){
                            scope.chosenArray.push(element.index());
                        });

                        element.toggleClass("chosen");
                        chosenNum=element.parents(".br-reserve-li").find(".chosen").length;
                        if(chosenNum){
                            element.parents(".br-reserve-li").find("button").removeAttr("disabled");
                        }else{
                            element.parents(".br-reserve-li").find("button").attr("disabled",true);
                        }
                    }
                });

                var bodyW = parseInt(document.body.clientWidth)-265;
                $(window).on("resize",function(e) {
                    bodyW = parseInt(document.body.clientWidth)-265;
                });
                // 滑过提示
                element.bind("mouseenter",function(){
                    var Rtip = element.find(".reserved-tip");
                    var thisR = parseInt(element[0].offsetLeft)+550;
                    console.log(bodyW);
                    if( bodyW - thisR<0 && bodyW > 820){
                        Rtip.addClass("reserved-rtip").show();
                    }else{
                        Rtip.removeClass("reserved-rtip").show();
                    }
                });
                element.bind("mouseleave",function(){
                    element.find(".reserved-tip").hide();
                });
                
                //给以选中添加样式
                $timeout(function(){
                    scope.$emit('ngRepeatFinished');
                });
     
            }
        };
    });

    oaApp.directive('reservedTime', function() {
        return function(scope, element, attrs){
            var reservedTime=element.find(".reserved-time-list");
            reservedTime.bind("mouseenter",function(){
                reservedTime.find(".reserved-time-tip").show();
            });
            reservedTime.bind("mouseleave",function(){
                reservedTime.find(".reserved-time-tip").hide();
            });
        }
    });


    // 防重复点击
    oaApp.directive('repeatClick', function($timeout) {
        return function(scope, element, attrs){
            element.bind("click",function(){
                element.addClass("disabled-click");
                $timeout(function(){
                    element.removeClass("disabled-click");
                },6000);
            })
        }
    });
    // 防重复点击
    oaApp.directive('repeatClickmin', function($timeout) {
        return function(scope, element, attrs){
            element.bind("click",function(){
                element.addClass("disabled-click");
                $timeout(function(){
                    element.removeClass("disabled-click");
                },3000);
            })
        }
    });


    //权限判断
    oaApp.directive('isprem',function($compile,$cookies,$cookieStore){
        return function(scope, element, attrs){
            var user = $cookieStore.get('userInfo');
            //var user = JSON.parse(unescape($cookies.userInfo));

            if(user.perm_groupid==1){
                return false;
            }
            var permStr=attrs.pcode;
            if(user.permission!=null||user.permission!=''){
                //console.log(user.member.permission);
                var permObject=angular.fromJson(user.permission);
                if(permStr!=''&&permStr!=undefined){
                    permStr=permStr.toLowerCase();
                    var ishide=true;
                    angular.forEach(permObject,function(v,k){
                        if(v.toLowerCase()==permStr){
                            ishide=false;
                            return false;
                        }
                    });
                    if(ishide){
                        angular.element(element).addClass('hide');
                    }

                }else{
                    angular.element(element).addClass('hide');
                }
            }else{
                angular.element(element).addClass('hide');
            }

        };
    });
    //主类权限判断
    oaApp.directive('ispremmulti',function($compile,$cookieStore,$state){
        return {
            restrict: "EA",
            scope: true,
            link:function($scope, element, attrs){
                var user = $cookieStore.get('userInfo');

                /*
                var permUrls={
                    'SkillSkillList':'main.setting.admin.skillpoint.skilllist',
                    'GroupAllgroup1':'main.setting.admin.skillpoint.pointsmanager' ,
                    'FormEditform':'main.set.createForm',
                    'FormGetAllModel':'main.set.formset',
                    'PermissionCtrlList':'main.permission.ctrlalt',
                    'PermissionGroupAjaxShow':'main.permission',
                    'EmployeeAjaxShow':'main.permission.employee',
                    'PropertyGetallpropertylist':'main.wiki.propertymanager',
                    'WikisearchauditversionGetsearchauditversionlist':'main.wiki.searchauditversion'
                }; */
                var permUrls=user.allper;
                if(typeof(permUrls) == "undefined"){
                    angular.element(element).addClass('hide');
                    return false;
                }
                $scope.tmpperm={};
                $scope.tmpperm.tojump=function(perm){
                    if(typeof(permUrls[perm]) == "undefined"){
                        return false;
                    }
                    return $state.href(permUrls[perm]);
                };
                $scope.tmpperm.currentperm=attrs.currenturl;
                if(user.perm_groupid==1){
                    return false;
                }
                if(attrs.pcodes==''||typeof(attrs.pcodes) =="undefined"){
                    return true;
                }
                if(user.permission==null||user.permission=='')
                {
                    angular.element(element).addClass('hide');
                    return false;
                }



                var perms=attrs.pcodes.split(',');
                var permObject=angular.fromJson(user.permission);
                // console.log(perms);
                var ishide=true;
                angular.forEach(perms,function(ov,ok){
                    angular.forEach(permObject,function(v,k){
                        // console.log(ov+'---'+v);
                        if(v.toLowerCase()==ov.toLowerCase()){
                            ishide=false;
                            $scope.tmpperm.currentperm=v;
                           // console.log($scope.tmpperm.currentperm);
                            return false;
                        }
                    });
                });

                if(ishide){
                    angular.element(element).addClass('hide');
                }
            }
        };
    });


    // 当前用户组织名称
    oaApp.directive('userGroup', function($timeout) {
        return {
            scope: {
                gid:'@orgid'
            },
            //require: '?ngModel',
            restrict: 'AE',
            replace: true,
            transclude: true,
            template: '<em id="dire-org" ng-bind="ug"></em>',
     /*        compile: function(scope){
                 var org_id=scope.gid;
                 console.log(org_id);
             },*/
            controller: function($scope, $element, $attrs,$http) {
                $attrs.$observe('orgid', function(actual_value) {
                    if(actual_value){
                        $http.get('/index.php?r=org/get-all-parent-orgname&org_id='+actual_value).success(function (data, status) {
                            $scope.ug = data.data;
                        });
                    }
                })
            },
            link: function(scope, element, attrs) {
            }
        }
    });




    //文本区域字符限制
    oaApp.directive('spanFilesize', function($timeout) {
        return {
            restrict: 'E',
            replace : true,
            require: '?ngModel',
            transclude : true,
            scope: {
                sizesb : '=sizesId'
            },
            controller: function($scope) {},
            link : function(scope, element, attrs, ngModel) {
     
                //文件单位转换
                function bytesToSizes(bytes) {
                    if (bytes === 0) return '0 B';
                    var k = 1024, // or 1024
                        sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
                        i = Math.floor(Math.log(bytes) / Math.log(k));
                    return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
                }

                var spanfilesize = element.parent().find(".spanfilesize");
                $timeout(function(){
                     var texts = bytesToSizes(spanfilesize.text());
                     element.html(texts);
                },500);

            }
        };
    });

    



});