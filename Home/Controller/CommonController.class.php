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

		if(!file_exists("Public/Uploads/qrcodes/".$this->User['id'].".png")){
			$this->qrcode($this->User['id']);
		}

		$tuiguang=array("link"=>"http://www.blamember.com/?reg=".$this->User['id'],"img"=>"/Public/Uploads/qrcodes/".$this->User['id'].".png");

		$this->tuiinfo=$tuiguang;
		$this->Action = ACTION_NAME;
		$this->Url = "http://".$_SERVER['SERVER_NAME'];
		$this->WEB_TITLE = C("DEFAULT_WEB_TITLE");
		$this->btcionaddress=C("DEFAULT_WEB_FREE_DAY");
		if(SESSION("Notice")){
			$this->Notice = SESSION("Notice");
		}
		if(session("login")==1){
			session("login",0);
			$this->login = 1;
		}
	}


	protected function GetJiangJin($money_line,$member_id,$member_id_from,$order_money_number,$remark,$remark_en=""){
		//推荐奖
		
		$recom_member= M("member")->where(array("id"=>$member_id))->find();
		$User_From=$recom_member;
		if($member_id_from>0){
			$User_From= M("member")->where(array("id"=>$member_id_from))->find();
			if(!$User_From){
				$User_From= $recom_member;
			}
		}

		$recom_PriceMoneyInfo = M("PriceMoneyInfo")->where(array("member_id"=>$recom_member['id']))->order("id desc")->find();
		
		$money_data = array(
			"type"=>2,
			"member_id"=>$recom_member['id'],
			"member_username"=>$recom_member['username'],
			"order_id"=>$order_money_number,
			"title"=>$remark,
			"title_en"=>$remark_en,
			"title_sp"=>$remark_en,
			"new_price"=>$recom_PriceMoneyInfo['new_price']+$money_line,
			"old_price"=>$recom_PriceMoneyInfo['new_price'],
			"price"=>$money_line,
			"adddate"=>date("Y-m-d",time()),
			"addtime"=>date("Y-m-d H:i:s",time()+1),
		);
		M("PriceMoneyInfo")->data($money_data)->add();
		
		/*
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
			"new_price"=>$recom_PriceInfo['new_price']+$money_line,
			"old_price"=>$recom_PriceInfo['new_price']+$money_line,
			"price"=>$money_line,
			"adddate"=>date("Y-m-d",time()),
			"addtime"=>date("Y-m-d H:i:s",time()+1),
		);
		M("PriceInfo")->data($price_data)->add();
		*/
		//金额动态
		$log_data = array(
			"type"=>5,
			"member_id"=>$recom_member['id'],
			"member_username"=>$recom_member['username'],
			"change_id"=>$User_From['id'],
			"change_username"=>$User_From['username'],
			"order_id"=>$order_money_number,
			"title"=>$remark,
			"title_en"=>$remark_en,
			"title_sp"=>$remark_en,
			"adddate"=>date("Y-m-d",time()),
			"addtime"=>date("Y-m-d H:i:s",time()),
		);
		M("ChangeLog")->data($log_data)->add();

		//金额累计
		$ChangeLogInfo = M("ChangeLogInfo")->where(array("adddate"=>date("Y-m-d",time()),"member_id"=>$recom_member['id']))->find();
		if($ChangeLogInfo){
			$log_data_info = array(
				"recom_price"=>$ChangeLogInfo['recom_price']+$money_line,
			);
			M("ChangeLogInfo")->where(array("adddate"=>date("Y-m-d",time()),"member_id"=>$recom_member['id']))->data($log_data_info)->save();
		}else{
			$log_data_info = array(
				"member_id"=>$recom_member['id'],
				"member_username"=>$recom_member['username'],
				"recom_price"=>$money_line,
				"adddate"=>date("Y-m-d",time()),
				"addtime"=>date("Y-m-d H:i:s",time()+2),
			);
			M("ChangeLogInfo")->data($log_data_info)->add();
		}
	}

	protected function InsertOneZhuce($money_line,$member_id,$remark,$remark_en=""){
		$recom_member= M("member")->where(array("id"=>$member_id))->find();
		$recom_PriceInfo = M("PriceInfo")->where(array("member_id"=>$recom_member['id']))->order("id desc")->find();
		$price_data = array(
										"type"=>5,
										"member_id"=>$recom_member['id'],
										"member_username"=>$recom_member['username'],
										"order_id"=>"",
										"title"=>$remark,
										"title_en"=>$remark_en,
										"title_sp"=>$remark_en,
										"new_price"=>$recom_PriceInfo['new_price']+$money_line,
										"old_price"=>$recom_PriceInfo['new_price'],
										"price"=>$money_line,
										"adddate"=>date("Y-m-d",time()),
										"addtime"=>date("Y-m-d H:i:s",time()+1),
		);
		M("PriceInfo")->data($price_data)->add();
	}

	protected function qrcode($id){
        Vendor('phpqrcode.phpqrcode');
        //生成二维码图片
        $object = new \QRcode();
        $url='http://www.blamember.com/?reg='.$id;//网址或者是文本内容
        $level=3;
        $size=4;
        $errorCorrectionLevel =intval($level) ;//容错级别
        $matrixPointSize = intval($size);//生成图片大小
        $object->png($url, 'Public/Uploads/qrcodes/'.$id.'.png', $errorCorrectionLevel, $matrixPointSize, 2);
    }
}