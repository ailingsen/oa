$(function(){
    
    /*===============================================
      表单设计对象
      ===============================================*/
    $.extend({
    	showeditForm:function(){}
    });
	jQuery.showeditForm.prototype={
		// 控件DOM	
		formWidget:function(widgetType){
			switch(widgetType)
				{
				case "Text":
					return "<div class='ui-state-default borderbor of porela' data-widget='Text'><label class='widget-title porela break inblock title'>文本输入框</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><input type='text' class='form-control'><div class='field-description hide'></div></div></div>";
					break;
				case "TextArea":
					return "<div class='ui-state-default borderbor of porela' data-widget='TextArea'><label class='widget-title porela break inblock title'>多行文本框</label><span class='widget-required'>*</span><div class='widget-content'><textarea class='pb_textareas noborder'></textarea><div class='field-description hide'></div></div></div>";
					break;
				case "RadioBox":
					return "<div class='ui-state-default borderbor of porela flex' data-widget='RadioBox'><label class='widget-title porela break inblock title'>单选框</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><ul><li><label class='radio-inline'><input name='radioView' type='radio'><span>选项1</span></label></li><li><label class='radio-inline'><input name='radioView' type='radio'><span>选项2</span></label></li><li><label class='radio-inline'><input name='radioView' type='radio'><span>选项3</span></label></li></ul><div class='field-description hide'></div></div></div>";
					break;
				case "CheckBox":
					return "<div class='ui-state-default borderbor of porela flex' data-widget='CheckBox'><label class='widget-title porela break inblock title'>复选框</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><ul><li><label class='CheckBox_js'><input name='CheckBoxView' type='checkbox'><span>选项1</span></label></li><li><label class='CheckBox_js'><input name='CheckBoxView' type='checkbox'><span>选项2</span></label></li><li><label class='CheckBox_js'><input name='CheckBoxView' type='checkbox'><span>选项3</span></label></li></ul><div class='field-description hide'></div></div><input type='hidden' name='checkedMax' value=''></div>";
					break;
				case "Select":
					return "<div class='ui-state-default borderbor of porela flex' data-widget='Select'><label class='widget-title porela break inblock title'>下拉菜单</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><select class='form-control choicelist'><option class='Select_js' value='选项1'>选项1</option><option class='Select_js' value='选项2'>选项2</option><option class='Select_js' value='选项3'>选项3</option></select><div class='field-description hide'></div></div></div>";
					break;
				case "DateComponent":
					return "<div class='ui-state-default borderbor of porela' data-widget='DateComponent'><label class='widget-title porela break inblock title'>日期</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><input type='text' class='form-control' placeholder='年-月-日' readonly='readonly' onfocus='this.blur()'/><div class='field-description hide'></div></div></div>";
					break;
				case "DateInterval":
					return "<div class='ui-state-default borderbor of porela' data-widget='DateInterval'><label class='widget-title porela break inblock title'>日期区间</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><input type='text' class='form-control' placeholder='年-月-日' readonly='readonly' onfocus='this.blur()'/><span class='mr5'>——</span><input type='text' class='form-control' placeholder='年-月-日' readonly='readonly' onfocus='this.blur()' /><div class='field-description hide'></div></div></div>";
					break;		
				case "NumberComponent":
					return "<div class='ui-state-default borderbor of porela' data-widget='NumberComponent'><label class='widget-title porela break inblock title'>数字输入框</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><input type='text' class='form-control'><div class='field-description hide'></div></div></div>";
					break;
				case "Money":
					return "<div class='ui-state-default borderbor of porela' data-widget='Money'><label class='widget-title porela break inblock title'>金额</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'> <input type='text' class='form-control'> <span class='money-type fr'>(人民币)</span><div class='field-description hide'></div></div></div>";
					break;
				case "Employee":
					return "<div class='ui-state-default borderbor porela of' data-widget='Employee'><label class='widget-title porela break inblock title'>用户选择</label><span class='widget-required'>*</span> <div class='poabso addbtn1 fpgbtn' ng-click='addgroup($event,1)'></div>  <div class='break pbc inblock rcontent'><ul></ul></div>   <div class='field-description hide'></div></div>";
					break;
				case "Department":
					return "<div class='ui-state-default borderbor porela of' data-widget='Department'><label class='widget-title porela break inblock title'>部门选择</label><span class='widget-required'>*</span> <div class='poabso addbtn1 fpgbtn' ng-click='addgroup($event,0)'></div> <div class='break pbc inblock rcontent'><ul></ul></div>   <div class='field-description hide'></div></div>";
					break;
				case "DividingLine":
					return "<div class='ui-state-default borderbor of porela' data-widget='DividingLine'><div class='divider-line solid'></div><span class='widget-required'></span></div>";
					break;	
				case "Paragraph":
					return "<div class='ui-state-default borderbor of porela' data-widget='Paragraph'><div class='alert alert-success' role='alert'>空白段落</div><span class='widget-required'></span></div>";
					break;
				case "Email":
					return "<div class='ui-state-default borderbor of porela' data-widget='Email'><label class='widget-title porela break inblock title'>邮箱</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><input type='text' class='form-control'><div class='field-description hide'></div></div></div>";
					break;
				case "Phone":
					return "<div class='ui-state-default borderbor of porela' data-widget='Phone'><label class='widget-title porela break inblock title'>电话</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><input type='text' class='form-control'><div class='field-description hide'></div></div></div>";
					break;
				case "Mobile":
					return "<div class='ui-state-default borderbor of porela' data-widget='Mobile'><label class='widget-title porela break inblock title'>手机</label><span class='widget-required'>*</span><div class='widget-content break pbc inblock rcontent'><input type='text' class='form-control'><div class='field-description hide'></div></div></div>";
					break;
				case "FileComponent":
					return "<div class='ui-state-default borderbor of porela ' data-widget='FileComponent'><label class='widget-title porela break inblock title'>附件</label><span class='widget-required'>*</span> <ul></ul><div class='none'>0</div>   <div class='btn blue fr poabso'></div>   <div class='field-description hide'></div> </div>";
					break;
				case "ImageComponent":
					return "<div class='ui-state-default borderbor of porela ' data-widget='ImageComponent'><label class='widget-title porela break inblock title'>图片</label><span class='widget-required'>*</span> <ul></ul><div class='none'>0</div>  <div class='btn blue fr poabso'></div>   <div class='field-description hide'></div> </div>";
					break;								
				default:
					alert("缺少模块！");
					return false;					
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
		// 保存表单
		// 保存表单
		saveForm:function(){
			var formArray={};
			var field=new Array();
			
			
			formArray["title"]=$("#formpresentation_edit .formtitles").text(); 
            
            var maxid = $("#formpresentation_edit .scrollbor .borderbor");
      
			var widgetNum=$("#formpresentation_edit .scrollbor .borderbor").length;
			for(var i=0;i<widgetNum;i++){
				var fieldArray={};
				var field_setting={}
				var field_dataArray=new Array();
				//获取字段类型
				var field_type=maxid.eq(i).data("widget");
				fieldArray["formtype"]=field_type;
				
				if(field_type!='DividingLine'&&field_type!='Paragraph'){
					//获取字段标题
					var field_title=maxid.eq(i).find(".widget-title").text();
					//获取必填项
					if(maxid.eq(i).find(".widget-required").is(":hidden")){
						 var field_required=0;
					}
					else{
						 var field_required=1;
					}
					//获取字段描述
					var field_describe=maxid.eq(i).find(".field-description").text();
					var field_value=maxid.eq(i).find("textarea").val();

					var field_value;
					if(field_type=='TextArea'){
						field_value=maxid.eq(i).find("textarea").val();
					}else{
						field_value=maxid.eq(i).find("input").val();
					}

					fieldArray["title"]=field_title;
					fieldArray["required"]=field_required;
					fieldArray["describe"]=field_describe;
					 
                    if(field_type=="TextArea"){
						field_value=field_value.replace(/\n+/g,"<br/>");
						field_value=field_value.replace(/\s+/g,"&nbsp;");
					}
					fieldArray["inputvalue"]=field_value;
					 
				}

				// 获取表格配置
				if(field_type=='DataTable'){
					var field_table={}
					var rowNum=maxid.eq(i).find("input[name=tableRow]").val();
					var totalItem=maxid.eq(i).find("input[name=tableTotal]").val();
					var cellNum=maxid.eq(i).find("th").length;
					var countNum = maxid.eq(i).find(".hcounts").html();
					for(var t=0;t<cellNum;t++){
						var thText=maxid.eq(i).find("th:eq("+t+")").text();
						var inputType=maxid.eq(i).find("tr:eq(1)").find("td:eq("+t+") input").attr("placeholder");
						var field_item={};
						field_item["itemTitle"]=thText;
						field_item["itemData"]=inputType;
						field_table[t]=field_item;
					}
					
					var rowsNum=$(".borderbor:eq("+i+") tr:gt(0):not(:last)").length;
					var field_item_tr={};
					
					for(var tr=0;tr<rowsNum;tr++){
					    var field_item_form={};
					    var trhang = maxid.eq(i).find("tr:eq("+(tr+1)+")");	
						for(var td=0; td<cellNum; td++){
							var trText=trhang.find("td:eq("+td+") input").val();
						    field_item_form[td]=trText;
						}
						field_item_tr[tr]=field_item_form;
					}
					
					field_setting["rowNum"]=rowNum;
					field_setting["cellNum"]=cellNum;
					field_setting["totalItem"]=totalItem;
					field_setting["tableData"]=field_table;
					field_setting["table"]=field_item_tr;
					field_setting["countNum"]=countNum;
					fieldArray["setting"]=field_setting;
				}


				// 获取单选项配置
				if(field_type=='RadioBox'){
					var field_radio={}
					
					var radioNum=maxid.eq(i).find(".radio-inline").length;
					for(var r=0;r<radioNum;r++){
						var radioText=maxid.eq(i).find(".radio-inline:eq("+r+") span").text();
						if(maxid.eq(i).find(".radio-inline:eq("+r+") input").prop("checked")){
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
					var field_checkedMax=maxid.eq(i).find("input[name=checkedMax]").val();
					var checkboxNum=maxid.eq(i).find(".CheckBox_js").length;
					for(var c=0;c<checkboxNum;c++){
						var checkboxText=maxid.eq(i).find(".CheckBox_js:eq("+c+") span").text();
						if(maxid.eq(i).find(".CheckBox_js:eq("+c+") input").prop("checked")){
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
					var selectNum=maxid.eq(i).find(".Select_js").length;
					for(var s=0;s<selectNum;s++){
						var selectText=maxid.eq(i).find(".Select_js:eq("+s+")").text();
						if(maxid.eq(i).find(".Select_js:eq("+s+")").prop("selected")){
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
					var field_dateType=maxid.eq(i).find("input").attr("placeholder");
					fieldArray["dateType"]=field_dateType;
					if (maxid.eq(i).find(".form-control").length==2){
					    fieldArray["dateTimeEnd"]=maxid.eq(i).find(".form-control").eq(1).val();
					}
				}
				// 获取金额配置
				if(field_type=='Money'){
					var field_MoneyType=maxid.eq(i).find(".money-type").text().replace(/[()]/g,"");
					fieldArray["moneyType"]=field_MoneyType;
				}
				// 获取分割线配置
				if(field_type=='DividingLine'){
					var field_DividingLine=maxid.eq(i).find(".divider-line").attr("class");
					var field_DividingLine=field_DividingLine.split(" ");
					fieldArray["styleType"]=field_DividingLine[1];
				}
				// 获取描述配置
				if(field_type=='Paragraph'){
 					var field_Paragraph=maxid.eq(i).find(".alert").attr("class");
					var field_Paragraph=field_Paragraph.split(" ");
					if (maxid.eq(i).find(".alert #ckeditor")){
					   // var field_ParagraphContent=maxid.eq(i).find(".alert textarea").val();
					    var field_ParagraphContent=maxid.eq(i).find(".alert").html();
					}else{
					    var field_ParagraphContent=maxid.eq(i).find(".alert").html();
					}
					fieldArray["styleType"]=field_Paragraph[1];
					fieldArray["content"]=field_ParagraphContent;
				}
				
				
				// 获取用户选择
				if(field_type=='Employee' || field_type=='Department'){
					var field_Employee={}
					var checkboxNum=maxid.eq(i).find("ul li").length;
					for(var em=0;em<checkboxNum;em++){
						var EmployeeName=maxid.eq(i).find("ul li:eq("+em+") .filename").html();
						var EmployeeId=maxid.eq(i).find("ul li:eq("+em+") .filename").attr("data-member_id");
						 
						var field_item={};
						field_item["itemName"]=EmployeeName;
						field_item["itemId"]=EmployeeId;
						field_Employee[em]=field_item;
					}
					field_setting["Employee"]=field_Employee;
					fieldArray["setting"]=field_setting;
				}
				
				
				//获取图片
				if(field_type=='ImageComponent'){
				    var field_ImageComponent={}
					var ImageComponentNum=maxid.eq(i).find("li").length;
					for(var img=0;img<ImageComponentNum;img++){
						//var ImageComponentImg='<div class="images_zone">'+maxid.eq(i).find(".images_zone").eq(img).html()+'</div>';
					    var ImageComponentImg = maxid.eq(i).find("li").eq(img).html();
					    var field_item={};
						field_item["itemImgs"]=ImageComponentImg;
						field_ImageComponent[img] = field_item;
					}
					field_setting["ImageComponent"]=field_ImageComponent;
					fieldArray["setting"]=field_setting;
				}
				
				//获取文件
				if(field_type=="FileComponent"){
				    var field_FileComponent={}
					var FileComponentNum=maxid.eq(i).find("li").length;
					for(var imgf=0;imgf<FileComponentNum;imgf++){
						//var FileComponentImg='<div class="files_zone">'+maxid.eq(i).find(".files_zone").eq(imgf).html()+'</div>';
					    var FileComponentImg = maxid.eq(i).find("li").eq(imgf).html();
					    var field_item={};
						field_item["FileComponent"]=FileComponentImg;
						field_FileComponent[imgf] = field_item;
					}
					field_setting["FileComponent"]=field_FileComponent;
					fieldArray["setting"]=field_setting;
				}

				field[i]=fieldArray;
				formArray["field"]=field;
				
			}
			return formArray; 
		}
	};

});