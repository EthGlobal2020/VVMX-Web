<?php
namespace Home\Controller;
use Think\Controller;
class FinanceController extends CommonController {
    public function index(){
        $this->display();
    }
	public function change_log(){
		$Member = session("MemberID");
		$change_log = M("ChangeLog")->where(array("member_id"=>$Member['id']))->order("id desc")->limit(25)->select();
/* 		$arr = array();
		foreach($change_log as $val){
			
			if(!empty($arr[$val['member_id'].$val['change_id']])){
				$arr[$val['member_id'].$val['change_id']]['title'] = $arr[$val['member_id'].$val['change_id']]['title'].$val['title'];
			}else{
				$arr[$val['member_id'].$val['change_id']] = $val;
			}
		} */
		$this->change_log = $change_log;
		$this->display();
	}
}