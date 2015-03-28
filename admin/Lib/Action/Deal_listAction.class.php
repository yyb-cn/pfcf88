<?php

class Deal_listAction extends CommonAction{
	public function index()
	{
		$condition=' d.deal_status in(1,2,4,5) ';
		$group_list = M("UserGroup")->findAll();
		$this->assign("group_list",$group_list);
		if($_REQUEST['start_time']!=''){
			$_REQUEST['start_time']=strtotime($_REQUEST['start_time']);
			$condition .= " and  dl.create_time  >"."'".$_REQUEST['start_time']."'";
		};
		if($_REQUEST['end_time']!=''){
			$_REQUEST['end_time']=strtotime($_REQUEST['end_time']);
			$condition .= " and  dl.create_time  <"."'".$_REQUEST['end_time']."'";
		};
		if(trim($_REQUEST['user_name'])!='')
		{
			
			$condition .= " and  dl.user_name like"."'%".trim($_REQUEST['user_name'])."%'";
		}
		
		if(trim($_REQUEST['name'])!='')
		{
			
			$condition .= "and   d.name like"."'%".trim($_REQUEST['name'])."%'";
		}
		if(trim($_REQUEST['deal_load_id']!=''))
		{
			$condition .= "and   dl.id ="."'".trim($_REQUEST['deal_load_id'])."'";
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
		elseif(trim($_REQUEST['_order'])=='real_name')
		{
			$order=	"order  by u.real_name ".$sort;
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
		
		$module=m('deal');		
		import('ORG.Util.Page');// 导入分页类
		$count  = $module->query( "select  count(*) as count from ".DB_PREFIX."deal d left join ".DB_PREFIX."deal_load as dl on d.id = dl.deal_id LEFT JOIN ".DB_PREFIX."user u ON u.id=dl.user_id  left join ".DB_PREFIX."user_group as g on u.group_id = g.id  where ".$condition);
		$count=$count[0]['count'];
		// 查询满足要求的总记录数
		$per_page=$_REQUEST['per_page']?$_REQUEST['per_page']:30;
		
		
		$Page   = new Page($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
		$show   = $Page->show();// 分页显示输出
		$this->assign('page',$show);// 赋值分页输出
		
    	$sql = "select u.real_name,g.name as group_name, d.name,d.rate,d.repay_time,d.repay_time_type,d.id as deal_id,dl.user_name,dl.user_id,dl.money as u_load_money,dl.id as deal_load_id,dl.create_time as deal_time , dl.deal_load_check_yn,dl.virtual_money from ".DB_PREFIX."deal d left join ".DB_PREFIX."deal_load as dl on d.id = dl.deal_id LEFT JOIN ".DB_PREFIX."user u ON u.id=dl.user_id  left join ".DB_PREFIX."user_group as g on u.group_id = g.id  where ".$condition .' '. $order . ' limit '.$Page->firstRow.','.$Page->listRows ;
		/*
		d   是  deal
		dl  是  deal_load
		u   是  user
		g   是  user_group
		*/
		$sql_no_limit = "select d.name,d.rate,d.repay_time,d.repay_time_type, dl.money as u_load_money,dl.virtual_money  from ".DB_PREFIX."deal d left join ".DB_PREFIX."deal_load as dl on d.id = dl.deal_id LEFT JOIN ".DB_PREFIX."user u ON u.id=dl.user_id  left join ".DB_PREFIX."user_group as g on u.group_id = g.id  where ".$condition ;
		
		$list_no_limit = $GLOBALS['db']->getAll($sql_no_limit);
		foreach($list_no_limit as $k=>$v)
		{
			$total_no_limit+=$v['u_load_money'];
			if($v['repay_time_type']==1){ //1表示月0表示日
			$list_no_limit[$k]['get_money']=number_format((($v['u_load_money']+$v['virtual_money'])*$v['rate']/12)*$v['repay_time']*0.01,2);
			//计算利率
			}
			else{
			$list_no_limit[$k]['get_money']=number_format((($v['u_load_money']+$v['virtual_money'])*$v['rate']/365)*$v['repay_time']*0.01,2);
			}
			$total_rate_money_nolimit+=$list_no_limit[$k]['get_money'];//当页累计效益
		}
		$this->assign('total_rate_money_nolimit',$total_rate_money_nolimit);
		$list = $GLOBALS['db']->getAll($sql);
		//var_dump($list);exit;
		//deal_load_check_yn
		foreach($list as $k=>$v)
		{
			$total_limit+=$v['u_load_money'];//当页累计成交金额
			if($v['repay_time_type']==1){ //1表示月0表示日
			$list[$k]['get_money']=number_format((($v['u_load_money']+$v['virtual_money'])*$v['rate']/12)*$v['repay_time']*0.01,2);
			//计算利率
			}
			else{
			$list[$k]['get_money']=number_format((($v['u_load_money']+$v['virtual_money'])*$v['rate']/365)*$v['repay_time']*0.01,2);
			}
			$total_rate_money+=$list[$k]['get_money'];//当页累计效益
		}
			$this->assign('total_rate_money',$total_rate_money);
		$total_limit=number_format($total_limit);
		$this->assign('total_limit',$total_limit);
		$total_no_limit=number_format($total_no_limit);
		$this->assign('total_no_limit',$total_no_limit);
		
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