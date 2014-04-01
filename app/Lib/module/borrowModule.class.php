<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------
if($_REQUEST['act']!="aboutborrow")
	require APP_ROOT_PATH.'app/Lib/uc.php';
class borrowModule extends SiteBaseModule
{
    function index() {
    	$GLOBALS['tmpl']->assign('page_title',$GLOBALS['lang']['APPLY_BORROW']);
    	//判断是否有借款
    	if(floatval($GLOBALS['user_info']['quota']) > 0||$GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal WHERE user_id=".intval($GLOBALS['user_info']['id'])) > 0){
    		$GLOBALS['tmpl']->assign('disable_apply',1);
    	}
    	$GLOBALS['tmpl']->display("page/borrow.html");
    }
    
    function stepone() {
    	//检查是否有发布的但是未确认投标的标
    	if($GLOBALS['db']->getOne("SELECT * FROM ".DB_PREFIX."deal WHERE is_delete=0 AND start_time=0 AND deal_status=0 AND user_id=".$GLOBALS['user_info']['id']) > 0){
    		app_redirect(url("index","borrow#steptwo"));
    	}
    	if($GLOBALS['db']->getOne("SELECT * FROM ".DB_PREFIX."deal WHERE is_delete=0 AND (start_time + enddate*24*3600) > ".get_gmtime()." AND deal_status=0 AND user_id=".$GLOBALS['user_info']['id']) > 0){
    		app_redirect(url("index","borrow#steptree"));
    	}
    	
    	$GLOBALS['tmpl']->assign('step',1);
    	$GLOBALS['tmpl']->assign('page_title',$GLOBALS['lang']['APPLY_BORROW']);
    	
    	$user_statics = sys_user_status($GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign("user_statics",$user_statics);
		
    	$work_count= $GLOBALS['db']->getOne("select count(*) FROM ".DB_PREFIX."user_work WHERE user_id=".$GLOBALS['user_info']['id']);
    	//判断用户信息是否输入完整
    	if(!isset($_REQUEST['status'])){
	    	if(
	    		$GLOBALS['user_info']['real_name'] == ""
	    		||
	    		$GLOBALS['user_info']['idno'] == ""
	    		||
	    		$GLOBALS['user_info']['mobile'] == ""
	    		||
	    		$GLOBALS['user_info']['marriage'] == ""
	    		||
	    		$GLOBALS['user_info']['address'] == ""
	    	){
	    		$_REQUEST['status'] = 1;
	    	}
	    	elseif($work_count==0){
	    		$_REQUEST['status'] = 2;
	    	}
	    	else{
	    		$_REQUEST['status'] = 3;
	    	}
    	}
    	
    	$status = intval($_REQUEST['status']);
    	switch($status){
    		case 1 :
	    		$inc_file =  "inc/borrow/stepone_u_info.html";
	    		//扩展字段
				$field_list = load_auto_cache("user_field_list");
				
				foreach($field_list as $k=>$v)
				{
					$field_list[$k]['value'] = $GLOBALS['db']->getOne("select value from ".DB_PREFIX."user_extend where user_id=".$GLOBALS['user_info']['id']." and field_id=".$v['id']);
				}
				
				$GLOBALS['tmpl']->assign("field_list",$field_list);
				
				//地区列表
		
				$region_lv2 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where region_level = 2");  //二级地址
				foreach($region_lv2 as $k=>$v)
				{
					if($v['id'] == intval($GLOBALS['user_info']['province_id']))
					{
						$region_lv2[$k]['selected'] = 1;
						break;
					}
				}
				$GLOBALS['tmpl']->assign("region_lv2",$region_lv2);
				
				$region_lv3 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where pid = ".intval($GLOBALS['user_info']['province_id']));  //三级地址
				foreach($region_lv3 as $k=>$v)
				{
					if($v['id'] == intval($GLOBALS['user_info']['city_id']))
					{
						$region_lv3[$k]['selected'] = 1;
						break;
					}
				}
				$GLOBALS['tmpl']->assign("region_lv3",$region_lv3);
				
				$n_region_lv3 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where pid = ".intval($GLOBALS['user_info']['n_province_id']));  //三级地址
				foreach($n_region_lv3 as $k=>$v)
				{
					if($v['id'] == intval($GLOBALS['user_info']['n_city_id']))
					{
						$n_region_lv3[$k]['selected'] = 1;
						break;
					}
				}
				$GLOBALS['tmpl']->assign("n_region_lv3",$n_region_lv3);
												
	    		break;
    		case 2 :
	    		$inc_file =  "inc/borrow/stepone_work_info.html";
	    		
	    		//地区列表
				$work =  $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_work where user_id =".$GLOBALS['user_info']['id']);
				$GLOBALS['tmpl']->assign("work",$work);
				
				$region_lv2 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where region_level = 2");  //二级地址
				foreach($region_lv2 as $k=>$v)
				{
					if($v['id'] == intval($work['province_id']))
					{
						$region_lv2[$k]['selected'] = 1;
						break;
					}
				}
				$GLOBALS['tmpl']->assign("region_lv2",$region_lv2);
				
				$region_lv3 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where pid = ".intval($GLOBALS['user_info']['province_id']));  //三级地址
				foreach($region_lv3 as $k=>$v)
				{
					if($v['id'] == intval($work['city_id']))
					{
						$region_lv3[$k]['selected'] = 1;
						break;
					}
				}
				$GLOBALS['tmpl']->assign("region_lv3",$region_lv3);
	    		break;
    		case 3 :
	    		$inc_file =  "inc/borrow/stepone_deal_info.html";
	    		$loan_type_list = load_auto_cache("deal_loan_type_list");
	    		$GLOBALS['tmpl']->assign("loan_type_list",$loan_type_list);
	    		
				$level = $GLOBALS['db']->getOneCached("SELECT name FROM ".DB_PREFIX."user_level WHERE id=".intval($GLOBALS['user_info']['level_id']));
				$GLOBALS['tmpl']->assign("level",$level);
	    		
				$level_list = load_auto_cache("level");
				$GLOBALS['tmpl']->assign("level_list",$level_list);
				//可用额度
				$can_use_quota=get_can_use_quota($GLOBALS['user_info']['id']);
				$GLOBALS['tmpl']->assign('can_use_quota',$can_use_quota);
				
				$deal = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal WHERE is_delete=2 AND user_id=".$GLOBALS['user_info']['id']);
				$GLOBALS['tmpl']->assign("deal",$deal);
				
	    		break;
    		default :
	    		//扩展字段
				$field_list = load_auto_cache("user_field_list");
				
				foreach($field_list as $k=>$v)
				{
					$field_list[$k]['value'] = $GLOBALS['db']->getOne("select value from ".DB_PREFIX."user_extend where user_id=".$GLOBALS['user_info']['id']." and field_id=".$v['id']);
				}
				
				$GLOBALS['tmpl']->assign("field_list",$field_list);
				
				//地区列表
		
				$region_lv2 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where region_level = 2");  //二级地址
				foreach($region_lv2 as $k=>$v)
				{
					if($v['id'] == intval($GLOBALS['user_info']['province_id']))
					{
						$region_lv2[$k]['selected'] = 1;
						break;
					}
				}
				$GLOBALS['tmpl']->assign("region_lv2",$region_lv2);
				
				$region_lv3 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where pid = ".intval($GLOBALS['user_info']['province_id']));  //三级地址
				foreach($region_lv3 as $k=>$v)
				{
					if($v['id'] == intval($GLOBALS['user_info']['city_id']))
					{
						$region_lv3[$k]['selected'] = 1;
						break;
					}
				}
				$GLOBALS['tmpl']->assign("region_lv3",$region_lv3);
				
				$n_region_lv3 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where pid = ".intval($GLOBALS['user_info']['n_province_id']));  //三级地址
				foreach($n_region_lv3 as $k=>$v)
				{
					if($v['id'] == intval($GLOBALS['user_info']['n_city_id']))
					{
						$n_region_lv3[$k]['selected'] = 1;
						break;
					}
				}
				$GLOBALS['tmpl']->assign("n_region_lv3",$n_region_lv3);
	    		break;
    	}
    	
    	$GLOBALS['tmpl']->assign('inc_file',$inc_file);
    	$GLOBALS['tmpl']->assign('status',$status);
    	$GLOBALS['tmpl']->assign('work_count',$work_count);
    	$GLOBALS['tmpl']->display("page/borrow_step.html");
    }
    
    function savedeal(){
    	$is_ajax = intval($_REQUEST['is_ajax']);
    	
    	if(!$GLOBALS['user_info']){
    		showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$is_ajax);
    	}
    	$t = trim($_REQUEST['t']);
    	
    	if(!in_array($t,array("save","publish"))){
    		showErr($GLOBALS['lang']['ERROR_TITLE'],$is_ajax);
    	}
    	
    	if($t=="save")
    		$data['is_delete'] = 2;
    	else
    		$data['is_delete'] = 0;
    	
    	$data['name'] = htmlspecialchars(trim($_REQUEST['borrowtitle']));
    	if(empty($data['name'])){
    		showErr("请输入借款标题",$is_ajax);
    	}
    	$data['publish_wait'] = 1;
    	$icon_type = trim($_REQUEST['imgtype']);
    	if($icon_type==""){
    		showErr("请选择借款图片类型",$is_ajax);
    	}
    	$icon_type_arr = array(
    		'upload' =>1,
    		'userImg' =>2,
    		'systemImg' =>3,
    	);
    	$data['icon_type'] = $icon_type_arr[$icon_type];
    	if(intval($data['icon_type'])==0)
    	{
    		showErr("请选择借款图片类型",$is_ajax);
    	}
    	
    	switch($data['icon_type']){
    		case 1 :
    			if(trim($_REQUEST['icon'])==''){
    				showErr("请上传图片",$is_ajax);
    			}
    			else{
    				$data['icon'] = str_replace(get_domain().APP_ROOT,".",trim($_REQUEST['icon']));
    			}
    			break;
    		case 2 :
    			$data['icon'] = str_replace(get_domain().APP_ROOT,".",get_user_avatar($GLOBALS['user_info']['id'],'big'));
    			break;
    		case 3 :
    			if(intval($_REQUEST['systemimgpath'])==0){
    				showErr("请选择系统图片",$is_ajax);
    			}
    			else{
    				$data['icon'] = $GLOBALS['db']->getOne("SELECT icon FROM ".DB_PREFIX."deal_loan_type WHERE id=".intval($_REQUEST['systemimgpath']));
    			}
    			break;
    	}
    	
    	$data['type_id'] = intval($_REQUEST['borrowtype']);
    	if($data['type_id']==0){
    		showErr("请选择借款用途",$is_ajax);
    	}
    	
    	$data['borrow_amount'] = floatval($_REQUEST['borrowamount']);
    	
    	if($data['borrow_amount'] < (int)trim(app_conf('MIN_BORROW_QUOTA')) || $data['borrow_amount'] > (int)trim(app_conf('MAX_BORROW_QUOTA')) || $data['borrow_amount'] %50 != 0){
    		showErr("请正确输入借款金额",$is_ajax);
    	}
    	
    	if(intval($GLOBALS['user_info']['quota']) > 0){
    		$can_use_quota = get_can_use_quota($GLOBALS['user_info']['id']);
    		if($data['borrow_amount'] > intval($can_use_quota)){
    			showErr("输入借款的借款金额超过您的可用额度<br>您当前可用额度为：".$can_use_quota,$is_ajax);
    		}
    	}
    	
    	$data['repay_time'] = intval($_REQUEST['repaytime']);
    	if($data['repay_time']==0){
    		showErr("借款期限",$is_ajax);
    	}
    	$data['rate'] = floatval($_REQUEST['apr']);
    	$data['repay_time_type'] = intval($_REQUEST['repaytime_type']);
    	$level_list = load_auto_cache("level");
    	$min_rate = 0;
    	$max_rate = 0;
    	foreach($level_list['repaytime_list'][$GLOBALS['user_info']['level_id']] as $kkk=>$vvv){
    		if($vvv[0] == $data['repay_time'] && $vvv[1]==$data['repay_time_type']){
    			$min_rate = $vvv[2];
    			$max_rate = $vvv[3];
    		}
    	}
    	
    	if($data['rate'] <= 0 || $data['rate'] > $max_rate || $data['rate'] < $min_rate){
    		showErr("请正确输入借款利率",$is_ajax);
    	}
    	
    	$data['enddate'] = intval($_REQUEST['enddate']);
    	
    	$data['description'] = htmlspecialchars($_REQUEST['borrowdesc']);
    	if(trim($data['description'])==''){
    		showErr("请输入借款描述",$is_ajax);
    	}
    	
    	$data['voffice'] = intval($_REQUEST['voffice']);
    	$data['vposition'] = intval($_REQUEST['vposition']);
    	
    	$data['is_effect'] = 1;
    	$data['deal_status'] = 0;
    
    	$data['user_id'] = intval($GLOBALS['user_info']['id']);
    	
    	$data['loantype'] = intval($_REQUEST['loantype']);
    	if($data['repay_time_type'] == 0){
    		$data['loantype'] = 2;
    	}
    	
    	$data['create_time'] = get_gmtime();
    	
    	$module = "INSERT";
    	$jumpurl = url("index","borrow#steptwo");
    	$condition = "";
    	
    	$deal_id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."deal WHERE is_delete=2 AND user_id=".$GLOBALS['user_info']['id']);
    	if($deal_id > 0){
    		$module = "UPDATE";
    		if($t=="save")
    			$jumpurl = url("index","borrow#stepone");
    		$condition = "id = $deal_id";
    	}
    	else{
    		if($t=="save"){
    			$jumpurl = url("index","borrow#stepone");
    		}
    	}
    	
    	$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$data,$module,$condition);
    	if($module == "INSERT"){
    		$deal_id = $GLOBALS['db']->insert_id();
    	}
    	
		require_once APP_ROOT_PATH.'app/Lib/deal.php';
		$deal = get_deal($deal_id);
    	//发送验证通知
    	if(
    		$t!="save" &&
    		trim(app_conf('CUSTOM_SERVICE'))!=''&&
    		($GLOBALS['user_info']['idcardpassed']==0 || 
    		 $GLOBALS['user_info']['incomepassed']==0 || 
    		 $GLOBALS['user_info']['creditpassed']==0 || 
    		 $GLOBALS['user_info']['workpassed']==0
    		)
    		
    	){
    		$ulist = explode(",",trim(app_conf('CUSTOM_SERVICE')));
			$ulist = array_filter($ulist);
			
			if($ulist){
			
	    		$content = app_conf("SHOP_TITLE")."用户您好，请尽快上传必要信用认证材料（包括身份证认证、工作认证、收入认证、信用报告认证）。另外，多上传一些可选信用认证，有助于您提高借款额度，也有利于出借人更多的了解您的情况，以便让您更快的筹集到所需的资金。请您点击'我要贷款'，之后点击相应的审核项目，进入后，可先阅读该项信用认证所需材料及要求，然后按要求上传资料即可。 如果您有任何问题请您拨打客服电话 ".app_conf('SHOP_TEL')." 或给客服邮箱发邮件 ".app_conf("REPLY_ADDRESS")." 我们会及时给您回复。";
	    		require_once APP_ROOT_PATH.'app/Lib/message.php';
	    		
	    		//添加留言
				$message['title'] = $content;
				$message['content'] = htmlspecialchars(addslashes(valid_str($content)));
				$message['title'] = valid_str($message['title']);
					
				$message['create_time'] = get_gmtime();
				$message['rel_table'] = "deal";
				$message['rel_id'] = $deal_id;
				$message['user_id'] = $ulist[array_rand($ulist)];
				
				$message['is_effect'] = 1;		
				$GLOBALS['db']->autoExecute(DB_PREFIX."message",$message);
				
				//添加到动态
				insert_topic("message",$message['rel_id'],$message['user_id'],get_user_name($message['user_id'],false),$GLOBALS['user_info']['id']);
				
				//自己给自己留言不执行操作
				if($deal['user_id']!=$message['user_id']){
					$msg_conf = get_user_msg_conf($deal['user_id']);
					//站内信
					if($msg_conf['sms_asked']==1){
						$content = "<p>您好，用户 ".get_user_name($message['user_id'])."对您发布的借款列表 “<a href=\"".$deal['url']."\">".$deal['name']."</a>”进行了以下留言：</p>"; 
						$content .= "<p>“".$message['content']."”</p>";
						send_user_msg("",$content,0,$deal['user_id'],get_gmtime(),0,true,13,$message['rel_id']);
					}
					//邮件
					if($msg_conf['mail_asked']==1 && app_conf('MAIL_ON')==1){
						$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_DEAL_MSG'");
						$tmpl_content = $tmpl['content'];
						
						$notice['user_name'] = $GLOBALS['user_info']['user_name'];
						$notice['msg_user_name'] = get_user_name($message['user_id'],false);
						$notice['deal_name'] = $deal['name'];
						$notice['deal_url'] = get_domain().url("index","deal",array("id"=>$deal['id']));
						$notice['message'] = $message['content'];
						$notice['site_name'] = app_conf("SHOP_TITLE");
						$notice['site_url'] = get_domain().APP_ROOT;
						$notice['help_url'] = get_domain().url("index","helpcenter");
						
						
						$GLOBALS['tmpl']->assign("notice",$notice);
						
						$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
						$msg_data['dest'] = $GLOBALS['user_info']['email'];
						$msg_data['send_type'] = 1;
						$msg_data['title'] = get_user_name($message['user_id'],false)."给您的标留言！";
						$msg_data['content'] = addslashes($msg);
						$msg_data['send_time'] = 0;
						$msg_data['is_send'] = 0;
						$msg_data['create_time'] = get_gmtime();
						$msg_data['user_id'] = $GLOBALS['user_info']['id'];
						$msg_data['is_html'] = $tmpl['is_html'];
						$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
					}
				}
			}
    	}
    	
    	if($is_ajax==1){
    		showSuccess($GLOBALS['lang']['SUCCESS_TITLE'],$is_ajax,$jumpurl);
    	}
    	else{
	    	app_redirect($jumpurl);
    	}
    }
    
    function steptwo(){
    	$GLOBALS['tmpl']->assign('step',2);
    	
    	$deal_id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."deal WHERE is_delete=0 AND start_time=0 AND deal_status=0 AND user_id=".$GLOBALS['user_info']['id']) ;
    	if(!$deal_id)
    	{
    		app_redirect(url("index","index"));
    	}
    	require APP_ROOT_PATH.'app/Lib/deal.php';
    	
    	$deal = get_deal($deal_id);
    	
    	$GLOBALS['tmpl']->assign('deal',$deal);
    	
    	$u_info = get_user("user_name,level_id,province_id,city_id",$deal['user_id']);
    	$GLOBALS['tmpl']->assign('u_info',$u_info);
    	
    	$GLOBALS['tmpl']->assign('page_title',$GLOBALS['lang']['UPLOAD_DATA']);
    	
    	
    	
    	$temp_credit = $GLOBALS['db']->getAll("SELECT user_id,`type`,`file` FROM ".DB_PREFIX."user_credit_file WHERE user_id=".$GLOBALS['user_info']['id']." ");
    	$credit = array();
    	foreach($temp_credit as $k=>$v){
    		$file_list = array();
    		if($v['file'])
    			$file_list = unserialize($v['file']);
    		
    		if(is_array($file_list)) 
    			$v['file_list']= $file_list;
    		
    		$credit[$v['type']] = $v;
    	}
    	
    	$GLOBALS['tmpl']->assign('credit',$credit);
    	
    	$inc_file =  "inc/borrow/steptwo.html";
    	$GLOBALS['tmpl']->assign('inc_file',$inc_file);
    	$GLOBALS['tmpl']->display("page/borrow_step.html");
    }
    
    function file_upload(){
    	if(!$GLOBALS['user_info']){
    		exit();
    	}
    	$auth = trim($_REQUEST['auth']);
    	$auth_array = array(
    		'IDCARD_AUTH',
    		'JOB_AUTH',
    		'BANK_AUTH',
    		'INCOME_AUTH',
    		'ESTATE_AUTH',
    		'CAR_AUTH',
    		'MARRIED_AUTH',
    		'EDUCATION_AUTH',
    		'TITLES-_AUTH',
    		'VIDEO_AUTH',
    		'PHONE_AUTH',
    		'WEIBO_AUTH',
    		'RESIDENCE_AUTH',
    	);
    	if(!in_array($auth,$auth_array)){
    		exit();
    	}
    	
    	if($auth=="JOB_AUTH"){
    		//地区列表
			$work =  $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_work where user_id =".$GLOBALS['user_info']['id']);
			$GLOBALS['tmpl']->assign("work",$work);
			
			$region_lv2 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where region_level = 2");  //二级地址
			foreach($region_lv2 as $k=>$v)
			{
				if($v['id'] == intval($work['province_id']))
				{
					$region_lv2[$k]['selected'] = 1;
					break;
				}
			}
			$GLOBALS['tmpl']->assign("region_lv2",$region_lv2);
			
			$region_lv3 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where pid = ".intval($GLOBALS['user_info']['province_id']));  //三级地址
			foreach($region_lv3 as $k=>$v)
			{
				if($v['id'] == intval($work['city_id']))
				{
					$region_lv3[$k]['selected'] = 1;
					break;
				}
			}
			$GLOBALS['tmpl']->assign("region_lv3",$region_lv3);
    	}
    	if($auth=="RESIDENCE_AUTH"){
    		//地区列表
			$work =  $GLOBALS['db']->getRow("SELECT province_id,city_id FROM ".DB_PREFIX."user_work where user_id =".$GLOBALS['user_info']['id']);
			$GLOBALS['tmpl']->assign("work",$work);
			
			$region_lv2 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where region_level = 2");  //二级地址
			foreach($region_lv2 as $k=>$v)
			{
				if($v['id'] == intval($work['province_id']))
				{
					$region_lv2[$k]['selected'] = 1;
					break;
				}
			}
			$GLOBALS['tmpl']->assign("region_lv2",$region_lv2);
			
			$region_lv3 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where pid = ".intval($GLOBALS['user_info']['province_id']));  //三级地址
			foreach($region_lv3 as $k=>$v)
			{
				if($v['id'] == intval($work['city_id']))
				{
					$region_lv3[$k]['selected'] = 1;
					break;
				}
			}
			$GLOBALS['tmpl']->assign("region_lv3",$region_lv3);
    	}
    	
    	$GLOBALS['tmpl']->assign('auth',$auth);
    	$GLOBALS['tmpl']->assign('path',$_REQUEST['path']);
    	   	
    	$file = "inc/borrow/fileupload/".strtolower(trim($_REQUEST['path'])).".html";
    	$GLOBALS['tmpl']->assign($file);
    	$GLOBALS['tmpl']->display($file);
    }
    function file_upload_save(){
    	if(!$GLOBALS['user_info']){
    		exit();
    	}
    	$auth = trim($_REQUEST['auth']);
    	$auth_array = array(
    		'IDCARD_AUTH',
    		'JOB_AUTH',
    		'BANK_AUTH',
    		'INCOME_AUTH',
    		'ESTATE_AUTH',
    		'CAR_AUTH',
    		'MARRIED_AUTH',
    		'EDUCATION_AUTH',
    		'TITLES-_AUTH',
    		'VIDEO_AUTH',
    		'PHONE_AUTH',
    		'WEIBO_AUTH',
    		'RESIDENCE_AUTH',
    	);
    	if(!in_array($auth,$auth_array)){
    		exit();
    	}
    	$path = trim($_REQUEST['path']);
    	if($path==""){
    		exit();
    	}
    	//汽车认证
    	if($auth == "CAR_AUTH"){
    		$u_c_data['car_brand'] = htmlspecialchars($_REQUEST['carbrand']);
    		$u_c_data['car_year'] = intval($_REQUEST['caryear']);
    		$u_c_data['car_number'] = htmlspecialchars($_REQUEST['carnumber']);
    		
    		$GLOBALS['db']->autoExecute(DB_PREFIX."user",$u_c_data,"UPDATE","id=".$GLOBALS['user_info']['id']);
    	}
    	//学历认证
    	if($auth == "EDUCATION_AUTH"){
    		$u_edu_data['edu_validcode'] = htmlspecialchars($_REQUEST['validcode']);
    		$u_edu_data['graduation'] = htmlspecialchars($_REQUEST['graduation']);
    		$u_edu_data['university'] = htmlspecialchars($_REQUEST['university']);
    		$u_edu_data['graduatedyear'] = intval($_REQUEST['graduatedyear']);
    		
    		$GLOBALS['db']->autoExecute(DB_PREFIX."user",$u_edu_data,"UPDATE","id=".$GLOBALS['user_info']['id']);
    		$GLOBALS['tmpl']->display("inc/borrow/fileupload/upload_result_tip.html");
    		exit();
    	}
    	
    	//视频认证
    	if($auth == "VIDEO_AUTH"){
    		$u_v_data['has_send_video'] = intval($_REQUEST['usemail']);
    		
    		$GLOBALS['db']->autoExecute(DB_PREFIX."user",$u_v_data,"UPDATE","id=".$GLOBALS['user_info']['id']);
    		$GLOBALS['tmpl']->display("inc/borrow/fileupload/upload_result_tip.html");
    		exit();
    	}
    	
    	//居住地证明
    	if($auth == "RESIDENCE_AUTH"){
    		$u_w_data['province_id'] = intval($_REQUEST['province_id']);
    		$u_w_data['city_id'] = intval($_REQUEST['city_id']);
    		
    		$GLOBALS['db']->autoExecute(DB_PREFIX."user_work",$u_w_data,"UPDATE","user_id=".$GLOBALS['user_info']['id']);
    		
    		$u_a_data['address'] = htmlspecialchars($_REQUEST['address']);
    		$u_a_data['phone'] = htmlspecialchars($_REQUEST['phone']);
    		$u_a_data['postcode'] = htmlspecialchars($_REQUEST['postcode']);
    		
    		$GLOBALS['db']->autoExecute(DB_PREFIX."user",$u_a_data,"UPDATE","id=".$GLOBALS['user_info']['id']);
    	}
    	
    	$file=array();
    	for($i=1;$i<=10;$i++){
    		if(trim($_REQUEST['file'.$i])!=""){
    			$file[] = str_replace(get_domain().APP_ROOT,".",trim($_REQUEST['file'.$i]));
    		}
    	}
    	if(count($file)==0){
    		exit();
    	}
    	
    	$mode = "INSERT";
    	$condition = "";
    	
    	$temp_info = $GLOBALS['db']->getRow("SELECT user_id,`type`,`file` FROM ".DB_PREFIX."user_credit_file WHERE user_id=".$GLOBALS['user_info']['id']." AND type='".$path."'");
    	if($temp_info){
    		$file_list = unserialize($temp_info['file']);
    		if(is_array($file_list))
    			$file = array_merge($file,$file_list);
    		
    		$mode = "UPDATE";
    		$condition = "user_id=".$GLOBALS['user_info']['id']." AND type='".$path."'";
    	}
    	
    	$data['user_id'] = $GLOBALS['user_info']['id'];
    	$data['type'] = $path;
    	$data['file'] = serialize($file);
    	$data['create_time'] = get_gmtime();
    	
    	$GLOBALS['db']->autoExecute(DB_PREFIX."user_credit_file",$data,$mode,$condition);
    	$GLOBALS['tmpl']->display("inc/borrow/fileupload/upload_result_tip.html");
    }
    
    public function applyamount(){
    	/*if(floatval($GLOBALS['user_info']['quota']) > 0||$GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal WHERE user_id=".intval($GLOBALS['user_info']['id'])) > 0){
    		app_redirect(url("index","borrow"));
    	}*/
    	$work_count= $GLOBALS['db']->getOne("select count(*) FROM ".DB_PREFIX."user_work WHERE user_id=".$GLOBALS['user_info']['id']);
    	//判断用户信息是否输入完整
    	if(!isset($_REQUEST['status'])){
	    	if(
	    		$GLOBALS['user_info']['real_name'] == ""
	    		||
	    		$GLOBALS['user_info']['idno'] == ""
	    		||
	    		$GLOBALS['user_info']['mobile'] == ""
	    		||
	    		$GLOBALS['user_info']['marriage'] == ""
	    		||
	    		$GLOBALS['user_info']['address'] == ""
	    	){
	    		$_REQUEST['status'] = 1;
	    		$GLOBALS['tmpl']->assign('step',1);
	    	}
	    	elseif($work_count==0){
	    		$_REQUEST['status'] = 2;
	    		$GLOBALS['tmpl']->assign('step',1);
	    	}
	    	else{
	    		$_REQUEST['status'] = 3;
	    		$GLOBALS['tmpl']->assign('step',2);
	    	}
    	}
    	
    		
    	$GLOBALS['tmpl']->assign('page_title',$GLOBALS['lang']['APPLY_AMOUNT']);
    	
    	$status = intval($_REQUEST['status']);
    	if($status < 3){
    		$GLOBALS['tmpl']->assign('step',1);
    	}
    	switch($status){
    		case 1 :
	    		$inc_file =  "inc/borrow/stepone_u_info.html";
	    		//扩展字段
				$field_list = load_auto_cache("user_field_list");
				
				foreach($field_list as $k=>$v)
				{
					$field_list[$k]['value'] = $GLOBALS['db']->getOne("select value from ".DB_PREFIX."user_extend where user_id=".$GLOBALS['user_info']['id']." and field_id=".$v['id']);
				}
				
				$GLOBALS['tmpl']->assign("field_list",$field_list);
				
				//地区列表
		
				$region_lv2 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where region_level = 2");  //二级地址
				foreach($region_lv2 as $k=>$v)
				{
					if($v['id'] == intval($GLOBALS['user_info']['province_id']))
					{
						$region_lv2[$k]['selected'] = 1;
						break;
					}
				}
				$GLOBALS['tmpl']->assign("region_lv2",$region_lv2);
				
				$region_lv3 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where pid = ".intval($GLOBALS['user_info']['province_id']));  //三级地址
				foreach($region_lv3 as $k=>$v)
				{
					if($v['id'] == intval($GLOBALS['user_info']['city_id']))
					{
						$region_lv3[$k]['selected'] = 1;
						break;
					}
				}
				$GLOBALS['tmpl']->assign("region_lv3",$region_lv3);
												
	    		break;
    		case 2 :
	    		$inc_file =  "inc/borrow/stepone_work_info.html";
	    		
	    		//地区列表
				$work =  $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_work where user_id =".$GLOBALS['user_info']['id']);
				$GLOBALS['tmpl']->assign("work",$work);
				
				$region_lv2 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where region_level = 2");  //二级地址
				foreach($region_lv2 as $k=>$v)
				{
					if($v['id'] == intval($work['province_id']))
					{
						$region_lv2[$k]['selected'] = 1;
						break;
					}
				}
				$GLOBALS['tmpl']->assign("region_lv2",$region_lv2);
				
				$region_lv3 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where pid = ".intval($GLOBALS['user_info']['province_id']));  //三级地址
				foreach($region_lv3 as $k=>$v)
				{
					if($v['id'] == intval($work['city_id']))
					{
						$region_lv3[$k]['selected'] = 1;
						break;
					}
				}
				$GLOBALS['tmpl']->assign("region_lv3",$region_lv3);
	    		break;
	    	case 3 :
	    		$temp_credit = $GLOBALS['db']->getAll("SELECT user_id,`type`,`file` FROM ".DB_PREFIX."user_credit_file WHERE user_id=".$GLOBALS['user_info']['id']." ");
		    	$credit = array();
		    	foreach($temp_credit as $k=>$v){
		    		$file_list = array();
		    		if($v['file'])
		    			$file_list = unserialize($v['file']);
		    		
		    		if(is_array($file_list)) 
		    			$v['file_list']= $file_list;
		    		
		    		$credit[$v['type']] = $v;
		    	}
		    	
		    	$GLOBALS['tmpl']->assign('credit',$credit);
		    	
		    	$inc_file =  "inc/borrow/steptwo.html";
	    		break;
    	}
    	
    	$GLOBALS['tmpl']->assign('inc_file',$inc_file);
    	$GLOBALS['tmpl']->assign('status',$status);
    	$GLOBALS['tmpl']->assign('work_count',$work_count);
    	$GLOBALS['tmpl']->display("page/applyamount_step.html");
    }
    
    public function creditswitch(){
    	$info['title'] = "信用审核";
    	$GLOBALS['tmpl']->assign("info",$info);
    	
    	$list = load_auto_cache("level");
		
		$GLOBALS['tmpl']->assign("list",$list['list']);
		
		foreach($list["list"] as $k=>$v){
			if($v['id'] ==  $GLOBALS['user_info']['level_id'])
				$user_point_level = $v['name'];
		}
    	$GLOBALS['tmpl']->assign("user_point_level",$user_point_level);
    	
    	//资料
    	$temp_credit = $GLOBALS['db']->getAll("SELECT user_id,`type`,`file` FROM ".DB_PREFIX."user_credit_file WHERE user_id=".$GLOBALS['user_info']['id']." ");
    	$credit = array();
    	foreach($temp_credit as $k=>$v){
    		$file_list = array();
    		if($v['file'])
    			$file_list = unserialize($v['file']);
    		
    		if(is_array($file_list)) 
    			$v['file_list']= $file_list;
    		
    		$credit[$v['type']] = $v;
    	}
    	
    	$GLOBALS['tmpl']->assign('credit',$credit);
    	
    	//可用额度
		$can_use_quota=get_can_use_quota($GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign('can_use_quota',$can_use_quota);
    	
    	//工作认证是否过期
    	$time = get_gmtime();
    	$expire_time = 6*30*24*3600;
    	if($GLOBALS['user_info']['workpassed']==1){
    		if(($time - $GLOBALS['user_info']['workpassed_time']) > $expire_time){
    			$expire['workpassed_expire'] = 1;
    		}
    	}
    	if($GLOBALS['user_info']['incomepassed']==1){
    		if(($time - $GLOBALS['user_info']['incomepassed_time']) > $expire_time){
    			$expire['incomepassed_expire'] = 1;
    		}
    	}
    	if($GLOBALS['user_info']['creditpassed']==1){
    		if(($time - $GLOBALS['user_info']['creditpassed_time']) > $expire_time){
    			$expire['creditpassed_expire'] = 1;
    		}
    	}
    	if($GLOBALS['user_info']['residencepassed']==1){
    		if(($time - $GLOBALS['user_info']['residencepassed_time']) > $expire_time){
    			$expire['residencepassed_expire'] = 1;
    		}
    	}
    	
    	$GLOBALS['tmpl']->assign('expire',$expire);
    	
    	//必要信用认证
    	$level_point['need_other_point'] = 0;
    	if($GLOBALS['user_info']['idcardpassed']==1){
    		$level_point['need_other_point'] += (int)trim(app_conf("IDCARDPASSED_POINT"));
    	}
    	if($GLOBALS['user_info']['workpassed']==1){
    		$level_point['need_other_point'] += (int)trim(app_conf("WORKPASSED_POINT"));
    	}
    	if($GLOBALS['user_info']['incomepassed']==1){
    		$level_point['need_other_point'] += (int)trim(app_conf("INCOMEPASSED_POINT"));
    	}
    	if($GLOBALS['user_info']['creditpassed']==1){
    		$level_point['need_other_point'] += (int)trim(app_conf("CREDITPASSED_POINT"));
    	}
    	//可选信用认证
    	if($GLOBALS['user_info']['housepassed']==1){
    		$level_point['need_other_point'] += (int)trim(app_conf("HOUSEPASSED_POINT"));
    	}
    	if($GLOBALS['user_info']['skillpassed']==1){
    		$level_point['need_other_point'] += (int)trim(app_conf("SKILLPASSED_POINT"));
    	}
    	if($GLOBALS['user_info']['carpassed']==1){
    		$level_point['need_other_point'] += (int)trim(app_conf("CARPASSED_POINT"));
    	}
    	if($GLOBALS['user_info']['marrypassed']==1){
    		$level_point['need_other_point'] += (int)trim(app_conf("MARRYPASSED_POINT"));
    	}
    	if($GLOBALS['user_info']['residencepassed']==1){
    		$level_point['need_other_point'] += (int)trim(app_conf("RESIDENCEPASSED_POINT"));
    	}
    	if($GLOBALS['user_info']['videopassed']==1){
    		$level_point['need_other_point'] += (int)trim(app_conf("VIDEOPASSED_POINT"));
    	}
    	
    	//还清
    	$level_point['repay_success'] = $GLOBALS['db']->getRow("SELECT sum(point) as total_point,count(*) AS total_count FROM ".DB_PREFIX."user_log WHERE log_info='还清借款' AND user_id=".$GLOBALS['user_info']['id']);
    	//逾期
    	$level_point['impose_repay'] = $GLOBALS['db']->getRow("SELECT sum(point) as total_point,count(*) AS total_count FROM ".DB_PREFIX."user_log WHERE log_info='逾期还款' AND user_id=".$GLOBALS['user_info']['id']);
    	//严重逾期
    	$level_point['yz_impose_repay'] = $GLOBALS['db']->getRow("SELECT sum(point) as total_point,count(*) AS total_count FROM ".DB_PREFIX."user_log WHERE log_info='严重逾期还款' AND user_id=".$GLOBALS['user_info']['id']);
    	
    	$GLOBALS['tmpl']->assign('level_point',$level_point);
		$seo_title = $info['seo_title']!=''?$info['seo_title']:$info['title'];
		$GLOBALS['tmpl']->assign("page_title",$seo_title);
		$seo_keyword = $info['seo_keyword']!=''?$info['seo_keyword']:$info['title'];
		$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
		$seo_description = $info['seo_description']!=''?$info['seo_description']:$info['title'];
		$GLOBALS['tmpl']->assign("page_description",$seo_description.",");
    	$GLOBALS['tmpl']->display("page/borrow_creditswitch.html");
    }
    
    
    public function aboutborrow(){
    	$GLOBALS['tmpl']->caching = true;
		$GLOBALS['tmpl']->cache_lifetime = 6000;  //首页缓存10分钟
		$cache_id  = md5(MODULE_NAME.ACTION_NAME);	
		if (!$GLOBALS['tmpl']->is_cached("page/aboutborrow.html", $cache_id))
		{	
			$info = get_article_buy_uname("aboutborrow");
			$info['content']=$GLOBALS['tmpl']->fetch("str:".$info['content']);
			$GLOBALS['tmpl']->assign("info",$info);
			
			$seo_title = $info['seo_title']!=''?$info['seo_title']:$info['title'];
			$GLOBALS['tmpl']->assign("page_title",$seo_title);
			$seo_keyword = $info['seo_keyword']!=''?$info['seo_keyword']:$info['title'];
			$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
			$seo_description = $info['seo_description']!=''?$info['seo_description']:$info['title'];
			$GLOBALS['tmpl']->assign("page_description",$seo_description.",");
		}
		$GLOBALS['tmpl']->display("page/aboutborrow.html",$cache_id);
    }
}
?>