<?php

namespace Admin\Controller;
use Think\Controller;
Class RbacController extends CommonController{
	
	Public function index(){
		$this->user = D('UserRelation')->relation(true)->field('PassWord',true)->where("MsgDelete = 0")->select();
		$this->display();
	}

	Public function role(){
		$this->role = M('role')->select();
		$this->display();
	}

	Public function node(){
		$node = M('node')->order('sort')->select();
		$this->node = node_merge($node);
		$this->display();
	}
	
	Public function addUserHandle(){
		$user = array(
			'UserName' => I('username'),
			'PassWord' => I('password','','md5'),
			'LoginTime' => time(),
			'AddTime' => date("Y-m-d H:i:s",time()),
			'LoginIP' => get_client_ip()
		);
		
		$role_user = array();

		if($uid = M('Admin')->add($user)){
			foreach ($_POST['role_id'] as $v){
				$role_user[] = array(
					'role_id' => $v,
					'user_id' => $uid
				);
			}
			M('role_user')->addAll($role_user);
			$this->success('添加成功',U('Admin/Rbac/index'));
		}else{
			$this->error('添加失败');
		}
	}

	Public function addRole(){
		$this->display();
	}

	Public function addRoleHandle(){
		if(M('role')->add($_POST)){
			$this->success('添加成功',U('Admin/Rbac/role'));
		}else{
			$this->error('添加失败');
		}
	}

	Public function addNode(){
		$this->pid = I('pid',0,'intval');
		$this->level = I('level',1,'intval');

		switch($this->level){
			case 1:
				$this->type = '应用';
				break;
			case 2:
				$this->type = '控制器';
				break;
			case 3:
				$this->type = '动作方法';
				break;
		}
		$this->display();
	}

	Public function addNodeHanlde(){
		if(M('node')->add($_POST)){
			$this->success('添加成功',U('Admin/Rbac/node'));
		}else{
			$this->error('添加失败');
		}
	}

	Public function access(){
		
		$rid = I('rid',0,'intval');
		$node = M('node')->order('sort')->select();
		$access = M('access')->where(array('role_id' => $rid))->getField('node_id', true);
		$this->node = node_merge($node,$access);
		$this->rid = $rid;
		$this->display();
	}

	Public function setAccess(){
		$rid = I('rid',0,'intval');
		$db = M('access');
		$db->where(array('role_id' => $rid))->delete();

		$data = array();

		foreach($_POST['access'] as $v){
			$tmp = explode('_',$v);
			$data[] = array(
				'role_id' => $rid,
				'node_id' => $tmp[0],
				'level' => $tmp[1]
			);
		}
		if(empty($data)){
			$this->error("未添加任何节点");
		}
		if($db->addAll($data)){
			$this->success('修改成功',U('Admin/Rbac/role'));
		}else{
			$this->error('修改失败');
		}
	}
	public function deleteUser(){
		$id = I('id');
		if($id==1){
			$this->error("管理员帐号不能删除！");
		}
		if(delete($id,'Admin')){
			$this->success('success',U('Admin/Rbac/index'));
		}else{
			$this->error("删除失败");
		}
	}
	
	Public function addUser(){
		$this->role = M('role')->select();
		$this->display();
	}
	
	public function updateUser(){
		if(IS_POST){
			$where['ID'] = I("id");
			$password = I("password");				
			if(empty($password)){
				$this->error("没有填写密码");
			}else{
				$data["PassWord"] = md5($password);
				if(M("Admin")->where($where)->data($data)->save()){
					$this->success("修改成功");
				}else{
					$this->error("修改失败");
				}								
			}		
		}else{
			$where['ID'] = I('id');
			$this->data = M("Admin")->where($where)->find();
			$this->display();
		}
	
	}
	public function deleteNode(){
		if(isset($_GET["ID"])){
			$id = I('ID');
			if(Delete($id,"Node")){	
				$this->success("success",U("Admin/Rbac/node"));
			}else{
				$this->error("删除失败",U("Admin/Rbac/node"));
			}				
		}else{
			$this->error("删除失败");
		}		
	}
	public function updateNode(){
		if(IS_POST){
			$result = M("Node")->data($_POST)->save();
			if($result||$result==0){
				$this->success("success",U("Admin/Rbac/node"));
			}else{
				$this->error("修改失败");
			}
		}else{
			switch(I('level')){
				case 1:
					$this->type = '应用';
					break;
				case 2:
					$this->type = '控制器';
					break;
				case 3:
					$this->type = '动作方法';
					break;
			}		
			$where = array("id"=>I('ID'));
			$this->node = M("Node")->where($where)->find();
			$this->display();
		}
	}	
}
?>