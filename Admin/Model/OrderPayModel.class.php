<?php
namespace Admin\Model;
use Think\Model;
class OrderPayModel extends Model{
	protected $tableName = "price_info";
	protected $_validate = array(
		array('price','require','金额必须！'),
		//array('price','currency','金额必须是数字格式！'),
		array('price_type','require','充值类型必须！'),
		array('price_type',array(1,3,0),'充值类型范围不正确！',2,'in'),
		array("member_id",'require','充值会员必须！'),
	);
	protected $_auto = array(
		array("addtime","getTime",3,"callback"),
		array('adddate','getDate',3,'callback') ,
		array('admin_id','getAdmin',3,'callback') ,
		array('type',1) ,
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