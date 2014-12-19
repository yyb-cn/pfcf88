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
		
		//print_r($vo);exit;
		
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
	
	
	
	public function export_csv()
	{
		 $id = $_REQUEST ['id'];
		
		//where(array ('user_id' => array ('in', explode ( ',', $id ) ) ));
		$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
		
		$vo = M(MODULE_NAME)->where($condition)->select();
		
		foreach($vo as $k=>$v)
		{
		
		$v['region_lv1_name'] = M("DeliveryRegion")->where("id=".$v['region_lv1'])->getField("name");
		$v['region_lv2_name'] = M("DeliveryRegion")->where("id=".$v['region_lv2'])->getField("name");
		$v['region_lv3_name'] = M("DeliveryRegion")->where("id=".$v['region_lv3'])->getField("name");
		$v['region_lv4_name'] = M("DeliveryRegion")->where("id=".$v['region_lv4'])->getField("name");
		$v['phone']=M("User")->where("id=".$v['user_id'])->getField("mobile");
		$v['user_name']=M("User")->where("id=".$v['user_id'])->getField("user_name");
		$v['bank_name'] =  M("bank")->where("id=".$v['bank_id'])->getField("name");
		$arr[0]=array('序号','银行','地区(省)','地区(市/区)','支行名','开户名','卡号','金额','电话号码','操作备注','申请时间','备注');
		$arr[$k+1]=array($k+1,$v['bank_name'],$v['region_lv2_name'],$v['region_lv3_name'],$v['bankzone'],$v['real_name'],"'".$v['bankcard'],$v['money'],$v['phone'],"'".$v['desc'],to_date($v['create_time'],'Y-m-d'),$v['user_name']);
		}
		
		
		//var_dump($arr);exit;
		
		$this->outputXlsHeader($arr,'提现名单'.time());
		
		
	}
	
	
	public function outputXlsHeader($data,$file_name = 'export')
{
 header('Content-Type: text/xls'); 
 header ( "Content-type:application/vnd.ms-excel;charset=utf-8" );
 $str = mb_convert_encoding($file_name, 'gbk', 'utf-8');   
 header('Content-Disposition: attachment;filename="' .$str . '.xls"');      
 header('Cache-Control:must-revalidate,post-check=0,pre-check=0');        
 header('Expires:0');         
 header('Pragma:public');
 
 $table_data = '<table border="1">'; 
 foreach ($data as $line)         
 {
  $table_data .= '<tr>';
  foreach ($line as $key => &$item)
  {
   $item = mb_convert_encoding($item, 'gbk', 'utf-8'); 
   $table_data .= '<td>' . $item . '</td>';
  }
  $table_data .= '</tr>';
 }
 $table_data .='</table>';
 echo $table_data;    
 die();
}

	
	
	
	
	
}
?>