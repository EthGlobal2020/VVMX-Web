<?php
namespace Home\Controller;
use Think\Controller;
class StockController extends CommonController {
    public function index(){
        $this->display();
    }
	public function my_share(){
		$Member = session("MemberID");
		$new_time = date("Y-m-d H:i:s",time());
		//$NewDigitPrice = M("DigitPrice")->where(array("date"=>date("Y-m-d",time())))->find();
		$this->zore = "0.000";
		
		//$ShareList = M("ShareOrderInfo")->where(array("type"=>array("in","1,2,3"),"member_id"=>$Member['id'],"delete"=>0))->order("id desc")->select();
		
		//$cold_share_price = M("ShareOrderInfo")->where(array("member_id"=>$Member['id'],"status"=>1))->sum("pay_number");
		
		//$this->cold_share_price = $cold_share_price?$cold_share_price:0;
		
		//$this->all_sell_share_price = M("ShareOrderInfo")->where(array("member_id"=>$Member['id'],"type"=>2))->sum("pay_price");
		
		$Info = M("ShareNumberInfo")->where(array("member_id"=>$Member['id']))->order("id desc")->select();
		
		
		//$this->free_share_price = $Info['new_number']?$Info['new_number']:0;
		
		//$this->all_share_price =  sprintf("%1.2f",($Info['new_number']+$cold_share_price)*$NewDigitPrice["price"]);
		
		//$this->acl_price = $cold_share_price+$Info['new_number'];
		$this->zore = "0.00";
		//$this->NewDigitPrice = $NewDigitPrice;
		$this->ShareList = $Info;
		$this->new_time = $new_time;
		if(ismobile()){
			$this->theme('erci')->display();
		}
		else{
			$this->display();
		}
	}
	public function reinvest(){
		$Member = session("MemberID");
		$ShareList = M("ShareOrderInfo")->where(array("type"=>array("in","3,4"),"member_id"=>$Member['id'],"delete"=>0))->select();
		$NewDigitPrice = M("DigitPrice")->where(array("date"=>date("Y-m-d",time())))->find();
		$this->wait_reinvest_price = M("ShareOrderInfo")->where(array("status"=>array("in","0,1"),"reinvest_id"=>1,"member_id"=>$Member['id'],"delete"=>0))->sum("pay_price");
		$over_reinvest_number = M("ShareOrderInfo")->where(array("status"=>4,"reinvest_id"=>0,"member_id"=>$Member['id'],"delete"=>0))->sum("pay_number");
		$this->zore_price = "0.00";
		$this->zore = "0.000";
		$this->NewDigitPrice = $NewDigitPrice;
		$this->all_number = $over_reinvest_number*$NewDigitPrice['price'];
		$this->over_reinvest_number = $over_reinvest_number;
		$this->ShareList = $ShareList;
		$this->new_time = date("Y-m-d H:i:s",time());
		$this->display();
	}
	public function out_share(){
		if(IS_POST){
			$Member = session("MemberID");
			$paypass = I("account_password");
			$sell_price = I("sell_amount");
			if($paypass!=$Member['paypass']){
				$this->error(L("class-Stock-error"));
			}
			//当前优速币价
			$NewDigitPrice = M("DigitPrice")->where(array("date"=>date("Y-m-d",time())))->find();
			if($NewDigitPrice['price']>0){
				$price = $NewDigitPrice['price'];
			}else{
				$price = C("DEFAULT_WEB_SHARE_PRICE_NOHARE");
			}
			$PackageType = M("PackageType")->where(array("id"=>$Member['package_type']))->find();
			
			$Info = M("ShareNumberInfo")->where(array("member_id"=>$Member['id']))->order("id desc")->find();
			if($sell_price<($Info['new_number']*25/100)){
				$this->error(L("stock-reinvest-7"));
			}
			
			//手续费
			$DEFAULT_WEB_SET_T_RETURN = (float)C("DEFAULT_WEB_SET_T_RETURN");
			//现金币转化率
			$DEFAULT_WEB_SET_B_RETURN = (float)C("DEFAULT_WEB_SET_B_RETURN");
			//复投转化率
			$DEFAULT_WEB_SET_R_RETURN = (float)C("DEFAULT_WEB_SET_R_RETURN");
			//us积分
			$DEFAULT_WEB_SET_G_RETURN = C("DEFAULT_WEB_SET_G_RETURN");
			//转出股票
			$out_price = $sell_price*$price*(1-$DEFAULT_WEB_SET_T_RETURN);
			
			$all_sell_share_price = M("ShareOrderInfo")->where(array("member_id"=>$Member['id'],"type"=>2))->sum("pay_price");
			if($PackageType['status_profit_max']*6<=($all_sell_share_price+$sell_price)){
				$sell_price = $PackageType['status_profit_max']*6-$all_sell_share_price;	
			}
				
			
			if($Info['new_number']>=$sell_price){
								
				$PriceData = array(
					'member_id'=>$Member['id'],
					'member_username'=>$Member['username'],
					'type'=>2,
					'new_number'=>$Info['new_number'] - $sell_price,
					'old_number'=>$Info['new_number'],
					'title'=>"复投",
					"title_en"=>"Reinvest",
					"title_sp"=>"Reinversión",
					'price'=>$sell_price,
					"adddate"=>date("Y-m-d",time()),
					'addtime'=>date("Y-m-d H:i:s",time()),
				);
				if($PriceInfoResult = M("ShareNumberInfo")->data($PriceData)->add()){
					$order_number2 = "";
					while(true){
						$arr2 = getOrderNumber();
						if($arr2['result']){
							$order_number2 = $arr2['order_number'];
							break;
						}
					}
					$share_data = array(
						"type"=>2,
						"order_number"=>$order_number2,
						"member_id"=>$Member['id'],
						"member_username"=>$Member['username'],
						"pay_number"=>$sell_price,
						"share_price"=>$price,
						"title"=>"售出",
						"title_en"=>"Sell",
						"title_sp"=>"Sell",
						"pay_price"=>round($sell_price*$price,2),
						"status"=>4,
						"adddate"=>date("Y-m-d",time()),
						"addtime"=>date("Y-m-d H:i:s",time()),
					);
					M("ShareOrderInfo")->data($share_data)->add();
					
					$order_number = "";
					while(true){
						$arr = getOrderNumber();
						if($arr['result']){
							$order_number = $arr['order_number'];
							break;
						}
					}
					$TOUCH_DAY = C("DEFAULT_WEB_TOUCH_DAY");
					$FREE_DAY = $TOUCH_DAY+C("DEFAULT_WEB_FREE_DAY");
					$share_data = array(
						"type"=>3,
						"order_number"=>$order_number,
						"member_id"=>$Member['id'],
						"member_username"=>$Member['username'],
						"pay_price"=>round($out_price*$DEFAULT_WEB_SET_R_RETURN,2),
						"reinvest_id"=>1,
						"title"=>"复投",
						"title_en"=>"Reinvest",
						"title_sp"=>"Reinversión",
						"touch_datetime"=>date("Y-m-d",strtotime("+".$TOUCH_DAY." days")),
						"free_datetime"=>date("Y-m-d",strtotime("+".$FREE_DAY." days")),
						"adddate"=>date("Y-m-d",time()),
						"addtime"=>date("Y-m-d H:i:s",time()),
					);
					M("ShareOrderInfo")->data($share_data)->add();
					$money_number = "";
					while(true){
						$arr = getMoneyNumber();
						if($arr['result']){
							$money_number = $arr['order_number'];
							break;
						}
					}
					//现金币
					$PriceMoneyInfo = M("PriceMoneyInfo")->where(array("delete"=>0,"member_id"=>$Member['id']))->order("id desc")->find();
					$money_pay = array(
						"type"=>2,
						'member_id'=>$Member['id'],
						'member_username'=>$Member['username'],
						"order_id"=>$money_number,
						'title'=>"卖出优速币转现金币",
						'title_en'=>"Sell shares transfer cash",
						'title_sp'=>"Sell shares transfer cash",
						'new_price'=>$PriceMoneyInfo['new_price'] + $out_price*$DEFAULT_WEB_SET_B_RETURN,
						'old_price'=>$PriceMoneyInfo['new_price'],
						"price"=>$out_price*$DEFAULT_WEB_SET_B_RETURN,
						"adddate"=>date("Y-m-d",time()),
						"addtime"=>date("Y-m-d H:i:s",time()),
					);
					M("PriceMoneyInfo")->data($money_pay)->add();
					//us积分
					$PriceIntegralInfo = M("PriceIntegralInfo")->where(array("delete"=>0,"member_id"=>$Member['id']))->order("id desc")->find();
					$Integral_Info = array(
						"type"=>2,
						'member_id'=>$Member['id'],
						'member_username'=>$Member['username'],
						"order_id"=>$order_number,
						'title'=>"售出优速币转化为US积分",
						'title_en'=>"Converted to US shares sold",
						'title_sp'=>"Converted to US shares sold",
						'new_number'=>$PriceIntegralInfo['new_number'] + $out_price*$DEFAULT_WEB_SET_G_RETURN,
						'old_number'=>$PriceIntegralInfo['new_number'],
						"number"=>$out_price*$DEFAULT_WEB_SET_G_RETURN,
						"adddate"=>date("Y-m-d",time()),
						"addtime"=>date("Y-m-d H:i:s",time()),
					);
					M("PriceIntegralInfo")->data($Integral_Info)->add();
					if($PackageType['status_profit_max']*6<=($all_sell_share_price+$sell_price)){
						$sell_price = $PackageType['status_profit_max']*6-$all_sell_share_price;
						$NewPriceData = array(
							'member_id'=>$Member['id'],
							'member_username'=>$Member['username'],
							'type'=>2,
							'new_number'=>0,
							'old_number'=>0,
							'title'=>"复投上线",
							"title_en"=>"Reinvest MAX",
							"title_sp"=>"Reinversión MAX",
							'price'=>0,
							"adddate"=>date("Y-m-d",time()),
							'addtime'=>date("Y-m-d H:i:s",time()),
						);
						M("ShareNumberInfo")->where(array("member_id"=>$Member['id']))->data($NewPriceData)->save();
					}
					$this->success(L("class-Stock-sllsharesus"));
				}else{
					$this->error(L("class-Stock-sllok"));
				}
				
			}else{
				$this->error(L("class-Stock-equityownership"));
			}
		}else{
			$this->error(L("public-system-error"));
		}
	}
	public function split(){
		$this->SplitList = M("Split")->where(array("delete"=>0))->select();
		$this->display();
	}
}