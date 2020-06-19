<?php
namespace Admin\Model;
use Think\Model;
class AddSplitModel extends Model{
	protected $tableName = "split";
	protected $_validate = array(

		array('status',array(1,0),'是否开启范围不正确！',2,'in'),		
	);
}
?>