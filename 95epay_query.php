<?php
header("Content-Type: text/html; charset=UTF-8");
require './system/common.php';
require './app/Lib/app_init.php';

if ($_REQUEST['order']=="1" && $_REQUEST['succeed']=="success") {

		$payment_notice_id =$_SESSION['Sqepay_nixtemp_id'];
    	$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
		$order = $GLOBALS['db']->getRow("select order_sn,bank_id from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);//查询订单
		
		$_TransID = $order['order_sn'];//
		
		$payment_info = $GLOBALS['db']->getRow("select config from ".DB_PREFIX."payment where id=".intval($payment_notice['payment_id']));
		$payment_info['config'] = unserialize($payment_info['config']);
		
			require_once APP_ROOT_PATH."system/libs/cart.php";
	
			$rs = payment_paid($payment_notice['id']);
			if($rs)
			{
				$rs = order_paid($payment_notice['order_id']);				
				if($rs)
				{
					//开始更新相应的outer_notice_sn					
					$GLOBALS['db']->query("update ".DB_PREFIX."payment_notice set outer_notice_sn = '".$_TransID."' where id = ".$payment_notice['id']);
					if($order_info['type']==0)
						app_redirect(url("index","payment#done",array("id"=>$payment_notice['order_id']))); //支付成功
					else
						app_redirect(url("index","payment#incharge_done",array("id"=>$payment_notice['order_id']))); //支付成功
				}
				else 
				{
					if($order_info['pay_status'] == 2)
					{				
						if($order_info['type']==0)
							app_redirect(url("index","payment#done",array("id"=>$payment_notice['order_id']))); //支付成功
						else
							app_redirect(url("index","payment#incharge_done",array("id"=>$payment_notice['order_id']))); //支付成功
					}
					else
						app_redirect(url("index","payment#pay",array("id"=>$payment_notice['id'])));
				}
			}
			else
			{
				app_redirect(url("index","payment#pay",array("id"=>$payment_notice['id'])));
			}
} else if ($_REQUEST['id']<>"") {
		$payment_notice_id = intval($_REQUEST['id']);//转为整数
		$_SESSION['Sqepay_nixtemp_id']=$payment_notice_id;
    	$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
		$order = $GLOBALS['db']->getRow("select order_sn,bank_id from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);//查询订单
		
		$_TransID = $order['order_sn'];//
		
		$payment_info = $GLOBALS['db']->getRow("select config from ".DB_PREFIX."payment where id=".intval($payment_notice['payment_id']));
		$payment_info['config'] = unserialize($payment_info['config']);
		
       
		//$_MerchantID = $payment_info['config']['baofoo_account'];
        //$_Md5Key = $payment_info['config']['baofoo_key'];
		
		$MerNo 				= "181138";
		$MD5key      		= "aWOv]Fct";
		$BillNo 			= $_TransID;
		//$MerUrl 			= "http://www.pfcf88.com/95epay_callback.php?act=userquery";
		$MerUrl 			= "http://www.pfcf88.com/95epay_query.php";
		$MD5Info 			= getSignature($MerNo, $BillNo, $MerUrl, $MD5key);
		//print $MD5Info;
		
		$post_data = array();  
		$post_data['MerNo']  = $MerNo;  
		$post_data['BillNo'] = $BillNo;  
		$post_data['MerUrl'] = $MerUrl; 
		$post_data['MD5Info'] = $MD5Info; 
		
		
		$data = curl_post("http://www.95epay.cn/ReconciliationPort", $post_data);  
	  
		var_dump($data);
} else {
	app_redirect(url("index","payment#pay",array("id"=>$_SESSION['Sqepay_nixtemp_id'])));

}
function getSignature($MerNo, $BillNo, $MerUrl, $MD5key){
	$_SESSION['MerNo'] = $MerNo;
	$_SESSION['MD5key'] = $MD5key;
	$sign_params  = array(
        'BillNo'       => $BillNo, 
        'MerNo'       => $MerNo,
        'MerUrl'       => $MerUrl
    );
  $sign_str = "";
  ksort($sign_params);
  foreach ($sign_params as $key => $val) {
                               
       $sign_str .= sprintf("%s=%s&", $key, $val);                
                
   }
   //print $sign_str;print '<br/><br/><br/>';
   return strtoupper(md5($sign_str.strtoupper(md5($MD5key))));   

	
}


function curl_post($url, $post) {  
    $options = array(  
        CURLOPT_RETURNTRANSFER => true,  
        CURLOPT_HEADER         => false,  
        CURLOPT_POST           => true,  
        CURLOPT_POSTFIELDS     => $post,  
    );  


    $ch = curl_init($url);  
    curl_setopt_array($ch, $options);  
    $result = curl_exec($ch);  
    curl_close($ch);  
    return $result;  
}  
  
?>