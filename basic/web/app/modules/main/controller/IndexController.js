define([
    'indexjs'
], function(indexjs) {
    
     return {

          init:function($scope){
               indexjs.init();
               var index = $scope.index = {};
          }

     };

});