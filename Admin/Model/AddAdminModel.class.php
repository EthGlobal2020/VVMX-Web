<?php
namespace Admin\Model;
use Think\Model;
class AddAdminModel extends Model{
	protected $tableName = "Admin";
	protected $_validate = array(
		array('username','require','帐号必须！'),
		array('userpass','require','密码必须！'),
		array('repass','userpass','确认密码不正确',0,'confirm'), // 验证确认密码是否和密码一致
		array('username','','帐号名称已经存在！',0,'unique',1), // 在新增的时候验证name字段是否唯一
		array('sex',array(1,0),'性别范围不正确！',2,'in'),
		array('status',array(1,0),'是否开启范围不正确！',2,'in'),		
	);
	protected $_auto = array(
		array('userpass','md5',3,'function') ,
	);
}
?>