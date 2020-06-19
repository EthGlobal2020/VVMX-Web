<?php
namespace Home\Controller;
use Think\Controller;
class MainController extends CommonController {
	public function index(){
		$Member = session("MemberID");
		
		$new_time = date("Y-m-d H:i:s",time());
		
		$this->NewDigitPrice = M("DigitPrice")->where(array("date"=>date("Y-m-d",time())))->find();
			
		$cold_share_price = M("ShareOrderInfo")->where(array("member_id"=>$Member['id'],"status"=>1))->sum("pay_number");
		
		$this->cold_share_price = $cold_share_price?$cold_share_price:0;
		
		$Info = M("ShareNumberInfo")->where(array("member_id"=>$Member['id']))->order("id desc")->find();
		
		$this->free_share_price = $Info['new_number']?$Info['new_number']:0;
		
		$this->all_share_price =  sprintf("%1.2f",($Info['new_number']+$cold_share_price)*$NewDigitPrice["price"]);
		
		$this->PriceIntegralInfo = M("PriceIntegralInfo")->where(array("delete"=>0,"member_id"=>$Member['id']))->order("id desc")->find();
		
		$this->zore = "0.000";
		
		$this->SplitList = M("Split")->where(array("delete"=>0))->order("id desc")->find();
		
		$this->SplitCount = M("Split")->where(array("delete"=>0))->count();
		
		$this->change_log = M("ChangeLog")->where(array("member_id"=>$Member['id']))->order("id desc")->limit(6)->select();
		
		$recom_price = M("ChangeLogInfo")->where(array("member_id"=>$Member['id']))->sum("recom_price");
		$dot_price = M("ChangeLogInfo")->where(array("member_id"=>$Member['id']))->sum("dot_price");
		$touch_price = M("ChangeLogInfo")->where(array("member_id"=>$Member['id']))->sum("touch_price");
		$leader_price = M("ChangeLogInfo")->where(array("member_id"=>$Member['id']))->sum("leader_price");
		
		$this->recom_price = $recom_price;
		
		$this->dot_price = $dot_price;
		
		$this->touch_price = $touch_price;
		
		$this->leader_price = $leader_price;
		
		$this->zore2 = "0.00";
		
		$this->all_price = $recom_price+$dot_price+$touch_price+$leader_price;
		
		$this->display();
	}
	public function getHistory(){
		
		$DigitPrice = M("DigitPrice")->where(array("date"=>array('elt',date('Y-m-d',time()))))->order("date desc")->limit(16)->select();
		krsort($DigitPrice);
		$data = array();
		foreach($DigitPrice as $val){
			$data[] = array(date("m.d",strtotime($val['date'])),$val['price']);
		}
		$this->AjaxReturn($data);
		
	}
}
