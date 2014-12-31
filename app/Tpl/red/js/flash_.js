$(document).ready(function(){
var j=1;
var MyTime=false;
var fot=500;
var fin=400;
var amt=300;
var speed=5000;
var maxpic=$("#banner ul li").length;
var autostar=true;
$("#banner").find("li").each(function(i){
	
	$("#banner").find("li").eq(i).mouseover(function(){											  
		var cur=$("#bannerpic").find("div:visible").prevAll().length;
	
		var m=$("#banner").find("li").eq(i).find("img").attr("src");
		
		if(i!==cur){
			j=i;					
			$("#bannerpic").find("div:visible").fadeOut(fot,function(){
			$("#bannerpic").find("div").eq(i).fadeIn(fin);});
			current(this,"li");	
			
		}
		autostar=false;
	})
	$("#banner").find("li").eq(i).mouseout(function(){autostar=true;})
})



function current(ele,tag){
	$(ele).addClass("cur").siblings(tag).removeClass("cur");
	}	

var MyTime=setInterval(function(){
					if(autostar){
					$("#banner").find("li").eq(j).mouseover();
					autostar=true;
					$("#bannerpic div:visible").mouseover(function(){autostar=false;}).mouseout(function(){autostar=true;})
					j++;
					if(j==maxpic){j=0;}}
	 				} , speed);

})