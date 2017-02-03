NanoCloudMod.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider,$q) {
    var basePath = 'appnews/modules/nanoCloud/view/';
    //纳米云主路由
    $stateProvider.state('main.nanoCloud', {
        url: '/nanoCloud',
        module: 'private',  //需要授权的
        data: {
            css: [
                'css/nanoCloud.css',
            ]
        },
        views: {
            'nanoCloudContent@main': {
                templateUrl: basePath + 'nanoCloud_content.html'
            }
        },
        onEnter:function(){
            document.oncontextmenu = function(){
                return false;
            }
        },
        onExit: function () {
            document.oncontextmenu = function(){
                return true;
            }
        }   
    })

        //主页面
        .state('main.nanoCloud.nanoCloudSection',{
            url:'/nanoCloudSection',
            module:'private',
            views:{
                'nanoCloudSection@main.nanoCloud':{
                    templateUrl: basePath + 'nanoCloudSection.html',
                    controller: 'NanoCloudSectionCtrl'
                }
            }
        })

        //回收站页面
        .state('main.nanoCloud.nanoCloudRecycle',{
            url:'/nanoCloudRecycle',
            module:'private',
            views:{
                'nanoCloudRecycle@main.nanoCloud':{
                    templateUrl: basePath + 'nanoCloudRecycle.html',
                    controller: 'NanoCloudRecycleCtrl'
                }
            }
        })

}]);
