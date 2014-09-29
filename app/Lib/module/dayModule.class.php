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
	
		$data['time']=time();
	
		$data['yn']=rand(10000000,99999999);
		
		$time=date("H:i",$data['time']);
		$GLOBALS['tmpl']->assign("time",$time);
		$GLOBALS['tmpl']->assign("yn",$data['yn']);
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."day",$data,"INSERT","","SILENT");
	
		if($this->check_wap())
		{
				$GLOBALS['tmpl']->display("page/day/mobile/day_mobile_index.html");//手机端
		
		}
		else{
		$GLOBALS['tmpl']->display("page/day/day_index.html");//浏览器端
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
	
	
	function check_wap()
{
if (strpos(strtoupper($_SERVER['HTTP_ACCEPT']),"VND.WAP.WML") > 0)
{
// Check whether the browser/gateway says it accepts WML.
	$br = "WML";
}
else
{
		$browser=substr(trim($_SERVER['HTTP_USER_AGENT']),0,4);
		if ($browser=="Noki" || // Nokia phones and emulators
		$browser=="Eric" || // Ericsson WAP phones and emulators
		$browser=="WapI" || // Ericsson WapIDE 2.0
		$browser=="MC21" || // Ericsson MC218
		$browser=="AUR" || // Ericsson R320
		$browser=="R380" || // Ericsson R380
		$browser=="UP.B" || // UP.Browser
		$browser=="WinW" || // WinWAP browser
		$browser=="UPG1" || // UP.SDK 4.0
		$browser=="upsi" || // another kind of UP.Browser ??
		$browser=="QWAP" || // unknown QWAPPER browser
		$browser=="Jigs" || // unknown JigSaw browser
		$browser=="Java" || // unknown Java based browser
		$browser=="Alca" || // unknown Alcatel-BE3 browser (UP based?)
		$browser=="MITS" || // unknown Mitsubishi browser
		$browser=="MOT-" || // unknown browser (UP based?)
		$browser=="My S" ||// unknown Ericsson devkit browser ?
		$browser=="WAPJ" || // Virtual WAPJAG www.wapjag.de
		$browser=="fetc" || // fetchpage.cgi Perl script from www.wapcab.de
		$browser=="ALAV" || // yet another unknown UP based browser ?
		$browser=="Wapa" || // another unknown browser (Web based "Wapalyzer"?)
					$browser=="Oper") // Opera
			{
			$br = "WML";
			}
			else
			{
			$br = "HTML";
			}
			}
			if($br == "WML")
			{
			return TRUE;//手机端
			}
			else
			{
			return FALSE;//客户端
			}
	} 

	
	
}
?>