<?php
// +----------------------------------------------------------------------
// | easethink 易想商城系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class DealAction extends CommonAction{
	public function index()
	{
		
		//分类
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		
		//开始加载搜索条件
		if(intval($_REQUEST['id'])>0)
		$map['id'] = intval($_REQUEST['id']);
		$map['is_delete'] = 0;
		if(trim($_REQUEST['name'])!='')
		{
			$map['name'] = array('like','%'.trim($_REQUEST['name']).'%');			
		}

		if(intval($_REQUEST['cate_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("deal_cate");
			$cate_ids = $child->getChildIds(intval($_REQUEST['cate_id']));
			$cate_ids[] = intval($_REQUEST['cate_id']);
			$map['cate_id'] = array("in",$cate_ids);
		}
		
		
		if(trim($_REQUEST['user_name'])!='')
		{
			$sql  ="select group_concat(id) from ".DB_PREFIX."user where user_name like '%".trim($_REQUEST['user_name'])."%'";
			
			$ids = $GLOBALS['db']->getOne($sql);
			$map['user_id'] = array("in",$ids);
		}
		
		if(isset($_REQUEST['deal_status']) && trim($_REQUEST['deal_status']) != '' && trim($_REQUEST['deal_status']) != 'all'){
			$map['deal_status'] = array("eq",intval($_REQUEST['deal_status']));
		}
		
		$map['publish_wait'] = 0;
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
	
	public function three()
	{
		$this->assign("main_title",L("DEAL_THREE"));
		//分类
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		
		//开始加载搜索条件
		if(intval($_REQUEST['id'])>0)
		$map['id'] = intval($_REQUEST['id']);
		$map['is_delete'] = 0;
		if(trim($_REQUEST['name'])!='')
		{
			$map['name'] = array('like','%'.trim($_REQUEST['name']).'%');			
		}

		if(intval($_REQUEST['cate_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("deal_cate");
			$cate_ids = $child->getChildIds(intval($_REQUEST['cate_id']));
			$cate_ids[] = intval($_REQUEST['cate_id']);
			$map['cate_id'] = array("in",$cate_ids);
		}
		
		
		if(trim($_REQUEST['user_name'])!='')
		{
			$sql  ="select group_concat(id) from ".DB_PREFIX."user where user_name like '%".trim($_REQUEST['user_name'])."%'";
			
			$ids = $GLOBALS['db']->getOne($sql);
			$map['user_id'] = array("in",$ids);
		}
		
		$map['publish_wait'] = 0;
		
		//获取到期还本息的贷款
		$TloanThreeIds = M("Deal")->where("publish_wait = 0 AND is_delete = 0 AND deal_status = 4 AND loantype =2 ")->field("id,repay_time,repay_start_time")->findAll();
		$loanThreeIds = array();
		foreach($TloanThreeIds as $kk=>$vv){
			$y=to_date($vv['repay_start_time'],"Y");
			$m=to_date($vv['repay_start_time'],"m");
			$d=to_date($vv['repay_start_time'],"d");
			$y = $y + intval(($m+$vv['repay_time'])/12);
			$m = ($m+$vv['repay_time'])%12;
			
			$vv["end_repay_time"] = to_timespan($y."-".$m."-".$d,"Y-m-d");
			$left_day = ($vv["end_repay_time"] - get_gmtime())/24/3600 ;
			
			if($left_day <=3 && $left_day >=0){
				$loanThreeIds[] = $vv["id"];
			}
		}
		
		if($loanThreeIds){
			 $ext_T = "OR id in (".implode(",",$loanThreeIds).")";
		}
		
		$temp_ids = M("Deal")->where("publish_wait = 0 AND deal_status = 4 AND (((next_repay_time - ".get_gmtime().")/24/3600 between 0 AND 3 AND loantype in(0,1)) $ext_T) ")->Field('id')->findAll();
		$deal_ids[] = 0;
		foreach($temp_ids as $k=>$v){
			$deal_ids[] = $v['id'];
		}
		$map['id'] = array("in",implode(",",$deal_ids));
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
	
	public function three_msg(){
		$map['is_delete'] = 0;
		if(trim($_REQUEST['name'])!='')
		{
			$map['name'] = array('like','%'.trim($_REQUEST['name']).'%');			
		}

		if(intval($_REQUEST['cate_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("deal_cate");
			$cate_ids = $child->getChildIds(intval($_REQUEST['cate_id']));
			$cate_ids[] = intval($_REQUEST['cate_id']);
			$map['cate_id'] = array("in",$cate_ids);
		}
		
		
		if(trim($_REQUEST['user_name'])!='')
		{
			$sql  ="select group_concat(id) from ".DB_PREFIX."user where user_name like '%".trim($_REQUEST['user_name'])."%'";
			
			$ids = $GLOBALS['db']->getOne($sql);
			$map['user_id'] = array("in",$ids);
		}
		
		$map['publish_wait'] = 0;
		
		//获取到期还本息的贷款
		$TloanThreeIds = M("Deal")->where("publish_wait = 0 AND is_delete = 0 AND deal_status = 4 AND loantype =2 ")->field("id,repay_time,repay_start_time")->findAll();
		$loanThreeIds = array();
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
		}
		
		if($loanThreeIds){
			 $ext_T = "OR id in (".implode(",",$loanThreeIds).")";
		}
		
		$temp_ids = M("Deal")->where("publish_wait = 0 AND deal_status = 4 AND (((next_repay_time - ".get_gmtime().")/24/3600 <= 3 AND loantype in(0,1)) $ext_T) AND  (send_three_msg_time < next_repay_time OR send_three_msg_time = 0) ")->Field('id')->findAll();
		$deal_ids[] = 0;
		foreach($temp_ids as $k=>$v){
			$deal_ids[] = $v['id'];
		}
		$map['id'] = array("in",implode(",",$deal_ids));
	
		$list = D ("Deal")->where($map)->findAll();
		//发送信息
		foreach($list as $k=>$v){
			$next_repay_time = 0;
			$true_repay_time = $v['repay_time'];
			if($v['next_repay_time'] > 0)
				$next_repay_time = $v['next_repay_time'];
			else{
				if($v['repay_time_type'] == 0){
					$r_y = to_date($v['repay_start_time'],"Y");
					$r_m = to_date($v['repay_start_time'],"m");
					$r_d = to_date($v['repay_start_time'],"d");
					if($r_m-1 <=0){
						$r_m = 12;
						$r_y = $r_y-1;
					}
					else{
						$r_m = $r_m - 1;
					}
					$true_repay_time = 1;
					$v['repay_start_time'] = to_timespan($r_y."-".$r_m."-".$r_d,"Y-m-d") + $v['repay_time']*24*3600;
				}
				//到期还本息
				if($v['loantype'] == 2)
				{
					$y=to_date($v['repay_start_time'],"Y");
					$m=to_date($v['repay_start_time'],"m");
					$d=to_date($v['repay_start_time'],"d");
					$y = $y + intval(($m+$true_repay_time)/12);
					$m = ($m+$true_repay_time)%12;
					
					$next_repay_time = to_timespan($y."-".$m."-".$d,"Y-m-d");
				}
				else
					$next_repay_time = next_replay_month($v['repay_start_time']);
			}
			
			//计算最近一期该还多少
			if($v["loantype"] == 0)
				$repay_money = pl_it_formula($v['borrow_amount'],$v['rate']/12/100,$true_repay_time);
			elseif($v['loantype'] == 1)
				$repay_money = av_it_formula($v['borrow_amount'],$v['rate']/12/100) ;
			elseif($v['loantype'] == 2)	
				$repay_money = $v['borrow_amount'] * $v['rate']/12/100 * $true_repay_time;
			
			if($v['repay_time_type'] == 1){
				$idx = ((int)to_date(get_gmtime(),"Y") - (int)to_date($v['repay_start_time'],"Y"))*12 + ((int)to_date(get_gmtime(),"m") - (int)to_date($v['repay_start_time'],"m"));
				if($true_repay_time==$idx){
					if($v['loantype'] == 0)
						$repay_money = $repay_money*12 - ($idx-1)*round($repay_money,2);
					elseif($v['loantype'] == 1)
						$repay_money = $repay_money + $v['borrow_amount'];
					elseif($v['loantype'] == 2)
						$repay_money = $repay_money + $v['borrow_amount'];
				}
			}
			
			//站内信
			$content = "您在".app_conf("SHOP_TITLE")."的借款 “<a href=\"".url("index","deal",array("id"=>$v['id']))."\">".$v['name']."</a>”，" .
					"最近一期还款将于".to_date($next_repay_time,"d")."日到期，需还金额".round($repay_money,2)."元。";
			
			$group_arr = array(0,$v['user_id']);
			sort($group_arr);
			$group_arr[] =  4;
			
			$msg_data['content'] = $content;
			$msg_data['to_user_id'] = $v['user_id'];
			$msg_data['create_time'] = get_gmtime();
			$msg_data['type'] = 0;
			$msg_data['group_key'] = implode("_",$group_arr);
			$msg_data['is_notice'] = 12;
			$msg_data['fav_id'] = $v['id'];
			
			$GLOBALS['db']->autoExecute(DB_PREFIX."msg_box",$msg_data);
			$id = $GLOBALS['db']->insert_id();
			$GLOBALS['db']->query("update ".DB_PREFIX."msg_box set group_key = '".$msg_data['group_key']."_".$id."' where id = ".$id);
			
			$user_info  = D("User")->where("id=".$v['user_id'])->find();
			
			//邮件
			if(app_conf("MAIL_ON")==1)
			{
				$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_THREE_EMAIL'");
				$tmpl_content = $tmpl['content'];
				
				$notice['user_name'] = $user_info['user_name'];
				$notice['deal_name'] = $v['name'];
				$notice['deal_url'] = get_domain().url("index","deal",array("id"=>$v['id']));
				$notice['repay_url'] = get_domain().url("index","uc_deal#refund");
				$notice['repay_time_y'] = to_date($next_repay_time,"Y");
				$notice['repay_time_m'] = to_date($next_repay_time,"m");
				$notice['repay_time_d'] = to_date($next_repay_time,"d");
				$notice['site_name'] = app_conf("SHOP_TITLE");
				$notice['repay_money'] = round($repay_money,2);
				$notice['help_url'] = get_domain().url("index","helpcenter");
				$notice['msg_cof_setting_url'] = get_domain().url("index","uc_msg#setting");
				
				$GLOBALS['tmpl']->assign("notice",$notice);
				
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $user_info['email'];
				$msg_data['send_type'] = 1;
				$msg_data['title'] = "三日内还款通知";
				$msg_data['content'] = addslashes($msg);
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = get_gmtime();
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
			}
			
			//短信
			if(app_conf("SMS_ON")==1)
			{
				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_THREE_SMS'");				
				$tmpl_content = $tmpl['content'];
								
				$notice['user_name'] = $user_info["user_name"];
				$notice['deal_name'] = $v['name'];
				$notice['repay_time_y'] = to_date($next_repay_time,"Y");
				$notice['repay_time_m'] = to_date($next_repay_time,"m");
				$notice['repay_time_d'] = to_date($next_repay_time,"d");
				$notice['site_name'] = app_conf("SHOP_TITLE");
				$notice['repay_money'] = round($repay_money,2);
				
				$GLOBALS['tmpl']->assign("notice",$notice);
				
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				
				$msg_data['dest'] = $user_info['mobile'];
				$msg_data['send_type'] = 0;
				$msg_data['title'] = "三日内还款通知";
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = get_gmtime();
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入				
			}
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal",array("send_three_msg_time"=>$next_repay_time),"UPDATE","id=".$v['id']); 
		}
		
		//成功提示
		if($deal_ids){
			save_log(implode(",",$deal_ids)."发送三日内还款提示",1);
		}
		$this->success("发送成功");
		
	}
	
	public function yuqi()
	{
		$this->assign("main_title",L("DEAL_YUQI"));
		//分类
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		
		//开始加载搜索条件
		if(intval($_REQUEST['id'])>0)
		$map['id'] = intval($_REQUEST['id']);
		$map['is_delete'] = 0;
		if(trim($_REQUEST['name'])!='')
		{
			$map['name'] = array('like','%'.trim($_REQUEST['name']).'%');			
		}

		if(intval($_REQUEST['cate_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("deal_cate");
			$cate_ids = $child->getChildIds(intval($_REQUEST['cate_id']));
			$cate_ids[] = intval($_REQUEST['cate_id']);
			$map['cate_id'] = array("in",$cate_ids);
		}
		
		
		if(trim($_REQUEST['user_name'])!='')
		{
			$sql  ="select group_concat(id) from ".DB_PREFIX."user where user_name like '%".trim($_REQUEST['user_name'])."%'";
			
			$ids = $GLOBALS['db']->getOne($sql);
			$map['user_id'] = array("in",$ids);
		}
		
		$map['publish_wait'] = 0;
		
		$yuqiday=0;
		if(intval($_REQUEST['yuqi_day']) >0){
			$yuqiday = intval($_REQUEST['yuqi_day']);
		}
		
		//获取到期还本息的贷款
		$TloanThreeIds = M("Deal")->where("publish_wait = 0 AND is_delete = 0 AND deal_status = 4 AND loantype =2 ")->field("id,repay_time,repay_start_time")->findAll();
		$loanExpIds = array();
		foreach($TloanThreeIds as $kk=>$vv){
			$y=to_date($vv['repay_start_time'],"Y");
			$m=to_date($vv['repay_start_time'],"m");
			$d=to_date($vv['repay_start_time'],"d");
			$y = $y + intval(($m+$vv['repay_time'])/12);
			$m = ($m+$vv['repay_time'])%12;
			
			$vv["end_repay_time"] = to_timespan($y."-".$m."-".$d." 23:59:59","Y-m-d");
			$left_day = ($vv["end_repay_time"] - get_gmtime())/24/3600 ;
			
			if(ceil(-$left_day) > $yuqiday)
			{
				$loanExpIds[] = $vv['id'];
			}
		}
		
		if($loanExpIds){
			 $ext_E = "OR id in (".implode(",",$loanExpIds).")";
		}
		
		$temp_ids = M("Deal")->where("publish_wait = 0 AND deal_status = 4 AND (((".get_gmtime()." - next_repay_time)/24/3600 > $yuqiday AND loantype in(0,1)) $ext_E ) ")->Field('id')->findAll();
		
		$deal_ids[] = 0;
		foreach($temp_ids as $k=>$v){
			$deal_ids[] = $v['id'];
		}
		$map['id'] = array("in",implode(",",$deal_ids));
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
	
	
	public function trash()
	{
		$condition['is_delete'] = 1;
		$this->assign("default_map",$condition);
		parent::index();
	}
	public function add()
	{
		$this->assign("new_sort", M("Deal")->where("is_delete=0")->max("sort")+1);
		
		$deal_cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$deal_cate_tree = D("DealCate")->toFormatTree($deal_cate_tree,'name');
		$this->assign("deal_cate_tree",$deal_cate_tree);
		
		$deal_agency = M("DealAgency")->where('is_effect = 1')->order('sort DESC')->findAll();
		$this->assign("deal_agency",$deal_agency);
		
		$deal_type_tree = M("DealLoanType")->findAll();
		$deal_type_tree = D("DealLoanType")->toFormatTree($deal_type_tree,'name');
		$this->assign("deal_type_tree",$deal_type_tree);

		$this->display();
	}
	
	public function insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();

		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		
		if(!check_empty($data['name']))
		{
			$this->error(L("DEAL_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['sub_name']))
		{
			$this->error(L("DEAL_SUB_NAME_EMPTY_TIP"));
		}	
		if($data['cate_id']==0)
		{
			$this->error(L("DEAL_CATE_EMPTY_TIP"));
		}
		if($data['type_id']==0)
		{
			$this->error(L("DEAL_TYPE_EMPTY_TIP"));
		}
		
		// 更新数据

		$log_info = $data['name'];
		$data['create_time'] = get_gmtime();
		$data['update_time'] = get_gmtime();
		$data['start_time'] = trim($data['start_time'])==''?0:to_timespan($data['start_time']);
		$data['bad_time'] = trim($data['bad_time'])==''?0:to_timespan($data['bad_time']);
		
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			require_once(APP_ROOT_PATH."app/Lib/common.php");
			//成功提示
			syn_deal_status($list);
			syn_deal_match($list);
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($log_info.L("INSERT_FAILED").$dbErr,0);
			$this->error(L("INSERT_FAILED").$dbErr);
		}
	}	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['is_delete'] = 0;
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		
		$vo['start_time'] = $vo['start_time']!=0?to_date($vo['start_time']):'';
		$vo['bad_time'] = $vo['bad_time']!=0?to_date($vo['bad_time']):'';
		
		$vo['repay_start_time'] = $vo['repay_start_time']!=0?to_date($vo['repay_start_time'],"Y-m-d"):'';
		
		if($vo['deal_status'] ==0){
			$level_list = load_auto_cache("level");
			$u_level = M("User")->where("id=".$vo['user_id'])->getField("level_id");
			$vo['services_fee'] = $level_list['services_fee'][$u_level];
		}
		
		$this->assign ( 'vo', $vo );
		
		if(trim($_REQUEST['type'])=="deal_status"){
			$this->display ("Deal:deal_status");
			exit();
		}
				
		$deal_cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$deal_cate_tree = D("DealCate")->toFormatTree($deal_cate_tree,'name');
		$this->assign("deal_cate_tree",$deal_cate_tree);
		
		$deal_agency = M("DealAgency")->where('is_effect = 1')->order('sort DESC')->findAll();
		$this->assign("deal_agency",$deal_agency);
		
		$deal_type_tree = M("DealLoanType")->findAll();
		$deal_type_tree = D("DealLoanType")->toFormatTree($deal_type_tree,'name');
		$this->assign("deal_type_tree",$deal_type_tree);
		
		
		$this->display ();
	}
	
	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['name']))
		{
			$this->error(L("DEAL_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['sub_name']))
		{
			$this->error(L("DEAL_SUB_NAME_EMPTY_TIP"));
		}		
		if($data['cate_id']==0)
		{
			$this->error(L("DEAL_CATE_EMPTY_TIP"));
		}
		
		$data['update_time'] = get_gmtime();
		
		// lu 修改发标时间
		if($_POST['c_id']){
			$c_time=$_POST['c_year'].'-'.$_POST['c_month'].'-'.$_POST['c_day'].' '.$_POST['c_hour'].':'.$_POST['c_minute'];
			$c_time=strtotime($c_time);
			$data['create_time']=$c_time;
			}
			
	
		$data['publish_wait'] = 0;
		
		$data['start_time'] = trim($data['start_time'])==''?0:to_timespan($data['start_time']);
		$data['bad_time'] = trim($data['bad_time'])==''?0:to_timespan($data['bad_time']);
		$data['repay_start_time'] = trim($data['repay_start_time'])==''?0:to_timespan($data['repay_start_time']);
		if($data['repay_start_time'] > 0 && intval($data['old_next_repay_time']) == 0){
			$data['next_repay_time'] = next_replay_month($data['repay_start_time']);
		}
		if($data['deal_status'] == 4){
			if($GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."deal_load where deal_id=".$data['id']) <floatval($data['borrow_amount'])){
				$this->error("未满标无法设置为还款状态!");
				exit();
			}
			else{
				if($GLOBALS['db']->getOne("SELECT repay_start_time FROM ".DB_PREFIX."deal where id=".$data['id']) <= 0){
					$data['repay_start_time'] = get_gmtime();
				}
			}
		}
		
		
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			require_once(APP_ROOT_PATH."app/Lib/common.php");
			//成功提示
			syn_deal_status($data['id']);
			syn_deal_match($data['id']);
			//发送电子协议邮件
			require_once(APP_ROOT_PATH."app/Lib/deal.php");
			send_deal_contract_email($data['id'],array(),$data['user_id']);
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($log_info.L("UPDATE_FAILED").$dbErr,0);
			$this->error(L("UPDATE_FAILED").$dbErr,0);
		}
	}
	
	
	public function delete() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
					rm_auto_cache("cache_deal_cart",array("id"=>$data['id']));
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 1 );
				if ($list!==false) {
					
					save_log($info.l("DELETE_SUCCESS"),1);
					$this->success (l("DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("DELETE_FAILED"),0);
					$this->error (l("DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}
	
	public function restore() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
					rm_auto_cache("cache_deal_cart",array("id"=>$data['id']));					
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 0 );
				if ($list!==false) {
										
					save_log($info.l("RESTORE_SUCCESS"),1);
					$this->success (l("RESTORE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("RESTORE_FAILED"),0);
					$this->error (l("RESTORE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}
	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				//删除的验证
				if(M("DealOrder")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->count()>0)
				{
					$this->error(l("DEAL_ORDER_NOT_EMPTY"),$ajax);
				}
				M("DealPayment")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("DealLoad")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("DealLoadRepay")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("DealRepay")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("DealCollect")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("DealInrepayRepay")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
					rm_auto_cache("cache_deal_cart",array("id"=>$data['id']));
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
					
				if ($list!==false) {
					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	
	
	public function set_sort()
	{
		$id = intval($_REQUEST['id']);
		$sort = intval($_REQUEST['sort']);
		$log_info = M(MODULE_NAME)->where("id=".$id)->getField('name');
		if(!check_sort($sort))
		{
			$this->error(l("SORT_FAILED"),1);
		}
		M(MODULE_NAME)->where("id=".$id)->setField("sort",$sort);
		rm_auto_cache("cache_deal_cart",array("id"=>$id));
		save_log($log_info.l("SORT_SUCCESS"),1);
		$this->success(l("SORT_SUCCESS"),1);
	}
	
	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = M(MODULE_NAME)->where("id=".$id)->getField("name");
		$c_is_effect = M(MODULE_NAME)->where("id=".$id)->getField("is_effect");  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M(MODULE_NAME)->where("id=".$id)->setField("is_effect",$n_is_effect);	
		M(MODULE_NAME)->where("id=".$id)->setField("update_time",get_gmtime());	
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;	
	}
	
	
	
	public function show_detail()
	{
		$id = intval($_REQUEST['id']);
		syn_deal_status($id);
		$deal_info = M("Deal")->getById($id);
		$this->assign("deal_info",$deal_info);
		
		$true_repay_money  =  M("DealLoadRepay")->where("deal_id=".$id)->sum("repay_money");
		
		$this->assign("true_repay_money",$true_repay_money);
		
		$loan_list = D("DealLoad")->where('deal_id='.$id)->order("id ASC")->findall();
		$this->assign("loan_list",$loan_list);
		
		$this->display();
	}
	
	public function filter_html()
	{
		$shop_cate_id = intval($_REQUEST['shop_cate_id']);
		$deal_id = intval($_REQUEST['deal_id']);
		$ids = $this->get_parent_ids($shop_cate_id);
		$filter_group = M("FilterGroup")->where(array("cate_id"=>array("in",$ids)))->findAll();
		foreach($filter_group as $k=>$v)
		{
			$filter_group[$k]['value'] = M("DealFilter")->where("filter_group_id = ".$v['id']." and deal_id = ".$deal_id)->getField("filter");
		}
		$this->assign("filter_group",$filter_group);
		$this->display();
	}
	
	//获取当前分类的所有父分类包含本分类的ID
	private $cate_ids = array();
	private function get_parent_ids($shop_cate_id)
	{
		$pid = $shop_cate_id;
		do{
			$pid = M("ShopCate")->where("id=".$pid)->getField("pid");
			if($pid>0)
			$this->cate_ids[] = $pid;
		}while($pid!=0);
		$this->cate_ids[] = $shop_cate_id;
		return $this->cate_ids;
	}
	
	
	public function publish()
	{
		$map['publish_wait'] = 1;
		$map['is_delete'] = 0;
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
	
	public function load_user(){
		$return= array("status"=>0,"message"=>"");
		$id = intval($_REQUEST['id']);
		if($id==0){
			ajax_return($return);
			exit();
		}
		$user = $GLOBALS['db']->getRow("SELECT u.*,l.name,l.point as l_point,l.services_fee,enddate FROM ".DB_PREFIX."user u LEFT JOIN ".DB_PREFIX."user_level l ON u.level_id = l.id WHERE u.id=".$id);
		if(!$user){
			ajax_return($return);
			exit();
		}
		$return['status']=1;
		$return['user']=$user;
		ajax_return($return);
		exit();
	}
	
	//补还功能
	
	public function after_repay_list()
	{
		$this->assign("main_title",L("DEAL_AFTER_REPAY_LIST"));
		//分类
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		
		//开始加载搜索条件
		if(intval($_REQUEST['id'])>0)
		$map['id'] = intval($_REQUEST['id']);
		$map['is_delete'] = 0;
		if(trim($_REQUEST['name'])!='')
		{
			$map['name'] = array('like','%'.trim($_REQUEST['name']).'%');			
		}

		if(intval($_REQUEST['cate_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("deal_cate");
			$cate_ids = $child->getChildIds(intval($_REQUEST['cate_id']));
			$cate_ids[] = intval($_REQUEST['cate_id']);
			$map['cate_id'] = array("in",$cate_ids);
		}
		
		
		if(trim($_REQUEST['user_name'])!='')
		{
			$sql  ="select group_concat(id) from ".DB_PREFIX."user where user_name like '%".trim($_REQUEST['user_name'])."%'";
			
			$ids = $GLOBALS['db']->getOne($sql);
			$map['user_id'] = array("in",$ids);
		}
		
		$map['publish_wait'] = 0;
		
		$deal_ids[] = 0;
		
		$temp_ids = $GLOBALS['db']->getAll("SELECT id FROM ".DB_PREFIX."deal as d where deal_status in(4,5) AND repay_money > round((SELECT sum(repay_money) FROM ".DB_PREFIX."deal_load_repay WHERE deal_id = d.id),2)");
		foreach($temp_ids as $k=>$v){
			$deal_ids[] = $v['id'];
		}
		$map['id'] = array("in",implode(",",$deal_ids));
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
	function after_repay(){
		$id = intval($_REQUEST['id']);
		if($id == 0){
			$this->error("操作失败",0);
			die();
		}
		
		require_once(APP_ROOT_PATH."app/Lib/common.php");
		require_once(APP_ROOT_PATH."app/Lib/deal.php");
		require_once(APP_ROOT_PATH."system/libs/user.php");
		$deal = get_deal($id);
		$deal_load_list = get_deal_load_list($deal);
		//查询是否有提前还款
		$time =get_gmtime();
		$inrepay_repay_time = 0;
		
		//最早提前还款的时间
		$inrepay_repay_kk = -1;
		$inrepay_repay  = $GLOBALS['db']->getRow("SELECT * FROM  ".DB_PREFIX."deal_inrepay_repay where deal_id=".$id);
		if($inrepay_repay){
			$inrepay_repay_time = intval($inrepay_repay['true_repay_time']);
			$deal_repay_list  = $GLOBALS['db']->getAll("SELECT true_repay_time FROM  ".DB_PREFIX."deal_repay where deal_id=".$id." ORDER BY id ASC");
			foreach($deal_repay_list as $drk=>$drv){
				if($drv['true_repay_time'] - $inrepay_repay_time >= 0 && $inrepay_repay_kk == -1){
					$inrepay_repay_kk = $drk;
				}
			}
		}
		$user_load_ids = $GLOBALS['db']->getAll("SELECT deal_id,user_id,money FROM ".DB_PREFIX."deal_load WHERE deal_id=".$id);

		foreach($deal_load_list as $dk=>$dv){
			foreach($user_load_ids as $k=>$v){
				//本金
				if($dk==0){
					$user_self_money[$v['user_id']][$k] = 0;
					//本息
					$user_repay_money[$v['user_id']][$k] = 0;
					//违约金
					$user_impose_money[$v['user_id']][$k] = 0;
					//管理费
					$user_manage_money[$v['user_id']][$k] = 0;
					//第几期还的款
					$user_repay_k[$v['user_id']][$k] = 0;
				}
				
				$v['repay_start_time'] = $deal['repay_start_time'];
				$v['repay_time'] = $deal['repay_time'];
				$v['rate'] = $deal['rate'];
				$v['u_key'] = $k;
				
				//获取某个用户当前的回款信息列表
				$user_load_list[$v['user_id']] = get_deal_user_load_list($v,$deal['loantype'],$deal['repay_time_type'],$dv['true_repay_time']);
				
				$loan_user_info = array();
				foreach($user_load_list[$v['user_id']] as $kk=>$vv){
					$user_load_data = array();
					//当期已还款，但是会员未得到回款
					if($dv['has_repay']==1 && intval($vv['has_repay']) == 0 && $dk==$kk){
						$user_load_data['deal_id'] = $deal['id'];
						$user_load_data['user_id'] = $v['user_id'];
						$user_load_data['repay_time'] = $vv['repay_day'];
						$user_load_data['true_repay_time'] = $time;
						$user_load_data['is_site_repay'] = 0;
						$user_load_data['status'] = 0;
						
						//==========假如有提前还款START===============
						if($inrepay_repay_kk  != -1){
							//=============少于提前还款时间 按正常还款START==================
							if($kk < $inrepay_repay_kk){
								
								//等额本息的时候才通过公式计算剩余多少本金
								if($deal['loantype']==0){
									$user_load_data['self_money'] = $vv['month_repay_money'] - get_benjin($kk,$deal['repay_time'],$v['money'],$vv['month_repay_money'],$deal['rate'])*$deal['rate']/12/100;
								}
								//每月还息，到期还本
								elseif($deal['loantype']==1){
									$user_load_data['self_money'] = 0;
								}
								//到期还本息
								elseif($deal['loantype']==2){
									$user_load_data['self_money'] = 0;
								}
								
								$user_load_data['repay_money'] = $vv['month_repay_money'];
								$user_load_data['manage_money'] = $vv['month_manage_money'];
								$user_load_data['impose_money'] = $vv['impose_money'];
								if($vv['status']>0)
									$user_load_data['status'] = $vv['status'] - 1;
								
								$content = "您好，您在".app_conf("SHOP_TITLE")."的投标 “<a href=\"".$deal['url']."\">".$deal['name']."</a>”成功还款".($vv['month_repay_money']+$vv['impose_money'])."元，";
								$unext_loan = $user_load_list[$v['user_id']][$kk+1];
								if($unext_loan){
									$content .= "本笔投标的下个还款日为".to_date($unext_loan['repay_day'],"Y年m月d日")."，需要本息".round($unext_loan['month_repay_money'],2)."元。";
								}
								$user_self_money[$v['user_id']][$k] +=(float)$user_load_data['self_money'];
								if($user_load_data['impose_money']!=0||$user_load_data['manage_money']!=0||$user_load_data['repay_money']!=0){
									//更新用户账户资金记录
									modify_account(array("money"=>$user_load_data['impose_money']),$v['user_id'],"标:".$deal['id'].",期:".($kk+1).",逾期罚息");
									
									modify_account(array("money"=>-$user_load_data['manage_money']),$v['user_id'],"标:".$deal['id'].",期:".($kk+1).",投标管理费");
									
									modify_account(array("money"=>$user_load_data['repay_money']),$v['user_id'],"标:".$deal['id'].",期:".($kk+1).",回报本息");
									
									$msg_conf = get_user_msg_conf($v['user_id']);
									//站内信
									if($msg_conf['sms_bidrepaid']==1)
										send_user_msg("",$content,0,$v['user_id'],$time,0,true,9);
									//邮件
									if($msg_conf['mail_bidrepaid']==1){
										
									}
								}
							}
							//=============少于提前还款时间 按正常还款END==================
							
							//=============等于提前还款时间START==================
							elseif($kk == $inrepay_repay_kk){
								if($deal['loantype']==0){//等额本息的时候才通过公式计算剩余多少本金
									$user_load_data['self_money'] = $vv['month_repay_money'] - get_benjin($kk,$deal['repay_time'],$v['money'],$vv['month_repay_money'],$deal['rate'])*$deal['rate']/12/100;
									$user_load_data['impose_money'] = ($user_load_data['self_money'] - $vv['month_repay_money'] + $user_load_data['self_money']*$v['rate']) * (int)trim(app_conf('COMPENSATE_FEE'))/100;
								}
								elseif($deal['loantype']==1){//每月还息，到期还本
									$user_load_data['self_money'] = $v['money'];
									$user_load_data['impose_money'] = $v['money'] * (int)trim(app_conf('COMPENSATE_FEE'))/100;
								}
								elseif($deal['loantype']==2){//每月还息，到期还本
									$user_load_data['self_money'] = $v['money'];
									$user_load_data['impose_money'] = $v['money'] * (int)trim(app_conf('COMPENSATE_FEE'))/100;
								}
									
								$user_self_money[$v['user_id']][$k] +=(float)$user_load_data['self_money'];
								
								if($deal['loantype']==0){//等额本息的时候才通过公式计算剩余多少本金
									$user_load_data['repay_money'] = $vv['month_repay_money'];
									$user_load_data['manage_money'] = $vv['month_manage_money'];
								}
								elseif($deal['loantype']==1){
									$user_load_data['repay_money'] = $vv['month_repay_money'] + $v['money'];
									$user_load_data['manage_money'] = $vv['month_manage_money'];
								}
								elseif($deal['loantype']==2){
									$user_load_data['repay_money'] = $v['money'];
									$user_load_data['manage_money'] = $vv['money'] * trim(app_conf('USER_LOAN_MANAGE_FEE')) /100 * ($kk +1) ;
								}
								$user_repay_k[$v['user_id']][$k] = $kk+1;
							}
							//=============等于提前还款时间END==================
							
							//=============大于于提前还款时间 START==================
							else{
								//其他月份
								//等额本息
								if($deal['loantype']==0){
									$user_load_data['self_money'] = $user_load_data['repay_money'] = ($v['money'] - $user_self_money[$v['user_id']][$k])/($v['repay_time']-$user_repay_k[$v['user_id']][$k]);
									$user_load_data['manage_money'] = 0;
									$user_load_data['impose_money'] = 0;
								}
								//每月还息，到期还本
								elseif($deal['loantype']==1){
									if($user_self_money[$v['user_id']][$k] == 0){
										$user_self_money[$v['user_id']][$k] = $user_load_data['self_money'] = $v['money'];
										$user_load_data['repay_money'] = $vv['month_repay_money'] + $v['money'];
										$user_load_data['impose_money'] = $v['money'] * (int)trim(app_conf('COMPENSATE_FEE'))/100;
										$user_load_data['manage_money'] = $vv['month_manage_money'];
									}
									else{
										$user_load_data['self_money'] = $user_load_data['repay_money'] = 0;
										$user_load_data['manage_money'] = 0;
										$user_load_data['impose_money'] = 0;
									}
								}
								//到期还本息
								elseif($deal['loantype']==2){
									if($user_self_money[$v['user_id']][$k] == 0){
										$user_self_money[$v['user_id']][$k] = $user_load_data['self_money'] = $v['money'];
										$user_load_data['repay_money'] = $vv['month_repay_money'] + $v['money'];
										$user_load_data['impose_money'] = $v['money'] * (int)trim(app_conf('COMPENSATE_FEE'))/100;
										$user_load_data['manage_money'] = $vv['money'] * trim(app_conf('USER_LOAN_MANAGE_FEE')) /100 * ($kk +1) ;
									}
									else{
										$user_load_data['self_money'] = $user_load_data['repay_money'] = 0;
										$user_load_data['manage_money'] = 0;
										$user_load_data['impose_money'] = 0;
									}
								}
							}
							//=============大于于提前还款时间 END==================
							
							$user_repay_money[$v['user_id']][$k] += (float)$user_load_data['repay_money'];
							$user_impose_money[$v['user_id']][$k] += (float)$user_load_data['impose_money'];
							$user_manage_money[$v['user_id']][$k] += (float)$user_load_data['manage_money'];
							$user_load_data['l_key'] = $kk;
							$user_load_data['u_key'] = $k;
						    $load_repay_id = 0;
						    $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$user_load_data,"INSERT","","SILENT");
							
						}
						//==========假如有提前还款END===============
						
						//=========正常回款START ========
						else{
							
								//等额本息的时候才通过公式计算剩余多少本金
								if($deal['loantype']==0){
									$user_load_data['self_money'] = $vv['month_repay_money'] - get_benjin($kk,$deal['repay_time'],$v['money'],$vv['month_repay_money'],$deal['rate'])*$deal['rate']/12/100;
								}
								//每月还息，到期还本
								elseif($deal['loantype']==1){
									if($kk+1 == count($user_load_list[$v['user_id']])){//判断是否是最后一期
										$user_load_data['self_money'] = $v['money'];
									}
									else{
										$user_load_data['self_money'] = 0;
									}
								}
								elseif($deal['loantype']==2){//到期还本息
									if($kk+1 == count($user_load_list[$v['user_id']])){//判断是否是最后一期
										$user_load_data['self_money'] = $v['money'];
									}
									else{
										$user_load_data['self_money'] = 0;
									}
								}
								$user_load_data['repay_money'] = $vv['month_repay_money'];
								$user_load_data['manage_money'] = $vv['month_manage_money'];
								$user_load_data['impose_money'] = $vv['impose_money'];
								if($vv['status']>0)
									$user_load_data['status'] = $vv['status'] - 1;
								
								$load_repay_id = 0;
								$user_load_data['l_key'] = $kk;
								$user_load_data['u_key'] = $k;
								$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$user_load_data,"INSERT","","SILENT");
								$load_repay_id = $GLOBALS['db']->insert_id();
								
							
								if($load_repay_id > 0){
								
									$content = "您好，您在".app_conf("SHOP_TITLE")."的投标 “<a href=\"".$deal['url']."\">".$deal['name']."</a>”成功还款".($vv['month_repay_money']+$vv['impose_money'])."元，";
									$unext_loan = $user_load_list[$v['user_id']][$kk+1];
									
									if($unext_loan){
										$content .= "本笔投标的下个还款日为".to_date($unext_loan['repay_day'],"Y年m月d日")."，需还本息".round($unext_loan['month_repay_money'],2)."元。";
									}
									else{
										$all_repay_money= round($GLOBALS['db']->getOne("SELECT (sum(repay_money)-sum(self_money) + sum(impose_money)) as shouyi FROM ".DB_PREFIX."deal_load_repay WHERE deal_id=".$v['deal_id']." AND user_id=".$v['user_id']),2);
										$all_impose_money = round($GLOBALS['db']->getOne("SELECT sum(impose_money) FROM ".DB_PREFIX."deal_load_repay WHERE deal_id=".$v['deal_id']." AND user_id=".$v['user_id']),2);
										$content .= "本次投标共获得收益:".$all_repay_money."元,其中违约金为:".$all_impose_money."元,本次投标已回款完毕！";
										
									}
									if($user_load_data['impose_money'] !=0 || $user_load_data['manage_money'] !=0 || $user_load_data['repay_money']!=0){
										//更新用户账户资金记录
										modify_account(array("money"=>$user_load_data['impose_money']),$v['user_id'],"标:".$deal['id'].",期:".($kk+1).",逾期罚息");
										
										modify_account(array("money"=>-$user_load_data['manage_money']),$v['user_id'],"标:".$deal['id'].",期:".($kk+1).",投标管理费");
										
										modify_account(array("money"=>$user_load_data['repay_money']),$v['user_id'],"标:".$deal['id'].",期:".($kk+1).",回报本息");
										$msg_conf = get_user_msg_conf($v['user_id']);
										
										
										//短信通知
										if(app_conf("SMS_ON")==1&&app_conf('SMS_REPAY_TOUSER_ON')==1){
											if(!$loan_user_info[$v['user_id']])
												$loan_user_info[$v['user_id']] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$v['user_id']);
											
											$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_LOAD_REPAY_SMS'");
											$tmpl_content = $tmpl['content'];
											
											$notice['user_name'] = $loan_user_info[$v['user_id']]['user_name'];
											$notice['deal_name'] = $deal['sub_name'];
											$notice['deal_url'] = $deal['url'];
											$notice['site_name'] = app_conf("SHOP_TITLE");
											$notice['repay_money'] = $vv['month_repay_money']+$vv['impose_money'];
											if($unext_loan){
												$notice['need_next_repay'] = $unext_loan;
												$notice['next_repay_time'] = to_date($unext_loan['repay_day'],"Y年m月d日");
												$notice['next_repay_money'] = round($unext_loan['month_repay_money'],2);
											}
											else{
												$notice['all_repay_money'] = $all_repay_money;
												$notice['impose_money'] = $all_impose_money;
											}
											
											$GLOBALS['tmpl']->assign("notice",$notice);
											$sms_content = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
											
											$msg_data['dest'] = $loan_user_info[$v['user_id']]['mobile'];
											$msg_data['send_type'] = 0;
											$msg_data['title'] = $msg_data['content'] = addslashes($sms_content);
											$msg_data['send_time'] = 0;
											$msg_data['is_send'] = 0;
											$msg_data['create_time'] = $time;
											$msg_data['user_id'] = $v['user_id'];
											$msg_data['is_html'] = 0;
											$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入				
										}
										
										//站内信
										if($msg_conf['sms_bidrepaid']==1)
											send_user_msg("",$content,0,$v['user_id'],$time,0,true,9);
										//邮件
										if($msg_conf['mail_bidrepaid']==1 && app_conf('MAIL_ON')==1){
											if(!$loan_user_info[$v['user_id']])
												$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$v['user_id']);
											else
												$user_info = $loan_user_info[$v['user_id']];
												
											$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_LOAD_REPAY_EMAIL'");
											$tmpl_content = $tmpl['content'];
											
											$notice['user_name'] = $user_info['user_name'];
											$notice['deal_name'] = $deal['sub_name'];
											$notice['deal_url'] = $deal['url'];
											$notice['site_name'] = app_conf("SHOP_TITLE");
											$notice['site_url'] = get_domain().APP_ROOT;
											$notice['help_url'] = get_domain().url("index","helpcenter");
											$notice['msg_cof_setting_url'] = get_domain().url("index","uc_msg#setting");
											$notice['repay_money'] = $vv['month_repay_money']+$vv['impose_money'];
											if($unext_loan){
												$notice['need_next_repay'] = $unext_loan;
												$notice['next_repay_time'] = to_date($unext_loan['repay_day'],"Y年m月d日");
												$notice['next_repay_money'] = round($unext_loan['month_repay_money'],2);
											}
											else{
												$notice['all_repay_money'] = $all_repay_money;
												$notice['impose_money'] = $all_impose_money;
											}
											
											$GLOBALS['tmpl']->assign("notice",$notice);
											
											$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
											$msg_data['dest'] = $user_info['email'];
											$msg_data['send_type'] = 1;
											$msg_data['title'] = "“".$deal['name']."”回款通知";
											$msg_data['content'] = addslashes($msg);
											$msg_data['send_time'] = 0;
											$msg_data['is_send'] = 0;
											$msg_data['create_time'] = $time;
											$msg_data['user_id'] = $user_info['id'];
											$msg_data['is_html'] = $tmpl['is_html'];
											$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
										}
									}
								}
						}
						//=========正常回款END =============
					}
				}
				
				//最后一期并且有提前还款
				if($user_repay_money[$v['user_id']][$k] >0 && $dk+1 == count($deal_load_list) && $inrepay_repay_kk !=-1){
					$all_repay_money = round($GLOBALS['db']->getOne("SELECT (sum(repay_money)-sum(self_money) + sum(impose_money)) as shouyi FROM ".DB_PREFIX."deal_load_repay WHERE deal_id=".$deal['id']." AND user_id=".$v['user_id']),2);
					$all_impose_money = round($GLOBALS['db']->getOne("SELECT sum(impose_money) FROM ".DB_PREFIX."deal_load_repay WHERE deal_id=".$deal['id']." AND user_id=".$v['user_id']),2);
					
					$content = "您好，您在".app_conf("SHOP_TITLE")."的投标 “<a href=\"".$deal['url']."\">".$deal['name']."</a>”提前还款,";
					$content .= "本次投标共获得收益:".$all_repay_money."元,其中违约金为:".$all_impose_money."元,本次投标已回款完毕！";
					
					
					//更新用户账户资金记录
					modify_account(array("money"=>$user_impose_money[$v['user_id']][$k]),$v['user_id'],"标:".$deal['id'].",违约金");
					
					modify_account(array("money"=>-$user_manage_money[$v['user_id']][$k]),$v['user_id'],"标:".$deal['id'].",投标管理费");
					
					modify_account(array("money"=>$user_repay_money[$v['user_id']][$k]),$v['user_id'],"标:".$deal['id'].",回报本息");
								
					$msg_conf = get_user_msg_conf($v['user_id']);
					//短信通知
					if(app_conf("SMS_ON")==1&&app_conf('SMS_REPAY_TOUSER_ON')==1){
						if(!$loan_user_info[$v['user_id']])
							$loan_user_info[$v['user_id']] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$v['user_id']);
						
						$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_LOAD_REPAY_SMS'");
						$tmpl_content = $tmpl['content'];
						
						$notice['user_name'] = $loan_user_info[$v['user_id']]['user_name'];
						$notice['deal_name'] = $deal['sub_name'];
						$notice['deal_url'] = $deal['url'];
						$notice['site_name'] = app_conf("SHOP_TITLE");
						$notice['repay_money'] = $vv['month_repay_money']+$vv['impose_money'];
						
						$notice['all_repay_money'] = $all_repay_money;
						$notice['impose_money'] = $all_impose_money;
						
						$GLOBALS['tmpl']->assign("notice",$notice);
						$sms_content = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
						
						$msg_data['dest'] = $loan_user_info[$v['user_id']]['mobile'];
						$msg_data['send_type'] = 0;
						$msg_data['title'] = $msg_data['content'] = addslashes($sms_content);
						$msg_data['send_time'] = 0;
						$msg_data['is_send'] = 0;
						$msg_data['create_time'] = $time;
						$msg_data['user_id'] = $v['user_id'];
						$msg_data['is_html'] = 0;
						$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入				
					}
					//站内信
					if($msg_conf['sms_bidrepaid']==1)
						send_user_msg("",$content,0,$v['user_id'],$time,0,true,9);
					//邮件
					if($msg_conf['mail_bidrepaid']==1 && app_conf('MAIL_ON')==1){
						$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$v['user_id']);
						$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_LOAD_REPAY_EMAIL'");
						$tmpl_content = $tmpl['content'];
						
						$notice['user_name'] = $user_info['user_name'];
						$notice['deal_name'] = $deal['sub_name'];
						$notice['deal_url'] = $deal['url'];
						$notice['site_name'] = app_conf("SHOP_TITLE");
						$notice['site_url'] = get_domain().APP_ROOT;
						$notice['help_url'] = get_domain().url("index","helpcenter");
						$notice['msg_cof_setting_url'] = get_domain().url("index","uc_msg#setting");
						$notice['repay_money'] = $vv['month_repay_money']+$vv['impose_money'];
						
						$notice['all_repay_money'] = $all_repay_money;
						$notice['impose_money'] = $all_impose_money;
						
						$GLOBALS['tmpl']->assign("notice",$notice);
						
						$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
						$msg_data['dest'] = $user_info['email'];
						$msg_data['send_type'] = 1;
						$msg_data['title'] = "“".$deal['name']."”回款通知";
						$msg_data['content'] = addslashes($msg);
						$msg_data['send_time'] = 0;
						$msg_data['is_send'] = 0;
						$msg_data['create_time'] = $time;
						$msg_data['user_id'] = $user_info['id'];
						$msg_data['is_html'] = $tmpl['is_html'];
						$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
					}
				}
			}
		}
		$this->success("操作完成",0);
	}
}
?>