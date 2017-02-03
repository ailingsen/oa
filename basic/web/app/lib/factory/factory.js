define(['app'],function(oaApp) {
/**
 * Created by yangliang on 2015/11/11.
 */
oaApp.factory('Publicfactory',function(){
    var service={};
    
    //时间区间判断
    service.Timeintervalcomparison = function(startTime,endTime){
        if(endTime && startTime > endTime){
            alert("开始必须小于结束时间-或-结束必须大于开始时间！");
            return false;
        }
    };

    //判断文本框内字符不为空 以及最长不超过20个字符
    //【传入需验证的值,字符长度,提示名称,是否为空】
    service.VerificationInputs = function(str,maxnum,title,isnull){
        maxnum == "" ? maxnum = 20 : maxnum = maxnum;
        var name = title; //传入标题
        str = str.replace(/\s/g,""); //过滤所有空格
        if(str || isnull==false){
            if(service.checkEnCnstrlen(str)==0){
                alert("请输入"+name+"！");
                return false;
            }
        }
        if(service.checkEnCnstrlen(str)>maxnum){
            alert(name+"长度不能超过"+maxnum/2+"个字符！");
            return false;
        }
    };

    //[公共调用方法]中英文，数字字符算法
    service.checkEnCnstrlen = function(str){
        var realLength = 0, len = str.length, charCode = -1;
        for (var i = 0; i < len; i++) {
            charCode = str.charCodeAt(i);
            if (charCode >= 0 && charCode <= 128) realLength += 1;
            else realLength += 2;
        }
        return realLength;
    };

    //数字验证
    service.VerificationInputNumber = function(str,maxnum,title,isnull){
        var name = title; //传入标题
        //str = str.replace(/\s/g,""); //过滤所有空格
        var check = /^[0-9]*[1-9][0-9]*$/;//正整数      
        if(str || isnull==false){
            if(service.checkEnCnstrlen(str)==0){
                alert("请输入"+name+"！");
                return false;
            }
        }
        if(!check.test(str)){
            alert("请输入1-100的正整数！");
            return false;
        }
        if(str>100){
            alert("请输入小于100的正整数！");
            return false;
        }
    };

    return service;
});


oaApp.factory('filtersModel',function($filter){
    var service = {};
     
    //格式化 年 月 日
    service.filterTime = function(datatime){
        var timedata;
        var dateFilter = $filter('date');
        timedata = dateFilter(datatime, 'yyyy-MM-dd');
        return timedata;
    };

    //格式化 时 分
    service.filterTimeHHmm = function(datatime){
        var timedata;
        var dateFilter = $filter('date');
        timedata = dateFilter(datatime, 'HH:mm');
        return timedata;
    };

    //当前时间 年 月 日 时 分
    service.filterNowDateTime = function(){
        var service = {};
        var Today_date = new Date();
        var year_date = Today_date.getFullYear();
        var month_date = Today_date.getMonth()+1;
        var day_date = Today_date.getDate();
        var hour_date = Today_date.getHours();
        var minute_date = Today_date.getMinutes();
        month_date<10? month_date = 0+''+month_date : month_date;
        day_date<10? day_date = 0+''+day_date : day_date;
        hour_date<10? hour_date = 0+''+hour_date : hour_date;
        minute_date<10? minute_date = 0+''+minute_date : minute_date;
        var Today_date_s = year_date+"-"+month_date+"-"+day_date;
        return Today_date_s;
    };

    //计算时间天数
    service.countTimesdays = function(startTime,endTime){
        var s1 = endTime;
        var s2 = startTime;
        s1 = new Date(s1.replace(/-/g, "/"));
        s2 = new Date(s2.replace(/-/g, "/"));
        var days = s1.getTime() - s2.getTime();
        var times = parseInt(days / (1000 * 60 * 60 * 24));
        return times;
    };
    
    return service;
});


// 文件验证
oaApp.factory('validate',function(){
    var service={
        fileSize: function(file, maxSize, callback) {
            if(file.size > maxSize) {
                alert("上传文件超过指定大小！");
                return false;
            }else {
                return true;
            }
        }
    };

    return service;
});


// 文件验证
oaApp.factory('topsearchsize',function(){
    var service={
        adjustment: function() {
            var topsearchbar = $(".applywork .top-search-bar");
            if(topsearchbar.length == 1){
               var z_bor = parseInt(topsearchbar.css("width"));
               var t_bor = parseInt($(".applywork .top-search-bar > div").css("width"));
               var z_left = (z_bor-t_bor)/2;
               topsearchbar.find("div:first").css("left",z_left+"px");
            }
        }
    };

    return service;
});


//提示信息
oaApp.factory('infoModel',function($http,$state){
    var service = {};
    //显示详情
    service.permissionalert = function ($scope,data) {
        if(data.code==-5){
            alert(data.msg);
            $state.go('main.index',{},{reload:true});
            return false;
        }else{
            return true;
        }
    }

    return service;
});




//判断浏览器类型
oaApp.factory('checkBrowsers',function($http,$state){
    var service = {};
 
    service.checkBrowser = function () {
        var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串
        var isOpera = userAgent.indexOf("Opera") > -1;
        if (isOpera) {
            return "Opera"
        } //判断是否Opera浏览器
        if (userAgent.indexOf("Firefox") > -1) {
            return "FF";
        } //判断是否Firefox浏览器
        if (userAgent.indexOf("Chrome") > -1){
           return "Chrome";
        }
        if (userAgent.indexOf("Safari") > -1) {
            return "Safari";
        } //判断是否Safari浏览器
        if (userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1 && !isOpera) {
            return "IE";
        }//判断是否IE浏览器
    }

    return service;
});




//判断平台
oaApp.factory('checkBrowserRedirect',function($http,$state){
    var service = {};
 
    service.checkBrowserRect = function () {
        var sUserAgent = navigator.userAgent.toLowerCase();   
        var bIsIpad = sUserAgent.match(/ipad/i) == "ipad";     
        var bIsIphoneOs = sUserAgent.match(/iphone os/i) == "iphone os";   
        var bIsMidp = sUserAgent.match(/midp/i) == "midp";   
        var bIsUc7 = sUserAgent.match(/rv:1.2.3.4/i) == "rv:1.2.3.4";   
        var bIsUc = sUserAgent.match(/ucweb/i) == "ucweb";   
        var bIsAndroid = sUserAgent.match(/android/i) == "android";   
        var bIsCE = sUserAgent.match(/windows ce/i) == "windows ce";   
        var bIsWM = sUserAgent.match(/windows mobile/i) == "windows mobile";   
        if(bIsIpad || bIsIphoneOs){   
             //ios
             return 1;
        }   
        else if(bIsAndroid){   
             //安卓
             return 2;
        }   
        else{   
             //pc
             return 'PC';
        }   
    }

    return service;
});
 



oaApp.factory('checkModel',function($http){
    var service = {};
    //显示详情
    service.checkInput = function (formName) {

        var patt=/[^\u4e00-\u9fa5a-zA-Z\d]/g;

        formName.replace(/\s/g,"");//去掉空格

        if(formName == 'undefined' || formName==''){
            alert("请输入内容！");
            return false;
        }
        if(formName.length>15){
            alert("不能超过15个字符！");
            return false;
        }
        if(formName.match(patt)){
            alert("内容只能包含中文、字母和数字");
            return false;
        }
        return true;
    }
    service.checkNumber=function(formName,msg){
        var reg_Number = /\D/g;
        formName.replace(/\s/g,"");//去掉空格
        if(formName.match(reg_Number)){
            alert(msg);
            return false;
        }
        return true;
    };
    service.checkMail=function(formName){
        var patt=/[\w!#$%&'*+/=?^_`{|}~-]+(?:\.[\w!#$%&'*+/=?^_`{|}~-]+)*@(?:[\w](?:[\w-]*[\w])?\.)+[\w](?:[\w-]*[\w])?/g;
        if(!formName.match(patt)){
            alert("邮箱格式不正确");
            return false;
        }
        return true;
    }

    return service;
});

oaApp.factory('activeModel',function($http){
    var service = {};
    //显示详情
    service.openTaskDetail = function ($scope) {

        //var detailBox=document.getElementById("detailBox");
        //var listWrap=document.getElementById("listWrap");
        //detailBox.style.transform="translate3d(0,0,0)";
        //listWrap.style.width="56%";
    }
    service.closeTaskDetail=function($scope){
        //var detailBox=document.getElementById("detailBox");
        //var listWrap=document.getElementById("listWrap");
        //detailBox.style.transform="translate3d(750px,0,0)";
        //listWrap.style.width="100%";
    }

    return service;
});



});