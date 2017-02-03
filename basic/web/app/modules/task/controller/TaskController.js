define([
    'taskjs',
    'Bootstrapdatetimepickercn',
    'modules/task/models/TaskModel'
], function(taskjs) {
     return {

          init:function($scope,$http,$rootScope,Publicfactory){

               taskjs.init();
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


               //开始时间
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

               //点击选中分配人
               task.memberChange = function($event,index){
                   $(".menbersearchbor").stop().slideUp();
                   var thisid = $(".menbersearchbor ul li");
                   var name = thisid.eq(index).find(".name").text();
                   task.querymemberok = name;
                   task.querymember = '';
               };
               //点击删除分配人
               task.delmember = function(){
                   task.querymemberok = '';
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

               


               $(".fpgbtn").hover(function(){
                   $(".menbersearchbor input").val('');
                   task.querymember = '';
                   $(this).find(".menbersearchbor").stop().slideDown(200);
               },function(){
                   $(this).find(".menbersearchbor").stop().slideUp(200);
                   $(".menbersearchbor input").val('');
                   task.querymember = '';
               });





               //=-====================================================================
               //单层遮罩
               var masklayer1 = $("#masklayer1");
               //多层遮罩
               var masklayer2 = $("#masklayer2");
               //内容层
               var mainsbar = $(".mainsbar");
               //创建任务层
               var taskcreatewin = $(".taskcreatewin");
               //公共动态改变内容层
               task.mainsbaris = function(){
                   if( !$(".mainzindex").length ){
                       mainsbar.addClass("mainzindex");
                   }else{
                       mainsbar.removeClass("mainzindex");
                   }
               };
               // taskrs.showCreateTaskWin = function () {
               //     taskrs.mainsbaris();
               //     masklayer1.show();
               //     taskcreatewin.show();
               // };=======================================================================

                 
          }

     };
});