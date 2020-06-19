<?php
namespace Home\Controller;
use Think\Controller;
class AjaxController extends CommonController {
    public function getChildNumber(){
		$id = I("id");
		$MemberList = M("Member")->field("id,p_id,name,username,position")->where(array("delete"=>0))->select();
		if($LeftMember = M("Member")->where(array("p_id"=>$id,"position"=>0))->find()){
			$member_list_left =array_multi2single(member_list($MemberList,$LeftMember['id']));
			$arr_left = array();
			foreach($member_list_left as $val_left){
				$arr_left[] = $val_left['id'];
			}
			$member_number_left = count($arr_left)+1;
		}else{
			$member_number_left = 0;
		}
		if($RightMember = M("Member")->where(array("p_id"=>$id,"position"=>1))->find()){
			$member_list_right = array_multi2single_2(member_list($MemberList,$RightMember['id']));
			$arr_right = array();
			foreach($member_list_right as $val_right){
				$arr_right[] = $val_right['id'];
			}
			$member_number_right = count($arr_right)+1;
		}else{
			$member_number_right = 0;
		}
		
        $this->AjaxReturn(array("result"=>true,"member_number_left"=>$member_number_left,"member_number_right"=>$member_number_right));
    }
	public function getMemberReg(){
		$id = I("id");
		$position = I("position");
		if($position==1){
			if(M("Member")->where(array("p_id"=>$id,"position"=>0))->find()){
				$this->AjaxReturn(array("result"=>true));
			}else{
				$this->AjaxReturn(array("result"=>false,"message"=>L('class-ajax-leftarea')));
			}
		}else{
			if(M("Member")->where(array("p_id"=>$id,"position"=>0))->find()){
				$this->AjaxReturn(array("result"=>false,"message"=>L('class-ajax-isregistered')));
			}else{
				$this->AjaxReturn(array("result"=>true));
			}
			
		}
		
	}
	public function textSMS(){
		$SMS_URL = C("DEFAULT_WEB_SMS_URL");
		$SMS_ACCOUNT = C("DEFAULT_WEB_SMS_ACCOUNT");
		$SMS_KEY = C("DEFAULT_WEB_SMS_KEY");
		$SMS_PORT = C("DEFAULT_WEB_SMS_PORT");
		$SMS_NUMBER = C("DEFAULT_WEB_SMS_NUMBER");
		$SMS_REMARK = C("DEFAULT_WEB_SMS_REMARK");
		$language = session("language");
		if($language=="se"){
			$SMS_REMARK = C("DEFAULT_WEB_SMS_REMARK_SE");
		}else if($language=="en"){
			$SMS_REMARK = C("DEFAULT_WEB_SMS_REMARK_EN");
		}else{
			$SMS_REMARK = C("DEFAULT_WEB_SMS_REMARK");
		}
		$content = I("remark"); 
		$type = I("type",1,"int");
		$User = session("MemberID");
		$number = rand(pow(10,($SMS_NUMBER-1)),pow(10,$SMS_NUMBER)-1);
		if($Verify = M("Verify")->where(array("member_id"=>$User['id'],"type"=>$type))->order("id desc")->find()){
			$new = time();
			if(date("Y-m-d H:i:s",strtotime($Verify['addtime'])+60)<date("Y-m-d H:i:s",$new)){
				M("Verify")->data(array("member_id"=>$User['id'],"member_username"=>$User['username'],"number"=>$number,"addtime"=>date("Y-m-d H:i:s",time()),"type"=>$type))->add();		
			}else{
				$minute = $new - strtotime($Verify['addtime']);
				echo "发送时间间隔小于".$minute."秒 小于60秒 无法发送";
				die;
			}
		}else{
			M("Verify")->data(array("member_id"=>$User['id'],"member_username"=>$User['username'],"number"=>$number,"addtime"=>date("Y-m-d H:i:s",time()),"type"=>$type))->add();		
		}	
		$result = send_sms($SMS_URL,$SMS_ACCOUNT,$SMS_KEY,$SMS_PORT,$User['telephone'],$content.L('login-code').":".$number." ".$SMS_REMARK,'','');
		if($result['result']){
			$this->AjaxReturn($result['message']);
		}else{
			$this->AjaxReturn(L("public-system-error"));
		}
	}
	public function set_telephone(){
		$User = session("MemberID");
		$varify_addtime = session("varify_addtime");
		$telephone = I("telephone");
			$result = M("Member")->where(array("id"=>$User['id']))->data(array("telephone"=>$telephone))->save();
			if($result||$result==0){
				$this->AjaxReturn(true);
			}else{
				$this->AjaxReturn(false);
			}		
	}
	public function set_password(){
		$User = session("MemberID");
		$password = I("password");
		$repassword = I("repassword");
		if($password!=$repassword){
			$this->AjaxReturn(false);
		}
		$result = M("Member")->where(array("id"=>$User['id']))->data(array("userpass"=>md5($password)))->save();
		if($result||$result==0){
			$this->AjaxReturn(true);
		}else{
			$this->AjaxReturn(false);
		}		
	}
	public function set_pay_password(){
		$User = session("MemberID");
		$password = I("password");
			$repassword = I("repassword");
			if($password!=$repassword){
				$this->AjaxReturn(false);
			}
			$result = M("Member")->where(array("id"=>$User['id']))->data(array("paypass"=>$password))->save();
			if($result||$result==0){
				$this->AjaxReturn(true);
			}else{
				$this->AjaxReturn(false);
			}		
	}
	public function set_china_card(){
		$User = session("MemberID");
		$china_card = I("china_card");
			$result = M("Member")->where(array("id"=>$User['id']))->data(array("china_card"=>$china_card))->save();
			if($result||$result==0){
				$this->AjaxReturn(true);
			}else{
				$this->AjaxReturn(false);
			}		
	}
	public function set_extend_china_card(){
		$User = session("MemberID");
		$varify_addtime = session("varify_addtime");
		if(date("Y-m-d H:i:s",strtotime($varify_addtime)+60*60)>date("Y-m-d H:i:s",time())){				
			$extend_china_card = I("extend_china_card");
			$result = M("Member")->where(array("id"=>$User['id']))->data(array("extend_china_card"=>$extend_china_card))->save();
			if($result||$result==0){
				$this->AjaxReturn(true);
			}else{
				$this->AjaxReturn(false);
			}		
		}else{
			session("verify_code",null);
			session("varify_addtime",null);
			$this->error(L('class-user-identifyingcodeoverdue'),U("Home/User/security"));
		}
	}
	public function set_name(){
		$User = session("MemberID");
		$name = I("name");
			$result = M("Member")->where(array("id"=>$User['id']))->data(array("name"=>$name))->save();
			if($result||$result==0){
				$this->AjaxReturn(true);
			}else{
				$this->AjaxReturn(false);
			}		
	}
	public function set_email(){
		$User = session("MemberID");
		$varify_addtime = session("varify_addtime");
		if(date("Y-m-d H:i:s",strtotime($varify_addtime)+60*60)>date("Y-m-d H:i:s",time())){				
			$email = I("email");
			$result = M("Member")->where(array("id"=>$User['id']))->data(array("email"=>$email))->save();
			if($result||$result==0){
				$this->AjaxReturn(true);
			}else{
				$this->AjaxReturn(false);
			}		
		}else{
			session("verify_code",null);
			session("varify_addtime",null);
			$this->error(L('class-user-identifyingcodeoverdue'),U("Home/User/security"));
		}
	}
	//异步上传图片
	public function UpdateImage(){
		$sell_id = I("sell_id");
		$config = array(
			'maxSize'    =>    3145728,
			'rootPath'   =>    "Public/Uploads/",
			'savePath'   =>    '',
			'saveName'   =>    array('uniqid',''),
			'exts'       =>    array('jpg', 'gif', 'png', 'jpeg'),
			'subName'    =>    array('date','Ymd'),
		);
		$upload = new \Think\Upload($config);
		$info   =   $upload->upload();
		if(!$info) {
			$this->error($upload->getError());
		}else{
			$Picture = 'http://'.$_SERVER['HTTP_HOST']."/".$upload->rootPath.$info["imgfile"]["savepath"].$info["imgfile"]["savename"];//获取图片文件路径
			if($sell_id!=""){
				M("ServerOutPrice")->where(array("id"=>$sell_id))->data(array("picture"=>$Picture))->save();
			}		
			$this->AjaxReturn(array("result"=>true,"imgurl"=>$Picture));
		}		
    }
}