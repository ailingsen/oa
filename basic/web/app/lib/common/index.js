/*
    Modelname : 公共交互js
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
                    
                });

                //顶部显示个人信息
                $(".infosbor").hover(function(){
                    $(this).find(".infoslist").stop().slideDown(200);
                    $(this).find(".infosbtn i").addClass("rotate");
                },function(){
                    $(this).find(".infoslist").stop().slideUp(200);
                    $(this).find(".infosbtn i").removeClass("rotate");
                });

                //搜索按钮
                $(".searchbor i,.searchbor input").hover(function(){
                    $(this).parent().addClass("show");
                },function(){
                    $(this).parent().removeClass("show");
                }); 

 

       }
    };
    
});