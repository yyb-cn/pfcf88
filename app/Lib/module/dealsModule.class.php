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
		
		//输出投标列表
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
		
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
				$orderby = "update_time DESC , sort DESC , id DESC";
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
		
		$result = get_deal_list($limit,$n_cate_id,$condition,$orderby);
		$GLOBALS['tmpl']->assign("deal_list",$result['list']);
		$GLOBALS['tmpl']->assign("total_money",$total_money);
		
		
		
		$page_args['cid'] =  $cate_id;
		$page_args['keywords'] =  $keywords;
		$page_args['level'] =  $level;
		$page_args['interest'] =  $interest;
		$page_args['months'] =  $months;
		$page_args['lefttime'] =  $lefttime;
		//lu 产品分页错误修改
		foreach($page_args as $k=>$v){
			if($v!=0){
				$page_string.='&'.$k.'='.$v;
				}
			}
		$page = new Page($result['count'],app_conf("PAGE_SIZE"),$page_string);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("page_title",$page_title . $GLOBALS['lang']['FINANCIAL_MANAGEMENT']);
				
		$GLOBALS['tmpl']->assign("cate_id",$cate_id);
		$GLOBALS['tmpl']->assign("keywords",$keywords);
		$GLOBALS['tmpl']->assign("deal_cate_list",$deal_cates);
		$GLOBALS['tmpl']->display("page/deals.html");
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
