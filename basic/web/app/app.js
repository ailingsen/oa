define([
 'angular',
	'angularRouteUi',
  'datetimeBootstrap',
  'datetimes',

  //'datetimeMoment',
	'modules/modules!'
], function(angular, angularRoute,routes) {
	// Declare app level module which depends on views, and components
	var oaApp= angular.module('oaApp', [
    'ui.router',
		'ui.bootstrap.datetimepicker',
    'angularFileUpload',
    'ngFileUpload',

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
   ]).run(function($rootScope,$stateParams, $state,$location) {
	    $rootScope.$on("$stateChangeStart", function(e, toState, toParams, fromState, fromParams) {
        $rootScope.previousState_name = fromState.name;
        $rootScope.previousState_params = fromParams;
        //console.log(toState);
        //console.log(fromState);
      });
    }).config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider,$q) {
		$urlRouterProvider.otherwise('/login'); 
		if(window.routeArr.length>0)
		{
			 for (var index = 0; index < window.routeArr.length; index++) {
				 var obj = window.routeArr[index];
				 obj.setRoute($stateProvider);
			 }
		}
		
	}]);

  return oaApp;

});

