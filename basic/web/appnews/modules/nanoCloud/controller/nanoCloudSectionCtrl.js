var NanoCloudMod=angular.module('NanoCloudMod',[]);
NanoCloudMod.controller('NanoCloudSectionCtrl',function($scope,$http,$state,$rootScope,$stateParams,$timeout,$filter,nanoCloudModel,$compile,$state,Publicfactory){
	var nanocloud = $scope.nanocloud = {};
	//上传下拉框
	nanocloud.isShowUplodingMenu=false;
	//列表内容头部操作菜单
	nanocloud.isShowOperatorMenu=false;
	//重命名输入框
	nanocloud.isShowRename=false;
	//全选框
	nanocloud.isAllSelected=false;
	//选中项目的数量
	nanocloud.selecteds=0;
	//设置分享弹框
	nanocloud.isShowSetShare=false;
	//设置共享权限弹框
	nanocloud.isShowSetShare=false;
	//查看共享权限弹框
	nanocloud.isShowCheckShare=false;
	//分享链接弹框
	nanocloud.isShowShare=false;
	//操作历史弹框
	nanocloud.isShowOperateLog=false;
	//查看历史版本
	nanocloud.isShowCheckHistoryVer=false;
	//移动文件弹框
	nanocloud.isShowMoveFile=false;
	//删除文件弹框
	nanocloud.isShowDeleteFile=false;
	//取消参与弹框
	nanocloud.isShowCancelJoin=false;
	//正在上传-大弹框
	nanocloud.isShowBigUploading=false;
	//正在上传-小弹框
	nanocloud.isShowSmallUploading=false;
	//重命名之前的文件名
	nanocloud.oldFileName='';
	//主页面列表内容循环数据
	nanocloud.myFiles=[];
	//新建文件夹状态是否激活
	nanocloud.isBiuldNewFile=false;
	//获取数据
	nanoCloudModel.getMyFiles($scope);



	//右键菜单
	nanocloud.indexs =-1;
	$scope.getindex = function($index){
		nanocloud.indexs=$index;
		angular.forEach(nanocloud.myFiles,function(data){
			data.isSelected=false;
		})
		nanocloud.selected();
	};
	$scope.fuzhi = function () {
		if(nanocloud.indexs>=0){
			alert(nanocloud.indexs);
		}else{
			console.log(nanocloud.indexs);
		}
	};




	//上传按钮下拉控制
	nanocloud.dropdown=function(e){
		nanocloud.isShowUplodingMenu=!nanocloud.isShowUplodingMenu;
	}


	//上传文件或文件夹
	nanocloud.upLodingFile=function($event){
		$event.stopPropagation();
		nanocloud.isShowUplodingMenu=!nanocloud.isShowUplodingMenu;
	}


	//复选框
	nanocloud.selected=function(event,index,i){
		if(i==0){
			nanocloud.isAllSelected=!nanocloud.isAllSelected;
			if(nanocloud.isAllSelected){
				angular.forEach(nanocloud.myFiles,function(data){
					data.isSelected=true;
				})
			}else{
				angular.forEach(nanocloud.myFiles,function(data){
					data.isSelected=false;
				})
			}
		}else if(i==1){
			if(angular.element(event.target).parents('span.operate').length==0){
				nanocloud.myFiles[index].isSelected=!nanocloud.myFiles[index].isSelected;
			}
		}else{
			nanocloud.myFiles[nanocloud.indexs].isSelected=true;
		}
		var arr=nanocloud.myFiles.filter(function(data){return data.isSelected==true;});
		nanocloud.selecteds=arr.length;
		nanocloud.isShowOperatorMenu=arr.length>0?true:false;
		nanocloud.isAllSelected=arr.length==nanocloud.myFiles.length?true:false;
	}


	//重命名
	nanocloud.rename=function(event,i){
		var input=null;
		if(i==1){
			input=angular.element('#li'+nanocloud.indexs).find('input.input-rename').show();
			nanocloud.oldFileName=input.val();
			nanocloud.selectText(input);
		}else{
			input=angular.element(event.target).parents('li.list-content').find('input.input-rename').show();
			nanocloud.oldFileName=input.val();
			nanocloud.selectText(input);
			angular.element(event.target).parents('div.dia-more').hide();
		}
	}
	nanocloud.selectText=function(input){
		input.prev().hide();
		input.focus();
		var dot=input.val().split('.');
		if(dot.length>1){
			input[0].setSelectionRange(0,dot[0].length);
		}else{
			input[0].setSelectionRange(0,input.val().length);
		}
	}
	nanocloud.blur=function($event){
		angular.element($event.target).hide().prev().show();
		angular.element($event.target).next().hide();
	}



	//更多弹框
	nanocloud.showDiaMore=function(event){
		angular.element(document).unbind('mousedown');
		angular.element('ul.list div.dia-more').hide();
		angular.element(event.target).next().show();
		angular.element(document).bind('mousedown',function(e){
			if(angular.element(e.target).parents('div.dia-more').length==0){
				angular.element('ul.list div.dia-more').hide();
			}
		})
	}
	nanocloud.hideDiaMore=function(event){
		angular.element(event.currentTarget).find('.dia-more').hide();
	}


	//新建文件夹
	nanocloud.createFile=function(){
		var obj={fileType:'wenjianjia',fileName:'新建文件夹',modifyTime:$filter('date')(new Date(),'yyyy-MM-dd HH:mm'),size:0}//模拟数据添加
		nanocloud.myFiles.unshift(obj);
		$timeout(function(){
			angular.element('#li0').find('input.input-rename').show().focus().prev().hide();
			angular.element('#li0').find('input.input-rename')[0].select();
		})
	}


	//进入回收站
	nanocloud.enterRecycle=function(){
		$state.go('main.nanoCloud.nanoCloudRecycle');
	}


	//回到纳米云主体
	nanocloud.backToNanocloudSection=function(){
		$state.go('main.nanoCloud.nanoCloudSection');
	}
	//删除文件
	nanocloud.deleteFile=function(event){
		var arr= _.remove(nanocloud.myFiles,function(data){
			return data.isSelected==true;
		})
		event&&angular.element(event.target).parents('.right-click').removeClass('open');
	}


	//文件夹目录功能
	nanocloud.fileListToggle=function(event){
		$('.file>div div').removeClass('folderSelected');
		$(event.currentTarget).addClass('folderSelected');
		var b=$(event.currentTarget).find('b:nth-child(1)');
		if(b.text()==='+'){
			b.text('-');
			var id=$(event.currentTarget).attr('id');
			var left=parseInt($(event.currentTarget).css('padding-left'))+20;
			id++;
			$scope['data'+id]=nanocloud.getDatas(id);//获取数据
            //var html=`
            //<ul>
			//	<li ng-repeat="item in data${id}">
			//		<div id="${id}" ng-click="nanocloud.fileListToggle($event)"  class="fileList" style="padding-left: ${left}px">
			//			<b class="addIcon f18">+</b>
			//			<b class="fileIcon"></b>
			//			<span>{{item}}</span>
			//		</div>
			//	</li>
            //</ul>
            //`;
			//console.log(html);
			html='<ul><li ng-repeat="item in data'+id+'"> <div id="'+id+'" ng-click="nanocloud.fileListToggle($event)"  class="fileList" style="padding-left: '+
				left+'px"> <b class="addIcon f18">+</b> <b class="fileIcon"></b> <span>{{item}}</span> </div> </li> </ul>';
			var parent=$(event.currentTarget).parent();
			parent.append($compile(html)($scope));
		}else{
			b.text('+');
			$(event.currentTarget).next().remove();
		}

	}
	nanocloud.getDatas=function(id){//模拟-根据选择项获取数据
		if(id==1){
			return ['项目1a','项目1b'];
		}
		if(id==2){
			return ['项目2a','项目2b'];
		}
		if(id==3){
			return ['项目3a','项目3b'];
		}
		if(id==4){
			return ['项目4a','项目4b'];
		}
	}





	//设置共享权限按钮
	nanocloud.setShare=function(){
		$('#masklayer1').show();
		nanocloud.isShowSetShare=true;
	}
	nanocloud.closeSetShare=function(){
		$('#masklayer1').hide();
		nanocloud.isShowSetShare=false;
	}

	//查看共享权限按钮
	nanocloud.checkShare=function(){
		$('#masklayer1').show();
		nanocloud.isShowCheckShare=true;
	}
	nanocloud.closeCheckShare=function(){
		$('#masklayer1').hide();
		nanocloud.isShowCheckShare=false;
	}

	//下载按钮
	nanocloud.load=function(){

	}

	//获取分享链接按钮
	nanocloud.getShareLink=function(){
		$('#masklayer1').show();
		nanocloud.isShowShare=true;
	}
	nanocloud.closeGetShareLink=function(){
		$('#masklayer1').hide();
		nanocloud.isShowShare=false;
	}

	//取消分享按钮
	nanocloud.cancelShare=function(){

	}

	//显示操作日志按钮
	nanocloud.showOperateLog=function(){
		$('#masklayer1').show();
		nanocloud.isShowOperateLog=true;
	}
	nanocloud.closeOperateLog=function(){
		$('#masklayer1').hide();
		nanocloud.isShowOperateLog=false;
	}

	//查看历史版本
	nanocloud.checkHistoryVer=function(){
		$('#masklayer1').show();
		nanocloud.isShowCheckHistoryVer=true;
	}
	nanocloud.closeCheckHistoryVer=function(){
		$('#masklayer1').hide();
		nanocloud.isShowCheckHistoryVer=false;
	}

	//移动文件按钮
	nanocloud.moveFile=function(){
		$('#masklayer1').show();
		nanocloud.isShowMoveFile=true;
	}
	nanocloud.closeMoveFile=function(){
		$('#masklayer1').hide();
		nanocloud.isShowMoveFile=false;
	}

	//复制文件按钮
	nanocloud.copyFile=function(){
		$('#masklayer1').show();
		nanocloud.isShowMoveFile=true;
	}

	//取消参与按钮
	nanocloud.cancelJoin=function(){
		$('#masklayer1').show();
		nanocloud.isShowCancelJoin=true;
	}
	nanocloud.closeCancelJoin=function(){
		$('#masklayer1').hide();
		nanocloud.isShowCancelJoin=false;
	}

	//删除文件按钮
	nanocloud.deleteFile=function(event){
		$('#masklayer1').show();
		nanocloud.isShowDeleteFile=true;
	}
	nanocloud.closeDeleteFile=function(event){
		$('#masklayer1').hide();
		nanocloud.isShowDeleteFile=false;
	}


})

















