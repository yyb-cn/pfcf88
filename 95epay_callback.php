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
		$result1='ʧ��';
		break;
		case "1";
		$result1='�ɹ�';
		break;
		case "2";
		$result1='������';
		break;
		case "3";
		$result1='ȡ��';
		break;
		case "4";
		$result1='���δ����';
		break;
		default;
		$result1='��״̬';
		break;
	}

	echo "</br>"."����״̬��".$result1."</br>";

	switch($_REQUEST['succeed'] )
	{
		case "success";
		$result2='��Ϣ��֤�ɹ���������ѯ��������';
		break;
		case "Error_01";
		$result2='������Ϊ�գ�ȡ����ѯ';
		break;
		case "Error_02";
		$result2='�̻���Ϊ�գ�ȡ����ѯ';
		break;
		case "Error_03";
		$result2='���ص�ַΪ�գ�ȡ����ѯ';
		break;
		case "Error_04";
		$result2='MD5�����ַ���Ϊ�գ�ȡ����ѯ';
		break;	
		case "Error_05";
		$result2='���������ڣ�ȡ����ѯ';
		break;	
		case "Error_06";
		$result2='�̻������ڣ�ȡ����ѯ';
		break;	
		case Error_07;
		$result2='MD5�����ַ�����֤����ȡ����ѯ';
		break;	
		case Error_08;
		$result2='���Ų�Ψһ��ȡ����ѯ';
		break;	
	}

	echo "��֤״̬��".$result2."</br>";

} else {
	include ROOT_PATH."index.php";
}
?>