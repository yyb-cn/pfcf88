<?php

class Deal_listAction extends CommonAction{
	public function index()
	{
		$condition=' d.deal_status in(1,2,4,5) ';
		$group_list = M("UserGroup")->findAll();
		$this->assign("group_list",$group_list);
		if(trim($_REQUEST['user_name'])!='')
		{
			
			$condition .= " and  dl.user_name ="."'".$_REQUEST['user_name']."'";
		}
		
		if(trim($_REQUEST['name'])!='')
		{
			
			$condition .= "and   d.name ="."'".$_REQUEST['name']."'";
		}
		if(trim($_REQUEST['deal_load_id']!=''))
		{
			$condition .= "and   dl.id ="."'".$_REQUEST['deal_load_id']."'";
		}
		if(trim($_REQUEST['group_id']!=0))
		{
			$condition .= "and   u.group_id ="."'".$_REQUEST['group_id']."'";
		}
		if(trim($_REQUEST['_sort'])==0){
			$sort='desc';
		
		}
		elseif(trim($_REQUEST['_sort'])==1){
			$sort='asc';
		
		}
		if(trim($_REQUEST['_order'])=='')
		{
		
			$order=	"order  by deal_time desc";
			
		}
		if(trim($_REQUEST['_order'])=='name')
		{
			$order=	"order  by  d.name  ".$sort;
		}
		elseif(trim($_REQUEST['_order'])=='deal_load_id')
		{
			$order=	"order  by deal_load_id ".$sort;
		}
		elseif(trim($_REQUEST['_order'])=='user_name')
		{
			$order=	"order  by dl.user_name ".$sort;
		}
		elseif(trim($_REQUEST['_order'])=='deal_time')
		{
			$order=	"order  by deal_time ".$sort;
		}
		elseif(trim($_REQUEST['_order'])=='u_load_money')
		{
			$order=	"order  by u_load_money ".$sort;
		}
		elseif(trim($_REQUEST['_order'])=='repay_time')
		{
			$order=	"order  by d.repay_time ".$sort;
		}
		elseif(trim($_REQUEST['_order'])=='deal_load_check_yn')
		{
			$order=	"order  by dl.deal_load_check_yn ".$sort;
		}
		elseif(trim($_REQUEST['_order'])=='group_name')
		{
			$order=	"order  by group_name ".$sort;
		}
    	$sql = "select g.name as group_name, d.name,d.repay_time,d.repay_time_type,d.id as deal_id,dl.user_name,dl.user_id,dl.money as u_load_money,dl.id as deal_load_id,dl.create_time as deal_time , dl.deal_load_check_yn from ".DB_PREFIX."deal d left join ".DB_PREFIX."deal_load as dl on d.id = dl.deal_id LEFT JOIN ".DB_PREFIX."user u ON u.id=dl.user_id  left join ".DB_PREFIX."user_group as g on u.group_id = g.id  where ".$condition .' '. $order;
		/*
		d   是  deal
		dl  是  deal_load
		u   是  user
		g   是  user_group
		*/
		$list = $GLOBALS['db']->getAll($sql);
		//deal_load_check_yn
		//print_r($list);exit;
		$this->assign('list',$list);
		$this->display();
		
	}
	public function check(){
		
		$id=trim($_REQUEST['id']);
		
		$GLOBALS['db']->query("update ".DB_PREFIX."deal_load set deal_load_check_yn = 1 where id = ".$id);
		
		redirect('?m=Deal_list&a=index');
	}
	public function send()
	{
		echo  'heihei，还没做好';
	}
	
}
?>