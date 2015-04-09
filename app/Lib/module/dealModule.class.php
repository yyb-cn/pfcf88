<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/deal.php';
   function now_insert($table,$data){
    $clomne='';
	$val='';
	foreach ($data as $key =>$value){
	  $clomne.='`'.$key.'`,';
	  $val.="'".$value."',";
	}
	$clomne=substr($clomne,0,-1);
	$val=substr($val,0,-1);
    $sql="insert into `$table`($clomne)values($val)";
	 // echo $sql;exit;
    mysql_query($sql);
    return mysql_affected_rows();
	 }	
class dealModule extends SiteBaseModule
{
	public function index(){  
	 
		$id = intval($_REQUEST['id']);
	
		$deal = get_deal($id);
		// var_dump($deal);exit;
		send_deal_contract_email($id,$deal,$deal['user_id']);
		
		if(!$deal)
			app_redirect(url("index")); 
		
		//借款列表
		$load_list = $GLOBALS['db']->getAll("SELECT deal_id,user_id,user_name,money,virtual_money,unjh_pfcfb,create_time FROM ".DB_PREFIX."deal_load WHERE deal_id = ".$id);
		
		
		$u_info = get_user("*",$deal['user_id']);
		
		//可用额度
		$can_use_quota=get_can_use_quota($deal['user_id']);
		$GLOBALS['tmpl']->assign('can_use_quota',$can_use_quota);
		
		$credit_file = get_user_credit_file($deal['user_id']);
		$deal['is_faved'] = 0;
		if($GLOBALS['user_info']){
			$deal['is_faved'] = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_collect WHERE deal_id = ".$id." AND user_id=".intval($GLOBALS['user_info']['id']));
			
			if($deal['deal_status'] >=4){
				//还款列表
				$loan_repay_list = get_deal_load_list($deal);
				$GLOBALS['tmpl']->assign("loan_repay_list",$loan_repay_list);
				
				foreach($load_list as $k=>$v){
					$load_list[$k]['remain_money'] = $v['money'] - $GLOBALS['db']->getOne("SELECT sum(self_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$v['user_id']." AND deal_id=".$id);
					if($load_list[$k]['remain_money'] <=0){
						$load_list[$k]['remain_money'] = 0;
						$load_list[$k]['status'] = 1;
					}
				}
			}	
			$user_statics = sys_user_status($deal['user_id'],true);
			$GLOBALS['tmpl']->assign("user_statics",$user_statics);
		}
		//lu 用户名中间部分用星号代替 echo   cut_str($str, 1, 0).'**'.cut_str($str, 1, -1);
		foreach($load_list as $k_n=>$v_n){
			$load_list[$k_n]['user_name']=cut_str($load_list[$k_n]['user_name'], 1, 0).'****'.cut_str($load_list[$k_n]['user_name'], 1, -1);
			
			}
		//print_r($load_list);exit;
		//本项目投资列表——@@———————————————————@—————————————————@——————————@—————————————！！
		$GLOBALS['tmpl']->assign("load_list",$load_list);	
		$GLOBALS['tmpl']->assign("credit_file",$credit_file);
		$GLOBALS['tmpl']->assign("u_info",$u_info);
		
		//工作认证是否过期
    	$time = get_gmtime();
    	$expire_time = 6*30*24*3600;
    	if($u_info['workpassed']==1){
    		if(($time - $u_info['workpassed_time']) > $expire_time){
    			$expire['workpassed_expire'] = 1;
    		}
    	}
    	if($u_info['incomepassed']==1){
    		if(($time - $u_info['incomepassed_time']) > $expire_time){
    			$expire['incomepassed_expire'] = 1;
    		}
    	}
    	if($u_info['creditpassed']==1){
    		if(($time - $u_info['creditpassed_time']) > $expire_time){
    			$expire['creditpassed_expire'] = 1;
    		}
    	}
    	if($u_info['residencepassed']==1){
    		if(($time - $u_info['residencepassed_time']) > $expire_time){
    			$expire['residencepassed_expire'] = 1;
    		}
    	}
		
		$GLOBALS['tmpl']->assign('expire',$expire);
		if($deal['type_match_row'])
			$seo_title = $deal['seo_title']!=''?$deal['seo_title']:$deal['type_match_row'] . " - " . $deal['name'];
		else
			$seo_title = $deal['seo_title']!=''?$deal['seo_title']: $deal['name'];
			
		$GLOBALS['tmpl']->assign("page_title",$seo_title);
		$seo_keyword = $deal['seo_keyword']!=''?$deal['seo_keyword']:$deal['type_match_row'].",".$deal['name'];
		$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
		$seo_description = $deal['seo_description']!=''?$deal['seo_description']:$deal['name'];
		
		//留言
		require APP_ROOT_PATH.'app/Lib/message.php';
		require APP_ROOT_PATH.'app/Lib/page.php';
		$rel_table = 'deal';
		$message_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."message_type where type_name='".$rel_table."'");
		$condition = "rel_table = '".$rel_table."' and rel_id = ".$id;
	
		if(app_conf("USER_MESSAGE_AUTO_EFFECT")==0)
		{
			$condition.= " and user_id = ".intval($GLOBALS['user_info']['id']);
		}
		else 
		{
			if($message_type['is_effect']==0)
			{
				$condition.= " and user_id = ".intval($GLOBALS['user_info']['id']);
			}
		}
		
		//message_form 变量输出
		$GLOBALS['tmpl']->assign('rel_id',$id);
		$GLOBALS['tmpl']->assign('rel_table',"deal");
		
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$msg_condition = $condition." AND is_effect = 1 ";
		$message = get_message_list($limit,$msg_condition);
		
		$page = new Page($message['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		foreach($message['list'] as $k=>$v){
			$msg_sub = get_message_list("","pid=".$v['id'],false);
			$message['list'][$k]["sub"] = $msg_sub["list"];
		}
		
		$GLOBALS['tmpl']->assign("message_list",$message['list']);
		if(!$GLOBALS['user_info'])
		{
			$GLOBALS['tmpl']->assign("message_login_tip",sprintf($GLOBALS['lang']['MESSAGE_LOGIN_TIP'],url("shop","user#login"),url("shop","user#register")));
		}
		$deal['create_time']=to_date($deal['create_time'],"Y-m-d H:i");
		$deal['repay_start_time']=date("Y-m-d",$deal['repay_start_time']);
		// var_dump($deal);exit;
		$GLOBALS['tmpl']->assign("deal",$deal);
		
		//print_r($deal);exit;
		$GLOBALS['tmpl']->display("page/deal.html");
	}
	
	function preview(){
	
		$deal['id'] = 'XXX';
		$deal['name'] = trim($_REQUEST['borrowtitle']);
		$deal_loan_type_list = load_auto_cache("deal_loan_type_list");
		foreach($deal_loan_type_list as $k=>$v){
			if($v['id'] == intval($_REQUEST['borrowtype'])){
				$deal['type_match_row'] = $v['name'];
			}
		}
		
		if(intval($_REQUEST['borrowtype']) > 0){
			$deal['type_info'] = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_loan_type where id = ".intval($_REQUEST['borrowtype'])." and is_effect = 1 and is_delete = 0");
		}
		
		$deal['borrow_amount_format'] = format_price(trim($_REQUEST['borrowamount']));
		$deal['rate_foramt'] = number_format(trim($_REQUEST['apr']),2);
		$deal['min_loan_money'] = 50;
		$deal['need_money'] = $deal['borrow_amount_format'];
		$deal['repay_time'] = trim($_REQUEST['repaytime']);
		//本息还款金额
			$deal['month_repay_money'] = format_price(pl_it_formula($deal['borrow_amount'],trim($_REQUEST['apr'])/12/100,$deal['repay_time']));
		
		$deal['progress_point'] = 0;
		$deal['buy_count'] = 0;
		$deal['voffice'] = intval($_REQUEST['voffice']);
		$deal['vjobtype'] = intval($_REQUEST['vjobtype']);
		
		$deal['description']= $_REQUEST['borrowdesc'];
		
		$deal['is_delete'] = 2;
		
		$u_info = get_user("*",$GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign("u_info",$u_info);
		$seo_title = $deal['seo_title']!=''?$deal['seo_title']:$deal['type_match_row'] . " - " . $deal['name'];
		$GLOBALS['tmpl']->assign("page_title",$seo_title);
		$seo_keyword = $deal['seo_keyword']!=''?$deal['seo_keyword']:$deal['type_match_row'].",".$deal['name'];
		$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
		$seo_description = $deal['seo_description']!=''?$deal['seo_description']:$deal['name'];
		
		$GLOBALS['tmpl']->assign("seo_description",$seo_description.",");
		
		$GLOBALS['tmpl']->assign("deal",$deal);
		$GLOBALS['tmpl']->display("page/deal.html");
	}
	
	function bid(){  
	     
		if(!$GLOBALS['user_info']){
			set_gopreview();
			app_redirect(url("index","user#login")); 
		}
		
		//如果未绑定手机
		if(intval($GLOBALS['user_info']['mobilepassed'])==0 || intval($GLOBALS['user_info']['idcardpassed'])==0){
			$GLOBALS['tmpl']->assign("page_title","成为理财人");
			$GLOBALS['tmpl']->display("page/deal_mobilepaseed.html");
			exit();
		}
		
		$id = intval($_REQUEST['id']);
		$deal = get_deal($id);
		
		if(!$deal)
			app_redirect(url("index")); //不存在这个标
		
		if($deal['user_id'] == $GLOBALS['user_info']['id']){
			showErr($GLOBALS['lang']['CANT_BID_BY_YOURSELF']);//不能投自己的标
		}
		require APP_ROOT_PATH.'app/Lib/uc.php';
		
		$result = get_voucher_list_can($GLOBALS['user_info']['id']);//查询代金券
		//print_r($result);exit;
		$GLOBALS['tmpl']->assign("voucher",$result['list']);//查询代金券
		
		$seo_title = $deal['seo_title']!=''?$deal['seo_title']:$deal['type_match_row'] . " - " . $deal['name'];
		$GLOBALS['tmpl']->assign("page_title",$seo_title);
		$seo_keyword = $deal['seo_keyword']!=''?$deal['seo_keyword']:$deal['type_match_row'].",".$deal['name'];
		$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
		$seo_description = $deal['seo_description']!=''?$deal['seo_description']:$deal['name'];
		
		$GLOBALS['tmpl']->assign("deal",$deal);
		$GLOBALS['tmpl']->display("page/deal_bid.html");
	}
	
//成为贷款人

	function dobidstepone(){
		if(!$GLOBALS['user_info'])
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],1);
			
		if($GLOBALS['user_info']['idcardpassed'] == 0){
			if(trim($_REQUEST['idno'])==""){
				showErr($GLOBALS['lang']['PLEASE_INPUT'].$GLOBALS['lang']['IDNO'],1);
			}
			if(trim($_REQUEST['idno'])!=trim($_REQUEST['idno_re'])){
				showErr($GLOBALS['lang']['TWO_ENTER_IDNO_ERROR'],1);
			}
			$data['idno'] = trim($_REQUEST['idno']);
			$data['idcardpassed'] = 1;
			$data['real_name']=$_REQUEST['name'];//实名认证；姓名
			
			
			
			
		}
		
		if($GLOBALS['user_info']['mobilepassed'] == 0){
			

			if(trim($_REQUEST['phone'])==""){
				showErr($GLOBALS['lang']['MOBILE_EMPTY_TIP'],1);
			}
			if(!check_mobile(trim($_REQUEST['phone']))){
				showErr($GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'],1);
			}
			if(trim($_REQUEST['validateCode'])==""){
				showErr($GLOBALS['lang']['PLEASE_INPUT'].$GLOBALS['lang']['VERIFY_CODE'],1);
			}
			if(trim($_REQUEST['validateCode'])!=$GLOBALS['user_info']['bind_verify']){
				showErr($GLOBALS['lang']['BIND_MOBILE_VERIFY_ERROR'],1);
			}
			$data['mobile'] = trim($_REQUEST['phone']);
			$data['mobilepassed'] = 1;
		}
		if($data)
			$GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$GLOBALS['user_info']['id']);
		
		showSuccess($GLOBALS['lang']['SUCCESS_TITLE'],1);
	}
	
	//_!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!确认投资
	function dobid(){
		$ajax = intval($_REQUEST["ajax"]);
		$id = intval($_REQUEST["id"]);
		if(!$GLOBALS['user_info'])
		showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax);
		$deal = get_deal($id);
		//echo json_encode($deal);
		if(trim($_REQUEST["bid_money"])=="" || !is_numeric($_REQUEST["bid_money"]) || floatval($_REQUEST["bid_money"])<=0 || floatval($_REQUEST["bid_money"]) < $deal['min_loan_money']){
			showErr($GLOBALS['lang']['BID_MONEY_NOT_TRUE'],$ajax);
		}
		if((int)trim(app_conf('DEAL_BID_MULTIPLE')) > 0){
			 if(intval($_REQUEST["bid_money"])%(int)trim(app_conf('DEAL_BID_MULTIPLE'))!=0){
			 	showErr($GLOBALS['lang']['BID_MONEY_NOT_TRUE'],$ajax);
			 	exit();
			 }
		}
		
		
		if(!$deal){
			showErr($GLOBALS['lang']['PLEASE_SPEC_DEAL'],$ajax);
		}
		
		if(floatval($deal['progress_point']) >= 100){
			showErr($GLOBALS['lang']['DEAL_BID_FULL'],$ajax);
		}
		
		if(floatval($deal['deal_status']) != 1 ){
			showErr($GLOBALS['lang']['DEAL_FAILD_OPEN'],$ajax);
		}
		
		if(floatval($_REQUEST["bid_money"]) > $GLOBALS['user_info']['money']){
			showErr($GLOBALS['lang']['MONEY_NOT_ENOUGHT'],$ajax);
		}
		if(floatval($_REQUEST["unjh_pfcfb"]) > $GLOBALS['user_info']['unjh_pfcfb']){
			showErr('浦发币不足，无法投标',$ajax);
		}
		//判断所投的钱是否超过了剩余投标额度
		if(floatval($_REQUEST["bid_money"]) > ($deal['borrow_amount'] - $deal['load_money'])){
			showErr(sprintf($GLOBALS['lang']['DEAL_LOAN_NOT_ENOUGHT'],format_price($deal['borrow_amount'] - $deal['load_money'])),$ajax);
		}
		
		$data['user_id'] = $user_id=$GLOBALS['user_info']['id'];
		$data['user_name'] = $GLOBALS['user_info']['user_name'];
		$data['deal_id'] = $id;
		$data['money'] = trim($_REQUEST["bid_money"]);
		$data['create_time'] = get_gmtime();
		
		//浦发财富b
		if($_REQUEST['unjh_pfcfb']){
			if($deal['repay_time']==1&&$deal['repay_time_type']==1){
			$data['unjh_pfcfb']=$_REQUEST['unjh_pfcfb'];
			}
			else{
				showErr('只能用于投资1个月的标',$ajax);
			}
		}
		//以下为代金券判断操作
			if($_REQUEST['virtual_money']!=0)//判断复选框是否为勾选
			{
		
			if($_REQUEST['virtual_money']>3200){
				showErr('单笔投资代金券不能超过3200',$ajax);
			}
			if($deal['repay_time']==1&&$deal['repay_time_type']==1){  //一个月的标
				$i=0;
				foreach ($_REQUEST['v_money'] as $k=>$v){
				
				 $sql = "select *,e.id as ecv_id from ".DB_PREFIX."ecv as e left join ".DB_PREFIX."ecv_type as et on e.ecv_type_id = et.id where e.user_id = ".$user_id." and e.used_yn=0 and (et.end_time=0 or et.end_time>" .time().  " ) and e.password=".$k." and et.money=".$v;
				// showErr($sql,$ajax);
				$one = $GLOBALS['db']->getRow($sql);
				$virtual_money+=$one['money'];
					
				 if(!$one)
				 {
				 showErr('代金券不存在,请联系客服',$ajax);
				 }
				
					$id_str.=($i==0)?$one['ecv_id']:','.$one['ecv_id'];
					$i+=1; 
				 
				}
				//showErr('代金券'.$id_str,$ajax);
				if($virtual_money!=$_REQUEST['virtual_money']){
					showErr('单笔投资代金券不能超过'.$_REQUEST['v_money'],$ajax);
				showErr('代金券金额出错',$ajax);
				}
				else{
					$data['virtual_money']=$_REQUEST['virtual_money'];  //记录data的虚拟金额
					//修改代金券已用
					$GLOBALS['db']->query("update ".DB_PREFIX."ecv set used_yn = 1 where id in (".$id_str.")");											
					}
				require APP_ROOT_PATH.'app/Lib/uc.php';
			}
			else{
			showErr('只能用于投资1个月的标',$ajax);
			}
		}
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load",$data,"INSERT");//插入一条投资目录
		$load_id = $GLOBALS['db']->insert_id();//获取插入的ID
		
		if($load_id > 0){
		//如果投资金额大于1000;增加一次抽奖机会
		/*
		if($data['money']>=10000&&$deal['repay_time_type']==1){  //只有月份的才送
			
		$a=floor($data['money']/10000);
		
		 $lottery=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."lottery where uid = ".$user_id);
		 
		if($lottery){
		$GLOBALS['db']->query("update ".DB_PREFIX."lottery set draw_sec=draw_sec+".$a." where uid = ".$user_id);
		}
		
		else{
			    	$lottery_data=array('draw_sec'=>$a,'uid'=>$user_id);//插入抽奖数组
				$GLOBALS['db']->autoExecute(DB_PREFIX."lottery",$lottery_data);//执行插入
			}

		}
		*/
			//更改资金记录
			$msg = sprintf('编号%s的投标,付款单号%s',$id,$load_id);
			require_once APP_ROOT_PATH."system/libs/user.php";	
			modify_account(array('money'=>-trim($_REQUEST["bid_money"]),'score'=>0),$GLOBALS['user_info']['id'],$msg);
			modify_account(array('unjh_pfcfb'=>-trim($_REQUEST["unjh_pfcfb"])),$GLOBALS['user_info']['id'],$msg.'(浦发币:'.$_REQUEST["unjh_pfcfb"].')');
			$deal = get_deal($id);
			sys_user_status($GLOBALS['user_info']['id']);
			//超过一半的时候
			
			if($deal['deal_status']==1 && $deal['progress_point'] >= 50 && $deal['progress_point']<=60 && $deal['is_send_half_msg'] == 0)
			{
				$msg_conf = get_user_msg_conf($deal['user_id']);
				//邮件
				if(app_conf("MAIL_ON")){
					if(!$msg_conf || intval($msg_conf['mail_half'])==1){
						$load_tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_HALF_EMAIL'");
						$user_info = $GLOBALS['db']->getRow("select email,user_name from ".DB_PREFIX."user where id = ".$deal['user_id']);
						$tmpl_content = $load_tmpl['content'];
						$notice['user_name'] = $user_info['user_name'];
						$notice['deal_name'] = $deal['name'];
						$notice['deal_url'] = get_domain().$deal['url'];
						$notice['site_name'] = app_conf("SHOP_TITLE");
						$notice['site_url'] = get_domain().APP_ROOT;
						$notice['help_url'] = get_domain().url("index","helpcenter");
						$notice['msg_cof_setting_url'] = get_domain().url("index","uc_msg#setting");
						$GLOBALS['tmpl']->assign("notice",$notice);
						$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
						$msg_data['dest'] = $user_info['email'];
						$msg_data['send_type'] = 1;
						$msg_data['title'] = "您的借款列表“".$deal['name']."”招标过半！";
						$msg_data['content'] = addslashes($msg);
						$msg_data['send_time'] = 0;
						$msg_data['is_send'] = 0;
						$msg_data['create_time'] = get_gmtime();
						$msg_data['user_id'] =  $deal['user_id'];
						$msg_data['is_html'] = $load_tmpl['is_html'];
						$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
					}
				}
				
				//站内信
				if(intval($msg_conf['sms_half'])==1){
					$content = "<p>您在".app_conf("SHOP_TITLE")."的借款“<a href=\"".$deal['url']."\">".$deal['name']."</a>”完成度超过50%"; 
					send_user_msg("",$content,0,$deal['user_id'],get_gmtime(),0,true,15);
				}
				
				//更新
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal",array("is_send_half_msg"=>1),"UPDATE","id=".$id);
			}

		//附送代金卷投资 111111111111111111111111111
		$panduan_time=strtotime("2015-03-23");
		//新用户ID查询投标的时间戳，后判断；
	    $vo= $GLOBALS['db']->getRow("select * from fanwe_deal_load where user_id='".$GLOBALS['user_info']['id']."' and create_time <".$panduan_time);
		if(!$vo&&(time()>=$panduan_time))
		{
		$shiwu=$_REQUEST["bid_money"];
        
	       $deal_voucher=$GLOBALS['db']->getOne("SELECT `repay_time_type` FROM ".DB_PREFIX."deal where id=".$id); 
		   $deal_voucher_user_id=$GLOBALS['user_info']['id'];
		   
	    if($deal_voucher==1){  //判断是否是投资一个月的标
		  
		  //$deal_voucher_user_id=$GLOBALS['user_info']['id'];
		  $tshiwu=intval($shiwu);
		  $sql="select max(sort) from `fanwe_deal` where is_delete=0";
		  $maxs=$GLOBALS['db']->getRow($sql);
		  $max=$maxs['max(sort)']+1; 
		  $name='(投标送)投资了'.$tshiwu.'所送15天投资'.$tshiwu.'的利息'.$GLOBALS['user_info']['user_name'];
		   
		    $deal_data['name']=$name;
            $deal_data['sub_name']=$name;	
            $deal_data['cate_id'] =14;
            $deal_data['user_id']=6;
            $deal_data['is_effect']=1;
            $deal_data['is_delete']=0 ;  
            $deal_data['sort']=$max;         
            $deal_data['type_id']=10;
            $deal_data['borrow_amount']=0;
            $deal_data['min_loan_money']=1000;
            $deal_data['repay_time']=15;
            $deal_data['deal_status']=4;
            $deal_data['enddate']=1;
            $deal_data['create_time']=get_gmtime();
            $deal_data['update_time']=get_gmtime();
            $deal_data['name_match_row']=$name;
            // $deal_data['deal_cate_match_row']='我不想让你看到';
			// $deal_data['deal_cate_match']='ux25105ux19981ux24819ux35753ux20320ux30475ux21040';
            // $deal_data['type_match']='ux20854ux20182ux20511ux27454';
            // $deal_data['type_match_row']='其他借款';
            $deal_data['buy_count']=1;
            $deal_data['loantype'] =2;
            $deal_data['warrant'] = 2 ;
            $deal_data['services_fee']=0;
            $deal_data['repay_time_type']=0; 
            $deal_data['load_money']=0;
			$deal_data['presented_virtual'] =$tshiwu;
            $deal_data['enddate']=1;
            $deal_data['start_time']=get_gmtime();
            $deal_data['success_time']=get_gmtime();
            $deal_data['repay_start_time']=get_gmtime();
            $deal_data['next_repay_time']=get_gmtime()+86400;
			$deal_data['rate']=7;  
			$deal_data['virtual_id']=1;  //判断是否是公司所送的纯代金卷投资、 
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$deal_data);
		   $deal_id= $GLOBALS['db']->insert_id();//获取插入的ID	
		  if($deal_id){
		      $deal=get_deal($deal_id);
		      $data['user_id'] =$deal_voucher_user_id;
		      $data['user_name'] = $GLOBALS['user_info']['user_name'];
		      $data['deal_id'] = $deal_id;
			  $data['money'] =0;
		      $data['virtual_money'] =$tshiwu;
		      $data['create_time'] = get_gmtime();
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load",$data);
         $deal_load_id = $GLOBALS['db']->insert_id();//获取插入的ID
		if($deal_load_id > 0){		
			// 更改资金记录
		//7.插入用户记录
			$log_info['log_info'] = '代金券'.$tshiwu.'元，用于'.'<a href="?deal_load_id='.$deal_id.'&m=Deal_list&a=index">'. $deal_data['name'].'</a>';
			$log_info['log_time'] = get_gmtime();
			$log_info['user_id'] = $GLOBALS['user_info']['id'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_log",$log_info);
			
			require_once APP_ROOT_PATH."system/libs/user.php";	
			$deal = get_deal($deal_id);
			sys_user_status($GLOBALS['user_info']['id']);
			
		}
		else{
			showErr($GLOBALS['lang']['ERROR_TITLE'],3);
		    }
	    }
      
	
  }	 
 } 
		//结束 			

			showSuccess($GLOBALS['lang']['DEAL_BID_SUCCESS'],$ajax,url("index","deal",array("id"=>$id)));
		}
		else{
			showErr($GLOBALS['lang']['ERROR_TITLE'],$ajax);
		}
	
	}
	
	function downloadfile(){
		
		
		$cat_id=intval($_REQUEST['cat']);
		
		
		switch($cat_id){
		
		case 3 :forceDownload('public/comment/201405/pfcf88xindaiying.pdf');//下载信贷赢pdf
		break;
		case 5 :forceDownload('public/comment/201405/pfcfchengjian3.pdf');//下载文峰pdf
		break;
		case 6 :forceDownload('public/comment/201405/pfcf88wenfeng.pdf');//下载嘉良
		break;	
		default:
  			echo "您的请求不存在，<a href='index.php'>点击返回</a>";
			
			}
		
		
		}
	//lu 设置批量投资金额
/*	function bid_more(){
		
		// 获取 正在投资中借款列表    publish_wait =0 AND deal_status=1    load_money
		$deal_arr = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal where publish_wait =0 AND deal_status=1 AND is_delete=0 AND  load_money=0 ");
        foreach($deal_arr as $kdeal=>$vdeal){
			 $user_id[]=$vdeal['user_id'];
			}
		//获取id小于30 的用户
		$user_a=$GLOBALS['db']->getAll("select user_name,id from ".DB_PREFIX."user where id<=30 and  is_delete=0 order by id asc ");
		// 过滤发标用户 
		foreach($user_a as $kg=>$vg){
			if(!in_array($vg['id'],$user_id)){
				$user_arr[$kg]['user_name']=$vg['user_name'];
			    $user_arr[$kg]['id']=$vg['id'];
				}
			}
			// 数组元素随机排序	
		 	shuffle($user_arr);
			// 获取数组10个 元素
			for($i=0;$i<=9;$i++){
				 $user_array[]=$user_arr[$i];
				}
	 		

		
		$GLOBALS['tmpl']->assign("user_arr",$user_array);
		$GLOBALS['tmpl']->assign("deal_arr",$deal_arr);
		$GLOBALS['tmpl']->display("page/deal_bid_more.html");
		} */
		
	//LU 批量投资
	/*function dobid_more(){

		$id = intval($_REQUEST['deal_id']); // fabiao_time
		
		if($id==0||$id==''){
			showErr("未选择投资产品");
			
			}
		$deal_all = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal where id=".$id." ");
		//判断是否为 0% 的投资产品
		if($deal_all[0]['load_money']!=0){
			
			showErr(sprintf($GLOBALS['lang']['DEAL_LOAN_NOT_ENOUGHT'],format_price($deal['borrow_amount'] - $deal['load_money'])));
			return false;
			}
		//重组数组  去掉value=0  user_id=0 的提交	
		$jj=0;
		for($i=0;$i<=9;$i++){
			if($_REQUEST['user_id'.$i]==0){
				showErr("用户id不能为0");
				}
			if(trim($_REQUEST['bid_money'.$i])>0&&$_REQUEST['user_id'.$i]>0){
					$data_a[$jj]['bid_money']=$_REQUEST['bid_money'.$i];
					$data_a[$jj]['user_id']=$_REQUEST['user_id'.$i];
					$data_a[$jj]['user_name']=$_REQUEST['user_name'.$i];
					$jj++;
				}
			
			} //print_r($data_a);exit;
		for($i=0;$i<=$jj-1;$i++){
		$deal = get_deal($_REQUEST['deal_id']);
		
		$data['user_id'] = $data_a[$i]['user_id'];
		$data['user_name'] = $data_a[$i]['user_name'];
		$data['deal_id'] = $id;
		$data['money'] = trim($data_a[$i]['bid_money']);
		//$data['create_time'] = get_gmtime();
		// 更改投资时间成 发标时间
		$n_time=rand(15,60);
	
		$_REQUEST['fabiao_time']=$_REQUEST['fabiao_time']+60*$n_time; 
		$data['create_time'] = $_REQUEST['fabiao_time'];//

		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load",$data,"INSERT");
		$load_id = $GLOBALS['db']->insert_id();
		//更改资金记录
			if($load_id > 0){
				
			require_once APP_ROOT_PATH."system/libs/user.php";	
			modify_account(array('money'=>-trim($data_a[$i]['bid_money']),'score'=>0),$data_a[$i]['user_id']);
			$deal = get_deal($id);
			sys_user_status($$data_a[$i]['user_id']);
			}
			
			
		//print_r($id);exit;
		}	
		app_redirect(url("index.php?ctl=deal"));
		
	}*/
	
	
}

?>
