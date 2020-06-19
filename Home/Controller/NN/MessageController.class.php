<?php
namespace Home\Controller;
use Think\Controller;
class MessageController extends CommonController {
    public function index(){
        $this->display();
    }
	public function feedback(){
		$Member = SESSION("MemberID");
		if(IS_POST){
			$picture = UploadImg('picture',"Information");
			$type = (int)I("type");
			switch($type){
				case 1:
					$type = "账号冻结";
				;break;
				case 2:
					$type = "投诉他人违规";
				;break;
				case 3:
					$type = "提现订单不匹配";
				;break;
				case 4:
					$type = "买方拒绝付款";
				;break;
				case 5:
					$type = "卖方不确认收款";
				;break;
				case 6:
					$type = "买方收款信息有误";
				;break;
				case 7:
					$type = "收不到短信";
				;break;
				case 8:
					$type = "奖金计算错误";
				;break;
				case 9:
					$type = "更改个人信息";
				;break;
				case 10:
					$type = "其他问题";
				;break;
			}
			
			$data = array(
				"title"=>I("title"),
				"content"=>I("content"),
				"type"=>$type,
				"picture"=>$picture,
				"member_id"=>$Member["id"],
				"member_username"=>$Member["username"],
				"member_telephone"=>$Member["telephone"],
				"addtime"=>date("Y-m-d H:i:s",time()),
			);
			if(M("Information")->data($data)->add()){
				$this->error(L("class-message-errorok"));
			}else{
				$this->error(L("class-message-errorno"));
			}
		}else{
			$Member = SESSION("MemberID");
			$this->InformationList = M("Information")->where(array("member_id"=>$Member['id']))->order("id desc")->select();
			$this->display();
		}
	}
	public function showMessage(){
		$id = I("id");
		$Member = SESSION("MemberID");
		$this->Information = M("Information")->where(array("id"=>$id,"member_id"=>$Member['id']))->find();
		$this->display();
	}
}