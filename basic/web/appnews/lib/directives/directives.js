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

                    if(attrs.placeholder=="年-月-日 时:分" || attrs.placeholder=="" || attrs.placeholder=="请输入开始时间" || attrs.placeholder=="请输入结束时间"){
                        return dateFilter(value, 'yyyy-MM-dd HH:mm');
                    }else if(attrs.placeholder=="年-月-日 时"){
                        return dateFilter(value, 'yyyy-MM-dd HH');
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


    //日历时间格式化
    oaApp.directive('myformDateFormats',['$filter', function($filter) {
        var dateFilter = $filter('date');
        return {
            require: 'ngModel',
            link: function(scope, elm, attrs, ctrl) {
                //$compile(html)($scope);
                function formatter(value) {

                    if(attrs.placeholder=="年-月-日 时:分"){
                        return dateFilter(value, 'yyyy-MM-dd HH:00');
                    }else if(attrs.placeholder=="年-月-日 时"){
                        return dateFilter(value, 'yyyy-MM-dd HH');
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
                uploadAttrs:'&uploadAttrs',
                att : '=attObject'
            }, // {} = isolate, true = child, false/undefined = no change
            controller: function($scope, $element, $attrs, $transclude) {

                    $scope.uploader = new FileUploader();


                $scope.uploader.onAfterAddingFile = function(fileItem) {

                    //$scope.uploadModal = true;

                    var regexp = /(.rar)|(.zip)|(.apk)|(.ipa)$/g;
                    // console.log(regexp.test(fileItem._file.name));
                    /*console.log(fileItem);
                    if(fileItem._file.type === "" && !regexp.test(fileItem._file.name)){
                        alert("未知文件，已从上传队列中移除。");
                        fileItem.remove();
                    }*/

                    //if (fileItem._file.name.length > 20) {
                    //    alert("文件名过长，请限制在20字以内。");
                    //    fileItem.remove();
                    //    return false;
                    //}
                    
                    // 选择文件后判断大小
                    if(fileItem._file.size>52428800){
                        alert(fileItem._file.name+"大小超过50MB，已从上传队列中移除。");
                        fileItem.remove();
                        return false;
                    }

                    var isUpload = 0;
                    var msg = '总文件大小超过50MB，已从上传队列中移除。';
                    var countSize = 0;
                    // 判断文件是否已经上传
                    /*for(var i=0;i<$scope.uploader.queue.length-1;i++){
                        if(fileItem._file.name==$scope.uploader.queue[i].file.name){
                            alert(fileItem._file.name+"已经上传。");
                            isUpload = 1;
                            //fileItem.remove();
                        }
                    }*/
                    angular.element.each($scope.att, function (key, val) {
                        // if(val.file_name==fileItem._file.name || val.real_name==fileItem._file.name){
                        //     msg = fileItem._file.name+"已经上传。";
                        //     isUpload = 1;
                        //     //fileItem.remove();
                        // }
                        countSize=countSize+parseInt(val.file_size);
                    });
                    countSize=countSize+fileItem._file.size;


                    if(isUpload == 1 || countSize>52428800){
                        alert(msg);
                        fileItem.remove();
                        if(!$scope.uploadModal){
                            $scope.uploadModal=false;//是否显示上传窗口
                        }
                        return false;
                    }else{
                        $scope.uploadModal=false;
                    }

                    $scope.uploader.autoUpload=true;//自动上传
                    
                };

            },
            // require: 'ngModel', // Array = multiple requires, ? = optional, ^ = check parent elements
            restrict: 'AE', // E = Element, A = Attribute, C = Class, M = Comment
            // template: '',
            templateUrl: '/appnews/modules/window/upload_modal.html',
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

                    uploadAttrs(scope.uploader, element);

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


    // 附件上传 [多文件]
    oaApp.directive('uploadModalImages',function(FileUploader){
        // Runs during compile
        return {
            // name: '',
            //priority: 1,
            // terminal: true,
            scope: {
                uploadAttrs:'&uploadAttrs',
                att : '=attObject'
            }, // {} = isolate, true = child, false/undefined = no change
            controller: function($scope, $element, $attrs, $transclude) {

                    $scope.uploader = new FileUploader();


                $scope.uploader.onAfterAddingFile = function(fileItem) {

                    //$scope.uploadModal = true;

                    var regexp = /(.png)|(.PNG)|(.jpg)|(.JPG)|(.gif)|(.jpeg)$/g;
                    // console.log(regexp.test(fileItem._file.name));
                    // console.log(fileItem);
                    if(!regexp.test(fileItem._file.name)){
                        alert("请上传图片文件！");
                        fileItem.remove();
                        return false;
                    }

                    //if (fileItem._file.name.length > 20) {
                    //    alert("文件名过长，请限制在20字以内。");
                    //    fileItem.remove();
                    //    return false;
                    //}
                    
                    // 选择文件后判断大小
                    if(fileItem._file.size>52428800){
                        alert(fileItem._file.name+"大小超过50MB，已从上传队列中移除。");
                        fileItem.remove();
                        return false;
                    }

                    var isUpload = 0;
                    var msg = '总文件大小超过50MB，已从上传队列中移除。';
                    var countSize = 0;
                    // 判断文件是否已经上传
                    /*for(var i=0;i<$scope.uploader.queue.length-1;i++){
                        if(fileItem._file.name==$scope.uploader.queue[i].file.name){
                            alert(fileItem._file.name+"已经上传。");
                            isUpload = 1;
                            //fileItem.remove();
                        }
                    }*/
                    angular.element.each($scope.att, function (key, val) {
                        // if(val.file_name==fileItem._file.name || val.real_name==fileItem._file.name){
                        //     msg = fileItem._file.name+"已经上传。";
                        //     isUpload = 1;
                        //     //fileItem.remove();
                        // }
                        countSize=countSize+parseInt(val.file_size);
                    });
                    countSize=countSize+fileItem._file.size;


                    if(isUpload == 1 || countSize>52428800){
                        alert(msg);
                        fileItem.remove();
                        if(!$scope.uploadModal){
                            $scope.uploadModal=false;//是否显示上传窗口
                        }
                        return false;
                    }else{
                        $scope.uploadModal=false;
                    }

                    $scope.uploader.autoUpload=true;//自动上传
                    
                };

            },
            // require: 'ngModel', // Array = multiple requires, ? = optional, ^ = check parent elements
            restrict: 'AE', // E = Element, A = Attribute, C = Class, M = Comment
            // template: '',
            templateUrl: '/appnews/modules/window/upload_modal_images.html',
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

                    uploadAttrs(scope.uploader, element);

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
                    $scope.uploadModal=false;
                    var regexp = /(.rar)|(.zip)|(.apk)|(.ipa)$/g;
                    // console.log(regexp.test(fileItem._file.name));
                    if (fileItem._file.name.length > 40) {
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
            templateUrl: '/appnews/modules/window/upload_modal_single.html',
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
            templateUrl: '/appnews/views/templates/default/modal/upload_modal_single3.html',
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
    oaApp.directive('isperm',function($compile,$cookies,$cookieStore,$rootScope){
        return function(scope, element, attrs){
            // var user = $cookieStore.get('userInfo');
            //
            // if(user.perm_groupid==1){
            //     return false;
            // }

            //app端绕过检验
            if($cookieStore.get('app') == 1) {
                return true;
            }

            var permStr=attrs.pcode;
            //if($cookieStore.get('userper')!=null||$cookieStore.get('userper')!=''){
                //var permObject=angular.fromJson($cookieStore.get('userper'));
            if(JSON.parse(window.localStorage.userper)!=null||JSON.parse(window.localStorage.userper)!=''){
                var permObject=angular.fromJson(JSON.parse(window.localStorage.userper));
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
                        angular.element(element).addClass('none');
                    }
                }else{
                    angular.element(element).addClass('none');
                }
            }else{
                angular.element(element).addClass('none');
            }
        };
    });
    //主类权限判断
    oaApp.directive('ispremmulti',function($compile,$cookieStore,$state, $rootScope){
        return {
            restrict: "EA",
            scope: true,
            link:function($scope, element, attrs){
                //var permUrls=$cookieStore.get('allper');
                var permUrls=JSON.parse(window.localStorage.allper);
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

   oaApp.directive('paging', function ($timeout) {
       return{
           replace:true,
           scope:{
               page : '=pageObject',
               query : '=clickFunction'
           },
           controller : function ($scope,$element) {
               $scope.createHtml = function () {
                   if($scope.page.sumPage > 1){
                       /*var str='<div class="turnPageWrap"> <div class="turnPageBox">';
                       str+='<a href="javascript:;" class="pageNumBox taped">上一页</a>';
                       str+='<span>'+$scope.page.curPage+'</span>/'+$scope.page.sumPage+'页</span>';
                       str+='<a href="javascript:;" class="pageNumBox taped">下一页</a>';
                       str+='<input type="text" id="jumpNum"> <a href="javascript:;" class="pageNumBox">跳转</a></div> </div>';*/
                       var str='<div class="listWrap"><div class="pagebor of clear"><ul>';
                       if($scope.page.curPage > 1){
                           str+='<li><a href="javascript:void(0);">&lt;上一页</a></li>';
                       }
                       if($scope.page.sumPage > 5){
                           if($scope.page.curPage < 4){
                               for(var i=1; i<=4; i++){
                                   if(i == $scope.page.curPage){
                                       str+='<li class="selected"><a href="javascript:void(0);">'+i+'</a></li>';//当前页
                                   }else{
                                       str+='<li><a href="javascript:void(0);">'+i+'</a></li>';
                                   }
                               }
                               str+='<li class="omit3 nobor poabso">…</li>';
                               str+='<li><a href="javascript:void(0);">'+$scope.page.sumPage+'</a></li>';
                           }else if($scope.page.curPage > $scope.page.sumPage-3){
                               str+='<li><a href="javascript:void(0);">1</a></li>';
                               str+='<li class="omit3 nobor poabso">…</li>';
                               for(var i=parseInt($scope.page.sumPage)-3; i<=parseInt($scope.page.sumPage); i++){
                                   if(i == $scope.page.curPage){
                                       str+='<li class="selected"><a href="javascript:void(0);">'+i+'</a></li>';//当前页
                                   }else{
                                       str+='<li><a href="javascript:void(0);">'+i+'</a></li>';
                                   }
                               }
                           }else{
                               str+='<li><a href="javascript:void(0);">1</a></li>';
                               str+='<li class="omit3 nobor poabso">…</li>';
                               for(var i=parseInt($scope.page.curPage)-1; i<=parseInt($scope.page.curPage)+1; i++){
                                   if(i == $scope.page.curPage){
                                       str+='<li class="selected"><a href="javascript:void(0);">'+i+'</a></li>';//当前页
                                   }else{
                                       str+='<li><a href="javascript:void(0);">'+i+'</a></li>';
                                   }
                               }
                               str+='<li class="omit3 nobor poabso">…</li>';
                               str+='<li><a href="javascript:void(0);">'+$scope.page.sumPage+'</a></li>';
                           }
                       }else{
                           for(var i=1; i<=$scope.page.sumPage; i++){
                               if(i == $scope.page.curPage){
                                   str+='<li class="selected"><a href="javascript:void(0);">'+i+'</a></li>';//当前页
                               }else{
                                   str+='<li><a href="javascript:void(0);">'+i+'</a></li>';
                               }

                           }
                       }
                       if($scope.page.curPage < $scope.page.sumPage){
                           str+='<li><a href="javascript:void(0);">下一页&gt;</a></li>';
                       }
                       str+='<div class="inblock pagesearchbor porela">共<span>'+$scope.page.sumPage+'</span>页，到第 <input type="text" id="jumpNum"/> 页<a class="of button">确定</a><!--<button class="of">确定</button>--></div></ul></div></div>';




                       $element.html(str);
                       $scope.bindEvent();
                   }else{
                       var str='';
                       $element.html(str);
                       $scope.bindEvent();
                   }
               };
               $scope.bindEvent = function () {
                   $element.find('a').on('click', function () {
                       if($scope.page.tempcurPage==$scope.page.curPage) {
                           var text = $(this).html();
                           var is_fun = false;
                           var jumpNum = $("#jumpNum").val();
                           var chk_num = /^\d+$/;
                           var curPage = $scope.page.curPage;
                           if ($.trim(text) == '&lt;上一页') {
                               if (curPage > 1) {
                                   $scope.page.tempcurPage = parseInt(curPage) - 1;
                                   is_fun = true;
                               }
                           } else if ($.trim(text) == '下一页&gt;') {
                               if (curPage < $scope.page.sumPage) {
                                   $scope.page.tempcurPage = parseInt(curPage) + 1;
                                   is_fun = true;
                               }
                           } else if($.trim(text) == '确定'){
                               if (!chk_num.test(jumpNum)) {
                                   $("#jumpNum").focus().select();
                                   alert('请输入正确的页码');
                                   return;
                               }
                               if (jumpNum < 1) {
                                   $("#jumpNum").focus().select();
                                   alert('请输入正确的页码');
                                   return;
                               }
                               if (jumpNum > $scope.page.sumPage) {
                                   $("#jumpNum").focus().select();
                                   alert('请输入正确的页码');
                                   return;
                               }
                               is_fun = true;
                               $scope.page.tempcurPage = parseInt(jumpNum);
                           }else{
                               if($scope.page.curPage==$.trim(text)){
                                   return;
                               }
                               if (!chk_num.test($.trim(text))) {
                                   $("#jumpNum").focus().select();
                                   alert('请输入正确的页码');
                                   return;
                               }
                               if ($.trim(text) < 1) {
                                   $("#jumpNum").focus().select();
                                   alert('请输入正确的页码');
                                   return;
                               }
                               if ($.trim(text) > $scope.page.sumPage) {
                                   $("#jumpNum").focus().select();
                                   alert('请输入正确的页码');
                                   return;
                               }
                               $scope.page.tempcurPage = $.trim(text);
                               is_fun = true;
                           }
                           if (is_fun) {
                               $scope.query();
                               $scope.createHtml();
                           }
                       }
                   });
               }
               $scope.createHtml();
               $scope.$watch('page.curPage', function () {
                   $scope.createHtml();
               })
               $scope.$watch('page.sumPage', function () {
                   $scope.createHtml();
               })
           }
       }
   });
   /**
    * 提示框
    */
   oaApp.directive('notice',function($state){
       return {
           restrict: 'EA',
           templateUrl: '/appnews/modules/window/dialog.html',
           scope : {
               message : "=",
               type : "=",
               gourl : "="
           },
           link: function(scope,element, attrs){
               scope.hideNotice = function(gourl) {
                   scope.message = null;
                   scope.type = null;
                   if (gourl && gourl.url != undefined && gourl.params != undefined && gourl.params2 != undefined) {
                       $state.go(gourl.url,gourl.params, gourl.params2);
                   }
                   scope.gourl = null;
                   $("#masklayer1").hide();
               };

           }
       };
   });

    
