<?php
namespace Admin\Controller;
use Think\Controller;
class OrderController extends CommonController {
    public function order(){
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
            $where["member_username"] = array("like","%".$title."%");
            $this->text = $title;
		}
		
		$where['type']=array('in','1,3');
        $page = 16;
        $p = I("p",1,"int");
        $list = M('OrderInfo')->where($where)->order('id desc')->page($p.','.$page)->select();
        $this->assign('OrderInfo',$list);// 赋值数据集
        $count = M('OrderInfo')->where($where)->count();// 查询满足要求的总记录数
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

    public function recharge(){
        if(IS_POST){
            $User = session("MemberID");
            $username = I("username");
            $price_type = I("price_type");
			if($price_type==1){
				if(!$Member = M("Member")->where(array("username"=>$username,"delete"=>0))->find()){
					$this->error("未找到该会员");
				}else{
					$PriceInfo = M("PriceInfo")->where(array("delete"=>0,"member_id"=>$Member['id']))->order("id desc")->find();
				}
				$_POST["new_price"] = I("price")+$PriceInfo['new_price'];
				$_POST["old_price"] = $PriceInfo['new_price'];
				$_POST["title"] = "注册币充值";
				$_POST["price"] = I("price");
				$_POST["member_id"] = $Member["id"];
				$_POST["member_username"] = $Member["username"];
				$OrderPay = D("OrderPay");
				if (!$OrderPay->create()){
					$this->error($OrderPay->getError(),U('Admin/Order/recharge'));
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
						$data = array(
							"order_number"=>$order_number,
							"price_info_id"=>$result,
							"member_username"=>$username,
							"title"=>"注册币充值",
							"remark"=>"",
							"type"=>$price_type,
							"member_id"=>$Member['id'],
							"admin_id"=>$User["id"],
							"price"=>I("price"),
							"adddate"=>date("Y-m-d",time()),
							"addtime"=>date("Y-m-d H:i:s",time()),
						);
						M("OrderInfo")->data($data)->add();
						$this->success("充值成功",U('Admin/Order/recharge'));
					}else{
						$this->error("充值失败",U('Admin/Order/recharge'));
					}
				}
			}else if($price_type==2){
				if(!$Member = M("Member")->where(array("username"=>$username,"delete"=>0))->find()){
					$this->error("未找到该会员");
				}else{
					$PriceMoneyInfo = M("PriceMoneyInfo")->where(array("delete"=>0,"member_id"=>$Member['id']))->order("id desc")->find();
				}
				$_POST["new_price"] = I("price")+$PriceMoneyInfo['new_price'];
				$_POST["old_price"] = $PriceMoneyInfo['new_price'];
				$_POST["title"] = "奖金币充值";
				$_POST["price"] = I("price");
				$_POST["member_id"] = $Member["id"];
				$_POST["member_username"] = $Member["username"];
				$OrderPay = D("OrderMoneyPay");
				if (!$OrderPay->create()){
					$this->error($OrderPay->getError(),U('Admin/Order/recharge'));
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
						$data = array(
							"order_number"=>$order_number,
							"price_info_id"=>$result,
							"member_username"=>$username,
							"title"=>"奖金币充值",
							"remark"=>"",
							"type"=>$price_type,
							"member_id"=>$Member['id'],
							"admin_id"=>$User["id"],
							"price"=>I("price"),
							"adddate"=>date("Y-m-d",time()),
							"addtime"=>date("Y-m-d H:i:s",time()),
						);
						$order = M("OrderInfo")->data($data)->add();
						$order_money_number = "";
						while(true){
							$arr = getMoneyNumber();
							if($arr['result']){
								$order_money_number = $arr['order_number'];
								break;
							}
						}
						M("PriceMoneyInfo")->where(array("id"=>$result))->data(array("order_id"=>$order_money_number))->save();
						$this->success("充值成功",U('Admin/Order/recharge'));
					}else{
						$this->error("充值失败",U('Admin/Order/recharge'));
					}
				}
			
			}
			else if($price_type==3){
				if(!$Member = M("Member")->where(array("username"=>$username,"delete"=>0))->find()){
					$this->error("未找到该会员");
				}

				$ShareInfo = M("ShareNumberInfo")->where(array("member_id"=>$Member["id"]))->order("id desc")->find();

				$oldpriece=0;
				if($ShareInfo){
					$oldpriece=$ShareInfo['new_number'];
				}

				$_POST["new_price"] = I("price")+$oldpriece;
				$_POST["old_price"] = $oldpriece;
				$_POST["title"] = "现金币充值";
				$_POST["price"] = I("price");
				$_POST["member_id"] = $Member["id"];
				$_POST["member_username"] = $Member["username"];
				$OrderPay = D("OrderPay");
				
				$order_number = "";
				while(true){
					$arr = getOrderNumber();
					if($arr['result']){
						$order_number = $arr['order_number'];
						break;
					}
				}
				

				$data = array(
					"type"=>1,
					"member_id"=>$Member['id'],
					"member_username"=>$username,
					"reinvest_id"=>0,
					"title"=>"现金币充值",
					"title_en"=>"Stock thaw",
					"title_sp"=>"Stock thaw",
					"new_number"=>$oldpriece + I("price"),
					"old_number"=>$oldpriece,
					"number"=>I("price"),
					"adddate"=>date("Y-m-d",time()),
					"addtime"=>date("Y-m-d H:i:s",time()),
				);
				M("ShareNumberInfo")->data($data)->add();


				$data_order = array(
					"order_number"=>$order_number,
					"price_info_id"=>'0',
					"member_username"=>$username,
					"title"=>"现金币充值",
					"remark"=>"",
					"type"=>$price_type,
					"member_id"=>$Member['id'],
					"admin_id"=>$User["id"],
					"price"=>I("price"),
					"adddate"=>date("Y-m-d",time()),
					"addtime"=>date("Y-m-d H:i:s",time()),
				);
				M("OrderInfo")->data($data_order)->add();

				$this->success("充值成功",U('Admin/Order/recharge'));
			}
        }else{
            $id = I("member_id");
            if($id!=""){
                if(!$this->Member = M("Member")->where(array("id"=>$id,"delete"=>0))->find()){
                    $this->error("未找到该会员");
                }
            }
            $this->display();
        }
    }
    public function deleteOrder(){
        $id = I("id");
        if($OrderInfo = M("OrderInfo")->where(array("id"=>$id))->find()){
            if(M("PriceInfo")->where(array("id"=>$OrderInfo['price_info_id']))->delete()){
                $this->success("删除成功");
            }else{
                $this->error("现金记录删除失败！");
            }
        }else{
            $this->error("未查找到订单！");
        }
    }
}
