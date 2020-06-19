<?php
namespace Admin\Model;
use Think\Model;
class AddPackageTypeModel extends Model{
	protected $tableName = "package_type";
	protected $_validate = array(
		array('title','require','标题必须！'),
		array('type','require','分类必须！'),
		array('price','currency','价格应为金额！'),
/* 		array('dgc_price','currency','DGC价格应为金额！'),
		array('bv_price','currency','BV价格应为金额！'),
		array('sort','number','排序应为数字！'),
		array('percent','currency','购币百分比必须小数！'), */
		array('status',array(1,0),'是否开启范围不正确！',2,'in'),		
	);
}
?>