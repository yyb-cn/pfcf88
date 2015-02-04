<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class DirectoryAction extends CommonAction{
	public function Directory_index()
	{
		echo '<p style="background:#aaFa00">首页目录role_nav表</p>';
		echo '<a href="?m=Directory&a=add&type=Role_nav">新增</a>';
		echo '<hr/>';
		$list=M('Role_nav')->order('sort asc')->select();
		echo '<table border="1">';
		echo '<tr><th>ID</th><th>名</th><th>排序</th></tr>';
		foreach ($list as $k=>$v){
		echo "<tr><td>".$v['id']."</td><td>".$v['name']."</td><td>".$v['sort']."</td></tr>";
		
		}
		echo '</table>';
			
		echo '<p style="background:#ddFa00">组别role_group表</p>';
		echo '<a href="?m=Directory&a=add&type=Role_group">新增</a>';
		echo '<hr/>';
		$list=M('Role_group')->order('nav_id asc,sort ')->select();
		echo '<table border="1">';
		echo '<tr><th>ID</th><th>名</th><th>排序</th><th>nav_id</th></tr>';
		foreach ($list as $k=>$v){
		echo "<tr><td>".$v['id']."</td><td>".$v['name']."</td><td>".$v['sort']."</td><td>".$v['nav_id']."</td></tr>";
		
		}
		echo '</table>';
		
		echo '<p style="background:#dddd00">类名Role_module</p>';
		echo '<a href="?m=Directory&a=add&type=Role_module">新增</a>';
		echo '<hr/>';
		$list=M('Role_module')->select();
		echo '<table border="1">';
		echo '<tr><th>ID</th><th>module</th><th>名</th></tr>';
		foreach ($list as $k=>$v){
		echo "<tr><td>".$v['id']."</td><td>".$v['module']."</td><td>".$v['name']."</td></tr>";
		
		}
		
		echo '</table>';
		
		echo '<p style="background:#ddffff">动作Role_node</p>';
		echo '<a href="?m=Directory&a=add&type=Role_node">新增</a>';
		echo '<hr/>';
		$list=M('Role_node')->order('group_id')->select();
		echo '<table border="1">';
		echo '<tr><th>ID</th><th>名</th><th>action_name</th><th>group_id</th><th>module_id</th></tr>';
		foreach ($list as $k=>$v){
		echo "<tr><td>".$v['id']."</td><td>".$v['name']."</td><td>".$v['action']."</td><td>".$v['group_id']."</td><td>".$v['module_id']."</td></tr>";
		
		}
		echo '</table>';
		
	}
	public function add()
	{
		$type=$_GET['type'];
		$this->assign('type',$type);
		$this->display();
		
	
	}
	public function doadd()
	{
		$type=$_POST['type'];
		$module=M($type);
		$data['is_delete']=0;	
		$data['is_effect']=1;
		if($type=='Role_nav'){
		$data['name']=$_POST['name'];
		$data['sort']=$_POST['sort'];
		$module->add($data);
		}
		if($type=='Role_group'){
		$data['name']=$_POST['name'];
		$data['nav_id']=$_POST['nav_id'];
		$data['sort']=$_POST['sort'];
		$module->add($data);
		}
		if($type=='Role_module'){
		$data['name']=$_POST['name'];
		$data['module']=$_POST['module'];
		$module->add($data);
		}
		if($type=='Role_node'){
		$data['name']=$_POST['name'];
		$data['group_id']=$_POST['group_id'];
		$data['module_id']=$_POST['module_id'];
		$data['action']=$_POST['action'];
		$module->add($data);
		}
	$this->success('插入成功');
	
	}
}
?>