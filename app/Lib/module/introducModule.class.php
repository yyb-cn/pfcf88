<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class introducModule extends SiteBaseModule
{
	public function index()
	{
		
		$GLOBALS['tmpl']->display("page/wenfeng.html",$cache_id);
	}
}
?>