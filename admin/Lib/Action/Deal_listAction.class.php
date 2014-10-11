<?php

class Deal_listAction extends CommonAction{
	public function index()
	{
		$condtion = "   d.deal_status in(1,2,4,5)  ";
			
    	$sql = "select d.name,d.repay_time,d.repay_time_type,dl.user_name,dl.money as u_load_money,dl.id as deal_load_id,dl.create_time as deal_time from ".DB_PREFIX."deal d left join ".DB_PREFIX."deal_load as dl on d.id = dl.deal_id LEFT JOIN ".DB_PREFIX."user u ON u.id=d.user_id where  d.deal_status in(1,2,4,5) order by dl.create_time desc";
		
		$list = $GLOBALS['db']->getAll($sql);
		//print_r($list);exit;
		$this->assign('list',$list);
		$this->display();
		
	}
	
	
	
}
?>