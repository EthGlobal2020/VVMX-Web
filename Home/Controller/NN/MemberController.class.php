<?php
namespace Home\Controller;
use Think\Controller;
class MemberController extends CommonController {
    public function index(){
        $this->display();
    }
	public function register(){
		$language = session("language");
		if(IS_POST){			
			$Member = session("MemberID");
			$package_type = I("package_type");
			if(!$PackageType = M("PackageType")->where(array("id"=>$package_type))->find()){
				$this->error(L("class-member-packagetype"));
			}
			$r_username = I("recommended_name");
			$p_id = I("p_id");
			$p_username = I("parent_name");
			$position = I("reg_position");
			$telephone = I("telephone");
			if(M("Member")->where(array("telephone"=>$telephone))->count()>3){
				$this->error(L("class-member-telephone"));
			}
			$date = date("Y-m-d",time());
			$time = date("Y-m-d H:i:s",time());
			if($position==1){
				if(!$left_member = M("Member")->where(array("p_id"=>$p_id,"position"=>0))->find()){
					$this->error(L("class-member-memberleft"));
				}
			}
			if(!$recom_member = M("member")->where(array("username"=>$r_username))->find()){
				$this->error(L("class-member-username"));
			}	
			if(!$p_member = M("member")->where(array("id"=>$p_id,"username"=>$p_username))->find()){
				$this->error(L("class-member-contact"));
			}
			if(M("member")->where(array("p_id"=>$p_id,"p_username"=>$p_username,"position"=>$position))->count()>0){
				$this->error(L("class-member-error"));
			}
			$PriceInfo = M("PriceInfo")->where(array("member_id"=>$Member['id']))->order("id desc")->find();
			if($PackageType['price']<=$PriceInfo['new_price']){
				$_POST['new_price'] = $PriceInfo['new_price']-$PackageType['price'];
				$_POST['price'] = $PackageType['price'];
				$_POST['old_price'] = $PriceInfo['new_price'];
				$_POST['title'] = I("username")." 注册账号支出";
				$_POST['title_en'] = I("username")." Register";
				$_POST['title_sp'] = I("username")."  gastos para el registro de cuenta";
				$OrderPay = D("OrderPay");
				if (!$OrderPay->create()){
					$this->error($OrderPay->getError(),U('Home/Member/register'));
				}else{
					$result = $OrderPay->add();
					if($result){
						$order_number = "";
						while(true){
							$arr = getOrderNumber();
							if($arr['result']){
								$order_number = $arr['order_number'];
								break;
							}
						}
						$data = array(
							"order_number"=>$order_number,
							"price_info_id"=>$result,
							"member_username"=>$Member['username'],
							"title"=>"注册用户",
							"title_en"=>"Register",
							"title_sp"=>"Registrar",
							"remark"=>"",
							"type"=>2,
							"member_id"=>$Member['id'],
							"price"=>$PackageType['price'],
							"adddate"=>$date,
							"addtime"=>$time,
						);
						if($orderInfo = M("OrderInfo")->data($data)->add()){
							$_POST['userpass'] = I("telephone");
							$_POST['paypass'] = I("telephone");
							$_POST['r_id'] = $recom_member['id'];
							$_POST['r_username'] = $r_username;
							$_POST['position'] = $position;
							$_POST['p_id'] = $p_id;
							$_POST['p_username'] = $p_username;
							$AddMember = D("AddMember");
							if (!$AddMember->create()){
								session("model",1);
								$this->error($AddMember->getError(),U('Home/Member/register'));
							}else{
								if($result2 = $AddMember->add()){
									$TOUCH_DAY = C("DEFAULT_WEB_TOUCH_DAY");
									$FREE_DAY = $TOUCH_DAY+C("DEFAULT_WEB_FREE_DAY");
									$share_data = array(
										"type"=>1,
										"order_number"=>$order_number,
										"member_id"=>$result2,
										"member_username"=>I("username"),
										"title"=>"认购",
										"title_en"=>"Register",
										"title_sp"=>"Subscripción",
										"pay_price"=>$PackageType['price']*$PackageType['matching_ratio'],
										"touch_datetime"=>date("Y-m-d",strtotime("+".$TOUCH_DAY." days")),
										"free_datetime"=>date("Y-m-d",strtotime("+".$FREE_DAY." days")),
										"adddate"=>$date,
										"addtime"=>$time,
									);
									M("ShareOrderInfo")->data($share_data)->add();
									$order_money_number = "";
									while(true){
										$arr = getMoneyNumber();
										if($arr['result']){
											$order_money_number = $arr['order_number'];
											break;
										}
									}
									//推荐奖
									$DEFAULT_WEB_SET_SPONSOR = (float)C("DEFAULT_WEB_SET_SPONSOR");		
									$SET_SPONSOR = $DEFAULT_WEB_SET_SPONSOR*100;									
									$recom_PriceMoneyInfo = M("PriceMoneyInfo")->where(array("member_id"=>$recom_member['id']))->order("id desc")->find();
									$recom_package = M("PackageType")->where(array("id"=>$recom_member["package_type"]))->find();
									$money_data = array(
										"type"=>2,
										"member_id"=>$recom_member['id'],
										"member_username"=>$recom_member['username'],
										"order_id"=>$order_money_number,
										"title"=>I("username")." 注册,推荐奖".$PackageType['price']*$recom_package['recom_reward']."的".$SET_SPONSOR."%转为现金币",
										"title_en"=>I("username")." registered, Referral Bonus".$PackageType['price']*$recom_package['recom_reward']." ".$SET_SPONSOR."%transfered into Cash Coin",
										"title_sp"=>I("username")." Registrar，Premio de recomendación".$PackageType['price']*$recom_package['recom_reward']."El ".$SET_SPONSOR."% de ha cambiado en monedas de efectivo",
										"new_price"=>$recom_PriceMoneyInfo['new_price']+$PackageType['price']*$recom_package['recom_reward']*$DEFAULT_WEB_SET_SPONSOR,
										"old_price"=>$recom_PriceMoneyInfo['new_price'],
										"price"=>$PackageType['price']*$recom_package['recom_reward']*$DEFAULT_WEB_SET_SPONSOR,
										"adddate"=>date("Y-m-d",time()),
										"addtime"=>date("Y-m-d H:i:s",time()+1),
									);
									M("PriceMoneyInfo")->data($money_data )->add();
									$DEFAULT_WEB_SET_MATCH = (float)C("DEFAULT_WEB_SET_MATCH");
									$SET_MATCH = $DEFAULT_WEB_SET_MATCH *100;
									$recom_PriceInfo = M("PriceInfo")->where(array("member_id"=>$recom_member['id']))->order("id desc")->find();
									$price_data = array(
										"type"=>5,
										"member_id"=>$recom_member['id'],
										"member_username"=>$recom_member['username'],
										"order_id"=>"",
										"title"=>I("username")." 注册,推荐奖".$PackageType['price']*$recom_package['recom_reward']."的".$SET_MATCH."%转为注册币",
										"title_en"=>I("username")." registered, Referral Bonus".$PackageType['price']*$recom_package['recom_reward']." ".$SET_MATCH."%transfered into Register Coin",
										"title_sp"=>I("username")." Registrar，Premio de recomendación".$PackageType['price']*$recom_package['recom_reward']."El ".$SET_MATCH."%de está transferido a monedas de registro",
										"new_price"=>$recom_PriceInfo['new_price']+$PackageType['price']*$recom_package['recom_reward']*$DEFAULT_WEB_SET_MATCH,
										"old_price"=>$recom_PriceInfo['new_price'],
										"price"=>$PackageType['price']*$recom_package['recom_reward']*$DEFAULT_WEB_SET_MATCH,
										"adddate"=>date("Y-m-d",time()),
										"addtime"=>date("Y-m-d H:i:s",time()+1),
									);
									M("PriceInfo")->data($price_data)->add();
									//金额动态
									$log_data = array(
										"type"=>1,
										"member_id"=>$recom_member['id'],
										"member_username"=>$recom_member['username'],
										"change_id"=>$result2,
										"price"=>$PackageType['price']*$recom_package['recom_reward'],
										"change_username"=>I("username"),
										"order_id"=>$order_money_number,
										"title"=>I("username")."(".$PackageType['title'].") 注册,您获得 推荐奖 $". sprintf("%.2f",$PackageType['price']*$recom_package['recom_reward']),
										"title_en"=>I("username")."(".$PackageType['en_title'].") registered, you get Referral Bonus $". sprintf("%.2f",$PackageType['price']*$recom_package['recom_reward']),
										"title_sp"=>I("username")."(".$PackageType['sp_title'].") de registro, logra Premio de recomendación $". sprintf("%.2f",$PackageType['price']*$recom_package['recom_reward']),
										"adddate"=>date("Y-m-d",time()),
										"addtime"=>date("Y-m-d H:i:s",time()),
									);
									M("ChangeLog")->data($log_data)->add();
									//金额累计
									$ChangeLogInfo = M("ChangeLogInfo")->where(array("adddate"=>date("Y-m-d",time()),"member_id"=>$recom_member['id']))->find();
									if($ChangeLogInfo){
										$log_data_info = array(
											"recom_price"=>$ChangeLogInfo['recom_price']+$PackageType['price']*$recom_package['recom_reward'],
										);
										M("ChangeLogInfo")->where(array("adddate"=>date("Y-m-d",time()),"member_id"=>$recom_member['id']))->data($log_data_info)->save();
									}else{
										$log_data_info = array(
											"member_id"=>$recom_member['id'],
											"member_username"=>$recom_member['username'],
											"recom_price"=>$PackageType['price']*$recom_package['recom_reward'],
											"adddate"=>date("Y-m-d",time()),
											"addtime"=>date("Y-m-d H:i:s",time()+2),
										);
										M("ChangeLogInfo")->data($log_data_info)->add();
									}
									//间点奖
									$MemberList = M("Member")->field("id,username,package_type,p_id,p_username")->select();
									
									$dot_list = member_tree($MemberList,$p_id);

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
												"title"=>I("username")." 注册,见点奖".$PackageType['price']*$top_package['dot_ratio']."的".$SET_SPONSOR."%转为现金币",
												"title_en"=>I("username")." registered, POS Bonus".$PackageType['price']*$top_package['dot_ratio']." ".$SET_SPONSOR."% transfered into Cash Coin.",
												"title_sp"=>I("username")." Registrar, Premio de proposición".$PackageType['price']*$top_package['dot_ratio']."El ".$SET_SPONSOR."% de ha cambiado en monedas de efectivo",
												"new_price"=>$top_PriceMoneyInfo['new_price']+$PackageType['price']*$top_package['dot_ratio']*$DEFAULT_WEB_SET_SPONSOR,
												"old_price"=>$top_PriceMoneyInfo['new_price'],
												"price"=>$PackageType['price']*$top_package['dot_ratio']*$DEFAULT_WEB_SET_SPONSOR,
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
												"title"=>I("username")." 注册,见点奖".$PackageType['price']*$top_package['dot_ratio']."的".$SET_MATCH."%转为注册币",
												"title_en"=>I("username")." registered, POS Bonus".$PackageType['price']*$top_package['dot_ratio']." ".$SET_MATCH."% transfered into Register Coin",
												"title_sp"=>I("username")." Registrar，Premio de proposición".$PackageType['price']*$top_package['dot_ratio']."El ".$SET_MATCH."% de está transferido a monedas de registro",
												"new_price"=>$top_PriceInfo['new_price']+$PackageType['price']*$top_package['dot_ratio']*$DEFAULT_WEB_SET_MATCH,
												"old_price"=>$top_PriceInfo['new_price'],
												"price"=>$PackageType['price']*$top_package['dot_ratio']*$DEFAULT_WEB_SET_MATCH,
												"adddate"=>date("Y-m-d",time()),
												"addtime"=>date("Y-m-d H:i:s",time()+2),
											);
											M("PriceInfo")->data($price_data)->add();
											
											//金额动态
											$log_data = array(
												"type"=>2,
												"member_id"=>$val['id'],
												"member_username"=>$val['username'],
												"change_id"=>$result2,
												"change_username"=>I("username"),
												"price"=>$PackageType['price']*$top_package['dot_ratio'],
												"order_id"=>$order_money_number,
												"title"=>I("username")."(".$PackageType['title'].") 注册,您获得 见点奖 $". sprintf("%.2f",$PackageType['price']*$top_package['dot_ratio']),
												"title_en"=>I("username")."(".$PackageType['en_title'].") registered, you get POS Bonus $". sprintf("%.2f",$PackageType['price']*$top_package['dot_ratio']),
												"title_sp"=>I("username")."(".$PackageType['sp_title'].") de registro, logra Premio de proposición $". sprintf("%.2f",$PackageType['price']*$top_package['dot_ratio']),
												"adddate"=>date("Y-m-d",time()),
												"addtime"=>date("Y-m-d H:i:s",time()+1),
											);
											M("ChangeLog")->data($log_data)->add();
											//金额累计
											$ChangeLogInfo = M("ChangeLogInfo")->where(array("adddate"=>date("Y-m-d",time()),"member_id"=>$val['id']))->find();
											if($ChangeLogInfo){
												$log_data_info = array(
													"dot_price"=>$ChangeLogInfo['dot_price']+$PackageType['price']*$top_package['dot_ratio'],
												);
												M("ChangeLogInfo")->where(array("adddate"=>date("Y-m-d",time()),"member_id"=>$val['id']))->data($log_data_info)->save();
											}else{
												$log_data_info = array(
													"member_id"=>$val['id'],
													"member_username"=>$val['username'],
													"dot_price"=>$PackageType['price']*$top_package['dot_ratio'],
													"adddate"=>date("Y-m-d",time()),
													"addtime"=>date("Y-m-d H:i:s",time()+2),
												);
												M("ChangeLogInfo")->data($log_data_info)->add();
											}
										}
									}
									
									//对碰奖金
									if($position==1){
										
										$PackageType_left = M("PackageType")->where(array("id"=>$left_member['package_type']))->find();
										$PackageType_parent = M("PackageType")->where(array("id"=>$Member['package_type']))->find();
										if($PackageType_left['price']>=$PackageType['price']){
											$touch_price = $PackageType['price']*$PackageType_parent['touch_reward'];
										}else{
											$touch_price = $PackageType_left['price']*$PackageType_parent['touch_reward'];
										}
										$all_price = M("ChangeLog")->where(array("member_id"=>$Member['id'],"adddate"=>date("Y-m-d",time()),"type"=>3))->sum("price");
										if($PackageType_parent['touch_day_max']<($all_price+$touch_price)&&$PackageType_parent['touch_day_max']>$all_price){
											$touch_price = $PackageType_parent['touch_day_max']-$all_price;
										}
										if($all_price>$PackageType_parent['touch_day_max']){
											$this->success(L('reg-success'),U('Home/Member/register'));
											die;
										}
										$order_money_number = "";
											
										while(true){
											$arr = getMoneyNumber();
											if($arr['result']){
												$order_money_number = $arr['order_number'];
												break;
											}
										}
										$PriceMoneyInfo = M("PriceMoneyInfo")->where(array("member_id"=>$Member['id']))->order("id desc")->find();
										$money_data = array(
											"type"=>2,
											"member_id"=>$Member['id'],
											"member_username"=>$Member['username'],
											"order_id"=>$order_money_number,
											"title"=>I("username")." 注册,对碰奖".$touch_price."的".$SET_SPONSOR."%转为现金币",
											"title_en"=>I("username")." register, Cycle Bonus ".$touch_price." ".$SET_SPONSOR."% transfered into Cash Coin.",
											"title_sp"=>I("username")." Registro, Premio de competición".$touch_price."El ".$SET_SPONSOR."% de ha cambiado en monedas de efectivo",
											"new_price"=>$PriceMoneyInfo['new_price']+$touch_price*$DEFAULT_WEB_SET_SPONSOR,
											"old_price"=>$PriceMoneyInfo['new_price'],
											"price"=>$touch_price*$DEFAULT_WEB_SET_SPONSOR,
											"adddate"=>date("Y-m-d",time()),
											"addtime"=>date("Y-m-d H:i:s",time()+3),
										);
										M("PriceMoneyInfo")->data($money_data)->add();
										
										
										$top_PriceInfo = M("PriceInfo")->where(array("member_id"=>$Member['id']))->order("id desc")->find();
										
										$price_data = array(
											"type"=>5,
											"member_id"=>$Member['id'],
											"member_username"=>$Member['username'],
											"order_id"=>"",
											"title"=>I("username")." 注册,对碰奖".$touch_price."的".$SET_MATCH."%转为注册币",
											"title_en"=>I("username")." registered, Cycle Bonus".$touch_price." ".$SET_SPONSOR."% transfered into Register Coin",
											"title_sp"=>I("username")."  Registrar，Premio de competición".$touch_price."El ".$SET_SPONSOR."% de está transferido a monedas de registro",
											"new_price"=>$top_PriceInfo['new_price']+$touch_price*$DEFAULT_WEB_SET_MATCH,
											"old_price"=>$top_PriceInfo['new_price'],
											"price"=>$touch_price*$DEFAULT_WEB_SET_MATCH,
											"adddate"=>date("Y-m-d",time()),
											"addtime"=>date("Y-m-d H:i:s",time()+3),
										);
										M("PriceInfo")->data($price_data)->add();
										//金额动态
										$log_data = array(
											"type"=>3,
											"member_id"=>$Member['id'],
											"member_username"=>$Member['username'],
											"change_id"=>$result2,
											"change_username"=>I("username"),
											"price"=>$touch_price,
											"order_id"=>$order_money_number,
											"title"=>I("username")."(".$PackageType['title'].") 注册,您获得 对碰奖 $". sprintf("%.2f",$touch_price),
											"title_en"=>I("username")."(".$PackageType['en_title'].") registered, you get Cycle Bonus $". sprintf("%.2f",$touch_price),
											"title_sp"=>I("username")."(".$PackageType['sp_title'].") de registro, logra Premio de competición $". sprintf("%.2f",$touch_price),
											"adddate"=>date("Y-m-d",time()),
											"addtime"=>date("Y-m-d H:i:s",time()+3),
										);
										M("ChangeLog")->data($log_data)->add();
										
										//金额累计
										$ChangeLogInfo = M("ChangeLogInfo")->where(array("adddate"=>date("Y-m-d",time()),"member_id"=>$Member['id']))->find();
										if($ChangeLogInfo){
											$log_data_info = array(
												"touch_price"=>$ChangeLogInfo['touch_price']+$touch_price,
											);
											M("ChangeLogInfo")->where(array("adddate"=>date("Y-m-d",time()),"member_id"=>$Member['id']))->data($log_data_info)->save();
										}else{
											$log_data_info = array(
												"member_id"=>$Member['id'],
												"member_username"=>$Member['username'],
												"touch_price"=>$touch_price,
												"adddate"=>date("Y-m-d",time()),
												"addtime"=>date("Y-m-d H:i:s",time()+2),
											);
											M("ChangeLogInfo")->data($log_data_info)->add();
										}
										
										//对碰触发 领导奖
										foreach($dot_list as $val){
											
											$top_package = M("PackageType")->where(array("id"=>$val['package_type']))->find();
											
											if($top_package['leader_layer']>=$val['lv']){
												
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
													"title"=>I("username")." 注册,领导奖".$touch_price*$top_package['leader_reward']."的".$SET_SPONSOR."%转为现金币",
													"title_en"=>I("username")." register,Leader Bonus".$touch_price*$top_package['leader_reward']." ".$SET_SPONSOR."% transfered into Cash Coin.",
													"title_sp"=>I("username")." Registro,Leader Bonus".$touch_price*$top_package['leader_reward']."El ".$SET_SPONSOR."% de ha cambiado en monedas de efectivo",
													"new_price"=>$top_PriceMoneyInfo['new_price']+$touch_price*$top_package['leader_reward']*$DEFAULT_WEB_SET_SPONSOR,
													"old_price"=>$top_PriceMoneyInfo['new_price'],
													"price"=>$touch_price*$top_package['leader_reward']*$DEFAULT_WEB_SET_SPONSOR,
													"adddate"=>date("Y-m-d",time()),
													"addtime"=>date("Y-m-d H:i:s",time()+4),
												);
												M("PriceMoneyInfo")->data($money_data)->add();											
												
												$top_PriceInfo = M("PriceInfo")->where(array("member_id"=>$val['id']))->order("id desc")->find();
												
												$price_data = array(
													"type"=>5,
													"member_id"=>$val['id'],
													"member_username"=>$val['username'],
													"order_id"=>"",
													"title"=>I("username")." 注册,领导奖".$touch_price*$top_package['leader_reward']."的".$SET_MATCH."%转为注册币",
													"title_en"=>I("username")." register,Leader Bonus".$touch_price*$top_package['leader_reward']." ".$SET_MATCH."% transfered into Register Coin",
													"title_sp"=>I("username")." Registro,Premio de dirección".$touch_price*$top_package['leader_reward']."El ".$SET_MATCH."% transfered into Register Coin",
													"new_price"=>$top_PriceInfo['new_price']+$touch_price*$top_package['leader_reward']*$DEFAULT_WEB_SET_MATCH,
													"old_price"=>$top_PriceInfo['new_price'],
													"price"=>$touch_price*$top_package['leader_reward']*$DEFAULT_WEB_SET_MATCH,
													"adddate"=>date("Y-m-d",time()),
													"addtime"=>date("Y-m-d H:i:s",time()+4),
												);
												M("PriceInfo")->data($price_data)->add();
												
												//金额动态
												$log_data = array(
													"type"=>4,
													"member_id"=>$val['id'],
													"member_username"=>$val['username'],
													"change_id"=>$result2,
													"change_username"=>I("username"),
													"price"=>$touch_price*$top_package['leader_reward'],
													"order_id"=>$order_money_number,
													"title"=>I("username")."(".$PackageType['title'].") 注册,您获得 领导奖 $". sprintf("%.2f",$touch_price*$top_package['leader_reward']),
													"title_en"=>I("username")."(".$PackageType['en_title'].") registered, you get Leader Bonus $". sprintf("%.2f",$touch_price*$top_package['leader_reward']),
													"title_sp"=>I("username")."(".$PackageType['sp_title'].") de registro, logra Premio de dirección $". sprintf("%.2f",$touch_price*$top_package['leader_reward']),
													"adddate"=>date("Y-m-d",time()),
													"addtime"=>date("Y-m-d H:i:s",time()+4),
												);
												M("ChangeLog")->data($log_data)->add();
												//金额累计
												$ChangeLogInfo = M("ChangeLogInfo")->where(array("adddate"=>date("Y-m-d",time()),"member_id"=>$val['id']))->find();
												if($ChangeLogInfo){
													$log_data_info = array(
														"leader_price"=>$ChangeLogInfo['leader_price']+$touch_price*$top_package['leader_reward'],
													);
													M("ChangeLogInfo")->where(array("adddate"=>date("Y-m-d",time()),"member_id"=>$val['id']))->data($log_data_info)->save();
												}else{
													$log_data_info = array(
														"member_id"=>$val['id'],
														"member_username"=>$val['username'],
														"leader_price"=>$touch_price*$top_package['leader_reward'],
														"adddate"=>date("Y-m-d",time()),
														"addtime"=>date("Y-m-d H:i:s",time()+2),
													);
													M("ChangeLogInfo")->data($log_data_info)->add();
												}
											}
										}
									}
									$this->success(L('reg-success'),U('Home/Member/register'));
								}else{
									M("OrderInfo")->where(array("id"=>$orderInfo))->delete();
									M("PriceInfo")->where(array("id"=>$result))->delete();
									$this->error(L("class-member-registerfailure"));
								}
							}						
						}else{
							M("PriceInfo")->where(array("id"=>$result))->delete();
							$this->error(L("class-member-orderfailure"),U('Home/Member/register'));
						}
						
					}else{
						$this->error(L("class-member-payfailure"),U('Home/Member/register'));
					}
				}
			}else{
				$this->error(L("class-member-registerccinsufficient"));
			}
		}else{
			$id = I("id");
			$Member = session("MemberID");
			$MemberList = M("Member")->field("id,p_id,name,package_type,username,position")->where(array("delete"=>0))->select();
			$member_list = member_list($MemberList,$Member['id']);
			$array_id = array_multi2single($member_list);
			if($id!=""){
				foreach($array_id as $val){
					if($id==$val["id"]){
						$Member = M("Member")->where(array("id"=>$id))->find();
						$member_list = member_list($MemberList,$id);
					}
				}		
			}else{
				
			}
			
			$this->User = $Member;
			$this->member_list = $member_list;
			$this->package_list = M("PackageType")->where(array("status"=>1))->select();
			$this->picture = "Public/Public/Images/default.jpg";
			$this->display();
		}
	}
	public function my_team(){
		$Member = session("MemberID");
		$MemberList = M("Member")->field("id,p_id,name,username,position")->where(array("delete"=>0))->select();
		$member_list = array_multi2single(member_list($MemberList,$Member['id']));
		$arr = array();
		foreach($member_list as $val){
			$arr[] = $val['id'];
		}
		$this->member_number = count($arr);
		$this->member_list = M("Member")->where(array("id"=>array('in',implode(',',$arr))))->select();
		$this->package_list = M("PackageType")->where(array("status"=>1))->select();
		$this->display();
	}
	public function my_member(){
		$Member = session("MemberID");
		$MemberList = M("Member")->where(array("delete"=>0,"r_id"=>$Member['id']))->select();
		$this->member_number = count($MemberList);
		$this->package_list = M("PackageType")->where(array("status"=>1))->select();
		$this->member_list = $MemberList;
		$this->display();
	}
}