function init_dealform()
{
	//绑定副标题20个字数的限制
	$("input[name='sub_name']").bind("keyup change",function(){
		if($(this).val().length>20)
		{
			$(this).val($(this).val().substr(0,20));
		}		
	});
}
jQuery(function(){
	//绑定会员ID检测
	$("input[name='user_id']").bind("blur",function(){
		if(isNaN($(this).val())){
			alert("必须为数字");
			return false;
		}
		if($(this).val().length>0)
		{
			$.ajax({
				url:ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=load_user&id="+$(this).val(), 
				dataType:"json",
				success:function(result){
					if(result.status ==1)
					{
						if(result.user.services_fee)
							$("input[name='services_fee']").val(parseFloat(result.user.services_fee));
						else
							$("input[name='services_fee']").val("5");
					}
					else{
						alert("会员不存在");
					}
				}
			});
		}		
	});
	
	$("input[name='deal_status']").live("click",function(){
		$("#start_time_box #start_time").removeClass("require");
		$("#bad_time_box #bad_time").removeClass("require");
		$("#repay_start_time_box #repay_start_time").removeClass("require");
		switch($(this).val()){
			case "1":
				$("#start_time_box").show();
				$("#start_time_box #start_time").addClass("require");
				$("#bad_time_box").hide();
				$("#bad_info_box").hide();
				$("#repay_start_time_box").hide();
				break;
			case "3":
				$("#start_time_box").hide();
				$("#bad_time_box").show();
				$("#bad_time_box #bad_time").addClass("require");
				$("#bad_info_box").show();
				$("#repay_start_time_box").hide();
				break;
			case "4":
				$("#start_time_box").hide();
				$("#bad_time_box").hide();
				$("#bad_info_box").hide();
				$("#repay_start_time_box").show();
				$("#bad_time_box #repay_start_time").addClass("require");
				break;
			default :
				$("#start_time_box").hide();
				$("#bad_time_box").hide();
				$("#bad_info_box").hide();
				$("#repay_start_time_box").hide();
				break;
		}
	});
});
