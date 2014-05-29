<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/uc.php';
require APP_ROOT_PATH."app/Lib/deal.php";
class uc_dealModule extends SiteBaseModule
{
	public function refund(){
		$user_id = $GLOBALS['user_info']['id'];
		
		$status = intval($_REQUEST['status']);
		
		$GLOBALS['tmpl']->assign("status",$status);
		
		//输出借款记录
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			
		$deal_status = 4;
		if($status == 1){
			$deal_status = 5;
		}
		//lu  偿还贷款收索功能
		//判断是否ajax提交
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])&& strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
			$page = intval($_REQUEST['p']);
				if($page==0)
					$page = 1;
				$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
				//app_conf("DEAL_PAGE_SIZE")==10, $limit=0,10
				
			//已还清借款
			if($_GET['status']==1){
				$deal_status=5; 
				}
				//还款列表
				else{
				$deal_status=4; 
					}
			$deal_name=$_POST['deal_name']; 		
			$result = get_deal_list($limit,0,"deal_status =  '".$deal_status."'  and name like '%".$deal_name."%' ","id DESC");
			//分页
			$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		    $p  =  $page->show();
			$arr=array('result'=>$result['list'],'page'=>$p);
			if(empty($result['list'])){
				echo  json_encode("没有找到相应的产品");
				}
				else{
			echo json_encode($arr);
				}
			}
	//lu 不是AJAX提交
	else {
		
		$result = get_deal_list($limit,0,"deal_status =$deal_status AND user_id=".$user_id,"id DESC");
		$GLOBALS['tmpl']->assign("deal_list",$result['list']);
		
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_REFUND']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_refund.html");
		$GLOBALS['tmpl']->display("page/uc.html");	
	}
}
	public function contract(){
		$id = intval($_REQUEST['id']);
		if($id == 0){
			showErr("操作失败！");
		}
		$deal = get_deal($id);
		if(!$deal || $deal['user_id']!=$GLOBALS['user_info']['id'] || $deal['deal_status']!=4){
			showErr("操作失败！");
		}
		if($deal['agency_id'] > 0){
			$agency = $GLOBALS['db']->getRow("select * FROM ".DB_PREFIX."deal_agency WHERE id=".$deal['agency_id']." ");
			$deal['agency_name'] = $agency['name'];
			$deal['agency_address'] = $agency['address'];
		}
		
		$GLOBALS['tmpl']->assign('deal',$deal);
		
		$loan_list = $GLOBALS['db']->getAll("select * FROM ".DB_PREFIX."deal_load WHERE deal_id=".$id." ORDER BY create_time ASC");
		foreach($loan_list as $k=>$v){
			if($deal['loantype']==0)
			{
				$loan_list[$k]['get_repay_money'] = pl_it_formula($v['money'],$deal['rate']/12/100,$deal['repay_time']);
			}
			elseif($deal['loantype']==1){
				$loan_list[$k]['get_repay_money'] = av_it_formula($v['money'],$deal['rate']/12/100);
			}
			elseif($deal['loantype']==2){
				$loan_list[$k]['get_repay_money'] = $v['money']*$deal['rate']/12/100 * $deal['repay_time'] + $v['money'];
			}
			
			if($deal['repay_time_type']==0){
				$loan_list[$k]['get_repay_money'] = $v['money']*$deal['rate']/12/100 + $v['money'];
			}
		}
		
		$GLOBALS['tmpl']->assign('loan_list',$loan_list);
		
		if($deal['agency_id'] > 0){
			$contract = $GLOBALS['tmpl']->fetch("str:".app_conf("CONTRACT_1"));
		}
		else
			$contract = $GLOBALS['tmpl']->fetch("str:".app_conf("CONTRACT_0"));
		$GLOBALS['tmpl']->assign('contract',$contract);
		$GLOBALS['tmpl']->display("inc/uc/uc_deal_contract.html");	
	}
	
	//正常还款操作界面
	public function quick_refund(){
		$id = intval($_REQUEST['id']);
		if($id == 0){
			showErr("操作失败！");
		}
		$deal = get_deal($id);
		if(!$deal || $deal['user_id']!=$GLOBALS['user_info']['id'] || $deal['deal_status']!=4){
			showErr("操作失败！");
		}
		$GLOBALS['tmpl']->assign('deal',$deal);
		
		//还款列表
		$loan_list = get_deal_load_list($deal);
		$GLOBALS['tmpl']->assign("loan_list",$loan_list);
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_REFUND']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_quick_refund.html");
		$GLOBALS['tmpl']->display("page/uc.html");	
	}
	
	//正常还款执行界面
	public function repay_borrow_money(){
		$id = intval($_REQUEST['id']);
		if($id == 0){
			showErr("操作失败！",1);
			exit();
		}
		$deal = get_deal($id);
		if(!$deal || $deal['user_id']!=$GLOBALS['user_info']['id'] || $deal['deal_status']!=4){
			showErr("操作失败！",1);
			exit();
		}
		$ids = explode(",",$_REQUEST['ids']);
		
		//还款列表
		$loan_list = get_deal_load_list($deal);
		$first_key = -1;
		$find_first_key = false;
		
		
		$repay_data = null;
		
		//需还多少
		$must_repay = 0;
		//管理费多少
		$must_fee = 0;
		//罚息
		$must_impose = 0;
		$time = get_gmtime();
		
		$pt_impose = 0;
		$yz_impose = 0;
		$k_repay_time = 0 ;
		
		foreach($loan_list as $k=>$v){
			if($v['has_repay']<>1){
				if(!$find_first_key){
					$first_key = $k;
					$find_first_key = true;
				}
				
				if(in_array($k,$ids)){
					if($k_repay_time==0)
						$k_repay_time = $v['repay_day'];
					$repay_data[$k]['deal_id'] = $id;
					$repay_data[$k]['user_id'] = $GLOBALS['user_info']['id'];
					//月还本息
					$repay_data[$k]['repay_money'] = round($v['month_repay_money'],2);
					$must_repay +=round($v['month_repay_money'],2);
					
					//管理费
					$repay_data[$k]['manage_money'] = round($v['month_manage_money'],2);
					$must_fee += round($v['month_manage_money'],2);
					
					//罚息
					$repay_data[$k]['impose_money'] = round($v['impose_money'],2);
					$must_impose += round($v['impose_money'],2);
					
					$repay_data[$k]['status'] = 0;
					if(to_date($v['repay_day'],"Y-m-d") == to_date($time,"Y-m-d")){
						//准时
						$repay_data[$k]['status'] = 1;
					}
					elseif($v['impose_money'] >0){
						//逾期
						$repay_data[$k]['status'] = 2;
						if($v['impose_day'] < app_conf('YZ_IMPSE_DAY')){
							//普通逾期
							++$pt_impose;
						}
						
						else{
							//严重逾期
							$repay_data[$k]['status'] = 3;
							++$yz_impose;
						}
					}
					$repay_data[$k]['repay_time'] = $v['repay_day'];
					$repay_data[$k]['true_repay_time'] = $time;
				}
			}
		}
		
		//不等于到期还本息时才判断是否按顺序
		if($deal['loantype'] !=2){		
			if($first_key!=$ids[0]){
				showErr("还款失败，请按顺序还款！",1);
				exit();
			}
		}
				
		if(($must_repay+$must_fee+$must_impose)>$GLOBALS['user_info']['money']){
			showErr("对不起，您的余额不足！",1);
			exit();
		}
		
		//录入到还款列表
		require APP_ROOT_PATH.'system/libs/user.php';
		foreach($repay_data as $k=>$v){
			$deal_repay_id = 0;
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$v,"INSERT",'','SILENT');
			$deal_repay_id = $GLOBALS['db']->insert_id();
			if(intval($deal_repay_id) == 0)
			{
				showErr("对不起，处理数据失败请联系客服！",1);
				exit();
			}
			else{
				//更新用户账户资金记录
				modify_account(array("money"=>-$v['impose_money']),$GLOBALS['user_info']['id'],"标:".$deal['id'].",期:".($k+1).",逾期罚息");
				modify_account(array("money"=>-$v['manage_money']),$GLOBALS['user_info']['id'],"标:".$deal['id'].",期:".($k+1).",借款管理费");
				modify_account(array("money"=>-$v['repay_money']),$GLOBALS['user_info']['id'],"标:".$deal['id'].",期:".($k+1).",偿还本息");
			}
		}
		
		
		//信用额度
		for($i=0;$i<$pt_impose;$i++){
			modify_account(array("point"=>trim(app_conf('IMPOSE_POINT'))),$GLOBALS['user_info']['id'],"标:".$deal['id'].",期:".($i+1).",逾期还款");
		}
		for($i=0;$i<$yz_impose;$i++){
			modify_account(array("point"=>trim(app_conf('YZ_IMPOSE_POINT'))),$GLOBALS['user_info']['id'],"标:".$deal['id'].",期:".($i+1).",严重逾期还款");
		}
		
		$next_loan = $loan_list[$ids[count($ids)-1]+1];
		$content = "您好，您在".app_conf("SHOP_TITLE")."的借款 “<a href=\"".$deal['url']."\">".$deal['name']."</a>”成功还款".($must_repay+$must_impose+$must_impose)."元，";
		if($next_loan){
			$content .= "本笔借款的下个还款日为".to_date($next_loan['repay_day'],"Y年m月d日")."，需要本息".round($next_loan['month_repay_money'],2)."元。";
			$GLOBALS['db']->query("UPDATE ".DB_PREFIX."deal SET next_repay_time = '".$next_loan['repay_day']."' WHERE id=".$id);
		}
		else{
			$content .= "本笔借款已还款完毕！";
			
			//判断获取的信用是否超过限制
			if($GLOBALS['db']->getOne("SELECT sum(point) FROM ".DB_PREFIX."user_log WHERE log_info='还清借款' AND user_id=".$GLOBALS['user_info']['id']) < (int)trim(app_conf('CONF_REPAY_SUCCESS_LIMIT'))){
				//获取上一次还款时间
				$befor_repay_time = $GLOBALS['db']->getOne("SELECT MAX(log_time) FROM ".DB_PREFIX."user_log WHERE log_info='还清借款' AND user_id=".$GLOBALS['user_info']['id']);
				$day = ceil(($time-$befor_repay_time)/24/3600);
				//当天数大于等于间隔时间 获得信用
				if($day >= (int)trim(app_conf('REPAY_SUCCESS_DAY'))){
					modify_account(array("point"=>trim(app_conf('REPAY_SUCCESS_POINT'))),$GLOBALS['user_info']['id'],"标:".$deal['id'].",还清借款");
				}
			}
			
			//用户获得额度
			modify_account(array("quota"=>trim(app_conf('USER_REPAY_QUOTA'))),$GLOBALS['user_info']['id'],"标:".$deal['id'].",还清借款获得额度");
		}
		
	
		send_user_msg("",$content,0,$GLOBALS['user_info']['id'],$time,0,true,8);
		//短信通知
		if(app_conf("SMS_ON")==1&&app_conf('SMS_SEND_REPAY')==1){
			$sms_content = "尊敬的".app_conf("SHOP_TITLE")."用户".$GLOBALS['user_info']['user_name']."，您成功还款".($must_repay+$must_impose+$must_impose)."元，感谢您的关注和支持。【".app_conf("SHOP_TITLE")."】";
			$msg_data['dest'] = $GLOBALS['user_info']['mobile'];
			$msg_data['send_type'] = 0;
			$msg_data['title'] = $msg_data['content'] = addslashes($sms_content);
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = $time;
			$msg_data['user_id'] = $GLOBALS['user_info']['id'];
			$msg_data['is_html'] = 0;
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入				
		}
		
		syn_deal_status($id);
		sys_user_status($GLOBALS['user_info']['id'],false,true);
		
		
		//用户回款
		$user_load_ids = $GLOBALS['db']->getAll("SELECT deal_id,user_id,money FROM ".DB_PREFIX."deal_load WHERE deal_id=".$id);
		foreach($user_load_ids as $k=>$v){
			
			$v['repay_start_time'] = $deal['repay_start_time'];
			$v['repay_time'] = $deal['repay_time'];
			$v['rate'] = $deal['rate'];
			$v['u_key'] = $k;
			$user_loan_list = get_deal_user_load_list($v,$deal['loantype'],$deal['repay_time_type']);
			$loan_user_info = array();
			foreach($user_loan_list as $kk=>$vv){
				if($vv['has_repay']==0){//借入者已还款，但是没打款到借出用户中心
					$user_load_data['deal_id'] = $v['deal_id'];
					$user_load_data['user_id'] = $v['user_id'];
					$user_load_data['repay_time'] = $vv['repay_day'];
					$user_load_data['true_repay_time'] = $time;
					$user_load_data['is_site_repay'] = 0;
					$user_load_data['status'] = 0;
					
					//当默认还款时间小于当前还款时间  或者 指针小于等于当前还款的日期
					if($vv['repay_day'] < $k_repay_time || $kk <= max($ids)){
						//小于提前还款的话
						if($deal['loantype']==0){//等额本息的时候才通过公式计算剩余多少本金
							$user_load_data['self_money'] = $vv['month_repay_money'] - get_benjin($kk,$deal['repay_time'],$v['money'],$vv['month_repay_money'],$deal['rate'])*$deal['rate']/12/100;
						}
						elseif($deal['loantype']==1){//每月还息，到期还本
							if($kk+1 == count($user_loan_list)){//判断是否是最后一期
								$user_load_data['self_money'] = $v['money'];
							}
							else{
								$user_load_data['self_money'] = 0;
							}
						}
						elseif($deal['loantype']==2){//到期还本息
							if($kk+1 == count($user_loan_list)){//判断是否是最后一期
								$user_load_data['self_money'] = $v['money'];
							}
							else{
								$user_load_data['self_money'] = 0;
							}
						}
						$user_load_data['repay_money'] = $vv['month_repay_money'];
						$user_load_data['manage_money'] = $vv['month_manage_money'];
						$user_load_data['impose_money'] = $vv['impose_money'];
						if($vv['status']>0)
							$user_load_data['status'] = $vv['status'] - 1;
						$user_load_data['l_key'] = $kk;
						$user_load_data['u_key'] = $k;
						$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$user_load_data,"INSERT","","SILENT");
						$load_repay_id = $GLOBALS['db']->insert_id();
						
						if($load_repay_id > 0){
						
							$content = "您好，您在".app_conf("SHOP_TITLE")."的投标 “<a href=\"".$deal['url']."\">".$deal['name']."</a>”成功还款".($vv['month_repay_money']+$vv['impose_money'])."元，";
							$unext_loan = $user_loan_list[$kk+1];
							
							if($unext_loan){
								$content .= "本笔投标的下个还款日为".to_date($unext_loan['repay_day'],"Y年m月d日")."，需还本息".round($unext_loan['month_repay_money'],2)."元。";
							}
							else{
								$all_repay_money= round($GLOBALS['db']->getOne("SELECT (sum(repay_money)-sum(self_money) + sum(impose_money)) as shouyi FROM ".DB_PREFIX."deal_load_repay WHERE deal_id=".$v['deal_id']." AND user_id=".$v['user_id']),2);
								$all_impose_money = round($GLOBALS['db']->getOne("SELECT sum(impose_money) FROM ".DB_PREFIX."deal_load_repay WHERE deal_id=".$v['deal_id']." AND user_id=".$v['user_id']),2);
								$content .= "本次投标共获得收益:".$all_repay_money."元,其中违约金为:".$all_impose_money."元,本次投标已回款完毕！";
								
								//投标不获得信用
								/*
								//判断获取的信用是否超过限制
								if($GLOBALS['db']->getOne("SELECT sum(point) FROM ".DB_PREFIX."user_log WHERE log_info='回款完毕' AND user_id=".$v['user_id']) < (int)trim(app_conf('CONF_REPAY_SUCCESS_LIMIT'))){
									//获取上一次还款时间
									$befor_repay_time = $GLOBALS['db']->getOne("SELECT MAX(log_time) FROM ".DB_PREFIX."user_log WHERE log_info='回款完毕' AND user_id=".$v['user_id']);
									$day = ceil((get_gmtime()-$befor_repay_time)/24/3600);
									//当天数大于等于间隔时间 获得信用
									if($day >= (int)trim(app_conf('REPAY_SUCCESS_DAY'))){
										modify_account(array("point"=>trim(app_conf('REPAY_SUCCESS_POINT'))),$v['user_id'],"回款完毕");
									}
								}*/
								
							}
							if($user_load_data['impose_money'] !=0 || $user_load_data['manage_money'] !=0 || $user_load_data['repay_money']!=0){
								//更新用户账户资金记录
								modify_account(array("money"=>$user_load_data['impose_money']),$v['user_id'],"标:".$deal['id'].",期:".($kk+1).",逾期罚息");
								
								modify_account(array("money"=>-$user_load_data['manage_money']),$v['user_id'],"标:".$deal['id'].",期:".($kk+1).",投标管理费");
								
								modify_account(array("money"=>$user_load_data['repay_money']),$v['user_id'],"标:".$deal['id'].",期:".($kk+1).",回报本息");
								$msg_conf = get_user_msg_conf($v['user_id']);
								
								
								//短信通知
								if(app_conf("SMS_ON")==1&&app_conf('SMS_REPAY_TOUSER_ON')==1){
									if(!$loan_user_info[$v['user_id']])
										$loan_user_info[$v['user_id']] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$v['user_id']);
									
									$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_LOAD_REPAY_SMS'");
									$tmpl_content = $tmpl['content'];
									
									$notice['user_name'] = $loan_user_info[$v['user_id']]['user_name'];
									$notice['deal_name'] = $deal['sub_name'];
									$notice['deal_url'] = $deal['url'];
									$notice['site_name'] = app_conf("SHOP_TITLE");
									$notice['repay_money'] = $vv['month_repay_money']+$vv['impose_money'];
									if($unext_loan){
										$notice['need_next_repay'] = $unext_loan;
										$notice['next_repay_time'] = to_date($unext_loan['repay_day'],"Y年m月d日");
										$notice['next_repay_money'] = round($unext_loan['month_repay_money'],2);
									}
									else{
										$notice['all_repay_money'] = $all_repay_money;
										$notice['impose_money'] = $all_impose_money;
									}
									
									$GLOBALS['tmpl']->assign("notice",$notice);
									$sms_content = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
									
									$msg_data['dest'] = $loan_user_info[$v['user_id']]['mobile'];
									$msg_data['send_type'] = 0;
									$msg_data['title'] = $msg_data['content'] = addslashes($sms_content);
									$msg_data['send_time'] = 0;
									$msg_data['is_send'] = 0;
									$msg_data['create_time'] = $time;
									$msg_data['user_id'] = $v['user_id'];
									$msg_data['is_html'] = 0;
									$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入				
								}
								
								//站内信
								if($msg_conf['sms_bidrepaid']==1)
									send_user_msg("",$content,0,$v['user_id'],$time,0,true,9);
								//邮件
								if($msg_conf['mail_bidrepaid']==1 && app_conf('MAIL_ON')==1){
									if(!$loan_user_info[$v['user_id']])
										$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$v['user_id']);
									else
										$user_info = $loan_user_info[$v['user_id']];
										
									$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_LOAD_REPAY_EMAIL'");
									$tmpl_content = $tmpl['content'];
									
									$notice['user_name'] = $user_info['user_name'];
									$notice['deal_name'] = $deal['sub_name'];
									$notice['deal_url'] = $deal['url'];
									$notice['site_name'] = app_conf("SHOP_TITLE");
									$notice['site_url'] = get_domain().APP_ROOT;
									$notice['help_url'] = get_domain().url("index","helpcenter");
									$notice['msg_cof_setting_url'] = get_domain().url("index","uc_msg#setting");
									$notice['repay_money'] = $vv['month_repay_money']+$vv['impose_money'];
									if($unext_loan){
										$notice['need_next_repay'] = $unext_loan;
										$notice['next_repay_time'] = to_date($unext_loan['repay_day'],"Y年m月d日");
										$notice['next_repay_money'] = round($unext_loan['month_repay_money'],2);
									}
									else{
										$notice['all_repay_money'] = $all_repay_money;
										$notice['impose_money'] = $all_impose_money;
									}
									
									$GLOBALS['tmpl']->assign("notice",$notice);
									
									$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
									$msg_data['dest'] = $user_info['email'];
									$msg_data['send_type'] = 1;
									$msg_data['title'] = "“".$deal['name']."”回款通知";
									$msg_data['content'] = addslashes($msg);
									$msg_data['send_time'] = 0;
									$msg_data['is_send'] = 0;
									$msg_data['create_time'] = $time;
									$msg_data['user_id'] = $user_info['id'];
									$msg_data['is_html'] = $tmpl['is_html'];
									$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
								}
							}
						}
					}
				}
			}
		}
		showSuccess("操作成功!",1);
	}
	
	//提前还款操作界面
	public function inrepay_refund(){
		$id = intval($_REQUEST['id']);
		if($id == 0){
			showErr("操作失败！");
		}
		$deal = get_deal($id);
		if(!$deal || $deal['user_id']!=$GLOBALS['user_info']['id'] || $deal['deal_status']!=4){
			showErr("操作失败！");
		}
		$GLOBALS['tmpl']->assign('deal',$deal);
		$time = get_gmtime();
		$impose_money = 0;
		//还了几期了
		$has_repay_count =  $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$id);
		//计算罚息
		$loan_list = get_deal_load_list($deal);
		foreach($loan_list as $k=>$v){
			if($k>($has_repay_count-1))
			{
				$impose_money +=$v['impose_money'];
			}
		}
		
		//月利率
		$rate = $deal['rate']/12/100;
		
		//计算剩多少本金
		$benjin = $deal['borrow_amount'];
		if($deal['loantype']==0){//等额本息的时候才通过公式计算剩余多少本金
			for($i=1;$i<=$has_repay_count;$i++){
				$benjin = $benjin - $deal['month_repay_money'] + $benjin*$rate;
			}
			
			$impose_money += ($benjin - $deal['month_repay_money'] + $benjin*$rate) * (int)trim(app_conf('COMPENSATE_FEE'))/100;
			$total_repay_money = $benjin + $benjin*$rate;
		}
		elseif($deal['loantype']==1){//每月付息，到期还本
			$impose_money += $benjin * (int)trim(app_conf('COMPENSATE_FEE'))/100;
			$total_repay_money = $benjin + $deal['month_repay_money'];
		}
		elseif($deal['loantype']==2){//到期还本息
			$impose_money += $benjin * (int)trim(app_conf('COMPENSATE_FEE'))/100;
			$total_repay_money = $benjin + $benjin*$rate;
			//计算应缴多少管理费
			$now_ym = to_date($time,"Y-m");
			$i=0;
			foreach($loan_list as $k=>$v){
				++$i;
				if($now_ym==to_date($v['repay_day'],"Y-m")){
					$deal['month_manage_money'] = $benjin * trim(app_conf('MANAGE_FEE'))/100 * $i;
				}
			}
			
			$GLOBALS['tmpl']->assign('true_all_manage_money',$deal['month_manage_money']);
		}
		
		
		$GLOBALS['tmpl']->assign("impose_money",$impose_money);
		$GLOBALS['tmpl']->assign("total_repay_money",$total_repay_money);
		
		$true_total_repay_money = $total_repay_money + $impose_money + $deal['month_manage_money'];
		$GLOBALS['tmpl']->assign("true_total_repay_money",$true_total_repay_money);
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_REFUND']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_inrepay_refund.html");
		$GLOBALS['tmpl']->display("page/uc.html");	
	}
	//提前还款执行程序
	public function inrepay_repay_borrow_money(){
		$id = intval($_REQUEST['id']);
		if($id == 0){
			showErr("操作失败！",1);
			exit();
		}
		$deal = get_deal($id);
		if(!$deal || $deal['user_id']!=$GLOBALS['user_info']['id'] || $deal['deal_status']!=4){
			showErr("操作失败！",1);
			exit();
		}
		$time = get_gmtime();
		$impose_money = 0;
		//还了几期了
		$has_repay_count =  $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$id);
		//计算罚息
		$loan_list = get_deal_load_list($deal);
		$k_repay_time = 0;
		foreach($loan_list as $k=>$v){
			if($k>($has_repay_count-1))
			{
				if($k_repay_time==0)
					$k_repay_time = $v['repay_time'];
				$impose_money +=$v['impose_money'];
			}
		}
		
		if($impose_money > 0){
			showErr("请将逾期未还的借款还完才可以进行此操作！",1);
			exit();
		}
		
		//月利率
		$rate = $deal['rate']/12/100;
		
		$impose_money = 0;
		//计算剩多少本金
		$benjin = $deal['borrow_amount'];
		if($deal['loantype']==0){//等额本息的时候才通过公式计算剩余多少本金
			for($i=1;$i<=$has_repay_count;$i++){
				$benjin = $benjin - ($deal['month_repay_money'] - $benjin*$rate);
			}
		
			$impose_money = ($benjin - $deal['month_repay_money'] + $benjin*$rate) * (int)trim(app_conf('COMPENSATE_FEE'))/100;
			$total_repay_money = $benjin + $benjin*$rate;
		}
		elseif($deal['loantype']==1){//每月付息，到期还本
			$impose_money = $benjin * (int)trim(app_conf('COMPENSATE_FEE'))/100;
			$total_repay_money = $benjin + $deal['month_repay_money'];
		}
		elseif($deal['loantype']==2){//到期还本息
			$impose_money += $benjin * (int)trim(app_conf('COMPENSATE_FEE'))/100;
			$total_repay_money = $benjin + $benjin*$rate;
			
			//计算应缴多罚息 多少管理费
			$now_ym = to_date($time,"Y-m");
			$i=0;
			foreach($loan_list as $k=>$v){
				++$i;
				if($now_ym==to_date($v['repay_day'],"Y-m")){
					$deal['month_manage_money'] = $benjin * trim(app_conf('MANAGE_FEE'))/100 * $i;
				}
			}
		}
		
		$GLOBALS['tmpl']->assign("impose_money",$impose_money);
		$GLOBALS['tmpl']->assign("total_repay_money",$total_repay_money);
		
		$true_total_repay_money = $total_repay_money + $impose_money + $deal['month_manage_money'];
		
		if(($total_repay_money+$impose_money+$deal['month_manage_money'])>$GLOBALS['user_info']['money']){
			showErr("对不起，您的余额不足！",1);
			exit();
		}
		
		
		//录入到提前还款列表
		$inrepay_data['deal_id'] = $id;
		$inrepay_data['user_id'] = $GLOBALS['user_info']['id'];
		$inrepay_data['repay_money'] = round($total_repay_money);
		$inrepay_data['impose_money'] = round($impose_money,2);
		$inrepay_data['manage_money'] = round($deal['month_manage_money']);
		$inrepay_data['repay_time'] = $k_repay_time;
		$inrepay_data['true_repay_time'] = $time;
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_inrepay_repay",$inrepay_data,"INSERT");
		$inrepay_id = $GLOBALS['db']->insert_id();
		if($inrepay_id==0){
			showErr("对不起，数据处理失败，请联系客服！",1);
			exit();
		}
		
		//录入还款列表
		$after_time = $GLOBALS['db']->getOne("SELECT repay_time FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$id." ORDER BY repay_time DESC");
		if($after_time==""){
			$after_time = $deal['repay_start_time'];
		}
		
		$temp_ids[] = array();
		for($i=0;$i<($deal['repay_time']-$has_repay_count);$i++){
			$repay_data['deal_id'] = $id;
			$repay_data['user_id'] = $GLOBALS['user_info']['id'];
			$repay_data['repay_time'] = $after_time = next_replay_month($after_time);
			$repay_data['true_repay_time'] = $time;
			$repay_data['status'] = 0;
			if($i==0){
				$repay_data['repay_money'] = round($deal['month_repay_money'],2);
				$repay_data['impose_money'] = round($impose_money,2);
				$repay_data['manage_money'] = round($deal['month_manage_money']);
			}
			else{
				if($deal['loantype']==0){//等额本息
					$repay_data['repay_money'] = $benjin/($deal['repay_time']-$has_repay_count);
				}
				elseif($deal['loantype']==1){//每月还息
					if($i+1==($deal['repay_time']-$has_repay_count)){
						$repay_data['repay_money'] = $benjin;
					}
					else{
						$repay_data['repay_money'] =0;
					}
				}
				elseif($deal['loantype']==2){//每月还息
					if($i+1==($deal['repay_time']-$has_repay_count)){
						$repay_data['repay_money'] = $benjin;
					}
					else{
						$repay_data['repay_money'] =0;
					}
				}
				$repay_data['impose_money'] = 0;
				$repay_data['manage_money'] = 0;
			}
			$deal_repay_id = 0;
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$repay_data,"INSERT");
			$deal_repay_id = $GLOBALS['db']->insert_id();
			
			//假如出错 删除掉原来的以插入的数据
			if(intval($deal_repay_id)==0)
			{
				if($temp_ids){
					$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."deal_repay WHERE id in (".implode(",",$temp_ids).")");
				}
				showErr("对不起，处理数据失败请联系客服！",1);
				exit();
			}
			else{
				$temp_ids[] = $deal_repay_id;
			}
		}
		
		//更新用户账户资金记录
		require APP_ROOT_PATH.'system/libs/user.php';
		modify_account(array("money"=>-round($impose_money)),$GLOBALS['user_info']['id'],"标:".$deal['id'].",提前还款违约金");
		modify_account(array("money"=>-round(($total_repay_money+$deal['month_manage_money']),2)),$GLOBALS['user_info']['id'],"标:".$deal['id'].",提前还款");
		
		//用户获得额度
		modify_account(array("quota"=>trim(app_conf('USER_REPAY_QUOTA'))),$GLOBALS['user_info']['id'],"标:".$deal['id'].",还清借款获得额度");
		
		$content = "您好，您在".app_conf("SHOP_TITLE")."的借款 “<a href=\"".$deal['url']."\">".$deal['name']."</a>”成功提前还款".round($true_total_repay_money,2)."元，";
		$content .= "其中违约金为:".round($impose_money,2)."元,本笔借款已还款完毕！";
		
		send_user_msg("",$content,0,$GLOBALS['user_info']['id'],$time,0,true,8);	
		//短信通知
		if(app_conf("SMS_ON")==1&&app_conf('SMS_SEND_REPAY')==1){
			$sms_content = "尊敬的".app_conf("SHOP_TITLE")."用户".$GLOBALS['user_info']['user_name']."，您成功提前还款".round($true_total_repay_money,2)."元，其中违约金为:".round($impose_money,2)."元,感谢您的关注和支持。【".app_conf("SHOP_TITLE")."】";
			$msg_data['dest'] = $GLOBALS['user_info']['mobile'];
			$msg_data['send_type'] = 0;
			$msg_data['title'] = $msg_data['content'] = addslashes($sms_content);
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = $time;
			$msg_data['user_id'] = $GLOBALS['user_info']['id'];
			$msg_data['is_html'] = 0;
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入				
		}
		
		//判断获取的信用是否超过限制
		if($GLOBALS['db']->getOne("SELECT sum(point) FROM ".DB_PREFIX."user_log WHERE (log_info='标:".$deal['id'].",还清借款' or log_info='还清借款') AND user_id=".$GLOBALS['user_info']['id']) < (int)trim(app_conf('CONF_REPAY_SUCCESS_LIMIT'))){
			//获取上一次还款时间
			$befor_repay_time = $GLOBALS['db']->getOne("SELECT MAX(log_time) FROM ".DB_PREFIX."user_log WHERE (log_info='标:".$deal['id'].",还清借款' or log_info='还清借款') AND user_id=".$GLOBALS['user_info']['id']);
			$day = ceil(($time-$befor_repay_time)/24/3600);
			//当天数大于等于间隔时间 获得信用
			if($day >= (int)trim(app_conf('REPAY_SUCCESS_DAY'))){
				modify_account(array("point"=>trim(app_conf('REPAY_SUCCESS_POINT'))),$GLOBALS['user_info']['id'],"标:".$deal['id'].",还清借款");
			}
			
			//用户获得额度
			modify_account(array("quota"=>trim(app_conf('USER_REPAY_QUOTA'))),$GLOBALS['user_info']['id'],"标:".$deal['id'].",还清借款获得额度");
		}
				
		syn_deal_status($id);
		sys_user_status($GLOBALS['user_info']['id'],false,true);
		
		
		//用户回款
		$user_load_ids = $GLOBALS['db']->getAll("SELECT deal_id,user_id,money FROM ".DB_PREFIX."deal_load WHERE deal_id=".$id);
		foreach($user_load_ids as $k=>$v){
			//本金
			$user_self_money = 0;
			//本息
			$user_repay_money = 0;
			//违约金
			$user_impose_money = 0;
			//管理费
			$user_manage_money = 0;
			//第几期还的款
			$user_repay_k = 0;
			
			
			$v['repay_start_time'] = $deal['repay_start_time'];
			$v['repay_time'] = $deal['repay_time'];
			$v['rate'] = $deal['rate'];
			$v['u_key'] = $k;
			
			$user_loan_list = get_deal_user_load_list($v,$deal['loantype']);
			$loan_user_info = array();
			foreach($user_loan_list as $kk=>$vv){
				//借入者已还款，但是没打款到借出用户中心
				if($vv['has_repay']==0){
					$user_load_data['deal_id'] = $v['deal_id'];
					$user_load_data['user_id'] = $v['user_id'];
					$user_load_data['repay_time'] = $vv['repay_day'];
					$user_load_data['true_repay_time'] = $time;
					$user_load_data['is_site_repay'] = 0;
					$user_load_data['status'] = 0;
					
					//小于提前还款按正常还款
					if($vv['repay_day'] < $k_repay_time){
						
						//等额本息的时候才通过公式计算剩余多少本金
						if($deal['loantype']==0){
							$user_load_data['self_money'] = $vv['month_repay_money'] - get_benjin($kk,$deal['repay_time'],$v['money'],$vv['month_repay_money'],$deal['rate'])*$deal['rate']/12/100;
						}
						//每月还息，到期还本
						elseif($deal['loantype']==1){
							$user_load_data['self_money'] = 0;
						}
						//到期还本息
						elseif($deal['loantype']==2){
							$user_load_data['self_money'] = 0;
						}
						
						$user_load_data['repay_money'] = $vv['month_repay_money'];
						$user_load_data['manage_money'] = $vv['month_manage_money'];
						$user_load_data['impose_money'] = $vv['impose_money'];
						if($vv['status']>0)
							$user_load_data['status'] = $vv['status'] - 1;
						
						$content = "您好，您在".app_conf("SHOP_TITLE")."的投标 “<a href=\"".$deal['url']."\">".$deal['name']."</a>”成功还款".($vv['month_repay_money']+$vv['impose_money'])."元，";
						$unext_loan = $user_loan_list[$kk+1];
						if($unext_loan){
							$content .= "本笔投标的下个还款日为".to_date($unext_loan['repay_day'],"Y年m月d日")."，需要本息".round($unext_loan['month_repay_money'],2)."元。";
						}
						$user_self_money +=(float)$user_load_data['self_money'];
						if($user_load_data['impose_money']!=0||$user_load_data['manage_money']!=0||$user_load_data['repay_money']!=0){
							//更新用户账户资金记录
							modify_account(array("money"=>$user_load_data['impose_money']),$v['user_id'],"标:".$deal['id'].",期:".($kk+1).",逾期罚息");
							
							modify_account(array("money"=>-$user_load_data['manage_money']),$v['user_id'],"标:".$deal['id'].",期:".($kk+1).",投标管理费");
							
							modify_account(array("money"=>$user_load_data['repay_money']),$v['user_id'],"标:".$deal['id'].",期:".($kk+1).",回报本息");
							
							$msg_conf = get_user_msg_conf($v['user_id']);
							//站内信
							if($msg_conf['sms_bidrepaid']==1)
								send_user_msg("",$content,0,$v['user_id'],$time,0,true,9);
							//邮件
							if($msg_conf['mail_bidrepaid']==1){
								
							}
						}
					}
					//提前还款
					//当前提前还款的第一个月
					else{
						
						if($vv['repay_day'] == $k_repay_time){
							if($deal['loantype']==0){//等额本息的时候才通过公式计算剩余多少本金
								$user_load_data['self_money'] = $vv['month_repay_money'] - get_benjin($kk,$deal['repay_time'],$v['money'],$vv['month_repay_money'],$deal['rate'])*$deal['rate']/12/100;
								$user_load_data['impose_money'] = ($user_load_data['self_money'] - $vv['month_repay_money'] + $user_load_data['self_money']*$v['rate']) * (int)trim(app_conf('COMPENSATE_FEE'))/100;
							}
							elseif($deal['loantype']==1){//每月还息，到期还本
								$user_load_data['self_money'] = $v['money'];
								$user_load_data['impose_money'] = $v['money'] * (int)trim(app_conf('COMPENSATE_FEE'))/100;
							}
							elseif($deal['loantype']==2){//每月还息，到期还本
								$user_load_data['self_money'] = $v['money'];
								$user_load_data['impose_money'] = $v['money'] * (int)trim(app_conf('COMPENSATE_FEE'))/100;
							}
								
							$user_self_money +=(float)$user_load_data['self_money'];
							
							if($deal['loantype']==0){//等额本息的时候才通过公式计算剩余多少本金
								$user_load_data['repay_money'] = $vv['month_repay_money'];
								$user_load_data['manage_money'] = $vv['month_manage_money'];
							}
							elseif($deal['loantype']==1){
								$user_load_data['repay_money'] = $vv['month_repay_money'] + $v['money'];
								$user_load_data['manage_money'] = $vv['month_manage_money'];
							}
							elseif($deal['loantype']==2){
								$user_load_data['repay_money'] = $v['money'];
								$user_load_data['manage_money'] = $vv['money'] * trim(app_conf('USER_LOAN_MANAGE_FEE')) /100 * ($kk +1) ;
							}
							
							
							$user_repay_k = $kk+1;
						}
						else{
							//其他月份
							
							//等额本息
							if($deal['loantype']==0){
								$user_load_data['self_money'] = $user_load_data['repay_money'] = ($v['money'] - $user_self_money)/($v['repay_time']-$user_repay_k);
								$user_load_data['manage_money'] = 0;
								$user_load_data['impose_money'] = 0;
							}
							//每月还息，到期还本
							elseif($deal['loantype']==1){
								if($user_self_money == 0){
									$user_self_money = $user_load_data['self_money'] = $v['money'];
									$user_load_data['repay_money'] = $vv['month_repay_money'] + $v['money'];
									$user_load_data['impose_money'] = $v['money'] * (int)trim(app_conf('COMPENSATE_FEE'))/100;
									$user_load_data['manage_money'] = $vv['month_manage_money'];
								}
								else{
									$user_load_data['self_money'] = $user_load_data['repay_money'] = 0;
									$user_load_data['manage_money'] = 0;
									$user_load_data['impose_money'] = 0;
								}
							}
							//到期还本息
							elseif($deal['loantype']==2){
								if($user_self_money == 0){
									$user_self_money = $user_load_data['self_money'] = $v['money'];
									$user_load_data['repay_money'] = $vv['month_repay_money'] + $v['money'];
									$user_load_data['impose_money'] = $v['money'] * (int)trim(app_conf('COMPENSATE_FEE'))/100;
									$user_load_data['manage_money'] = $vv['money'] * trim(app_conf('USER_LOAN_MANAGE_FEE')) /100 * ($kk +1) ;
								}
								else{
									$user_load_data['self_money'] = $user_load_data['repay_money'] = 0;
									$user_load_data['manage_money'] = 0;
									$user_load_data['impose_money'] = 0;
								}
							}
							
						}
						
						$user_repay_money += (float)$user_load_data['repay_money'];
						$user_impose_money += (float)$user_load_data['impose_money'];
						$user_manage_money += (float)$user_load_data['manage_money'];
						$user_load_data['l_key'] = $kk;
						$user_load_data['u_key'] = $k;
					}
					
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$user_load_data,"INSERT");
					
				}
			}
			
			if($user_repay_money >0){
				$all_repay_money = round($GLOBALS['db']->getOne("SELECT (sum(repay_money)-sum(self_money) + sum(impose_money)) as shouyi FROM ".DB_PREFIX."deal_load_repay WHERE deal_id=".$v['deal_id']." AND user_id=".$v['user_id']),2);
				$all_impose_money = round($GLOBALS['db']->getOne("SELECT sum(impose_money) FROM ".DB_PREFIX."deal_load_repay WHERE deal_id=".$v['deal_id']." AND user_id=".$v['user_id']),2);
				
				$content = "您好，您在".app_conf("SHOP_TITLE")."的投标 “<a href=\"".$deal['url']."\">".$deal['name']."</a>”提前还款,";
				$content .= "本次投标共获得收益:".$all_repay_money."元,其中违约金为:".$all_impose_money."元,本次投标已回款完毕！";
				
				
				//更新用户账户资金记录
				modify_account(array("money"=>$user_impose_money),$v['user_id'],"标:".$deal['id'].",违约金");
				
				modify_account(array("money"=>-$user_manage_money),$v['user_id'],"标:".$deal['id'].",投标管理费");
				
				modify_account(array("money"=>$user_repay_money),$v['user_id'],"标:".$deal['id'].",回报本息");
							
				$msg_conf = get_user_msg_conf($v['user_id']);
				//短信通知
				if(app_conf("SMS_ON")==1&&app_conf('SMS_REPAY_TOUSER_ON')==1){
					if(!$loan_user_info[$v['user_id']])
						$loan_user_info[$v['user_id']] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$v['user_id']);
					
					$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_LOAD_REPAY_SMS'");
					$tmpl_content = $tmpl['content'];
					
					$notice['user_name'] = $loan_user_info[$v['user_id']]['user_name'];
					$notice['deal_name'] = $deal['sub_name'];
					$notice['deal_url'] = $deal['url'];
					$notice['site_name'] = app_conf("SHOP_TITLE");
					$notice['repay_money'] = $vv['month_repay_money']+$vv['impose_money'];
					
					$notice['all_repay_money'] = $all_repay_money;
					$notice['impose_money'] = $all_impose_money;
					
					$GLOBALS['tmpl']->assign("notice",$notice);
					$sms_content = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
					
					$msg_data['dest'] = $loan_user_info[$v['user_id']]['mobile'];
					$msg_data['send_type'] = 0;
					$msg_data['title'] = $msg_data['content'] = addslashes($sms_content);
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = $time;
					$msg_data['user_id'] = $v['user_id'];
					$msg_data['is_html'] = 0;
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入				
				}
				//站内信
				if($msg_conf['sms_bidrepaid']==1)
					send_user_msg("",$content,0,$v['user_id'],$time,0,true,9);
				//邮件
				if($msg_conf['mail_bidrepaid']==1 && app_conf('MAIL_ON')==1){
					$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$v['user_id']);
					$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_LOAD_REPAY_EMAIL'");
					$tmpl_content = $tmpl['content'];
					
					$notice['user_name'] = $user_info['user_name'];
					$notice['deal_name'] = $deal['sub_name'];
					$notice['deal_url'] = $deal['url'];
					$notice['site_name'] = app_conf("SHOP_TITLE");
					$notice['site_url'] = get_domain().APP_ROOT;
					$notice['help_url'] = get_domain().url("index","helpcenter");
					$notice['msg_cof_setting_url'] = get_domain().url("index","uc_msg#setting");
					$notice['repay_money'] = $vv['month_repay_money']+$vv['impose_money'];
					
					$notice['all_repay_money'] = $all_repay_money;
					$notice['impose_money'] = $all_impose_money;
					
					$GLOBALS['tmpl']->assign("notice",$notice);
					
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
					$msg_data['dest'] = $user_info['email'];
					$msg_data['send_type'] = 1;
					$msg_data['title'] = "“".$deal['name']."”回款通知";
					$msg_data['content'] = addslashes($msg);
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = $time;
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				}
			}
		}
		
		
		showSuccess("操作成功!",1);
	}
	
	public function borrowed(){
		$user_id = $GLOBALS['user_info']['id'];
		
		//输出借款记录
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			
		
		$result = get_deal_list($limit,0,"user_id=".$user_id,"id DESC");

		$GLOBALS['tmpl']->assign("deal_list",$result['list']);
		
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_BORROWED']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_borrowed.html");
		$GLOBALS['tmpl']->display("page/uc.html");	
	}
	
	public function borrow_stat(){
		$user_statics = sys_user_status($GLOBALS['user_info']['id'],false,true);
		$GLOBALS['tmpl']->assign("user_statics",$user_statics);
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_BORROW_STAT']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_borrow_stat.html");
		$GLOBALS['tmpl']->display("page/uc.html");	
	}
	
}
?>