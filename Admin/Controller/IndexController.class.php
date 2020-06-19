<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        //echo 222;exit;
		$this->display("login");    
    }
	public function login(){
		if(IS_POST){
			
			$username = I("username");
			$userpass = I("userpass");
			$result = M("admin")->where(array("username"=>$username,"userpass"=>md5($userpass)))->find();
			if($result){
				SESSION("AdminID",$result);
				SESSION(C('USER_AUTH_KEY'),$result['id']);
				if($result['username']==C('RBAC_SUPERADMIN')){
					session(C('ADMIN_AUTH_KEY'),true);
				}
				$Rbac = new \Org\Util\Rbac();
				$Rbac::saveAccessList();
				//$this->ajaxReturn("AdminID:".SESSION("AdminID")."，USER_AUTH_KEY：".SESSION(C('USER_AUTH_KEY')));
				$this->ajaxReturn('ddddddddd');
			}else{
				$this->ajaxReturn('ddddddssssssssdddddd');
			}
		}else{
			$this->Url = "http://".$_SERVER['SERVER_NAME'];
			$this->display();
		}
	}
	public function outLogin(){
		SESSION(null);
		$this->success("安全退出",U("Admin/Index/index"));
	}
}




?>