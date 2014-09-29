<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';
class dayModule extends SiteBaseModule
{
	public function index()
	{	
	
	
	
		require_once 'Mobile_Detect.php';
		$detect = new Mobile_Detect;
		$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
		
		
		
		$data['time']=time();
	
		$data['yn']=rand(10000000,99999999);
		
		$time=date("H:i",$data['time']);
		$GLOBALS['tmpl']->assign("time",$time);
		$GLOBALS['tmpl']->assign("yn",$data['yn']);
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."day",$data,"INSERT","","SILENT");
	
		if($deviceType=='computer')
		{
				
			$GLOBALS['tmpl']->display("page/day/day_index.html");//浏览器端
		}
		else{
		$GLOBALS['tmpl']->display("page/day/mobile/day_mobile_index.html");//手机端
	
			}
	
		
		
		
		
	}
	public function view_list()
	{	
	
		
		$day_list=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."day" );
		//var_dump($day_list);
		echo  '时间&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;序列';
		echo '<hr >';
		foreach($day_list as $k=>$v)
		{
			$v['time']=date("Y-m-d H:i:s",$v['time']);
			
			echo $v['time'] .'&nbsp;&nbsp;&nbsp;'.$v['yn'];
			echo '<hr >';
			
		}
		
		
		
	}
	
	
	
	
	
}
?>