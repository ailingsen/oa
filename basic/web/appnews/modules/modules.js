define(function() {
    return {
   
        load:function (resourceId, req, load) {
         var routearr=this.routeArr;
         window.routeArr=[];
           if(require.s.contexts._.config.paths.configRoute.length>0)
            {
                var routes=require.s.contexts._.config.paths.configRoute;
                for (var index = 0; index < routes.length; index++) {
                    var routeStr = routes[index];
                    req([routeStr],function(r){
                        load(window.routeArr.push(r));
                    });
                }
            }
        }
    }
});