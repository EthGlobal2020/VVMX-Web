<?php
namespace Admin\Controller;
use Think\Controller;
class MainController extends CommonController {
	public function index(){
		
		$Model = new \Think\Model();
		$sql="SELECT SUM(`new_number`) as m FROM zyx_share_number_info WHERE `id` IN (SELECT Max(`id`) FROM `zyx_share_number_info` group by `member_id`)";
		$yejiinfo= $Model->query($sql);
		$this->XianJiN = $yejiinfo[0]['m'];

		$sql="SELECT SUM(`new_price`) as m FROM zyx_price_info WHERE `id` IN (SELECT Max(`id`) FROM `zyx_price_info` group by `member_id`)";
		$yejiinfo= $Model->query($sql);
		$this->ZhuCe = $yejiinfo[0]['m'];

		$sql="SELECT SUM(`new_price`) as m FROM zyx_price_money_info WHERE `id` IN (SELECT Max(`id`) FROM `zyx_price_money_info` group by `member_id`)";
		$yejiinfo= $Model->query($sql);
		$this->JiangJin = $yejiinfo[0]['m'];

		$sql="SELECT SUM(`new_number`) as m FROM zyx_price_integral_info WHERE `id` IN (SELECT Max(`id`) FROM `zyx_price_integral_info` group by `member_id`)";
		$yejiinfo= $Model->query($sql);
		$this->FenHong = $yejiinfo[0]['m'];

		//$this->MemberNumber = M("Member")->where(array("delete"=>0))->count();
		//$this->NewsNumber = M("News")->where(array("delete"=>0))->count();
		//$this->XianJiN = M("share_number_info")->where(array("delete"=>0))->count();
		$this->display();
	}
	public function packageType(){
		if(session("packagetypeid")&&session("model")==2){
			$PackageType = M("PackageType")->where(array("id"=>session("packagetypeid")))->find();
			$this->Package = $PackageType;
		}else{
			$package = array(
				"title"=>urldecode(I("title")),
				"en_title"=>urldecode(I("en_title")),
				"sp_title"=>urldecode(I("sp_title")),
			);
		}
		$this->model = session("model");
		session("model",null);
		$count = M("PackageType")->where(array("delete"=>0))->count();
		$Page = new \Think\Page($count,40);
		$show = $Page->show();
		$list = M("PackageType")->where(array("delete"=>0))->order('id')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('PackageType',$list);
		$this->assign('page',$show);
		$this->p = I("p",0);
		$this->display();
	}
	public function addPackageType(){
		if(IS_POST){	
			$AddPackageType = D("AddPackageType");
			if(session("packagetypeid")){
				$_POST["id"] = session("packagetypeid");
			}					
			if (!$AddPackageType->create()){
				session("model",1);
				$this->error($AddPackageType->getError(),U('Admin/Main/packageType',array("title"=>urlencode(I("title")),"en_title"=>urlencode(I("en_title")),"sp_title"=>urlencode(I("sp_title")),"p"=>I("p"))));
			}else{
				if(session("packagetypeid")){
					session("packagetypeid",null);
					$result = $AddPackageType->save();
					if($result||$result==0){	
						session("model",4);						
					}else{
						session("model",3);
					}						
				}else{
					if($AddPackageType->add()){
						session("model",4);
					}else{
						session("model",3);
					}										
				}
				$this->redirect('Admin/Main/packageType', array("p"=>I('p')), 0, '页面跳转中...');
			}
		}else{
			if(I("id")!=""){
				//有id  进入修改
				session("packagetypeid",I("id"));
				session("model",2);			
			}else{
				session("packagetypeid",null);
				session("model",1);
			}	
			$this->redirect('Admin/Main/packageType', array("p"=>I('p')), 0, '页面跳转中...');
		}
	}
	public function statusPackageType(){
		if(changeStatus("PackageType",I("id"),I("status"))){		
			session("model",4);
		}else{
			session("model",3);
		}
		$this->redirect('Admin/Main/packageType', array("p"=>I('p')), 0, '页面跳转中...');
	}
	public function deletePackageType(){
		$id = I("id");
		if(del("PackageType",$id)){
			$this->success("加入回收站 成功");
		}else{
			$this->error("加入回收站 失败");
		}
	}

	/**新闻**/
	public function news(){
		$this->model = session("model");
		session("model",null);
		$count = M("News")->where(array("delete"=>0))->count();
		$Page = new \Think\Page($count,10);
		$show = $Page->show();
		$list = M("News")->where(array("delete"=>0))->order('id')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('NewsList',$list);
		
		$this->assign('page',$show);
		$this->p = I("p",0);
		$this->display();
	}
	public function addNews(){
		if(IS_POST){
			$AddNews = D("AddNews");
			if(session("newsid")){
				$_POST["id"] = session("newsid");
			}
			if (!$AddNews->create()){
				if(session("newsid")) {
					$this->error($AddNews->getError(), U('Admin/Main/addNews', array("id" => session("newsid"))));
				}else{
					$this->error($AddNews->getError(),U('Admin/Main/addNews'));
				}
			}else{
				if(session("newsid")){
					session("newsid",null);
					$result = $AddNews->save();
					if($result||$result==0){
						session("model",4);
					}else{
						session("model",3);
					}
				}else{
					if($AddNews->add()){
						session("model",4);
					}else{
						session("model",3);
					}
				}
				$this->redirect('Admin/Main/news', array("p"=>I('p')), 0, '页面跳转中...');
			}
		}else{
			if(I("id")!=""){
				//有id  进入修改
				session("newsid",I("id"));
				if($News = M("News")->where(array("id"=>I('id'),"delete"=>0))->find()){
					$this->News = $News;
				}
			}else{
				session("newsid",null);
			}
			$this->p = I("p");
			$this->display();
		}
	}
	public function recomNews(){
		if(changeRecom("News",I("id"),I("recom"))){
			session("model",4);
		}else{
			session("model",3);
		}
		$this->redirect('Admin/Main/news', array("p"=>I('p')), 0, '页面跳转中...');
	}
	public function statusNews(){
		if(changeStatus("News",I("id"),I("status"))){
			session("model",4);
		}else{
			session("model",3);
		}
		$this->redirect('Admin/Main/news', array("p"=>I('p')), 0, '页面跳转中...');
	}
	public function deleteNews(){
		$id = I("id");
		if(del("News",$id)){
			$this->success("加入回收站 成功");
		}else{
			$this->error("加入回收站 失败");
		}
	}

	/**信息中心**/

	public function information(){
		if(session("informationid")&&session("model")==2){
			$Information = M("Information")->where(array("id"=>session("informationid")))->find();
			$this->Information = $Information;
		}else{
			$package = array(
				"title"=>urldecode(I("title")),
				"en_title"=>urldecode(I("en_title")),
				"sp_title"=>urldecode(I("sp_title")),
			);
		}
		$this->model = session("model");
		session("model",null);
		$count = M("Information")->where(array("delete"=>0))->count();
		$Page = new \Think\Page($count,10);
		$show = $Page->show();
		$list = M("Information")->where(array("delete"=>0))->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('InformationList',$list);
		$this->assign('page',$show);
		$this->p = I("p",0);
		$this->display();
	}
	public function addInformation(){
		if(IS_POST){
			$AddInformation = D("AddInformation");
			if(session("informationid")){
				$_POST["id"] = session("informationid");
			}
			if (!$AddInformation->create()){
				if(session("informationid")) {
					$this->error($AddInformation->getError(), U('Admin/Main/addInformation', array("id" => session("informationid"))));
				}else{
					$this->error($AddInformation->getError(),U('Admin/Main/addInformation'));
				}
			}else{
				if(session("informationid")){
					session("informationid",null);
					$result = $AddInformation->save();
					if($result||$result==0){
						session("model",4);
					}else{
						session("model",3);
					}
				}else{
					if($AddInformation->add()){
						session("model",4);
					}else{
						session("model",3);
					}
				}
				$this->redirect('Admin/Main/information', array("p"=>I('p')), 0, '页面跳转中...');
			}
		}else{
			if(I("id")!=""){
				//有id  进入修改
				session("informationid",I("id"));
				session("model",2);			
			}else{
				session("informationid",null);
				session("model",1);
			}	
			$this->redirect('Admin/Main/information', array("p"=>I('p')), 0, '页面跳转中...');
		}
	}
	public function recomInformation(){
		if(changeRecom("Information",I("id"),I("recom"))){
			session("model",4);
		}else{
			session("model",3);
		}
		$this->redirect('Admin/Main/information', array("p"=>I('p')), 0, '页面跳转中...');
	}
	public function statusInformation(){
		if(changeStatus("Information",I("id"),I("status"))){
			session("model",4);
		}else{
			session("model",3);
		}
		$this->redirect('Admin/Main/information', array("p"=>I('p')), 0, '页面跳转中...');
	}
	public function deleteInformation(){
		$id = I("id");
		if(del("Information",$id)){
			$this->success("加入回收站 成功");
		}else{
			$this->error("加入回收站 失败");
		}
	}

	//会员管理
	public function member(){
		
		if(session("setmemberid")&&session("model")==2){
			$member_id = session("setmemberid");
			$this->PriceInfo = M("PriceInfo")->where(array("delete"=>0,"member_id"=>$member_id))->order("id desc")->find();
			$this->PriceMoneyInfo = M("PriceMoneyInfo")->where(array("delete"=>0,"member_id"=>$member_id))->order("id desc")->find();
			$this->free_share_price = $Info['new_number']?$Info['new_number']:0;
			$this->PriceIntegralInfo = M("PriceIntegralInfo")->where(array("delete"=>0,"member_id"=>$Member['id']))->order("id desc")->find();
		}	
		$this->model = session("model");
		session("model",null);
		$where = array("delete"=>0);
		$status = I("status");
		$title = urldecode(I("text"));
		if($status!=""){
			$where['status'] = $status;
			$this->status = $status;
		}
		if($title!=""){
			$where["username"] = array("like","%".$title."%");
			$this->text = $title;
		}
		$page = 20;
		$p = I("p",1,"int");
		$list = D('MemberRelation')->order('id desc')->page($p.','.$page)->relation(true)->select();
		$this->assign('Member',$list);// 赋值数据集
		$count = M('Member')->where($where)->count();// 查询满足要求的总记录数
		$Page = new \Think\Page($count,$page);// 实例化分页类 传入总记录数和每页显示的记录数
		foreach($where as $key=>$val) {
			$Page->parameter[$key] = urlencode($val);
		}
		$Page->parameter['text'] = $title;
		$show = $Page->show();// 分页显示输出
		$this->assign('page',$show);// 赋值分页输出
		$this->assign("PackageType",M("PackageType")->where(array("delete"=>0))->select());
		$this->p = I("p",0);
		$this->display();
	}
	public function setMember(){
		if(IS_POST){
			$SetMember = D("SetMember");
			if(session("setmemberid")){
				$_POST["id"] = session("setmemberid");
			}
			$Member = M("Member")->where(array("id"=>session("setmemberid")))->find();
			$_POST['old_paypass'] = $Member["paypass"];
			$_POST['old_dgcpass'] = $Member["dgcpass"];
			if (!$SetMember->create()){
				if(session("setmemberid")) {
					$this->error($SetMember->getError(), U('Admin/Main/member'));
				}else{
					$this->error($SetMember->getError(),U('Admin/Main/member'));
				}
			}else{
				if(session("setmemberid")){
					session("setmemberid",null);
					$result = $SetMember->save();
					if($result||$result==0){
						session("model",4);
					}else{
						session("model",3);
					}
				}else{
					$this->error("未找到该用户");
				}
				$this->redirect('Admin/Main/member', array("p"=>I('p')), 0, '页面跳转中...');
			}
		}else{
			if(I("member_id")!=""){
				//有id  进入修改
				session("setmemberid",I("member_id"));
				session("model",2);
			}else{
				$this->error("没有找到该用户");
			}
			$this->redirect('Admin/Main/member', array("p"=>I('p')), 0, '页面跳转中...');
		}
	}
	//添加会员
	public function addMember(){
		$Admin = SESSION("AdminID");
		$id = I("id");
		if(IS_POST){
			$userpass = I("userpass");
			$repass = I("repass");
			$repaypass = I("repaypass");
			$paypass = I("paypass");
			$package_type = I("package_type");
			if($id!=""){
				$_POST["id"] = $id;
				if($userpass==""){
					$Member = M("Member")->where(array("id"=>$id))->find();
					$_POST["userpass"] = $Member["userpass"];
					$_POST["repass"] = $Member["userpass"];
				}else{
					$_POST["userpass"] = md5($userpass);
					$_POST["repass"] = md5($repass);
				}
				if($paypass==""){
					$Member = M("Member")->where(array("id"=>$id))->find();
					$_POST["paypass"] = $Member["paypass"];
					$_POST["repaypass"] = $Member["paypass"];
				}else{
					$_POST["paypass"] = $paypass;
					$_POST["repaypass"] = $repaypass;
				}
			}
			$_POST['picture'] = UploadImg('picture',"Member",$_POST["id"]);
			$AddMember = D("AddMember");
			if (!$AddMember->create()){
				session("model",1);
				$this->error($AddMember->getError(),U('Admin/Main/addMember',array("id"=>$id,"p"=>I('p'))));
			}else{
				if($id!=""){
					$result = $AddMember->save();
					if($result||$result==0){
						session("model",4);
					}else{
						session("model",3);
					}
				}else{
					if($result = $AddMember->add()){

						M("Member")->where(array("id"=>$result))->data(array("admin_id"=>$Admin['id']))->save();
						if($PackageType = M("PackageType")->where(array("id"=>$package_type,"delete"=>0))->find()){
							$order_number = "";
							while(true){
								$arr = getOrderNumber();
								if($arr['result']){
									$order_number = $arr['order_number'];
									break;
								}
							}
							
						}
						session("model",4);
					}else{
						session("model",3);
					}
				}
				$this->redirect('Admin/Main/member', array("p"=>I('p')), 0, '页面跳转中...');
			}
		}else{
			if($id!=""){
				$this->Member = M("Member")->where(array("id"=>$id,"delete"=>0))->find();
			}
			$this->p = I("p",0);
			$this->Time = date("Y-m-d",time());
			$this->CountryList = M("Country")->where(array("delete"=>0,"status"=>1))->select();
			$this->PackageType = M("PackageType")->where(array("delete"=>0,"status"=>1))->select();
			$this->display();
		}
	}
	//会员状态
	public function statusMember(){
		$result = M("Member")->where(array("id"=>I("id")))->data(array("status"=>I("status")))->save();
		if($result||$result==0){
			session("model",4);
		}else{
			session("model",3);
		}
		$this->redirect('Admin/Main/member',array("p"=>I('p')), 0, '页面跳转中...');
	}
	//删除会员
	public function deleteMember(){
		$id = I("id");
		if(del("Member",$id)){
			session("model",4);
		}else{
			session("model",3);
		}
		$this->redirect('Admin/Main/member',array("p"=>I('p')), 0, '页面跳转中...');
	}


	public function country(){
		if(session("countryid")&&session("model")==2){
			$Country = M("Country")->where(array("id"=>session("countryid")))->find();
			$this->cn_name = $Country["cn_name"];
			$this->sort = $Country["sort"];
			$this->en_name = $Country["en_name"];
			$this->status = $Country["status"];
		}else{
			$this->cn_name = urldecode(I("cn_name"));
			$this->sort = urldecode(I("sort"));
			$this->en_name = urldecode(I("en_name"));
			$this->status = urldecode(I("status"));
		}
		$this->model = session("model");
		session("model",null);
		$count = M("Country")->where(array("delete"=>0))->count();
		$Page = new \Think\Page($count,40);
		$show = $Page->show();
		$list = M("Country")->where(array("delete"=>0))->order('id')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('CountryList',$list);
		$this->assign('page',$show);
		$this->p = I("p",0);
		$this->display();
	}
	public function addCountry(){
		if(IS_POST){
			$AddCountry = D("AddCountry");
			if(session("countryid")){
				$_POST["id"] = session("countryid");
			}
			$_POST['picture'] = UploadImg('picture',"country",$_POST["id"]);
			if (!$AddCountry->create()){
				session("model",1);
				$this->error($AddCountry->getError(),U('Admin/Main/country',array("cn_name"=>urlencode(I("cn_name")),"en_name"=>urlencode(I("en_name")),"sort"=>urlencode(I("sort")),"status"=>urlencode(I("status")))));
			}else{
				if(session("countryid")){
					session("countryid",null);
					$result = $AddCountry->save();
					if($result||$result==0){
						session("model",4);
					}else{
						session("model",3);
					}
				}else{
					if($AddCountry->add()){
						session("model",4);
					}else{
						session("model",3);
					}
				}
				$this->redirect('Admin/Main/country', array("p"=>I('p')), 0, '页面跳转中...');
			}
		}else{
			if(I("id")!=""){
				//有id  进入修改
				session("countryid",I("id"));
				session("model",2);
			}else{
				session("countryid",null);
				session("model",1);
			}
			$this->redirect('Admin/Main/country', array("p"=>I('p')), 0, '页面跳转中...');
		}
	}
	public function statusCountry(){
		if(changeStatus("Country",I("id"),I("status"))){
			session("model",4);
		}else{
			session("model",3);
		}
		$this->redirect('Admin/Main/country', array("p"=>I('p')), 0, '页面跳转中...');
	}
	public function deleteCountry(){
		$id = I("id");
		if(del("Country",$id)){
			$this->success("加入回收站 成功");
		}else{
			$this->error("加入回收站 失败");
		}
	}


	public function priceLevel(){
		if(session("pricelevelid")&&session("model")==2){
			$PriceLevel = M("PriceLevel")->where(array("id"=>session("pricelevelid")))->find();
			$this->title = $PriceLevel["title"];
			$this->sort = $PriceLevel["sort"];
			$this->price = $PriceLevel["price"];
			$this->status = $PriceLevel["status"];
			$this->package_id= $PriceLevel["package_id"];
		}else{
			$this->title = urldecode(I("title"));
			$this->sort = urldecode(I("sort"));
			$this->price = urldecode(I("price"));
			$this->status = urldecode(I("status"));
			$this->package_id = urldecode(I("package_id"));
		}
		$this->model = session("model");
		session("model",null);
		$count = M("PriceLevel")->where(array("delete"=>0))->count();
		$Page = new \Think\Page($count,40);
		$show = $Page->show();
		$list = M("PriceLevel")->where(array("delete"=>0))->order('id')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('PriceLevel',$list);
		$this->assign('page',$show);
		$this->PackageList = M("PackageType")->where(array("delete"=>0))->select();
		$this->p = I("p",0);
		$this->display();
	}
	public function addPriceLevel(){
		if(IS_POST){
			$AddPriceLevel = D("AddPriceLevel");
			if(session("pricelevelid")){
				$_POST["id"] = session("pricelevelid");
			}
			$PackageType = M("PackageType")->where(array("delete"=>0,"id"=>I("package_id")))->find();
			$_POST['package_id'] = $PackageType["id"];
			$_POST['package_price'] = $PackageType["price"];
			$_POST['package_title'] = $PackageType["title"];
			if (!$AddPriceLevel->create()){
				session("model",1);
				$this->error($AddPriceLevel->getError(),U('Admin/Main/priceLevel',array("title"=>urlencode(I("title")),"sort"=>urlencode(I("sort")),"price"=>urlencode(I("price")),"status"=>urlencode(I("status")),"p"=>I("p"))));
			}else{
				if(session("pricelevelid")){
					session("pricelevelid",null);
					$result = $AddPriceLevel->save();
					if($result||$result==0){
						session("model",4);
					}else{
						session("model",3);
					}
				}else{
					if($AddPriceLevel->add()){
						session("model",4);
					}else{
						session("model",3);
					}
				}
				$this->redirect('Admin/Main/priceLevel', array("p"=>I('p')), 0, '页面跳转中...');
			}
		}else{
			if(I("id")!=""){
				//有id  进入修改
				session("pricelevelid",I("id"));
				session("model",2);
			}else{
				session("pricelevelid",null);
				session("model",1);
			}
			$this->redirect('Admin/Main/priceLevel', array("p"=>I('p')), 0, '页面跳转中...');
		}
	}
	public function statusPriceLevel(){
		if(changeStatus("PriceLevel",I("id"),I("status"))){
			session("model",4);
		}else{
			session("model",3);
		}
		$this->redirect('Admin/Main/priceLevel', array("p"=>I('p')), 0, '页面跳转中...');
	}
	public function deletePriceLevel(){
		$id = I("id");
		if(del("PriceLevel",$id)){
			$this->success("加入回收站 成功");
		}else{
			$this->error("加入回收站 失败");
		}
	}
	public function cash_wallet(){
		$where = array("delete"=>0);
		$where["_string"] = "type in (1,3,5,7,8)";
		$starttime = I("starttime");
		$endtime = I("endtime");
		if($starttime!=""&&$endtime!=""){
			if($starttime>$endtime){
				$this->error("开始时间不能大于结算时间");
			}
			if($starttime==$endtime){
				$where["_string"] = "adddate = '".$starttime."'";
			}else{
				$where["_string"] = "adddate between '".$starttime."' and '".$endtime."'";
			}
			$this->starttime = $starttime;
			$this->endtime = $endtime;
		}
		$page_set = I("page_set",10);
		$count = M("PriceInfo")->where($where)->order("id desc")->count();
		$Page = new \Think\Page($count,$page_set);
		foreach($where as $key=>$val) {
			$Page->parameter[$key] = urlencode($val);
		}
		if($starttime!=""){
			$Page->parameter["starttime"] = urlencode($starttime);
		}
		if($endtime!=""){
			$Page->parameter["endtime"] = urlencode($endtime);
		}
		$show = $Page->show();
		$list = M("PriceInfo")->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('PriceInfoList',$list);
		$this->assign('page',$show);
		$this->p = I("p",0);
		$this->MemberList = M("Member")->where(array("delete"=>0))->select();
		$this->page_set = I("page_set",10);
		$this->display();
	}
	public function bcash_wallet(){
		$where = array("delete"=>0);
		$where["_string"] = "type in (2,4,6,7,8)";
		$starttime = I("starttime");
		$endtime = I("endtime");
		if($starttime!=""&&$endtime!=""){
			if($starttime>$endtime){
				$this->error("开始时间不能大于结算时间");
			}
			if($starttime==$endtime){
				$where["_string"] = "adddate = '".$starttime."'";
			}else{
				$where["_string"] = "adddate between '".$starttime."' and '".$endtime."'";
			}
			$this->starttime = $starttime;
			$this->endtime = $endtime;
		}
		$page_set = I("page_set",10);
		$count = M("PriceInfo")->where($where)->order("id desc")->count();
		$Page = new \Think\Page($count,$page_set);
		foreach($where as $key=>$val) {
			$Page->parameter[$key] = urlencode($val);
		}
		if($starttime!=""){
			$Page->parameter["starttime"] = urlencode($starttime);
		}
		if($endtime!=""){
			$Page->parameter["endtime"] = urlencode($endtime);
		}
		$show = $Page->show();
		$list = M("PriceInfo")->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('PriceInfoList',$list);
		$this->assign('page',$show);
		$this->p = I("p",0);
		$this->MemberList = M("Member")->where(array("delete"=>0))->select();
		$this->page_set = I("page_set",10);
		$this->display();
	}
	public function digitPrice(){
		if(session("digitpriceid")&&session("model")==2){
			$DigitPrice = M("DigitPrice")->where(array("id"=>session("digitpriceid")))->find();
			$this->price = $DigitPrice["price"];
			$this->date = $DigitPrice["date"];
			$this->status = $DigitPrice["status"];
			$this->open = $DigitPrice["open"];
			$this->low = $DigitPrice["low"];
			$this->high = $DigitPrice["high"];
			$this->close = $DigitPrice["close"];
			$this->volume = $DigitPrice["volume"];
		}else{
			$this->price = urldecode(I("price"));
			$this->date = urldecode(I("date"));
			$this->status = urldecode(I("status"));
			$this->open = urldecode(I("open"));
			$this->low = urldecode(I("low"));
			$this->high = urldecode(I("high"));
			$this->close = urldecode(I("close"));
			$this->volume = urldecode(I("volume"));
		}
		$this->model = session("model");
		session("model",null);
		$count = M("DigitPrice")->where(array("delete"=>0))->count();
		$Page = new \Think\Page($count,20);
		$show = $Page->show();
		$list = M("DigitPrice")->where(array("delete"=>0))->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('DigitPriceList',$list);
		$this->assign('page',$show);
		$this->p = I("p",0);
		$this->display();
	}
	public function addDigitPrice(){
		if(IS_POST){
			$AddDigitPrice = D("AddDigitPrice");
			if(session("digitpriceid")){
				$_POST["id"] = session("digitpriceid");
			}
			if (!$AddDigitPrice->create()){
				session("model",1);
				$this->error($AddDigitPrice->getError(),U('Admin/Main/digitPrice',array("price"=>urlencode(I("price")),"date"=>urlencode(I("date")))));
			}else{
				if(session("digitpriceid")){
					session("digitpriceid",null);
					$result = $AddDigitPrice->save();
					if($result||$result==0){
						session("model",4);
					}else{
						session("model",3);
					}
				}else{
					if($AddDigitPrice->add()){
						session("model",4);
					}else{
						session("model",3);
					}
				}
				$this->redirect('Admin/Main/digitPrice', array("p"=>I('p')), 0, '页面跳转中...');
			}
		}else{
			if(I("id")!=""){
				//有id  进入修改
				session("digitpriceid",I("id"));
				session("model",2);
			}else{
				session("digitpriceid",null);
				session("model",1);
			}
			$this->redirect('Admin/Main/digitPrice', array("p"=>I('p')), 0, '页面跳转中...');
		}
	}
	public function statusDigitPrice(){
		if(changeStatus("DigitPrice",I("id"),I("status"))){
			session("model",4);
		}else{
			session("model",3);
		}
		$this->redirect('Admin/Main/digitPrice', array("p"=>I('p')), 0, '页面跳转中...');
	}
	public function deleteDigitPrice(){
		$id = I("id");
		if(del("DigitPrice",$id)){
			$this->success("加入回收站 成功");
		}else{
			$this->error("加入回收站 失败");
		}
	}
	//cash 存款
	public function cash(){
		if(session("cashid")&&session("model")==2){
			$Cash = M("Cash")->where(array("id"=>session("cashid")))->find();
			$this->content = $Cash["content"];
			$this->remark = $Cash["remark"];
			$this->price = $Cash["price"];
			$this->recom = $Cash["recom"];
			$this->status = $Cash["status"];
		}else{
			$this->content = urldecode(I("content"));
			$this->remark = urldecode(I("remark"));
			$this->price = urldecode(I("price"));
			$this->recom = urldecode(I("recom"));
			$this->status = urldecode(I("status"));
		}
		$this->model = session("model");
		session("model",null);
		$count = M("Cash")->where(array("delete"=>0))->count();
		$Page = new \Think\Page($count,40);
		$show = $Page->show();
		$list = M("Cash")->where(array("delete"=>0))->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('CashList',$list);
		$this->assign('page',$show);
		$this->p = I("p",0);
		$this->display();
	}
	public function addCash(){
		if(IS_POST){
			$Cash = D("Cash");
			if(session("cashid")){
				$_POST["id"] = session("cashid");
				$CashFind = M("Cash")->where(array("id"=>$_POST["id"]))->find();
				if(M("PriceInfo")->where(array("cash_id"=>$CashFind['id']))->find()){
					$this->error("审核已通过，无法继续添加S币",U("Admin/Main/cash"));
				}
			}else{
				$this->error("无数据");
			}
			if (!$Cash->create()){
				session("model",1);
				$this->error($Cash->getError(),U('Admin/Main/cash',array("remark"=>urlencode(I("remark")),"content"=>urlencode(I("content")))));
			}else{
				if(session("cashid")){
					session("cashid",null);
					$result = $Cash->save();
					if($result||$result==0){
						if(I("status")==1){
							$Admin = SESSION("AdminID");						
							if(!$Member = M("Member")->where(array("id"=>$CashFind['member_id'],"delete"=>0))->find()){
								$this->error("未找到该会员");
							}else{
								$PriceInfo = M("PriceInfo")->where(array("delete"=>0,"member_id"=>$Member['id']))->order("id desc")->limit(1)->select();
							}
							$data = array(
									"member_username"=>$CashFind['member_username'],
									"title"=>"The Corporate deposit",
									"remark"=>"",
									"type"=>1,
									"member_id"=>$CashFind['member_id'],
									"admin_id"=>$Admin["id"],
									"s_price"=>$CashFind['price']+$PriceInfo[0]['s_price'],
									"b_price"=>$PriceInfo[0]['b_price'],
									"cash_id"=>$CashFind["id"],
									"price"=>$CashFind['price'],
									"adddate"=>date("Y-m-d",time()),
									"addtime"=>date("Y-m-d H:i:s",time()),
							);
							M("PriceInfo")->data($data)->add();
						}
						session("model",4);
					}else{
						session("model",3);
					}
				}else{
					if($Cash->add()){
						session("model",4);
					}else{
						session("model",3);
					}
				}
				$this->redirect('Admin/Main/cash', array("p"=>I('p')), 0, '页面跳转中...');
			}
		}else{
			if(I("id")!=""){
				//有id  进入修改
				session("cashid",I("id"));
				session("model",2);
			}else{
				session("cashid",null);
				session("model",1);
			}
			$this->redirect('Admin/Main/cash', array("p"=>I('p')), 0, '页面跳转中...');
		}
	}
	public function recomCash(){
		if(changeRecom("Cash",I("id"),I("recom"))){
			session("model",4);
		}else{
			session("model",3);
		}
		$this->redirect('Admin/Main/cash', array("p"=>I('p')), 0, '页面跳转中...');
	}
	public function statusCash(){
		$Cash = M("Cash")->where(array("id"=>I("id")))->find();
		if(M("PriceInfo")->where(array("cash_id"=>$Cash['id']))->find()){
			$this->error("审核已通过，无法继续添加S币",U("Admin/Main/cash"));
		}		
		if(changeStatus("Cash",I("id"),I("status"))){
			$Admin = SESSION("AdminID");
			
			if(!$Member = M("Member")->where(array("id"=>$Cash['member_id'],"delete"=>0))->find()){
				$this->error("未找到该会员");
			}else{
				$PriceInfo = M("PriceInfo")->where(array("delete"=>0,"member_id"=>$Member['id']))->order("id desc")->limit(1)->select();
			}
			$data = array(
					"member_username"=>$Cash['member_username'],
					"title"=>"The Corporate deposit",
					"remark"=>"",
					"type"=>1,
					"member_id"=>$Cash['member_id'],
					"admin_id"=>$Admin["id"],
					"s_price"=>$Cash['price']+$PriceInfo[0]['s_price'],
					"b_price"=>$PriceInfo[0]['b_price'],
					"cash_id"=>$Cash["id"],
					"price"=>$Cash['price'],
					"adddate"=>date("Y-m-d",time()),
					"addtime"=>date("Y-m-d H:i:s",time()),
			);
			M("PriceInfo")->data($data)->add();
			session("model",4);
		}else{
			session("model",3);
		}
		$this->redirect('Admin/Main/cash', array("p"=>I('p')), 0, '页面跳转中...');
	}
	public function deleteCash(){
		$id = I("id");
		if(del("Cash",$id)){
			$this->success("加入回收站 成功");
		}else{
			$this->error("加入回收站 失败");
		}
	}
	public function split_share(){
		if(IS_POST){	
			$AddSplit = D("AddSplit");
			if(session("splitid")){
				$_POST["id"] = session("splitid");
			}					
			if (!$AddSplit->create()){
				session("model",1);
				$this->error($AddSplit->getError(),U('Admin/Main/split',array("title"=>urlencode(I("title")),"en_title"=>urlencode(I("en_title")),"sp_title"=>urlencode(I("sp_title")),"p"=>I("p"))));
			}else{
				if(session("splitid")){
					session("splitid",null);
					$result = $AddSplit->save();
					if($result||$result==0){	
						session("model",4);						
					}else{
						session("model",3);
					}						
				}else{
					if($AddSplit->add()){
						session("model",4);
					}else{
						session("model",3);
					}										
				}
				$this->redirect('Admin/Main/split', array("p"=>I('p')), 0, '页面跳转中...');
			}
		}else{
			if(I("id")!=""){
				//有id  进入修改
				session("splitid",I("id"));
				session("model",2);			
			}else{
				session("splitid",null);
				session("model",1);
			}	
			$this->redirect('Admin/Main/split', array("p"=>I('p')), 0, '页面跳转中...');
		}		
	}
	public function split_digit(){

		session("packagetypeid",null);
		session("model",1);
		$this->redirect('Admin/Main/split', array("p"=>I('p')), 0, '页面跳转中...');
	}
	public function split(){
		$this->model = session("model");
		session("model",null);
		$count = M("Split")->where(array("delete"=>0))->count();
		$Page = new \Think\Page($count,40);
		$show = $Page->show();
		$list = M("Split")->where(array("delete"=>0))->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('SplitList',$list);
		$this->assign('page',$show);
		$this->p = I("p",0);
		$this->count = $count;
		$this->display();
	}
	public function deleteSplit(){
		$id = I("id");
		if(del("Split",$id)){
			$this->success("加入回收站 成功");
		}else{
			$this->error("加入回收站 失败");
		}
	}
	public function coin_info(){
		if(session("coininfoid")&&session("model")==2){
			$CoinInfo = M("CoinInfo")->where(array("id"=>session("coininfoid")))->find();
			$this->number = $CoinInfo["number"];
			$this->title = $CoinInfo["title"];
			$this->status = $CoinInfo["status"];
		}else{
			$this->number = urldecode(I("number"));
			$this->title = urldecode(I("title"));
			$this->status = urldecode(I("status"));
		}
		$this->model = session("model");
		session("model",null);
		$count = M("CoinInfo")->where(array("delete"=>0))->count();
		$Page = new \Think\Page($count,20);
		$show = $Page->show();
		$list = M("CoinInfo")->where(array("delete"=>0))->order('id')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('CoinInfo',$list);
		$this->assign('page',$show);
		$this->p = I("p",0);
		$this->display();
	}
	public function addCoinInfo(){
		if(IS_POST){
			$number = I("number",0,"int");
			if($number>100&&$number>=0){
				$this->error("趋势在0-100之间");
			}
			$CoinInfo = D("CoinInfo");
			if(session("coininfoid")){
				$_POST["id"] = session("coininfoid");
			}

			if (!$CoinInfo->create()){
				session("model",1);
				$this->error($CoinInfo->getError(),U('Admin/Main/coin_info',array("title"=>urlencode(I("title")),"number"=>urlencode(I("number")),"status"=>urlencode(I("status")),"p"=>I("p"))));
			}else{
				if(session("coininfoid")){
					session("coininfoid",null);
					$result = $CoinInfo->save();
					if($result||$result==0){
						session("model",4);
					}else{
						session("model",3);
					}
				}else{
					if($CoinInfo->add()){
						session("model",4);
					}else{
						session("model",3);
					}
				}
				$this->redirect('Admin/Main/coin_info', array("p"=>I('p')), 0, '页面跳转中...');
			}
		}else{
			if(I("id")!=""){
				//有id  进入修改
				session("coininfoid",I("id"));
				session("model",2);
			}else{
				session("coininfoid",null);
				session("model",1);
			}
			$this->redirect('Admin/Main/coin_info', array("p"=>I('p',0)), 0, '页面跳转中...');
		}
	}
	public function statusCoinInfo(){
		if(changeStatus("CoinInfo",I("id"),I("status"))){
			session("model",4);
		}else{
			session("model",3);
		}
		$this->redirect('Admin/Main/coin_info', array("p"=>I('p')), 0, '页面跳转中...');
	}
	public function deleteCoinInfo(){
		$id = I("id");
		if(del("CoinInfo",$id)){
			$this->success("加入回收站 成功");
		}else{
			$this->error("加入回收站 失败");
		}
	}
	public function message(){
		if(session("messageid")&&session("model")==2){
			$Message = M("Message")->where(array("id"=>session("messageid")))->find();
			$this->name = $Message["name"];
			$this->message = $Message["message"];
			$this->email = $Message["email"];
			$this->emailAddress = $Message["emailAddress"];
		}else{
			$this->name = urldecode(I("name"));
			$this->message = urldecode(I("message"));
			$this->email = urldecode(I("email"));
			$this->emailAddress = urldecode(I("emailAddress"));
		}
		$this->model = session("model");
		session("model",null);
		$count = M("Message")->where(array("delete"=>0))->count();
		$Page = new \Think\Page($count,20);
		$show = $Page->show();
		$list = M("Message")->where(array("delete"=>0))->order('id')->limit($Page->firstRow.','.$Page->listRows)->order("id desc")->select();
		$this->assign('MessageList',$list);
		$this->assign('page',$show);
		$this->p = I("p",0);
		$this->display();
	}
	public function showMessage(){
		if(IS_POST){

		}else{
			if(I("id")!=""){
				//有id  进入修改
				session("messageid",I("id"));
				session("model",2);
			}else{
				session("messageid",null);
				session("model",1);
			}
			$this->redirect('Admin/Main/message', array("p"=>I('p',0)), 0, '页面跳转中...');
		}
	}
}
