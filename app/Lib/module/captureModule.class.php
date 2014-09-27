<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------
/**
class captureModule extends SiteBaseModule
{
	public function bank()
	{
		//1.银行理财产品
			 include APP_ROOT_PATH.'phpQuery/phpQuery/phpQuery.php';
			 phpQuery::newDocumentFile('http://www.qianxs.com/mrMoney/portal/Product/prodSearchPageV3/sort=return_rate/pageIdx=1.html');
			 //获取总页数 
			 //银行理财产品总款数
			 $qxs_nums=pq("#search_product_total")->text();
			  $GLOBALS['tmpl']->assign("qxs_nums",$qxs_nums);	
			 //每页显示产品数量 $qxs_n
			 $qxs_n=pq("#smart-paginator span:eq(3) b script")->text();
			 $start=strpos($qxs_n,"prodCount)<")+11;
			 $end=strpos($qxs_n,"){");
			 $qxs_n=substr($qxs_n,$start,$end-$start);
			 //页码总数
             $qxs_page=ceil($qxs_nums/$qxs_n);

			 for($i=1;$i<=$qxs_page;$i++){
			//采集数据的网页	 
			 phpQuery::newDocumentFile("http://www.qianxs.com/mrMoney/portal/Product/prodSearchPageV3/sort=return_rate/pageIdx=".$i.".html");	 
			 //获取发行银行名称	
			  $qxs_bank=pq("#cardList div div h2 strong a");
			  	 foreach($qxs_bank as $a_bank){
				   $qxs_bank_arr[]=pq($a_bank)->text(); 
				
			} 
			 foreach($qxs_bank_arr as $k_b => $v_b){
				 $start=strpos($qxs_bank_arr[$k_b],'"')+1;
			     $end=strpos($qxs_bank_arr[$k_b],'")');
				 $qxs_bank_arr[$k_b]=substr($qxs_bank_arr[$k_b],$start,$end-$start);
				 
				  $qxs_name=pq(".sel_bank_cont ul li input[value='".$qxs_bank_arr[$k_b]."']")->parent();	 
				 foreach($qxs_name as $a_n){
				   $qxs_bank_name[]=pq($a_n)->text(); 
				   
				} 
			}
			
			 $qxs_arr['qxs_bank_name']=$qxs_bank_name;
			 
				
			  //获取理财产品名称  
			  $qxs_name=pq("#cardList div div h2 strong span"); 
			  foreach($qxs_name as $a){
				   $qxs_name_array[]=pq($a)->text(); 
				} 

				
				$qxs_arr['qxs_name_array']=$qxs_name_array;
				//获取投资期限
				$qxs_time=pq("#cardList div a div div table tr td span strong[name='invest_cycle']");
				foreach($qxs_time as $a_time){
				   $qxs_time_arr[]=pq($a_time)->text(); 
				} 
				$qxs_arr['qxs_time_arr']=$qxs_time_arr;
				//获取预期收益率
				$qxs_yield =pq("#cardList div a div div table tr td span[name='return_rate']");
				foreach($qxs_yield  as $a_yield){
				   $qxs_yield_arr[]=pq($a_yield)->text(); 
				}
				$qxs_arr['qxs_yield_arr']=$qxs_yield_arr; 	
				//获取是银行活期收益倍数  multiple 
				$qxs_multiple =pq("#cardList div a div div table tr td span strong[style='color:#f59027']");
				foreach($qxs_multiple  as $a_multiple){
				   $qxs_multiple_arr[]=pq($a_multiple)->text(); 
				} 	
				$qxs_arr['qxs_multiple_arr']=$qxs_multiple_arr;
			 }
			 //重构数组
			$qxs_array=array();
			foreach($qxs_arr as $key=>$val){
				
				foreach($val as $key2=>$val2){
					$qxs_array[$key2][$key]=$val2;
					}
   
				}
				
		   $GLOBALS['tmpl']->assign("qxs_array",$qxs_array);
		   $GLOBALS['tmpl']->display("page/bank.html");		
		
	}
	//2.互联网金融产品
	public function internet()
	{		
		
		   include APP_ROOT_PATH.'phpQuery/phpQuery/phpQuery.php';
		   phpQuery::newDocumentFile('http://www.qianxs.com/mrMoney/portal/Internet/iProdSearchPage;jsessionid=323AB63C9DB9045274AA69057555533C/sort=week_return_rate/pageIdx=1.html');
		     //互联网金融产品总数
			 $inter_nums=pq("#search_product_total")->text();
			 $GLOBALS['tmpl']->assign("inter_nums",$inter_nums);	
			  //每页显示产品数量 $qxs_n
			 $inter_str=pq("#smart-paginator span:eq(3) b script")->text();
			 $start_inter=strpos($inter_str,"prodCount)<")+11;
			 $end_inter=strpos($inter_str,"){");
			 $inter_n=substr($inter_str,$start_inter,$end_inter-$start_inter);
			  //页码总数
             $inter_page=ceil($inter_nums/$inter_n); 
			 for($i=1;$i<=$inter_page;$i++)
			 {
								//采集数据的网页	 
								 phpQuery::newDocumentFile("http://www.qianxs.com/mrMoney/portal/Internet/iProdSearchPage;jsessionid=323AB63C9DB9045274AA69057555533C/sort=week_return_rate/pageIdx=".$i.".html'");
								//获取互联网金融公司英文字符串
								  $qxs_inter=pq("#cardList div div h2 strong a");
										 foreach($qxs_inter as $a_inter)
										 {
												$inter_name_str[]=pq($a_inter)->text(); 
							   
										}
								 foreach($inter_name_str as $k_inter => $v_inter)
								 {
									 $start_int=strpos($inter_name_str[$k_inter],'"')+1;
									 $end_int=strpos($inter_name_str[$k_inter],'")');
									 $inter_name_str[$k_inter]=substr($inter_name_str[$k_inter],$start_int,$end_int-$start_int);
									 //互联网金融公司中文名称
									 if($inter_name_str[$k_inter]=='PINGAN')
									 {
										 $inter_company_name[$k_inter]='平安';
									 }
									 else {
										 
									  $inter_name_objec=pq(".sel_bank_cont ul li input[value='".$inter_name_str[$k_inter]."']")->parent();	
												   foreach($inter_name_objec as $a_inter)
												   {
													 
												   $inter_company_name[$k_inter]=pq($a_inter)->find('label')->text(); 
												   
													} 
										  }
								 }
					 	
					 $inter_arr['inter_company_name']=$inter_company_name; 
					 //互联网金融理财产品名称
					 $inter_prod=pq("#cardList div div h2 strong span");
					   foreach($inter_prod as $a_prod)
						   {
							 
						   $inter_prod_name[]=pq($a_prod)->text(); 
						   
							} 
							
					$inter_arr['inter_prod_name']=$inter_prod_name;
					//万份收益
					$inter_yield=pq("#cardList div a div div table tr td[style='width:210px'] span strong span[name='return_rate']");
					 foreach($inter_yield as $a_yield)
						   {
							 
						   $inter_yield_num[]=pq($a_yield)->text(); 
						   
							}
					$inter_arr['inter_yield_num']=$inter_yield_num;
							
					//7日年化收益
					$inter_7day=pq("#cardList div a div div table tr td[style='width:205px'] span strong span[name='return_rate']");
					foreach($inter_7day as $a_7day)
						   {
							 
						   $inter_7day_num[]=pq($a_7day)->text(); 
						   
							}
					$inter_arr['inter_7day_num']=$inter_7day_num;
					//是银行活期收益 的倍数
					$inter_mul=pq("#cardList div a div div table tr td span strong[style='color:#f59027']");	
					foreach($inter_mul as $a_mul)
						   {
							 
						   $inter_mul_num[]=pq($a_mul)->text(); 
						   
							}
					$inter_arr['inter_mul_num']=$inter_mul_num;	 
					 		
					 
			 }
			  //重构数组
			$inter_array=array();
			foreach($inter_arr as $key=>$val){
				
				foreach($val as $key2=>$val2){
					$inter_array[$key2][$key]=$val2;
					}
   
				}
			
		   $GLOBALS['tmpl']->assign("inter_array",$inter_array);
		    $GLOBALS['tmpl']->display("page/internet.html");		
	}
	//基金
	public function index()
	{		
		//3.基金 cardList
		 include APP_ROOT_PATH.'phpQuery/phpQuery/phpQuery.php';
		  phpQuery::newDocumentFile('http://www.qianxs.com/mrMoney/portal/Fund/fundSearchPageV3/fund_id=none/sort=week_return/product_type=none/pageIdx=1.html');
		     //基金 金融产品总数
			 $fund_nums=pq("#search_product_num")->text();
			  $GLOBALS['tmpl']->assign("fund_nums",$fund_nums);	
			  //每页显示产品数量 $qxs_n
			 $fund_str=pq("#smart-paginator span:eq(3) b script")->text();
			 $start_fund=strpos($fund_str,"prodCount)<")+11;
			 $end_fund=strpos($fund_str,"){");
			 $fund_n=substr($fund_str,$start_fund,$end_fund-$start_fund);
			 //页码总数
             $fund_page=ceil($fund_nums/$fund_n); 
		   for($i=1;$i<=$fund_page;$i++)
		  {
			//采集数据的网页	 
			 phpQuery::newDocumentFile("http://www.qianxs.com/mrMoney/portal/Fund/fundSearchPageV3/fund_id=none/sort=week_return/product_type=none/pageIdx=".$i.".html");
			//获取互联网金融公司英文字符串
			  $qxs_fund=pq("#cardList div div ul li div strong span");
			 
					 foreach($qxs_fund as $a_fund)
					 {
							$fund_name_str[]=pq($a_fund)->text(); 
		   
					} 
				 foreach($fund_name_str as $k_fund => $v_fund)
				 {
					 $start_fund=strpos($fund_name_str[$k_fund],'"')+1;
					 $end_fund=strpos($fund_name_str[$k_fund],'")');
					
					 $fund_name_str[$k_fund]=substr($fund_name_str[$k_fund],$start_fund,$end_fund-$start_fund);
			
			  //基金机构中文名称
				 $fund_name_objec=pq("input[value='".$fund_name_str[$k_fund]."']")->parent();	
					   foreach($fund_name_objec as $a_fund)
					   {
						 
					   $fund_company_name[]=pq($a_fund)->find('label')->text(); 
					   
						} 

					 
				 }
				 $fund_arr['fund_company_name']=$fund_company_name;  
			// 基金理财产品名称
				$fund_product=pq("#cardList div div ul li div a"); 
				foreach($fund_product as $a_prod)
				{
					$fund_product_name[]=pq($a_prod)->text(); 
					}
					$fund_arr['fund_product_name']=$fund_product_name; 
					
			//基金代码
			$product_code=pq("#cardList div div ul li div ol li span[name='product_code']"); 
				foreach($product_code as $a_code)
				{
					$fund_product_code[]=pq($a_code)->text(); 
					}
					$fund_arr['fund_product_code']=$fund_product_code; 
					
			//基金类型 
			$product_type=pq("#cardList div div ul li div ol li span[name='product_type']"); 
				foreach($product_type as $a_type)
				{
					$fund_product_type[]=pq($a_type)->text(); 
					}
					$fund_arr['fund_product_type']=$fund_product_type; 
					
			//成立日期 
			$establish_date=pq("#cardList div div ul li div ol li span[name='establish_date'] script"); 
			foreach($establish_date as $a_estab)
				{
					$fund_establish_date[]=pq($a_estab)->text(); 
					}
							
			
				  
			//今年以来回报率
			$year_return=pq("#cardList div div ul li div ol li span[name='year_return']"); 
				foreach($year_return as $a_ret)
				{
					$fund_year_return[]=pq($a_ret)->text(); 
					}
					 $fund_arr['fund_year_return']=$fund_year_return; 	
				  
			//成立以来回报率
			$establish_return=pq("#cardList div div ul li div ol li span[name='establish_return']"); 
				foreach($establish_return as $a_est)
				{
					$fund_establish_return[]=pq($a_est)->text(); 
					}
					$fund_arr['fund_establish_return']=$fund_establish_return; 	
				
			//累计净值
			$cumulative_value=pq("#cardList div div ul li div ol li span[name='cumulative_value']"); 
				foreach($cumulative_value as $a_cumu)
				{
					$fund_cumulative_value[]=pq($a_cumu)->text(); 
					}
					$fund_arr['fund_cumulative_value']=$fund_cumulative_value; 	
				
			// 最新净值 
			$net_value=pq("#cardList div div ul li span[name='net_value'] strong"); 
			foreach($net_value as $a_net)
				{
					$fund_net_value[]=pq($a_net)->text(); 
					}
					$fund_arr['fund_net_value']=$fund_net_value;
			
			// 日期 
			$net_date=pq("#cardList div div ul li span[name='net_date'] b script"); 
			foreach($net_date as $a_net)
				{
					$fund_net_date[]=pq($a_net)->text(); 
					}
					
			
			
				 
				
			//最近一周回报率 week_return
			$week_return=pq("#cardList div div ul li span[name='week_return'] strong"); 
			foreach($week_return as $a_week)
				{
					$fund_week_return[]=pq($a_week)->text(); 
					}
			$fund_arr['fund_week_return']=$fund_week_return;
				  
				 
		  }
		  //成立日期 截取
		   foreach($fund_establish_date as $k_estab => $v_estab)
				 {
					 $start_estab=strpos($fund_establish_date[$k_estab],'establish_date="')+16;
					 $end_estab=strpos($fund_establish_date[$k_estab],'";');
					
					 $fund_establish_date[$k_estab]=substr($fund_establish_date[$k_estab],$start_estab,$end_estab-$start_estab);
				 }
				 $fund_arr['fund_establish_date']=$fund_establish_date; 
		//日期 截取
		 foreach($fund_net_date as $k_net => $v_net)
				 {
					 $start_net=strpos($fund_net_date[$k_net],'net_date="')+10;
					 $end_net=strpos($fund_net_date[$k_net],'";');
					
					 $fund_net_date[$k_net]=substr($fund_net_date[$k_net],$start_net,$end_net-$start_net);
				 }		 
			$fund_arr['fund_net_date']=$fund_net_date;
		 
		 
		   //重构数组
			$fund_array=array();
			foreach($fund_arr as $key=>$val){
				
				foreach($val as $key2=>$val2){
					$fund_array[$key2][$key]=$val2;
					}
   
				}
				
			//print_r($fund_array);exit;	
		 $GLOBALS['tmpl']->assign("fund_array",$fund_array);
		  $GLOBALS['tmpl']->display("page/fund.html");		
	}
	

}
?>
*/