<?php
require APP_ROOT_PATH.'app/Lib/page.php';
require APP_ROOT_PATH."system/libs/user.php";
class lotteryModule extends SiteBaseModule
{  
	public function zouma()
	{
	
	  // print_r($GLOBALS['user_info']);exit;
	 $score=$GLOBALS['user_info']['lottery_score'];
	 // $GLOBALS['user_info']['score']
	 
	 $huodong= $GLOBALS['db']->getRow("select * from ".DB_PREFIX."huodong where id=1"); //活动ID
	  $mobilepassed=intval($GLOBALS['user_info']['mobilepassed']);
	  $GLOBALS['tmpl']->assign("mobilepassed",$mobilepassed);
	$idcardpassed= intval($GLOBALS['user_info']['idcardpassed']);
	$GLOBALS['tmpl']->assign("idcardpassed",$idcardpassed);
	
	 //获取中奖列表
	 $zhongjiang_list= $GLOBALS['db']->getAll("select * from ".DB_PREFIX."award_log where huodong_id=1 order by log_time desc");//活动ID
	
		foreach($zhongjiang_list as $k=>$v){
				$user=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id ='".$v['user_id']."'");
				$user['user_name']=cut_str($user['user_name'], 1, 0).'***'.cut_str($user['user_name'], 1, -1);
				$zhongjiang_list[$k]['user_name']=$user['user_name'];
				$prize=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."prize where id =".$v['prize_id']);
				if($prize==''){
				$zhongjiang_list[$k]['prize_name']='无';
				}
				else{
				$zhongjiang_list[$k]['prize_name']=$prize['name'];
				}
		}
		
	$GLOBALS['tmpl']->assign("zhongjiang_list",$zhongjiang_list);
	 if(time()<=$huodong['endtime']){
		 $score_id=$GLOBALS['user_info']['id'];
     $GLOBALS['tmpl']->assign("score",$score);
	 $GLOBALS['tmpl']->assign("score_id",$score_id);
	 $GLOBALS['tmpl']->display("page/choujiang.html");
	 }
	else{
		showErr("活动已经到期了");//活动到期
	}
	
		
	
	}
	
	
	
	
	public function lottery()
	{
	//1.积分判断
	 $statr_score=$GLOBALS['user_info']['lottery_score']; 
	 $statr_score = (int)$statr_score;
	 if($statr_score<20000){
		echo json_encode(1);exit;
	  }
	//2.查询是否在3月22号前买过产品。
	$id=$GLOBALS['user_info']['id'];
	$c_time=strtotime("2015-03-22");
	$sql = "select dl.id,dl.create_time as cl_time,dl.money as u_load_money,d.name as deal_name,dl.id as deal_load_id from ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal as d on d.id = dl.deal_id  where dl.create_time>".$c_time." and dl.user_id = ".$id." and (d.deal_status=1  or d.deal_status=2 or d.deal_status=4) and d.repay_time_type=1  order by dl.id desc";
	$list = $GLOBALS['db']->getRow($sql);
	if(empty($list)){
		echo json_encode(2);exit;
	}
	
	///*@luo-抽奖、
	//3.开始抽奖奖品配置
	 
	 $b=0.001;
	$abc= $GLOBALS['db']->getRow("select count(*) as c from ".DB_PREFIX."award_log where prize_id = 8 ");
		if($abc['c']>=35){
			$b=0;
		}	  
			  if($statr_score >=20000){
			  $award = array(
					   ///////////  // 奖品ID => array('奖品名称',概率,面额,奖品ID)
					   1 => array('1000元代金券',0.45,1000,7),
					   2 => array('IPhone6 PLUS',0.0,0,5),
					   3 => array('2000元代金券',0.05-$b,2000,8),          
					   4 => array('5000元代金券',$b,5000,9),  //0.001
					   5 => array('1000元代金券',0.45,1000,7),          
					   6 => array('IPad MINI2',0.0,0),
					   7 => array('2000元代金券',0.05-$b,2000,8),          
					   8 => array('5000元代金券',$b,5000,9)   // 0.001 
					  );
			  }
					  $r =rand(1,100);
					  $num =0;
					  $award_id =0;
				 foreach($award as $k=>$v){
					 $tmp = $num;
					 $num += $v[1]*100;
					 if($r>$tmp && $r<=$num){
					$award_id = $k;
					break;
					  }
				 }
				$nice=$nice+ $award[$award_id][2];
				$text.=$award[$award_id][2]; 
				if($award[$award_id][2]==5000){
				$five+=1;
				}
				 $text.="\r\n";	

				 
		
		
	  //4.扣除积分
	  $now_score=$statr_score-20000;
	  $GLOBALS['db']->query("update ".DB_PREFIX."user set lottery_score=".$now_score." where id = ".$id);
	 //5.写入代金券
	 //$award[$award_id][2]//面额
	  $money=$award[$award_id][2];
	  $deal_id=$list['id'];
		$sql="update ".DB_PREFIX."deal_load set virtual_money=virtual_money+   ".  $money ."   where id=".$deal_id;
		$GLOBALS['db']->query($sql);
		
		//7.插入用户记录
		$log_info['log_info'] = '代金券'.$money.'元，用于'.'<a href="?deal_load_id='.$list['deal_load_id'].'&m=Deal_list&a=index">'.$list['deal_name'].'</a>';
		$log_info['log_time'] = get_gmtime();
		$log_info['user_id'] = $id;
		$GLOBALS['db']->autoExecute(DB_PREFIX."user_log",$log_info);
		
	  //6.抽奖记录
	  $lottery_user_id=$id;//用户ID
	  $lottery_prize_id=$award[$award_id][3];//奖品ID
	  $lottery_log_time=time();//抽奖时间
	  $huodong_id=1;//活动ID
	  $sql="insert into `fanwe_award_log`(`user_id`,`prize_id`,`log_time`,`huodong_id`) values('$lottery_user_id','$lottery_prize_id','$lottery_log_time','$huodong_id')";
		mysql_query($sql);
	 $lottery_log_id= mysql_affected_rows();
	 	
	 
	
	//8.抽奖结束,ajax返回
      $data=array('award_id'=>$award_id,'award_name'=>$award[$award_id][0]);
		 if($lottery_log_id>0){
			echo json_encode($data);
	     }
	}

	public function every_lottery()
	{  
	 $id=$GLOBALS['user_info']['id'];
	    $max_id=$GLOBALS['db']->getOne("SELECT max(id) FROM ".DB_PREFIX."award_log where user_id=".$id);
     if($max_id){
	 $log_time=$GLOBALS['db']->getOne("SELECT `log_time` FROM ".DB_PREFIX."award_log where id=".$max_id);
	 $log_data=date('Y-m-d',$log_time);
	 $now_time=date('Y-m-d',time());
	 if($log_data==$now_time){
	    echo 2;exit; 
		
	   }
	   }
	  	$lottery_rand_statr=mt_rand(18,49);
        $lottery_rand_now=$lottery_rand_statr/100;		
	  $award = array(
			   ////// 奖品ID => array('金额',概率，id)
			   1 => array(0.5,0.05,10),
			   2 => array($lottery_rand_now,0.95,10)
			  );
			  $r =rand(1,100);
			  $num = 0;
			  $award_id = 0;
		 foreach($award as $k=>$v){
			 $tmp = $num;
			 $num += $v[1]*100;
			 if($r>$tmp && $r<=$num){
			$award_id = $k;
			break;
		      }
	     }
	  $lottery_user_id=$id;
	  $lottery_prize_id=$award[$award_id][2];
	  $lottery_log_time=time();
	  $prize_name=$award[$award_id][0];
	  $log_info=$now_time.'每日抽奖';
	  $sql="insert into `fanwe_award_log`(`user_id`,`prize_id`,`log_time`,`huodong_id`,`prize_name`) values('$lottery_user_id','$lottery_prize_id','$lottery_log_time',2,$prize_name)";
    	mysql_query($sql);
	  $lottery_log_id= mysql_affected_rows();		
      $data=array('award_id'=>$award_id,'award_name'=>$award[$award_id][0]);
		 if($lottery_log_id>0){
		 //修改钱
         $money_num=$GLOBALS['db']->getOne("SELECT `money` FROM ".DB_PREFIX."user where id=".$id); 
		 $money_uptate=$award[$award_id][0];
	     $money=$money_num+$money_uptate;
         $GLOBALS['db']->query("update ".DB_PREFIX."user set money =".$money." where id = ".$id);
	       //用户明细  
			 $que="insert into `fanwe_user_log`(`log_info`,`log_time`,`log_admin_id`,`money`,`user_id`) values('$log_info','$lottery_log_time',1,'$money_uptate','$id')";
    	     mysql_query($que);
			 mysql_affected_rows();
		   
			echo json_encode($data);
	     }else{
		   echo 1;
		 }
      

		
		
	}
	
	
	
	
	
}	
?>