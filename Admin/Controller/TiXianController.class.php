<?php
namespace Admin\Controller;
use Think\Controller;
class TiXianController extends CommonController {
    public function index($pageindex=1,$limit=10){
        $p = I("p",1,"int");
        $startindex= ($p -1)*$limit;
		$this->list_data=M("Tixian")->order("tixian_id desc")->limit($startindex,$limit)->select();
        $this->total=M("Tixian")->count();
        $Page = new \Think\Page($this->total,$limit);// 实例化分页类 传入总记录数和每页显示的记录数
      
        $show = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
		$this->display();
    }

    public function shenhe(){

        $id=I('id');
        $state=I('state');
        $tixianifno=M("Tixian")->where(array("tixian_id"=>$id))->find();
        $account=$tixianifno['money_line'];
        $qianbao=$tixianifno['tx_type'];
        $curstate=$tixianifno['tx_state'];
        $userid=$tixianifno['member_id'];
        $User=M("Member")->where(array("id"=>$userid))->find();
        if($curstate==0){
            if($state==2){
                if($qianbao==1){
                    $PriceIntegralInfo = M("PriceIntegralInfo")->where(array("delete"=>0,"member_id"=>$userid))->order("id desc")->find();
                    $old_price=$PriceIntegralInfo['new_number'];
                    if(!$old_price){
                        $old_price=0;
                    }
                                $Integral_Info = array(
                                    "type"=>11,
                                    'member_id'=>$User['id'],
                                    'member_username'=>$User['username'],
                                    "order_id"=>'',
                                    'title'=>'提现取消解冻金额',
                                    'title_en'=>'',
                                    'title_sp'=>'',
                                    'new_number'=>$old_price + $account,
                                    'old_number'=>$old_price,
                                    "number"=>$account,
                                    "adddate"=>date("Y-m-d",time()),
                                    "addtime"=>date("Y-m-d H:i:s",time()),
                                );
                    M("PriceIntegralInfo")->data($Integral_Info)->add();
                }
                else if($qianbao==2){
                    $top_PriceMoneyInfo = M("PriceMoneyInfo")->where(array("member_id"=>$userid))->order("id desc")->find();
                    $oldprice=$top_PriceMoneyInfo['new_price'];
                    if(!$oldprice){
                        $oldprice=0;
                    }
                    $money_data = array(
                                            "type"=>2,
                                            "member_id"=>$User['id'],
                                            "member_username"=>$User['username'],
                                            "order_id"=>'',
                                            "title"=>'提现取消解冻金额',
                                            "title_en"=>'',
                                            "title_sp"=>'',
                                            "new_price"=>$oldprice+$account,
                                            "old_price"=>$oldprice,
                                            "price"=>$account,
                                            "adddate"=>date("Y-m-d",time()),
                                            "addtime"=>date("Y-m-d H:i:s",time()+2),
                                        );
                    M("PriceMoneyInfo")->data($money_data)->add();
                }
            }
            M("Tixian")->where(array("tixian_id"=>$id))->data(array("tx_state"=>$state))->save();
            
        }
        echo '1';
        //$this->success(L("操作成功！"));	
    }
}
