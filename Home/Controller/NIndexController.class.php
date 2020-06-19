<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
	 public function _initialize(){
		$language = I("language");
		if($language==""){
			session("language","zh");
			$this->language = "zh";
		}else{
			session("language",$language);
			$this->language = $language;
		}
	}
    public function index(){
        $this->display();
    }
	public function login(){
		if(IS_POST){
			$code = I("code");
			$verify = new \Think\Verify();
			if(!$verify->check($code)){
				$this->AjaxReturn(array("result"=>false,"message"=>L('public-code-error')));
			}
			$username = I("nickname");
			$userpass = I("password","0","md5");
			$Member = M("Member")->where(array("username"=>$username,"userpass"=>$userpass))->find();
			$package_type = M("PackageType")->where(array("id"=>$Member['package_type']))->find();
			if($Member){
				if($Member['status']==0){
					$this->error(L('public-account-close'),U("Home/Index/login",array("language"=>I("language"))));
				}
				SESSION("MemberIP",get_client_ip());
				//查询 Digit 数据
				SESSION("MemberID",$Member);
				//上一次登陆时间
				$MemberLogin = M("MemberLoginInfo")->where(array("member_id"=>$Member['id']))->order("id desc")->find();
				//获取拆分数据
				if($MemberLogin){									
					$SplitList = M("Split")->select();
					for($i = strtotime($MemberLogin['login_time']); $i <= strtotime(date("y-m-d H:i:s",time())); $i += 86400){
						$ThisDate=date("Y-m-d H:i:s",$i);
						//拆分
						if(count($SplitList)>0){
							foreach($SplitList as $sl){
								if(date("Y-m-d",strtotime($ThisDate))==$sl['adddate']){
									if(!M("ShareOrderInfo")->where(array("member_id"=>$Member['id'],"split_id"=>$sl['id']))->find()){
										$ShareInfo = M("ShareNumberInfo")->where(array("member_id"=>$Member['id'],"adddate"=>array("lt",date("Y-m-d",strtotime($ThisDate)))))->order("id desc")->find();
										if($ShareInfo['new_number']>0){
											$price = intval($ShareInfo['new_number']*($sl['split_double']-1));
											$split_price = (float)$sl["split_new_price"];
											$data = array(
												"type"=>1,
												"member_id"=>$Member['id'],
												"member_username"=>$Member['username'],
												"title"=>"拆分",
												"title_en"=>"split",
												"title_sp"=>"división",
												"split_id"=>$sl['id'],
												"new_number"=>$price+$ShareInfo['new_number'],
												"old_number"=>$ShareInfo['new_number'],
												"number"=>$price,
												"adddate"=>$ThisDate,
												"addtime"=>$ThisDate,
											);
											M("ShareNumberInfo")->data($data)->add();
											$order_number = "";
											while(true){
												$arr = getOrderNumber();
												if($arr['result']){
													$order_number = $arr['order_number'];
													break;
												}
											}
											$share_data = array(
												"type"=>1,
												"order_number"=>$order_number,
												"split_id"=>$sl['id'],
												"title"=>"拆分",
												"title_en"=>"split",
												"title_sp"=>"división",
												"member_id"=>$Member['id'],
												"member_username"=>$Member['username'],
												"pay_number"=>$price,
												"share_price"=>$split_price,
												"pay_price"=>$price*$split_price,
												"status"=>4,
												"touch_datetime"=>$ThisDate,
												"free_datetime"=>$ThisDate,
												"adddate"=>$ThisDate,
												"addtime"=>$ThisDate,
											);
											M("ShareOrderInfo")->data($share_data)->add();
										}
									}
								}
							}
						}
						//配股 start
						$ShareList = M("ShareOrderInfo")->where(array("member_id"=>$Member['id'],"delete"=>0,"status"=>array("in","0,1")))->select();
						if(count($ShareList)>0){
							foreach($ShareList as $val){
								if($val['reinvest_id']==1){
									if($val['status']==0&&$ThisDate>=$val['touch_datetime']){
										//配股操作
										//当天优速币价
										$DigitPrice = M("DigitPrice")->where(array("date"=>date("Y-m-d",strtotime($val['touch_datetime']))))->find();
										if(!$DigitPrice){
											$DigitPrice['price'] = C("DEFAULT_WEB_SHARE_PRICE_NOHARE");
										}
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
											"type"=>3,
											"member_id"=>$Member['id'],
											"member_username"=>$Member['username'],
											"share_price"=>$DigitPrice['price'],
											"title"=>"复投匹配 (".$val['order_number'].")",
											"title_en"=>"Multiple match (".$val['order_number'].")",
											"title_sp"=>"Multiple match (".$val['order_number'].")",
											"pay_number"=>floor($val['pay_price']*$package_type['matching_ratio']/$DigitPrice['price']),
											"pay_price"=>$val['pay_price']*$package_type['matching_ratio'],
											"touch_datetime"=>$val["touch_datetime"],
											"free_datetime"=>$val["free_datetime"],
											"reinvest_id"=>0,
											"reinvest_number"=>$val['order_number'],
											"status"=>1,
											"addtime"=>$ThisDate,
											"adddate"=>$ThisDate,
										);
										M("ShareOrderInfo")->data($data)->add();
										M("ShareOrderInfo")->where(array("id"=>$val['id']))->data(array("status"=>3))->save();
										$val['status'] = 1;
										$val['pay_number'] = floor($val['pay_price']/$DigitPrice['price']);
									}
									if($val['status']==1&&$ThisDate>$val['free_datetime']&&$val['reinvest_id']!=1){
										//解冻操作

										M("ShareOrderInfo")->where(array("id"=>$val['id']))->data(array("status"=>4))->save();
										
										M("ShareOrderInfo")->where(array("order_number"=>$val['reinvest_number']))->data(array("status"=>4))->save();
										
										$ShareInfo = M("ShareNumberInfo")->where(array("member_id"=>$val['member_id']))->order("id desc")->find();
																
										$data = array(
											"type"=>1,
											"member_id"=>$val['member_id'],
											"member_username"=>$val['member_username'],
											"reinvest_id"=>$val['reinvest_id'],
											"title"=>"股票解冻",
											"title_en"=>"Stock thaw",
											"title_sp"=>"Stock thaw",
											"new_number"=>$ShareInfo['new_number'] + $val['pay_number'],
											"old_number"=>$ShareInfo['new_number'],
											"number"=>$val["pay_number"],
											"adddate"=>date("Y-m-d",strtotime($val['free_datetime'])),
											"addtime"=>date("Y-m-d H:i:s",strtotime($val['free_datetime'])),
										);
										M("ShareNumberInfo")->data($data)->add();
									}
								}else{
									if($val['status']==0&&$ThisDate>=$val['touch_datetime']){
										//配股操作
										//当天优速币价
										$DigitPrice = M("DigitPrice")->where(array("date"=>date("Y-m-d",strtotime($val['touch_datetime']))))->find();
										if(!$DigitPrice){
											$DigitPrice['price'] = C("DEFAULT_WEB_SHARE_PRICE_NOHARE");
										}
										$data = array(
											"share_price"=>$DigitPrice['price'],
											"pay_number"=>floor($val['pay_price']/$DigitPrice['price']),
											"status"=>1,
										);
										M("ShareOrderInfo")->where(array("id"=>$val['id']))->data($data)->save();
										$val['status'] = 1;
										$val['pay_number'] = floor($val['pay_price']/$DigitPrice['price']);
									}
									if($val['status']==1&&$ThisDate>$val['free_datetime']&&$val['reinvest_id']!=1){
										//解冻操作
										
										M("ShareOrderInfo")->where(array("id"=>$val['id']))->data(array("status"=>4))->save();
										
										$ShareInfo = M("ShareNumberInfo")->where(array("member_id"=>$val['member_id']))->order("id desc")->find();
																
										$data = array(
											"type"=>1,
											"member_id"=>$val['member_id'],
											"member_username"=>$val['member_username'],
											"reinvest_id"=>$val['reinvest_id'],
											"title"=>"股票解冻",
											"title_en"=>"Stock thaw",
											"title_sp"=>"Stock thaw",
											"new_number"=>$ShareInfo['new_number'] + $val['pay_number'],
											"old_number"=>$ShareInfo['new_number'],
											"number"=>$val["pay_number"],
											"adddate"=>date("Y-m-d",strtotime($val['free_datetime'])),
											"addtime"=>date("Y-m-d H:i:s",strtotime($val['free_datetime'])),
										);
										M("ShareNumberInfo")->data($data)->add();
									}

								}
							}
						}
						//配股 end
						

					}
				}
				session("verify_code",null);
				session("bank_code",null);
				session("bank_addtime",null);
				session("varify_addtime",null);
				session("login",null);
				M("MemberLoginInfo")->data(array("member_id"=>$Member['id'],"login_ip"=>get_client_ip(),"login_time"=>date("Y-m-d H:i:s",time())))->add();
				session("login",1);
				$this->AjaxReturn(array("result"=>true,"message"=>L('public-success')));
			}else{
				$this->AjaxReturn(array("result"=>false,"message"=>L("class-index-error")));
			}
		}else{
			$this->Url = "http://".$_SERVER['SERVER_NAME'];
			$this->language = I("language");
			$this->display();
		}
	}
	public function code(){
		$Verify =     new \Think\Verify();
		// 设置验证码字符为纯数字
		$Verify->codeSet = '0123456789';
		$Verify->fontSize = 40;
		$Verify->length = '4';
		$Verify->entry();
	}
	public function getName(){
		$name = I("forgotten_nickname");
		if($Member = M("Member")->where(array("username"=>$name))->find()){
			$this->AjaxReturn(array("success"=>true,"phone"=>$Member['telephone']));
		}else{
			$this->AjaxReturn(array("success"=>false,"error"=>L("class-index-nouser")));
		}
	}
	public function getTelephoneCode(){
		$name = I("forgotten_nickname");
		$language = session("language");
		if($Member = M("Member")->where(array("username"=>$name))->find()){
			$SMS_URL = C("DEFAULT_WEB_SMS_URL");
			$SMS_ACCOUNT = C("DEFAULT_WEB_SMS_ACCOUNT");
			$SMS_KEY = C("DEFAULT_WEB_SMS_KEY");
			$SMS_PORT = C("DEFAULT_WEB_SMS_PORT");
			$SMS_NUMBER = C("DEFAULT_WEB_SMS_NUMBER");
			$SMS_REMARK = C("DEFAULT_WEB_SMS_REMARK");
			
			if($language=="se"){
				$SMS_FIND = C("DEFAULT_WEB_SMS_FIND_SE");
			}else if($language=="en"){
				$SMS_FIND = C("DEFAULT_WEB_SMS_FIND_EN");
			}else{
				$SMS_FIND = C("DEFAULT_WEB_SMS_FIND");
			}
			$SMS_NUMBER = C("DEFAULT_WEB_SMS_NUMBER");
			$number = rand(pow(10,($SMS_NUMBER-1)),pow(10,$SMS_NUMBER)-1);
			if($Verify = M("Verify")->where(array("member_id"=>$Member['id'],"type"=>3))->order("id desc")->find()){
				$new = time();
				if(date("Y-m-d H:i:s",strtotime($Verify['addtime'])+60)<date("Y-m-d H:i:s",$new)){
					M("Verify")->data(array("member_id"=>$Member['id'],"member_username"=>$Member['username'],"number"=>$number,"addtime"=>date("Y-m-d H:i:s",time()),"type"=>3))->add();		
				}else{
					$minute = $new - strtotime($Verify['addtime']);
					$last_time = 60-$minute;
					$this->AjaxReturn(array("success"=>false,"error"=>"time ".$last_time));
				}
			}else{
				M("Verify")->data(array("member_id"=>$Member['id'],"member_username"=>$Member['username'],"number"=>$number,"addtime"=>date("Y-m-d H:i:s",time()),"type"=>3))->add();		
			}
			$result = send_sms($SMS_URL,$SMS_ACCOUNT,$SMS_KEY,$SMS_PORT,$Member['telephone'],L("login-code").":".$number." ".$SMS_FIND,'');
			if($result['result']){
				if($language=="en"){
					$result['message'] = "Success";
				}
				if($language=="es"){
					$result['message'] = "éxito";
				}
				$this->AjaxReturn(array("success"=>$result['message'],"phone"=>$Member['telephone']));
			}else{
				if($language=="en"){
					$result['message'] = "Error";
				}
				if($language=="es"){
					$result['message'] = "Error";
				}
				$this->AjaxReturn(array("success"=>false,"error"=>$result['message']));
			}
			
		}else{
			if($language=="en"){
				$result['message'] = "No Find User";
			}
			if($language=="es"){
				$result['message'] = "No buscar";
			}
			$this->AjaxReturn(array("success"=>false,"error"=>L("class-index-nouser")));
		}
	}
	public function checkCode(){
		$code = I("verification");
		$name = I("forgotten_nickname");
		if($Member = M("Member")->where(array("username"=>$name))->find()){
			if($Verify = M("Verify")->where(array("member_id"=>$Member['id'],"type"=>3,"status"=>0))->order("id desc")->find()){
				if($Verify['number']==$code){
					session("Verify",null);
					session("Verify",$Member);
					M("Verify")->where(array("id"=>$Verify['id']))->data(array("status"=>1))->save();
					$this->AjaxReturn(array("success"=>true));
				}
			}else{
				$this->AjaxReturn(array("success"=>false,"error_verification"=>L("public-code-error")));
			}
		}else{
			$this->AjaxReturn(array("success"=>false,"error"=>L("class-index-nouser"),"error_verification"=>L("class-index-nouser")));
		}
	}
	public function repass(){
		$new_password = I("new_password");
		if($new_password==""){
			$this->AjaxReturn(array("success"=>false,"error_password"=>L("class-index-passwordempty")));
		}
		$new_confirm = I("new_confirm");
		$member = session("Verify");
		if($new_password==$new_confirm){
			$result = M("Member")->where(array("id"=>$member['id']))->data(array("userpass"=>md5($new_password)))->save();
			if($result||$result==0){
				$this->AjaxReturn(array("success"=>true,"error"=>L("class-index-Passwordischanged")));
			}else{
				$this->AjaxReturn(array("success"=>false,"error"=>L("class-index-failederror")));
			}
		}else{
			$this->AjaxReturn(array("success"=>false,"error_confirm"=>L("class-index-Passwordsdonotmatch")));
		}
	}
	public function userMessage(){
		
		$data = array(
			"name" => I("name"),
			"email" => I("email"),
			"message" => I("message"),
			"emailAddress" => I("emailAddress"),
			"addtime"=>date("Y-m-d H:i:s",time()),
		);
		if(M("Message")->data($data)->add()){
			$this->AjaxReturn(true);
		}else{
			$this->AjaxReturn(false);
		}
	}
	public function out(){
		SESSION("Notice",null);
		SESSION("MemberID",null);
		$this->success("success",U("Home/Index/index"));
	}
}