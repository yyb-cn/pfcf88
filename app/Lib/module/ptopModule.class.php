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
		//mobilepassed//验证
		//print_r($nums);
		arsort($nums);
		echo '<!doctype html><head><charset utf-8/><title>推荐排行榜</title></head>';
		echo '<h1 style="margin:auto;width:100%;text-align:center;color:red">推荐排行榜(top10)</h1>';
		$i=0;
		
		foreach($nums as $id=>$num  )
		{$i++;
			if($i>10){break;}
			
			$arr=$GLOBALS['db']->getone("select  user_name   from ".DB_PREFIX."user  where id=".$id );
			$arr_son=$GLOBALS['db']->getall("select user_name from ".DB_PREFIX."user where pid=".$id);
			
			//echo "select count(*) as mps from ".DB_PREFIX."user where pid=".$id." and mobilepassed=1";
			
			$oo=$GLOBALS['db']->getone("select count(*) as mps from ".DB_PREFIX."user where pid=".$id." and mobilepassed=1");
			
			
			
			$list='';
			foreach($arr_son as $k=>$v)
			{
				$list.='<li>'.$v['user_name'].'</li>';
				
			}
			echo '<div style="margin:auto;width:50%;font-size:20px">'.$arr.'&nbsp;&nbsp;推荐了&nbsp;&nbsp;'.$num.'个&nbsp;&nbsp;&nbsp;&nbsp;'.$oo.'个激活&nbsp&nbsp<details><summary>查看</summary><ul>'.$list.'</ul></details>';echo '<br /></div>';
			
		
		}
		
		
		
		
		
		
		
		
		//print_r($nums);
		
    }
	
	
		 
	 
}
?>