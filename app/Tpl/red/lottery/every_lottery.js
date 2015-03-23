
            //开始时速度
var     flag2  = false,          //正在抽奖标志
      lucky2,                  //中奖号码，实际应用由后台产生
      award2                 //奖品名称
 

//开始抽奖
function start_lottery2(){
    var score_id=$("#score_id").val();
     if(score_id==''){
	  alert('请登录用户，大奖欢迎你！');
	    return false;
	 }
    $.ajax({
        url: 'index.php?ctl=lottery&act=every_lottery',
        type: "post",
        data:null,
        dataType: "json",
        timeout: 20000,
        cache: false,
        beforeSend: function(){// 提交之前
        },
        error: function(){//出错
            flag2=false;
        },
        success: function(res){//成功
            if(typeof(res.award_id)!='undefined'){
                lucky2 = res.award_id;    //中奖号码
                award2 = res.award_name;  //奖品名称
              // show_lottery();
				    alert('恭喜您获得：'+award2);
            }else{
                flag2=false;
                alert('今天已经抽奖！明天更多丰富礼品等着你来拿');
            }
        }
    });
}
