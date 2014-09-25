<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/deal.php';
class dealModule extends SiteBaseModule
{
	public function index(){
		$id = intval($_REQUEST['id']);
		
		$deal = get_deal($id);
		send_deal_contract_email($id,$deal,$deal['user_id']);
		
		if(!$deal)
			app_redirect(url("index")); 
		
		//借款列表
		$load_list = $GLOBALS['db']->getAll("SELECT deal_id,user_id,user_name,money,create_time FROM ".DB_PREFIX."deal_load WHERE deal_id = ".$id);
		
		
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
		$deal['create_time']=date("Y-m-d H:i",$deal['create_time']);
		$deal['repay_start_time']=date("Y-m-d",$deal['repay_start_time']);
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
			$GLOBALS['tmpl']->assign("page_title","成为借出者");
			$GLOBALS['tmpl']->display("page/deal_mobilepaseed.html");
			exit();
		}
		
		$id = intval($_REQUEST['id']);
		$deal = get_deal($id);
		if(!$deal)
			app_redirect(url("index")); 
		
		if($deal['user_id'] == $GLOBALS['user_info']['id']){
			showErr($GLOBALS['lang']['CANT_BID_BY_YOURSELF']);
		}
		require APP_ROOT_PATH.'app/Lib/uc.php';
		$result = get_voucher_list(1,$GLOBALS['user_info']['id']);
		//print_r( $result);exit;
		$GLOBALS['tmpl']->assign("voucher",$result['list'][0]);
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
	
	function dobid(){
				$data['user_id'] = $GLOBALS['user_info']['id'];
		$data['user_name'] = $GLOBALS['user_info']['user_name'];
		$data['deal_id'] = $id;
		$data['money'] = trim($_REQUEST["bid_money"]);
		
		//echo trim($_REQUEST["tmoney"]);//
		if($_REQUEST['check'])//判断复选框是否为勾选
		{
			require APP_ROOT_PATH.'app/Lib/uc.php';
			$result = get_voucher_list(1,$GLOBALS['user_info']['id']);
			
			$data['money']+=$result['list'][0]['money'];
				if(trim($_REQUEST["tmoney"])!==$data['money'])
				{
					showErr('代金券金额不符合系统金额,请联系客服',$ajax);
				}
			//操作数据库
			$GLOBALS['db']->query("update ".DB_PREFIX."ecv set use_count = use_count + 1 where sn = '".$ecv_sn."' and password = '".$ecv_password."'");
		//更新代金券库
			//get_voucher_list($limit,$user_id)					//查询数据库中代金券，然后 //bid_money=金额+代金券金额;bid_money!=提交过来的tmoney;出错!
								//更新代金券数据库使用情况。update  使用次数 +1
		}
		
		echo $data['money'];
		//echo $data['money'] = trim($_REQUEST["bid_money"]);
	//	echo  1;echo 2;
		
		$data['create_time'] = get_gmtime();
		
		//$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load",$data,"INSERT");//插入一条投资目录
	
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
