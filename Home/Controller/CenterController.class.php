<?php
namespace Home\Controller;
use Think\Controller;
class CenterController extends CommonController {
    public function index(){
        $this->display();
    }
	public function activation(){
		$User = session("MemberID");
		if(IS_POST){
			$paypass = I("account_password");
			$out_price = I("price",0,"float");
			if($out_price % 10 != 0){
				$this->error(L('class-center-multipleten'));
			}
			if($out_price<=0){
				$this->error(L('class-center-amountoferror'));
			}
			if($paypass!=$User['paypass']){
				$this->error(L('class-center-error'));
			}
			//查询会员
			if(!$Member = M("Member")->Where(array("username"=>I("username"),"delete"=>0))->find()){
				$this->error(L('class-center-transfertheuser'));
			}
			if($Member['id']==$User['id']){
				$this->error(L('class-center-nouser'));
			}
			if($out_price>0){
				//查询 转出用户 金额
				$PriceInfo = M("PriceInfo")->where(array("delete"=>0,"member_id"=>$User['id']))->order("id desc")->find();
				if(!$PriceInfo){
					$this->error(L("class-center-Lackofbalance"));
				}
				if($out_price>$PriceInfo['new_price']){
					$this->error(L("class-center-Lackofbalance"));
				}
				//查询 接收用户 金额
				$MemberPriceInfo = M("PriceInfo")->where(array("delete"=>0,"member_id"=>$Member['id']))->order("id desc")->find();
				if(!$MemberPriceInfo){
					$MemberPriceInfo['new_price'] = 0;
				}
				$PriceData = array(
					'member_id'=>$User['id'],
					'member_username'=>$User['username'],
					'type'=>4,
					'new_price'=>$PriceInfo['new_price'] - $out_price,
					'old_price'=>$PriceInfo['new_price'],
					'title'=>"转出到 ".I('username'),
					'title_en'=>"Roll out to ".I('username'),
					'title_sp'=>"Roll out to ".I('username'),
					'price'=>$out_price,
					'addtime'=>date("Y-m-d H:i:s",time()),
				);			
				if ($resultNum = M("PriceInfo")->data($PriceData)->add()){						
					$MemberPriceData = array(
						'member_id'=>$Member['id'],
						'member_username'=>$Member['username'],
						'type'=>5,
						'new_price'=>$MemberPriceInfo['new_price'] + $out_price,
						'old_price'=>$MemberPriceInfo['new_price'],
						'title'=>"从 ".$User['username']." 转入",
						'title_en'=>"from ".$User['username']." To change into",
						'title_sp'=>"from ".$User['username']." To change into",
						'price'=>$out_price,
						'addtime'=>date("Y-m-d H:i:s",time()),
					);
					if ($results = M("PriceInfo")->data($MemberPriceData)->add()){
						$this->success(L("class-center-transfersuccess"));
					}else{
						$this->error(L("class-center-intoerror"));
					}
				}else{
					$this->error(L("class-center-turnouterror"));
				}
				
			}else{
				$this->error(L("class-center-turnouto"));
			}
			
		}else{
			$this->all_in_price = M("PriceInfo")->where(array("type"=>array("in","1,5"),"member_id"=>$User['id']))->sum("price");
			$this->all_out_price = M("PriceInfo")->where(array("type"=>array("in","2,3,4"),"member_id"=>$User['id']))->sum("price");
			$this->PriceInfoList = M("PriceInfo")->where(array("delete"=>0,"member_id"=>$User['id']))->select();
			$this->today_gift = M("PriceInfo")->where(array("delete"=>0,"member_id"=>$User['id'],"type"=>6,"adddate"=>date("Y-m-d",time())))->count();
			$ShareOrderInfo = M("ShareOrderInfo")->field("touch_datetime")->where(array("delete"=>0,"member_id"=>$User['id']))->order("id")->find();
			if(time()>strtotime($ShareOrderInfo['touch_datetime'])){
				$this->show = 0;
			}else{
				$this->show = 1;
			}

			$this->title=L('acl-activation');
			if(ismobile()){
				$this->theme('erci')->display();
			}
			else{
				$this->display();
			}
		}
	}
	public function expenditure(){
		$User = session("MemberID");
		$this->all_in_price = M("PriceInfo")->where(array("type"=>array("in","1,5"),"member_id"=>$User['id']))->sum("price");
		$this->all_out_price = M("PriceInfo")->where(array("type"=>array("in","2,3,4"),"member_id"=>$User['id']))->sum("price");
		$this->PriceInfoList = M("PriceInfo")->where(array("delete"=>0,"member_id"=>$User['id'],"type"=>array("in","4,5")))->select();
		$this->display();
	}
	public function coin(){
		$User = session("MemberID");
		$this->all_in_price = M("PriceMoneyInfo")->where(array("type"=>array("in","1,2,5,6"),"member_id"=>$User['id']))->sum("price");
		$this->all_out_price = M("PriceMoneyInfo")->where(array("type"=>array("in","3"),"member_id"=>$User['id']))->sum("price");
		$this->all_turn_price = M("PriceMoneyInfo")->where(array("type"=>array("in","4"),"member_id"=>$User['id']))->sum("price");
		$this->PriceMoneyInfoList = M("PriceMoneyInfo")->where(array("delete"=>0,"member_id"=>$User['id']))->select();
		$this->zore = "0.00";
		$this->title = "我的奖金币";
		if(ismobile()){
			$this->theme('erci')->display();
		}
		else{
			$this->display();
		}
	}
	public function coin_deal(){
		$User = session("MemberID");
		$this->all_in_price = M("PriceMoneyInfo")->where(array("type"=>array("in","1,2,5,6"),"member_id"=>$User['id']))->sum("price");
		$this->all_out_price = M("PriceMoneyInfo")->where(array("type"=>array("in","3"),"member_id"=>$User['id']))->sum("price");
		$this->all_turn_price = M("PriceMoneyInfo")->where(array("type"=>array("in","4"),"member_id"=>$User['id']))->sum("price");
		$this->PriceMoneyInfoList = M("PriceMoneyInfo")->where(array("type"=>array("in","3,5,6"),"delete"=>0,"member_id"=>$User['id']))->select();
		$this->zore = "0.00";
		$this->display();
	}
	public function coin_to_reg(){
		
		$User = session("MemberID");

		$paypass = I("account_password");
		$out_price = I("price",0,"float");
		
		if($out_price<=0){
			$this->error(L("class-center-amountof"));
		}

		if($out_price%50>0){
			$this->error("转换数量必须是50的倍数！");
		}

		if($paypass!=$User['paypass']){
			$this->error(L("class-center-error"));
		}
		if($out_price>0){
			//查询 转出用户 US积分
			$PriceIntegralInfo = M("PriceIntegralInfo")->where(array("delete"=>0,"member_id"=>$User['id']))->order("id desc")->find();
			if(!$PriceIntegralInfo){
				$this->error(L("class-center-userror"));
			}
			if($out_price>$PriceIntegralInfo['new_number']){
				$this->error(L("class-center-userror"));
			}
			//查询 接收用户 注册币金额
			$MemberPriceInfo = M("PriceInfo")->where(array("delete"=>0,"member_id"=>$User['id']))->order("id desc")->find();
			if(!$MemberPriceInfo){
				$MemberPriceInfo['new_price'] = 0;
			}
			$order_number = "";
			while(true){
				$arr = getOrderNumber();
				if($arr['result']){
					$order_number = $arr['order_number'];
					break;
				}
			}
			$PriceData = array(
				'member_id'=>$User['id'],
				'member_username'=>$User['username'],
				'order_id'=>$order_number,
				'type'=>4,
				'new_number'=>$PriceIntegralInfo['new_number'] - $out_price,
				'old_number'=>$PriceIntegralInfo['new_number'],
				'title'=>"分红币转注册币",
				'title_en'=>"US integral to register the currency",
				'title_sp'=>"US integral to register the currency",
				'number'=>$out_price,
				'addtime'=>date("Y-m-d H:i:s",time()),
			);			
			if ($resultNum = M("PriceIntegralInfo")->data($PriceData)->add()){						
				$MemberPriceData = array(
					'member_id'=>$User['id'],
					'member_username'=>$User['username'],
					'type'=>5,
					'new_price'=>$MemberPriceInfo['new_price'] + $out_price,
					'old_price'=>$MemberPriceInfo['new_price'],
					'title'=>"分红币转注册币",
					'title_en'=>"US integral to register the currency",
					'title_sp'=>"US integral to register the currency",
					'price'=>$out_price,
					'addtime'=>date("Y-m-d H:i:s",time()),
				);
				if ($results = M("PriceInfo")->data($MemberPriceData)->add()){
					$this->success(L("class-center-ustoregisteredok"));
				}else{
					$this->error(L("class-center-conversionfailed"));
				}
			}else{
				$this->error(L("class-center-transferfailed"));
			}
			
		}else{
			$this->error(L("class-center-converttheamount"));
		}
	}
	
	public function reward(){
		$User = session("MemberID");
		$PriceInfo = M("PriceIntegralInfo")->where(array("delete"=>0,"member_id"=>$User['id']))->order("id desc")->find();
		$this->PriceInfo = $PriceInfo;
		$this->all_price = M("PriceIntegralInfo")->where(array("delete"=>0,"member_id"=>$User['id'],"type"=>array("in","1,2")))->sum("number");
		$this->all_out_price = M("PriceIntegralInfo")->where(array("delete"=>0,"member_id"=>$User['id'],"type"=>array("in","3,4")))->sum("number");
		$this->PriceIntegralInfo = M("PriceIntegralInfo")->where(array("delete"=>0,"member_id"=>$User['id']))->select();
		$this->zore = "0.00";
		$this->title = "分红币";
		if(ismobile()){
			$this->theme('erci')->display();
		}
		else{
			$this->display();
		}
	}
	public function buy_price(){
		if(IS_POST){
			$User = session("MemberID");		
			$PriceMoneyInfo = M("PriceMoneyInfo")->where(array("delete"=>0,"member_id"=>$User['id']))->order("id desc")->find();
			$order_number = "";
			while(true){
				$arr = getMoneyNumber();
				if($arr['result']){
					$order_number = $arr['order_number'];
					break;
				}
			}
			$price = I("buy_coin_value");
			$PriceData = array(
				'member_id'=>$User['id'],
				'member_username'=>$User['username'],
				'type'=>6,
				'order_id'=>$order_number,
				'new_price'=>$PriceMoneyInfo['new_price'],
				'old_price'=>$PriceMoneyInfo['old_price'],
				'title'=>"购买现金币",
				'title_en'=>"Enable Buy CC",
				'title_sp'=>"Enable Buy CC",
				'status'=>0,
				'price'=>$price,
				'change_price'=>$price,
				"adddate"=>date("Y-m-d"),
				'addtime'=>date("Y-m-d H:i:s",time()),
			);
			if($PriceInfoResult = M("PriceMoneyInfo")->data($PriceData)->add()){
				$DEFAULT_WEB_BUY_MONEY_PRICE = (float)C("DEFAULT_WEB_BUY_MONEY_PRICE");
				$DEFAULT_WEB_US_TO_CHINA = (float)C("DEFAULT_WEB_US_TO_CHINA");
				$data = array(
					"order_number"=>$order_number,
					"member_id"=>$User['id'],
					"member_username"=>$User['username'],
					"member_telephone"=>$User['telephone'],
					"money_id"=>$PriceInfoResult,
					"price"=>$price,
					"price_turn"=>$DEFAULT_WEB_US_TO_CHINA,
					"system_price"=>$price*$DEFAULT_WEB_BUY_MONEY_PRICE,
					"title"=>"购买现金币",
					'title_en'=>"Enable Buy CC",
					'title_sp'=>"Enable Buy CC",
					"status"=>0,
					"adddate"=>date("Y-m-d"),
					"addtime"=>date("Y-m-d H:i:s",time()),
				);
				if(M("ServerBuyPrice")->data($data)->add()){
					$this->success(L("class-center-applicationissuccessful"));
				}else{
					$this->error(L("class-center-buyerror"));
				}
			}else{
				$this->error(L("class-center-buyerror"));
			}
		}else{
			$this->error(L("class-center-buyerror"));
		}
	}
	public function coin_turn(){
		
		$User = session("MemberID");

		$paypass = I("account_password");
		$out_price = I("price",0,"float");
		if($out_price % 50 != 0){
			$this->error(L("class-center-multiplefive"));
		}
		if($out_price<=0){
			$this->error(L("class-center-cashno"));
		}
		if($paypass!=$User['paypass']){
			$this->error(L("class-user-error"));
		}
		if($out_price>0){
			//查询 转出用户 现金金额
			$PriceMoneyInfo = M("PriceMoneyInfo")->where(array("delete"=>0,"member_id"=>$User['id']))->order("id desc")->find();
			if(!$PriceMoneyInfo){
				$this->error(L("class-center-cashno"));
			}
			if($out_price>$PriceMoneyInfo['new_price']){
				$this->error(L("class-center-cashno"));
			}
			//查询 接收用户 注册币金额
			$MemberPriceInfo = M("PriceInfo")->where(array("delete"=>0,"member_id"=>$User['id']))->order("id desc")->find();
			if(!$MemberPriceInfo){
				$MemberPriceInfo['new_price'] = 0;
			}
			$order_number = "";
			while(true){
				$arr = getMoneyNumber();
				if($arr['result']){
					$order_number = $arr['order_number'];
					break;
				}
			}
			$PriceData = array(
				'member_id'=>$User['id'],
				'member_username'=>$User['username'],
				'order_id'=>$order_number,
				'type'=>4,
				'new_price'=>$PriceMoneyInfo['new_price'] - $out_price,
				'old_price'=>$PriceMoneyInfo['new_price'],
				'title'=>"现金币转注册币",
				'title_en'=>"CC Transfers RC",
				'title_sp'=>"CC Transfers RC",
				'price'=>$out_price,
				'addtime'=>date("Y-m-d H:i:s",time()),
			);			
			if ($resultNum = M("PriceMoneyInfo")->data($PriceData)->add()){						
				$MemberPriceData = array(
					'member_id'=>$User['id'],
					'member_username'=>$User['username'],
					'type'=>5,
					'new_price'=>$MemberPriceInfo['new_price'] + $out_price,
					'old_price'=>$MemberPriceInfo['new_price'],
					'title'=>"现金币转注册币",
					'title_en'=>"CC Transfers RC",
					'title_sp'=>"CC Transfers RC",
					'price'=>$out_price,
					'addtime'=>date("Y-m-d H:i:s",time()),
				);
				if ($results = M("PriceInfo")->data($MemberPriceData)->add()){
					$this->success(L("class-center-cashregisteredok"));
				}else{
					$this->error(L("class-center-conversionfailed"));
				}
			}else{
				$this->error(L("class-center-transferfailed"));
			}
			
		}else{
			$this->error(L("class-center-converttheamount"));
		}
	
	}
	public function sell_coin(){
		if(IS_POST){
			$User = session("MemberID");
			if(empty($User['bank'])||empty($User['bank_account'])||empty($User['bank_account_name'])){
				$this->success(L("class-center-bankcardinformation"),U("Home/User/bank"));
			}
			$price = I("sell_amount");
			$DEFAULT_WEB_MONEY_PRICE_DEFAULT = (int)C("DEFAULT_WEB_MONEY_PRICE_DEFAULT");
			if($price % $DEFAULT_WEB_MONEY_PRICE_DEFAULT != 0){
				$this->error(L("class-center-pleaseenterthe").$DEFAULT_WEB_MONEY_PRICE_DEFAULT.L("class-center-multiple"));
			}
			if($price<=0){
				$this->error(L("class-center-amountoferror"));
			}
			$paypass = I("sell_account_password");
			if($paypass!=$User['paypass']){
				$this->error(L("class-Stock-error"));
			}
			
			$PriceMoneyInfo = M("PriceMoneyInfo")->where(array("delete"=>0,"member_id"=>$User['id']))->order("id desc")->find();
			if($PriceMoneyInfo["new_price"]>=$price){
				$order_number = "";
				while(true){
					$arr = getMoneyNumber();
					if($arr['result']){
						$order_number = $arr['order_number'];
						break;
					}
				}
				$PriceData = array(
					'member_id'=>$User['id'],
					'member_username'=>$User['username'],
					'type'=>3,
					'order_id'=>$order_number,
					'new_price'=>$PriceMoneyInfo['new_price'] - $price,
					'old_price'=>$PriceMoneyInfo['new_price'],
					'title'=>"售出现金币",
					'title_en'=>"Sold CC",
					'title_sp'=>"Sold CC",
					'change_price'=>$price,
					'status'=>0,
					'price'=>$price,
					"adddate"=>date("Y-m-d"),
					'addtime'=>date("Y-m-d H:i:s",time()),
				);
				if($PriceInfoResult = M("PriceMoneyInfo")->data($PriceData)->add()){
					$OrderData = array(
						'member_id'=>$User['id'],
						'member_username'=>$User['username'],
						'type'=>3,
						'price_info_id'=>$PriceInfoResult,
						'title'=>"售出现金币",
						'title_en'=>"Sold CC",
						'title_sp'=>"Sold CC",
						'price'=>$price,
						'addtime'=>date("Y-m-d H:i:s",time()),
					);
					if($OrderInfo = M("OrderInfo")->data($OrderData)->add()){
						$DEFAULT_WEB_MONEY_PRICE_PROCEDURES = (float)C("DEFAULT_WEB_MONEY_PRICE_PROCEDURES");
						$DEFAULT_WEB_US_TO_CHINA = (float)C("DEFAULT_WEB_US_TO_CHINA");
						$data = array(
							"order_number"=>$order_number,
							"member_id"=>$User['id'],
							"member_username"=>$User['username'],
							"member_telephone"=>$User['telephone'],
							"member_bank_card"=>$User['bank_account'],
							"member_bank"=>$User['bank'],
							"member_bank_name"=>$User['bank_account_name'],
							"member_zhifubao"=>$User['zhifubao'],
							"member_bitcoin"=>$User['bitcoin'],
							"member_paypal"=>$User['paypal'],
							"money_id"=>$PriceInfoResult,
							'title'=>"售出现金币",
							'title_en'=>"Sold CC",
							'title_sp'=>"Sold CC",
							"price"=>$price,
							"price_turn"=>$DEFAULT_WEB_US_TO_CHINA,
							"system_price"=>$price*$DEFAULT_WEB_MONEY_PRICE_PROCEDURES,
							"status"=>0,
							"adddate"=>date("Y-m-d",time()),
							"addtime"=>date("Y-m-d H:i:s",time()),
						);
						M("ServerOutPrice")->data($data)->add();
						$this->success(L("class-center-sllcashok"));	
					}else{
						M("PriceInfo")->where(array("id"=>$PriceInfoResult))->delete();
						$this->error(L("class-center-sllcashno"));
					}
				}else{
					$this->error(L("class-center-sllcashno"));
				}

			}else{
				$this->error(L("class-center-cashbalance"));
			}

		}else{
			$this->error(L("class-center-sllerror"));
		}
	}
	public function dynamic_profit(){
		$User = session("MemberID");
		$this->log_list = M("ChangeLogInfo")->where(array("member_id"=>$User['id']))->select();
		$recom_price = M("ChangeLogInfo")->where(array("member_id"=>$User['id']))->sum("recom_price");
		$dot_price = M("ChangeLogInfo")->where(array("member_id"=>$User['id']))->sum("dot_price");
		$touch_price = M("ChangeLogInfo")->where(array("member_id"=>$User['id']))->sum("touch_price");
		$leader_price = M("ChangeLogInfo")->where(array("member_id"=>$User['id']))->sum("leader_price");
		$this->recom_price = $recom_price;
		$this->dot_price = $dot_price;
		$this->touch_price = $touch_price;
		$this->leader_price = $leader_price;
		$this->zore = "0.00";
		$this->all_price = $recom_price+$dot_price+$touch_price+$leader_price;
		$this->title = "奖金收益";
		if(ismobile()){
			$this->theme('erci')->display();
		}
		else{
			$this->display();
		}
	}
	public function show_buy(){
		$User = session("MemberID");
		if($MoneyInfo = M("PriceMoneyInfo")->where(array("id"=>I("id"),"member_id"=>$User['id']))->find()){		
			$this->ServerBuyPrice = M("ServerBuyPrice")->where(array("id"=>$MoneyInfo['server_buy_id']))->find();
			$ServerOutPrice = M("ServerOutPrice")->where(array("id"=>$MoneyInfo['server_sell_id']))->find();
			$member = M("Member")->find($ServerOutPrice['member_id']);
			$this->Package = M("PackageType")->where(array("id"=>$member['package_type']))->find();
			$this->ServerOutPrice = $ServerOutPrice;
			
			$this->MoneyInfo = $MoneyInfo;
			
		}
		
		$this->display();
	}
	public function show_sell(){
		$User = session("MemberID");
		if($MoneyInfo = M("PriceMoneyInfo")->where(array("id"=>I("id"),"member_id"=>$User['id']))->find()){
			$this->ServerOutPrice = M("ServerOutPrice")->where(array("id"=>$MoneyInfo['server_sell_id']))->find();
			$ServerBuyPrice = M("ServerBuyPrice")->where(array("id"=>$MoneyInfo['server_buy_id']))->find();
			$member = M("Member")->find($ServerBuyPrice['member_id']);
			$this->Package = M("PackageType")->where(array("id"=>$member['package_type']))->find();
			$this->ServerBuyPrice = $ServerBuyPrice;
			$this->MoneyInfo = $MoneyInfo;
		}
		$this->display();
	}
	public function confirm_sell(){
		$id = I("id");
		if(IS_POST){
			$User = session("MemberID");
			$paypass = I("payment_account_password");
			if($paypass!=$User['paypass']){
				$this->error(L("class-user-error"));
			}else{
				$ServerOutPrice = M("ServerOutPrice")->where(array("id"=>$id))->find();
				if($ServerOutPrice['status']==2){
					if($Money = M("PriceMoneyInfo")->where(array("id"=>$ServerOutPrice['money_id']))->find()){
						M("PriceMoneyInfo")->where(array("server_buy_id"=>$Money['server_buy_id']))->data(array("status"=>3,'touch_time'=>null))->save();
						M("PriceMoneyInfo")->where(array("server_sell_id"=>$Money['server_sell_id']))->data(array("status"=>3,'touch_time'=>null))->save();
						M("ServerOutPrice")->where(array("id"=>$Money['server_sell_id']))->data(array("status"=>3))->save();
						M("ServerBuyPrice")->where(array("id"=>$Money['server_buy_id']))->data(array("status"=>3))->save();
						$ServerBuyPrice = M("ServerBuyPrice")->where(array("id"=>$Money['server_buy_id']))->find();
						$PriceMoneyInfo = M("PriceMoneyInfo")->where(array("delete"=>0,"member_id"=>$ServerBuyPrice['member_id']))->order("id desc")->find();
						$order_number = "";
						while(true){
							$arr = getMoneyNumber();
							if($arr['result']){
								$order_number = $arr['order_number'];
								break;
							}
						}
						$PriceData = array(
							'new_price'=>$PriceMoneyInfo['new_price']+$ServerBuyPrice['price'],
							'change_price'=>$ServerBuyPrice['price'],
							'touch_time'=>null,
						);
						if($PriceInfoResult = M("PriceMoneyInfo")->where(array("id"=>$PriceMoneyInfo['id']))->data($PriceData)->save()){
							$this->success(L("class-center-slleok"));
						}
						
					}else{
						$this->error(L("class-center-matchingerror"));
					}
					
				}else{
					$this->error(L("public-system-error"));
				}
			}
		}else{
			if(!$this->ServerOutPrice = M("ServerOutPrice")->where(array("id"=>$id))->find()){
				$this->error(L("public-system-error"));
			}
			$this->display();
		}
		
		
	}
	public function confirm_buy(){
		$id = I("id");
		if(IS_POST){
			$ServerBuyPrice = M("ServerBuyPrice")->where(array("id"=>$id))->find();
			if($ServerBuyPrice['status']==1){
				if($Money = M("PriceMoneyInfo")->where(array("id"=>$ServerBuyPrice['money_id']))->find()){
					$remark = I("payment_name");
					M("PriceMoneyInfo")->where(array("id"=>$ServerBuyPrice['money_id']))->data(array("status"=>2,"touch_time"=>null))->save();
					$ServerOutPrice = M("ServerOutPrice")->where(array("id"=>$Money['server_sell_id']))->find();
					M("PriceMoneyInfo")->where(array("id"=>$ServerOutPrice['money_id']))->data(array("status"=>2,"touch_time"=>date("Y-m-d H:i:s",time())))->save();
					M("ServerOutPrice")->where(array("id"=>$Money['server_sell_id']))->data(array("status"=>2,"remark"=>$remark))->save();
					M("ServerBuyPrice")->where(array("id"=>$Money['server_buy_id']))->data(array("status"=>2))->save();
					$SMS_URL = C("DEFAULT_WEB_SMS_URL");
					$SMS_ACCOUNT = C("DEFAULT_WEB_SMS_ACCOUNT");
					$SMS_KEY = C("DEFAULT_WEB_SMS_KEY");
					$SMS_PORT = C("DEFAULT_WEB_SMS_PORT");
					$SMS_NUMBER = C("DEFAULT_WEB_SMS_NUMBER");
					$SMS_REMARK = C("DEFAULT_WEB_SMS_REMARK");
					$SMS_SELL = C("DEFAULT_WEB_SMS_SELL");
					$result = send_sms($SMS_URL,$SMS_ACCOUNT,$SMS_KEY,$SMS_PORT,$ServerOutPrice['member_telephone'],$SMS_SELL,'');
					if($result['result']){
						$this->success(L("class-center-identifysuccessful"));
					}else{
						$this->error(L("public-system-error"));
					}
								
				}else{
					$this->error(L("class-center-matchingerror"));
				}		
			}else{
				$this->success(L("class-center-paymenthasbeen"));
			}
		}else{
			if(!$this->ServerBuyPrice = M("ServerBuyPrice")->where(array("id"=>$id))->find()){
				$this->error(L("public-system-error"));
			}
			$this->display();
		}
	}
	public function getGift(){
		
		$MONEY_MIN = (float)C("DEFAULT_WEB_MONEY_MIN")*100;
		$MONEY_MAX = (float)C("DEFAULT_WEB_MONEY_MAX")*100;
		$price = rand($MONEY_MIN,$MONEY_MAX)/100;
		
		$User = session("MemberID");
		$ShareOrderInfo = M("ShareOrderInfo")->field("touch_datetime")->where(array("delete"=>0,"member_id"=>$User['id']))->order("id")->find();
		if(time()>strtotime($ShareOrderInfo['touch_datetime'])){
			$this->AjaxReturn(array("error"=>L("class-center-packageoverdue")));
		}
		if(M("PriceInfo")->where(array("delete"=>0,"member_id"=>$User['id'],"type"=>6,"adddate"=>date("Y-m-d",time())))->find()){
			$this->AjaxReturn(array("error"=>L("class-center-packageoverdue")));
		}else{			
			$MemberPriceInfo = M("PriceInfo")->where(array("delete"=>0,"member_id"=>$User['id']))->order("id desc")->find();				
			$MemberPriceData = array(
				'member_id'=>$User['id'],
				'member_username'=>$User['username'],
				'type'=>6,
				'new_price'=>$MemberPriceInfo['new_price'] + $price,
				'old_price'=>$MemberPriceInfo['new_price'],
				'title'=>"新手礼包",
				'title_en'=>"Gift Pack",
				'title_sp'=>"Gift Pack",
				'price'=>$price,
				"adddate"=>date("Y-m-d",time()),
				'addtime'=>date("Y-m-d H:i:s",time()),
			);
			if ($results = M("PriceInfo")->data($MemberPriceData)->add()){
				$this->AjaxReturn(array("gift_amount"=>$price));
			}else{
				$this->AjaxReturn(array("error"=>L("public-system-error")));
			}
		}	
		
	}
}