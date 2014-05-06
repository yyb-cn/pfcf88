<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

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
			//最新借款列表
			$deal_list =  get_deal_list(6,0,"publish_wait =0 AND deal_status in(1,2,4,5) "," id DESC");
			$GLOBALS['tmpl']->assign("deal_list",$deal_list['list']);
						
			//输出公告
			$notice_list = get_notice(0);
			$GLOBALS['tmpl']->assign("notice_list",$notice_list);	
			
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
		
		$GLOBALS['tmpl']->display("page/index.html",$cache_id);
	}
}	
?>