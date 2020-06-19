<?php
namespace Home\Controller;
use Think\Controller;
class UserController extends CommonController {
    public function index(){
        $this->display();
    }
	public function upgrade_account(){
		$User = session("MemberID");
		if(IS_POST){
			$package_type = I("package_type");
			$paypass = I("account_password");
			if($paypass!=$User['paypass']){
				$this->error(L("class-user-error"));
			}
			if(!$PackageType = M("PackageType")->where(array("id"=>$package_type,"delete"=>0))->find()){
				$this->error(L("class-user-packagetype"));
			}
			if(!$UserPackageType = M("PackageType")->where(array("id"=>$User['package_type']))->find()){
				$this->error(L("class-user-packagetype"));
			}
			if($PackageType['price']<$UserPackageType['price']){
				$this->error(L("public-system-error"));
			}else{
				$price = $PackageType['price']-$UserPackageType['price'];
			}
			if($PackageType['id']<=$User['package_type']){
				$this->error(L("public-system-error"));
			}
			$PriceInfo = M("PriceInfo")->where(array("delete"=>0,"member_id"=>$User['id']))->order("id desc")->find();
			if($PriceInfo["new_price"]>=$price){
				$PriceData = array(
					'member_id'=>$User['id'],
					'member_username'=>$User['username'],
					'type'=>3,
					'new_price'=>$PriceInfo['new_price'] - $price,
					'old_price'=>$PriceInfo['new_price'],
					'title'=>"原点升级",
					'title_en'=>"Upgrade",
					'title_sp'=>"Actualización",
					'price'=>$price,
					'addtime'=>date("Y-m-d H:i:s",time()),
				);
				if($PriceInfoResult = M("PriceInfo")->data($PriceData)->add()){
					$order_number = "";
					while(true){
						$arr = getOrderNumber();
						if($arr['result']){
							$order_number = $arr['order_number'];
							break;
						}
					}
					$OrderData = array(
						"order_number"=>$order_number,
						'member_id'=>$User['id'],
						'member_username'=>$User['username'],
						'type'=>3,
						'price_info_id'=>$PriceInfoResult,
						'title'=>"原点升级",
						'title_en'=>"Upgrade",
						'title_sp'=>"Actualización",
						'price'=>$price,
						'addtime'=>date("Y-m-d H:i:s",time()),
					);
					if($OrderInfo = M("OrderInfo")->data($OrderData)->add()){
						if(M("Member")->where(array("id"=>$User['id']))->data(array("package_type"=>$PackageType['id']))->save()){
								
							if($recom_member = M("Member")->where(array("id"=>$User['r_id'],"username"=>$User['r_username']))->find()){
								$order_money_number = "";
								while(true){
									$arr = getMoneyNumber();
									if($arr['result']){
										$order_money_number = $arr['order_number'];
										break;
									}
								}
								$recom_package = M("PackageType")->where(array("id"=>$recom_member["package_type"]))->find();
								
								//推荐奖
								$DEFAULT_WEB_SET_SPONSOR = (float)C("DEFAULT_WEB_SET_SPONSOR");		
								$SET_SPONSOR = $DEFAULT_WEB_SET_SPONSOR*100;									
								$recom_PriceMoneyInfo = M("PriceMoneyInfo")->where(array("member_id"=>$recom_member['id']))->order("id desc")->find();
								
								$money_data = array(
									"type"=>2,
									"member_id"=>$recom_member['id'],
									"member_username"=>$recom_member['username'],
									"order_id"=>$order_money_number,
									"title"=>$User['username']." 升级,推荐奖".$price*$recom_package['recom_reward']."的".$SET_SPONSOR."%转为现金币",
									"title_en"=>$User['username']." Upgrade, Referral Bonus".$price*$recom_package['recom_reward']." ".$SET_SPONSOR."%transfered into Cash Coin",
									"title_sp"=>$User['username']." Actualización，Premio de recomendación".$price*$recom_package['recom_reward']."El ".$SET_SPONSOR."% de ha cambiado en monedas de efectivo",
									"new_price"=>$recom_PriceMoneyInfo['new_price']+$price*$recom_package['recom_reward']*$DEFAULT_WEB_SET_SPONSOR,
									"old_price"=>$recom_PriceMoneyInfo['new_price'],
									"price"=>$price*$recom_package['recom_reward']*$DEFAULT_WEB_SET_SPONSOR,
									"adddate"=>date("Y-m-d",time()),
									"addtime"=>date("Y-m-d H:i:s",time()+1),
								);
								M("PriceMoneyInfo")->data($money_data)->add();
								
								$DEFAULT_WEB_SET_MATCH = (float)C("DEFAULT_WEB_SET_MATCH");
								$SET_MATCH = $DEFAULT_WEB_SET_MATCH *100;
								$recom_PriceInfo = M("PriceInfo")->where(array("member_id"=>$recom_member['id']))->order("id desc")->find();
								
								$price_data = array(
									"type"=>5,
									"member_id"=>$recom_member['id'],
									"member_username"=>$recom_member['username'],
									"order_id"=>"",
									"title"=>$User['username']." 升级,推荐奖".$price*$recom_package['recom_reward']."的".$SET_MATCH."%转为注册币",
									"title_en"=>$User['username']." Upgrade, Referral Bonus".$price*$recom_package['recom_reward']." ".$SET_MATCH."%transfered into Register Coin",
									"title_sp"=>$User['username']." Actualización, Premio de recomendación".$price*$recom_package['recom_reward']."El ".$SET_MATCH."%de está transferido a monedas de registro",
									"new_price"=>$recom_PriceInfo['new_price']+$price*$recom_package['recom_reward']*$DEFAULT_WEB_SET_MATCH,
									"old_price"=>$recom_PriceInfo['new_price']+$price*$recom_package['recom_reward']*$DEFAULT_WEB_SET_MATCH,
									"price"=>$price*$recom_package['recom_reward']*$DEFAULT_WEB_SET_MATCH,
									"adddate"=>date("Y-m-d",time()),
									"addtime"=>date("Y-m-d H:i:s",time()+1),
								);
								M("PriceInfo")->data($price_data)->add();
								
								//金额动态
								$log_data = array(
									"type"=>5,
									"member_id"=>$recom_member['id'],
									"member_username"=>$recom_member['username'],
									"change_id"=>$User['id'],
									"change_username"=>$User['username'],
									"order_id"=>$order_money_number,
									"title"=>$User['username']."(".$recom_package['title'].") 升级,您获得 推荐奖 $".$price*$recom_package['recom_reward']*$DEFAULT_WEB_SET_SPONSOR,
									"title_en"=>$User['username']."(".$recom_package['en_title'].") Upgrade, you get Referral Bonus $". sprintf("%.2f",$price*$recom_package['recom_reward']*$DEFAULT_WEB_SET_SPONSOR),
									"title_sp"=>$User['username']."(".$recom_package['sp_title'].") Actualización, logra Premio de recomendación $". sprintf("%.2f",$price*$recom_package['recom_reward']*$DEFAULT_WEB_SET_SPONSOR),
									"adddate"=>date("Y-m-d",time()),
									"addtime"=>date("Y-m-d H:i:s",time()),
								);
								M("ChangeLog")->data($log_data)->add();
								//金额累计
								$ChangeLogInfo = M("ChangeLogInfo")->where(array("adddate"=>date("Y-m-d",time()),"member_id"=>$recom_member['id']))->find();
								if($ChangeLogInfo){
									$log_data_info = array(
										"recom_price"=>$ChangeLogInfo['recom_price']+$price*$recom_package['recom_reward']*$DEFAULT_WEB_SET_SPONSOR,
									);
									M("ChangeLogInfo")->where(array("adddate"=>date("Y-m-d",time()),"member_id"=>$recom_member['id']))->data($log_data_info)->save();
								}else{
									$log_data_info = array(
										"member_id"=>$recom_member['id'],
										"member_username"=>$recom_member['username'],
										"recom_price"=>$price*$recom_package['recom_reward']*$DEFAULT_WEB_SET_SPONSOR,
										"adddate"=>date("Y-m-d",time()),
										"addtime"=>date("Y-m-d H:i:s",time()+2),
									);
									M("ChangeLogInfo")->data($log_data_info)->add();
								}
							}

							//间点奖
									
							$MemberList = M("Member")->field("id,username,package_type,p_id,p_username")->select();
							
							$dot_list = member_tree($MemberList,$User['p_id']);

							foreach($dot_list as $val){
								
								$top_package = M("PackageType")->where(array("id"=>$val['package_type']))->find();
								
								if($top_package['dot_layer']>=$val['lv']){
									
									$order_money_number = "";
									
									while(true){
										$arr = getMoneyNumber();
										if($arr['result']){
											$order_money_number = $arr['order_number'];
											break;
										}
									}
									
									$top_PriceMoneyInfo = M("PriceMoneyInfo")->where(array("member_id"=>$val['id']))->order("id desc")->find();
									
									$money_data = array(
										"type"=>2,
										"member_id"=>$val['id'],
										"member_username"=>$val['username'],
										"order_id"=>$order_money_number,
										"title"=>$User['username']." 升级,见点奖".$price*$top_package['dot_ratio']."的".$SET_SPONSOR."%转为现金币",
										"title_en"=>$User['username']." Upgrade, POS Bonus".$price*$top_package['dot_ratio']." ".$SET_SPONSOR."% transfered into Cash Coin.",
										"title_sp"=>$User['username']." Actualización, Premio de proposición".$price*$top_package['dot_ratio']."El ".$SET_SPONSOR."% de ha cambiado en monedas de efectivo",
										"new_price"=>$top_PriceMoneyInfo['new_price']+$price*$top_package['dot_ratio']*$DEFAULT_WEB_SET_SPONSOR,
										"old_price"=>$top_PriceMoneyInfo['new_price'],
										"price"=>$price*$top_package['dot_ratio']*$DEFAULT_WEB_SET_SPONSOR,
										"adddate"=>date("Y-m-d",time()),
										"addtime"=>date("Y-m-d H:i:s",time()+2),
									);
									M("PriceMoneyInfo")->data($money_data)->add();											
									
									$top_PriceInfo = M("PriceInfo")->where(array("member_id"=>$val['id']))->order("id desc")->find();
									
									$price_data = array(
										"type"=>5,
										"member_id"=>$val['id'],
										"member_username"=>$val['username'],
										"order_id"=>"",
										"title"=>$User['username']." 升级,见点奖".$price*$top_package['dot_ratio']."的".$SET_MATCH."%转为注册币",
										"title_en"=>$User['username']." Upgrade, POS Bonus".$price*$top_package['dot_ratio']." ".$SET_MATCH."% transfered into Register Coin",
										"title_sp"=>$User['username']." Actualización, Premio de proposición".$price*$top_package['dot_ratio']."El ".$SET_MATCH."% de está transferido a monedas de registro",
										"new_price"=>$top_PriceInfo['new_price']+$price*$top_package['dot_ratio']*$DEFAULT_WEB_SET_MATCH,
										"old_price"=>$top_PriceInfo['new_price'],
										"price"=>$price*$top_package['dot_ratio']*$DEFAULT_WEB_SET_MATCH,
										"adddate"=>date("Y-m-d",time()),
										"addtime"=>date("Y-m-d H:i:s",time()+2),
									);
									M("PriceInfo")->data($price_data)->add();
									
									//金额动态
									$log_data = array(
										"type"=>6,
										"member_id"=>$val['id'],
										"member_username"=>$val['username'],
										"change_id"=>$User['id'],
										"change_username"=>$User['username'],
										"price"=>$price*$top_package['dot_ratio']*$DEFAULT_WEB_SET_MATCH,
										"order_id"=>$order_money_number,
										"title"=>$User['username']."(".$top_package['title'].") 升级,您获得 见点奖 $". sprintf("%.2f",$price*$top_package['dot_ratio']*$DEFAULT_WEB_SET_MATCH),
										"title_en"=>$User['username']."(".$top_package['en_title'].") Upgrade,you get POS Bonus $". sprintf("%.2f",$price*$top_package['dot_ratio']*$DEFAULT_WEB_SET_MATCH),
										"title_sp"=>$User['username']."(".$top_package['sp_title'].") Actualización,logra Premio de proposición $". sprintf("%.2f",$price*$top_package['dot_ratio']*$DEFAULT_WEB_SET_MATCH),
										"adddate"=>date("Y-m-d",time()),
										"addtime"=>date("Y-m-d H:i:s",time()+1),
									);
									M("ChangeLog")->data($log_data)->add();
									
									//金额累计
									$ChangeLogInfo = M("ChangeLogInfo")->where(array("adddate"=>date("Y-m-d",time()),"member_id"=>$val['id']))->find();
									if($ChangeLogInfo){
										$log_data_info = array(
											"dot_price"=>$ChangeLogInfo['dot_price']+$price*$top_package['dot_ratio']*$DEFAULT_WEB_SET_MATCH,
										);
										M("ChangeLogInfo")->where(array("adddate"=>date("Y-m-d",time()),"member_id"=>$val['id']))->data($log_data_info)->save();
									}else{
										$log_data_info = array(
											"member_id"=>$val['id'],
											"member_username"=>$val['username'],
											"dot_price"=>$price*$top_package['dot_ratio']*$DEFAULT_WEB_SET_MATCH,
											"adddate"=>date("Y-m-d",time()),
											"addtime"=>date("Y-m-d H:i:s",time()+2),
										);
										M("ChangeLogInfo")->data($log_data_info)->add();
									}
									
								}
							}

							
							$TOUCH_DAY = C("DEFAULT_WEB_TOUCH_DAY");
							$FREE_DAY = $TOUCH_DAY+C("DEFAULT_WEB_FREE_DAY");
							$share_data = array(
								"type"=>1,
								"order_number"=>$order_number,
								"member_id"=>$User['id'],
								"member_username"=>$User['username'],
								"pay_price"=>$price*$PackageType['matching_ratio'],
								"title"=>"认购",
								"title_en"=>"Register",
								"title_sp"=>"Subscripción",
								"touch_datetime"=>date("Y-m-d",strtotime("+".$TOUCH_DAY." days")),
								"free_datetime"=>date("Y-m-d",strtotime("+".$FREE_DAY." days")),
								"adddate"=>date("Y-m-d",time()),
								"addtime"=>date("Y-m-d H:i:s",time()),
							);
							M("ShareOrderInfo")->data($share_data)->add();
							$this->success(L("class-user-accountok"));	
						}else{
							M("OrderInfo")->where(array("id"=>$OrderInfo))->delete();
							M("PriceInfo")->where(array("id"=>$PriceInfoResult))->delete();
							$this->success(L("class-user-accountno"));	
						}
					}else{
						M("PriceInfo")->where(array("id"=>$PriceInfoResult))->delete();
						$this->error(L("class-user-togenerateorder"));
					}
				}else{
					$this->error(L("class-user-registerconsumption"));
				}

			}else{
				$this->error(L("class-user-registerinsufficient"));
			}
		}else{
			$this->package = M("PackageType")->order("id")->select();
			$this->display();
		}
	}
	public function profile(){
		$this->package = M("PackageType")->order("id")->select();
		$this->display();
	}
	
	public function security(){
		if(IS_POST){
			$verification = I("number");
			$User = session("MemberID");
			$Verify = M("Verify")->where(array("type"=>1,"member_id"=>$User['id'],"status"=>0))->order("id desc")->find();
			if($verification==$Verify['number']){
				M("Verify")->where(array("type"=>1,"member_id"=>$User['id'],"number"=>$Verify['number']))->data(array("status"=>1))->save();
				session("verify_code",$Verify);
				session("varify_addtime",$Verify['addtime']);
				$this->AjaxReturn(array("result"=>true,"message"=>""));
			}else{
				$this->AjaxReturn(array("result"=>false,"message"=>L("public-code-error")));
			}
		}else{
			$Verify = session("verify_code");
			if($Verify){
				if(date("Y-m-d H:i:s",strtotime($Verify['addtime'])+60*60)>date("Y-m-d H:i:s",time())){								
					$this->display("User/update_security");		
				}else{
					session("verify_code",null);
					$this->error(L("class-user-identifyingcodeoverdue"),U("Home/User/security"));
				}
			}else{
				$this->display();
			}
		}
		
	}
	public function bank_info(){
		$Verify = session("bank_code");
		if(IS_POST){
			if($Verify){
				if(date("Y-m-d H:i:s",strtotime($Verify['addtime'])+60*60)>date("Y-m-d H:i:s",time())){
					$Member = D("MemberBank");
					if(!$Member->create()){
						$this->error($Member->getError());
					}else{
						$result = $Member->save();
						if($result||$result==0){
							$this->success(L("class-user-success"));
						}else{
							$this->error(L("class-user-failed"));
						}
					}
				}else{
					session("bank_code",null);
					$this->error(L("class-user-identifyingcodeoverdue"),U("Home/User/bank"));
				}
			}else{
				session("bank_code",null);
				$this->error(L("class-user-identifyingcodeoverdue"),U("Home/User/bank"));
			}
			
		}else{		
			if($Verify){
				if(date("Y-m-d H:i:s",strtotime($Verify['addtime'])+60*60)>date("Y-m-d H:i:s",time())){								
					$this->display("User/bank_info");		
				}else{
					session("bank_code",null);
					$this->error(L("class-user-identifyingcodeoverdue"),U("Home/User/bank"));
				}
			}else{
				session("bank_code",null);
				$this->error(L("class-user-identifyingcodeuse"),U("Home/User/bank"));
			}
		}
	}
	public function bank(){
		if(IS_POST){
			$verification = I("number");
			$User = session("MemberID");
			$Verify = M("Verify")->where(array("type"=>2,"member_id"=>$User['id'],"status"=>0))->order("id desc")->find();
			if($verification==$Verify['number']){
				M("Verify")->where(array("type"=>2,"member_id"=>$User['id'],"number"=>$Verify['number']))->data(array("status"=>1))->save();
				session("bank_code",$Verify);
				$this->AjaxReturn(array("result"=>true,"message"=>""));
			}else{
				$this->AjaxReturn(array("result"=>false,"message"=>L("public-code-error")));
			}
		}else{
			$Verify = session("bank_code");
			if($Verify){
				if(date("Y-m-d H:i:s",strtotime($Verify['addtime'])+60*60)>date("Y-m-d H:i:s",time())){								
					$this->display("User/bank_info");		
				}else{
					session("bank_code",null);
					$this->error(L("class-user-identifyingcodeoverdue"),U("Home/User/bank"));
				}
			}else{
				$this->display();
			}
		}
	}
	public function update_security(){
		$Verify = session("verify_code");
		if($Verify){
			if(date("Y-m-d H:i:s",strtotime($Verify['addtime'])+60*60)>date("Y-m-d H:i:s",time())){								
				$this->display();		
			}else{
				session("verify_code",null);
				$this->error(L("class-user-identifyingcodeoverdue"),U("Home/User/security"));
			}
		}else{
			session("verify_code",null);
			$this->error(L("class-user-identifyingcodeuse"),U("Home/User/security"));
		}			
	}
}