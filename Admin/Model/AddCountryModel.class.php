<?php
namespace Admin\Model;
use Think\Model;
class AddCountryModel extends Model{
	protected $tableName = "country";
	protected $_validate = array(
		array('cn_name','require','中文名称必须！'),
		array('en_name','require','英文名称必须！'),
		array('sort','number','排序应为数字！'),
		array('status',array(1,0),'是否开启范围不正确！',2,'in'),		
	);
}
?>