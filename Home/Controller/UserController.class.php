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

			$user_last=M("Member")->where(array("id"=>$User['id']))->find();
			//$package_type_old=
			$kelingqu_old=$user_last['money_n']-$user_last['money_s'];
			if($kelingqu_old<0){
				$kelingqu_old=0;
			}

			$koufeitype=I("koufei_type");		
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
			/*
			if($PackageType['price']<$UserPackageType['price']){
				$this->error(L("public-system-error"));
			}else{
				$price = $PackageType['price']-$UserPackageType['price'];
			}
			*/
			$price = $PackageType['price'];
			/*
			if($PackageType['id']<=$User['package_type']){
				$this->error(L("public-system-error"));
			}*/
			$PriceInfo = M("PriceInfo")->where(array("delete"=>0,"member_id"=>$User['id']))->order("id desc")->find();
			$ShareInfo = M("ShareNumberInfo")->where(array("member_id"=>$User['id']))->order("id desc")->find();

			$price_zhuce=$PackageType['price'] * intval(C("DEFAULT_WEB_ZHUCE_PERCENT")) * 0.01;
			$price_xianjin=$PackageType['price'] * (100-intval(C("DEFAULT_WEB_ZHUCE_PERCENT"))) * 0.01;

			if($koufeitype=='2'){
				$price_zhuce=0;
				$price_xianjin=$PackageType['price'];
			}

			if($price_zhuce>$PriceInfo['new_price']){
				$this->error(L("注册币不足！"));
			}

			if($price_xianjin>$ShareInfo['new_number']){
				$this->error(L("现金币不足！"));
			}

			if($PriceInfo["new_price"]>=$price_zhuce){
				$PriceData = array(
					'member_id'=>$User['id'],
					'member_username'=>$User['username'],
					'type'=>3,
					'new_price'=>$PriceInfo['new_price'] - $price_zhuce,
					'old_price'=>$PriceInfo['new_price'],
					'title'=>"原点复投扣注册币",
					'title_en'=>"Upgrade",
					'title_sp'=>"Actualización",
					'price'=>$price_zhuce,
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


					//再扣现金币
						
																
					$data = array(
						"type"=>1,
						"member_id"=>$User['id'],
						"member_username"=>$User['username'],
						"reinvest_id"=>0,
						"title"=>"原点复投扣现金币",
						"title_en"=>"Stock thaw",
						"title_sp"=>"Stock thaw",
						"new_number"=>$ShareInfo['new_number'] - $price_xianjin,
						"old_number"=>$ShareInfo['new_number'],
						"number"=>$price_xianjin,
						"adddate"=>date("Y-m-d",time()),
						"addtime"=>date("Y-m-d H:i:s",time()),
					);
					M("ShareNumberInfo")->data($data)->add();

					$OrderData = array(
						"order_number"=>$order_number,
						'member_id'=>$User['id'],
						'member_username'=>$User['username'],
						'type'=>3,
						'price_info_id'=>$PriceInfoResult,
						'title'=>"原点复投",
						'title_en'=>"Upgrade",
						'title_sp'=>"Actualización",
						'price'=>$price,
						'addtime'=>date("Y-m-d H:i:s",time()),
					);
					if($OrderInfo = M("OrderInfo")->data($OrderData)->add()){

						$kelingqu=$PackageType['price']*$PackageType['status_profit_max'];
						$kelingqu=$kelingqu+$kelingqu_old;
						if(M("Member")->where(array("id"=>$User['id']))->data(array("package_type"=>$PackageType['id'],"money_n"=>$kelingqu,"money_s"=>0))->save()){
							$temptjstr=$User['tjstr'];
							$map['id']  = array('in',$temptjstr);
							M("Member")->where($map)->setInc('money_yeji',$PackageType['price']);
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
			//$map['price']  = array('et'=>0);
			$this->package = M("PackageType")->order("id")->select();
            $this->mypackagetype=M("PackageType")->where(array("id"=>$User['package_type']))->find();
			$member_last=M("Member")->where(array("id"=>$User['id']))->find();
			$this->shouyi_all =$member_last['money_s'];
			$this->shouyi_need =$member_last['money_n'];
			$this->title=L('acl-upgrade-account');
			if(ismobile()){
				$this->theme('erci')->display();
			}
			else{
				$this->display();
			}
		}
	}


	public function upgrande_shengji(){
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
			$ShareInfo = M("ShareNumberInfo")->where(array("member_id"=>$User['id']))->order("id desc")->find();

			$zhucebi_had=$PriceInfo?$PriceInfo['new_price']:0;
			$xianjinbi_had=$ShareInfo?$ShareInfo['new_number']:0;

			$price_zhuce=$price * intval(C("DEFAULT_WEB_ZHUCE_PERCENT")) * 0.01;
			$price_xianjin=$price * (100-intval(C("DEFAULT_WEB_ZHUCE_PERCENT"))) * 0.01;

			$koufeitype=I("koufei_type");
			if($koufeitype=='2'){
				$price_zhuce=0;
				$price_xianjin=$price;
			}

			//var_dump($price_zhuce);die;
			if($price_zhuce>$zhucebi_had){
				$this->error(L("注册币不足！"));
			}

			if($price_xianjin>$xianjinbi_had){
				$this->error(L("现金币不足！"));
			}


			$user_last=M("Member")->where(array("id"=>$User['id']))->find();
			
			//$UserPackageType['price']
			//$package_type_old=
			$kelingqu_old=$user_last['money_n']-($UserPackageType['price']*$UserPackageType['status_profit_max']);
			if($kelingqu_old<0){
				$kelingqu_old=0;
			}

			if($PriceInfo["new_price"]>=$price_zhuce){
				$PriceData = array(
					'member_id'=>$User['id'],
					'member_username'=>$User['username'],
					'type'=>3,
					'new_price'=>$zhucebi_had - $price_zhuce,
					'old_price'=>$zhucebi_had,
					'title'=>"原点升级扣注册币",
					'title_en'=>"Upgrade",
					'title_sp'=>"Actualización",
					'price'=>$price_zhuce,
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


					//再扣现金币
						
																
					$data = array(
						"type"=>1,
						"member_id"=>$User['id'],
						"member_username"=>$User['username'],
						"reinvest_id"=>0,
						"title"=>"原点升级扣现金币",
						"title_en"=>"Stock thaw",
						"title_sp"=>"Stock thaw",
						"new_number"=>$xianjinbi_had - $price_xianjin,
						"old_number"=>$xianjinbi_had,
						"number"=>$price_xianjin,
						"adddate"=>date("Y-m-d",time()),
						"addtime"=>date("Y-m-d H:i:s",time()),
					);
					M("ShareNumberInfo")->data($data)->add();

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

						$kelingqu=$PackageType['price']*$PackageType['status_profit_max'];
						$kelingqu=$kelingqu+$kelingqu_old;
						if(M("Member")->where(array("id"=>$User['id']))->data(array("package_type"=>$PackageType['id'],"money_n"=>$kelingqu))->save()){
							$temptjstr=$User['tjstr'];
							$map['id']  = array('in',$temptjstr);
							M("Member")->where($map)->setInc('money_yeji',$price);
							$this->success("升级成功！");	
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
		}
	}

	public function profile(){
		$this->package = M("PackageType")->order("id")->select();
		if(ismobile()){
			$this->theme('erci')->display();
		}
		else{
			$this->display();
		}
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
			$this->display("User/bank_info");	
		}
	}
	public function bank(){
		if(IS_POST){
			$verification = I("number");
			$User = session("MemberID");
			$Verify = M("Verify")->where(array("type"=>2,"member_id"=>$User['id'],"status"=>0))->order("id desc")->find();

			$verification = $Verify['number']; //临时添加

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

		$this->title=L('acl-security');

		if(ismobile()){
			$this->theme('erci')->display();
		}
		else{
			$this->display();
		}
	}
	
	
}