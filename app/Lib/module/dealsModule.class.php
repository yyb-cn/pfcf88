<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/deal.php';
class dealsModule extends SiteBaseModule
{
	public function index(){
	
	//判断是否为ajax提交
	if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
	{	
		
				
					//把提交上来的条件保留到session中
					//这个是condition
					if($_POST['type']==1)
					{
				if($_POST['name']=='rate')
					 {
							$_SESSION['condition'][$_POST['name']]=' '."AND " . $_POST['name'] .' >= '.   $_POST['value'];
					 }
				elseif($_POST['name']=='repay_time')
					{
						 	$_SESSION['condition'][$_POST['name']]=' '." AND " . $_POST['name'] .' <= '.   $_POST['value'];
						}
					}
					//这个是order
					if($_POST['type']==2)
					{
						
						$_SESSION['order']=''.$_POST['name'] .' '.$_POST['check'];
						
						//echo json_encode($_SESSION['order']);exit;
						}
						
					//echo json_encode($_SESSION['condition']['repay_time']);exit;	
					
				require APP_ROOT_PATH.'app/Lib/page.php';
				$level_list = load_auto_cache("level");
				$GLOBALS['tmpl']->assign("level_list",$level_list['list']);
				
				
				//$cate_id 是分类id 0表示所有 ，1表示抵押信用类，2表示黄金现货类，3表示信用托管类，-1表示最近成交
				if(trim($_REQUEST['cid'])=="last"){
					$cate_id = "-1";
					$page_title = $GLOBALS['lang']['LAST_SUCCESS_DEALS']." - ";
				}
				else{
					$cate_id = intval($_REQUEST['cid']);
				}
				
				if($cate_id == 0){
					$page_title = $GLOBALS['lang']['ALL_DEALS']." - ";
				}
				//echo $cate_id;exit;
				$keywords = trim(htmlspecialchars($_REQUEST['keywords']));
				
				$GLOBALS['tmpl']->assign("keywords",$keywords);
				
				$level = intval($_REQUEST['level']);
				
				$GLOBALS['tmpl']->assign("level",$level);
				
				$interest = intval($_REQUEST['interest']);
				$GLOBALS['tmpl']->assign("interest",$interest);
				
				$months = intval($_REQUEST['months']);
				$GLOBALS['tmpl']->assign("months",$months);
				
				 $lefttime = intval($_REQUEST['lefttime']);
				$GLOBALS['tmpl']->assign("lefttime",$lefttime);
				
				//输出分类
				$deal_cates_db = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 order by sort desc");
				
				$deal_cates = array();
				foreach($deal_cates_db as $k=>$v)
				{		
					if($cate_id==$v['id']){
						$v['current'] = 1;
						$page_title = $v['name']." - ";
					}
					$v['url'] = url("index","deals",array("id"=>$v['id']));
					$deal_cates[] = $v;
				}
				//print_r($dael_cates);exit;
				//输出投标列表
				$page = intval($_REQUEST['p']);
				if($page==0)
					$page = 1;
				$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
				//app_conf("DEAL_PAGE_SIZE")==10
				$n_cate_id = 0;
				$condition = " publish_wait = 0 ";
				$orderby = "";
				if($cate_id > 0){
					$n_cate_id = $cate_id;
					$condition .= "AND deal_status in(0,1)";
					$field = es_cookie::get("shop_sort_field"); //名字
					$field_sort = es_cookie::get("shop_sort_type"); //升降
					if($field && $field_sort)
						$orderby = "$field $field_sort ,deal_status desc , sort DESC,id DESC";
					else
						$orderby = "update_time DESC ,sort DESC,id DESC";
					$total_money = $GLOBALS['db']->getOne("SELECT sum(borrow_amount) FROM ".DB_PREFIX."deal WHERE cate_id=$cate_id AND deal_status in(2,4)");
				}
				elseif ($cate_id == 0){
					$n_cate_id = 0;
					$condition .= "AND deal_status in(0,1,2,4,5)";
					$field = es_cookie::get("shop_sort_field"); 
					$field_sort = es_cookie::get("shop_sort_type"); 
					if($field && $field_sort)
						$orderby = "$field $field_sort ,sort DESC,id DESC";

					else
						$orderby = "update_time DESC , sort DESC , id DESC";
					$total_money = $GLOBALS['db']->getOne("SELECT sum(borrow_amount) FROM ".DB_PREFIX."deal WHERE deal_status in(2,4) ");
				}
				elseif ($cate_id == "-1"){//最近产品
					$n_cate_id = 0;
					$condition .= "AND deal_status in(2,4) ";
					$orderby = "success_time DESC,sort DESC,id DESC";
				}
				
				if($keywords){
					$kw_unicode = str_to_unicode_string($keywords);
					$condition .=" and (match(name_match,deal_cate_match,tag_match,type_match) against('".$kw_unicode."' IN BOOLEAN MODE))";			
				}
				
				if($level > 0){
					$point  = $level_list['point'][$level];
					$condition .= " AND user_id in(SELECT u.id FROM ".DB_PREFIX."user u LEFT JOIN ".DB_PREFIX."user_level ul ON ul.id=u.level_id WHERE ul.point >= $point)";
				}
				
				if($interest > 0){
					$condition .= " AND rate >= ".$interest;
				}
				
				if($months > 0){
					if($months==12)
						$condition .= " AND repay_time <= ".$months;
					elseif($months==18)
						$condition .= " AND repay_time >= ".$months;
				}
				
				if($lefttime > 0){//剩余时间
					$condition .= " AND (start_time + enddate*24*3600 - ".get_gmtime().") <= ".$lefttime*24*3600;
				}
				
				//$order.=
				//$condition.="AND repay_time <=6";
				//吧提交上来的东西编译。
				//如果有两个条件
				if(!empty($_SESSION['condition']['rate']))
						{
						$condition.= $_SESSION['condition']['rate'] ;}
				if(!empty($_SESSION['condition']['repay_time']))			
						{
						$condition.= $_SESSION['condition']['repay_time'] ;}
				if(!empty($_SESSION['order']))
					{
					
					$orderby=$_SESSION['order'];
					}
						
					
			//echo 1;exit;
				//echo json_encode($orderby);exit;
				// echo  $condtion=json_encode($condition);exit;
				//默认的orderby 是 update_time DESC , sort DESC , id DESC
				 $result = get_deal_list($limit,$n_cate_id,$condition,$orderby);
				//echo json_encode($result);exit;
				 $page_args['cid'] =  $cate_id;
				$page_args['keywords'] =  $keywords;
				$page_args['level'] =  $level;
				$page_args['interest'] =  $interest;
				$page_args['months'] =  $months;
				$page_args['lefttime'] =  $lefttime;
			//lu 产品分页错误修改
			//print_r($page_args);exit;
				foreach($page_args as $k=>$v){
					if($v!=0){
						$page_string.='&'.$k.'='.$v;
						}
					}
				$page = new Page($result['count'],app_conf("PAGE_SIZE"),$page_string);   //初始化分页对象 		
				$p  =  $page->show();
				$arr=array();
				$arr=array('result'=>$result['list'],'page'=>$p);
				echo json_encode ($arr);
				
				//把条件存入session给多条件查询使用
				//$_SESSION[md5('condition')][md5('repay_time')]=" AND repay_time <=".$_POST['repay_time']; //期限
				//$_SESSION[md5('condition')][md5('rate')]=" AND repay_time <=".$_POST['repay_time']; //利率
				//rate 和repay_time
				
			
				
				//把结果存入session给刷新分页使用,错，存条件即可。
				//$_SESSION[md5('result')]=$result;
			}
	
	//不是AJAX提交
	else {
	
	
		require APP_ROOT_PATH.'app/Lib/page.php';
		$level_list = load_auto_cache("level");
		$GLOBALS['tmpl']->assign("level_list",$level_list['list']);
		
		if(trim($_REQUEST['cid'])=="last"){
			$cate_id = "-1";
			$page_title = $GLOBALS['lang']['LAST_SUCCESS_DEALS']." - ";
		}
		else{
			$cate_id = intval($_REQUEST['cid']);
		}
		
		if($cate_id == 0){
			$page_title = $GLOBALS['lang']['ALL_DEALS']." - ";
		}
		//echo $cate_id;exit;
		$keywords = trim(htmlspecialchars($_REQUEST['keywords']));
		
		$GLOBALS['tmpl']->assign("keywords",$keywords);
		
		$level = intval($_REQUEST['level']);
		
		$GLOBALS['tmpl']->assign("level",$level);
		
		$interest = intval($_REQUEST['interest']);
		$GLOBALS['tmpl']->assign("interest",$interest);
		
		$months = intval($_REQUEST['months']);
		$GLOBALS['tmpl']->assign("months",$months);
		
		 $lefttime = intval($_REQUEST['lefttime']);
		$GLOBALS['tmpl']->assign("lefttime",$lefttime);
		
		//输出分类
		$deal_cates_db = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 order by sort desc");
		
		$deal_cates = array();
		foreach($deal_cates_db as $k=>$v)
		{		
			if($cate_id==$v['id']){
				$v['current'] = 1;
				$page_title = $v['name']." - ";
			}
			$v['url'] = url("index","deals",array("id"=>$v['id']));
			$deal_cates[] = $v;
		}
		//print_r($dael_cates);exit;
		//输出投标列表
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
		//app_conf("DEAL_PAGE_SIZE")==10
		$n_cate_id = 0;
		$condition = " publish_wait = 0 ";
		$orderby = "";
		if($cate_id > 0){
			$n_cate_id = $cate_id;
			$condition .= "AND deal_status in(0,1)";
			$field = es_cookie::get("shop_sort_field"); 
			$field_sort = es_cookie::get("shop_sort_type"); 
			if($field && $field_sort)
				$orderby = "$field $field_sort ,deal_status desc , sort DESC,id DESC";
			else
				$orderby = "update_time DESC ,sort DESC,id DESC";
			$total_money = $GLOBALS['db']->getOne("SELECT sum(borrow_amount) FROM ".DB_PREFIX."deal WHERE cate_id=$cate_id AND deal_status in(2,4)");
		}
		elseif ($cate_id == 0){
			$n_cate_id = 0;
			$condition .= "AND deal_status in(0,1,2,4,5)";
			$field = es_cookie::get("shop_sort_field"); 
			$field_sort = es_cookie::get("shop_sort_type"); 
			if($field && $field_sort)
				$orderby = "$field $field_sort ,sort DESC,id DESC";
			else
				$orderby = "create_time DESC,sort DESC,id DESC";
			$total_money = $GLOBALS['db']->getOne("SELECT sum(borrow_amount) FROM ".DB_PREFIX."deal WHERE deal_status in(2,4) ");
		}
		elseif ($cate_id == "-1"){
			$n_cate_id = 0;
			$condition .= "AND deal_status in(2,4) ";
			$orderby = "success_time DESC,sort DESC,id DESC";
		}
		
		if($keywords){
			$kw_unicode = str_to_unicode_string($keywords);
			$condition .=" and (match(name_match,deal_cate_match,tag_match,type_match) against('".$kw_unicode."' IN BOOLEAN MODE))";			
		}
		
		if($level > 0){
			$point  = $level_list['point'][$level];
			$condition .= " AND user_id in(SELECT u.id FROM ".DB_PREFIX."user u LEFT JOIN ".DB_PREFIX."user_level ul ON ul.id=u.level_id WHERE ul.point >= $point)";
		}
		
		if($interest > 0){
			$condition .= " AND rate >= ".$interest;
		}
		
		if($months > 0){
			if($months==12)
				$condition .= " AND repay_time <= ".$months;
			elseif($months==18)
				$condition .= " AND repay_time >= ".$months;
		}
		
		if($lefttime > 0){
			$condition .= " AND (start_time + enddate*24*3600 - ".get_gmtime().") <= ".$lefttime*24*3600;
		}
		
		 if(isset( $_SESSION['condition']))
		{
			unset($_SESSION['condition']);
		} 
		if(isset($_SESSION['order']))
		{
			unset($_SESSION['order']);
		}
		$result = get_deal_list($limit,$n_cate_id,$condition,$orderby);
		//print_r($result);exit;
		
		
		$GLOBALS['tmpl']->assign("deal_list",$result['list']);
		$GLOBALS['tmpl']->assign("total_money",$total_money);
		
		
		
		$page_args['cid'] =  $cate_id;
		$page_args['keywords'] =  $keywords;
		$page_args['level'] =  $level;
		$page_args['interest'] =  $interest;
		$page_args['months'] =  $months;
		$page_args['lefttime'] =  $lefttime;
		//lu 产品分页错误修改
		//print_r($page_args);exit;
		foreach($page_args as $k=>$v){
			if($v!=0){
				$page_string.='&'.$k.'='.$v;
				}
			}
		$page = new Page($result['count'],app_conf("PAGE_SIZE"),$page_string);   //初始化分页对象 		
		$p  =  $page->show();
		//var_dump($p);exit;
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("page_title",$page_title . $GLOBALS['lang']['FINANCIAL_MANAGEMENT']);
				
		$GLOBALS['tmpl']->assign("cate_id",$cate_id);
		$GLOBALS['tmpl']->assign("keywords",$keywords);
		$GLOBALS['tmpl']->assign("deal_cate_list",$deal_cates);
		$GLOBALS['tmpl']->display("page/deals.html");
		}
}
	
	
	
	public function about(){
		$GLOBALS['tmpl']->caching = true;
		$GLOBALS['tmpl']->cache_lifetime = 6000;  //首页缓存10分钟
		$name = trim($_REQUEST['u']) == "" ? "financing" : trim($_REQUEST['u']);
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$name);	
		if (!$GLOBALS['tmpl']->is_cached("page/deals_about.html", $cache_id))
		{	
			$info = get_article_buy_uname($name);
			$info['content']=$GLOBALS['tmpl']->fetch("str:".$info['content']);
			$GLOBALS['tmpl']->assign("info",$info);
			
			$about_list = get_article_list(20,7,"","id ASC",true);
			
			$GLOBALS['tmpl']->assign("about_list",$about_list['list']);
			
			$seo_title = $info['seo_title']!=''?$info['seo_title']:$info['title'];
			$GLOBALS['tmpl']->assign("page_title",$seo_title);
			$seo_keyword = $info['seo_keyword']!=''?$info['seo_keyword']:$info['title'];
			$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
			$seo_description = $info['seo_description']!=''?$info['seo_description']:$info['title'];
			$GLOBALS['tmpl']->assign("page_description",$seo_description.",");
		}
		$GLOBALS['tmpl']->display("page/deals_about.html",$cache_id);
	}
}
?>
