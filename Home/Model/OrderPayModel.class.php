<?php
namespace Home\Model;
use Think\Model;
class OrderPayModel extends Model{
	protected $tableName = "price_info";
	protected $_validate = array(
		array('price','require','金额必须！'),
		array('price_type','require','类型必须！'),
		array('price_type',array(0,1,2,3,5,21),'类型范围不正确！',2,'in'),
	);
	protected $_auto = array(
		array("addtime","getTime",3,"callback"),
		array('adddate','getDate',3,'callback') ,
		array('member_id','getMemberId',3,'callback') ,
		array('member_username','getMemberUsername',3,'callback') ,
		//array('type',2),
	);
	function getTime(){
		return date("Y-m-d H:i:s",time());
	}
	function getDate(){
		return date("Y-m-d",time());
	}
	function getMemberId(){
		$Member = session("MemberID");
		return $Member['id'];
	}
	function getMemberUsername(){
		$Member = session("MemberID");
		return $Member['username'];
	}
}
?>