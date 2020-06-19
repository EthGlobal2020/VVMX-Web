<?php
namespace Admin\Model;
use Think\Model;
class AddMemberModel extends Model{
	protected $tableName = "member";
	protected $_validate = array(
		array('username','require','帐号必填！'),
		array('userpass','require','密码必填！'),
		array('repass','userpass','登陆密码不一致',0,'confirm'), // 验证确认密码是否和密码一致
		//array('paypass','repaypass','支付密码不一致',0,'confirm'), // 验证确认密码是否和密码一致
		array('username','','帐号名称已经存在！',0,'unique',1), // 在新增的时候验证name字段是否唯一
		array('telephone','require','手机必填！'),
		array('sex',array(1,0),'性别范围不正确！',2,'in'),
		array('status',array(1,0),'是否开启范围不正确！',2,'in'),		
	);
	protected $_auto = array(
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
		//return I("paypass","","md5");
	}
}
?>