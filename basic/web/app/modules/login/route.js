define(function(require) {

    return {
 
        setRoute:function($stateProvider){

            var basePath = 'app/modules/login/view/';

            $stateProvider.state('login', {
                url: '/login',
                module: 'public',  //不需要授权的
                data: {
                    css: ['']
                },
                views: {
                    '': {
                        templateUrl: basePath+'login.html',
                        controller:  function($scope) {
                            require(['loginCtr'],function(t){
                                $scope.$apply(t.init.call($scope,$scope));
                            });
                        }
                    }
                }
            });

        }

    };

});