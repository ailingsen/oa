//require配置
require.config({
	paths: {
		//jquery插件
		jQuery: 'lib/common/jquery.min',
		//angular框架
		angular: 'lib/angular/angular.min',
		//angular路由
		angularRouteUi:'lib/angular/angular-ui-router',
		//登录
		loginCtr:'modules/login/controller/LoginController',
		//首页主框架
		indexCtr:'modules/main/controller/IndexController',
        //首页交互
		indexjs:'lib/common/index',
		//任务交互
		taskjs:'lib/common/task',
		//任务
		taskCtr:'modules/task/controller/TaskController',
        //路由集合
		configRoute:[
		    //登录
		    'modules/login/route',
		    //主页         
		    'modules/main/route',
		    //任务
		    'modules/task/route'
		],
		//时间控件开始
		datetimeBootstrap: 'lib/angular/bootstrap.min',
		datetimes: 'lib/angular/datetimepicker',
		
		Bootstrapdatetimepicker: 'lib/angular/bootstrap-datetimepicker',
		Bootstrapdatetimepickercn: 'lib/angular/bootstrap-datetimepicker.zh-CN',

		//指令文件
		directives : 'lib/directives/directives',
		//工厂文件
		factory : 'lib/factory/factory',
		//附件上传模块
		angularFileUpload:'lib/angular/angular-file-upload.min',
		ngFileUpload:'lib/angular/ng-file-upload.min'
	}
	,shim: {
		//ng依赖关系，不可删
		'angular' : { 'exports' : 'angular'},
		'datetimeBootstrap':['jQuery'],
		'datetimes':['datetimeBootstrap','jQuery'],
		//'datetimeMoment':['jQuery'],
		'Bootstrapdatetimepicker':['jQuery'],
		'Bootstrapdatetimepickercn':['jQuery','Bootstrapdatetimepicker'],
        
        'angularFileUpload':['angular'],
        'ngFileUpload':['angular','angularFileUpload'],

		'jQuery':{ 'exports' : '$'},
		'angularRouteUi':['angular'],
		//'app':['angularFileUpload','ngFileUpload','angularRouteUi','datetimeBootstrap','datetimes']
	}
});

//引入
require([
	'angular',
	'ngFileUpload',
	'directives',
	'factory'
	], function(angular, app) {

		var $html = angular.element(document.getElementsByTagName('html')[0]);

		angular.element().ready(function() { 
			angular.bootstrap(document, ['oaApp']);
		});

	}
);