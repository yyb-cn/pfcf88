// JavaScript Document

var beginDay = 7;
$(document).ready(function(){
	$(".denglu_main").height($(".denglu_main1").outerHeight());
	$(".guanggao span").click(function(){
		$(".guanggao").hide();
	});
	//进度条
	$(".bianhao_left table td i").each(function(){
		var n=$(this).text();
		$(this).siblings().find(".jindu1").css("width",n);
	});
	// 合作伙伴
	var h1 = 136;
	var n = 0;
	var hh = $(".hezuo_sun").width();
	$(".hezuo_right").click(function() {
	$(".hezuo_left").removeClass("on");
	n = n + 1;
	n1 = n * h1 + hh;
	n2 = -(n * h1);
	var m = $(".hezuo_sun ul li").length;
	n11 = (m - 7) * h1 + hh;
	n22 = -(m - 7) * h1;
	if (n + 7 >= m) {
	n = m - 7;
	$(".hezuo_sun ul").animate({
	width : n11,
	marginLeft : n22
	}, 1000);
	$(this).addClass("on1");
	}
	if (n + 7 < m) {
	$(".hezuo_sun ul").animate({
	width : n1,
	marginLeft : n2
	}, 1000);
	$(".hezuo_left").removeClass("on");
	}
	});
	$(".hezuo_left").click(function() {
	$(".hezuo_right").removeClass("on1");
	n3 = -(n - 1) * h1;
	n4 = (n - 1) * h1 + hh;
	n = n - 1;
	if (n <= 0) {
	$(".hezuo_sun ul").animate({
	width : hh,
	marginLeft : 0
	}, 1000);
	n = 0;
	$(this).addClass("on");
	}
	if (n > 0) {
	$(".hezuo_sun ul").animate({
	width : n4,
	marginLeft : n3
	}, 1000);
	$(".hezuo_right").removeClass("on1");
	}
	});
// qq
	$(".left_top ul li.qq").hover(function(){
		$(this).addClass("qq1");
		$(this).children("em").addClass("on");
		$(this).children("ul").show();

	},function(){
		$(this).removeClass("qq1");
		$(this).children("em").removeClass("on");
		$(this).children("ul").hide();
	})
	$(".left_top ul li ul.kefu").hover(function(){
		$(this).show();
	},function(){
		$(this).hide();
	});

	//weixin
	$(".weixin").hover(function(){ 
		$(this).attr("src",ctx+"/image/weixin1.png")
	},function(){
		$(this).attr("src",ctx+"/image/weixin.png")
	});
	$(".weibo").hover(function(){
		$(this).attr("src",ctx+"/image/weibo1.png")
	},function(){
		$(this).attr("src",ctx+"/image/weibo.png")
	});
	//计算器
	$(".dhb_top h2 span.jsq").click(function(){
		$(this).parents(".dhb_top").siblings(".jsq1").show();
	});
	$(".jsq1 h2 span").click(function(){
		$(this).parents(".jsq1").hide();
	});
	$(".jsq1 input.js").hover(function(){
		$(this).css("background","#d94130");
	},function(){
			$(this).css("background","#E02D21");
	});
	$(".jsq1 input.cz").hover(function(){
		$(this).css("background","#383e45");
	},function(){
			$(this).css("background","#30373E");
	});

	//滚动
	var nn=$("#ygHotResources table").eq(0).find("tr").length;
	var speed=70;
	var demo=document.getElementById("ygHotResources");
	var demo2=document.getElementById("hotDemo2");
	var demo1=document.getElementById("hotDemo1");
	if (nn>=5){
		$("#hotDemo2").html($("#hotDemo1").html());
		function Marquee(){
		if(demo2.offsetHeight-demo.scrollTop<=0)
		demo.scrollTop=demo.scrollTop-demo1.offsetHeight
		else{
		demo.scrollTop++
		}
		}
		var MyMar=setInterval(Marquee,speed);
		demo.onmouseover=function() {clearInterval(MyMar)}
		demo.onmouseout=function() {MyMar=setInterval(Marquee,speed)} 
	}

	var nn2=$("#ygHotResources1 table").eq(0).find("tr").length;
	var speed1=70;
	var demo1=document.getElementById("ygHotResources1");
	var demo21=document.getElementById("hotDemo21");
	var demo11=document.getElementById("hotDemo11");
	if (nn2>=5) {
		$("#hotDemo21").html($("#hotDemo11").html());
		function Marquee1(){
		if(demo21.offsetHeight-demo1.scrollTop<=0)
		demo1.scrollTop=demo1.scrollTop-demo11.offsetHeight
		else{
		demo1.scrollTop++
		}
		}
		var MyMar1=setInterval(Marquee1,speed1)
		demo1.onmouseover=function() {clearInterval(MyMar1)}
		demo1.onmouseout=function() {MyMar1=setInterval(Marquee1,speed1)} 
	}
	
	var nn3=$("#ygHotResources2 table").eq(0).find("tr").length;
	var speed2=70;
	var demo2=document.getElementById("ygHotResources2");
	var demo22=document.getElementById("hotDemo22");
	var demo12=document.getElementById("hotDemo12");
	if (nn3>=5) {
		$("#hotDemo22").html($("#hotDemo12").html());
		function Marquee2(){
		if(demo22.offsetHeight-demo2.scrollTop<=0)
		demo2.scrollTop=demo2.scrollTop-demo12.offsetHeight
		else{
		demo2.scrollTop++
		}
		}
		var MyMar2=setInterval(Marquee2,speed2)
		demo2.onmouseover=function() {clearInterval(MyMar2)}
		demo2.onmouseout=function() {MyMar2=setInterval(Marquee2,speed2)} 
	}

	// nav
	$(".head_main>ul>li>a").each(function(){
		var n=$(this).width();
		$(this).siblings().find("li").css("width",n);
	});
	$(".head_main ul li ul li").hover(function(){
		$(this).css("background","#f9f6f7");
	},function(){
		$(this).css("background","#fff");
	});
	$(".head_main>ul>li>a").hover(function(){
		$(this).siblings("ul").show();
	},function(){
		$(this).siblings("ul").hide();
	});
	$(".head_main>ul>li>ul").hover(function(){
		$(this).show();
	},function(){
		$(this).hide();
	});
	$(".head_main>ul>li.onn").hover(function(){
		$(this).addClass("onn1");
	},function(){
		$(this).removeClass("onn1");
	});
});

/**
 * 登录验证
 */
function validateLogin(failNum) {
	if ($.trim($("#j_username").val()) == "") {
		$("#errorTip").text("请输入用户名");
		return false;
	}
	var parten = /^[a-zA-Z0-9_\-]*$/;
	if (!parten.test($("#j_username").val())) {
		$("#errorTip").text("用户名格式不正确");
		return false;
	}
	if ($.trim($("#j_password").val()) == "") {
		$("#errorTip").text("请输入密码");
		return false;
	}
	if(Number(failNum)>=3){
	if ($.trim($("#j_captcha").val()) == "") {
		$("#errorTip").text("请输入验证码");
		return false;
	}
	}
	return true;
}

/**
 * 首页登录
 */
function doSubmit_index(failNum) {
	if (validateLogin(failNum)) {
		var username = $("#j_username").val();
		$("#username").val(username + "$30");
		return true;
	}
	return false;
}

/**
 * 刷新验证码
 */
function refreshCaptcha() {
	$("#captchaImg").hide().attr("src",
		ctx + "/hy/jcaptcha.xjpg?" + Math.floor(Math.random() * 100)).fadeIn();
}

function shorttitle(e, t) {
	e.length > t ? document.write(e.substr(0, t) + "...") : document
			.write(e);
}

/**
 * 月盈宝计算器
 */
function calcyyb_do() {
	var calcyyb_amount = $("#calcyyb_amount").val();
	var calcyyb_start = $("#calcyyb_start").val();
	var calcyyb_end = $("#calcyyb_end").val();
	var calcyyb_lilv = $("#calcyyb_lilv").val();
	if (calcyyb_amount == "" || escape(calcyyb_amount).indexOf("%u") >= 0 || 
		calcyyb_lilv == "" || escape(calcyyb_lilv).indexOf("%u") >= 0 || 
		calcyyb_start == "" || calcyyb_end == "") {
		$("#calcyyb_total1").text("0");
		$("#calcyyb_total2").text("0");
		return;
	}
	var days = DateDiff(calcyyb_end, calcyyb_start);
	if (days < 0) {
		$("#calcyyb_total1").text("0");
		$("#calcyyb_total2").text("0");
		return;
	}
	var total1 = Number(calcyyb_amount * (calcyyb_lilv / 36500) * (days + 1));
	var total2 = Number(calcyyb_amount) + Number(total1);
	$("#calcyyb_total1").text(total1.toFixed(2));
	$("#calcyyb_total2").text(total2.toFixed(2));
}

/**
 * 定活宝计算器
 */
function calcdhb_do() {
	var calcdhb_amount = $("#calcdhb_amount").val();
	var calcdhb_start = $("#calcdhb_start").val();
	var calcdhb_end = $("#calcdhb_end").val();
	var calcdhb_lilv = $("#calcdhb_lilv").val();
	if (calcdhb_amount == "" || escape(calcdhb_amount).indexOf("%u") >= 0 || 
		calcdhb_lilv == "" || escape(calcdhb_lilv).indexOf("%u") >= 0 || 
		calcdhb_start == "" || calcdhb_end == "") {
		$("#calcdhb_total1").text("0");
		$("#calcdhb_total2").text("0");
		return;
	}
	var days = DateDiff(calcdhb_end, calcdhb_start);
	if (days < 0) {
		$("#calcdhb_total1").text("0");
		$("#calcdhb_total2").text("0");
		return;
	}
	days++;
	if ($("#isfutou").is(":checked") && days >= beginDay) {
		var total2 = calcdhb_amount;
		while (days > 0) {
			total2 = dhbFutouCalc(total2, calcdhb_lilv, days);
			days -= 7;
		}
		var total1 = total2 - calcdhb_amount;
		$("#calcdhb_total1").text(total1.toFixed(2));
		$("#calcdhb_total2").text(total2.toFixed(2));
	} else {
		var total1 = Number(calcdhb_amount * (calcdhb_lilv / 36500) * days);
		var total2 = Number(calcdhb_amount) + Number(total1);
		$("#calcdhb_total1").text(total1.toFixed(2));
		$("#calcdhb_total2").text(total2.toFixed(2));
	}
}

function dhbFutouCalc(amount, lilv, days) {
	var lixi;
	if (days > beginDay) {
		lixi = Number(amount * (lilv / 36500) * beginDay);
	} else {
		lixi = Number(amount * (lilv / 36500) * days);
	}
	return Number(amount) + lixi;
}

function calcyyb_reset(lilv) {
	$("#calcyyb_amount").val("");
	$("#calcyyb_start").val("");
	$("#calcyyb_end").val("");
	$("#calcyyb_lilv").val(lilv);
	$("#calcyyb_total1").text("0");
	$("#calcyyb_total2").text("0");
}

function calcdhb_reset(lilv) {
	$("#calcdhb_amount").val("");
	$("#calcdhb_start").val("");
	$("#calcdhb_end").val("");
	$("#calcdhb_lilv").val(lilv);
	$("#isfutou").attr("checked",false);
	$("#calcdhb_total1").text("0");
	$("#calcdhb_total2").text("0");
}

function pingji(v) {
	var t = "";
	if (v=="1-3")t="AAA";
	else if (v=="1-2")t="AA";
	else if (v=="1-1")t="A";
	else if (v=="2-3")t="BBB";
	else if (v=="2-2")t="BB";
	else if (v=="2-1")t="B";
	else if (v=="3-3")t="CCC";
	else if (v=="3-2")t="CC";
	else if (v=="3-1")t="C";
	document.write(t);
}

function shortname(e, t) {
	if (e.length > 3) {
		$("#"+t).html(e.substr(0, 2) + "***" + e.substr(e.length - 1, e.length));
	}
}