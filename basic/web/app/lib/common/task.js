 /*
    Modelname : task公共交互js
    Copyright : 2016.7
    Build time: 2016.7
    Github    : https://github.com/8543307
    Email     : 8543307@qq.com
    Developer : Yangliang
*/

define([
    'jQuery'
],function($){

    return { 

        init : function(){
               
                //创建任务收缩
                function taskupdown(){

                    var taskhight = parseInt($(".taskcreatewin").css('height'))-107,
                        iclass = $(".taskcreatewin .morebtn.ischange");
                    if( iclass.length ){
                        $(".taskcreatewin .btnbor").css({"top":taskhight+"px","height":"100%"});
                    }else{
                        $(".taskcreatewin .btnbor").css({"top":"335px","height":"100%"});
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
                        $(".taskcreatewin .btnbor").css({"top":"335px","height":"100%"});
                    }
                    
                });

                $(window).resize(function(){
                    taskupdown();
                });


        }

    };
    
});