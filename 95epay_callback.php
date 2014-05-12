<?php

if(!defined('ROOT_PATH'))
define('ROOT_PATH', str_replace('95epay_callback.php', '', str_replace('\\', '/', __FILE__)));

global $pay_req;
$pay_req['ctl'] = "payment";
$pay_req['act'] = $_REQUEST['act'];
$pay_req['class_name'] = "Sqepay";

if ($_REQUEST['act'] == "query") {
	//print_r($_REQUEST);
	switch($_REQUEST['order'] )
	{
		case "0";
		$result1='失败';
		break;
		case "1";
		$result1='成功';
		break;
		case "2";
		$result1='待处理';
		break;
		case "3";
		$result1='取消';
		break;
		case "4";
		$result1='结果未返回';
		break;
		default;
		$result1='无状态';
		break;
	}

	echo "</br>"."订单状态：".$result1."</br>";

	switch($_REQUEST['succeed'] )
	{
		case "success";
		$result2='信息验证成功，订单查询过程完整';
		break;
		case "Error_01";
		$result2='订单号为空，取消查询';
		break;
		case "Error_02";
		$result2='商户号为空，取消查询';
		break;
		case "Error_03";
		$result2='返回地址为空，取消查询';
		break;
		case "Error_04";
		$result2='MD5加密字符串为空，取消查询';
		break;	
		case "Error_05";
		$result2='订单不存在，取消查询';
		break;	
		case "Error_06";
		$result2='商户不存在，取消查询';
		break;	
		case Error_07;
		$result2='MD5加密字符串验证错误，取消查询';
		break;	
		case Error_08;
		$result2='单号不唯一，取消查询';
		break;	
	}

	echo "验证状态：".$result2."</br>";

} else {
	include ROOT_PATH."index.php";
}
?>