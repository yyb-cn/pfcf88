<?php

class UserCarryAction extends CommonAction{

    //提现申请列表
	public function index(){
		
		if(trim($_REQUEST['user_name'])!='')
		{
			$map['user_id'] = D("User")->where("user_name='".trim($_REQUEST['user_name'])."'")->getField('id');
		}
		
		if(trim($_REQUEST['status'])!='')
		{
			$map['status'] = intval($_REQUEST['status']);
		}
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$model = D ("UserCarry");
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
	}
	//提现申请列表
	public function edit(){
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$vo['region_lv1_name'] = M("DeliveryRegion")->where("id=".$vo['region_lv1'])->getField("name");
		$vo['region_lv2_name'] = M("DeliveryRegion")->where("id=".$vo['region_lv2'])->getField("name");
		$vo['region_lv3_name'] = M("DeliveryRegion")->where("id=".$vo['region_lv3'])->getField("name");
		$vo['region_lv4_name'] = M("DeliveryRegion")->where("id=".$vo['region_lv4'])->getField("name");
		$vo['bank_name'] =  M("bank")->where("id=".$vo['bank_id'])->getField("name");
		
		$this->assign("vo",$vo);
		$this->display ();
	}
	
	public function update(){
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/index"));
		if(intval($data['status'])==0)
		{
			$this->success(L("UPDATE_SUCCESS"));
		}
		$data['update_time'] = get_gmtime();
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			//成功提示
			$vo = M(MODULE_NAME)->where("id=".$data['id'])->find();
			$user_id = $vo['user_id'];
			require_once APP_ROOT_PATH."/system/libs/user.php";
			if($data['status']==1){
				//提现
				modify_account(array("money"=>-($vo['money']+$vo['fee']),"lock_money"=>-($vo['money']+$vo['fee'])),$vo['user_id'],"提现成功");
				$content = "您于".to_date($vo['create_time'],"Y年m月d日 H:i:s")."提交的".format_price($vo['money'])."提现申请汇款成功，请查看您的资金记录。";
				
				
				$group_arr = array(0,$user_id);
				sort($group_arr);
				$group_arr[] =  6;
				
				$msg_data['content'] = $content;
				$msg_data['to_user_id'] = $user_id;
				$msg_data['create_time'] = get_gmtime();
				$msg_data['type'] = 0;
				$msg_data['group_key'] = implode("_",$group_arr);
				$msg_data['is_notice'] = 6;
				
				$GLOBALS['db']->autoExecute(DB_PREFIX."msg_box",$msg_data);
				$id = $GLOBALS['db']->insert_id();
				$GLOBALS['db']->query("update ".DB_PREFIX."msg_box set group_key = '".$msg_data['group_key']."_".$id."' where id = ".$id);
				
			}
			else{
				//驳回
				modify_account(array("lock_money"=>-($vo['money']+$vo['fee'])),$vo['user_id'],"提现失败");
				$content = "您于".to_date($vo['create_time'],"Y年m月d日 H:i:s")."提交的".format_price($vo['money'])."提现申请被我们驳回，驳回原因\"".$data['msg']."\"";
				
				$group_arr = array(0,$user_id);
				sort($group_arr);
				$group_arr[] =  7;
				
				$msg_data['content'] = $content;
				$msg_data['to_user_id'] = $user_id;
				$msg_data['create_time'] = get_gmtime();
				$msg_data['type'] = 0;
				$msg_data['group_key'] = implode("_",$group_arr);
				$msg_data['is_notice'] = 7;
				
				$GLOBALS['db']->autoExecute(DB_PREFIX."msg_box",$msg_data);
				$id = $GLOBALS['db']->insert_id();
				$GLOBALS['db']->query("update ".DB_PREFIX."msg_box set group_key = '".$msg_data['group_key']."_".$id."' where id = ".$id);
			}
			
			save_log("编号为".$data['id']."的提现申请".L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		}else {
			//错误提示
			$DBerr = M()->getDbError();
			save_log("编号为".$data['id']."的提现申请".L("UPDATE_FAILED").$DBerr,0);
			$this->error(L("UPDATE_FAILED").$DBerr,0);
		}
	}
}
?>