<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/uc.php';

class uc_accountModule extends SiteBaseModule
{
	public function index()
	{

		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_ACCOUNT']);
		
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
			
			
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_account_index.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	
	public function work(){
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_WORK_AUTH']);
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
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_work_index.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	
	public function mobile(){
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_MOBILE']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_mobile_index.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	
	public function save()
	{
		require_once APP_ROOT_PATH.'system/libs/user.php';
		foreach($_REQUEST as $k=>$v)
		{
			$_REQUEST[$k] = htmlspecialchars(addslashes(trim($v)));
		}
		if(intval($_REQUEST['id']) == 0 )
			$_REQUEST['id'] = intval($GLOBALS['user_info']['id']);
		$res = save_user($_REQUEST,'UPDATE');
		if($res['status'] == 1)
		{
			$s_user_info = es_session::get("user_info");
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = '".intval($s_user_info['id'])."'");
			es_session::set("user_info",$user_info);
			if(intval($_REQUEST['is_ajax'])==1)
				showSuccess($GLOBALS['lang']['SUCCESS_TITLE'],1);
			else{
				app_redirect(url("index","uc_account#work"));
			}		
		}
		else
		{
			$error = $res['data'];		
			if(!$error['field_show_name'])
			{
					$error['field_show_name'] = $GLOBALS['lang']['USER_TITLE_'.strtoupper($error['field_name'])];
			}
			if($error['error']==EMPTY_ERROR)
			{
				$error_msg = sprintf($GLOBALS['lang']['EMPTY_ERROR_TIP'],$error['field_show_name']);
			}
			if($error['error']==FORMAT_ERROR)
			{
				$error_msg = sprintf($GLOBALS['lang']['FORMAT_ERROR_TIP'],$error['field_show_name']);
			}
			if($error['error']==EXIST_ERROR)
			{
				$error_msg = sprintf($GLOBALS['lang']['EXIST_ERROR_TIP'],$error['field_show_name']);
			}
			showErr($error_msg,intval($_REQUEST['is_ajax']));
		}
	}
	
	public function savework(){
		foreach($_REQUEST as $k=>$v)
		{
			$_REQUEST[$k] = htmlspecialchars(addslashes(trim($v)));
		}
		$data['office'] = trim($_REQUEST['office']);
		$data['jobtype'] = trim($_REQUEST['jobtype']);
		$data['province_id'] = intval($_REQUEST['province_id']);
		$data['city_id'] = intval($_REQUEST['city_id']);
		$data['officetype'] = trim($_REQUEST['officetype']);
		$data['officedomain'] = trim($_REQUEST['officedomain']);
		$data['officecale'] = trim($_REQUEST['officecale']);
		$data['position'] = trim($_REQUEST['position']);
		$data['salary'] = trim($_REQUEST['salary']);
		$data['workyears'] = trim($_REQUEST['workyears']);
		$data['workphone'] = trim($_REQUEST['workphone']);
		$data['workemail'] = trim($_REQUEST['workemail']);
		$data['officeaddress'] = trim($_REQUEST['officeaddress']);
		
		if(isset($_REQUEST['urgentcontact']))
			$data['urgentcontact'] = trim($_REQUEST['urgentcontact']);
		if(isset($_REQUEST['urgentrelation']))
			$data['urgentrelation'] = trim($_REQUEST['urgentrelation']);
		if(isset($_REQUEST['urgentmobile']))
			$data['urgentmobile'] = trim($_REQUEST['urgentmobile']);
		if(isset($_REQUEST['urgentcontact2']))
			$data['urgentcontact2'] = trim($_REQUEST['urgentcontact2']);
		if(isset($_REQUEST['urgentrelation2']))
			$data['urgentrelation2'] = trim($_REQUEST['urgentrelation2']);
		if(isset($_REQUEST['urgentmobile2']))
			$data['urgentmobile2'] = trim($_REQUEST['urgentmobile2']);
			
		if(intval($_REQUEST['id']) > 0)
			$data['user_id'] = intval($_REQUEST['id']);
		else
			$data['user_id'] = intval($GLOBALS['user_info']['id']);
			
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user_work WHERE user_id=".$data['user_id'])==0){
			//添加
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_work",$data,"INSERT");
		}
		else{
			//编辑
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_work",$data,"UPDATE","user_id=".$data['user_id']);
		}
		
		showSuccess($GLOBALS['lang']['SAVE_USER_SUCCESS'],intval($_REQUEST['is_ajax']));
	}
}
?>