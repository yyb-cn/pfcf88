<?php

class Deal_listAction extends CommonAction{
	public function index()
	{
		$condition=' d.deal_status in(1,2,4,5) ';
		
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
		
    	$sql = "select d.name,d.repay_time,d.repay_time_type,dl.user_name,dl.money as u_load_money,dl.id as deal_load_id,dl.create_time as deal_time from ".DB_PREFIX."deal d left join ".DB_PREFIX."deal_load as dl on d.id = dl.deal_id LEFT JOIN ".DB_PREFIX."user u ON u.id=d.user_id where ".$condition .' '. $order;
		
		$list = $GLOBALS['db']->getAll($sql);
		
		$this->assign('list',$list);
		$this->display();
		
	}
	
	
	
}
?>