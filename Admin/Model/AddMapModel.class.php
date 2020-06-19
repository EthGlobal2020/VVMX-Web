<?php
namespace Admin\Model;
use Think\Model;
class AddMapModel extends Model{
	protected $tableName = "server_buy_price";
	protected $_validate = array(
		array('price','require','购入金额必须！'),
		array('number','require','购入数量必须！'),
		array('status',array(1,0),'是否开启范围不正确！',2,'in'),		
	);
	protected $_auto = array(
		array('addtime','getTime',3,'callback'),
		array('adddate','getDate',3,'callback'),
	);
	function getTime(){
		return date("Y-m-d H:s:i",time());
	}
	function getDate(){
		return date("Y-m-d",time());
	}
}
?>