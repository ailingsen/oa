/*
2015-08-19
alert重写 
isalertIdshow 是否频繁提交
outTimes  超时隐藏 时间
关联layout css3 动画
*/ 
var isalertIdshow = true, outTimes = 3000;
function alert(str){
   if(isalertIdshow == true){
    isalertIdshow = false;
    var alertId = "#alertshowloading";
    $(alertId).addClass("md-show").text(str);
    setTimeouts(alertId,outTimes);
   }
}

//超时隐藏
function setTimeouts(alertId,outTimes){
   setTimeout(function(){$(alertId).removeClass("md-show"); isalertIdshow = true;},outTimes);
}
/*end=================================================================================================================================*/



/*过滤字符*/
String.prototype.YLstringcheck = function(){
    return this.replace(/(^\s*)/g, "");
};