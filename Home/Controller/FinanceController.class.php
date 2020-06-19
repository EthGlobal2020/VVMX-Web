<?php
namespace Home\Controller;
use Think\Controller;
class FinanceController extends CommonController {
    public function index(){
        $this->display();
    }
	public function change_log(){
		$Member = session("MemberID");
		$change_log = M("ChangeLog")->where(array("member_id"=>$Member['id']))->order("id desc")->limit(25)->select();
/* 		$arr = array();
		foreach($change_log as $val){
			
			if(!empty($arr[$val['member_id'].$val['change_id']])){
				$arr[$val['member_id'].$val['change_id']]['title'] = $arr[$val['member_id'].$val['change_id']]['title'].$val['title'];
			}else{
				$arr[$val['member_id'].$val['change_id']] = $val;
			}
		} */
		$this->change_log = $change_log;
		$this->display();
	}


	public function mytixian($page=1,$limit=16){
		$startindex= ($page -1)*$limit;
		$Member=session("MemberID");
		$PriceIntegralInfo = M("PriceIntegralInfo")->where(array("delete"=>0,"member_id"=>$Member['id']))->order("id desc")->find();
		$top_PriceMoneyInfo = M("PriceMoneyInfo")->where(array("member_id"=>$Member['id']))->order("id desc")->find();
		$jiangjinbi=0;
		$fenhongbi=0;
		if($PriceIntegralInfo){
			$fenhongbi=$PriceIntegralInfo['new_number'];
		}
		if($top_PriceMoneyInfo){
			$jiangjinbi=$top_PriceMoneyInfo['new_price'];
		}
		$this->fenhong_price=$fenhongbi;
		$this->jiangjin_price=$jiangjinbi;
		$this->list_data=M("Tixian")->where(array("member_id"=>$Member['id']))->order("tixian_id desc")->limit($startindex,$limit)->select();
		$this->total=M("Tixian")->where(array("member_id"=>$Member['id']))->count();

		$this->title='提现申请';
		if(ismobile()){
			$this->theme('erci')->display();
		}
		else{
			$this->display();
		}
	}

	public function tixianaction(){
		if(IS_POST){
			$User = session("MemberID");
			$user_real=M("Member")->where(array("id"=>$User['id']))->find();
			if(!$user_real['bitcoin'] || $user_real['bitcoin']==''){
				$this->error('你还未设置收款的钱包地址，设置完成后才能完成提现！',U("Home/User/bank_info"));
			}
			

			
			$account=I("sell_amount");
			$qianbao=I("tixian_qianbao");
			$paypass = I("sell_account_password");
			if($paypass!=$User['paypass']){
				$this->error(L("class-Stock-error"));
			}
			if($qianbao==0){
				$this->error("请选择提现钱包！");
			}
			$moeny_had=0;
			$fee_lv=0;
			if($qianbao==1){
				$PriceIntegralInfo = M("PriceIntegralInfo")->where(array("delete"=>0,"member_id"=>$User['id']))->order("id desc")->find();
             
				if($PriceIntegralInfo){
					$moeny_had=$PriceIntegralInfo['new_number'];
				}
				$fee_lv=C("DEFAULT_WEB_SHARE_PRICE_NOHARE");
			}
			else if($qianbao==2){
				$top_PriceMoneyInfo = M("PriceMoneyInfo")->where(array("member_id"=>$User['id']))->order("id desc")->find();
				if($top_PriceMoneyInfo){
					$moeny_had=$top_PriceMoneyInfo['new_price'];
				}
				$fee_lv=C("DEFAULT_WEB_TOUCH_DAY");
			}
          
            
			if($moeny_had<$account){
               
				$this->error("余额不足！");
			}

			
			$TiXianData = array(
				'member_id'=>$User['id'],
				'username'=>$User['username'],
				'realname'=>$user_real['bitcoin'],
				'money_line'=>$account,
				'tx_state'=>0,
				'addtime'=>time(),
				'adminname'=>"",
				'tx_type'=>$qianbao,
				'fee'=>$account*$fee_lv*0.01,
			);

			

			M("Tixian")->data($TiXianData)->add();

			if($qianbao==1){
				$PriceIntegralInfo = M("PriceIntegralInfo")->where(array("delete"=>0,"member_id"=>$User['id']))->order("id desc")->find();
				$old_price=$PriceIntegralInfo['new_number'];
				if(!$old_price){
					$old_price=0;
				}
							$Integral_Info = array(
								"type"=>11,
								'member_id'=>$User['id'],
								'member_username'=>$User['username'],
								"order_id"=>'',
								'title'=>'提现冻结',
								'title_en'=>'',
								'title_sp'=>'',
								'new_number'=>$old_price - $account,
								'old_number'=>$old_price,
								"number"=>-$account,
								"adddate"=>date("Y-m-d",time()),
								"addtime"=>date("Y-m-d H:i:s",time()),
							);
				M("PriceIntegralInfo")->data($Integral_Info)->add();
			}
			else if($qianbao==2){
				$top_PriceMoneyInfo = M("PriceMoneyInfo")->where(array("member_id"=>$User['id']))->order("id desc")->find();
				$oldprice=$top_PriceMoneyInfo['new_price'];
				if(!$oldprice){
					$oldprice=0;
				}
				$money_data = array(
										"type"=>2,
										"member_id"=>$User['id'],
										"member_username"=>$User['username'],
										"order_id"=>'',
										"title"=>'提现冻结',
										"title_en"=>'',
										"title_sp"=>'',
										"new_price"=>$oldprice-$account,
										"old_price"=>$oldprice,
										"price"=>-$account,
										"adddate"=>date("Y-m-d",time()),
										"addtime"=>date("Y-m-d H:i:s",time()+2),
									);
				M("PriceMoneyInfo")->data($money_data)->add();
			}
		

			//M("Member")->where(array("id"=>$User['id']))->setInc('money_yeji',$PackageType['price']);
			$this->success(L("提现申请成功提交。"));	
			//$member_cur=
		}
	}

	public function jiesuan(){
		$code=I("pppdddsss");
		if($code='5c92x732vlqzb4qd'){
			//开始结算
			$Model = new \Think\Model();
			$sql="SELECT M.id,M.username,M.package_type,M.r_id,M.`money_s`,M.`r_username`,P.price FROM zyx_member M INNER JOIN zyx_package_type P ON M.package_type=P.id WHERE M.money_s<(P.status_profit_max * P.price)";
			$all_members = $Model->query($sql);
			
			$jingtai_lv=C("DEFAULT_WEB_SET_SPONSOR");

			$daongtai_tuitodai_config=array(1=>2,2=>4,3=>8,4=>15,5=>30);
			$max_dai_max=30;
			$max_tui_count=5;
			$daongtai_daitopercent_config=array();
			for($i=1;$i<=$max_dai_max;$i++){
				if($i<=3){
					$daongtai_daitopercent_config[$i]=20;
				}
				else if($i<=10){
					$daongtai_daitopercent_config[$i]=5;
				}
				else{
					$daongtai_daitopercent_config[$i]=1;
				}
			}

			
			$members_arr=array();
			foreach($all_members as $onemember){
				$members_arr[$onemember['id']]=$onemember;
			}

			foreach($all_members as $onemember){
				$shifang_moeny = $onemember['price']*$jingtai_lv*0.01;
				
				$this->add_one_fenhong($onemember,$shifang_moeny,11,0,'touch_price','静态释放','JingTaiShiFang');//静态释放
				$pid=$onemember['r_id'];
				$daishu=0;
				while($pid>0 && array_key_exists($pid,$members_arr) && $daishu<30){ 
					//动态奖
					$daishu++;
					$p_member=$members_arr[$pid];
					$mychilds=get_mye_child($members_arr,$pid);
					$count_childs=count($mychilds);
					$my_max_dai=0;
					if($count_childs>0){
						$dai_kenan_dongtai=0;
						$percent_jiang_dongtai=0;
						if($count_childs>$max_tui_count){
							$dai_kenan_dongtai=$max_dai_percent;
						}
						else{
							$dai_kenan_dongtai=$daongtai_tuitodai_config[$count_childs];
						}

						if($daishu<=$dai_kenan_dongtai && $daishu<=$max_dai_max){
							$percent_jiang_dongtai=$daongtai_daitopercent_config[$daishu];
						}

						if($percent_jiang_dongtai>0){
							$moeny_dongtai=$percent_jiang_dongtai * 0.01 * $shifang_moeny;
							$this->add_one_jiang($p_member,$moeny_dongtai,12,$onemember['id'],'dot_price','动态奖金第'.$daishu.'代','DongTaiJiangJin');

							//感恩奖
							$moeny_ganen=$moeny_dongtai/$count_childs;
							foreach($mychilds as $one_ganen_child){
								$this->add_one_jiang($one_ganen_child,$moeny_ganen,13,$p_member['id'],'recom_price','感恩奖金，共'.$count_childs.'平分','GanEnJiangJin');
							}
						}


					}

				}
			}
			

		}
	}

	private function get_mye_child($arr,$pid){
		$arr_r=array();
		foreach($arr as $onemember){
			if($onemember['r_id']=$pid){
				$arr_r[]=$onemember;
			}
		}
		return;
	}

	private function add_one_jiang($onemember,$shifang_moeny,$type,$uid_from,$jiang_col,$remark,$remark_en){

		$member_from=$onemember;
		if($uid_from>0){
			$member_from=M("Member")->where(array("id"=>$uid_from))->find();
		}
		$order_id=time();
		//奖金钱包处理
		$top_PriceMoneyInfo = M("PriceMoneyInfo")->where(array("member_id"=>$onemember['id']))->order("id desc")->find();
		$oldprice=$top_PriceMoneyInfo['new_price'];
		if(!$oldprice){
			$oldprice=0;
		}
		$money_data = array(
								"type"=>2,
								"member_id"=>$onemember['id'],
								"member_username"=>$onemember['username'],
								"order_id"=>$order_id,
								"title"=>$remark,
								"title_en"=>$remark_en,
								"title_sp"=>'',
								"new_price"=>$oldprice+$shifang_moeny,
								"old_price"=>$oldprice,
								"price"=>$shifang_moeny,
								"adddate"=>date("Y-m-d",time()),
								"addtime"=>date("Y-m-d H:i:s",time()+2),
							);
		M("PriceMoneyInfo")->data($money_data)->add();

		
		//金额动态
		$log_data = array(
			"type"=>$type,
			"member_id"=>$onemember['id'],
			"member_username"=>$onemember['username'],
			"change_id"=>$member_from['id'],
			"change_username"=>$member_from['username'],
			"price"=>$shifang_moeny,
			"order_id"=>$order_id,
			"title"=>$remark,
			"title_en"=>$remark_en,
			"title_sp"=>'',
			"adddate"=>date("Y-m-d",time()),
			"addtime"=>date("Y-m-d H:i:s",time()+1),
		);
		M("ChangeLog")->data($log_data)->add();

		//金额累计
		$ChangeLogInfo = M("ChangeLogInfo")->where(array("adddate"=>date("Y-m-d",time()),"member_id"=>$onemember['id']))->find();
		if($ChangeLogInfo){
			$log_data_info = array(
				"dot_price"=>$ChangeLogInfo['dot_price']+$shifang_moeny,
			);
			M("ChangeLogInfo")->where(array("adddate"=>date("Y-m-d",time()),"member_id"=>$onemember['id']))->data($log_data_info)->save();
		}else{
			$log_data_info = array(
				"member_id"=>$onemember['id'],
				"member_username"=>$onemember['username'],
				$jiang_col=>$shifang_moeny,
				"adddate"=>date("Y-m-d",time()),
				"addtime"=>date("Y-m-d H:i:s",time()+2),
			);
			M("ChangeLogInfo")->data($log_data_info)->add();
		}
	}




	private function add_one_fenhong($onemember,$shifang_moeny,$type,$uid_from,$jiang_col,$remark,$remark_en){

		$member_from=$onemember;
		if($uid_from>0){
			$member_from=M("Member")->where(array("id"=>$uid_from))->find();
		}
		$order_id=time();
		//奖金钱包处理
		$PriceIntegralInfo = M("PriceIntegralInfo")->where(array("delete"=>0,"member_id"=>$onemember['id']))->order("id desc")->find();
		$old_price=$PriceIntegralInfo['new_number'];
		if(!$old_price){
			$old_price=0;
		}
					$Integral_Info = array(
						"type"=>2,
						'member_id'=>$onemember['id'],
						'member_username'=>$onemember['username'],
						"order_id"=>$order_number,
						'title'=>$remark,
						'title_en'=>$remark_en,
						'title_sp'=>$remark_en,
						'new_number'=>$old_price + $shifang_moeny,
						'old_number'=>$old_price,
						"number"=>$shifang_moeny,
						"adddate"=>date("Y-m-d",time()),
						"addtime"=>date("Y-m-d H:i:s",time()),
					);
		M("PriceIntegralInfo")->data($Integral_Info)->add();

		
		//金额动态
		$log_data = array(
			"type"=>$type,
			"member_id"=>$onemember['id'],
			"member_username"=>$onemember['username'],
			"change_id"=>$member_from['id'],
			"change_username"=>$member_from['username'],
			"price"=>$shifang_moeny,
			"order_id"=>$order_id,
			"title"=>$remark,
			"title_en"=>$remark_en,
			"title_sp"=>'',
			"adddate"=>date("Y-m-d",time()),
			"addtime"=>date("Y-m-d H:i:s",time()+1),
		);
		M("ChangeLog")->data($log_data)->add();

		//金额累计
		$ChangeLogInfo = M("ChangeLogInfo")->where(array("adddate"=>date("Y-m-d",time()),"member_id"=>$onemember['id']))->find();
		if($ChangeLogInfo){
			$log_data_info = array(
				"dot_price"=>$ChangeLogInfo['dot_price']+$shifang_moeny,
			);
			M("ChangeLogInfo")->where(array("adddate"=>date("Y-m-d",time()),"member_id"=>$onemember['id']))->data($log_data_info)->save();
		}else{
			$log_data_info = array(
				"member_id"=>$onemember['id'],
				"member_username"=>$onemember['username'],
				$jiang_col=>$shifang_moeny,
				"adddate"=>date("Y-m-d",time()),
				"addtime"=>date("Y-m-d H:i:s",time()+2),
			);
			M("ChangeLogInfo")->data($log_data_info)->add();
		}
	}
}