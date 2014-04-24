function account(user_id)
{
	$.weeboxs.open(ROOT+'?m=User&a=account&id='+user_id, {contentType:'ajax',showButton:false,title:LANG['USER_ACCOUNT'],width:600,height:260});
}
function account_detail(user_id)
{
	location.href = ROOT + '?m=User&a=account_detail&id='+user_id;
}
function user_work(user_id)
{
	$.weeboxs.open(ROOT+'?m=User&a=work&id='+user_id, {contentType:'ajax',showButton:false,title:LANG['USER_WORK'],width:600,height:400});
}
function user_passed(user_id)
{
	window.location.href = ROOT+'?m=User&a=passed&id='+user_id;
	/*$.weeboxs.open(ROOT+'?m=User&a=passed&id='+user_id, {contentType:'ajax',showButton:false,title:LANG['USER_PASSED'],width:600,height:400});*/
}
function eidt_lock_money(user_id){
	$.weeboxs.open(ROOT+'?m=User&a=lock_money&id='+user_id, {contentType:'ajax',showButton:false,title:LANG['USER_LOCK_MONEY'],width:600,height:400});
}

function info_down(user_id){
	$.weeboxs.open(ROOT+'?m=User&a=info_down&id='+user_id, {contentType:'ajax',showButton:false,title:"资料",width:600,height:400});
}
//在后台直接登录前台用户 tagged by lu
function login_site(user_id)
{
	window.open(ROOT+'?m=User&a=login_site&id='+user_id);
}
