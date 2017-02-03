//¹ýÂËÆ÷-ÉÏ´«ÎÄ¼þÃû³Æ¹ýÂËÆ÷
factoryApp.filter("cut",function(){
    return function (value, wordwise, max, tail) {
        if (!value) return '';
        max = parseInt(max, 10);
        if (!max) return value;
        if (value.length <= max) return value;

        value = value.substr(0, max);
        if (wordwise) {
            var lastspace = value.lastIndexOf(' ');
            if (lastspace != -1) {
                value = value.substr(0, lastspace);
            }
        }
        return value + (tail || ' ¡­');
    };
});



//2016-4-29 杨亮 移动端过滤 表情emoji
function CheckTextBiaoqing(){
     
    var patrns = /(%uD)+/;          
    var texts = $('.borderbor[data-widget="Text"]');
    var TextAreas = $('.borderbor[data-widget="TextArea"]');
    var DataTables = $('.borderbor[data-widget="DataTable"]'); 
    if( texts.length > 0){
        for(var i=0; i<texts.length; i++){
            if ( patrns.test( escape(texts.eq(i).find("input").val()) ) ){
                alert("不允许输入系统表情");
                return false;
            }
        }
    }
    if( TextAreas.length > 0){
        for(var i=0; i<TextAreas.length; i++){
            if ( patrns.test( escape(TextAreas.eq(i).find("textarea").val()) ) ){
                alert("不允许输入系统表情");
                return false;
            }
        }
    }
    if( DataTables.length > 0){
        for(var i=0; i<DataTables.length; i++){
            var DataTablesInputs = DataTables.eq(i).find('input[placeholder="文本"]');
            for( var j=0; j<DataTablesInputs.length; j++ ){
                 if ( patrns.test( escape(DataTablesInputs.eq(j).val())) ){
                      alert("不允许输入系统表情");
                      return false;
                } 
                
            }
        }
    }
    return true;
}