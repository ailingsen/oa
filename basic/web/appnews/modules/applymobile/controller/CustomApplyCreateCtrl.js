/**
 * Created by nielixin on 2016/9/9.
 */
ApplyMod.controller('CustomApplyCreateCtrl',function($scope,$http,$rootScope,$state,$stateParams,Publicfactory,applyModel,applySetModel,formshowfactory){
    //局部
    var apply = $scope.apply = {};
    var model = $scope.model = {};
    var apply_param = $scope.apply_param = {};
    var model_id = $stateParams.model_id;

  
    var group = $scope.group = {};
        $scope.selectedMembers = [];
        $scope.selectedDeparts = [];
        group.memberdialog = false;

         var fields = '',
             currentType = '';
             $scope.att=[];


    $("#masklayer1").show();


        //附件，图片上传 公共
        $scope.addFileBtn = function(Uploader, element) {
            Uploader.url = "/index.php?r=apply/apply/upload";
            var ele = element.parents(".borderbor").find("ul");
            
            var filenamelength = ele.find('.filesize'),
                fileaftersize = 0;
            for(var i = 0 ; i<filenamelength.length; i++){
                fileaftersize = fileaftersize + parseFloat(filenamelength.eq(i).html().replace("KB",''));
            }
            
            fileaftersize = fileaftersize.toFixed(2);
            element.parents(".borderbor").find(".none").html(fileaftersize);

            if (fileaftersize > 51200){
                alert("总文件大小已超过上限50MB!");
                return false;
            }else{

                var elesize = element.parents(".borderbor").find(".none").html();
                Uploader.onCompleteItem = function (fileItem, response, status, headers) {
                    if(response.code==1){
                        
                        response.data.data.file_size = (response.data.data.file_size/1024).toFixed(2);
                        elesize = parseFloat(elesize)+parseFloat(response.data.data.file_size);

                        if (elesize > 51200){
                            alert("总文件大小超过50MB，已从上传队列中移除");
                            return false;
                        }
                        else{
                            var file_root = response.file_root;
                            var filefull_path = file_root+'/'+response.data.data.file_path+'/'+response.data.data.real_name;
                            var li  = '<li class="porela">'+
                                            '<i class="poabso icon-'+response.data.data.file_type+'"></i>'+
                                            '<div class="filename fl omit"><a href="'+filefull_path+'" target="_blank">'+response.data.data.file_name+'</a></div>'+
                                            '<div class="filesize fl omit">'+response.data.data.file_size+'KB</div>'+
                                            '<div class="del fr">删除</div>'+
                                      '</li>';
                            ele.append(li);
                            element.parents(".borderbor").find(".none").html(elesize);
                        }
                        //$scope.att.push(response.data.data);
                    }else if(response.code==0){
                        fileItem.remove();
                        alert(response.msg);
                    }

                }
            };
        };
        
        //添加 部门and人员
       


        
        //删除附件
        $(document).on('click','.borderbor .del',function(){
            
            $(this).parent().remove();
            var id = $(this).parent().find(".filename").attr("data-member_id");

            $scope.selectedMembers.forEach(function(item, index) {
                if (item.value == parseInt(id)) {
                    $scope.selectedMembers.splice(index, 1);
                }
            });
            $scope.selectedDeparts.forEach(function(item, index) {
                if (item.value == parseInt(id)) {
                    $scope.selectedDeparts.splice(index, 1);
                }
            });

        });

        $scope.addgroup = function($event,index){
           $("#masklayer1").show();
           fields = angular.element($event.target).attr("id");
           currentType = index;
           if(index==0){
                group.departDialogVisble=true;  //是否展示
           }else{
                group.memberDialogVisble=true;  //是否展示
           }
        };
        //关闭弹窗添加已选择的人或部门
        $scope.lishtml = function(i,value,label){
            var li  = '<li class="porela">'+
                           '<div class="filename fl omit" data-member_id="'+value+'">'+label+'</div>'+
                           '<div class="del fr">删除</div>'+
                      '</li>';
            return li;
        };

        $scope.cancels = function(index){
            //console.log(1);
            var index = fields;

            group.memberDialogVisble=false;
            $("#masklayer1").hide();

            var lis = $('.borderbor').eq(index).find("ul");

            if( $scope.selectedMembers.length > 0 || $scope.selectedDeparts.length > 0){

                var targetArray = currentType ? $scope.selectedMembers : $scope.selectedDeparts;

                if(lis.find('li').length<=0){
                    for( var i = 0; i<targetArray.length; i++){
                         //console.log(lis.find('li').length);
                         lis.append($scope.lishtml(i,targetArray[i].value,targetArray[i].label));
                    }
                }else{

                    var currentSelectArray = [],
                        selectedArray = [];

                    lis.find("li").each(function(iArray) { 
                         currentSelectArray.push(parseInt(lis.find("li").eq(iArray).find(".filename").attr("data-member_id")));
                    }); 

                    targetArray.forEach(function(item) {
                        selectedArray.push({value:parseInt(item.value), label:item.label});
                    });

                    currentSelectArray = currentSelectArray.sort(function(prev, next) {
                        return next - prev;
                    });

                    selectedArray = selectedArray.sort(function(prev, next) {
                        return next - prev;
                    });

                    selectedArray.forEach(function(item, index) {
                        if (currentSelectArray.indexOf(item.value) == -1) {
                            lis.append($scope.lishtml(undefined, item.value, item.label));
                        }
                    });
               }

            }
        };

        //表单提交保存
        $(".formsave").click(function(){
            if(formshowfactory.formshowsave(model.formshowdata)){
                //拿数据
                var showFormssave=new jQuery.showeditForm();
                var result=showFormssave.saveForm();
                $scope.apply_param = result;
                $scope.apply_param.model_id = model_id;
                applyModel.createApply($scope);
            }

        });

    //自定义表单预览
    applySetModel.showModel($scope,model_id,1);

    //关闭窗口
    apply.cancel = function() {
        $state.go('^');
        $("#masklayer1").hide();
    }
});