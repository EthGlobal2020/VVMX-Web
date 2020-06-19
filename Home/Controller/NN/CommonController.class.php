<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {
	
    public function _initialize(){
		
		if(SESSION("MemberID")&&SESSION("language")){
			$Member = SESSION("MemberID");
			$where = array("id"=>$Member['id'],"username"=>$Member['username'],"userpass"=>$Member['userpass']);
			$result = M("Member")->where($where)->find();
			if(!$result){
				SESSION("MemberID",null);
				$this->error(L("class-common-error"),U("Home/Index/index",array("language"=>$language)));
			}else{
				SESSION("MemberID",$result);
				$this->UserPackageType = M("PackageType")->where(array("id"=>$result['package_type']))->find();
/* 				if($result['status_login']==1){
					$this->redirect('Home/Lock/lock',array("language"=>$language),0,"");
				} */
			}
			$MemberLoginInfo = M("MemberLoginInfo")->where(array("member_id"=>$Member['id']))->order("id desc")->limit(1)->select();
			
			$PriceInfo = M("PriceInfo")->where(array("delete"=>0,"member_id"=>$Member['id']))->order("id desc")->find();
			if($PriceInfo){
				$this->new_price = $PriceInfo['new_price']?$PriceInfo['new_price']:"0.00";
			}else{
				$this->new_price = "0.00";
			}
			
			$PriceMoneyInfo = M("PriceMoneyInfo")->where(array("delete"=>0,"member_id"=>$Member['id']))->order("id desc")->find();
			if($PriceMoneyInfo){
				$this->new_money_price = $PriceMoneyInfo['new_price']?$PriceMoneyInfo['new_price']:"0.00";
			}else{
				$this->new_money_price = "0.00";
			}
			
			if(count($MemberLoginInfo)>0){
				if(SESSION("MemberIP")!=$MemberLoginInfo[0]["login_ip"]){
					SESSION("MemberID",null);
					$this->error(L("class-common-longerror"),U("Home/Index/index",array("language"=>$language)));
				}
			}else{
				SESSION("MemberID",null);
				$this->error(L("class-common-error"),U("Home/Index/index",array("language"=>$language)));
			}
		}else{
			$this->error(L("class-common-error"),U("Home/Index/index",array("language"=>$language)));
		}
		$language = I("language");
		if($language!=""){
			session("language",null);
			session("language",$language);
			$this->language = $language;
		}else{
			$language = session("language");
			$this->language = $language;
		}
		$this->User = session("MemberID");
		$this->Action = ACTION_NAME;
		$this->Url = "http://".$_SERVER['SERVER_NAME'];
		$this->WEB_TITLE = C("DEFAULT_WEB_TITLE");
		if(SESSION("Notice")){
			$this->Notice = SESSION("Notice");
		}
		if(session("login")==1){
			session("login",0);
			$this->login = 1;
		}
	}
}