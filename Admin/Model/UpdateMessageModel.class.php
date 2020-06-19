<?php
namespace Admin\Model;
use Think\Model;
class UpdateMessageModel extends Model{
	protected $tableName = "message";
	protected $_validate = array(
		array('title','require','标题必须！'),
		array('type','require','分类必须！'),
		array('status',array(1,0),'是否开启范围不正确！',2,'in'),		
	);
}
?>