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
		if (isset ( $_REQUEST ['_order'] )) {
			$order = $_REQUEST ['_order'];
		} else {
			$order = ! empty ( $sortBy ) ? $sortBy : $model->getPk ();
		}
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = $asc ? 'asc' : 'desc';
		}
		$count = $model->where ( $map )->count ( 'id' );
		
			if ($count > 0) {
				//创建分页对象
				if (! empty ( $_REQUEST ['listRows'] )) {
					$listRows = $_REQUEST ['listRows'];
				} else {
					$listRows = '';
				}
				$p = new Page ( $count, $listRows );
				//分页查询数据

				$voList = $model->where($map)->order( "`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->findAll ( );
				//var_dump($voList);exit;
				$ecv_moduel=D('ecv');
				$ecv_list=$ecv_moduel->join(DB_PREFIX.'ecv_type ON '.DB_PREFIX.'ecv_type.id = '.DB_PREFIX.'ecv.ecv_type_id')->select();
				
				
				//var_dump($ecv_type_list);exit;
				//var_dump($ecv_list);exit;
				foreach($voList as $k=>$v){
				
					foreach($ecv_list as $kk=>$vv){
						if($vv['user_id']==$v['id']){
						$use=$vv['used_yn']?'已用':'未用';
							$voList[$k]['ecvs'].=trim('类型：'.$vv['name'].',&nbsp;&nbsp;面额:'.$vv['money'].'【'.$use.'】<br />');
						}
						
					}
				
				}
				//var_dump($voList[0]['ecvs'][0]);exit;
	//			echo $model->getlastsql();
				//分页跳转的时候保证查询条件
				foreach ( $map as $key => $val ) {
					if (! is_array ( $val )) {
						$p->parameter .= "$key=" . urlencode ( $val ) . "&";
					}
				}
				//分页显示

				$page = $p->show ();
				//列表排序显示
				$sortImg = $sort; //排序图标
				$sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
				$sort = $sort == 'desc' ? 1 : 0; //排序方式
				//模板赋值显示
				
				
				$this->assign ( 'list', $voList );
				$this->assign ( 'sort', $sort );
				$this->assign ( 'order', $order );
				$this->assign ( 'sortImg', $sortImg );
				$this->assign ( 'sortType', $sortAlt );
				$this->assign ( "page", $page );
				$this->assign ( "nowPage",$p->nowPage);
			}
		
		}
		
		$this->display ();
	}
	
	
	
	public function send()   //按提交内容不同执行发送代金券，
	{
		$user_id = intval($_REQUEST['id']);
		$condition['reg_send']=1;
			
		$user=D('user')->where(array('id'=>$user_id))->find();
		
		$this->assign('user',$user);
		$ecv_type_list=D('ecv_type')->where($condition)->select();
			$this->assign ( 'ecv_type_list', $ecv_type_list );
		$this->display();
		
	}
	public function doSend()   //按提交内容不同执行发送代金券，
	{
		if($_POST)
		{
		$ecv_id=$_POST['ecv_id'];
		}
		include('app/Lib/common.php');
		$user_id = intval($_REQUEST['id']);
		$voucher_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."ecv_type where `id` = ".$ecv_id);
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
				else{
				$this->error ('改代金券已经失效');
				}
			}
		
		
	}
	
	
}
?>