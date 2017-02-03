$(function(){
 
    /*选中字段状态*/
    $(document).on('click','.createforms .field',function(){
    	var clickEdit=new jQuery.editForm();
		var currentWidget=$(this);
    	currentWidget.siblings().removeClass("field-active");
    	currentWidget.addClass("field-active");
		var currentWidgetType=currentWidget.data('widget');
		clickEdit.editWidget(currentWidget,currentWidgetType);
    });

    /*清除全部字段*/
    $("#clearAllForm").click(function(){
    	if(!$(".field").length){
			return false;
		}
		else{
			var clearbtn=confirm("您确定清空全部字段吗？");
	    	if(clearbtn){
	    		$(".widget-control").empty();
	    		$(".formseditbor .scrollbor").empty();
	    		//$(".formseditbor .scrollbor").empty().append("<div class='alert alert-danger' role='alert'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span><span class='sr-only'>Error:</span>请先选择控件</div>");
	    	}
	    	else{
    			return false;
    		}
		}
    });

    /*移除单个字段*/
    //$(document).on('click','.glyphicon-remove-circle',function(event){
    $(".createforms").delegate(".glyphicon-remove-circle","click",function(event){
    	event.stopPropagation();
    	var currentField=$(this).parents(".field");
    	$(".delcreatefield .toptitle span").html(currentField.index());
		$(".delcreatefield,#masklayer1").show();
    });

    $(".delcreatefield .btns:eq(0)").click(function(){
    	 
		var fieldNum=$(".field").length;
		var spannums=$(".delcreatefield .toptitle span").html();
		var currentField=$(".field").eq(spannums);
		if(fieldNum==1){
			$(".formseditbor .scrollbor").empty();
			//$(".formseditbor .scrollbor").empty().append("<div class='alert alert-danger' role='alert'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span><span class='sr-only'>Error:</span>请先选择控件</div>");
		}
		else{
			if(fieldNum-currentField.index()==1){
				currentField.prev(".field").trigger("click");
			}
			else{
				currentField.next(".field").trigger("click");
			}
		}
		currentField.remove();
        $(".delcreatefield,#masklayer1").hide();
	});
	$(".delcreatefield .btns:eq(1)").click(function(){
		$(".delcreatefield,#masklayer1").hide();
		return false;
	});

    /*拖拽配置*/
    $(".draggable").draggable({
    	containment:".mainsbar",
    	scroll:false,
		connectToSortable: ".droppable",
		helper: "clone"  
    });

    /*拖拽放置配置*/
    $(".droppable").droppable({
		 
    })

    /*排序配置*/
    var plusNum=1;
    $(".droppable").sortable({
    	// revert:"true",
		cursor: "move",    
		placeholder: "form-placeholder-filed",
		// 排序动作结束之前载入字段DOM
		beforeStop:function(event, ui) {
			if(ui.item.hasClass("ui-draggable")){
				var widgetType=ui.item.data('widget');
				var formObject=new jQuery.editForm();
				var widgetDom=formObject.formWidget(widgetType);
				$(".field").removeClass("field-active");
				$(".form-placeholder-filed").after(widgetDom);
				ui.item.remove();
				plusNum++;
			}
		},
		// 获取字段设置信息
		stop:function(event, ui){
			var editObject=new jQuery.editForm();
			var currentWidget=$(".field-active");
			var currentWidgetType=$(".field-active").data('widget');
			editObject.editWidget(currentWidget,currentWidgetType);
			var containers = $(".createforms .scrollbor");
			containers.scrollTop(
                currentWidget.offset().top - containers.offset().top + containers.scrollTop()
            );
		}
    });

    //禁止拖拽选择文本
    $(".droppable").disableSelection();

    /*单选、复选、选择参数配置*/
   // $(document).on('click','.glyphiconminus',function(){
   	$(".formseditbor").delegate(".glyphiconminus","click",function(){
   		var self_radio = $(this).parents("li").find("input[type=radio]");
    	if($(".RadioBox_js").length==2){
    		alert("至少保留2个选项！");
    		return false;
    	}
    	if (self_radio[0].checked) {
    		alert("不能移除已选中项！");
    		return false;
    	}
    	var positionNum=$(this).parents("li").index();
        //console.log(positionNum);
    	$(".field-active .rcontent ul").find("li:eq("+positionNum+")").remove();
    	 
    	$(".field-active .choicelist .Select_js").eq(positionNum).remove();
    	// $(".field-active .editTable tr").children("th:eq("+positionNum+")").remove();
    	// $(".field-active .editTable tr").children("td:eq("+positionNum+")").remove();
    	//$("select[name=totalItem]").children("option:eq("+(positionNum)+")").remove();
  
    	$(this).parents("li").remove();
    });
	
    var titlei = 4;
    $(".formseditbor").delegate(".glyphiconplus","click",function(){
    
    	var currentWidgetType=$(".field-active").data('widget');
    	var positionNum=$(this).parents("li").index();
    	var currentName=$(".field-active .rcontent ul").find("input").attr("name");
        
    	if(currentWidgetType=='RadioBox'){
    		$(this).parents("li").after("<li class='RadioBox_js'><input type='radio' name='editor-option' class='option_js' value=''><input type='text' class='form-control optionName_js' value='选项"+titlei+"'/>  <span class='setbtnbor fr'><i class='pbiconfont poabso setcutbtn pointer glyphiconplus'>&#xe646;</i><i class='pbiconfont poabso setaddbtn pointer glyphiconminus'>&#xe64a;</i></span> </li>");

    		$(".field-active .rcontent ul").find("li:eq("+positionNum+")").after("<li><label class='radio-inline'><input name='"+currentName+"' type='radio' disabled><span>选项"+titlei+"</span></label></li>");
    		titlei++;
    	}
    	if(currentWidgetType=='CheckBox'){
    		$(this).parents("li").after("<li class='RadioBox_js'><input type='checkbox' name='editor-option' class='option_js' value=''><input type='text' class='form-control optionName_js' value='选项"+titlei+"'/>  <span class='setbtnbor fr'><i class='pbiconfont poabso setcutbtn pointer glyphiconplus'>&#xe646;</i><i class='pbiconfont poabso setaddbtn pointer glyphiconminus'>&#xe64a;</i></span> </li>");

    		$(".field-active .rcontent ul").find("li:eq("+positionNum+")").after("<li><label class='CheckBox_js'><input name='"+currentName+"' type='checkbox' disabled><span>选项"+titlei+"</span></label></li>");
    		titlei++;
    	}
    	if(currentWidgetType=='Select'){
    		$(this).parents("li").after("<li class='RadioBox_js'><input type='radio' name='editor-option' class='option_js' value=''><input type='text' class='form-control optionName_js' value='选项"+titlei+"'/> <span class='setbtnbor fr'><i class='pbiconfont poabso setcutbtn pointer glyphiconplus'>&#xe646;</i><i class='pbiconfont poabso setaddbtn pointer glyphiconminus'>&#xe64a;</i></span> </li>");

    		$(".field-active .choicelist").find("option:eq("+positionNum+")").after("<option class='Select_js' value=''>选项"+titlei+"</option>");
    		titlei++;
    	}
		
    });
	
    /*====================================
      表单双向数据同步
      ====================================*/
    // 表单

	// $("#description-form").keyup(function() {
	// 	var vm=new jQuery.editForm();
	// 	var thisTemp=$("#description-form");
	// 	vm.textLength(thisTemp,80);
	//   	$(".form-design-wrapper .form-description").text($(this).val());
	// });
    $(".createforms .titlebor input").keyup(function() {
    	var vm=new jQuery.editForm();
		var thisTemp=$(this);
		vm.textLength(thisTemp,20); 
	});
	$(document).on('keyup','.formseditbor .scrollbor input',function(){
		var vm=new jQuery.editForm();
		var thisTemp=$(this);
		vm.textLength(thisTemp,10);

	});
	$(document).on('keyup','.formseditbor .scrollbor textarea',function(){
		var vm=new jQuery.editForm();
		var thisTemp=$(this);
		vm.textLength(thisTemp,80);
	});

	/*	// 字段
	$(document).on('keyup','#component-field-name',function(){
		$(".field-active").find("input[name=field_name]").val($(this).val());
	});*/

	$(document).on('keyup','#component-title',function(){
		$(".field-active").find(".widget-title").text($(this).val());
	});

	$(document).on('keyup','#component-describe',function(){
		if( $(this).val().replace(/(^\s*)/g, "") == '' ){
            $(".field-active").find(".field-description").addClass("hide").text('');
		}else{
			$(".field-active").find(".field-description").removeClass("hide").text($(this).val());
		}
	});



 //    $(document).on('click','#required',function(){
    	 
	//     if($(".field-active").find(".widget-required").is(":hidden")){
	//         $(".field-active").find(".widget-required").show();
	//     }else{
	//         $(".field-active").find(".widget-required").hide();
	//     }
	// });

     
	$(".formseditbor").delegate("#required","click",function(){
	    if($(".field-active").find(".widget-required").is(":hidden")){
	        $(".field-active").find(".widget-required").show();
	    }else{
	        $(".field-active").find(".widget-required").hide();
	    }
	});


	// 分割线同步
	$(document).on('click','input[name=dividingLineType]',function(){
		var lineType=$(this).val();
		$(".field-active").find(".divider-line").removeClass().addClass("divider-line "+lineType);
	});

	// 描述样式同步
	$(document).on('click','input[name=paragraphType]',function(){
		var lineType=$(this).val();
		$(".field-active").find(".alert").removeClass().addClass("alert "+lineType);
	});

	// 选项同步
	$(document).on('focus','.optionName_js',function(){
		optionText=$(this).val();
	});
	
	$(document).on('keyup','.optionName_js',function(){
		var positionNum=$(this).parents("li").index();

		 if($(".RadioBox_js").length){
		 	var OptionList=new Array();
			 $(".RadioBox_js .optionName_js").each(function (){
				OptionList.push($(this).val());
			 });
			 var reOptionList=OptionList.sort();

			 for(var i=0;i<OptionList.length;i++){
			     if(reOptionList[i]==reOptionList[i+1] && reOptionList[i]!="" && reOptionList[i+1]!=""){
			     	$(this).val(optionText);
				    alert("选项名称重复！");
				 }
			 }
		 }

		if(!$.trim($(this).val())){
			$(".field-active .rcontent ul").find("label:eq("+positionNum+")").children("span").text("null");
			$(".field-active .choicelist").find("option:eq("+positionNum+")").text("null").attr("value","null");
			alert("选项名称不能为空！");
		}else{
			$(".field-active .rcontent ul").find("label:eq("+positionNum+")").children("span").text($(this).val());
			$(".field-active .choicelist").find("option:eq("+positionNum+")").text($(this).val()).attr("value",$(this).val());
		}
		
	});

	$(document).on('click','input[name=editor-option]',function(){

		var currentName=$(".field-active .rcontent ul").find("input").attr("name");
		$(".field-active .rcontent ul").empty();

		var currentWidgetType=$(".field-active").data('widget');

		if(currentWidgetType=='Select'){ 
			$(".field-active .choicelist").empty();
		}

		var radioNum=$(".RadioBox_js").length;
		var formObject=new jQuery.editForm();

		var dataMax=$("#checkedMax").val();
		var checkedNum=formObject.checkedMax(dataMax,radioNum,'');
		if(checkedNum>dataMax){
			alert("您最多只能勾选"+dataMax+"个");
			$(this).attr("checked",false);
		}

		for(var i=0;i<radioNum;i++){
			var radioText=$(".optionName_js:eq("+i+")").val();
			if(currentWidgetType=='RadioBox'){
				$(".field-active .rcontent ul").append("<li><label class='radio-inline'><input name='"+currentName+"' type='radio' disabled><span>"+radioText+"</span></label></li>");
				if($(".RadioBox_js:eq("+i+")").find("input[name=editor-option]").prop("checked")){
					$(".field-active .rcontent ul").find("input[name="+currentName+"]:last").attr("checked",true);
				}
			}
			if(currentWidgetType=='CheckBox'){
				$(".field-active .rcontent ul").append("<li><label class='CheckBox_js'><input name='"+currentName+"' type='checkbox' disabled><span>"+radioText+"</span></label></li>");
				if($(".RadioBox_js:eq("+i+")").find("input[name=editor-option]").prop("checked")){
					$(".field-active .rcontent ul").find("input[name="+currentName+"]:last").attr("checked",true);
				}
			}
			if(currentWidgetType=='Select'){ 
				$(".field-active .choicelist").append("<option class='Select_js' value=''>"+radioText+"</option>");
				if($(".RadioBox_js:eq("+i+")").find("input[name=editor-option]").prop("checked")){
					$(".field-active .choicelist").find("option:last").attr("selected",true);
				}
			}

		}
	});

    var reg_Number_checkedMax = /^[1-9]*$/;
	$(document).on('keyup','#checkedMax',function(){
		var checkedMax=$(this).val();
		if (!reg_Number_checkedMax.test( checkedMax )){
			$("#checkedMax").val('');
            $(".field-active").find("input[name=checkedMax]").val('');
            alert("请填写正确的可选数量!");
            return false;
		}else{
			$(".field-active").find("input[name=checkedMax]").val(checkedMax);
		}
	});

	//金额同步
	$(document).on('keyup','#moneyType',function(){
		$(".field-active .money-type").text("("+$(this).val()+")");
	})
	
	// 日期同步
	$(document).on('change','input[name=dateFormat]',function(){
		var dateType=$("input[name=dateFormat]:checked").val().replace(/[-:\s]/g,"");
		switch(dateType){
			case "yyyyMMddHHmm":
				$(".field-active").find("input").attr("placeholder","年-月-日 时:分");
			break;

			case "yyyyMMddHH":
				$(".field-active").find("input").attr("placeholder","年-月-日 时");
			break;

			case "yyyyMMdd":
				$(".field-active").find("input").attr("placeholder","年-月-日");
			break;

			case "yyyyMM":
				$(".field-active").find("input").attr("placeholder","年-月");
			break;
		}
	});
	 
	/*==============================
	  保存表单
	==============================*/
	var f = {
		state : 0
	};

	var saveCheckFn = function () {
		if(!$(".field").length){
			alert("请先选择控件！");
			return false;
		}
		if(!$(".createforms .titlebor input").val().YLstringcheck()){
			alert("请输入表单标题！");
			return false;
		}
		var patt=/[^\u4e00-\u9fa5a-zA-Z\d]/g;
		if($(".createforms .titlebor input").val().YLstringcheck().match(patt)){
			alert("表单标题只能包含中文、字母、数字");
			return false;
		}

		if($(".createforms .field").length){

			$(".widget-control").find("span").each(function(){
				if($(this).text().YLstringcheck()=="null"){
					alert("选项名称不能为空！");
					return false;
				}
			});

			$(".widget-control").find("option").each(function(){
				if($(this).text().YLstringcheck()=="null"){
					alert("选项名称不能为空！");
					return false;
				}
			});

			var FieldList=new Array();

			$(".createforms .field .widget-title").each(function (){
				FieldList.push($(this).text().trim().YLstringcheck());
			});

			var reFieldList=FieldList.sort();

			for(var i=0;i<FieldList.length;i++){
				if(!reFieldList[i]){
					alert("请填写字段标题！");
					return false;
				}
				if(reFieldList[i]==reFieldList[i+1]){
					alert("字段标题重复！");
					return false;
				}
			}

            //判断创建表单时至少勾选一个必填项才能创建
            var widgetRequired = 0;
			$(".createforms .field .widget-required").each(function (){
				if( !$(this).is(':hidden') ){
					widgetRequired++;
				}
            });

            if( widgetRequired == 0){
            	alert("创建表单时至少勾选一个必填项才能创建!");
                return false;
            }else{
                return true;
            }
            //判断创建表单时至少勾选一个必填项才能创建
            
			
		}

		return true;
	};
    

    var saveurl='';
	$("#saveForm").click(function(){
		if (saveCheckFn()){
			var createForm = new jQuery.editForm();
			var result = createForm.saveForm();
			$.ajax({
				type : "post",
				url :"/index.php?r=apply/apply-model/create-model",
				contentType:"application/x-www-form-urlencoded",
				dataType : "json",
				data : {postdata:JSON.stringify(result)},

				success : function(msg) {
					if(msg.code==-5){
						alert(msg.msg);
						//console.log(msg.msg);
						return false;
					}
					if(msg.code==0){
						alert(msg.msg);
						//console.log(msg.msg);
					}else{
						$("#masklayer1,.issetFormStep").show();
						saveurl = "#/apply/flow/"+msg.model_id+"/"+msg.model_type+"/0";
					}
				}
			});
		}
	});

    $(".issetFormStep .savego").click(function(){
          $(window).unbind('beforeunload');//解除提示绑定 
          parent.location.href = saveurl;
    });

    $(".issetFormStep .btns.gray").click(function(){
          $("#masklayer1,.issetFormStep").hide();
          saveyesgo();
    });

	function saveyesgo(){
		$(window).unbind('beforeunload');//解除提示绑定
		parent.location.href = "#/apply/manage";
	}


	$("#saveFormStep").click(function(){
		if (saveCheckFn()){
			var createForm = new jQuery.editForm();
			var result = createForm.saveForm();
			$.ajax({
				type : "post",
				url :"/index.php?r=apply/apply-model/create-model",
				contentType:"application/x-www-form-urlencoded",
				dataType : "json",
				data : {postdata:JSON.stringify(result)},

				success : function(msg) {
					if(msg.code==-5){
						alert(msg.msg);
						return false;
					}
					if(msg.code==0){
						alert(msg.msg);
					}else{
						alert(msg.msg);
						$(window).unbind('beforeunload');//解除提示绑定
						parent.location.href="#/apply/flow/"+msg.model_id+"/"+msg.model_type+"/0";
					}
				}
			});
		}
	});


	/*==============================================
	编辑表单
	================================================*/
	var urlParams = parent.location.href.split("/");
    var FORMID = urlParams[urlParams.length-1];
    var p = /^[1-9]\d*$/;
    if(p.test(FORMID)){

    	f.tate = 1;
    	f.id = FORMID;
        function getCookie(cookieName) {
            var strCookie = document.cookie;
            var arrCookie = strCookie.split("; ");
            for(var i = 0; i < arrCookie.length; i++){
                var arr = arrCookie[i].split("=");
                if(cookieName == arr[0]){
                    return arr[1];
                }
            }
            return "";
        }
        var MyCookie = getCookie('sessionoa');
        window.onload = function() {
            $.ajax({
                type : "get",
                url :"/index.php?r=form/getform&modelid="+FORMID+"&token="+decodeURI(MyCookie),
                contentType:"application/x-www-form-urlencoded",
                dataType : "json",
                success : function(data) {
                    $(".createforms .scrollbor").html(data.data.html);
                    $(".createforms .titlebor input").val(data.data.title);
                    $(".createforms .field:first").trigger("click");
                    // $(".glyphicon-remove-circle").remove();
                }
            });
        }
    }



    /*===============================================
      表单设计对象
      ===============================================*/
    $.extend({
    	editForm:function(){}
    });
	jQuery.editForm.prototype={
		// 控件DOM	
		formWidget:function(widgetType){
			switch(widgetType)
				{
				case "Text":
					return "<div class='field ui-state-default field-active borderbor of porela' data-widget='Text'><label class='widget-title porela break inblock title'>文本输入框</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><input type='text' class='form-control' disabled><div class='field-description hide'></div></div><i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i></div>";
					break;
				case "TextArea":
					return "<div class='field ui-state-default field-active borderbor of porela' data-widget='TextArea'><label class='widget-title porela break inblock title'>多行文本框</label><span class='widget-required'>*</span><div class='widget-content'><textarea class='pb_textareas noborder' disabled></textarea><div class='field-description hide'></div></div><i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i></div>";
					break;
				case "RadioBox":
					return "<div class='field ui-state-default field-active borderbor of porela flex' data-widget='RadioBox'><label class='widget-title porela break inblock title'>单选框</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><ul><li><label class='radio-inline'><input name='radioView"+plusNum+"' type='radio' disabled><span>选项1</span></label></li><li><label class='radio-inline'><input name='radioView"+plusNum+"' type='radio' disabled><span>选项2</span></label></li><li><label class='radio-inline'><input name='radioView"+plusNum+"' type='radio' disabled><span>选项3</span></label></li></ul><div class='field-description hide'></div></div><i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i></div>";
					break;
				case "CheckBox":
					return "<div class='field ui-state-default field-active borderbor of porela flex' data-widget='CheckBox'><label class='widget-title porela break inblock title'>复选框</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><ul><li><label class='CheckBox_js'><input name='CheckBoxView"+plusNum+"' type='checkbox' disabled><span>选项1</span></label></li><li><label class='CheckBox_js'><input name='CheckBoxView"+plusNum+"' type='checkbox' disabled><span>选项2</span></label></li><li><label class='CheckBox_js'><input name='CheckBoxView"+plusNum+"' type='checkbox' disabled><span>选项3</span></label></li></ul><div class='field-description hide'></div></div><i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i><input type='hidden' name='checkedMax' value=''></div>";
					break;
				case "Select":
					return "<div class='field ui-state-default field-active borderbor of porela flex SelectSelect' data-widget='Select'><label class='widget-title porela break inblock title'>下拉菜单</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><select class='form-control choicelist' disabled><option class='Select_js' value='选项1'>选项1</option><option class='Select_js' value='选项2'>选项2</option><option class='Select_js' value='选项3'>选项3</option></select><div class='field-description hide'></div></div><i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i></div>";
					break;
				case "DateComponent":
					return "<div class='field ui-state-default field-active borderbor of porela' data-widget='DateComponent'><label class='widget-title porela break inblock title'>日期</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><input type='text' class='form-control' placeholder='年-月-日' disabled><div class='field-description hide'></div></div><i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i></div>";
					break;
				case "DateInterval":
					return "<div class='field ui-state-default field-active borderbor of porela' data-widget='DateInterval'><label class='widget-title porela break inblock title'>日期区间</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><input type='text' class='form-control' placeholder='年-月-日' disabled><span class='mr5'>——</span><input type='text' class='form-control' placeholder='年-月-日' disabled><div class='field-description hide'></div></div><i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i></div>";
					break;		
				case "NumberComponent":
					return "<div class='field ui-state-default field-active borderbor of porela' data-widget='NumberComponent'><label class='widget-title porela break inblock title'>数字输入框</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><input type='text' class='form-control' disabled><div class='field-description hide'></div></div><i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i></div>";
					break;
				case "Money":
					return "<div class='field ui-state-default field-active borderbor of porela' data-widget='Money'><label class='widget-title porela break inblock title'>金额</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'> <input type='text' class='form-control' disabled> <span class='money-type fr'>(人民币)</span><div class='field-description hide'></div></div><i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i></div>";
					break;
				case "Employee":
					return "<div class='field ui-state-default field-active borderbor porela flex' data-widget='Employee'><label class='widget-title porela break inblock title'>用户选择</label><span class='widget-required'>*</span>   <div class='break pbc inblock rcontent'><ul><li></li></ul></div>   <div class='field-description hide'></div><i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i></div>";
					break;
				case "Department":
					return "<div class='field ui-state-default field-active borderbor porela flex' data-widget='Department'><label class='widget-title porela break inblock title'>部门选择</label><span class='widget-required'>*</span> <div class='break pbc inblock rcontent'><ul><li></li></ul></div>   <div class='field-description hide'></div><i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i></div>";
					break;
				case "DividingLine":
					return "<div class='field ui-state-default field-active borderbor of porela' data-widget='DividingLine'><div class='divider-line solid'></div><i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i><span class='widget-required'></span></div>";
					break;	
				case "Paragraph":
					return "<div class='field ui-state-default field-active borderbor of porela' data-widget='Paragraph'><div class='alert alert-success' role='alert'>空白段落</div><i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i><span class='widget-required'></span></div>";
					break;
				case "Email":
					return "<div class='field ui-state-default field-active borderbor of porela' data-widget='Email'><label class='widget-title porela break inblock title'>邮箱</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><input type='text' class='form-control' disabled><div class='field-description hide'></div></div><i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i></div>";
					break;
				case "Phone":
					return "<div class='field ui-state-default field-active borderbor of porela' data-widget='Phone'><label class='widget-title porela break inblock title'>电话</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><input type='text' class='form-control' disabled><div class='field-description hide'></div></div><i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i></div>";
					break;
				case "Mobile":
					return "<div class='field ui-state-default field-active borderbor of porela' data-widget='Mobile'><label class='widget-title porela break inblock title'>手机</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><input type='text' class='form-control' disabled><div class='field-description hide'></div></div><i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i></div>";
					break;
				case "FileComponent":
					return "<div class='field ui-state-default field-active borderbor of porela flex' data-widget='FileComponent'><label class='widget-title porela break inblock title'>附件</label><span class='widget-required'>*</span> <ul><li class='porela'></li></ul>   <div class='btn blue fr poabso'>添加附件</div>   <div class='field-description hide'></div> <i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i></div>";
					break;
				case "ImageComponent":
					return "<div class='field ui-state-default field-active borderbor of porela flex' data-widget='ImageComponent'><label class='widget-title porela break inblock title'>图片</label><span class='widget-required'>*</span> <ul><li class='porela'></li></ul>  <div class='btn blue fr poabso'>上传图片</div>   <div class='field-description hide'></div> <i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i></div>";
					break;
				case "DataTable":
					return "<div class='field ui-state-default field-active' data-widget='DataTable'><label class='widget-title' style='display:none'>表格"+plusNum+"</label><table class='editTable' border='1'><tr><th>标题1</th><th>标题2</th><th>标题3</th></tr><tr><td><input type='text' class='form-control' placeholder='数字' disabled></td><td><input type='text' class='form-control' placeholder='数字' disabled></td><td><input type='text' class='form-control' placeholder='数字' disabled></td></tr></table><div class='field-description hide'></div><input type='hidden' name='tableRow' value='3'><input type='hidden' name='tableTotal' value='0'><span class='widget-required'></span><i class='glyphicon glyphicon-remove-circle' title='移除当前字段'></i></div>";
					break;									
				default:
					alert("缺少模块！");
					return false;					
				}
		},
		// 编辑控件
		editWidget:function(currentWidget,currentWidgetType){
			var editContainer=$(".formseditbor .scrollbor");
			editContainer.empty();
			$('#editTab a:first').tab('show');

			// 载入标题和必填项
			if(currentWidgetType!='DividingLine'&&currentWidgetType!='Paragraph'&&currentWidgetType!='DataTable'){
				var componentTitle=currentWidget.find(".widget-title").text();
				editContainer.append("<div class='borderbor of porela'><span class='porela break inblock title'>标题</span><div class='break pbc inblock rcontent'><input id='component-title' type='text' class='form-control' value='"+componentTitle+"'/></input></div>");
				editContainer.append("<div class='borderbor of porela flex required'><span class='porela break inblock title'>必填项目</span><div class='break pbc inblock rcontent'> <ul><li> <label><input id='required' type='checkbox'>这个是必填项</label> </li></ul> </div></div>");
				if(!currentWidget.find(".widget-required").is(":hidden")){
					 $("#required").attr("checked", true);
				}
			}
			// 载入单选项配置
			if(currentWidgetType=='RadioBox'){
				editContainer.append("<div class='borderbor of porela optionSet'><span class='porela break inblock title'>选项设置</span><div class='controls'><ul class='choicelistEdit choicelistEdit_js'></ul></div></div>");
				var radioNum=currentWidget.find(".radio-inline").length;
				var currentName=currentWidget.find("input").attr("name");
				for(var i=0;i<radioNum;i++){
					var radioText=currentWidget.find(".radio-inline:eq("+i+")").find("span").text();

					$(".choicelistEdit").append("<li class='RadioBox_js'><input type='radio' name='editor-option' class='option_js' value=''><input type='text' class='form-control optionName_js' value='"+radioText+"'/> <span class='setbtnbor fr'><i class='pbiconfont poabso setcutbtn pointer glyphiconplus'>&#xe646;</i><i class='pbiconfont poabso setaddbtn pointer glyphiconminus'>&#xe64a;</i></span> </li>");
					if(currentWidget.find(".radio-inline:eq("+i+")").children("input[name="+currentName+"]").prop("checked")){
						$("input[name=editor-option]:last").attr("checked",true);
					}
				}
			}

			// 载入复选框配置
			if(currentWidgetType=='CheckBox'){
				var checkedMax=currentWidget.find("input[name=checkedMax]").val();
				editContainer.append("<div class='borderbor of porela optionSet'><span class='porela break inblock title'>选项设置</span><div class='controls'><ul class='choicelistEdit choicelistEdit_js'></ul></div></div><div class='borderbor of porela'><span class='porela break inblock title'>可选数量</span><div class='break pbc inblock rcontent'><input id='checkedMax' type='text' class='form-control' value='"+checkedMax+"'/>（为空表示可全选）</div></div>");
				var checkBoxNum=currentWidget.find(".CheckBox_js").length;
				var currentName=currentWidget.find("input").attr("name");
				for(var i=0;i<checkBoxNum;i++){
					var checkBoxText=currentWidget.find(".CheckBox_js:eq("+i+")").find("span").text();

					$(".choicelistEdit").append("<li class='RadioBox_js'><input type='checkbox' name='editor-option' class='option_js fl' value=''><input type='text' class='form-control optionName_js' value='"+checkBoxText+"'/>  <span class='setbtnbor fr'><i class='pbiconfont poabso setcutbtn pointer glyphiconplus'>&#xe646;</i><i class='pbiconfont poabso setaddbtn pointer glyphiconminus'>&#xe64a;</i></span> </li>");

					if(currentWidget.find(".CheckBox_js:eq("+i+")").children("input[name="+currentName+"]").prop("checked")){
						$("input[name=editor-option]:last").attr("checked",true);
					}
				}
			}

			// 载入选择配置
			if(currentWidgetType=='Select'){
				editContainer.append("<div class='borderbor of porela optionSet'><span class='porela break inblock title'>选项设置</span><div class='controls'><ul class='choicelistEdit choicelistEdit_js'></ul></div></div>");
				var selectNum=currentWidget.find(".Select_js").length;
				for(var i=0;i<selectNum;i++){
					var selectText=currentWidget.find(".Select_js:eq("+i+")").text();

					$(".choicelistEdit").append("<li class='RadioBox_js'><input type='radio' name='editor-option' class='option_js' value=''><input type='text' class='form-control optionName_js' value='"+selectText+"' /> <span class='setbtnbor fr'><i class='pbiconfont poabso setcutbtn pointer glyphiconplus'>&#xe646;</i><i class='pbiconfont poabso setaddbtn pointer glyphiconminus'>&#xe64a;</i></span> </li>");
					if(currentWidget.find(".Select_js:eq("+i+")").prop("selected")){
						$("input[name=editor-option]:last").attr("checked",true);
					}
				}
			}

			if(currentWidgetType=='Money'){
				var moneyText=currentWidget.find(".money-type").text().replace(/[^\u4e00-\u9fa5\w]/g,"");//保留文字
				editContainer.append("<div class='borderbor of porela flex'><span class='porela break inblock title'>货币类型</span><div class='break pbc inblock rcontent'><input id='moneyType' type='text' class='form-control' value='"+moneyText+"'/></div></div>");
				
			}

			// 载入描述
			if(currentWidgetType!='DividingLine'&&currentWidgetType!='Paragraph'&&currentWidgetType!='ImageComponent'){
				editContainer.append("<div class='borderbor of porela fieldtips'><span class='porela break inblock title'>字段提示</span><textarea id='component-describe' class='pb_textareas noborder form-control' rows='3'></textarea></div>");
				$("#component-describe").val(currentWidget.find(".field-description").text());
			}

			// 载入分割线类型
			if(currentWidgetType=='DividingLine'){
				editContainer.append("<div class='form-group'><label>切换分割线类型</label><div class='controls'><div class='dividerchoicelist'><label class='ds-ib-w'><input type='radio' name='dividingLineType' value='solid'  checked='checked'><div class='divider-line solid'></div></label><label class='ds-ib-w'><input type='radio' name='dividingLineType' value='dashed'><div class='divider-line dashed'></div></label><label class='ds-ib-w'><input type='radio' name='dividingLineType' value='thicksolid'><div class='divider-line thicksolid'></div></label><label class='ds-ib-w'><input type='radio' name='dividingLineType' value='thickdashed'><div class='divider-line thickdashed'></div></label><label class='ds-ib-w'><input type='radio' name='dividingLineType' value='solid-double'><div class='divider-line solid-double'></div></label><label class='ds-ib-w'><input type='radio' name='dividingLineType' value='thicksolid-double'><div class='divider-line thicksolid-double'></div></label></div></div></div>");
					var typeNum=$(".ds-ib-w").length;
					var typeText=currentWidget.find(".divider-line").attr("class");
					var typeText=typeText.split(" ");
					for(var i=0;i<typeNum;i++){
						var dividingLineText=$(".ds-ib-w:eq("+i+")").find("input[name=dividingLineType]").val();
						if(typeText[1]==dividingLineText){
							$(".ds-ib-w:eq("+i+")").find("input[name=dividingLineType]").attr("checked",true);
						}
					}
			}

			// 描述样式类型
			if(currentWidgetType=='Paragraph'){
				editContainer.append("<div class='form-group ckeditor'><label>段落内容</label><div class='controls'><textarea name='ckeditor' id='ckeditor' rows='10' cols='80'></textarea></div></div>");

				ckeditor = CKEDITOR.replace('ckeditor');

				var paragraphContent=currentWidget.find(".alert").text();
				CKEDITOR.instances['ckeditor'].setData(paragraphContent);

				// 描述内容同步
				ckeditor.on('change',function(){
					var contentText=CKEDITOR.instances['ckeditor'].getData();
					$(".field-active .alert").empty();
					$(".field-active .alert").html(contentText);
				});
				
				editContainer.append("<div class='form-group ckeditorbg'><label>选择段落样式</label><div class='controls'><div class='paragraphchoicelist'><label class='ds-ib-w'><input type='radio' name='paragraphType' value='alert-success' checked='checked'><div class='paragraph alert alert-success'></div></label><label class='ds-ib-w'><input type='radio' name='paragraphType' value='alert-info'><div class='paragraph alert alert-info'></div></label><label class='ds-ib-w'><input type='radio' name='paragraphType' value='alert-warning'><div class='paragraph alert alert-warning'></div></label><label class='ds-ib-w'><input type='radio' name='paragraphType' value='alert-danger'><div class='paragraph alert alert-danger'></div></label></div></div></div>")
					var typeNum=$(".ds-ib-w").length;
					var typeText=currentWidget.find(".alert").attr("class");
					var typeText=typeText.split(" ");
					for(var i=0;i<typeNum;i++){
						var paragraphText=$(".ds-ib-w:eq("+i+")").find("input[name=paragraphType]").val();
						if(typeText[1]==paragraphText){
							$(".ds-ib-w:eq("+i+")").find("input[name=paragraphType]").attr("checked",true);
						}
					}
			}

			// 载入日期格式类型
			if(currentWidgetType=='DateComponent'||currentWidgetType=='DateInterval'){
				editContainer.append("<div class='borderbor of porela flex'><span class='porela break inblock title'>日期格式</span><div class='break pbc inblock rcontent'> <ul><li class='radio'> <label><input type='radio' name='dateFormat' value='yyyy-MM-dd HH:mm'>年-月-日 时:分</label> </li><li class='radio'> <label><input type='radio' name='dateFormat' value='yyyy-MM-dd HH'>年-月-日 时</label> </li> <li class='radio'><label><input type='radio' name='dateFormat' value='yyyy-MM-dd' checked='checked'>年-月-日</label></li><li class='radio'><label><input type='radio' name='dateFormat' value='yyyy-MM'>年-月</label></li></ul></div></div>");
				var currentType=currentWidget.find("input").attr("placeholder");

				var typeNum=$(".radio").length;
				//console.log(typeNum);
				for(var i=0;i<typeNum;i++){
					var dateVal=$(".radio:eq("+i+")").find("label").text();
					//console.log(dateVal);
					if(currentType==dateVal){
						$(".radio:eq("+i+")").find("input[name=dateFormat]").attr("checked",true);
					}
				}
			}
			
		},
		checkedMax:function(dataMax,radioNum,str){
			if(dataMax){
				var checkedNum=0;
				for(var i=0;i<radioNum;i++){
					if (str==""){
						if($("input[name=editor-option]:eq("+i+")").prop("checked")){
							checkedNum++;
						}
					}else{
					    if(str.find("input:eq("+i+")").prop("checked")){
							checkedNum++;
						}
					}
				}

			}
			return checkedNum;
		},
		textLength:function(currentInput,maxLength){
			 var length = maxLength;
	         var content_len = currentInput.val().length;
	         var in_len = length-content_len;
	         if(in_len <0){
	            currentInput.val(currentInput.val().substr(0,maxLength));
			    alert("超出字数限制！");	
				return false;
	         }
		},
	
		// 保存表单
		saveForm:function(){
			var formArray={};
			var field=new Array();
			 
			formArray["title"]=$(".createforms .titlebor input").val();
			//formArray["description"]=$(".form-design-wrapper .form-description").text();
			formArray["html"]=$(".createforms .scrollbor").html();

			var widgetNum=$(".field").length;
			for(var i=0;i<widgetNum;i++){
				var fieldArray={};
				var field_setting={}
				var field_dataArray=new Array();
				//获取字段类型
				var field_type=$(".field:eq("+i+")").data("widget");
				fieldArray["formtype"]=field_type;
				
				if(field_type!='DividingLine'&&field_type!='Paragraph'){
					//获取字段标题
					var field_title=$(".field:eq("+i+")").find(".widget-title").text();
					//获取必填项
					if($(".field:eq("+i+")").find(".widget-required").is(":hidden")){
						 var field_required=0;
					}
					else{
						 var field_required=1;
					}
					//获取字段描述
					var field_describe=$(".field:eq("+i+")").find(".field-description").text();
					fieldArray["title"]=field_title;
					fieldArray["required"]=field_required;
					fieldArray["describe"]=field_describe;
				}

				// 获取表格配置
				if(field_type=='DataTable'){
					var field_table={}
					var rowNum=$(".field:eq("+i+")").find("input[name=tableRow]").val();
					var totalItem=$(".field:eq("+i+")").find("input[name=tableTotal]").val();
					var cellNum=$(".field:eq("+i+")").find("th").length;
					for(var t=0;t<cellNum;t++){
						var thText=$(".field:eq("+i+")").find("th:eq("+t+")").text();
						var inputType=$(".field:eq("+i+")").find("tr:last").find("td:eq("+t+") input").attr("placeholder");
						var field_item={};
						field_item["itemTitle"]=thText;
						field_item["itemData"]=inputType;
						field_table[t]=field_item;
					}
					field_setting["rowNum"]=rowNum;
					field_setting["cellNum"]=cellNum;
					field_setting["totalItem"]=totalItem;
					field_setting["tableData"]=field_table;
					fieldArray["setting"]=field_setting;
				}

				// 获取单选项配置
				if(field_type=='RadioBox'){
					var field_radio={}
					
					var radioNum=$(".field:eq("+i+")").find(".radio-inline").length;
					for(var r=0;r<radioNum;r++){
						var radioText=$(".field:eq("+i+")").find(".radio-inline:eq("+r+") span").text();
						if($(".field:eq("+i+")").find(".radio-inline:eq("+r+") input").prop("checked")){
							var radioChecked=true;
						}
						else{
							var radioChecked=false;
						}
						var field_item={};
						field_item["itemTitle"]=radioText;
						field_item["itemData"]=radioChecked;
						field_radio[r]=field_item;
					}
					field_setting["radio"]=field_radio;
					fieldArray["setting"]=field_setting;
				}
				// 获取复选框配置
				if(field_type=='CheckBox'){
					var field_checkbox={}
					var field_checkedMax=$(".field:eq("+i+")").find("input[name=checkedMax]").val();
					var checkboxNum=$(".field:eq("+i+")").find(".CheckBox_js").length;
					for(var c=0;c<checkboxNum;c++){
						var checkboxText=$(".field:eq("+i+")").find(".CheckBox_js:eq("+c+") span").text();
						if($(".field:eq("+i+")").find(".CheckBox_js:eq("+c+") input").prop("checked")){
							var checkboxChecked=true;
						}
						else{
							var checkboxChecked=false;
						}
						var field_item={};
						field_item["itemTitle"]=checkboxText;
						field_item["itemData"]=checkboxChecked;
						field_checkbox[c]=field_item;
					}
					field_setting["checkedMax"]=field_checkedMax;
					field_setting["checkbox"]=field_checkbox;
					fieldArray["setting"]=field_setting;
				}
				// 获取下拉配置
				if(field_type=='Select'){
					var field_select={}
					var selectNum=$(".field:eq("+i+")").find(".Select_js").length;
					for(var s=0;s<selectNum;s++){
						var selectText=$(".field:eq("+i+")").find(".Select_js:eq("+s+")").text();
						if($(".field:eq("+i+")").find(".Select_js:eq("+s+")").prop("selected")){
							var selected=true;
						}
						else{
							var selected=false;
						}
						var field_item={};
						field_item["itemTitle"]=selectText;
						field_item["itemData"]=selected;
						field_select[s]=field_item;
					}
					field_setting["select"]=field_select;
					fieldArray["setting"]=field_setting;
				}
				// 获取日期配置
				if(field_type=='DateComponent'||field_type=='DateInterval'){
					var field_dateType=$(".field:eq("+i+")").find("input").attr("placeholder");
					fieldArray["dateType"]=field_dateType;
				}
				// 获取金额配置
				if(field_type=='Money'){
					var field_MoneyType=$(".field:eq("+i+")").find(".money-type").text().replace(/[()]/g,"");
					fieldArray["moneyType"]=field_MoneyType;
				}
				// 获取分割线配置
				if(field_type=='DividingLine'){
					var field_DividingLine=$(".field:eq("+i+")").find(".divider-line").attr("class");
					var field_DividingLine=field_DividingLine.split(" ");
					fieldArray["styleType"]=field_DividingLine[1];
				}
				// 获取描述配置
				if(field_type=='Paragraph'){
					var field_Paragraph=$(".field:eq("+i+")").find(".alert").attr("class");
					var field_Paragraph=field_Paragraph.split(" ");
					var field_ParagraphContent=$(".field:eq("+i+")").find(".alert").html();
					fieldArray["styleType"]=field_Paragraph[1];
					fieldArray["content"]=field_ParagraphContent;
				}

				field[i]=fieldArray;
				formArray["field"]=field;
			}
			return formArray;
		}
	}
})