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
		$tuiid=0;
		$isreg=0;
		$tguser="";
		if($_GET['reg'] && $_GET['reg']>0){
			$tuiid=$_GET['reg'];
			$user_tui=M("Member")->where(array("id"=>$tuiid))->find();
			if($user_tui){
				$tguser=$user_tui['username'];
			}
			$isreg=1;
		}

		

		$this->isreg=$isreg;
		$this->tuiguanuser=$tguser;
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
				
				session("verify_code",null);
				session("bank_code",null);
				session("bank_addtime",null);
				session("varify_addtime",null);
				session("login",null);
				//自动出售
				//$this->sell_number(date("Y-m-d H:i:s",time()));
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
	
	private function sell_number($ThisDate){
		
		$Member = SESSION("MemberID");
		
		$PackageType = M("PackageType")->where(array("id"=>$Member['package_type']))->find();
		
		$Info = M("ShareNumberInfo")->where(array("member_id"=>$Member['id'],"adddate"=>array("lt",$ThisDate)))->order("id desc")->find();
		
		if($Info['new_number']>$PackageType['share_holding_max']){
			$sell_price = $Info['new_number']*60/100;
		}else{
			return false;
		}
		//当前优速币价
		$NewDigitPrice = M("DigitPrice")->where(array("date"=>$ThisDate))->find();
		if($NewDigitPrice['price']>0){
			$price = $NewDigitPrice['price'];
		}else{
			$price = C("DEFAULT_WEB_SHARE_PRICE_NOHARE");
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
		
		$all_sell_share_price = M("ShareOrderInfo")->where(array("member_id"=>$Member['id'],"type"=>2,"adddate"=>array("lt",$ThisDate)))->sum("pay_price");
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
				"adddate"=>$ThisDate,
				'addtime'=>$ThisDate,
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
					"adddate"=>$ThisDate,
					"addtime"=>$ThisDate,
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
					"touch_datetime"=>date("Y-m-d",strtotime($ThisDate)+$TOUCH_DAY*8640),
					"free_datetime"=>date("Y-m-d",strtotime($ThisDate)+$FREE_DAY*8640),
					"adddate"=>$ThisDate,
					"addtime"=>$ThisDate,
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
				$PriceMoneyInfo = M("PriceMoneyInfo")->where(array("delete"=>0,"member_id"=>$Member['id'],"adddate"=>array("lt",$ThisDate)))->order("id desc")->find();
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
					"adddate"=>$ThisDate,
					"addtime"=>$ThisDate,
				);
				M("PriceMoneyInfo")->data($money_pay)->add();
				//us积分
				$PriceIntegralInfo = M("PriceIntegralInfo")->where(array("delete"=>0,"member_id"=>$Member['id'],"adddate"=>array("lt",$ThisDate)))->order("id desc")->find();
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
					"adddate"=>$ThisDate,
					"addtime"=>$ThisDate,
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
						"adddate"=>$ThisDate,
						'addtime'=>$ThisDate,
					);
					M("ShareNumberInfo")->where(array("member_id"=>$Member['id']))->data($NewPriceData)->save();
				}
				return true;
			}else{
				return false;
			}
			
		}else{
			return false;
		}
	}


	public function register(){
		if(IS_POST){			
			//$Member = session("MemberID");
			$package_type = 1;
			
			if(!$PackageType = M("PackageType")->where(array("id"=>$package_type))->find()){
				
				$this->AjaxReturn(array("success"=>false,"msg"=>L("class-member-packagetype")));
			}
			$r_username = I("recommended_name");
			$p_id = 12;
			$p_username = 'test';
			$position = 0;
			$telephone = I("telephone");
			if(M("Member")->where(array("telephone"=>$telephone))->count()>0){
				$this->AjaxReturn(array("success"=>false,"msg"=>'此手机号码已经被注册过！'));
			}
			$date = date("Y-m-d",time());
			$time = date("Y-m-d H:i:s",time());
			
			if(!$recom_member = M("member")->where(array("username"=>$r_username))->find()){
				$this->AjaxReturn(array("success"=>false,"msg"=>'推荐人不存在！'));
			}	
			
			if(trim(I("userpass"))==''){
				//$this->error();
				$this->AjaxReturn(array("success"=>false,"msg"=>"登录密码不能为空！"));
			}
			if(trim(I("paypass"))==''){
				//$this->error("支付密码不能为空！");
				$this->AjaxReturn(array("success"=>false,"msg"=>"支付密码不能为空！"));
			}
			if(trim(I("userpass"))!=trim(I("userpass_2"))){
				//$this->error("登录密码两次输入不一致！");
				$this->AjaxReturn(array("success"=>false,"msg"=>"登录密码两次输入不一致！"));
			}
			if(trim(I("paypass"))!=trim(I("paypass_2"))){
				//$this->error("支付密码两次输入不一致！");
				$this->AjaxReturn(array("success"=>false,"msg"=>"支付密码两次输入不一致！"));
			}
			$_POST['package_type']=$package_type;
			$_POST['userpass'] = trim(I("userpass"));
			$_POST['paypass'] = trim(I("paypass"));
			$_POST['r_id'] = $recom_member['id'];
			$_POST['r_username'] = $r_username;
			$_POST['position'] = $position;
			$_POST['p_id'] = $p_id;
			$_POST['p_username'] = $p_username;
			$AddMember = D("AddMember");
			if (!$AddMember->create()){
				session("model",1);
				$this->AjaxReturn(array("success"=>false,"msg"=>$AddMember->getError()));
			}else{
				if($result2 = $AddMember->add()){
					$this->AjaxReturn(array("success"=>true,"msg"=>'注册成功!您的默认密码，就是您注册的手机号码，请及时修改密码！'));
				}else{
					$this->AjaxReturn(array("success"=>false,"msg"=>'系统发生错误！'));
				}
			}			

		}
	}
	public function getregphonecode(){
		$phone = I("phonenum");
		$language = session("language");
		
		$SMS_URL = C("DEFAULT_WEB_SMS_URL");
			$SMS_ACCOUNT = C("DEFAULT_WEB_SMS_ACCOUNT");
			$SMS_KEY = C("DEFAULT_WEB_SMS_KEY");
			$SMS_PORT = C("DEFAULT_WEB_SMS_PORT");
			$SMS_NUMBER = C("DEFAULT_WEB_SMS_NUMBER");
			$SMS_REMARK = C("DEFAULT_WEB_SMS_REMARK");
			
			$SMS_FIND = "你正在注册贝拉社区。";
			$SMS_NUMBER = C("DEFAULT_WEB_SMS_NUMBER");
			$number = rand(pow(10,($SMS_NUMBER-1)),pow(10,$SMS_NUMBER)-1);
			
			$this->AjaxReturn(array("success"=>true,"phone"=>$phone));

			/*
			$result = send_sms($SMS_URL,$SMS_ACCOUNT,$SMS_KEY,$SMS_PORT,$phone,L("login-code").":".$number." ".$SMS_FIND,'');
			if($result['result']){
				
				$this->AjaxReturn(array("success"=>$result['message'],"phone"=>$Member['telephone']));
			}else{
				$this->AjaxReturn(array("success"=>false,"error"=>$result['message']));
			}
			*/
	}

	public function code(){
		$Verify =     new \Think\Verify();
		// 设置验证码字符为纯数字
		$Verify->codeSet = '0123456789';
		$Verify->fontSize = 40;
		$Verify->length = '4';
		$Verify->useCurve = false;
		$Verify->useNoise = false;
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