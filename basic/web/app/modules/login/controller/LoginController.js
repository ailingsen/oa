define([
    'angular',
], function(angular) {
    return {
        init:function($scope){
            var login=$scope.login={};
            login.testHtml='this is login page';
        }
    };
});