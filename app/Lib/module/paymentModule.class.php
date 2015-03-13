<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class paymentModule extends SiteBaseModule
{
	public function pay()
	{
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".intval($_REQUEST['id']));
		var_dump($payment_notice);exit;
		/*
		array
  'id' => string '142' (length=3)
  'notice_sn' => string '2015031301265523' (length=16)
  'create_time' => string '1426181215' (length=10)
  'pay_time' => string '0' (length=1)
  'order_id' => string '982' (length=3)
  'is_paid' => string '0' (length=1)
  'user_id' => string '6' (length=1)
  'payment_id' => string '5' (length=1)
  'memo' => string '' (length=0)
  'money' => string '1.0000' (length=6)
  'outer_notice_sn' => string '' (length=0)
  */
		if($payment_notice)
		{
			if($payment_notice['is_paid'] == 0)
			{
				$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$payment_notice['payment_id']);
				
				$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
				
				if($order['pay_status']==2)
				{
					if($order['after_sale']==0)
					{
						app_redirect(url("shop","payment#done",array("id"=>$order['id'])));
						exit;
					}
					else
					{
						showErr($GLOBALS['lang']['DEAL_ERROR_COMMON'],0,APP_ROOT."/",1);
					}
				}
				if($payment_info){
					require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
					$payment_class = $payment_info['class_name']."_payment";
					$payment_object = new $payment_class();
					$payment_code = $payment_object->get_payment_code($payment_notice['id']);
					
				}
				$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['PAY_NOW']);
				$GLOBALS['tmpl']->assign("payment_code",$payment_code);
				$GLOBALS['tmpl']->assign("order",$order);
				$GLOBALS['tmpl']->assign("payment_notice",$payment_notice);
				if(intval($_REQUEST['check'])==1)
				{
					if ($payment_info['class_name']=="Sqepay") {
						app_redirect(url("95epay_query","",array("id"=>intval($_REQUEST['id'])))); //如果是双乾接口，去到查询订单入口,added by nix
					}else {
						showErr($GLOBALS['lang']['PAYMENT_NOT_PAID_RENOTICE']);//付款未完成，如果您已经支付请进入相应支付平台重新通知
					}
				}
				
				$GLOBALS['tmpl']->display("page/payment_pay.html");
			}
			else
			{
				$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
				if($order['pay_status']==2)
				{
					if($order['after_sale']==0)
					app_redirect(url("shop","payment#done",array("id"=>$order['id'])));
					else
					showErr($GLOBALS['lang']['DEAL_ERROR_COMMON'],0,APP_ROOT."/",1);
				}
				else
				showSuccess($GLOBALS['lang']['NOTICE_PAY_SUCCESS'],0,APP_ROOT."/",1);
			}
		}
		else
		{
			showErr($GLOBALS['lang']['NOTICE_SN_NOT_EXIST'],0,APP_ROOT."/",1);
		}
	}
	public function tip()
	{
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".intval($_REQUEST['id']));
		$GLOBALS['tmpl']->assign("payment_notice",$payment_notice);
		$GLOBALS['tmpl']->display("page/payment_tip.html");
	}
	public function done()
	{
		$order_id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
		
		$deal_ids = $GLOBALS['db']->getOne("select group_concat(deal_id) from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
		if(!$deal_ids)
		$deal_ids = 0;
		$order_deals = $GLOBALS['db']->getAll("select d.* from ".DB_PREFIX."deal as d where id in (".$deal_ids.")");

		$GLOBALS['tmpl']->assign("order_info",$order_info);
		$GLOBALS['tmpl']->assign("order_deals",$order_deals);
		$is_coupon = 0;	
		$send_coupon_sms = 0;
		foreach($order_deals as $k=>$v)
		{
			if($v['is_coupon'] == 1&&$v['buy_status']>0)
			{
				$is_coupon = 1;
				break;
			}
		}
		
		foreach($order_deals as $k=>$v)
		{
			if($v['forbid_sms'] == 0)
			{
				$send_coupon_sms = 1;
				break;
			}
		}
	
		$is_lottery = 0;	
		foreach($order_deals as $k=>$v)
		{
			if($v['is_lottery'] == 1&&$v['buy_status']>0)
			{
				$is_lottery = 1;
				break;
			}
		}
		
		$GLOBALS['tmpl']->assign("is_lottery",$is_lottery);
		$GLOBALS['tmpl']->assign("is_coupon",$is_coupon);
		$GLOBALS['tmpl']->assign("send_coupon_sms",$send_coupon_sms);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['PAY_SUCCESS']);
		$GLOBALS['tmpl']->display("page/payment_done.html");
	}
	
	public function incharge_done()
	{
		$order_id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
		//$order_deals = $GLOBALS['db']->getAll("select d.* from ".DB_PREFIX."deal as d where id in (select distinct deal_id from ".DB_PREFIX."deal_order_item where order_id = ".$order_id.")");
		$GLOBALS['tmpl']->assign("order_info",$order_info);
		//$GLOBALS['tmpl']->assign("order_deals",$order_deals);
	
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['PAY_SUCCESS']);
		$GLOBALS['tmpl']->display("page/payment_done.html");
	}
	
	public function response()
	{
		//支付跳转返回页
		if($GLOBALS['pay_req']['class_name'])
			$_REQUEST['class_name'] = $GLOBALS['pay_req']['class_name'];
			
		$class_name = addslashes(trim($_REQUEST['class_name']));
		$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where class_name = '".$class_name."'");
		if($payment_info)
		{
			require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
			$payment_class = $payment_info['class_name']."_payment";
			$payment_object = new $payment_class();
			adddeepslashes($_REQUEST);
			$payment_code = $payment_object->response($_REQUEST);
		}
		else
		{
			showErr($GLOBALS['lang']['PAYMENT_NOT_EXIST']);//'支付接口不存在，或被站点管理员删除',
		}
	}
	
	public function notify()
	{
		//支付跳转返回页
		if($GLOBALS['pay_req']['class_name'])
			$_REQUEST['class_name'] = $GLOBALS['pay_req']['class_name'];
		
		$class_name = addslashes(trim($_REQUEST['class_name']));
		$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where class_name = '".$class_name."'");
		if($payment_info)
		{
			require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
			$payment_class = $payment_info['class_name']."_payment";
			$payment_object = new $payment_class();
			adddeepslashes($_REQUEST);
			$payment_code = $payment_object->notify($_REQUEST);
		}
		else
		{
			showErr($GLOBALS['lang']['PAYMENT_NOT_EXIST']);
		}
	}
}
?>