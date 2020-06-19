<?php
namespace Admin\Model;
use Think\Model;
class AddDigitPriceModel extends Model{
	protected $tableName = "digit_price";
	protected $_validate = array(
		array('price','require','价格必须！'),
		array('date','require','时间必须！'),
		array('price','currency','价格应为金额！'),
		array('open','require','open必须！'),
		array('low','require','low必须！'),
		array('high','require','high必须！'),
		array('close','require','close必须！'),
		array('volume','require','volume必须！'),
		array('date','','时间已经存在！',0,'unique',1),
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