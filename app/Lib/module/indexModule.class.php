<?php

define(MODULE_NAME,"index");
require APP_ROOT_PATH.'app/Lib/deal.php';
class indexModule extends SiteBaseModule
{
	public function index()
	{	
		$GLOBALS['tmpl']->caching = true;
		$GLOBALS['tmpl']->cache_lifetime = 600;  //首页缓存10分钟
		$cache_id  = md5(MODULE_NAME.ACTION_NAME);	
		if (!$GLOBALS['tmpl']->is_cached("page/index.html", $cache_id))
		{	
			make_deal_cate_js();
			make_delivery_region_js();	
			change_deal_status();
			//开始输出友情链接
			$f_link_group = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."link_group where is_effect = 1 order by sort desc");
			foreach($f_link_group as $k=>$v)
			{
				$g_links = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."link where is_effect = 1 and show_index = 1 and group_id = ".$v['id']." order by sort desc");
				if($g_links)
				{
					foreach($g_links as $kk=>$vv)
					{
						if(substr($vv['url'],0,7)=='http://')
						{
							$g_links[$kk]['url'] = str_replace("http://","",$vv['url']);
						}
					}
					$f_link_group[$k]['links'] = $g_links;
				}
				else
				unset($f_link_group[$k]);
			}
			//最新借款列表**产品发布**********************经常要修改的地方*********************
			$deal_list =  get_deal_list(6,0,"publish_wait =0 AND deal_status in(1,2,4,5) "," id DESC");
			foreach($deal_list['list'] as $ke => $vel){
				$deal_list['list'][$ke]['repay_start_time'] = date('Y-m-d',$vel['repay_start_time']);
			}
			
			//var_dump($deal_list['list']);exit;
			$GLOBALS['tmpl']->assign("deal_list",$deal_list['list']);
						
			//输出公告
			$notice_list = get_notice(0);
			foreach($notice_list as $kkd=>$vxd){
				$notice_list[$kkd]['update_time'] = date('m月d日',$vxd['update_time']);
				}
			$GLOBALS['tmpl']->assign("notice_list",$notice_list);
			
			//lu 成交数据
		$deal_data = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal where deal_status in(1,2,4,5) and is_delete=0  ");
			foreach($deal_data as $kd=>$vd){
				//lu 累计总交易金额
				$tatal_money+=$vd['borrow_amount'];
				//is_delete=0
				$deal_id[]=$vd['id'];
				}
			//lu 已按期还本金额
			$deal_benjin = $GLOBALS['db']->getAll("select borrow_amount from ".DB_PREFIX."deal where deal_status=5 ");
			foreach($deal_benjin as $kbj=>$vbj){
				 	
				$done_money+=$vbj['borrow_amount'];
				}
			//lu 为用户带来的 收益  	
			$deal_shouyi = $GLOBALS['db']->getAll("select repay_amount from ".DB_PREFIX."user_sta ");
			foreach($deal_shouyi as $ksy=>$vsy){
				$income+=$vsy['repay_amount'];	
				}
		
				
			$success_deal['tatal_money']="￥".number_format($tatal_money);
			$success_deal['done_money']=format_price($done_money);
			$success_deal['income']=format_price($income);
			$GLOBALS['tmpl']->assign("success_deal",$success_deal);	
			
			//使用技巧
			/*$use_tech_list  = get_article_list(12,6);
			$GLOBALS['tmpl']->assign("use_tech_list",$use_tech_list);*/	
			
			//行业新闻
			$news_list  = get_article_list(12,21);
			$GLOBALS['tmpl']->assign("news_list",$news_list);	
			
			$now = get_gmtime();
			$vote = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."vote where is_effect = 1 and begin_time < ".$now." and (end_time = 0 or end_time > ".$now.") order by sort desc limit 1");
			$GLOBALS['tmpl']->assign("vote",$vote);
			$GLOBALS['tmpl']->assign("f_link_data",$f_link_group);
			
			$GLOBALS['tmpl']->assign("show_site_titile",1);
		}
		//lu 马上购买 
		// 广西城建三期
		/**产品发布**********************经常要修改的地方*********************/
		$credit= get_deal_list(1,0,"publish_wait =0 AND deal_status=1 AND cate_id=3  "," id DESC");//左边
		//print_r($credit);exit;
		$chengjian= get_deal_list(1,0,"publish_wait =0 AND deal_status=1 AND cate_id=7  "," id DESC");//中
		
		$wenfeng= get_deal_list(1,0,"publish_wait =0 AND deal_status=1 AND cate_id=8  "," id DESC");//右边
		
			$GLOBALS['tmpl']->assign("chengjian",$chengjian['list'][0]);
			$GLOBALS['tmpl']->assign("credit",$credit['list'][0]);
			$GLOBALS['tmpl']->assign("wenfeng",$wenfeng['list'][0]);
		
		//lu 投资收益排行榜 charts
		$user_arr=$GLOBALS['db']->getAll("select id,user_name from ".DB_PREFIX."user where is_delete = 0  ");
		foreach($user_arr as $ku=>$vu){	
			 $user_invest[]=$GLOBALS['db']->getAll("select money,user_name,deal_id from ".DB_PREFIX."deal_load where user_id = '".$vu['id']."'"); 
			 if(!empty($user_invest[$ku])){
				
				foreach($user_invest as $km=>$vm){
				foreach($vm as $km2=>$vm2){
				
					
				}
					
				}
				
				$vm_num=count($vm);
				if(in_array($vm2['deal_id'],$deal_id)&&$vm2['deal_id']!=0){
							 if($vm_num>0){
									 for($i=0;$i<$vm_num;$i++){
										$charts[$vm2['user_name']]+=$vm[$i]['money'];
										
									 }
							 }	
				}
				 }
			}
			//lu 投标资金大到小排序
			foreach($charts as $kc=>$vc){ 
			$a[]=$vc;
				$num = count($a);
				for($i=0;$i<$num;$i++){
					for($j=0;$j<$num;$j++){
						if($a[$i]>$a[$j]){
							$temp = $a[$i];
							$a[$i]=$a[$j];
							$a[$j]=$temp;	
						}
					}	
				}
			}
			//lu  根据投标资金大到小 	 排序  用户
			$numk = count($a);
			foreach($charts as $kc=>$vc){ 
				for($ki=0;$ki<$numk;$ki++){
					if($vc==$a[$ki]){
					$charts_arr[$ki][]=$vc;
					$charts_arr[$ki][]=$kc;
					}
				}
			}
			//lu 重构数组  只要5个
			$numka = count($charts_arr);
			for($ka=0;$ka<$numka;$ka++){
				$charts_use[$ka]['money']=$charts_arr[$ka][0];
				$charts_use[$ka]['user_name']=cut_str($charts_arr[$ka][1], 1, 0).'***'.cut_str($charts_arr[$ka][1], 1, -1);
			}
			for($kai=1;$kai<=5;$kai++){
				$charts_user[$kai]=$charts_use[$kai-1];
				}
			$GLOBALS['tmpl']->assign("charts_user",$charts_user);

		
		$GLOBALS['tmpl']->display("page/index.html",$cache_id);
	}
}	
?>