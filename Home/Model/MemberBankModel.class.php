<?php
namespace Home\Model;
use Think\Model;
class MemberBankModel extends Model{
	protected $tableName = "member";
	protected $_validate = array(
		//array('bank_account_name','require','持卡人姓名必须！'),
		//array('bank','require','银行名称必须！'),
		array('bitcoin','require','钱包地址必填！'),		
	);
	protected $_auto = array(
		array('id','getMemberID',3,'callback'),
	);
	function getMemberID(){
		$User = session("MemberID");
		return $User['id'];
	}
}
?>