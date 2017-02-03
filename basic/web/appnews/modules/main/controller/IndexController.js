 
 var IndexMod=angular.module('IndexMod',[]);
IndexMod.controller('indexCtr',function($scope,$http,$location,deskModel){


    var index = $scope.index = {};
    var index_param = $scope.index_param = {};
    
    //任务
    index.task = [];

    //我的工作
    index.my_work = [];

    //考勤
    index.attendance = [];

    //项目
    index.project = [];
    //公告
    index.notice = [];
    //我的申请
    index.my_apply = [];
    //申请待审
    index.my_approval = [];
    //调研
    index.survey = [];
    //工作报告待审
    index.workstate_approval = [];
    //积分榜
    index.score_board = [];

    //获取首页模板
    deskModel.getDeskTpl($scope);
    //图表
    index.draw = function(index,allCount,finishCount){
        var canvas = angular.element('.pro-progress canvas');
        var ctx = canvas[index].getContext("2d");
        ctx.beginPath();
        var degree=(finishCount/allCount).toFixed(2);
        ctx.arc(75,75,66,-0.5*Math.PI,degree*(2*Math.PI) - Math.PI/2,false);
        ctx.lineWidth=15.4;
        ctx.lineCap="round";
        ctx.strokeStyle="#fff";
        ctx.stroke();
        ctx.closePath();
    };

    //积分榜
    index_param.type = 1;
    index.scoreBoard = function(type){
        // var e = window.event;
        angular.element('.score-module').find('span').toggleClass('score-active');
        index_param.type = type;
        deskModel.scoreBoard($scope);
    }





    index.tplUrl = function(tplCode){
        return "appnews/modules/common/view/templet/" + tplCode + '.html';
    }
});

 IndexMod.controller('navCtr',function($scope,$location,$cookieStore,$state){
   (function(){


       //console.log($(".leftsbar"));
                var menu  = {};
                //导航顶级菜单点击
                menu.menuTitle   = $(".leftsbar .menus .menu-title"),     //菜单标题
                menu.isMinMenu   = false,                                 //是否启动迷你菜单
                menu.menuUpbtn   = $(".menu-up"),                         //迷你向上滚动按钮
                menu.menuDownbtn = $(".menu-down"),                       //迷你向下滚动按钮
                menu.aclick      = $(".leftsbar ul ul li a"),             //a标签点击
                menu.uln         = 62,                                    //ul li 高度
                menu.lis         = 0,                                     //迷你计数
                menu.lisize      = $(".menu-title").length,               //导航菜单总数
                menu.ulH         = menu.lisize*menu.uln,                  //ul固定高度  
                menu.leftsbara   = $(".leftsbar a"),                      //left下a标签
                menu.logomax     = $(".logomax"),                         //大logo
                menu.logomin     = $(".logomin"),                         //小logo
                menu.leftsbar    = $(".leftsbar"),                        //左边menudiv
                menu.mainsbar    = $(".mainsbar"),                        //中部div
                menu.leftmenus   = $(".leftsbar .menus"),                 //菜单
                menu.menusli     = $(".menus>ul>li");                     //菜单顶级所有li
                
                //click事件
                menu.menubindFun = function(){

                    menu.thisParent = $(this).parent(),
                    menu.finduls = menu.thisParent.find("ul"),
                    menu.addhref = menu.thisParent.find(".addhref"),
                    menu.thisSlings = menu.thisParent.siblings(".menu-select");

                    menu.thisSlings.find("ul").slideUp();
                    menu.thisSlings.removeClass("menu-select");
                    menu.thisParent.siblings("li").find(".addhref").html("&#xe605;");

                    if( !menu.thisParent.parent().find(".menu-select").length ){
                        menu.finduls.slideDown();
                        menu.thisParent.addClass("menu-select");
                        menu.addhref.html("&#xe606;");
                    }else{
                        menu.finduls.slideUp();
                        menu.thisParent.removeClass("menu-select");
                        menu.addhref.html("&#xe605;");
                    }

                };

                //初始化绑定click事件
                menu.menuTitle.click(menu.menubindFun);

                //导航二级菜单点击
                menu.aclick.click(function(){
                    menu.leftsbara.removeClass("liamenu-select");
                    $(this).addClass("liamenu-select");
                });

                //迷你导航状态
                menu.mindhFunc = function(){

                    menu.minlihover = $(".minleftsbar ul:eq(0)>li");

                    menu.minlihover.hover(function(){

                        menu.hoverSlings = $(this).siblings(".menu-select");
                        menu.hoverSlings.find(".addhref").html("&#xe605;");
                        menu.hoverSlings.removeClass("menu-select");
                        $(this).addClass("menu-select").find(".libor").addClass("minMenusshow");
                        $(this).find("ul").stop().delay(350).slideDown(150);

                    },function(){

                        $(this).find(".libor").removeClass("minMenusshow");
                        $(this).find("ul").stop().slideUp(1);

                    });

                };

                //导航切换点击
                $(".switchbtn").click(function(){
                    menu.minleftsbar = $(".minleftsbar");
                    menu.minmenuTitle = $(".minleftsbar ul:eq(0)>li");
                    menu.leftbarul = menu.leftsbar.find("ul ul");
                        
                    if( !menu.minleftsbar.length ){

                        menu.isMinMenu = true;
                        menu.logomax.addClass("logoshowmax");
                        menu.logomin.addClass("logoshowmin");
                        menu.leftsbar.addClass("minleftsbar");
                        menu.mainsbar.addClass("minmainsbar");
                        menu.leftmenus.removeClass("menusscroll");
                        $(this).find("i").html("&#xe60e;");
                        menu.menuTitle.unbind("click");
                        menu.mindhFunc();
                        menu.leftbarul.hide(250,function(){ $(this).addClass("ulupdown"); });
                        menuUlChange();

                    }else{

                        menu.menusli.slideDown(200);
                        menu.lis = 0;
                        menu.isMinMenu = false;
                        menu.leftbarul.hide(1,function(){ $(this).removeClass("ulupdown"); });
                        menu.logomax.removeClass("logoshowmax");
                        menu.logomin.removeClass("logoshowmin");
                        menu.leftsbar.removeClass("minleftsbar");
                        menu.mainsbar.removeClass("minmainsbar");
                        menu.leftmenus.addClass("menusscroll");
                        $(this).find("i").html("&#xe60d;");
                        menu.menuTitle.bind("click",menu.menubindFun);
                        menu.minmenuTitle.unbind("mouseenter").unbind("mouseleave");
                        menu.minleftsbar.find(".menu-select .addhref").html("-");
                        menu.minleftsbar.find(".menu-select ul").slideDown();
                        menu.menuUpbtn.hide();
                        menu.menuDownbtn.hide();

                    }

                });
                
                //动态切换
                function menuUlChange(){
                     
                    menu.ulHs = $(".menus").height(); 

                    if( menu.ulHs < menu.ulH ){
                        menu.menuUpbtn.show();
                        menu.menuDownbtn.show();
                    }else{
                        menu.menuUpbtn.hide();
                        menu.menuDownbtn.hide();
                    }

                }
                
                //迷你模式向上按钮
                $(menu.menuUpbtn).click(function(){ 

                    if( menu.lis < menu.lisize-1 ){
                        menu.menusli.eq(menu.lis).slideUp(200);
                        menu.lis++;
                    }

                });
                
                //迷你模式向下按钮
                $(menu.menuDownbtn).click(function(){

                    if( menu.lis > 0 ){
                        menu.lis--;
                        menu.menusli.eq(menu.lis).slideDown(200);
                    }

                });
                
                //迷你模式时时响应按钮
                
                $(window).resize(function(){
                       
                    if( menu.isMinMenu == true ){
                        menuUlChange(); 
                    }

                    taskupdown();
                });



                //搜索按钮
                $(".searchbor i,.searchbor input").hover(function(){
                    $(this).parent().addClass("show");
                },function(){
                    $(this).parent().removeClass("show");
                }); 

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
               //监听路由变化收展相应导航项
               $scope.$on('$stateChangeSuccess',function(event, toState, toParams, fromState, fromParams){
                   var str='nav'+$location.url().split('/')[1];
                   var strs=$location.url().split('/')[2];
                   var ul=$('.'+str).next();
                   if(str=='navindex'&&$('.'+str).parent().hasClass('menu-select')&&strs!='set'){
                       $('.'+str).find('a').trigger('click');
                   }
                   if(str=='navmsg'){
                       angular.forEach($('.menus>ul>li>ul'),function(data){
                            if($(data).css('display')=='block'){
                                $(data).prev().trigger('click');
                            }
                       })
                   }
                   if(ul.css('display')=='none'||ul.css('display')==undefined){
                       $('.'+str).trigger('click');
                   }
                   if(strs=="progress"){
                       var route=fromState.url.split('/')[1];
                       if(route=='mycreatepro'||route=='myinvoepro'||route=='openpro'){
                           window.localStorage.setItem('route',route);
                       }
                       strs=window.localStorage.getItem('route');
                   }
                   if(strs&&strs.indexOf('mycreatepro')>-1){
                       ul.find('a').removeClass('liamenu-select');
                       ul.find('a:eq(0)').addClass('liamenu-select');
                   }
                   if(strs=='roleList'){
                       strs='employeelist';
                   }
                   if(strs=='gradeSettingList'){
                       strs='skillList';
                   }
                   if(strs=='departmentNanoCoinList'||strs=='personalNanoCoinSet'){
                       strs='personalNanoCoinList';
                   }
                   if(strs=='myRewardTask'||strs=='myClaimRecord'){
                        strs='rewardTask';
                   }
                   ul.find('a').each(function(index,ele){
                       if($(ele).attr('ui-sref').indexOf(strs)>-1){
                           $('div.menus').find('a[class="liamenu-select"]').removeClass('liamenu-select');
                           if(strs!='reserve'){
                               $(ele).addClass('liamenu-select');
                           }else{
                               ul.find('a:eq(0)').addClass('liamenu-select');
                           }
                       }
                   })
               })
       })();



     //var index = $scope.index = {};
});