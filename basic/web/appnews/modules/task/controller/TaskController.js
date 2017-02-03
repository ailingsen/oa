var TaskMod=angular.module('TaskMod',[]);

TaskMod.controller('taskCtrl',function($scope,$http,$rootScope,Publicfactory,taskModel,$timeout){
   
               //局部
               var task = $scope.task = {};
              
               //分配人初始化
               task.querymember = '';
               task.querymemberok = '';
               //成员模拟数据
               task.member = [
                                {'name':'a刘新宇'},
                                {'name':'b刘静b'},
                                {'name':'吴京'},
                                {'name':'成龙1'},
                                {'name':'成龙2'},
                                {'name':'杨亮'}
                            ];

               

               
               //1表示指派任务，2表示悬赏任务
               task.taskType = [
                                  {label: '指派任务', nums:1},
                                  {label: '悬赏任务', nums: 2}
                               ];
               //默认是指派任务
               task.taskTypeDefaultSelect = task.taskType[0];


               //开始时间windowselect
               task.datestart = '';
               //结束时间
               task.dateend = '';
               //任务标题
               task.title = '';
               //任务内容
               task.taskDesc='';
               //任务积分
               task.point=1000;

               //
               task.addfiles = [];
               //添加附件
               task.addfilesbtn = function(){
                    task.addfiles = ['232343.png'];
               };



               var taskpoint = $(".taskpoint").val().replace(/(^\s*)/g, ""); 
               var taskpointReg = /^[0-9]*$/;
               task.pointkeyup = function(){
                   console.log($(".taskpoint").val());
                   if (!taskpointReg.test($(".taskpoint").val())) {
                      alert("积分只能是大于0的整整数，最大值为自己的总积分!");
                      $(".taskpoint").val('');
                      return false;
                   }
               };
 


               //保存草稿
               task.caogao = function(){

                    //去除空格
                    task.title = task.title.replace(/(^\s*)/g, ""); 
                    if ( task.title == '' ) {
                         alert('请填写任务标题!');
                         return false;
                    }else{
                         if( Publicfactory.checkEnCnstrlen(task.title) > 100){
                             alert('任务标题长度不能大于50个字');
                             return false;
                         }
                    }
                    //时间判断，结束时间必须大于开始时间
                    
                    //积分输入框监听
                    

                    //当积分被输入时，做积分判断
                    if( taskpoint !='' ){
                        //task.pointkeyup();
                    }
                    



               };

               


               




                //创建任务收缩
                function taskupdown(){

                    var taskhight = parseInt($(".taskcreatewin").css('height'))-107,
                        iclass = $(".taskcreatewin .morebtn.ischange");
                    if( iclass.length ){
                        $(".taskcreatewin .btnbor").css({"top":taskhight+"px","height":"100%"});
                    }else{
                        $(".taskcreatewin .btnbor").css({"top":"395px","height":"100%"});
                    }

                }
                $(".taskcreatewin .morebtn").click(function(){
                    var taskhight = parseInt($(".taskcreatewin").css('height'))-107,
                        iclass = $(".taskcreatewin .morebtn.ischange");

                    if( !iclass.length ){
                        $(this).addClass("ischange").find('i').addClass("rotate180");
                        $(this).find('span').css({'left':'73px'}).html("收起")
                        $(".scrollbor").addClass("scroll");
                        $(".hidebor").stop().slideDown();
                        $(".taskcreatewin .btnbor").css({"top":taskhight+"px","height":"100%"});
                    }else{
                        $(this).removeClass("ischange").find('i').removeClass("rotate180");
                        $(this).find('span').css({'left':'30px'}).html("点击补充更多任务信息");
                        $(".scrollbor").removeClass("scroll");
                        $(".hidebor").stop().slideUp();  
                        $(".taskcreatewin .btnbor").css({"top":"395px","height":"100%"});
                    }
                    
                });

                $(window).resize(function(){
                    taskupdown();
                });

 





             

});


