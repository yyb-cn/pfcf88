<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class viewpdfModule 
{
	public function index()
	{	
		$cat_id=intval($_REQUEST['cat']);
		switch($cat_id){
		
		case 6 :$GLOBALS['tmpl']->display("page/viewpdf_jialiang.html");//嘉良产品pdf
		break;
		case 5 :$GLOBALS['tmpl']->display("page/viewpdf.html");//文峰pdf
		break;
		case 3 :$GLOBALS['tmpl']->display("page/viewpdf_xdy.html");//信贷赢
		break;	
		case 7 :$GLOBALS['tmpl']->display("page/viewpdf_plane.html");//飞机
		break;	
		default:
  			echo "您的请求不存在，<a href='index.php'>点击返回</a>";
		}
		
		
		
	}
}
?>