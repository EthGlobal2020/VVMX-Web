<?php
namespace Admin\Model;
use Think\Model;
class SetMemberModel extends Model{
	protected $tableName = "member";
	protected $_validate = array(
		array('paypass','repaypass','支付密码不一致',0,'confirm'), // 验证确认密码是否和密码一致
		array('dgcpass','redgcpass','支付密码不一致',0,'confirm'), // 验证确认密码是否和密码一致
	);
	protected $_auto = array(
		array('paypass','getPayPass',3,'callback') ,
		array('dgcpass','getDgcPass',3,'callback') ,
	);
	function getPayPass(){
		$paypass = I("paypass","");
		if($paypass==""){
			$paypass = I("old_paypass","");
		}
		return $paypass;
		//return I("paypass","","md5");
	}
	function getDgcPass(){
		$dgcpass = I("dgcpass","");
		if($dgcpass==""){
			$dgcpass = I("old_dgcpass","");
		}
		return $dgcpass;
	}
}
?>