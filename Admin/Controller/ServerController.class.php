<?php
namespace Admin\Controller;
use Think\Controller;
class ServerController extends Controller {
    public function index(){
		$this->Number = M("ServerOutPrice")->where(array("delete"=>0,"status"=>2))->count();
		$this->display();
    }
	public function about(){
		$id = I("id");
		if($id!=""){
			$ServerAbout = M("ServerAbout")->where(array("id"=>$id))->find();
			$this->title = $ServerAbout["title"];
			$this->sort = $ServerAbout["sort"];
			$this->content = $ServerAbout["content"];
			$this->status = $ServerAbout["status"];
		}else{
			$this->title = urldecode(I("title"));
			$this->sort = urldecode(I("sort"));
			$this->content = urldecode(I("content"));
		}
		$this->id = $id;
		$this->model = session("model");
		session("model",null);
		$this->display();		
	}
	public function addAbout(){
		if(IS_POST){
			$id = I("id",0,"int");			
			$ServerAbout = D("ServerAbout");
			if($id!=""){
				$_POST["id"] = $id;
			}					
			if (!$ServerAbout->create()){
				session("model",1);
				$this->error($ServerAbout->getError(),U('Admin/Server/about',array("title"=>urlencode(I("title")),"sort"=>urlencode(I("sort")),"content"=>urlencode(I("content")),"p"=>I("p"))));
			}else{
				if($id!=""){
					$result = $ServerAbout->save();
					if($result||$result==0){	
						session("model",4);						
					}else{
						session("model",3);
					}						
				}
				$this->redirect('Admin/Server/about', array('id'=>$id), 0, '页面跳转中...');
			}
		}else{	
			$this->redirect('Admin/Server/index', array(), 0, '页面跳转中...');
		}		
	}
	public function system(){
		if(IS_POST){
            if(F("server",$_POST,CONF_PATH)){
				session("model",4);
            }else{
				session("model",3);
            }
			$this->redirect('Admin/Server/system',null, 0, '页面跳转中...');
        }else{
			$this->model = session("model");
			session("model",null);
            $this->display();
        }
	}
	public function order(){

        $where = array("delete"=>0);
        $title = urldecode(I("text"));
        $where['status'] = 0;
        if($title!=""){
            $where["member_username"] = array("like","%".$title."%");
            $this->text = $title;
        }
        $page = 10;
        $p = I("p",1,"int");
        $list = M('ServerOutPrice')->where($where)->order('id desc')->page($p.','.$page)->select();
        $this->assign('OrderInfo',$list);// 赋值数据集
        $count = M('ServerOutPrice')->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Think\Page($count,$page);// 实例化分页类 传入总记录数和每页显示的记录数
        foreach($where as $key=>$val) {
            $Page->parameter[$key] = urlencode($val);
        }
        $Page->parameter['text'] = $title;
        $show = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $this->p = I("p",0);
        $this->display();
	}
	public function over_order(){

		$where = array("delete"=>0);
        $title = urldecode(I("text"));
        $where['status'] = array("in","3");
        if($title!=""){
            $where["member_username"] = array("like","%".$title."%");
            $this->text = $title;
        }
        $page = 10;
        $p = I("p",1,"int");
        $list = M('ServerOutPrice')->where($where)->order('id desc')->page($p.','.$page)->select();
        $this->assign('OrderInfo',$list);// 赋值数据集
        $count = M('ServerOutPrice')->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Think\Page($count,$page);// 实例化分页类 传入总记录数和每页显示的记录数
        foreach($where as $key=>$val) {
            $Page->parameter[$key] = urlencode($val);
        }
        $Page->parameter['text'] = $title;
        $show = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $this->p = I("p",0);
        $this->display();
	}
	public function ordering(){
		$where = array("delete"=>0);
        $title = urldecode(I("text"));
        $where['status'] = array("in","1,2");
        if($title!=""){
            $where["member_username"] = array("like","%".$title."%");
            $this->text = $title;
        }
        $page = 10;
        $p = I("p",1,"int");
        $list = M('ServerOutPrice')->where($where)->order('id desc')->page($p.','.$page)->select();
        $this->assign('OrderInfo',$list);// 赋值数据集
        $count = M('ServerOutPrice')->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Think\Page($count,$page);// 实例化分页类 传入总记录数和每页显示的记录数
        foreach($where as $key=>$val) {
            $Page->parameter[$key] = urlencode($val);
        }
        $Page->parameter['text'] = $title;
        $show = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $this->p = I("p",0);
        $this->display();
	}
	public function touch(){
		$ServerOutPrice = M("ServerOutPrice")->where(array("id"=>I("id")))->find();
		$this->username = $ServerOutPrice["member_username"];
		$this->price = $ServerOutPrice["price"];
		$this->telephone = $ServerOutPrice['member_telephone'];
		$this->id = $ServerOutPrice["id"];
		$this->empty = "<tr><td colspan='4'>暂无匹配数据</td></tr>";
		if($ServerOutPrice['status']==1){
			if($Money = M("PriceMoneyInfo")->where(array("id"=>$ServerOutPrice['money_id']))->find()){
				$this->server_buy_list = M("ServerBuyPrice")->where(array("id"=>$Money["server_buy_id"],"member_id"=>array("neq",$ServerOutPrice["member_id"]),"price"=>$ServerOutPrice["price"],"status"=>1))->select();
			}else{
				$this->error("匹配错误");
			}
			
		}else{
			$this->server_buy_list = M("ServerBuyPrice")->where(array("member_id"=>array("neq",$ServerOutPrice["member_id"]),"price"=>$ServerOutPrice["price"],"status"=>0))->select();
		}
		
		$this->display();
	}
	public function out_touch(){
		$ServerOutPrice = M("ServerOutPrice")->where(array("id"=>I("id")))->find();
		$Money_sell = M("PriceMoneyInfo")->where(array("id"=>$ServerOutPrice['money_id']))->find();
		$Money_buy = M("PriceMoneyInfo")->where(array("id"=>$Money_sell['server_buy_id']))->find();
		M("PriceMoneyInfo")->where(array("id"=>$ServerOutPrice['money_id']))->data(array("status"=>1,"touch_time"=>null,"server_buy_id"=>null,"server_sell_id"=>null))->save();
		M("PriceMoneyInfo")->where(array("id"=>$Money_sell['server_buy_id']))->data(array("status"=>1,"touch_time"=>null,"server_buy_id"=>null,"server_sell_id"=>null))->save();
		M("ServerOutPrice")->where(array("id"=>I("id")))->data(array("picture"=>""))->save();
		$this->success("取消成功");
	}
	public function member_touch(){
		$ServerOutPrice = M("ServerOutPrice")->where(array("id"=>I("sell_id"),"status"=>0))->find();
		$ServerBuyPrice = M("ServerBuyPrice")->where(array("id"=>I("buy_id"),"status"=>0))->find();
		
		if($ServerOutPrice&&$ServerBuyPrice){
			if($ServerOutPrice['price']==$ServerBuyPrice['price']){
				M("PriceMoneyInfo")->where(array("id"=>$ServerOutPrice['money_id']))->data(array("status"=>1,"server_buy_id"=>I("buy_id"),"server_sell_id"=>I("sell_id")))->save();
				M("PriceMoneyInfo")->where(array("id"=>$ServerBuyPrice['money_id']))->data(array("status"=>1,"server_buy_id"=>I("buy_id"),"server_sell_id"=>I("sell_id"),"touch_time"=>date("Y-m-d H:i:s",time())))->save();
				M("ServerOutPrice")->where(array("id"=>I("sell_id")))->data(array("status"=>1))->save();
				M("ServerBuyPrice")->where(array("id"=>I("buy_id")))->data(array("status"=>1))->save();
				$SMS_URL = C("DEFAULT_WEB_SMS_URL");
				$SMS_ACCOUNT = C("DEFAULT_WEB_SMS_ACCOUNT");
				$SMS_KEY = C("DEFAULT_WEB_SMS_KEY");
				$SMS_PORT = C("DEFAULT_WEB_SMS_PORT");
				$SMS_NUMBER = C("DEFAULT_WEB_SMS_NUMBER");
				$SMS_REMARK = C("DEFAULT_WEB_SMS_REMARK");
				$SMS_BUY = C("DEFAULT_WEB_SMS_BUY");
				sendSMS($SMS_URL,$SMS_ACCOUNT,$SMS_KEY,$SMS_PORT,$ServerBuyPrice['member_telephone'],$SMS_BUY,'');
				$this->success("匹配成功",U("Admin/Server/order"));
			}else{
				$this->error("匹配失败！");
			}
		}else{
			$this->error("匹配失败！");
		}

	}
	public function recomOrder(){
		if(IS_POST){
			$ServerOutPrice = D("ServerOutPrice");
			if(session("ServerOutPriceid")){
				$_POST["id"] = session("ServerOutPriceid");
				$ServerOutPrice = M("ServerOutPrice")->where(array("id"=>$_POST["id"]))->find();
				if($ServerOutPrice['status']==1){
					$this->error("已审核,无法改变状态",U("Admin/Server/order"));
				}
				if($ServerOutPrice['status']==0){
					$this->error("已审核,无法改变状态",U("Admin/Server/order"));
				}
			}else{
				$this->error("无数据");
			}
			$_POST['action_time'] = date("Y-m-d H:i:s",time());
			if (!$ServerOutPrice->create()){
				session("model",1);
				$this->error($ServerOutPrice->getError(),U('Admin/Server/order',array("member_username"=>urlencode(I("member_username")),"remark"=>urlencode(I("remark")),"content"=>urlencode(I("content")))));
			}else{
				if(session("ServerOutPriceid")){
					session("ServerOutPriceid",null);
					$result = $ServerOutPrice->save();
					if($result||$result==0){
						if(I("status")==0){
							$Admin = SESSION("AdminID");						
							$PriceInfo = M("ServerPriceInfo")->where(array("delete"=>0,"member_id"=>$ServerOutPrice['member_id']))->order("id desc")->limit(1)->select();
							$data = array(
								"member_id"=>$ServerOutPrice['member_id'],
								"member_username"=>$ServerOutPrice['member_username'],
								"type"=>4,
								"title"=>"Withdrawals Failure",
								"usd_price"=>$ServerOutPrice['price']+$PriceInfo[0]['usd_price']+$ServerOutPrice['system_price'],
								'price'=>$ServerOutPrice['price']+$ServerOutPrice['system_price'],
								"adddate"=>date("Y-m-d",time()),
								"addtime"=>date("Y-m-d H:i:s",time()),
							);
							M("ServerPriceInfo")->data($data)->add();
							//M("ServerOutPrice")->where(array("id"=>$_POST["id"]))->delete();
						}					
						session("model",4);
					}else{
						session("model",3);
					}
				}else{
					$this->error("错误");
				}
				$this->redirect('Admin/Server/order', array("p"=>I('p')), 0, '页面跳转中...');
			}
		}else{
			if(I("id")!=""){
				//有id  进入修改
				session("ServerOutPriceid",I("id"));
				session("model",2);
			}else{
				session("ServerOutPriceid",null);
				$this->error("错误");
			}
			$this->redirect('Admin/Server/order', array("p"=>I('p')), 0, '页面跳转中...');
		}		
	}
	
	public function map(){
		if(session("mapid")&&session("model")==2){
			$map = M("ServerBuyPrice")->where(array("id"=>session("mapid")))->find();
			$this->price = $map["price"];
			$this->number = $map["number"];
		}else{
			$this->price = urldecode(I("price"));
			$this->number = urldecode(I("number"));
		}
		$this->model = session("model");
		session("model",null);
		$count = M("ServerBuyPrice")->where(array("delete"=>0))->count();
		$Page = new \Think\Page($count,20);
		$show = $Page->show();
		$list = M("ServerBuyPrice")->where(array("delete"=>0))->order('id')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('MapList',$list);
		$this->assign('page',$show);
		$this->p = I("p",0);
		$this->display();
	}
	public function addMap(){
		if(IS_POST){
			$AddMap = D("AddMap");
			if(session("mapid")){
				$_POST["id"] = session("mapid");
			}
			if (!$AddMap->create()){
				session("model",1);
				$this->error($AddMap->getError(),U('Admin/Server/map',array("price"=>urlencode(I("price")),"number"=>urlencode(I("number")))));
			}else{
				if(session("mapid")){
					session("mapid",null);
					$result = $AddMap->save();
					if($result||$result==0){
						session("model",4);
					}else{
						session("model",3);
					}
				}else{
					if($AddMap->add()){
						session("model",4);
					}else{
						session("model",3);
					}
				}
				$this->redirect('Admin/Server/map', array("p"=>I('p')), 0, '页面跳转中...');
			}
		}else{
			if(I("id")!=""){
				//有id  进入修改
				session("mapid",I("id"));
				session("model",2);
			}else{
				session("mapid",null);
				session("model",1);
			}
			$this->redirect('Admin/Server/map', array("p"=>I('p')), 0, '页面跳转中...');
		}
	}
	public function statusMap(){
		if(changeStatus("ServerBuyPrice",I("id"),I("status"))){
			session("model",4);
		}else{
			session("model",3);
		}
		$this->redirect('Admin/Server/map', array("p"=>I('p')), 0, '页面跳转中...');
	}
	public function deleteMap(){
		$id = I("id");
		if(del("ServerBuyPrice",$id)){
			$this->success("加入回收站 成功");
		}else{
			$this->error("加入回收站 失败");
		}
	}	
	
	
}




?>