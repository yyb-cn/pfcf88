<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

/**
 * 获取指定的投标
 */
function get_deal($id=0,$cate_id=0)
{		
		$time = get_gmtime();
			
		if($id==0)  //有ID时不自动获取
		{	
			return false;	
			/*$sql = "select id from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0  ";
			if($cate_id>0)
			{
				
				$ids =load_auto_cache("deal_sub_parent_cate_ids",array("cate_id"=>$cate_id));

				$sql .= " and cate_id in (".implode(",",$ids).")";
			}
			
			$sql.=" order by sort desc";
			$deal = $GLOBALS['db']->getRow($sql);
			*/
			
		}
		else{
			$deal = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."deal where id = ".intval($id)." and is_effect = 1 and is_delete = 0 ");
		}
		
		if($deal)
		{
			if($deal['deal_status']!=3 && $deal['deal_status']!=5)
			{
				syn_deal_status($deal['id']);
				$deal = $GLOBALS['db']->getRow("select *,(start_time + enddate*24*3600 - ".$time.") as remain_time from ".DB_PREFIX."deal where id = ".$deal['id']." and is_effect = 1 and is_delete = 0");
			}
			
			//当为天的时候
			if($deal['repay_time_type'] == 0){
				$true_repay_time = 1;
			}
			else{
				$true_repay_time = $deal['repay_time'];
			}
			
			if(trim($deal['titlecolor']) != ''){
				$deal['color_name'] = "<span style='color:#".$deal['titlecolor']."'>".$deal['name']."</span>";
			}
			else
			{
				$deal['color_name'] = $deal['name'];
			}
			
			if($deal['cate_id'] > 0){
				$deal['cate_info'] = $GLOBALS['db']->getRowCached("select name,brief,uname,icon from ".DB_PREFIX."deal_cate where id = ".$deal['cate_id']." and is_effect = 1 and is_delete = 0");
			}
			if($deal['type_id'] > 0){
				$deal['type_info'] = $GLOBALS['db']->getRowCached("select name,brief,uname,icon from ".DB_PREFIX."deal_loan_type where id = ".$deal['type_id']." and is_effect = 1 and is_delete = 0");
			}
			if($deal['agency_id'] > 0){
				$deal['agency_info'] = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_agency where id = ".$deal['agency_id']." and is_effect = 1");
			}
			
			$deal['borrow_amount_format'] = format_price($deal['borrow_amount']);
			
			$deal['rate_foramt'] = number_format($deal['rate'],2);

/*
    计算金额
*/     
    
            //按天算收益
            if($deal['repay_time_type'] == 0){
                $deal['month_repay_money'] = $deal['borrow_amount'] * $deal['rate']/365/100 * $deal['repay_time'];
            }    

			//本息还款金额
			if($deal['loantype'] == 0){
				$deal['month_repay_money'] = pl_it_formula($deal['borrow_amount'],$deal['rate']/12/100,$true_repay_time);
			}
			//每月付息，到期还本
			elseif($deal['loantype'] == 1)
				$deal['month_repay_money'] = av_it_formula($deal['borrow_amount'],$deal['rate']/12/100) ;
			//到期还本息
			elseif($deal['loantype'] == 2)
				$deal['month_repay_money'] = $deal['borrow_amount'] * $deal['rate']/12/100 * $true_repay_time;
				
			$deal['month_repay_money_format'] = format_price($deal['month_repay_money']);
			
			//到期还本息管理费
			if($deal['loantype'] == 2)
				$deal['month_manage_money'] = $deal['borrow_amount']*app_conf('MANAGE_FEE')/100 * $true_repay_time;
			else
				$deal['month_manage_money'] = $deal['borrow_amount']*app_conf('MANAGE_FEE')/100;
				
			$deal['month_manage_money_format'] = format_price($deal['month_manage_money']);
			$deal['all_manage_money'] = $deal['month_manage_money'] * $deal["repay_time"];
			$deal['true_month_repay_money'] = $deal['month_repay_money'] + $deal['month_manage_money'];
			
			//还需多少钱
			$deal['need_money'] = format_price($deal['borrow_amount'] - $deal['load_money']);
			//百分比
			$deal['progress_point'] = $deal['load_money']/$deal['borrow_amount']*100;
			
			//投标剩余时间
			if($deal['deal_status'] <> 1 || $deal['remain_time'] <= 0){
				$deal['remain_time_format'] = "0".$GLOBALS['lang']['DAY']."0".$GLOBALS['lang']['HOUR']."0".$GLOBALS['lang']['MIN'];
			}
			else{
				$deal['remain_time_format'] = remain_time($deal['remain_time']);
			}
			
			if($deal['deal_status']==4){
				if($deal['repay_time_type'] == 0){
					$r_y = to_date($deal['repay_start_time'],"Y");
					$r_m = to_date($deal['repay_start_time'],"m");
					$r_d = to_date($deal['repay_start_time'],"d");
					if($r_m-1 <=0){
						$r_m = 12;
						$r_y = $r_y-1;
					}
					else{
						$r_m = $r_m - 1;
					}
					$deal["type_repay_start_time"]  = to_timespan($r_y."-".$r_m."-".$r_d,"Y-m-d") + $deal['repay_time']*24*3600;
					$deal["type_next_repay_time"] = next_replay_month($deal['type_repay_start_time']);
				}
				
				if($deal['last_repay_time'] > 0){
					$deal["next_repay_time"] = next_replay_month($deal['last_repay_time']);
				}
				else{
					$deal["next_repay_time"] = next_replay_month($deal['repay_start_time']);
				}
				
				//总的必须还多少本息
				//本额等息
				if($deal['loantype'] == 0)
					$deal['remain_repay_money'] = $deal['month_repay_money'] * $true_repay_time;
				//每月还息，到期还本
				elseif($deal['loantype'] == 1)
					$deal['remain_repay_money'] = $deal['borrow_amount'] + $deal['month_repay_money'] * $true_repay_time;
				elseif($deal['loantype'] == 2)
					$deal['remain_repay_money'] = $deal['borrow_amount'] + $deal['month_repay_money'];
					
				//还有多少需要还
				$deal['need_remain_repay_money'] = $deal['remain_repay_money'] - $deal['repay_money'];
				//还款进度条
				$deal['repay_progress_point'] =  $deal['repay_money']/$deal['remain_repay_money']*100;
				
				//最后一期还款
				if($deal['loantype'] == 2)
					$deal['last_month_repay_money'] = $deal['remain_repay_money'];
				else
					$deal['last_month_repay_money'] = $deal['remain_repay_money'] - round($deal['month_repay_money'],2)*($true_repay_time-1);
				
				//最后的还款日期
				$y=to_date($deal['repay_start_time'],"Y");
				$m=to_date($deal['repay_start_time'],"m");
				$d=to_date($deal['repay_start_time'],"d");
				$y = $y + intval(($m+$true_repay_time)/12);
				$m = ($m+$true_repay_time)%12;
				
				$deal["end_repay_time"] = to_timespan($y."-".$m."-".$d,"Y-m-d");
				
				if(to_date($deal["end_repay_time"],"Ymd") < to_date($time,"Ymd")){
					$deal['exceed_the_time'] = true;
				}
				//罚息
				$is_check_impose = true;
				//到期还本息 只有最后一个月后才算罚息
				if($deal['loantype'] == 2){
					//算出到期还本息的最后一个月是否小于今天
					if($deal['exceed_the_time']){
						$is_check_impose = true;
					}
					else{
						$is_check_impose = false;
					}
				}
				
				if($deal["next_repay_time"] - $time <0 && $is_check_impose){
					//晚多少天
					$time_span = to_timespan(to_date($time,"Y-m-d"),"Y-m-d");
					$next_time_span = to_timespan(to_date($deal['next_repay_time'],"Y-m-d"),"Y-m-d");
					$day  = ceil(($time_span-$next_time_span)/24/3600);
					
					$impose_fee = trim(app_conf('IMPOSE_FEE_DAY1'));
					$manage_impose_fee = trim(app_conf('MANAGE_IMPOSE_FEE_DAY1'));
					if($day > 31){
						$impose_fee = trim(app_conf('IMPOSE_FEE_DAY2'));
						$manage_impose_fee = trim(app_conf('MANAGE_IMPOSE_FEE_DAY2'));
					}
					
					//罚息
					$deal['impose_money'] = $deal['month_repay_money']*$impose_fee*$day/100;
					
					//罚管理费
					$deal['impose_money'] += $deal['month_repay_money']*$manage_impose_fee*$day/100;
				}
			}
			
			$durl = url("index","deal",array("id"=>$deal['id']));
			$deal['share_url'] = get_domain().$durl;
			if($GLOBALS['user_info'])
			{
				if(app_conf("URL_MODEL")==0)
				{
					$deal['share_url'] .= "&r=".base64_encode(intval($GLOBALS['user_info']['id']));
				}
				else
				{
					$deal['share_url'] .= "?r=".base64_encode(intval($GLOBALS['user_info']['id']));
				}
			}
			$deal['url'] = $durl;
			
		}
		return $deal;
	
}

/**

下载pdf函数  linjingxiong++
**/

	function forceDownload($filename) {

	if (false == file_exists($filename)) {
		return false;
	}
	
	// http headers
	header('Content-Type: application-x/force-download');
	header('Content-Disposition: attachment; filename="' . basename($filename) .'"');
	header('Content-length: ' . filesize($filename));

	// for IE6
	if (false === strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6')) {
		header('Cache-Control: no-cache, must-revalidate');
	}
	header('Pragma: no-cache');
		
	// read file content and output
	return readfile($filename);;
}
/**
 * 获取正在进行的投标列表
 */
function get_deal_list($limit,$cate_id=0, $where='',$orderby = '')
{		
		
		$time = get_gmtime();
		
		$count_sql = "select count(*) from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 ";
		if(es_cookie::get("shop_sort_field")=="ulevel"){
			$extfield = ",(SELECT u.level_id FROM fanwe_user u WHERE u.id=user_id ) as ulevel";
		}
		
		$sql = "select *,start_time as last_time,(load_money/borrow_amount*100) as progress_point,(start_time + enddate*24*3600 - ".$time.") as remain_time $extfield from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 ";
		if($cate_id>0)
		{
			$ids =load_auto_cache("deal_sub_parent_cate_ids",array("cate_id"=>$cate_id));
			$sql .= " and cate_id in (".implode(",",$ids).")";
			$count_sql .= " and cate_id in (".implode(",",$ids).")";
		}
		
		if($where != '')
		{
			$sql.=" and ".$where;
			$count_sql.=" and ".$where;
		}
		
		if($orderby=='')
		$sql.=" order by sort desc limit ".$limit;
		else
		$sql.=" order by ".$orderby." limit ".$limit;
		

		$deals = $GLOBALS['db']->getAll($sql);		
		$deals_count = $GLOBALS['db']->getOne($count_sql);
		//echo $sql;
 		if($deals)
		{
			foreach($deals as $k=>$deal)
			{	
				//当为天的时候
				if($deal['repay_time_type'] == 0){
					$true_repay_time = 1;
				}
				else{
					$true_repay_time = $deal['repay_time'];
				}
				
				if(trim($deal['titlecolor']) != ''){
					$deal['color_name'] = "<span style='color:#".$deal['titlecolor']."'>".$deal['name']."</span>";
				}
				else{
					$deal['color_name'] = $deal['name'];
				}	
				//格式化数据
				$deal['borrow_amount_format'] = format_price($deal['borrow_amount']);
				
				$deal['rate_foramt'] = number_format($deal['rate'],2);
				
				
				//本息还款金额
				if($deal['loantype'] == 0){
					$deal['month_repay_money'] = pl_it_formula($deal['borrow_amount'],$deal['rate']/12/100,$true_repay_time);
				}
				//每月付息，到期还本
				elseif($deal['loantype'] == 1)
					$deal['month_repay_money'] = av_it_formula($deal['borrow_amount'],$deal['rate']/12/100) ;
				//到期还本息
				elseif($deal['loantype'] == 2)
					$deal['month_repay_money'] = $deal['borrow_amount'] * $deal['rate']/12/100 * $true_repay_time;
					

				$deal['month_repay_money_format'] = format_price($deal['month_repay_money']);
				
				//到期还本息管理费
				if($deal['loantype'] == 2){
					$deal['month_manage_money'] = $deal['borrow_amount']*app_conf('MANAGE_FEE')/100 * $true_repay_time;
				}
				else
					$deal['month_manage_money'] = $deal['borrow_amount']*app_conf('MANAGE_FEE')/100;
					
				$deal['month_manage_money_format'] = format_price($deal['month_manage_money']);
				$deal['all_manage_money'] = $deal['month_manage_money'] * $deal["repay_time"];
				$deal['true_month_repay_money'] = $deal['month_repay_money'] + $deal['month_manage_money'];
			
				//还需多少钱
				$deal['need_money'] = format_price($deal['borrow_amount'] - $deal['load_money']);
				
				$durl = url("index","deal",array("id"=>$deal['id']));				
				$deal['share_url'] = get_domain().$durl;
				
				$deal['user'] = get_user("user_name,level_id,province_id,city_id",$deal['user_id']);
				
				if($deal['cate_id'] > 0){
					$deal['cate_info'] = $GLOBALS['db']->getRowCached("select name,brief,uname,icon from ".DB_PREFIX."deal_cate where id = ".$deal['cate_id']." and is_effect = 1 and is_delete = 0");
				}
				if($deal['type_id'] > 0){
					$deal['type_info'] = $GLOBALS['db']->getRowCached("select name,brief,uname,icon from ".DB_PREFIX."deal_loan_type where id = ".$deal['type_id']." and is_effect = 1 and is_delete = 0");
				}
				
				if($GLOBALS['user_info'])
				{
					if(app_conf("URL_MODEL")==0)
					{
						$deal['share_url'] .= "&r=".base64_encode(intval($GLOBALS['user_info']['id']));
					}
					else
					{
						$deal['share_url'] .= "?r=".base64_encode(intval($GLOBALS['user_info']['id']));
					}
				}
				if($deal['deal_status'] <> 1 || $deal['remain_time'] <= 0){
					$deal['remain_time_format'] = "0".$GLOBALS['lang']['DAY']."0".$GLOBALS['lang']['HOUR']."0".$GLOBALS['lang']['MIN'];
				}
				else{
					$deal['remain_time_format'] = remain_time($deal['remain_time']);
				}
				
				if($deal['deal_status']==4){
					
					if($deal['repay_time_type'] == 0){
						$r_y = to_date($deal['repay_start_time'],"Y");
						$r_m = to_date($deal['repay_start_time'],"m");
						$r_d = to_date($deal['repay_start_time'],"d");
						if($r_m-1 <=0){
							$r_m = 12;
							$r_y = $r_y-1;
						}
						else{
							$r_m = $r_m - 1;
						}
						$deal["type_repay_start_time"]  = to_timespan($r_y."-".$r_m."-".$r_d,"Y-m-d") + $deal['repay_time']*24*3600;
						$deal["type_next_repay_time"] = next_replay_month($deal['type_repay_start_time']);
					}
					
										
					if($deal['last_repay_time'] > 0){
						$deal["next_repay_time"] = next_replay_month($deal['last_repay_time']);
					}
					else{
						$deal["next_repay_time"] = next_replay_month($deal['repay_start_time']);
					}
					
					//总的必须还多少本息
					//
					if($deal['loantype'] == 0)
						$deal['remain_repay_money'] = $deal['month_repay_money'] * $true_repay_time;
					elseif($deal['loantype'] == 1)//每月还息，到期还本
						$deal['remain_repay_money'] = $deal['borrow_amount'] + $deal['month_repay_money'] * $true_repay_time;
					elseif($deal['loantype'] == 2)
						$deal['remain_repay_money'] = $deal['borrow_amount'] + $deal['month_repay_money'];
						
					//还有多少需要还
					$deal['need_remain_repay_money'] = $deal['remain_repay_money'] - $deal['repay_money'];
					//还款进度条
					$deal['repay_progress_point'] =  $deal['repay_money']/$deal['remain_repay_money']*100;
					
					//最后一期还款
					if($deal['loantype'] == 2)
						$deal['last_month_repay_money'] = $deal['remain_repay_money'];
					else
						$deal['last_month_repay_money'] = $deal['remain_repay_money'] - $deal['month_repay_money']*($true_repay_time-1);
					
					//最后的还款日期
					$y=to_date($deal['repay_start_time'],"Y");
					$m=to_date($deal['repay_start_time'],"m");
					$d=to_date($deal['repay_start_time'],"d");
					$y = $y + intval(($m+$true_repay_time)/12);
					$m = ($m+$true_repay_time)%12;
					
					$deal["end_repay_time"] = to_timespan($y."-".$m."-".$d,"Y-m-d");
					if(to_date($deal["end_repay_time"],"Ymd") < to_date($time,"Ymd")){
						$deal['exceed_the_time'] = true;
					}
					
					//罚息
					$is_check_impose = true;
					//到期还本息 只有最后一个月后才算罚息
					if($deal['loantype'] == 2){
						//算出到期还本息的最后一个月是否小于今天
						if($deal['exceed_the_time']){
							$is_check_impose = true;
						}
						else{
							$is_check_impose = false;
						}
					}
					if($deal["next_repay_time"] - $time <0 && $is_check_impose){
						//晚多少天
						$time_span = to_timespan(to_date($time,"Y-m-d"),"Y-m-d");
						$next_time_span = to_timespan(to_date($deal['next_repay_time'],"Y-m-d"),"Y-m-d");
						$day  = ceil(($time_span-$next_time_span)/24/3600);
						
						$impose_fee = trim(app_conf('IMPOSE_FEE_DAY1'));
						$manage_impose_fee = trim(app_conf('MANAGE_IMPOSE_FEE_DAY1'));
						if($day > 31){
							$impose_fee = trim(app_conf('IMPOSE_FEE_DAY2'));
							$manage_impose_fee = trim(app_conf('MANAGE_IMPOSE_FEE_DAY2'));
						}
						
						//罚息
						$deal['impose_money'] = $deal['month_repay_money']*$impose_fee*$day/100;
						
						//罚管理费
						$deal['impose_money'] += $deal['month_repay_money']*$manage_impose_fee*$day/100;
					}
				}
			
				$durl = url("index","deal",array("id"=>$deal['id']));
				$deal['url'] = $durl;
			
				$deals[$k] = $deal;
			}
		}				
		return array('list'=>$deals,'count'=>$deals_count);	
}

/**
 * 还款列表
 */
function get_deal_load_list($deal){
	$time = get_gmtime();
	$true_repay_time = $deal['repay_time'];
	//当为天的时候
	if($deal['repay_time_type'] == 0){
		$true_repay_time = 1;
		
		$r_y = to_date($deal['repay_start_time'],"Y");
		$r_m = to_date($deal['repay_start_time'],"m");
		$r_d = to_date($deal['repay_start_time'],"d");
		if($r_m-1 <=0){
			$r_m = 12;
			$r_y = $r_y-1;
		}
		else{
			$r_m = $r_m - 1;
		}
		
		$deal["repay_start_time"]  = to_timespan($r_y."-".$r_m."-".$r_d,"Y-m-d") + $deal['repay_time']*24*3600;
	}
	
	$repay_day = $deal['repay_start_time'];
	
	for($i=0;$i<$true_repay_time;$i++){
		
		$loan_list[$i]['status'] = 0;
		$repay_day = $loan_list[$i]['repay_day'] = next_replay_month($repay_day);
		//月还本息
		if($deal['loantype'] == 2)
			$loan_list[$i]['month_repay_money'] = 0;
		else
			$loan_list[$i]['month_repay_money'] = $deal['month_repay_money'];
		
		//最后一个月还本息
		if($i+1 == $true_repay_time){
			$loan_list[$i]['month_repay_money'] = $deal['last_month_repay_money'];
		}
		
		//判断是否已经还完
		$repay_item = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$deal['id']." AND repay_time=".$repay_day."");
		$loan_list[$i]['true_repay_time'] = $repay_item['true_repay_time'];
		//已还
		$loan_list[$i]['month_has_repay_money'] = 0;
		if($repay_item){
			$loan_list[$i]['month_has_repay_money'] = $repay_item['repay_money'];
			$loan_list[$i]['month_manage_money'] = 0;
			
			$loan_list[$i]['has_repay'] = 1;
			$loan_list[$i]['status'] = $repay_item['status']+1;
			$loan_list[$i]['month_repay_money'] -=$loan_list[$i]['repay_money'];
			$loan_list[$i]['impose_money'] = $repay_item['impose_money'];
			
			//真实还多少
			$loan_list[$i]['month_has_repay_money_all'] = $loan_list[$i]['month_has_repay_money'] + $deal['month_manage_money']+$loan_list[$i]['impose_money'];
			
		}
		else{
			//管理费
			if($deal['loantype'] == 2)
			{
				$loan_list[$i]['month_manage_money'] = 0;
				if($i+1 == $true_repay_time){
					$loan_list[$i]['month_manage_money'] = $deal['borrow_amount']* trim(app_conf('MANAGE_FEE')) /100 * $true_repay_time;	
				}
				
			}
			else
				$loan_list[$i]['month_manage_money'] = $deal['borrow_amount']* trim(app_conf('MANAGE_FEE')) /100;
			
			//判断是否罚息
			if($time > $repay_day && $loan_list[$i]['month_repay_money'] > 0){
				//晚多少天
				$time_span = to_timespan(to_date($time,"Y-m-d"),"Y-m-d");
				$next_time_span = to_timespan(to_date($repay_day,"Y-m-d"),"Y-m-d");
				$day  = ceil(($time_span-$next_time_span)/24/3600);
				
				$impose_fee = trim(app_conf('IMPOSE_FEE_DAY1'));
				$manage_impose_fee = trim(app_conf('MANAGE_IMPOSE_FEE_DAY1'));
				if($day > 31){
					$impose_fee = trim(app_conf('IMPOSE_FEE_DAY2'));
					$manage_impose_fee = trim(app_conf('MANAGE_IMPOSE_FEE_DAY2'));
				}
				
				//罚息
				if($deal['loantype'] == 0){
					$loan_list[$i]['impose_money'] = $loan_list[$i]['month_repay_money']*$impose_fee*$day/100;
				}
				elseif($deal['loantype'] == 1)//每月还息，到期还本
				{
					$loan_list[$i]['impose_money'] = ($deal['borrow_amount'] - $loan_list[$i]['month_repay_money'])*$impose_fee*$day/100;
				}
				elseif($deal['loantype'] == 2){
					//到期还款 只有最后一个月超出才罚息
					if($i+1 == $true_repay_time)
						$loan_list[$i]['impose_money'] = $loan_list[$i]['month_repay_money']*$impose_fee*$day/100;
					else{
						$loan_list[$i]['impose_money'] = 0;
					}
				}
				//罚管理费
				$loan_list[$i]['impose_money'] += $loan_list[$i]['month_repay_money']*$manage_impose_fee*$day/100;
			}
		}
		
		//总的必须还多少
		if ($loan_list[$i]['status']== 0)
			$loan_list[$i]['month_need_all_repay_money'] =  $loan_list[$i]['month_repay_money'] + $loan_list[$i]['month_manage_money'] + $loan_list[$i]['impose_money'];
		else{
			$loan_list[$i]['month_need_all_repay_money'] = 0;
		}
		
		
	}
	return $loan_list;
}


/**
 * 用户还款列表
 */
function get_deal_user_load_list($user_load_info,$loantype,$repay_time_type = 1,$true_time = false){
	if($true_time!==false)
		$time = $true_time;
	else
		$time = get_gmtime();
		
	//当为天的时候
	if($repay_time_type == 0){
		$true_repay_time = 1;
	}
	else{
		$true_repay_time = $user_load_info['repay_time'];
	}
	$repay_day = $user_load_info['repay_start_time'];
	for($i=0;$i<$true_repay_time;$i++){
		$loan_list[$i]['status'] = 0;
		$repay_day = $loan_list[$i]['repay_day'] = next_replay_month($repay_day);
		
		//月还本息
		if($loantype == 0)
		{
			$loan_list[$i]['month_repay_money'] = pl_it_formula($user_load_info['money'],$user_load_info['rate']/12/100,$true_repay_time);
		
			//最后一个月还本息
			if($i+1 == $true_repay_time){
				$loan_list[$i]['month_repay_money'] = $loan_list[$i]['month_repay_money']*$true_repay_time - round($loan_list[$i]['month_repay_money'],2)*($true_repay_time-1);
			}
		}
		elseif($loantype == 1){
			$lixi = $loan_list[$i]['month_repay_money'] = av_it_formula($user_load_info['money'],$user_load_info['rate']/12/100);
			//最后一个月还本息
			if($i+1 == $true_repay_time){
				$loan_list[$i]['month_repay_money'] = ($user_load_info['money'] + $loan_list[$i]['month_repay_money']*$true_repay_time) - round($loan_list[$i]['month_repay_money'],2)*($true_repay_time-1);
			}
		}
		elseif($loantype == 2){
			$lixi = $loan_list[$i]['month_repay_money'] = 0;
			//最后一个月还本息
			if($i+1 == $true_repay_time){
				$lixi = $loan_list[$i]['month_repay_money'] = $user_load_info['money'] + $user_load_info['money']*$user_load_info['rate']/12/100*$true_repay_time;
			}
		}
		
		
		//判断是否已经还完
		$repay_item = array();
		$repay_item = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_load_repay USE INDEX(idx_0) WHERE user_id=".$user_load_info['user_id']." AND deal_id=".$user_load_info['deal_id']." AND repay_time=".$repay_day." AND l_key=".$i." AND u_key=".$user_load_info['u_key']);
		
		//管理费
		if($loantype == 2)
		{
			$loan_list[$i]['month_manage_money'] = 0;
			if($i+1 == $true_repay_time){
				$loan_list[$i]['month_manage_money'] = $user_load_info['money']* trim(app_conf('USER_LOAN_MANAGE_FEE')) /100 * $true_repay_time;	
			}
			
		}
		else
			$loan_list[$i]['month_manage_money'] = $user_load_info['money']* trim(app_conf('USER_LOAN_MANAGE_FEE')) /100;
			
		//已还
		$loan_list[$i]['month_has_repay_money'] = 0;
		if($repay_item){
			$loan_list[$i]['month_has_repay_money'] = $repay_item['repay_money'];
			
			$loan_list[$i]['has_repay'] = 1;
			$loan_list[$i]['status'] = $repay_item['status']+1;
			$loan_list[$i]['month_repay_money'] -=$loan_list[$i]['repay_money'];
			$loan_list[$i]['impose_money'] = $repay_item['impose_money'];
			
			//真实还多少
			$loan_list[$i]['month_has_repay_money_all'] = $loan_list[$i]['month_has_repay_money'] + $user_load_info['month_manage_money']+$loan_list[$i]['impose_money'];
			
		}
		else{
			
			//判断是否罚息
			if($time > $repay_day && $loan_list[$i]['month_repay_money'] > 0){
				//晚多少天
				//获得真正的还款
				$true_time = $GLOBALS['db']->getOne("SELECT true_repay_time FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$user_load_info['deal_id']." AND repay_time=".$repay_day." ");
				if($true_time == 0){
					$true_time = $time;
				}
				$time_span = to_timespan(to_date($true_time,"Y-m-d"),"Y-m-d");
				$next_time_span = to_timespan(to_date($repay_day,"Y-m-d"),"Y-m-d");
				$day  = ceil(($time_span-$next_time_span)/24/3600);
				
				if($day >0){
					//普通逾期
					$loan_list[$i]['status'] = 3;
					$impose_fee = trim(app_conf('IMPOSE_FEE_DAY1'));
					//$manage_impose_fee = trim(app_conf('MANAGE_IMPOSE_FEE_DAY1'));
					if($day > 31){//严重逾期
						$loan_list[$i]['status'] = 4;
						$impose_fee = trim(app_conf('IMPOSE_FEE_DAY2'));
						//$manage_impose_fee = trim(app_conf('MANAGE_IMPOSE_FEE_DAY2'));
					}
					
					//罚息
					if($loantype == 0){
						$loan_list[$i]['impose_money'] = $loan_list[$i]['month_repay_money'] *$impose_fee*$day/100;
					}
					elseif($loantype == 1){
						$loan_list[$i]['impose_money'] = $lixi *$impose_fee*$day/100;
					}
					elseif($loantype == 2){
						$loan_list[$i]['impose_money'] = $lixi *$impose_fee*$day/100;
					}
					//罚管理费
					//$loan_list[$i]['impose_money'] += $loan_list[$i]['month_repay_money']*$manage_impose_fee*$day/100;
				}
			}
		}
		
		
	}
	return $loan_list;
}

//获取该期剩余本金
function get_benjin($idx,$all_idx,$amount_money,$month_repay_money,$rate){
	//计算剩多少本金
	$benjin = $amount_money;
	for($i=1;$i<=$idx+1;$i++){
		
		$benjin = $benjin - ($month_repay_money - $benjin*$rate/12/100);
	}
	return $benjin;
}

function insert_success_deal_list(){
	//输出成功案例
	$suc_deal_list =  get_deal_list(10,0,"deal_status in(4,5) "," success_time DESC,sort DESC,id DESC");
	$GLOBALS['tmpl']->assign("succuess_deal_list",$suc_deal_list['list']);
	return $GLOBALS['tmpl']->fetch("inc/insert/success_deal_list.html");
}


//更改过期流标状态
function change_deal_status(){
	//$sql = "select id from ".DB_PREFIX."deal where is_effect = 1 and deal_status = 1 and is_delete = 0 AND load_money/borrow_amount < 1 AND (start_time + enddate*24*3600 - ".get_gmtime().") <=0  ";
	/*$sql = "select id from ".DB_PREFIX."deal where is_effect = 1 and deal_status = 1 and is_delete = 0 AND load_money/borrow_amount <= 1 ";
	$deal_ids = $GLOBALS['db']->getAll($sql);
	
	foreach($deal_ids as $k=>$v)
	{
		syn_deal_status($v['id']);
	}*/
	syn_dealing();
}


?>