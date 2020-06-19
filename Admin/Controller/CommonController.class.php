<?php
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller {
	Public function _initialize(){
		//phpinfo();exit;
		
		if(!isset($_SESSION[C('USER_AUTH_KEY')])||!isset($_SESSION["AdminID"])) {
			$this->error("请重新登录！",U("Admin/Index/index"));
		}
		$notAuth = in_array(MODULE_NAME, explode(',',C('NOT_AUTH_MODULE'))) || in_array(ACTION_NAME, explode(',', C('NOT_AUTH_ACTION')));
		$Admin = SESSION("AdminID");
		if(C('USER_AUTH_ON' )&& !$notAuth && $Admin['username']!='panjun'){
			$RBAC = new \Org\Util\Rbac();
			$RBAC::AccessDecision() || $this->error('您没有相关权限');
		}
		if(SESSION("AdminID")){
			
			$where = array("id"=>$Admin['id'],"UserName"=>$Admin['username'],"PassWord"=>$Admin['userpass']);
			$result = M("Admin")->where($where)->find();
			if(!$result){
				SESSION("AdminID",null);
				SESSION(C('USER_AUTH_KEY'),null);
				$this->error("请重新登录！",U("Admin/Index/index"));
			}else{
				 SESSION("AdminID",$result);
			}
		}else{
			$this->error("请重新登录！",U("Admin/Index/index"));
		}
		$this->action = ACTION_NAME;
		$this->data = $result;
	}
}