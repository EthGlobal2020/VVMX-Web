<?php
namespace Admin\Model;
use Think\Model;
class OrderMoneyPayModel extends Model{
	protected $tableName = "price_money_info";
	protected $_validate = array(
		array('price','require','现金币必须！'),
		array('price','currency','现金币必须是金额！'),
		array('price_type','require','充值类型必须！'),
		array('price_type',array(1,2),'充值类型范围不正确！',2,'in'),
		array("member_id",'require','充值会员必须！'),
	);
	protected $_auto = array(
		array("addtime","getTime",3,"callback"),
		array('adddate','getDate',3,'callback') ,
		array('admin_id','getAdmin',3,'callback') ,
		array('type',5),
	);
	function getTime(){
		return date("Y-m-d H:i:s",time());
	}
	function getDate(){
		return date("Y-m-d",time());
	}
	function getAdmin(){
		$Member = session("MemberID");
		return $Member['id'];
	}
}
?>