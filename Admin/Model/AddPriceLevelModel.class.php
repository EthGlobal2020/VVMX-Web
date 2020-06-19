<?php
namespace Admin\Model;
use Think\Model;
class AddPriceLevelModel extends Model{
	protected $tableName = "price_level";
	protected $_validate = array(
		array('title','require','标题必须！'),
		array('price','currency','价格应为金额！'),
		array('sort','number','排序应为数字！'),
		array('status',array(1,0),'是否开启范围不正确！',2,'in'),		
	);
}
?>