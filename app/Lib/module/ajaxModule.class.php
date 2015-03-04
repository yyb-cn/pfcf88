<?php
// +----------------------------------------------------------------------
// | Fanwe 方维订餐小秘书商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class ajaxModule extends SiteBaseModule
{
	public function check_field()
	{
		$field_name = addslashes(trim($_REQUEST['field_name']));
		$field_data = addslashes(trim($_REQUEST['field_data']));
		require_once APP_ROOT_PATH."system/libs/user.php";
		$res = check_user($field_name,$field_data);
		$result = array("status"=>1,"info"=>'');
		if($res['status'])
		{
			ajax_return($result);
		}
		else
		{
			$error = $res['data'];		
			if(!$error['field_show_name'])
			{
					$error['field_show_name'] = $GLOBALS['lang']['USER_TITLE_'.strtoupper($error['field_name'])];
			}
			if($error['error']==EMPTY_ERROR)
			{
				$error_msg = sprintf($GLOBALS['lang']['EMPTY_ERROR_TIP'],$error['field_show_name']);
			}
			if($error['error']==FORMAT_ERROR)
			{
				$error_msg = sprintf($GLOBALS['lang']['FORMAT_ERROR_TIP'],$error['field_show_name']);
			}
			if($error['error']==EXIST_ERROR)
			{
				$error_msg = sprintf($GLOBALS['lang']['EXIST_ERROR_TIP'],$error['field_show_name']);
			}
			$result['status'] = 0;
			$result['info'] = $error_msg;
			ajax_return($result);
		}
	}
	public function get_verify_code()
	{
		if(app_conf("SMS_ON")==0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['SMS_OFF'];
			ajax_return($data);		
		}
		$user_mobile = addslashes(htmlspecialchars(trim($_REQUEST['user_mobile'])));
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id == 0)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($data);
		}
		if($user_mobile == '')
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_MOBILE_EMPTY'];
			ajax_return($data);
		}
		
		if(!check_mobile($user_mobile))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
			ajax_return($data);
		}
		
		
		//查询是否有用户绑定
		$user= $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where mobile = '".$user_mobile."' and mobilepassed = 1");
		
		if($user)
		{
			if($user['id'] == intval($GLOBALS['user_info']['id']))
			{
				$data['status'] = 1;
				$data['info'] = $GLOBALS['lang']['MOBILE_VERIFIED'];
			}
			else
			{
				$data['status'] = 0;
				$data['info'] = $GLOBALS['lang']['MOBILE_USED_BIND'];
			}
			
			ajax_return($data);
		}
		
		
		if(!check_ipop_limit(get_client_ip(),"bind_mobile_verify",60,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
			ajax_return($data);
		}
		
		//开始生成手机验证
		$code = rand(1111,9999);
		$GLOBALS['db']->query("update ".DB_PREFIX."user set bind_verify = '".$code."',verify_create_time = '".get_gmtime()."' where id = ".$user_id);
		send_verify_sms($user_mobile,$code,$GLOBALS['user_info']);
		$data['status'] = 1;
		$data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
		ajax_return($data);
	}
	
	public function check_verify_code(){
		$ajax = intval($_REQUEST['ajax']);
		$verify = addslashes(htmlspecialchars(trim($_REQUEST['verify'])));
		if($verify==""){
			showErr($GLOBALS['lang']['BIND_MOBILE_VERIFY_ERROR'],$ajax);
		}
		$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($GLOBALS['user_info']['id']));
		$user_mobile = addslashes(htmlspecialchars(trim($_REQUEST['mobile'])));	
		if(app_conf("SMS_ON")==1)
		{	
			if($user['bind_verify'] == '' || $user['bind_verify']!=$verify)
			{
				showErr($GLOBALS['lang']['BIND_MOBILE_VERIFY_ERROR'],$ajax);
			}
			else 
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."user set mobile='$user_mobile',mobilepassed=1, bind_verify = '', verify_create_time = 0 where id = ".intval($GLOBALS['user_info']['id']));
				showSuccess($GLOBALS['lang']['MOBILE_BIND_SUCCESS'],$ajax);
			}
		}
		else{
			showErr($GLOBALS['lang']['SMS_OFF'],$ajax);
		}
	}
	
	public function set_sort()
	{
		$type = htmlspecialchars(addslashes(trim($_REQUEST['type'])));
		es_cookie::set("shop_sort_field",$type); 
		if($type!='sort')
		{
			$sort_type = trim(es_cookie::get("shop_sort_type")); 
			if($sort_type&&$sort_type=='desc')
			{
				es_cookie::set("shop_sort_type",'asc'); 
			}
			else
			{
				es_cookie::set("shop_sort_type",'desc'); 
			}		
		}
		else
		{
			es_cookie::set("shop_sort_type",'desc'); 
		}
	}
	
	public function set_store_sort()
	{
		$type = htmlspecialchars(addslashes(trim($_REQUEST['type'])));
		if(!in_array($type,array("default","dp_count","avg_point","ref_avg_price")))
		{
			$type = "default";
		}
		es_cookie::set("store_sort_field",$type); 
		if($type!='sort')
		{
			$sort_type = trim(es_cookie::get("store_sort_type")); 
			if($sort_type&&$sort_type=='desc')
			{
				es_cookie::set("store_sort_type",'asc'); 
			}
			else
			{
				es_cookie::set("store_sort_type",'desc'); 
			}		
		}
		else
		{
			es_cookie::set("store_sort_type",'desc'); 
		}
	}

	public function set_event_sort()
	{
		$type = htmlspecialchars(addslashes(trim($_REQUEST['type'])));
		es_cookie::set("event_sort_field",$type); 
		if($type!='sort')
		{
			$sort_type = trim(es_cookie::get("event_sort_type")); 
			if($sort_type&&$sort_type=='desc')
			{
				es_cookie::set("event_sort_type",'asc'); 
			}
			else
			{
				es_cookie::set("event_sort_type",'desc'); 
			}		
		}
		else
		{
			es_cookie::set("event_sort_type",'desc'); 
		}
	}
	
	public function load_filter_group()
	{
		$cate_id = intval($_REQUEST['cate_id']);	
		$ids = load_auto_cache("shop_sub_parent_cate_ids",array("cate_id"=>$cate_id));		
		$filter_group_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."filter_group where is_effect = 1 and cate_id in (".implode(",",$ids).") order by sort desc");
		
		$GLOBALS['tmpl']->assign("filter_group_list",$filter_group_list);
		$GLOBALS['tmpl']->display("inc/inc_filter_group.html");
	}
	
	public function collect()
	{
		if(!$GLOBALS['user_info'])
		{
			$GLOBALS['tmpl']->assign("ajax",1);
			$html = $GLOBALS['tmpl']->fetch("inc/login_form.html");
			//弹出窗口处理
			$res['open_win'] = 1;
			$res['html'] = $html;
			ajax_return($res);
		}
		else
		{
			$goods_id = intval($_REQUEST['id']);
			$goods_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$goods_id." and is_effect = 1 and is_delete = 0");
			if($goods_info)
			{
				$sql = "INSERT INTO `".DB_PREFIX."deal_collect` (`id`,`deal_id`, `user_id`, `create_time`) select '0','".$goods_info['id']."','".intval($GLOBALS['user_info']['id'])."','".get_gmtime()."' from dual where not exists (select * from `".DB_PREFIX."deal_collect` where `deal_id`= '".$goods_info['id']."' and `user_id` = ".intval($GLOBALS['user_info']['id']).")";
				$GLOBALS['db']->query($sql);
				if($GLOBALS['db']->affected_rows()>0)
				{
					//添加到动态
					insert_topic("deal_collect",$goods_id,intval($GLOBALS['user_info']['id']),$GLOBALS['user_info']['user_name']);
					$res['info'] = $GLOBALS['lang']['COLLECT_SUCCESS'];
				}
				else
				{
					$res['info'] = $GLOBALS['lang']['GOODS_COLLECT_EXIST'];
				}
				$res['open_win'] = 0;
				ajax_return($res);
			}
			else
			{
				$res['open_win'] = 0;
				$res['info'] = $GLOBALS['lang']['INVALID_GOODS'];
				ajax_return($res);
			}
		}
	}
	
	public function focus()
	{
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			$data['tag'] = 4;
			$data['html'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($data);
		}
		$focus_uid = intval($_REQUEST['uid']);
		if($user_id==$focus_uid)
		{
			$data['tag'] = 3;
			$data['html'] = $GLOBALS['lang']['FOCUS_SELF'];
			ajax_return($data);
		}
		
		$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focus_uid);
		if(!$focus_data&&$user_id>0&&$focus_uid>0)
		{
				$focused_user_name = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$focus_uid);
				$focus_data = array();
				$focus_data['focus_user_id'] = $user_id;
				$focus_data['focused_user_id'] = $focus_uid;
				$focus_data['focus_user_name'] = $GLOBALS['user_info']['user_name'];
				$focus_data['focused_user_name'] = $focused_user_name;
				$GLOBALS['db']->autoExecute(DB_PREFIX."user_focus",$focus_data,"INSERT");
				$GLOBALS['db']->query("update ".DB_PREFIX."user set focus_count = focus_count + 1 where id = ".$user_id);
				$GLOBALS['db']->query("update ".DB_PREFIX."user set focused_count = focused_count + 1 where id = ".$focus_uid);
				$data['tag'] = 1;
				$data['html'] = $GLOBALS['lang']['CANCEL_FOCUS'];
				
				//添加到动态
				insert_topic("focus",$focus_uid,$user_id,$GLOBALS['user_info']['user_name']);
				
				ajax_return($data);
		}
		elseif($focus_data&&$user_id>0&&$focus_uid>0)
		{
			$GLOBALS['db']->query("delete from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focus_uid);
			$GLOBALS['db']->query("update ".DB_PREFIX."user set focus_count = focus_count - 1 where id = ".$user_id);
			$GLOBALS['db']->query("update ".DB_PREFIX."user set focused_count = focused_count - 1 where id = ".$focus_uid);		
			$data['tag'] =2;
			$data['html'] = $GLOBALS['lang']['FOCUS_THEY'];
			ajax_return($data);
		}
		
	}
	
	public function randuser()
	{
		$user_id = intval($GLOBALS['user_info']['id']);	
		$user_list = get_rand_user(24,0,$user_id);	
		$GLOBALS['tmpl']->assign("user_list",$user_list);		
		$GLOBALS['tmpl']->display("inc/uc/randuser.html");
	}
	
	public function vote_topic()
	{
		$tag = addslashes(trim($_REQUEST['tag']));
		$topic_id = intval($_REQUEST['topic_id']);
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			$result['status'] = 0;
			$result['data'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($result);
		}
		$vote_count = intval($GLOBALS['db']->getOne("select vote_count from ".DB_PREFIX."topic_vote_log where user_id = ".$user_id." and topic_id = ".$topic_id." limit 1"));
		if($vote_count>0)
		{
			$result['status'] = 0;
			$result['data'] = $GLOBALS['lang']['YOU_HAVE_VOTE'];
			ajax_return($result);
		}
		else
		{
			$topic_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".$topic_id." and is_effect = 1 and is_delete = 0");
			
			if(!$topic_info)
			{
				$result['status'] = 0;
				$result['data'] = $GLOBALS['lang']['TOPIC_NULL'];
				ajax_return($result);
			}
			else
			{
				if($topic_info['user_id'] == $user_id)
				{
					$result['status'] = 0;
					$result['data'] = $GLOBALS['lang']['TOPIC_YOURSELF'];
					ajax_return($result);
				}
				if($tag=='good')
				{
					$field = 'good_count';
				}
				else
				{
					$field = 'bad_count';
				}
				$topic_info[$field]++;
				$GLOBALS['db']->autoExecute(DB_PREFIX."topic",$topic_info,"UPDATE"," id = ".$topic_info['id']);
				$data['user_id'] = $user_id;
				$data['topic_id'] = $topic_id;
				$data['vote_count'] = 1;
				$GLOBALS['db']->autoExecute(DB_PREFIX."topic_vote_log",$data);
				
				$result['status'] = 1;
				$result['data'] = $GLOBALS['lang']['TOPIC_'.strtoupper($tag)].$topic_info[$field];
				ajax_return($result);
			}			
			
		}
		
	}
	
	public function relay_topic()
	{
		$topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".intval($_REQUEST['id']));
		$GLOBALS['tmpl']->assign("topic_info",$topic);
		$GLOBALS['tmpl']->assign("user_info",$GLOBALS['user_info']);
		if($topic['origin_id']!=$topic['id'])
		{
			$origin_topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".$topic['origin_id']);
			$GLOBALS['tmpl']->assign("origin_topic_info",$origin_topic);
		}
		$GLOBALS['tmpl']->display("inc/ajax_relay_box.html");
	}
	public function fav_topic()
	{
		$topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".intval($_REQUEST['id']));
		$GLOBALS['tmpl']->assign("topic_info",$topic);
		if($topic['origin_id']!=$topic['id'])
		{
			$origin_topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".$topic['origin_id']);
			$GLOBALS['tmpl']->assign("origin_topic_info",$origin_topic);
		}
		$GLOBALS['tmpl']->display("inc/ajax_relay_box.html");
	}	
	public function do_relay_topic()
	{
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			$result['status'] = 0;
			$result['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		}
		else
		{
			$result['status'] = 1;
			$content = addslashes(htmlspecialchars(trim(valid_str($_REQUEST['content']))));
			$id = intval($_REQUEST['id']);
			$tid = insert_topic($content,$title="",$type="",$group="", $id, $fav_id=0);
			if($tid)
			{
				increase_user_active(intval($GLOBALS['user_info']['id']),"转发了一则分享");
				$GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '网站' where id = ".intval($tid));
			}
			$result['info'] = $GLOBALS['lang']['RELAY_SUCCESS'];
		}
		ajax_return($result);
	}
	public function do_fav_topic()
	{	
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			$result['status'] = 0;
			$result['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		}
		else
		{					
			$id = intval($_REQUEST['id']);
			$topic = $GLOBALS['db']->getRow("select id,user_id from ".DB_PREFIX."topic where id = ".$id);
			if(!$topic)
			{
				$result['status'] = 0;
				$result['info'] = $GLOBALS['lang']['TOPIC_NOT_EXIST'];
			}
			else
			{
				if($topic['user_id']==intval($GLOBALS['user_info']['id']))
				{
					$result['status'] = 0;
					$result['info'] = $GLOBALS['lang']['TOPIC_SELF'];
				}
				else
				{					
					$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic where (fav_id = ".$id." or (origin_id = ".$id." and fav_id <> 0)) and user_id = ".intval($GLOBALS['user_info']['id']));
					if($count>0)
					{
						$result['status'] = 0;
						$result['info'] = $GLOBALS['lang']['TOPIC_FAVED'];
					}
					else
					{
						$result['status'] = 1;
						$tid = insert_topic($content,$title="",$type="",$group="", $relay_id = 0, $id);
						if($tid)
						{
							increase_user_active(intval($GLOBALS['user_info']['id']),"喜欢了一则分享");
							$GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '网站' where id = ".intval($tid));
						}
						$result['info'] = $GLOBALS['lang']['FAV_SUCCESS'];
					}
				}
			}
		}
		ajax_return($result);
	}
	
	public function msg_reply(){
		$ajax = 1;
		$user_info = $GLOBALS['user_info'];
		if(!$user_info)
		{
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax);
		}
		if($_REQUEST['content']=='')
		{
			showErr($GLOBALS['lang']['MESSAGE_CONTENT_EMPTY'],$ajax);
		}
		
		if(!check_ipop_limit(get_client_ip(),"message",intval(app_conf("SUBMIT_DELAY")),0))
		{
			showErr($GLOBALS['lang']['MESSAGE_SUBMIT_FAST'],$ajax);
		}
		
		$rel_table = addslashes(trim($_REQUEST['rel_table']));
		$message_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."message_type where type_name='".$rel_table."'");
		if(!$message_type)
		{
			showErr($GLOBALS['lang']['INVALID_MESSAGE_TYPE'],$ajax);
		}			
		//添加留言
		$message['title'] = $_REQUEST['title']?htmlspecialchars(addslashes($_REQUEST['title'])):htmlspecialchars(addslashes($_REQUEST['content']));
		$message['content'] = htmlspecialchars(addslashes(valid_str($_REQUEST['content'])));
		$message['title'] = valid_str($message['title']);
			
		$message['create_time'] = get_gmtime();
		$message['rel_table'] = $rel_table;
		$message['rel_id'] = addslashes(trim($_REQUEST['rel_id']));
		$message['user_id'] = intval($GLOBALS['user_info']['id']);
		$message['pid'] = intval($_REQUEST['pid']);
		
		if(app_conf("USER_MESSAGE_AUTO_EFFECT")==0)
		{
			$message_effect = 0;
		}
		else
		{
			$message_effect = $message_type['is_effect'];
		}
		$message['is_effect'] = $message_effect;		
		$GLOBALS['db']->autoExecute(DB_PREFIX."message",$message);
		
		$l_user_id =  $GLOBALS['db']->getOne("SELECT user_id FROM ".DB_PREFIX."deal WHERE id=".$message['rel_id']);
		
		//添加到动态
		insert_topic("message_reply",$message['rel_id'],$message['user_id'],$GLOBALS['user_info']['user_name'],$l_user_id);
		
		if($rel_table == "deal"){
			
			require_once APP_ROOT_PATH.'app/Lib/deal.php';
			$deal = get_deal($message['rel_id']);
			$msg_u_id = $GLOBALS['db']->getOne("SELECT user_id FROM ".DB_PREFIX."message WHERE id=".$message['pid']);
			
			if($message['user_id'] != $msg_u_id){
				$msg_conf = get_user_msg_conf($deal['user_id']);
				//站内信
				if($msg_conf['sms_answer']==1){
					$content .= "<p>您好，用户 ".get_user_name($message['user_id'])."回复了您对借款列表 “<a href=\"".$deal['url']."\">".$deal['name']."</a>”的留言。具体回复如下：</p>";
					$content .= "<p>“".$message['content']."”</p>";
					send_user_msg("",$content,0,$msg_u_id,get_gmtime(),0,true,14,$message['rel_id']);
				}
				
				//邮件
				if($msg_conf['mail_answer']==1 && app_conf('MAIL_ON')==1){
					$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$msg_u_id);
					$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_DEAL_REPLY_MSG'");
					$tmpl_content = $tmpl['content'];
					
					$notice['user_name'] = $user_info['user_name'];
					$notice['msg_user_name'] = get_user_name($message['user_id'],false);
					$notice['deal_name'] = $deal['name'];
					$notice['deal_url'] = get_domain().url("index","deal",array("id"=>$deal['id']));
					$notice['message'] = $message['content'];
					$notice['site_name'] = app_conf("SHOP_TITLE");
					$notice['site_url'] = get_domain().APP_ROOT;
					$notice['help_url'] = get_domain().url("index","helpcenter");
					
					
					$GLOBALS['tmpl']->assign("notice",$notice);
					
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
					$msg_data['dest'] = $user_info['email'];
					$msg_data['send_type'] = 1;
					$msg_data['title'] = "用户".get_user_name($message['user_id'],false)."回复了你的留言！";
					$msg_data['content'] = addslashes($msg);
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				}
			}
		}
		
		showSuccess($GLOBALS['lang']['REPLY_POST_SUCCESS'],$ajax);
	}
	
	public function ajax_login()
	{
		$GLOBALS['tmpl']->display("inc/login_form.html");
	}
	
	public function check_event()
	{
		if($GLOBALS['user_info'])
		{
			$event_id = intval($_REQUEST['id']);
			$event = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event where id = ".$event_id." and is_effect = 1");
			if($event)
			{
				if($event['submit_begin_time']>get_gmtime())
				{
					$result['status'] = 2;
					$result['info'] = $GLOBALS['lang']['EVENT_NOT_START'];
				}
				elseif($event['submit_end_time']<get_gmtime()&&$event['submit_end_time']!=0)
				{
					$result['status'] = 2;
					$result['info'] = $GLOBALS['lang']['EVENT_SUBMIT_END'];
				}
				else
				{
					$result['status'] = 1;
				}
			}
			else
			{
				$result['status'] = 2;
				$result['info'] = $GLOBALS['lang']['EVENT_NOT_EXIST'];
			}
		}
		else
		{
			$result['status'] = 0;
		}
		ajax_return($result);
		
	}
	
	public function submit_event()
	{
		$event_id = intval($_REQUEST['id']);		
		$GLOBALS['tmpl']->assign("event_id",$event_id);		
		$user_id = intval($GLOBALS['user_info']['id']);
		$user_submit = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_submit where user_id = ".$user_id." and event_id = ".$event_id);
		if($user_submit)
		{
			$event_fields = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."event_field where event_id = ".$event_id." order by sort asc");
			foreach($event_fields as $k=>$v)
			{
				$event_fields[$k]['result'] = $GLOBALS['db']->getOne("select result from ".DB_PREFIX."event_submit_field where submit_id = ".$user_submit['id']." and field_id = ".$v['id']." and event_id = ".$event_id);
				$event_fields[$k]['value_scope'] = explode(" ",$v['value_scope']);
			}
			$GLOBALS['tmpl']->assign("event_fields",$event_fields);
			$GLOBALS['tmpl']->assign("user_submit",$user_submit);  //表示修改已报名记录
		}
		else
		{
			$event_fields = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."event_field where event_id = ".$event_id." order by sort asc");
			foreach($event_fields as $k=>$v)
			{
				$event_fields[$k]['value_scope'] = explode(" ",$v['value_scope']);
			}
			$GLOBALS['tmpl']->assign("event_fields",$event_fields);
		}
		
		$GLOBALS['tmpl']->display("inc/event_submit.html");
	}
	
	public function do_event_submit()
	{
		if($GLOBALS['user_info'])
		{
			$event_id = intval($_REQUEST['event_id']);
			$event = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event where id = ".$event_id." and is_effect = 1");
			if($event)
			{
				if($event['submit_begin_time']>get_gmtime())
				{
					$result['status'] = 1;
					$result['info'] = $GLOBALS['lang']['EVENT_NOT_START'];
				}
				elseif($event['submit_end_time']<get_gmtime()&&$event['submit_end_time']!=0)
				{
					$result['status'] = 1;
					$result['info'] = $GLOBALS['lang']['EVENT_SUBMIT_END'];
				}
				else
				{
					$submit_id = intval($_REQUEST['submit_id']);
					$submit_id = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."event_submit where event_id = ".$event_id." and user_id = ".intval($GLOBALS['user_info']['id'])));							
					if($submit_id)
					{
							//已经报名，仅作修改
							$GLOBALS['db']->query("delete from ".DB_PREFIX."event_submit_field where submit_id = ".$submit_id);		
							$field_ids = $_REQUEST['field_id'];
							foreach($field_ids as $field_id)
							{
								$current_result =  addslashes(htmlspecialchars(trim($_REQUEST['result'][$field_id])));
								$field_data = array();
								$field_data['submit_id'] = $submit_id;
								$field_data['field_id'] = $field_id;
								$field_data['event_id'] = $event_id;
								$field_data['result'] = $current_result;
								$GLOBALS['db']->autoExecute(DB_PREFIX."event_submit_field",$field_data,"INSERT");
							}			
							$result['status'] = 2;
							$result['info'] = "报名修改成功";
							ajax_return($result);		
					}
					//开始提交报名
					$user_id = intval($GLOBALS['user_info']['id']);
					$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."event_submit where event_id = ".$event_id." and user_id = ".$user_id);
					if(intval($count)>0)
					{
						$result['status'] = 1;
						$result['info'] = $GLOBALS['lang']['EVENT_SUBMITTED'];
					}
					else
					{						
						$submit_data = array();
						$submit_data['user_id'] = $user_id;
						$submit_data['event_id'] = $event_id;
						$submit_data['create_time'] = get_gmtime();
						$GLOBALS['db']->autoExecute(DB_PREFIX."event_submit",$submit_data,"INSERT");
						$submit_id = $GLOBALS['db']->insert_id();
						if($submit_id)
						{
							$field_ids = $_REQUEST['field_id'];
							foreach($field_ids as $field_id)
							{
								$current_result =  addslashes(htmlspecialchars(trim($_REQUEST['result'][$field_id])));
								$field_data = array();
								$field_data['submit_id'] = $submit_id;
								$field_data['field_id'] = $field_id;
								$field_data['event_id'] = $event_id;
								$field_data['result'] = $current_result;
								$GLOBALS['db']->autoExecute(DB_PREFIX."event_submit_field",$field_data,"INSERT");
							}
							$GLOBALS['db']->query("update ".DB_PREFIX."event set submit_count = submit_count+1 where id=".$event_id);
							
							
							//同步分享
							$title = "报名参加了".$event['name'];
							$content = "报名参加了".$event['name']." - ".$event['brief'];
							$url_route = array(
									'rel_app_index'	=>	'store',
									'rel_route'	=>	'edetail',
									'rel_param' => 'id='.$event['id']
								);							
							
							$tid = insert_topic($content,$title,$type="eventsubmit",$group="", $relay_id = 0, $fav_id = 0,$group_data ="",$attach_list=array(),$url_route);
							if($tid)
							{
								$GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '网站' where id = ".intval($tid));
							}
							
							$result['status'] = 2;
							$result['info'] = $GLOBALS['lang']['EVENT_SUBMIT_SUCCESS'];
						}
						else
						{
							$result['status'] = 1;
							$result['info'] = $GLOBALS['lang']['EVENT_SUBMIT_FAILED'];
						}
						
					}
				}
			}
			else
			{
				$result['status'] = 1;
				$result['info'] = $GLOBALS['lang']['EVENT_NOT_EXIST'];
			}
		}
		else
		{
			$result['status'] = 0;
		}
		ajax_return($result);
		
	}
	
	public function drop_pm()
	{
		if($GLOBALS['user_info'])
		{
			$user_id = intval($GLOBALS['user_info']['id']);
			$res = $_REQUEST['pm_key'];
			foreach($res as $key)
			{
				$sql = "update  ".DB_PREFIX."msg_box set is_delete = 1 where ((to_user_id = ".$user_id." and `type` = 0) or (from_user_id = ".$user_id." and `type` = 1)) and group_key = '".$key."'";
				$GLOBALS['db']->query($sql);
			}
			$result['status'] = 1;
			$result['info'] = $GLOBALS['lang']['DELETE_SUCCESS'];
		}
		else
		{
			$result['status'] = 0;
			$result['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		}
		ajax_return($result);
	}
	public function drop_pm_item()
	{
		if($GLOBALS['user_info'])
		{
			$user_id = intval($GLOBALS['user_info']['id']);
			$res = $_REQUEST['id'];
			foreach($res as $id)
			{
				$sql = "update  ".DB_PREFIX."msg_box set is_delete = 1 where id = '".$id."'";
				$GLOBALS['db']->query($sql);
			}
			$result['status'] = 1;
			$result['info'] = $GLOBALS['lang']['DELETE_SUCCESS'];
		}
		else
		{
			$result['status'] = 0;
			$result['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		}
		ajax_return($result);
	}	
	public function check_send()
	{
		/*$user_name = addslashes(trim($_REQUEST['user_name']));
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focused_user_name = '".$GLOBALS['user_info']['user_name']."' and focus_user_name = '".$user_name."'")>0)
		{
			//是粉丝
			$result['status'] = 1;
		}
		else
		{
			$result['status'] = 0;
		}*/
		$result['status'] = 1;
		ajax_return($result);
	}
	
	public function send_pm()
	{
		if($GLOBALS['user_info'])
		{
			$user_name = addslashes(trim($_REQUEST['user_name']));
			$user_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where user_name = '".$user_name."'");
			if(intval($user_id)==0)
			{
				$result['status'] = 0;
				$result['info'] = $GLOBALS['lang']['TO_USER_EMPTY'];
				ajax_return($result);
			}
			/*if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focused_user_name = '".$GLOBALS['user_info']['user_name']."' and focus_user_name = '".$user_name."'")==0)
			{
				//不是粉丝,验证是否有来信记录
				$sql = "select count(*) from ".DB_PREFIX."msg_box 
						where is_delete = 0 and 
						(to_user_id = ".intval($GLOBALS['user_info']['id'])." and `type` = 0 and from_user_id = ".$user_id.")";
				$inbox_count = $GLOBALS['db']->getOne($sql);
				if($inbox_count==0)
				{
					$result['status'] = 0;
					$result['info'] = $GLOBALS['lang']['FANS_ONLY'];
					ajax_return($result);
				}			
			}*/
			$content = htmlspecialchars(addslashes(trim($_REQUEST['content'])));
			send_user_msg("",$content,intval($GLOBALS['user_info']['id']),$user_id,get_gmtime());
			$result['status'] = 1;
			$key = array($user_id,intval($GLOBALS['user_info']['id']));
			sort($key);
			$group_key = implode("_",$key);
			$result['info'] = url("shop","uc_msg#deal",array("id"=>$group_key));
		}
		else
		{
			$result['status'] = 0;
			$result['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		}
		ajax_return($result);
	}
	
	public function usercard()
	{
		$uid = intval($_REQUEST['uid']);		
		$uinfo = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$uid." and is_delete = 0 and is_effect = 1");		
		if($uinfo)
		{
		$user_id = intval($GLOBALS['user_info']['id']);
		$focused_uid = intval($uid);
		$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focused_uid);
		if($focus_data)
		$uinfo['focused'] = 1; 		
		$uinfo['point_level'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."user_level where id = ".intval($uinfo['level_id']));
		$uinfo['medal_list'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_medal where is_delete = 0 and user_id = ".$uid." order by create_time desc");
		$GLOBALS['tmpl']->assign("card_info",$uinfo);		
		$GLOBALS['tmpl']->display("inc/usercard.html");
		}
		else 
		{
			header("Content-Type:text/html; charset=utf-8");
			echo "<div class='load'>该会员已被删除或者已被禁用</div>";
		}
	}
	
	//采集分享
	/**
	 * 传入 class_name,url
	 * **
	 * 传出 
	 *  array("status"=>"","info"=>"", "group"=>"","type"=>"","group_data"=>"","content"=>"","tags"=>"","images"=>array("id"=>"","url"=>""));					
	 */
	public function do_fetch()
	{
		$class_name = addslashes(trim($_REQUEST['class_name']));
		$url = trim($_REQUEST['url']);
		$result['status'] = 0;
		if(file_exists(APP_ROOT_PATH."system/fetch_topic/".$class_name."_fetch_topic.php"))
		{
			require_once APP_ROOT_PATH."system/fetch_topic/".$class_name."_fetch_topic.php";
			$class = $class_name."_fetch_topic";
			if(class_exists($class))
			{
				$api = new $class;
				$rs = $api->fetch($url);
				if($rs['status']==0)
				{
					$result['info'] = $rs['info'];
				}
				else
				{
					$result['status'] = 1;
					$result['group'] = $class_name;
					$result['group_data'] = $rs['group_data'];
					$result['content'] = $rs['content'];
					$result['type'] = $rs['type'];
					$result['tags'] = $rs['tags'];
					$result['images'] = $rs['images'];					 
				}
			}	
			else
			{
				$result['info'] = "接口不存在";
			}		
		}
		else
		{
			$result['info'] = "接口不存在";
		}
		
		ajax_return($result);
	}
	
	
	public function set_syn()
	{
		if($GLOBALS['user_info'])
		{
			$field = addslashes(trim($_REQUEST['field']));
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($GLOBALS['user_info']['id']));
			$upd_value = intval($user_info[$field]) == 0? 1:0;
			$GLOBALS['db']->query("update ".DB_PREFIX."user set `".$field."` = ".$upd_value." where id = ".intval($GLOBALS['user_info']['id']));
			$result['info'] = "设置成功";
			$user_info[$field] = $upd_value;
			es_session::set("user_info",$user_info);
		}
		else
		{
			$result['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		}
		ajax_return($result);
	}
	
	
	//ajax同步发微博
	public function syn_to_weibo()
	{
		set_time_limit(0);
		$topic_id = intval($_REQUEST['topic_id']);
		$user_id = intval($GLOBALS['user_info']['id']);
		$api_class_name = addslashes(htmlspecialchars(trim($_REQUEST['class_name'])));
		es_session::close();
		$topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".$topic_id);	
		if($topic['topic_group']!="share")
		{
			$group = $topic['topic_group'];
			if(file_exists(APP_ROOT_PATH."system/fetch_topic/".$group."_fetch_topic.php"))
			{
				require_once APP_ROOT_PATH."system/fetch_topic/".$group."_fetch_topic.php";
				$class_name = $group."_fetch_topic";
				if(class_exists($class_name))
				{
					$fetch_obj = new $class_name;
					$data = $fetch_obj->decode_weibo($topic);
				}
			}
		}
		else
		{
			$data['content'] = $topic['content'];
			
			//图片
			$topic_image = $GLOBALS['db']->getRow("select o_path from ".DB_PREFIX."topic_image where topic_id = ".$topic['id']);
			if($topic_image)
			$data['img'] = get_domain().APP_ROOT."/".$topic_image['o_path'];
		}
		
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id);
		$api = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."api_login where is_weibo = 1 and class_name = '".$api_class_name."'");
		if($user_info["is_syn_".strtolower($api['class_name'])]==1)
		{
				//发送本微博
				require_once APP_ROOT_PATH."system/api_login/".$api_class_name."_api.php";
				$api_class = $api_class_name."_api";
				$api_obj = new $api_class($api);
				$api_obj->send_message($data);
		}
	}
	
	public function load_api_url()
	{
		$type = intval($_REQUEST['type']);  //0:小登录图标 1:大登录图标 2:绑定图标
		$class_name = addslashes(htmlspecialchars(trim($_REQUEST['class_name'])));
		if(file_exists(APP_ROOT_PATH."system/api_login/".$class_name."_api.php"))
		{
				require_once APP_ROOT_PATH."system/api_login/".$class_name."_api.php";
				$api_class = $class_name."_api";
				$api = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."api_login where class_name = '".$class_name."'");
				$api_obj = new $api_class($api);
				if($type==0)
				$url = $api_obj->get_api_url();
				elseif($type==1)
				$url = $api_obj->get_big_api_url();
				else
				$url = $api_obj->get_bind_api_url();				
		}
		$domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?get_domain().$GLOBALS['IMG_APP_ROOT']:app_conf("PUBLIC_DOMAIN_ROOT");	
		$url = str_replace("./public/",$domain."/public/",$url);	
		header("Content-Type:text/html; charset=utf-8");
		echo $url;
	}
	
	public function update_user_tip()
	{
			require_once APP_ROOT_PATH."app/Lib/insert_libs.php";
			header("Content-Type:text/html; charset=utf-8");
			echo  insert_load_user_tip();
	}
	
	public function check_login_status()
	{
		if($GLOBALS['user_info'])		
		$result['status'] = 1;		
		else
		$result['status'] = 0;
		ajax_return($result);
	}
	
	public function checkverify()
	{
		$ajax = intval($_REQUEST['ajax']);
		if(app_conf("VERIFY_IMAGE")==1)
		{
			$verify = md5(trim($_REQUEST['verify']));
			$session_verify = es_session::get('verify');
			if($verify!=$session_verify)
			{				
				showErr($GLOBALS['lang']['VERIFY_CODE_ERROR'],$ajax);
			}
			else
			{
				showSuccess("验证成功",$ajax);
			}
		}
		else
		{
			showSuccess("验证成功",$ajax);
		}
	}
	
	public function signin()
	{
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			$result['status'] = 2;
			ajax_return($result);
		}
		else
		{
			
			$t_begin_time = to_timespan(to_date(get_gmtime(),"Y-m-d"));  //今天开始
			$t_end_time = to_timespan(to_date(get_gmtime(),"Y-m-d"))+ (24*3600 - 1);  //今天结束
			$y_begin_time = $t_begin_time - (24*3600); //昨天开始
			$y_end_time = $t_end_time - (24*3600);  //昨天结束
			
			$t_sign_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_sign_log where user_id = ".$user_id." and sign_date between ".$t_begin_time." and ".$t_end_time);
			if($t_sign_data)
			{
				$result['status'] = 1;
				$result['info'] = "您已经签到过了";
			}
			else
			{
				$y_sign_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_sign_log where user_id = ".$user_id." and sign_date between ".$y_begin_time." and ".$y_end_time);
				$total_signcount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_sign_log where user_id = ".$user_id);
				if($y_sign_data&&$total_signcount>=3)
				{
					$point = intval(app_conf("USER_LOGIN_KEEP_POINT"));
					$score = intval(app_conf("USER_LOGIN_KEEP_SCORE"));
					$money = doubleval(app_conf("USER_LOGIN_KEEP_MONEY"));
				}
				else
				{
					$point = intval(app_conf("USER_LOGIN_POINT"));
					$score = intval(app_conf("USER_LOGIN_SCORE"));
					$money = doubleval(app_conf("USER_LOGIN_MONEY"));
				}
				if($point>0||$score>0||$money>0)
				{
					require_once APP_ROOT_PATH."system/libs/user.php";
					$data = array("money"=>$money,"score"=>$score,"point"=>$point);
					modify_account($data,$user_id,"您在".to_date(get_gmtime())."签到成功");
					$sign_log['user_id'] = $user_id;
					$sign_log['sign_date'] = get_gmtime();
					$GLOBALS['db']->autoExecute(DB_PREFIX."user_sign_log",$sign_log);					
				}
				$result['status'] = 1;
				$result['info'] = "签到成功";
			}
			ajax_return($result);
		}
	}
	
	public function gopreview()
	{		
		header("Content-Type:text/html; charset=utf-8");
		echo get_gopreview();		
	}
	
	/**
	 * 举报用户
	 */
	public function reportguy(){
		if(!$GLOBALS['user_info'])
			exit();
			
		$user_id = intval($_REQUEST['user_id']);
		if($user_id==0)
			exit();
		$u_info = get_user("id,user_name",$user_id);
		
		$GLOBALS['tmpl']->assign("u_info",$u_info);
		
		
		$GLOBALS['tmpl']->display("inc/ajax/reportguy.html");
	}
/*↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓抽奖↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓*/	
	public function lottery(){
	
		if(!$GLOBALS['user_info']){
			set_gopreview();
			echo json_encode('login');exit;//未登录
		}
		//查询抽奖次数
		$user_id = intval($GLOBALS['user_info']['id']);
		$user= $GLOBALS['db']->getRow("select * from ".DB_PREFIX."lottery where uid = '".$user_id."' ");
		if(!$user['draw_sec']){
		echo json_encode('less_lottery');exit;//次数不足
		}
		$count= $GLOBALS['db']->getRow("select count(*) as c from ".DB_PREFIX."award_log where prize_id = 1 ");
		
		if($count['c']>5){
		
			$a=0;
		}
		else{
			$a=4;
		};
		
		$abc= $GLOBALS['db']->getRow("select count(*) as c from ".DB_PREFIX."award_log where prize_id = 2 ");
		if($abc['c']>6){
		
			$b=0;
		}
		else{
			$b=6;
		};
			$prize_arr = array( 
			'0' => array('id'=>1,
						'min'=>array(4,190), 
						'max'=>array(40,220),
						'prize'=>'8888',
						'v'=>$a), 
			'1' => array('id'=>2,
						'min'=>array(140,320), 
						'max'=>array(170,350),
						'prize'=>'3200',
						'v'=>$b), 
			'2' => array('id'=>3,
						'min'=>array(95,277), 
						'max'=>array(130,300),
						'prize'=>'1600',
						'v'=>20+$a+$b),
			'3' => array('id'=>4,
						'min'=>array(50,230), 
						'max'=>array(80,260),
						'prize'=>'800',
						'v'=>70) 			
						);					
		
	foreach ($prize_arr as $key => $val) { 
		$arr[$val['id']] = $val['v']; 
	} 
	 
	$rid = $this->getRand($arr); //根据概率获取奖项id 
	 
	$res = $prize_arr[$rid-1]; //中奖项 
	$min = $res['min']; 
	$max = $res['max']; 
		$i = mt_rand(0,1); 
		$result['angle'] = mt_rand($min[$i],$max[$i]); 
	$result['prize'] = $res['prize']; 
	//抽奖结束,保存抽奖结果,然后抽奖次数减1
	//1.抽奖次数减1
	$GLOBALS['db']->query("update ".DB_PREFIX."lottery set draw_sec=draw_sec-1 where uid = ".$user_id); 
	//2.记录抽奖结果
	$award_data['log_time']=get_gmtime();
	$award_data['user_id']=$user_id;
	$award_data['prize_id']=$res['id'];
	
	$GLOBALS['db']->autoExecute(DB_PREFIX."award_log",$award_data); //插入抽奖记录
		//查询投资记录，当前会员的投资记录
		$sql = "select dl.id,dl.money as u_load_money,dl.id as deal_load_id from ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal as d on d.id = dl.deal_id  where dl.user_id = ".$user_id." and (d.deal_status=1  or d.deal_status=2 or d.deal_status=4)and dl.money>=10000 order by dl.id desc";
		$list = $GLOBALS['db']->getRow($sql);
		$deal_id=$list['id'];
		$money=trim($result['prize']);
		$sql="update ".DB_PREFIX."deal_load set virtual_money=virtual_money+   ".  $money ."   where id=".$deal_id;
		$list = $GLOBALS['db']->query($sql);
		ajax_return($result); 	
	}
	
/**------抽奖part2-------------**/	
	public function getRand($proArr){
	
	 
    $result = ''; 
 
    //概率数组的总概率精度 
    $proSum = array_sum($proArr); 
 
    //概率数组循环 
    foreach ($proArr as $key => $proCur) { 
        $randNum = mt_rand(1, $proSum); 
        if ($randNum <= $proCur) { 
            $result = $key; 
            break; 
        } else { 
            $proSum -= $proCur; 
        } 
    } 
    unset ($proArr); 
 
    return $result; 
	}
/*↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑抽奖↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑*/		
	public function savereportguy(){
		$result  = array("status"=>0,"message"=>"");
		if(!$GLOBALS['user_info']){
			$result['message'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($result);
			exit();
		}
		
		if(!check_ipop_limit(get_client_ip(),"savereportguy",10,0))
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['MESSAGE_SUBMIT_FAST'];
			ajax_return($data);
		}
		
		$user_id = intval($_REQUEST['user_id']);
		if($user_id==0){
			$result['message'] = "没有该用户";
			ajax_return($result);
			exit();
		}
		
		$data['user_id'] = $GLOBALS['user_info']['id'];
		$data['r_user_id'] = $user_id;
		$data['reason'] = htmlspecialchars($_REQUEST['reason']);
		$data['content'] = htmlspecialchars($_REQUEST['content']);
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."reportguy",$data,"INSERT");
		
		$result['status'] = 1;
		ajax_return($result);
	}
	
	/**
	 * 站内信
	 */
	public function send_msg(){
		if(!$GLOBALS['user_info'])
			exit();
			
		$user_id = intval($_REQUEST['user_id']);
		if($user_id==0)
			exit();
		$u_info = get_user("id,user_name",$user_id);
		
		$GLOBALS['tmpl']->assign("u_info",$u_info);
		
		
		$GLOBALS['tmpl']->display("inc/ajax/send_msg.html");
	}
	
}
?>