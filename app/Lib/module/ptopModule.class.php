<?php
//推荐人最多的是谁

class ptopModule  extends SiteBaseModule {
    function index() {
	
	//'SELECT column_id,count(*) as count FROM my_table group by column_id';
		$arr=$GLOBALS['db']->getAll("select  id ,pid    from ".   DB_PREFIX."user  where pid=0" );
		foreach($arr as $k=>$v)
		{
			$n=$GLOBALS['db']->getone("select  count(*)    from ".   DB_PREFIX."user  where pid=$v[id]" );
			
			$nums[$v['id']]=$n;
			
		}
		
		//print_r($nums);
		arsort($nums);
		echo '<!doctype html><head><charset utf-8/><title>推荐排行榜</title></head>';
		echo '<h1 style="margin:auto;width:100%;text-align:center;color:red">推荐排行榜</h1>';
		
		foreach($nums as $id=>$num)
		{
			$arr=$GLOBALS['db']->getone("select  user_name   from ".DB_PREFIX."user  where id=".$id );
			
			echo '<div style="margin:auto;width:25%;font-size:20px">'.$arr.'&nbsp;&nbsp;推荐了&nbsp;&nbsp;'.$num.'个';echo '<br /></div>';
		
		}
		
		
		
		
		
		
		
		
		//print_r($nums);
		
    }
	
	
		 
	 
}
?>