
	// Declare app level module which depends on views, and components
var oaApp= angular.module('oaApp', [
    'gantt', // angular-gantt
    // 'gantt.sortable',
    // 'gantt.movable',
    'gantt.drawtask',
    'gantt.tooltips',
    // 'gantt.bounds',
    'gantt.progress',
    'gantt.table',
    'gantt.tree',
    'ui.tree',
    'ng.ueditor',
    // 'gantt.groups',
    'gantt.overlap',
    // 'gantt.resizeSensor'
    'highcharts-ng',
    'ui.router',
    'ngCookies',
  	'ui.bootstrap.datetimepicker',
    'angularFileUpload',
    'ngFileUpload',
    'factoryApp',
    'LoginMod',
    'IndexMod',
    'TaskMod',
    'AttendanceMod',
    'NoticeMod',
    'MeetingMod',
    'ProjectMod',
  	'SurveyMod',
  	'workStatementMod',
  	'systemMod',
    'ApplyMod',
    'MsgMod',
   'ColleagueMod',
    'personalInfoMod',
    'NanoCloudMod',
    'ng-context-menu',
    'ApplymobileMod'
    //时间控件模块
    //'ui.bootstrap.datetimepicker',
	]).directive('head', ['$rootScope', '$compile', '$state', '$interpolate',
    function($rootScope, $compile, $state, $interpolate) {
      return {
        restrict: 'E',
        link: function(scope, elem){
          var start = $interpolate.startSymbol(),
              end = $interpolate.endSymbol();
          var html = '<link rel="stylesheet" ng-repeat="(k, css) in routeStyles track by k" ng-href="' + start + 'css' + end + '" >';
          elem.append($compile(html)(scope));

          // Get the parent state
          var $$parentState = function(state) {
            // Check if state has explicit parent OR we try guess parent from its name
            var name = state.parent || (/^(.+)\.[^.]+$/.exec(state.name) || [])[1];
            // If we were able to figure out parent name then get this state
            return name && $state.get(name);
          };

          scope.routeStyles = [];
          $rootScope.$on('$stateChangeSuccess', function (evt, toState) {
            // From current state to the root
            scope.routeStyles = [];
            for(var state = toState; state && state.name !== ''; state=$$parentState(state)) {
              if(state && state.data && state.data.css) {
                if(!Array.isArray(state.data.css)) {
                  state.data.css = [state.data.css];
                }
                angular.forEach(state.data.css, function(css) {
                  if(scope.routeStyles.indexOf(css) === -1) {
                    scope.routeStyles.push(css);
                  }
                });
              }
            }
            scope.routeStyles.reverse();
          });
        }
      };
     }
   ]).run(function($rootScope,$stateParams, $state,$location,$cookieStore) {
	    $rootScope.$on("$stateChangeStart", function(e, toState, toParams, fromState, fromParams) {
            //蒙版默认隐藏
            $("#masklayer1,#masklayer2").hide();
            if(toState.module === 'public' && $location.path().indexOf("/forgetPassword/") > 0 ){
                return true;
            }
            //app端绕过检验
            if($cookieStore.get('app') == 1) {
                return true;
            }
            if (toState.module === 'private' && !(typeof($cookieStore.get('userInfo')) != "undefined")) {
                // If logged out and transitioning to a logged in page:
                e.preventDefault();
                $state.go('login');
            } else if (toState.module === 'public' && (typeof($cookieStore.get('userInfo')) != "undefined")) {
                // If logged in and transitioning to a logged out page:
                e.preventDefault();
                $state.go('main.index');
            }

            //tanghui 路由级别的处理权限
            if(toState.module==='private'){
                var user = $cookieStore.get('userInfo');
                if(user.perm_groupid==1){
                    return true;
                }
                var sname=toState.name;
                //var perms=$cookieStore.get('allper');
                //var permObject=$cookieStore.get('userper');
                //var perms=$rootScope.allper;
                //var permObject=$rootScope.userper;
                var perms = JSON.parse(window.localStorage.allper);
                var permObject = JSON.parse(window.localStorage.userper);
                var ishide=true;
                var currentPerm='';
                angular.forEach(perms,function(ov,ok){
                    if(ov==sname){
                        currentPerm=ok;
                        return false;
                    }
                });
                if(currentPerm!='')
                {
                    angular.forEach(permObject,function(v,k){
                        if(v.toLowerCase()==currentPerm.toLowerCase()){
                            ishide=false;
                            //console.log( scope.tmpperm.currentperm);
                            return false;
                        }
                    });
                    if(ishide){
                        //console.log('no auth');
                        e.preventDefault();
                        $state.go('main.error');
                    }
                }

            }

            return true;

        //$rootScope.previousState_name = fromState.name;
        //$rootScope.previousState_params = fromParams;
      });
    }).config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider,$q,$state) {
	  	$urlRouterProvider.otherwise('/login');
	}]).config(['$httpProvider', function($httpProvider) {//注册Http拦截器
        $httpProvider.interceptors.push('OAInterceptor');
}]);



