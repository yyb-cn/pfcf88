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
	
/*抽奖记录*/	
	function award_log(){
		$group_list = M("UserGroup")->findAll();
		$this->assign("group_list",$group_list);
		$condition=1;
		if(trim($_REQUEST['user_name'])!='')
		{
			$condition = "and u.user_name like"."'%".trim($_REQUEST['user_name'])."%'";
		}
		if(trim($_REQUEST['user_name'])!='')
		{
			$condition = " u.user_name like"."'%".trim($_REQUEST['user_name'])."%'";
		}
		if(trim($_REQUEST['group_id']!=''))
			{
				if($_REQUEST['group_id']==0){
				$condition .='';
				}
			else{
			$condition .= "  and   u.group_id ="."'".$_REQUEST['group_id']."'";
			}
		}
		//排序
		if(trim($_REQUEST['_sort'])==0){
			$sort='desc';
		
		}
		elseif(trim($_REQUEST['_sort'])==1){
			$sort='asc';
		
		}
		if(trim($_REQUEST['_order'])=='user_name')
		{
			$order=	"order  by  u.user_name  ".$sort;
		}
		if(trim($_REQUEST['_order'])=='group_id')
		{
			$order=	"order  by  u.group_id  ".$sort;
		}
		if(trim($_REQUEST['_order'])=='log_time')
		{
			$order=	"order  by  a.log_time  ".$sort;
		}
		
		$module=m('award_log');		
		import('ORG.Util.Page');// 导入分页类
		$count  = $module->query( "select  count(*) as count from ".DB_PREFIX."award_log a left join ".DB_PREFIX."user as u on a.user_id = u.id   where ".$condition);
		$count=$count[0]['count'];
		// 查询满足要求的总记录数
		$per_page=$_REQUEST['per_page']?$_REQUEST['per_page']:30;
			
		$Page   = new Page($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
		$show   = $Page->show();// 分页显示输出
		$this->assign('page',$show);// 赋值分页输出
		$sql = "select u.user_name,u.group_id,u.real_name,p.name as prize_name,a.*  from ".DB_PREFIX."award_log a left join ".DB_PREFIX."user as u on a.user_id = u.id  left join ".DB_PREFIX."prize as p on p.id = a.prize_id  where ".$condition .' '. $order . ' limit '.$Page->firstRow.','.$Page->listRows ;
		$list = $GLOBALS['db']->getAll($sql);
		$this->assign('list',$list);
		$this->display ();
	}
	//这个是添加一次抽奖机会
	function send(){
		$user_id=$_REQUEST['id'];
		
		$gg=D(lottery)->where(array('uid'=>$user_id))->find();
		if($gg){
		$one=D(lottery)->where(array('uid'=>$user_id))->setInc('draw_sec');
		}
		else{
		$one=D(lottery)->add(array('uid'=>$user_id,'draw_sec'=>1));
		}
		if($one)
		$this->success(L("增加一次抽奖机会"));
	}
	//这个是奖品的目录
	function prize(){
		$list=D(prize)->select();
		$this->assign('list',$list);
		$this->display ();
	}
	
}
?>