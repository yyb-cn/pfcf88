<?php

class AwardAction extends CommonAction{
		public function index()
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
				$award_moduel=D('lottery');
				$award_list=$award_moduel->select();
				foreach($voList as $k=>$v){
				
					foreach($award_list as $kk=>$vv){
						if($vv['uid']==$v['id']){
							$voList[$k]['award_number']=$vv['draw_sec'];
						}
						
					}
				
				}
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
	
	public function send()
	{
		$id=intval($_REQUEST['id']);
		//增加一次抽奖机会
		$module=M('lottery');
		$list = $module->where(array('uid'=>$id))->select();
		//新增
		if(empty($list)){
		
			$id= $module->add(array('uid'=>$id,draw_sec=>1));
			if($id){
				$this->success('新增成功');
			}
		}
		else{
			$one=$module->where(array('uid'=>$id))->setInc('draw_sec'); 
			if($one){
			$this->success('新增成功');
			}
		}
		
		
	}
	
}
?>