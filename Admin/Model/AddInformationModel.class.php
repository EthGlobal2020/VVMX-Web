<?php
namespace Admin\Model;
use Think\Model;
class AddInformationModel extends Model{
	protected $tableName = "information";
	protected $_validate = array(
		array('title','require','标题必须！'),
		array('sort','number','排序应为数字！'),
		array('recom',array(1,0),'推荐范围不正确！',2,'in'),
		array('status',array(1,0),'是否开启范围不正确！',2,'in'),
	);
	protected $_auto = array(
			array("addtime","getTime",3,"callback"),
	);
	function getTime(){
		return date("Y-m-d H:i:s",time());
	}
}
?>