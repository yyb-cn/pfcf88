<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class IndexAction extends AuthAction{
	//首页
    public function index(){
		$this->display();
    }
    

    //框架头
	public function top()
	{
		$navs = M("RoleNav")->where("is_effect=1 and is_delete=0")->order("sort asc")->findAll();
		$this->assign("navs",$navs);
		//print_r($navs);exit;
		$this->display();
	}
	//框架左侧
	public function left()
	{
		$adm_session = es_session::get(md5(conf("AUTH_KEY")));
		$adm_id = intval($adm_session['adm_id']);
		
		$nav_id = intval($_REQUEST['id']);
		$nav_group = M("RoleGroup")->where("nav_id=".$nav_id." and is_effect = 1 and is_delete = 0")->order("sort asc")->findAll();		
		
		//print_r($nav_group);exit; //一级分类
		foreach($nav_group as $k=>$v)
		{
			$sql = "select role_node.`action` as a,role_module.`module` as m,role_node.id as nid,role_node.name as name from ".conf("DB_PREFIX")."role_node as role_node left join ".
				   conf("DB_PREFIX")."role_module as role_module on role_module.id = role_node.module_id ".
				   "where role_node.is_effect = 1 and role_node.is_delete = 0 and role_module.is_effect = 1 and role_module.is_delete = 0 and role_node.group_id = ".$v['id']." order by role_node.id asc";
			
			$nav_group[$k]['nodes']= M()->query($sql);//二级分类
		}
		$this->assign("menus",$nav_group);
		
		//print_r($nav_group);exit;
		$this->display();
	}
	//默认框架主区域
	public function main()
	{
		//会员数
		$total_user = M("User")->count();
		$total_verify_user = M("User")->where("is_effect=1")->count();
		$this->assign("total_user",$total_user);
		$this->assign("total_verify_user",$total_verify_user);
		
		//满标的借款
		$suc_deal_count = M("Deal")->where("publish_wait = 0 AND is_delete = 0 AND deal_status = 2")->count();
		//待审核的借款
		$wait_deal_count = M("Deal")->where("publish_wait = 1 AND is_delete = 0 ")->count();
		//等待材料的借款
		$info_deal_count = M("Deal")->where("publish_wait = 0 AND is_delete = 0 AND deal_status=0")->count();
		//提现申请
		$carry_count = D("UserCarry")->where("status = 0")->count();
		
		//三日要还款的借款
		//获取到期还本息的贷款
		$TloanThreeIds = M("Deal")->where("publish_wait = 0 AND is_delete = 0 AND deal_status = 4 AND loantype =2 ")->field("id,repay_time,repay_start_time")->findAll();
		$loanThreeIds = array();
		$loanExpIds = array();
		foreach($TloanThreeIds as $kk=>$vv){
			$y=to_date($vv['repay_start_time'],"Y");
			$m=to_date($vv['repay_start_time'],"m");
			$d=to_date($vv['repay_start_time'],"d");
			$y = $y + intval(($m+$vv['repay_time'])/12);
			$m = ($m+$vv['repay_time'])%12;
			
			$vv["end_repay_time"] = to_timespan($y."-".$m."-".$d." 23:59:59","Y-m-d");
			$left_day = ($vv["end_repay_time"] - get_gmtime())/24/3600 ;
			
			if($left_day <=3 && $left_day >=0){
				$loanThreeIds[] = $vv["id"];
			}
			
			if($left_day < 0)
			{
				$loanExpIds[] = $vv['id'];
			}
		}
		
		if($loanThreeIds){
			 $ext_T = "OR id in (".implode(",",$loanThreeIds).")";
		}
		$threeday_repay_count = M("Deal")->where("publish_wait = 0 AND is_delete = 0 AND deal_status = 4 AND (((next_repay_time - ".get_gmtime().")/24/3600 between 0 AND 3 AND loantype in(0,1)) $ext_T) ")->count();
		
		//逾期未还款的
		if($loanExpIds){
			 $ext_E = "OR id in (".implode(",",$loanExpIds).")";
		}
		
		$yq_repay_count = M("Deal")->where("publish_wait = 0 AND is_delete = 0  AND deal_status = 4 AND (((".get_gmtime()." - next_repay_time)/24/3600 > 0 AND loantype in(0,1)) $ext_E) ")->count();
		
		//未处理举报
		$reportguy_count = M("Reportguy")->where("status = 0")->count();
		
		$this->assign("suc_deal_count",$suc_deal_count);
		$this->assign("wait_deal_count",$wait_deal_count);	
		$this->assign("info_deal_count",$info_deal_count);
		$this->assign("carry_count",$carry_count);
		$this->assign("threeday_repay_count",$threeday_repay_count);
		$this->assign("yq_repay_count",$yq_repay_count);
		$this->assign("reportguy_count",$reportguy_count);
		
		$topic_count = M("Topic")->where("is_effect = 1 and fav_id = 0")->count();		
		$msg_count = M("Message")->where("is_buy = 0")->count();
		$buy_msg_count = M("Message")->count();
		
		
		
		$this->assign("topic_count",$topic_count);
		$this->assign("msg_count",$msg_count);
		$this->assign("buy_msg_count",$buy_msg_count);
		
		//订单数
		$order_count = M("DealOrder")->where("type = 0")->count();
		$this->assign("order_count",$order_count);
		
		$order_buy_count = M("DealOrder")->where("pay_status=2 and type = 0")->count();
		$this->assign("order_buy_count",$order_buy_count);
		
		
		//充值单数
		$incharge_order_buy_count = M("DealOrder")->where("pay_status=2 and type = 1")->count();
		$this->assign("incharge_order_buy_count",$incharge_order_buy_count);
		
		
		$income_amount = M("DealOrder")->sum("pay_amount");
		$refund_amount = M("DealOrder")->sum("refund_money");
		$this->assign("income_amount",$income_amount);
		$this->assign("refund_amount",$refund_amount);
		
		
		$reminder = M("RemindCount")->find();
		$reminder['topic_count'] = intval(M("Topic")->where("is_effect = 1 and relay_id = 0 and fav_id = 0 and create_time >".$reminder['topic_count_time'])->count());
		$reminder['msg_count'] = intval(M("Message")->where("create_time >".$reminder['msg_count_time'])->count());
		$reminder['buy_msg_count'] = intval(M("Message")->where("create_time >".$reminder['buy_msg_count_time'])->count());
		$reminder['order_count'] = intval(M("DealOrder")->where("is_delete = 0 and type = 0 and pay_status = 2 and create_time >".$reminder['order_count_time'])->count());
		$reminder['refund_count'] = intval(M("DealOrder")->where("is_delete = 0 and refund_status = 1 and create_time >".$reminder['refund_count_time'])->count());
		$reminder['retake_count'] = intval(M("DealOrder")->where("is_delete = 0 and retake_status = 1 and create_time >".$reminder['retake_count_time'])->count());
		$reminder['incharge_count'] = intval(M("DealOrder")->where("is_delete = 0 and type = 1 and pay_status = 2 and create_time >".$reminder['incharge_count_time'])->count());
		
		M("RemindCount")->save($reminder);
		$this->assign("reminder",$reminder);
		
		//待补还项目
		$after_repay_count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal as d where deal_status in(4,5) AND repay_money > round((SELECT sum(repay_money) FROM ".DB_PREFIX."deal_load_repay WHERE deal_id = d.id),2)");
		$this->assign("after_repay_count",$after_repay_count);
		
		$this->display();
	}	
	//底部
	public function footer()
	{
		$this->display();
	}
	
	//修改管理员密码
	public function change_password()
	{
		$adm_session = es_session::get(md5(conf("AUTH_KEY")));
		$this->assign("adm_data",$adm_session);
		$this->display();
	}
	public function do_change_password()
	{
		$adm_id = intval($_REQUEST['adm_id']);
		if(!check_empty($_REQUEST['adm_password']))
		{
			$this->error(L("ADM_PASSWORD_EMPTY_TIP"));
		}
		if(!check_empty($_REQUEST['adm_new_password']))
		{
			$this->error(L("ADM_NEW_PASSWORD_EMPTY_TIP"));
		}
		if($_REQUEST['adm_confirm_password']!=$_REQUEST['adm_new_password'])
		{
			$this->error(L("ADM_NEW_PASSWORD_NOT_MATCH_TIP"));
		}		
		if(M("Admin")->where("id=".$adm_id)->getField("adm_password")!=md5($_REQUEST['adm_password']))
		{
			$this->error(L("ADM_PASSWORD_ERROR"));
		}
		M("Admin")->where("id=".$adm_id)->setField("adm_password",md5($_REQUEST['adm_new_password']));
		save_log(M("Admin")->where("id=".$adm_id)->getField("adm_name").L("CHANGE_SUCCESS"),1);
		$this->success(L("CHANGE_SUCCESS"));
		
		
	}
	
	public function reset_sending()
	{
		$field = trim($_REQUEST['field']);
		if($field=='DEAL_MSG_LOCK'||$field=='PROMOTE_MSG_LOCK'||$field=='APNS_MSG_LOCK')
		{
			M("Conf")->where("name='".$field."'")->setField("value",'0');
			$this->success(L("RESET_SUCCESS"),1);
		}
		else
		{
			$this->error(L("INVALID_OPERATION"),1);
		}
	}
}
?>