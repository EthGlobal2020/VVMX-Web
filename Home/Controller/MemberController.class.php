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
			$koufeitype=I("koufei_type");		
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
			if(M("Member")->where(array("telephone"=>$telephone))->count()>0){
				$this->error(L("class-member-telephone"));
			}
			$date = date("Y-m-d",time());
			$time = date("Y-m-d H:i:s",time());
			/*
			if($position==1){
				if(!$left_member = M("Member")->where(array("p_id"=>$p_id,"position"=>0))->find()){
					$this->error(L("class-member-memberleft"));
				}
			}*/

			if(trim(I("username"))==''){
				$this->error("用户名不能为空！");
			}
			if(trim(I("userpass"))==''){
				$this->error("登录密码不能为空！");
			}
			if(trim(I("paypass"))==''){
				$this->error("支付密码不能为空！");
			}
			if(trim(I("userpass"))!=trim(I("userpass_2"))){
				$this->error("登录密码两次输入不一致！");
			}
			if(trim(I("paypass"))!=trim(I("paypass_2"))){
				$this->error("支付密码两次输入不一致！");
			}

			if(M("member")->where(array("username"=>I("username")))->find()){
				$this->error("系统已经存在相同用户名，请换一个！");
			}

			if(!$r_username || $r_username==''){
				$this->error("没有填写推荐人！");
			}

			if(!$recom_member = M("member")->where(array("username"=>$r_username))->find()){
				$this->error(L("class-member-username"));
			}	
			/*
			if(!$p_member = M("member")->where(array("id"=>$p_id,"username"=>$p_username))->find()){
				$this->error(L("class-member-contact"));
			}
			if(M("member")->where(array("p_id"=>$p_id,"p_username"=>$p_username,"position"=>$position))->count()>0){
				$this->error(L("class-member-error"));
			}*/
			$PriceInfo = M("PriceInfo")->where(array("member_id"=>$Member['id']))->order("id desc")->find();
			$ShareInfo = M("ShareNumberInfo")->where(array("member_id"=>$Member['id']))->order("id desc")->find();

			$old_PriceInfo=0;

			if($PriceInfo){
				$old_PriceInfo=$PriceInfo['new_price'];
			}

			$price_zhuce=$PackageType['price'] * intval(C("DEFAULT_WEB_ZHUCE_PERCENT")) * 0.01;
			$price_xianjin=$PackageType['price'] * (100-intval(C("DEFAULT_WEB_ZHUCE_PERCENT"))) * 0.01;

			if($koufeitype=='2'){
				$price_zhuce=0;
				$price_xianjin=$PackageType['price'];
			}

			if($price_zhuce>$old_PriceInfo){
				$this->error(L("注册币不足！"));
			}

			if($price_xianjin>$ShareInfo['new_number']){
				$this->error(L("现金币不足！"));
			}

			//echo '总额：'.$PackageType['price'].'注册币：'.$price_zhuce.'现金币：'.$price_xianjin;die;

			if($price_zhuce<=$old_PriceInfo){
				$_POST['new_price'] = $old_PriceInfo-$price_zhuce;
				$_POST['price'] = $price_zhuce;
				$_POST['old_price'] = $old_PriceInfo;
				$_POST['title'] = I("username")." 注册账号支出";
				$_POST['title_en'] = I("username")." Register";
				$_POST['title_sp'] = I("username")."  gastos para el registro de cuenta";
				$_POST['type']='2';
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

						//再扣现金币
						
																
						$data = array(
											"type"=>1,
											"member_id"=>$Member['id'],
											"member_username"=>$Member['username'],
											"reinvest_id"=>0,
											"title"=>"注册扣现金币",
											"title_en"=>"Stock thaw",
											"title_sp"=>"Stock thaw",
											"new_number"=>$ShareInfo['new_number'] - $price_xianjin,
											"old_number"=>$ShareInfo['new_number'],
											"number"=>$price_xianjin,
											"adddate"=>date("Y-m-d",time()),
											"addtime"=>date("Y-m-d H:i:s",time()),
						);
						M("ShareNumberInfo")->data($data)->add();

				

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
							"price"=>$price_zhuce,
							"adddate"=>$date,
							"addtime"=>$time,
						);

					

							$_POST['userpass'] = I("userpass");
							$_POST['paypass'] = I("paypass");
							$_POST['r_id'] = $recom_member['id'];
							$_POST['r_username'] = $r_username;
							$_POST['position'] = $position;
							$_POST['p_id'] = 12;
							$_POST['p_username'] ='test';
							$_POST['money_n']=$PackageType['price'] * $PackageType['status_profit_max'];
							$_POST['tjstr'] = $recom_member['tjstr'].','.$recom_member['id'];
							$temptjstr=$recom_member['tjstr'].','.$recom_member['id'];
							$AddMember = D("AddMember");
							if (!$AddMember->create()){
								session("model",1);
								$this->error($AddMember->getError(),U('Home/Member/register'));
							}else{
								if($result2 = $AddMember->add()){

									$map['id']  = array('in',$temptjstr);
									//$Model = new \Think\Model();
									//$Model->execute("update zyx_member set money_yeji=money_yeji+".$PackageType['price']." where id IN");
									M("Member")->where($map)->setInc('money_yeji',$PackageType['price']);
									$this->success(L('reg-success'),U('Home/Member/register'));
								}
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
			$this->xianjibi = M("ShareNumberInfo")->where(array("member_id"=>$Member['id']))->order("id desc")->find()['new_number'];
			$this->User = $Member;
			$this->member_list = $member_list;
			$this->package_list = M("PackageType")->where(array("status"=>1))->order("price asc")->select();
			$this->picture = "Public/Public/Images/default.jpg";
			$this->title=L('my-register-register');
			if(ismobile()){
				$this->theme('erci')->display();
			}
			else{
				$this->display();
			}

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
		$Model = new \Think\Model();
		$sql="SELECT IFNULL(MAX(`money_yeji`),0) AS M,IFNULL(SUM(`money_yeji`),0)-IFNULL(MAX(`money_yeji`),0) AS O FROM `zyx_member` WHERE r_id=".$Member['id'];
		//$this->yejiinfo = $Model->query($sql);
        $temp111=$Model->query($sql);
        $yeji_da= $temp111[0]['m'];
        $yeji_other= $temp111[0]['o'];

        
		$sql="SELECT IFNULL(MAX(R.`price`),0) AS ddd FROM zyx_member M INNER JOIN `zyx_package_type` R ON M.package_type=R.`id` WHERE M.`r_id`=".$Member['id']." AND `money_yeji`=".$yeji_da;
		$tempddd = $Model->query($sql);
		$yeji_da=$yeji_da + $tempddd[0]['ddd'];
        //$this->yejiinfo[0]['m']=999;
		
        //var_dump($sql);die;
		$sql="SELECT IFNULL(SUM(R.`price`),0) AS eee FROM zyx_member M INNER JOIN `zyx_package_type` R ON M.package_type=R.`id` WHERE M.`r_id`=".$Member['id'];
		$tempeee = $Model->query($sql);
		$yeji_other=$yeji_other+$tempeee[0]['eee']-$tempddd[0]['ddd'];
      
        $this->yejiinfo=array(array("m"=>$yeji_da,"o"=>$yeji_other));

		$MemberList = M("Member")->where(array("delete"=>0,"r_id"=>$Member['id']))->select();
		$this->member_number = count($MemberList);
		$this->package_list = M("PackageType")->where(array("status"=>1))->select();
		$this->member_list = $MemberList;

		$this->title=L('my-member-members');
		if(ismobile()){
			$this->theme('erci')->display();
		}
		else{
			$this->display();
		}
	}
}