define(function(require) {
    return {
        setRoute:function($stateProvider){

            var basePath = 'app/modules/common/view/';

            $stateProvider.state('main', {
                //url: '/login',
                module: 'private',  //不需要授权的
                data: {
                    css: ['']
                },
                views: {
                    //主页面
                    '': {
                        templateUrl: 'app/modules/main/view/index.html',
                        controller:  function($scope,$http) {
                           require(['indexCtr'],function(t){
                               $scope.$apply(t.init.call($scope,$scope));
                           });
                        }
                    },
                    //顶部控制区域
                    'header@main': {
                        templateUrl: basePath + 'header.html',
                        controller: ''
                    },
                    //左部菜单区域
                    'nav@main': {
                        templateUrl: basePath + 'nav.html',
                        controller: ''
                    },
                    //中心内容区域
                    'mainContent@main': {
                        templateUrl: basePath + 'main_content.html',
                        controller: ''
                    },
                }
            })
            // 首页状态 板块拖拽
            .state('main.index', {
                url: '/index',
                module: 'private',  //需要授权的
                data: {
                    css: [
                            //'css/default.css'
                    ]
                },
                views: {
                    'indexContent@main': {
                        templateUrl: basePath + 'index-content.html',
                        controller: '' //indexController
                    }
                }
            })
        }
    };
});