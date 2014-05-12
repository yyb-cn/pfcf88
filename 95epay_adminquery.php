<?php
header("Content-Type: text/html; charset=UTF-8");
require './system/common.php';
require './app/Lib/app_init.php';
$payment_notice_id = intval($_REQUEST['id']);//转为整数

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
	$MerUrl 			= "http://www.pfcf88.com/95epay_callback.php?act=query";
	$MD5Info 			= getSignature($MerNo, $BillNo, $MerUrl, $MD5key);
	print $MD5Info;
	
$post_data = array();  
$post_data['MerNo']  = $MerNo;  
$post_data['BillNo'] = $BillNo;  
$post_data['MerUrl'] = $MerUrl; 
$post_data['MD5Info'] = $MD5Info; 

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
   print $sign_str;print '<br/><br/><br/>';
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
  
$data = curl_post("http://www.95epay.cn/ReconciliationPort", $post_data);  
  
var_dump($data);

?>