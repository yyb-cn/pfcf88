jQuery(function(){
	$("#Jcarry_amount").keyup(function(){
		setCarryResult()
	});
	$("#Jcarry_amount").blur(function(){
		setCarryResult()
	});
	$("#Jcarry_bank_id").change(function(){
		if($(this).val()=="other"){
			$("#Jcarry_otherbank").removeClass("hide");
			$("#Jcarry_bankSuggestNote").addClass("f_red");
			$("#Jcarry_bankSuggestNote").html("其他银行的提现时间约为3-5个工作日,建议使用推荐的银行进行提现操作。");
		}
		else{
			$("#Jcarry_otherbank").addClass("hide");
			$("#Jcarry_otherbank").val("");
			$("#Jcarry_bankSuggestNote").removeClass("f_red");
			if($(this).find("option:selected").attr("day")!=undefined)
				$("#Jcarry_bankSuggestNote").html("提现时间约为"+$(this).find("option:selected").attr("day")+"个工作日。");
			else
				$("#Jcarry_bankSuggestNote").html("提现时间约为3个工作日。");
		}
	});
	
	$("#Jcarry_otherbank").change(function(){
		$("#Jcarry_bankSuggestNote").removeClass("f_red");
		if($(this).find("option:selected").attr("day")!=undefined)
			$("#Jcarry_bankSuggestNote").html("提现时间约为"+$(this).find("option:selected").attr("day")+"个工作日。");
		else
			$("#Jcarry_bankSuggestNote").html("提现时间约为3个工作日。");
	});
	$("#Jcarry_From").submit(function(){
		if($.trim($("#Jcarry_amount").val())=="" || !$.checkNumber($("#Jcarry_amount").val()) || parseFloat($("#Jcarry_amount").val())<=0){
			$.showErr(LANG.CARRY_MONEY_NOT_TRUE,function(){
				$("#Jcarry_amount").focus();
			});
			return false;
		}
		if(parseFloat($("#Jcarry_acount_balance_res").val())<0){
			$.showErr(LANG.CARRY_MONEY_NOT_ENOUGHT,function(){
				$("#Jcarry_acount_balance_res").focus();
			});
			return false;
		}
		
		if($("#Jcarry_real_name").val()==""){
			$.showErr("请输入开户名",function(){
				$("#Jcarry_real_name").focus();
			});
			return false;
		}
		if($("#Jcarry_bank_id").val()==""){
			$.showErr(LANG.PLASE_ENTER_CARRY_BANK,function(){
				$("#Jcarry_bank_id").focus();
			});
			return false;
		}
		if($("#Jcarry_bank_id").val()=="other" && $("#Jcarry_otherbank").val()==""){
			$.showErr(LANG.PLASE_ENTER_CARRY_BANK,function(){
				$("#Jcarry_bank_id").focus();
			});
			return false;
		}
		
		if($("#Jcarry_region_lv4").val()==""){
			$.showErr("请选择开户行所在地",function(){
				$("#Jcarry_region_lv4").focus();
			});
			return false;
		}
		
		if($("#Jcarry_bankzone").val()==""){
			$.showErr("请输入开户行网点",function(){
				$("#Jcarry_bankzone").focus();
			});
			return false;
		}
		
		if($.trim($("#Jcarry_bankcard").val())==""){
			$.showErr(LANG.PLASE_ENTER_CARRY_BANK_CODE,function(){
				$("#Jcarry_bankcard").focus();
			});
			return false;
		}
		if($.trim($("#Jcarry_rebankcard").val())==""){
			$.showErr(LANG.PLASE_ENTER_CARRY_CFR_BANK_CODE,function(){
				$("#Jcarry_rebankcard").focus();
			});
			return false;
		}
		if($.trim($("#Jcarry_bankcard").val())!=$.trim($("#Jcarry_rebankcard").val())){
			$.showErr(LANG.TWO_ENTER_CARRY_BANK_CODE_ERROR,function(){
				$("#Jcarry_rebankcard").focus();
			});
			return false;
		}
		return true;
	});
});
function tips(input,msg,top,left)
{
	var tip='<div class="cashdraw_tips" style="top: '+top+'px; left:'+left+'px; display: block;"><div class="cashdraw_tip_header"></div><div class="cashdraw_tip_body_container"><div class="cashdraw_tip_body"><div class="cashdraw_tip_content">'+msg+'</div></div></div></div>';
	$("#imgtips").after(tip);
	input.onmouseout=function(){
		setTimeout(function(){
			$(".cashdraw_tips").remove()
		},500);	
	}
}

function setCarryResult(){
	var carry_amount = 0;
	var total_amount =  parseFloat($("#Jcarry_totalAmount").val());
	if ($.trim($("#Jcarry_amount").val()).length > 0) {
		if ($("#Jcarry_amount").val() == "-") {
			carry_amount = "-0";
		}
		else{
			carry_amount = parseFloat($("#Jcarry_amount").val());
		}
	}
	if(carry_amount < 0){
		$("#Jcarry_balance").html(LANG.CARRY_MONEY_NOT_TRUE);
	}
	else if(carry_amount > total_amount){
		$("#Jcarry_balance").html(LANG.CARRY_MONEY_NOT_ENOUGHT);
	}
	else if(carry_amount == 0){
		$("#Jcarry_balance").html(LANG.MIN_CARRY_MONEY);
	}
	else{
		$("#Jcarry_balance").html("");
	}
	var fee = 0;
	if(carry_amount>0&&carry_amount < 20000){
		fee = 0;
	}
	if(carry_amount>=20000&&carry_amount < 50000){
		fee = 0;
	}
	if(carry_amount >= 50000){
		fee = 0;
	}
	
	if(carry_amount + fee > total_amount){
		$("#Jcarry_balance").html(LANG.CARRY_MONEY_NOT_ENOUGHT);
	}
	
	$("#Jcarry_fee").html(foramtmoney(fee,2)+" 元");
	var realAmount = carry_amount+fee;
	$("#Jcarry_realAmount").html(foramtmoney(realAmount,2)+" 元");
	var acount_balance = total_amount-carry_amount-fee;
	$("#Jcarry_acount_balance_res").val(foramtmoney(acount_balance,2));
	$("#Jcarry_acount_balance").html(foramtmoney(acount_balance,2)+" 元");
}
