<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class viewpdfModule extends SiteBaseModule
{
	public function index()
	{
		echo '这是查看pdf的控制器';exit;
		$GLOBALS['tmpl']->display("page/mobile_index.html",$cache_id);
	}
}
?>