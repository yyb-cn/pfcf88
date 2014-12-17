<?php
//---------------------------
//这个是后台代金券的相关操作。包括
/*
	index  主页
	add  添加(模板)
	insert  添加(操作)
	
		只用填写这四个参数
		
		begin_time  
		end_time
		name
		money
		
	send  发送代金券(模板)
	dosend 发送代金券(操作)
	‘
	'
	’
*/
	
//

class EcvTypeAction extends CommonAction{
	public function index()
	{
		parent::index();
	}
	public function add()
	{
		$this->display();
	}
	public function insert() {
		
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();
		$reg_num=M(MODULE_NAME)->where(array('reg_send'=>'1'))->count();//为是的数量
		
		if($data['reg_send']&&$reg_num)
		{
			$this->error( "只能有一个注册就送");
		}
		//$data=$_POST;
		//如果已经有就不要再插入了
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		if(!check_empty($data['name']))
		{
			$this->error(L("VOUCHER_NAME_EMPTY_TIP"));
		}	
		if(doubleval($data['money'])<=0)
		{
			$this->error(L("VOUCHER_MONEY_ERROR_TIP"));
		}	
	
		$data['begin_time'] = trim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
		$data['end_time'] = trim($data['end_time'])==''?0:to_timespan($data['end_time']);
		// 更新数据
		$log_info = $data['name'];
		//print_r($data);exit;
		//echo MODULE_NAME;exit;
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
		}
	}
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		//var_dump($vo);exit;
		$vo['begin_time']=$vo['begin_time']?date("Y-m-d H:i:s",$vo['begin_time']):'没有限制';
		$vo['end_time']=$vo['end_time']?date("Y-m-d H:i:s",$vo['end_time']):'没有限制';
		$this->assign ( 'vo', $vo );
		$this->display ();
	}
	
	public function update() {
	
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		//print_r($data);exit;
		$arr=M(MODULE_NAME)->where(array('id'=>$data['id'] ,'reg_send'=>0))->find();//当前数组为是
		
		$reg_num=M(MODULE_NAME)->where(array('reg_send'=>'1'))->count();//为是的数量
		if($data['reg_send']&&!empty($arr)&&$reg_num)//确保唯一性
		{
			$this->error("只能有一个注册就送",0,'$log_info.L("UPDATE_FAILED")');
		}
		
				//print_r($data);exit;
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['name']))
		{
			$this->error(L("VOUCHER_NAME_EMPTY_TIP"));
		}	
		if(doubleval($data['money'])<=0)
		{
			$this->error(L("VOUCHER_MONEY_ERROR_TIP"));
		}	
	
		$data['begin_time'] = trim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
		$data['end_time'] = trim($data['end_time'])==''?0:to_timespan($data['end_time']);
		// 更新数据
		//print_r($data);exit;
		
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			//成功提示
			M("Ecv")->where("ecv_type_id=".$data['id'])->setField("use_limit",$data['use_limit']);  //同步可用次数
			M("Ecv")->where("ecv_type_id=".$data['id'])->setField("begin_time",$data['begin_time']);
			M("Ecv")->where("ecv_type_id=".$data['id'])->setField("end_time",$data['end_time']);
			M("Ecv")->where("ecv_type_id=".$data['id'])->setField("money",$data['money']);
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("UPDATE_FAILED"),0);
			$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
		}
	}
	public function delete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				if(M("Ecv")->where(array ('ecv_type_id' => array ('in', explode ( ',', $id ) ) ))->count()>0)
				{
					$this->error(l("VOUCHER_EXIST"),$ajax);
				}
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
		
				if ($list!==false) {
					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	
	public function send_list()
	{
		$group_list = M("UserGroup")->findAll();
		$this->assign("group_list",$group_list);
		
		//定义条件
		$map[DB_PREFIX.'user.is_delete'] = 0;

		if(intval($_REQUEST['group_id'])>0)
		{
			$map[DB_PREFIX.'user.group_id'] = intval($_REQUEST['group_id']);
		}
		
		if(trim($_REQUEST['user_name'])!='')
		{
			$map[DB_PREFIX.'user.user_name'] = array('like','%'.trim($_REQUEST['user_name']).'%');
		}
		if(trim($_REQUEST['email'])!='')
		{
			$map[DB_PREFIX.'user.email'] = array('like','%'.trim($_REQUEST['email']).'%');
		}
		if(trim($_REQUEST['mobile'])!='')
		{
			$map[DB_PREFIX.'user.mobile'] = array('like','%'.trim($_REQUEST['mobile']).'%');
		}
		if(trim($_REQUEST['pid_name'])!='')
		{
			$pid = M("User")->where("user_name='".trim($_REQUEST['pid_name'])."'")->getField("id");
			$map[DB_PREFIX.'user.pid'] = $pid;
		}
		
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D (User);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		
		$this->display ();
	}
	
	
	
	public function doSend()   //按提交内容不同执行发送代金券，
	{
		include('app/Lib/common.php');
		$user_id = intval($_REQUEST['id']);
		//echo $user_id;exit;
		$voucher_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."ecv_type where `reg_send` = 1");
		//var_dump($voucher_info);exit;
		if(!empty($voucher_info))
			{
			//或者是没有时间限制
				if($voucher_info['end_time']>time()||$voucher_info['end_time']==0){
				//echo 1;exit;
					require_once APP_ROOT_PATH."system/libs/voucher.php";   
					$rs = send_voucher($voucher_info['id'],$user_id,1);   //返回ID
					if($rs){
					
					//发送站内信
					//send_voucher(代金券ID,用户ID,'是否需要密码')
					$voucher_info['end_time']=$voucher_info['end_time']?date("Y-m-d H:i:s",$voucher_info['end_time']):'没有限制';
					$title="获得代金券";
					$content="恭喜你,获得代金券".$voucher_info['name']."到期时间为:".$voucher_info['end_time'];
					
					 send_user_msg($title,$content,0,$user_id,time(),0,true,true);
					
					$msg = sprintf(l("SEND_VOUCHER_PAGE_SUCCESS"));
					$this->success($msg);
					}
				}
			}
		
		
	}
	
	
}
?>