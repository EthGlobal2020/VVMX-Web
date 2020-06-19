<?php
namespace Home\Model;
use Think\Model;
class AddMemberModel extends Model{
	protected $tableName = "member";
	protected $_validate = array(
		array('username','require','帐号必须！'),
		array('telephone','require','手机号码不能为空！'),
		array('userpass','require','密码必须！'),
		array('paypass','require','支付密码必须！'),
		array('username','','帐号名称已经存在！',0,'unique',1), // 在新增的时候验证name字段是否唯一	
		//array('p_id','require','上级ID必须！'),
		//array('p_username','require','上级帐号必须！'),		
	);
	protected $_auto = array(
		array('status',1),
		array('status_login',1),//第一次登陆验证  1表是 第一次
		array('userpass','md5',1,'function') ,
		array('paypass','getPayPass',3,'callback') ,
		array('addtime','getTime',3,'callback'),
		array('adddate','getDate',3,'callback'),
	);
	function getTime(){
		return date("Y-m-d H:s:i",time());
	}
	function getDate(){
		return date("Y-m-d",time());
	}
	function getPayPass(){
		return I("paypass");
	}
}
?>