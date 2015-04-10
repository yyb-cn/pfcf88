<?php
require APP_ROOT_PATH.'app/Lib/page.php';
require APP_ROOT_PATH."system/libs/user.php";
require APP_ROOT_PATH.'app/Lib/deal.php';
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
	// 获取我的抽奖记录
	$score_id=$GLOBALS['user_info']['id'];
	if($score_id){
	 $my_list= $GLOBALS['db']->getAll("select * from ".DB_PREFIX."award_log where user_id=".$score_id." order by log_time desc");//活动ID
		foreach($my_list as $k=>$v){
				$user=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id ='".$v['user_id']."'");
				$user['user_name']=cut_str($user['user_name'], 1, 0).'***'.cut_str($user['user_name'], 1, -1);
				$my_list[$k]['user_name']=$user['user_name'];
				$prize=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."prize where id =".$v['prize_id']);
				if($prize==''){
				$my_list[$k]['prize_name']='暂无';
				}
				else{
				$my_list[$k]['prize_name']=$prize['name'];
				}
		}
	$GLOBALS['tmpl']->assign("my_list",$my_list);
	}
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
	$abc= $GLOBALS['db']->getRow("select count(*) as c from ".DB_PREFIX."award_log where prize_id = 9 ");
		if($abc['c']>=5){
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
	 	/*****送标*******/ 
	         //$deal_voucher_user_id=$GLOBALS['user_info']['id'];
		  $tshiwu=$award[$award_id][2];//代金券金额
		  $sql="select max(sort) from `fanwe_deal` where is_delete=0";
		  $maxs=$GLOBALS['db']->getRow($sql);
		  $max=$maxs['max(sort)']+1; 
		  $name='投资了'.$tshiwu.'所送15天投资'.$tshiwu.'的利息';
		   
		    $deal_data['name']=$name;
            $deal_data['sub_name']=$name;	
            $deal_data['cate_id'] =14;
            $deal_data['user_id']=6;
            $deal_data['is_effect']=1;
            $deal_data['is_delete']=0 ;
            $deal_data['sort']=$max;         
            $deal_data['type_id']=10;
            $deal_data['borrow_amount']=0;
            $deal_data['min_loan_money']=1000;
            $deal_data['repay_time']=15;
            $deal_data['deal_status']=4;
            $deal_data['enddate']=1;
            $deal_data['create_time']=get_gmtime();
            $deal_data['update_time']=get_gmtime();
            $deal_data['name_match_row']=$name;
            // $deal_data['deal_cate_match_row']='我不想让你看到';
			// $deal_data['deal_cate_match']='ux25105ux19981ux24819ux35753ux20320ux30475ux21040';
            // $deal_data['type_match']='ux20854ux20182ux20511ux27454';
            // $deal_data['type_match_row']='其他借款';
            $deal_data['buy_count']=1;
            $deal_data['loantype'] =0 ;
            $deal_data['warrant'] = 2 ;
            $deal_data['services_fee']=0;
            $deal_data['repay_time_type']=0; 
            $deal_data['load_money']=0;
			$deal_data['presented_virtual'] =$tshiwu;
            $deal_data['enddate']=1;
            $deal_data['start_time']=get_gmtime();
            $deal_data['success_time']=get_gmtime();
            $deal_data['repay_start_time']=get_gmtime();
            $deal_data['next_repay_time']=get_gmtime()+86400;
			$deal_data['rate']=8;
			$deal_data['virtual_id']=1;  //判断是否是公司所送的纯代金卷投资、 
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$deal_data);
		//	now_insert('fanwe_deal',$deal_data);
		   $deal_id= $GLOBALS['db']->insert_id();//获取插入的ID	
          
	
		  if($deal_id){
		      $deal=get_deal($deal_id);
		      $data['user_id'] = $user_id=$deal_voucher_user_id;
		      $data['user_name'] = $GLOBALS['user_info']['user_name'];
		      $data['deal_id'] = $deal_id;
			  $data['money'] =0;
		      $data['virtual_money'] =$tshiwu;
		      $data['create_time'] = get_gmtime();
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load",$data);
         $deal_load_id = $GLOBALS['db']->insert_id();//获取插入的ID
		if($deal_load_id > 0){		
			////////////更改资金记录
			$msg = sprintf('编号%s的投标,付款单号%s',$deal_id,$deal_load_id);
			require_once APP_ROOT_PATH."system/libs/user.php";	
			modify_account(array('money'=>-trim($_REQUEST["bid_money"]),'score'=>0),$GLOBALS['user_info']['id'],$msg);
			$deal = get_deal($deal_id);
			sys_user_status($GLOBALS['user_info']['id']);
		///////	/超过一半的时候
			if($deal['deal_status']==1 && $deal['progress_point'] >= 50 && $deal['progress_point']<=60 && $deal['is_send_half_msg'] == 0)
			{
				$msg_conf = get_user_msg_conf($deal['user_id']);
		////////	邮件
				if(app_conf("MAIL_ON")){
					if(!$msg_conf || intval($msg_conf['mail_half'])==1){
						$load_tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_HALF_EMAIL'");
						$user_info = $GLOBALS['db']->getRow("select email,user_name from ".DB_PREFIX."user where id = ".$deal['user_id']);
						$tmpl_content = $load_tmpl['content'];
						$notice['user_name'] = $user_info['user_name'];
						$notice['deal_name'] = $deal['name'];
						$notice['deal_url'] = get_domain().$deal['url'];
						$notice['site_name'] = app_conf("SHOP_TITLE");
						$notice['site_url'] = get_domain().APP_ROOT;
						$notice['help_url'] = get_domain().url("index","helpcenter");
						$notice['msg_cof_setting_url'] = get_domain().url("index","uc_msg#setting");		
						$GLOBALS['tmpl']->assign("notice",$notice);					
						$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
						$msg_data['dest'] = $user_info['email'];
						$msg_data['send_type'] = 1;
						$msg_data['title'] = "您的借款列表“".$deal['name']."”招标过半！";
						$msg_data['content'] = addslashes($msg);
						$msg_data['send_time'] = 0;
						$msg_data['is_send'] = 0;
						$msg_data['create_time'] = get_gmtime();
						$msg_data['user_id'] =  $deal['user_id'];
						$msg_data['is_html'] = $load_tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data);
					
					}
				}	
			///////	//////////站内信
				if(intval($msg_conf['sms_half'])==1){
					$content = "<p>您在".app_conf("SHOP_TITLE")."的借款“<a href=\"".$deal['url']."\">".$deal['name']."</a>”完成度超过50%"; 
					send_user_msg("",$content,0,$deal['user_id'],get_gmtime(),0,true,15);
				}
		////////	/////////	更新
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal",array("is_send_half_msg"=>1),"UPDATE","id=".$deal_id);
			}
		}
		else{
			showErr($GLOBALS['lang']['ERROR_TITLE'],3);
		    }
	    }
	 
	
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