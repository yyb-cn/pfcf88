<?php

define(MODULE_NAME,"index");
require APP_ROOT_PATH.'app/Lib/deal.php';
class indexModule extends SiteBaseModule
{
	public function index()
	{	
	//抽奖期间暂时关闭缓存
		//$GLOBALS['tmpl']->caching = true;
		//$GLOBALS['tmpl']->cache_lifetime = 600;  //首页缓存10分钟
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
			
			//最新借款列表
			
			/**++++++++++++++++++++++++经常要修改的地方++++++++++++++++++++++++++++++++++++*/
			/**++++++++++++++++++++++++经常要修改的地方++++++++++++++++++++++++++++++++++++*/
			/**++++++++++++++++++++++++经常要修改的地方++++++++++++++++++++++++++++++++++++*/
			/**++++++++++++++++++++++++经常要修改的地方++++++++++++++++++++++++++++++++++++*/
			$deal_list =  get_deal_list(6,0,"publish_wait =0 AND deal_status in(1,2,4,5) AND cate_id != 13  AND  cate_id != 14 "," id DESC");//在$where中过滤掉cate_id等于特殊标的
			
			foreach($deal_list['list'] as $ke => $vel){
				$deal_list['list'][$ke]['repay_start_time'] = date('Y-m-d',$vel['repay_start_time']);
			}
			$GLOBALS['tmpl']->assign("deal_list",$deal_list['list']);
			//输出公告
			$notice_list = get_notice(0);
			foreach($notice_list as $kkd=>$vxd){
				$notice_list[$kkd]['update_time'] = date('m月d日',$vxd['create_time']);
				}
			$GLOBALS['tmpl']->assign("notice_list",$notice_list);
			// 成交数据
			$deal_data = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal where deal_status in(1,2,4,5) and is_delete=0  ");
			foreach($deal_data as $kd=>$vd){
				// 累计总交易金额
				$tatal_money+=$vd['borrow_amount'];
				//is_delete=0
				$deal_id[]=$vd['id'];
				}
			//已按期还本金额
			$deal_benjin = $GLOBALS['db']->getAll("select borrow_amount from ".DB_PREFIX."deal where deal_status=5 ");
			foreach($deal_benjin as $kbj=>$vbj){
				 	
				$done_money+=$vbj['borrow_amount'];
				}
			// 为用户带来的 收益  

			$deal_success_money=$GLOBALS['db']->getAll("select rate ,repay_time,repay_money,repay_time_type from ".DB_PREFIX."deal  where deal_status=5 ");
			
			//print_r($deal_success_money);exit;
			
			/**七天投资总额**/
			$seven_deal=$GLOBALS['db']->getAll("select money,deal_id,user_id from " .DB_PREFIX."deal_load where deal_id=101  ");//七天的deal_id 是101
			$seven_money_totle=0;
			$peo=array();
			foreach($seven_deal as  $k=>$v)
			{
				$seven_money_totle+=$v['money'];
				$peo[]=$v['user_id'];
			}
			//共有多少人投资
			$seven_nums=count(array_unique($peo));//数组中值不相同的个数  
			$GLOBALS['tmpl']->assign("seven_nums",$seven_nums);//投资人数 
			$GLOBALS['tmpl']->assign("seven_money_totle",$seven_money_totle);   //投资金额 
			/**七天投资总额**/
			$income_totle=0;
			foreach($deal_success_money as $k=>$v){
				if($v['repay_time_type']){//月份
				$income_totle+=$v['repay_money']*$v['rate']*$v['repay_time']/12*0.01;//计算总利率
				}
				else//天数
				{
					$income_totle+=$v['repay_money']*$v['rate']*$v['repay_time']/365*0.01;//计算总利率
				}
				
			}
			
			$success_deal['tatal_money']="￥".number_format($tatal_money);
			$success_deal['done_money']="￥".number_format($done_money);
			$success_deal['income']="￥".number_format($income_totle);//总收益
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
		
		//获取分类
		$cate=$GLOBALS['db']->getAll("select id,sort from ".DB_PREFIX."deal_cate order by sort desc limit 3");
		//print_r($cate);exit;
		//echo $cate[2]['id'];exit;
		$credit= get_deal_list(1,0,"publish_wait =0  AND cate_id=".$cate[0]['id']," id DESC");//左边
		$credit['list'][0]['longtime']=$credit['list'][0]['repay_time_type']?$credit['list'][0]['repay_time']*30:$credit['list'][0]['repay_time'];
		$credit['list'][0]['rate']=round($credit['list'][0]['rate'],1);
		
		$chengjian= get_deal_list(1,0,"publish_wait =0 AND  cate_id= ".$cate[1]['id']," id DESC");//中
		$chengjian['list'][0]['longtime']=$chengjian['list'][0]['repay_time_type']?$chengjian['list'][0]['repay_time']*30:$chengjian['list'][0]['repay_time'];
		$chengjian['list'][0]['rate']=round($chengjian['list'][0]['rate'],1);
		
		$wenfeng= get_deal_list(1,0,"publish_wait =0 AND cate_id=  ".$cate[2]['id']," id DESC");//右边
		$wenfeng['list'][0]['longtime']=$wenfeng['list'][0]['repay_time_type']?$wenfeng['list'][0]['repay_time']*30:$wenfeng['list'][0]['repay_time'];
		$wenfeng['list'][0]['rate']=round($wenfeng['list'][0]['rate'],1);
		
			$GLOBALS['tmpl']->assign("credit",$credit['list'][0]);
		
			$GLOBALS['tmpl']->assign("chengjian",$chengjian['list'][0]);
			
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
		//抽奖环节
		//1.获奖名单  ,最新的10条
		
			$award_log = $GLOBALS['db']->getAll("select a.*,u.user_name,p.name as prize_name from ".DB_PREFIX."award_log as a left join ".DB_PREFIX."user as u on a.user_id=u.id   left join ".DB_PREFIX."prize as p on a.prize_id=p.id order by a.log_time desc limit 5");
			//姓名加***
			foreach($award_log as $k=>$v){
			
			$award_log[$k]['user_name']=cut_str($v['user_name'], 1, 0).'***'.cut_str($v['user_name'], 1, -1);
			
			}
			
			$GLOBALS['tmpl']->assign("award_log",$award_log);
			
			//2.当前会员抽奖次数
		$user_id=$GLOBALS['user_info']['id'];
		if($user_id){
		$user_award = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."lottery where uid=".$user_id);//改会员抽奖次数
		//var_dump($user_award);exit;
		$award_sec=$user_award['draw_sec']; 
		
		$GLOBALS['tmpl']->assign("award_sec",$award_sec);
		}
		$GLOBALS['tmpl']->display("page/index.html",$cache_id);
	}

}	
?>