//控制器动作
 systemMod.controller('actionCtrl', function ($scope,$stateParams, $rootScope, $http, $cookieStore,$cookies,$state,permissionModel) {
     var ctrlAction = $scope.ctrlAction = {};
	 var permission_param = $scope.permission_param = {};
     ctrlAction.action_list = [];
     var permitem = $scope.permitem = {};
 	 var gname = $scope.gname = {};

	 permission_param.page = 1;
	 permission_param.size = 15;

	 //翻页对象
	 $scope.page={
		 curPage : 1,//当前页
		 tempcurPage : 1,//临时当前页
		 sumPage : 0//总页数
	 };

     ctrlAction.closeWindows = false;
     ctrlAction.closeWindow =function(){
             ctrlAction.closeWindows = false
     };
	 permissionModel.getAllCtrlList($scope);


 	//修改
     ctrlAction.edit =function(perm){
		 ctrlAction.closeWindows = true;
 		 $scope.permitem = perm;
 		 var index = 0;
 		 for ( var o in ctrlAction.gname){
 		 	if(perm.parent_id == ctrlAction.gname[o].pid){
 				index = o;
 			}
 		 }
         $scope.ctrlAction.selectgroup = ctrlAction.gname[index];

     };

 	//修改 保存
     ctrlAction.editsave =function(perm){
 		if(typeof perm == 'undefined'){
 			$rootScope.$broadcast('error', '操作错误，请联系管理员'); return;
 		}
 		if(perm.is_contoller_k < 0){
 			//子类
 			if(typeof ctrlAction.selectgroup == 'undefined'){
 				ctrlAction.closeWindows = "closeWindows";
 				return false;
 			}
 			perm.parent_id = ctrlAction.selectgroup.pid;
 		}else{
 			//父类
 			perm.parent_id = 0;
 		}

 		var URL = '';var DATA = '';
 		ctrlAction.closeWindows = "closeWindows";
 		
 		if(typeof perm.pid != 'undefined' && perm.pid > 0){
 			URL = '/index.php?r=permission/ajax-update';
 			DATA = 'parent_id='+perm.parent_id+'&pid='+perm.pid+'&p_name='+perm.p_name+'&p_router='+perm.p_router;
 		}else{
 			URL = '/index.php?r=permission/ajax-add';
 			var pid_other = perm.controller + perm.action +'#'+perm.is_contoller_k +'#'+ perm.is_ajax_k;
 			DATA = 'parent_id='+perm.parent_id+'&pid_other='+pid_other+'&p_name='+perm.p_name+'&p_router='+perm.p_router;
 		}
 		$scope.$broadcast('loadOpt', 'block'); 

 		$http({method:'POST',url:URL,data:DATA,headers:{'Content-Type':'application/x-www-form-urlencoded'}}).success(function(data,status,headers,config){
 				$scope.$broadcast('loadOpt', 'none'); 
 				if(data.code){
 					permissionModel.getAllCtrlList($scope,1);
 				}
 				$rootScope.$broadcast('error', data.msg); return;
 		});
     };

 	//删除
     ctrlAction.delsave =function(perm){
 		if(confirm("你确定要删除【"+perm.p_name+"】？")){
 			$scope.$broadcast('loadOpt', 'block'); 
 			$http({method:'POST',url:'/index.php?r=permission/ajax-del',data:'pid='+perm.pid,
 					headers: {'Content-Type': 'application/x-www-form-urlencoded'}}).success(function(data, status, headers, config){
 				$scope.$broadcast('loadOpt', 'none'); 
 				if(data.code){
 					permissionModel.getAllCtrlList($scope,1);
 				}
 				$rootScope.$broadcast('error', data.msg); return;
 			});
 		}
     };

	 //翻页方法
	 $scope.page_fun = function () {
		 $scope.permission_param.page = $scope.page.tempcurPage;
		 permissionModel.getAllCtrlList($scope);
	 };

 })