/**
 * Created by yangliang on 2015/11/11.
 */
var factoryApp= angular.module('factoryApp',[]);

//表单还原展示，可提交数据 开始
factoryApp.factory('formshowfactory',function(Publicfactory){
    var service={};

    service.formshow = function(results,$compile,$scope,status){
        $(".formpresentation .formtitles").html(results.title);
        if(status == 1) {
            $("#formpresentation_edit .scrollbor:eq(0)").html("");
        }else {
            $("#formpresentation .scrollbor:eq(0)").html("");
        }
        var showForms=new jQuery.showeditForm();

        function selectTimes(str){
            if(str=="年-月-日 时:分"){
               str = "yyyy-mm-dd hh:ii";
            }else if(str=="年-月-日 时"){
               str = "yyyy-mm-dd hh";
            }else if(str=="年-月-日"){
               str = "yyyy-mm-dd";
            }else if(str=="年-月"){
               str = "yyyy-mm";
            }
            return str;
        }

        function selectTimes_format(str){
            if(str=="年-月-日 时:分"){
                str = 0;
            }else if(str=="年-月-日 时"){
                str = 1;
            }else if(str=="年-月-日"){
                str = 2;
            }else if(str=="年-月"){
                str = 3;
            }
            return str;
        }


        $.each(results.field, function (i, item) {

            var htmls = showForms.formWidget(results.field[i].formtype);
            var fieldlast = '';
            //$(".formpresentation .scrollbor:eq(0)").append(htmls);
            if(status == 1) {
                $("#formpresentation_edit .scrollbor:eq(0)").append(htmls);
                fieldlast = $("#formpresentation_edit .scrollbor:eq(0) .borderbor:last");
            }else {
                $("#formpresentation .scrollbor:eq(0)").append(htmls);
                fieldlast = $("#formpresentation .scrollbor:eq(0) .borderbor:last");
            }

            //左侧标题，是否必填，描述
            var leftTitle = results.field[i].title;
            var description = results.field[i].describe;
            var required = results.field[i].required;
            fieldlast.find(".widget-title").text(leftTitle);
            if(description){
                fieldlast.find(".field-description").removeClass("hide").text(description);
            }
            if(required==1){
                fieldlast.find(".widget-required").addClass('block');
            }

            //所有文本框
            if (results.field[i].formtype == "Mobile" ||
                results.field[i].formtype == "Phone" ||
                results.field[i].formtype == "NumberComponent" ||
                results.field[i].formtype == "Email"){
                if(results.field[i].inputvalue){
                    fieldlast.find("input").val(results.field[i].inputvalue);
                }
            }

            //普通文本框
            if (results.field[i].formtype == "Text"){
                if( status == 1 ){
                    if(results.field[i].inputvalue){
                        fieldlast.find("input").val(results.field[i].inputvalue);
                    }
                }else{
                    if(results.field[i].inputvalue){
                        fieldlast.find("input").remove()
                        fieldlast.find('.rcontent').prepend('<span class="readtext">'+results.field[i].inputvalue+'</span>');
                    }
                }
            }
            
            //复选框
            if(results.field[i].formtype == "CheckBox"){
                fieldlast.find(".rcontent ul").html("");
                var checkboxNum=Object.keys(results.field[i].setting.checkbox).length;
                var checkboxMax=results.field[i].setting.checkedMax;
                fieldlast.find("input[name=checkedMax]").val(checkboxMax);

                for(var c=0;c<checkboxNum;c++){
                    var checkbox_title = results.field[i].setting.checkbox[c].itemTitle;
                    fieldlast.find(".rcontent ul").append('<li><label class="CheckBox_js"><input name="CheckBoxView'+i+'" type="checkbox"><span>'+checkbox_title+'</span></label></li>');
                    if(results.field[i].setting.checkbox[c].itemData){
                       fieldlast.find(".CheckBox_js:eq("+c+")").find("input").attr("checked",true);
                    }
                }
                
                fieldlast.find(".rcontent ul input").click(function(){
                    var checkedNum=showForms.checkedMax(checkboxMax,checkboxNum,fieldlast);
                    if(checkedNum>checkboxMax){
                        alert("您最多只能勾选"+checkboxMax+"个");
                        $(this).attr("checked",false);
                    }
                });
            }
            
            //单选按钮
            if(results.field[i].formtype == "RadioBox"){
                fieldlast.find(".rcontent ul").html("");
                var radioNum = Object.keys(results.field[i].setting.radio).length;
                for(var o=0;o<radioNum;o++){
                    var radio_title = results.field[i].setting.radio[o].itemTitle;
                    fieldlast.find(".rcontent ul").append('<li><label class="radio-inline"><input name="radioView'+i+'" type="radio"><span>'+radio_title+'</span></label></li>');
                    if(results.field[i].setting.radio[o].itemData){
                       fieldlast.find(".radio-inline:eq("+o+")").find("input").attr("checked",true);
                    }
                }
            }

            //下拉列表
            if(results.field[i].formtype == "Select"){
                fieldlast.find(".choicelist").html("");
                var SelectNum = Object.keys(results.field[i].setting.select).length;
                for(var s=0;s<SelectNum;s++){
                    var Select_title = results.field[i].setting.select[s].itemTitle;
                    fieldlast.find(".choicelist").append('<option class="Select_js" value="">'+Select_title+'</option>');
                    if(results.field[i].setting.select[s].itemData){
                       fieldlast.find(".Select_js:eq("+s+")").attr("selected",true);
                    }
                }
                if( status == 0 ){
                    //fieldlast.find(".choicelist").attr({"disabled":"disabled"});
                }
            }
            
            //分割线
            if(results.field[i].formtype == "Paragraph"){
                var Paragraph_style = results.field[i].styleType;
                fieldlast.find(".alert").html(results.field[i].content);
                fieldlast.find(".alert").removeClass().addClass("alert "+Paragraph_style);
            }

            //金额
            if(results.field[i].formtype == "Money"){
                fieldlast.find(".money-type").text(results.field[i].moneyType);
                if(results.field[i].inputvalue){
                    fieldlast.find("input").val(results.field[i].inputvalue);
                }
            }
            
            setTimeout(function(){
                //日期
                if (results.field[i].formtype == "DateComponent"){
                    fieldlast.find("input").val('');
                    if( status == 1 ){
                        fieldlast.find("input").eq(0).attr({"placeholder":results.field[i].dateType});
                        fieldlast.find("input").datetimepicker({
                            format: selectTimes(results.field[i].dateType),
                            autoclose: true,
                            todayBtn: true,
                            startView: selectTimes_format(results.field[i].dateType),
                            minView: selectTimes_format(results.field[i].dateType),
                            pickerPosition: "bottom-right"
                        });
                    }
                    if(results.field[i].inputvalue){
                        fieldlast.find("input").val(results.field[i].inputvalue);
                    }
                }
                
                //日期区间
                if (results.field[i].formtype == "DateInterval") {
                    fieldlast.find("input").attr({"placeholder":results.field[i].dateType});
                    if( status == 1 ){

                        if( fieldlast.find("input") ){
                            fieldlast.find("input").eq(0).datetimepicker({
                                format: selectTimes(results.field[i].dateType),
                                autoclose: true,
                                todayBtn: false,
                                startView: selectTimes_format(results.field[i].dateType),
                                minView: selectTimes_format(results.field[i].dateType),
                                pickerPosition: "bottom-right"
                            }).on('changeDate', function(ev,startDate){
                                //开始日期不能大于结束日期
                                fieldlast.find("input").eq(1).datetimepicker('setStartDate',fieldlast.find("input").eq(0).val());
                            });

                            fieldlast.find("input").eq(1).datetimepicker({
                                format: selectTimes(results.field[i].dateType),
                                autoclose: true,
                                todayBtn: false,
                                startView: selectTimes_format(results.field[i].dateType),
                                minView: selectTimes_format(results.field[i].dateType),
                                pickerPosition: "bottom-right"
                            }).on('changeDate', function(ev){ 
                                //结束日期不能小于开始日期
                                fieldlast.find("input").eq(0).datetimepicker('setEndDate',fieldlast.find("input").eq(1).val());
                            });
                        }
                    }
                    if(results.field[i].inputvalue){
                       fieldlast.find("input").eq(0).val(results.field[i].inputvalue);
                       fieldlast.find("input").eq(1).val(results.field[i].dateTimeEnd);
                    }
                }
            }, 1000);
            //附件 
            if (results.field[i].formtype == "FileComponent") {
                var file = "<upload-modal upload-attrs='addFileBtn' att-object='att'>添加附件</upload-modal>";
                //var imgs = "<upload-modalsingle upload-attrs='addFileBtn'>添加附件</upload-modalsingle>";
                var filenews = $compile(file)($scope);
                fieldlast.find(".btn").html(filenews);
                
                if (results.field[i].setting ){
                    var fieldNum = Object.keys(results.field[i].setting.FileComponent).length;
                    for (var fieldc = 0; fieldc < fieldNum; fieldc++) {
                        fieldlast.find("ul").append('<li class="porela">'+results.field[i].setting.FileComponent[fieldc].FileComponent+'</li>');
                    }
                }

                if( status == 0 ){
                    fieldlast.find(".btn, .del").remove();
                }

            }
            
            //图片
            if(results.field[i].formtype == "ImageComponent"){
                var file = "<upload-modal-images upload-attrs='addFileBtn' att-object='att'>上传图片</upload-modal-images>";
                //var imgs = "<upload-modalsingle upload-attrs='addFileBtn'>添加附件</upload-modalsingle>";
                var filenews = $compile(file)($scope);
                fieldlast.find(".btn").html(filenews);
                //console.log(results.field[i]);
                if (results.field[i].setting ){
                    var ImageNum = Object.keys(results.field[i].setting.ImageComponent).length;
                    for (var imgc = 0; imgc < ImageNum; imgc++) {
                        fieldlast.find("ul").append('<li class="porela">'+results.field[i].setting.ImageComponent[imgc].itemImgs+'</li>');
                    }
                }
                if( status == 0 ){
                    fieldlast.find(".btn, .del").remove();
                }
            }
            
            //所有文本区域
            if (results.field[i].formtype == "TextArea"){
                if(results.field[i].inputvalue){
                    var field_value = results.field[i].inputvalue;
                    field_value = field_value.replace(/<br\/>/ig , "\n");
                    field_value = field_value.replace(/&nbsp;/ig , " ");
                    fieldlast.find("textarea").val(field_value);
                }
                if( status == 0 ){
                    fieldlast.find("textarea").attr({"disabled":"disabled"});
                }
            }

            //部门 人员
            if(results.field[i].formtype == "Department" || results.field[i].formtype == "Employee"){
                var grouphtml = fieldlast.find('.addbtn1');
                grouphtml.attr({"id":i});
                $compile(grouphtml)($scope);
                if (results.field[i].setting ){
                    var deparNum = Object.keys(results.field[i].setting.Employee).length;
                    for (var dep = 0; dep < deparNum; dep++) {
                        fieldlast.find("ul").append('<li class="porela"><div class="filename fl omit" data-member_id='+results.field[i].setting.Employee[dep].itemId+'>'+results.field[i].setting.Employee[dep].itemName+'</div><div class="del fr">删除</div></li>');
                    }
                }
                if( status == 0 ){
                    fieldlast.find(".addbtn1,.del").remove();
                    fieldlast.find(".rcontent").addClass("read");
                }
            }

            if( status == 0 ){
                fieldlast.find("input").attr({"disabled":"disabled"});
            }
 
        });
    };
    
    //展示提交数据
    service.formshowsave = function(results){

 
        if( CheckTextBiaoqing() ){
           
        
            var isok = true;
            var fieldlast = $("#formpresentation_edit .borderbor");
            var borderborid = $('#formpresentation_edit .scrollbor .borderbor');

            var reg_Mobile = /^((\+?86)|(\(\+86\)))?(13[012356789][0-9]{8}|15[012356789][0-9]{8}|18[02356789][0-9]{8}|147[0-9]{8}|1349[0-9]{7})$/,
                reg_Number = /^[0-9]*$/,
                reg_Number_float =  /^([1-9]\d{0,15}|0)(\.\d{1,4})?$/,
                reg_Emails = /^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]+$/,
                reg_phones = /^([0-9]{3,4}-)?[0-9]{7,8}$/,
                arrFloat = /^-?(?:0|[1-9]\d+)(\.\d+)?$/,
                reg_num_one = /^([1-9]\d{0,7}|0)(\.\d{1,2})?$/;

            var container = $('#formpresentation_edit .scrollbor');
            

            function formError(obj) {
                    var oTimer = null;
                    var i = 0;
                    oTimer = setInterval(function () {
                        i++;
                        i == 7 ? clearInterval(oTimer) : (i % 2 == 0 ? obj.css("background-color", "#ffffff") : obj.css("background-color", "#ffd8d5"));
                    }, 100);
            }
                
            function scrollcontr(id){
                container.scrollTop(
                    id.offset().top - container.offset().top + container.scrollTop()
                );
                formError(id);
            }

            $.each(results.field, function (i, item) {
                var leftTitle = results.field[i].title;
                //复选框检测
                if(results.field[i].formtype == "CheckBox"){
                   if( results.field[i].required == 1 ){
                       var checkboxed = fieldlast.eq(i).find("input[type='checkbox']:checked").length;
                       if( checkboxed == 0 ){
                           alert("请选择该"+leftTitle+"！");
                           scrollcontr(borderborid.eq(i));
                           isok = false;
                           return false;
                       }
                   }
                }
                //单选框检测
                if(results.field[i].formtype == "RadioBox"){
                   if( results.field[i].required == 1 ){
                       var radioed = fieldlast.eq(i).find("input[type='radio']:checked").length;
                       if( radioed == 0 ){
                           alert("请选择该"+leftTitle+"！");
                           scrollcontr(borderborid.eq(i));
                           isok = false;
                           return false;
                       }
                   }
                }
                //日期
                if (results.field[i].formtype == "DateComponent"){
                    if( results.field[i].required == 1 ){
                        if (fieldlast.eq(i).find("input").val()==''){
                            alert("请选择该"+leftTitle+"！");
                            scrollcontr(borderborid.eq(i));
                            isok = false;
                            return false;
                        }
                    }
                }
                //日期区间
                if (results.field[i].formtype == "DateInterval"){
                    if( results.field[i].required == 1 ){
                        if (fieldlast.eq(i).find("input:eq(0)").val()=='' || fieldlast.eq(i).find("input:eq(1)").val()==''){
                            alert("请选择该"+leftTitle+"！");
                            scrollcontr(borderborid.eq(i));
                            isok = false;
                            return false;
                        }
                    }
                }
                //所有必填
                if (results.field[i].formtype == "Text" || 
                    results.field[i].formtype == "Money" ||
                    results.field[i].formtype == "Mobile" ||
                    results.field[i].formtype == "Phone" ||
                    results.field[i].formtype == "NumberComponent" ||
                    results.field[i].formtype == "Email"){
                    if( results.field[i].required == 1 ){
                        if (fieldlast.eq(i).find("input").val().YLstringcheck()==''){
                            alert("请填写该"+leftTitle+"！");
                            scrollcontr(borderborid.eq(i));
                            isok = false;
                            return false;
                        }
                    }
                    if( Publicfactory.checkEnCnstrlen(fieldlast.eq(i).find("input").val()) > 60){
                        alert("请填写该"+leftTitle+"内容长度不能大于30个字！");
                        scrollcontr(borderborid.eq(i));
                        isok = false;
                        return false;
                    }
                }
                //附件 图片 部门 人员
                if (results.field[i].formtype == "Employee" || 
                    results.field[i].formtype == "Department" ||
                    results.field[i].formtype == "FileComponent" ||
                    results.field[i].formtype == "ImageComponent"){
                    if( results.field[i].required == 1 ){
                        if (fieldlast.eq(i).find("ul li").length==0){
                            alert("请添加该"+leftTitle+"！");
                            scrollcontr(borderborid.eq(i));
                            isok = false;
                            return false;
                        }
                    }
                }
                //文本框
                if (results.field[i].formtype == "TextArea"){
                    if( results.field[i].required == 1 ){
                        if (fieldlast.eq(i).find("textarea").val().YLstringcheck()==''){
                            alert("请填写该"+leftTitle+"！");
                            scrollcontr(borderborid.eq(i));
                            isok = false;
                            return false;
                        }
                    }
                    if( Publicfactory.checkEnCnstrlen(fieldlast.eq(i).find("textarea").val()) > 200){
                        alert("请填写该"+leftTitle+"内容长度不能大于100个字！");
                        scrollcontr(borderborid.eq(i));
                        isok = false;
                        return false;
                    }
                }
                if (results.field[i].formtype == "Money"){
                    if (fieldlast.eq(i).find("input").val().YLstringcheck()!=''){
                        if (!reg_num_one.test(fieldlast.eq(i).find("input").val())){
                            alert("请填写正确的"+leftTitle);
                            scrollcontr(borderborid.eq(i));
                            isok = false;
                            return false;
                        }
                    }
                }
                if (results.field[i].formtype == "NumberComponent"){
                    if (fieldlast.eq(i).find("input").val().YLstringcheck()!=''){
                        if (!reg_num_one.test(fieldlast.eq(i).find("input").val())){
                            alert("请填写正确的"+leftTitle);
                            scrollcontr(borderborid.eq(i));
                            isok = false;
                            return false;
                        }
                    }
                }
                if (results.field[i].formtype == "Mobile"){
                    if (fieldlast.eq(i).find("input").val().YLstringcheck()!=''){
                        if (!reg_Mobile.test(fieldlast.eq(i).find("input").val())){
                            alert("请填写正确的"+leftTitle);
                            scrollcontr(borderborid.eq(i));
                            isok = false;
                            return false;
                        }
                    }
                }
                if (results.field[i].formtype == "Phone"){
                    if (fieldlast.eq(i).find("input").val().YLstringcheck()!=''){
                        if (!reg_phones.test(fieldlast.eq(i).find("input").val())){
                            alert("请填写正确的"+leftTitle);
                            scrollcontr(borderborid.eq(i));
                            isok = false;
                            return false;
                        }
                    }
                }
                if (results.field[i].formtype == "Email"){
                    if (fieldlast.eq(i).find("input").val().YLstringcheck()!=''){
                        if (!reg_Emails.test(fieldlast.eq(i).find("input").val())){
                            alert("请填写正确的"+leftTitle);
                            scrollcontr(borderborid.eq(i));
                            isok = false;
                            return false;
                        }
                    }
                }


            });

        }

        return isok;

    };

    return service;
});
//表单还原展示，可提交数据 开始








//=======================
factoryApp.factory('Publicfactory',function(){
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
        var realLength = 0, 
            len = 0, 
            charCode = -1;
        var str = str.replace(/[\r\n]/g, "");
            len = str.length;
            
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


factoryApp.factory('filtersModel',function($filter){
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

    //格式化 时
    service.filterTimeHH = function(datatime){
        var timedata;
        var dateFilter = $filter('date');
        timedata = dateFilter(datatime, 'HH');
        return timedata;
    };

    //格式化 年 月 日 时 分
    service.filterDateTime = function(datatime){
        var timedata;
        var dateFilter = $filter('date');
        timedata = dateFilter(datatime, 'yyyy-MM-dd HH:mm');
        return timedata;
    };

    // 字符串时间转Date
    service.filterStrDate = function(strDate) {
        if (typeof strDate == 'object') return strDate;
        return new Date(Date.parse(strDate.replace(/-/g, "/")));
    }

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
factoryApp.factory('validate',function(){
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
factoryApp.factory('topsearchsize',function(){
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
factoryApp.factory('infoModel',function($http,$state){
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
factoryApp.factory('checkBrowsers',function($http,$state){
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
factoryApp.factory('checkBrowserRedirect',function($http,$state){
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
 



factoryApp.factory('checkModel',function($http){
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

    // 判断字符串长度
    service.checkStrLen = function(str) {
        var realLength = 0,
            len = str.length,
            charCode = -1;

        for (var i = 0; i < len; i++) {
            charCode = str.charCodeAt(i);
            if (charCode >= 0 && charCode <= 128) realLength += 1;
            else realLength += 1;
        }

        return realLength;
    }

    return service;
});

factoryApp.factory('activeModel',function($http){
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

factoryApp.factory('OAInterceptor',function($q,$cookies,$cookieStore,$location){

    return {
        request:function(config){
            /*if(!config.cache)  //排除内置的请求
            {
                if(config.url.indexOf('token')<0)
                {
                    if("undefined" != typeof $cookies.app && $cookies.app == 1) {
                        if("undefined" != typeof $cookies.app_token && $cookies.app_token){
                            config.url+='&token='+$cookies.app_token + "&app=1";
                        }
                    }else {
                        if($cookies.sessionoa){
                            config.url+='&token='+$cookies.sessionoa;
                        }
                    }
                }
            }*/
            return config;
        },
        'response': function(response) {
            // do something on success
            return response;
        },
        responseError: function (response) {
            var data = response.data;
            // 判断错误码，如果是未登录
            if(data.status==401){
                $cookieStore.remove("userInfo");
                $location.path('#/login');
            }else if(data.status==504){
                alert("请求超时，请重试！");
            }else{
                alert("请求超时，请重试！");
             }
            return $q.reject(response);
        }
    };

});


factoryApp.factory('util',function(){
    var util = {};

    util.setImgRand = function(data) {

        if (Array.isArray(data)) {
            data.forEach(function(item) {
                if (item.hasOwnProperty("head_img") && item.head_img) {
                    item.head_img += '?' + Date.now();
                }

                if (item.hasOwnProperty("head_img_path") && item.head_img_path) {
                    item.head_img_path += '?' + Date.now();
                }

                if (item.hasOwnProperty("headImg") && item.headImg) {
                    item.headImg += '?' + Date.now();
                }
                
            })
        }else {
            if (data.hasOwnProperty("head_img") && data.head_img) {
                data.head_img += '?' + Date.now();
            }

            if (data.hasOwnProperty("head_img_path") && data.head_img_path) {
                data.head_img_path += '?' + Date.now();
            }

            if (data.hasOwnProperty("headImg") && data.headImg) {
                data.headImg += '?' + Date.now();
            }
        }

        return data;
    }

    return util;
});



