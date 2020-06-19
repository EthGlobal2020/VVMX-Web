<?php
namespace Admin\Controller;
use Think\Controller;
class ConfigController extends CommonController {
	public function system(){
		if(IS_POST){
			$upload = new \Think\Upload();
			$upload->maxSize   =  104857600 ;
			$upload->exts      =  array('jpg','png','jpeg');
			$upload->rootPath  =  './Public/Uploads/';
			$upload->savePath  =  '';
			$info   =   $upload->upload();
			if($info) {
				foreach($info as $file){
					$_POST[$file['key']] = $upload->rootPath.$file['savepath'].$file['savename'];

				}
			}
			if($_POST["DEFAULT_WEB_LOGO"]==""){
				$_POST["DEFAULT_WEB_LOGO"] = C("DEFAULT_WEB_LOGO");
			}
			if($_POST["DEFAULT_WEB_LOGIN_IMG"]==""){
				$_POST["DEFAULT_WEB_LOGIN_IMG"] = C("DEFAULT_WEB_LOGIN_IMG");
			}
            if(F("system",$_POST,CONF_PATH)){
				session("model",4);
            }else{
				session("model",3);
            }
			$this->redirect('Admin/Config/system',null, 0, '页面跳转中...');
        }else{
			$this->model = session("model");
			session("model",null);
            $this->display();
        }
	}
	public function app(){
		if(IS_POST){
			if(F("app",$_POST,CONF_PATH)){
				session("model",4);
			}else{
				session("model",3);
			}
			$this->redirect('Admin/Config/app',null, 0, '页面跳转中...');
		}else{
			$this->model = session("model");
			session("model",null);
			$this->display();
		}
	}
	public function sms(){
		if(IS_POST){
			if(F("sms",$_POST,CONF_PATH)){
				session("model",4);
			}else{
				session("model",3);
			}
			$this->redirect('Admin/Config/sms',null, 0, '页面跳转中...');
		}else{
			$this->model = session("model");
			session("model",null);
			$this->display();
		}
	}
	
	public function textSMS(){
		$SMS_URL = C("DEFAULT_WEB_SMS_URL");
		$SMS_ACCOUNT = C("DEFAULT_WEB_SMS_ACCOUNT");
		$SMS_KEY = C("DEFAULT_WEB_SMS_KEY");
		$SMS_PORT = C("DEFAULT_WEB_SMS_PORT");
		$SMS_NUMBER = C("DEFAULT_WEB_SMS_NUMBER");
		$telephone = I("telephone");
		$content = I("remark"); 
		$User = session("MemberID");
		$number = rand(pow(10,($SMS_NUMBER-1)),pow(10,$SMS_NUMBER)-1);
		if($Verify = M("Verify")->where(array("member_id"=>$User['id']))->order("id desc")->find()){
			$new = time();
			if(date("Y-m-d H:i:s",strtotime($Verify['addtime'])+60)<date("Y-m-d H:i:s",$new)){
				M("Verify")->data(array("member_id"=>$User['id'],"number"=>$number,"addtime"=>date("Y-m-d H:i:s",time()),"type"=>1))->add();		
			}else{
				$minute = $new - strtotime($Verify['addtime']);
				echo "发送时间间隔小于".$minute."秒 小于60秒 无法发送";
				die;
			}
		}else{
			M("Verify")->data(array("member_id"=>$User['id'],"number"=>$number,"addtime"=>date("Y-m-d H:i:s",time()),"type"=>1))->add();		
		}
		
		sendSMS($SMS_URL,$SMS_ACCOUNT,$SMS_KEY,$SMS_PORT,$telephone,$content." 您的验证码是:".$number,'','');
		//getStat($DEFAULT_WEB_SMS_URL,$DEFAULT_WEB_SMS_ACCOUNT,$DEFAULT_WEB_SMS_KEY);
		//sendSMS($DEFAULT_WEB_SMS_URL,$DEFAULT_WEB_SMS_ACCOUNT,$DEFAULT_WEB_SMS_KEY,"11",$telephone,$remark,"3","");
	}
	
	public function setPay(){
		if(IS_POST){
            if(F("pay",$_POST,CONF_PATH)){
				session("model",4);
            }else{
				session("model",3);
            }
			$this->redirect('Admin/Config/setPay', null, 0, '页面跳转中...');
		}else{
			$this->model = session("model");
			session("model",null);
            $this->display();			
		}
	}
	public function setThumb(){
		$_POST['DEFAULT_IMAGE_THUMB_PICTURE'] = UploadImg("DEFAULT_IMAGE_THUMB_PICTURE");
		if(!$_POST['DEFAULT_IMAGE_THUMB_PICTURE']){
			$_POST['DEFAULT_IMAGE_THUMB_PICTURE'] = C('DEFAULT_IMAGE_THUMB_PICTURE');
		}
		if(IS_POST){
			if(F("thumb",$_POST,CONF_PATH)){
				session("model",4);
			}else{
				session("model",3);
			}
			$this->redirect('Admin/Config/setThumb', null, 0, '页面跳转中...');
		}else{
			$this->model = session("model");
			session("model",null);
			$this->display();
		}
	}
	public function setEmail(){
		if(IS_POST){
            if(F("email",$_POST,CONF_PATH)){
				session("model",4);
            }else{
				session("model",3);
            }
			$this->redirect('Admin/Config/setEmail', null, 0, '页面跳转中...');
		}else{
			$this->model = session("model");
			session("model",null);
            $this->display();			
		}
	}
	public function delete(){
		if(isset($_GET['id'])){
			/*真实删除操作*/
			$Delete = M("Delete");
			if($data = $Delete->where(array("id"=>I("id")))->find()){
				if(M($data["delete_table"])->where(array("id"=>$data["delete_id"]))->delete()){
					$Delete->where(array("id"=>I("id")))->delete();
					$this->success("数据删除成功,不可找回!");
				}else{
					$this->error("数据删除失败！");
				}
			}else{
				$this->error("没有找到该信息");
			}
		}else{

			$result = M("Delete")->order("id desc")->select();
			//分页开始
			$count = count($result);
			$Page = new \Think\Page($count,10);
			$show = $Page->show();
			$lists = array_slice($result,$Page->firstRow,$Page->listRows);
			$this->deleteList = $lists;
			$this->page = $show;

			//$this->User = M("User")->where(array("delete"=>0))->select();
			$this->display();
		}
	}
	public function backDelete(){
		/*找回操作*/
		$Delete = M("Delete");
		if($data = $Delete->where(array("id"=>I("id")))->find()){
			if(M($data["delete_table"])->where(array("id"=>$data["delete_id"]))->data(array("delete"=>0))->save()){
				$Delete->where(array("id"=>I("id")))->delete();
				$this->success("找回成功！");
			}else{
				$this->error("找回失败！");
			}
		}else{
			$this->error("没有找到该信息");
		}
	}
}
