<?php
namespace Home\Controller;
use Think\Controller;
class JieSuanController extends Controller {

    //DELETE FROM zyx_change_log WHERE id>0;DELETE FROM zyx_change_log_info WHERE id>0;DELETE FROM zyx_price_money_info WHERE id>0;DELETE FROM zyx_price_integral_info WHERE id>0;
    public function jiesuan(){
		$code=I("pppdddsss");
		if($code=='5c92x732vlqzb4qd'){
			//开始结算
			$Model = new \Think\Model();
			$sql="SELECT M.id,M.username,M.package_type,M.r_id,M.`money_s`,M.`r_username`,M.`status`,P.price FROM zyx_member M INNER JOIN zyx_package_type P ON M.package_type=P.id WHERE M.money_s<(P.status_profit_max * P.price)";
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
                $pid=$onemember['r_id'];
                
				//var_dump(array_key_exists($pid,$members_arr));die;
				
				if($onemember['status'] != 1){
					continue;
				}
				
				$this->add_one_fenhong($onemember,$shifang_moeny,11,0,'touch_price','静态收益','JingTaiShiFang');//静态释放
				//$str_log=$onemember['username']."静态释放，金额：".$shifang_moeny;
				//\Think\Log::record($onemember['username']."静态释放，金额：".$shifang_moeny);
				$daishu=0;
				while($pid>0 && array_key_exists($pid,$members_arr) && $daishu<30){ 
					//动态奖
					$daishu++;
					$p_member=$members_arr[$pid];

					
                    $mychilds=$this->get_mye_child($members_arr,$pid);
					
					$pid=$p_member['r_id'];


					if($p_member['status'] != 1){
						continue;
					}

                    $count_childs=count($mychilds);
                   
					$my_max_dai=0;
					if($count_childs>0){
						$dai_kenan_dongtai=0;
						$percent_jiang_dongtai=0;
						if($count_childs>$max_tui_count){
							$dai_kenan_dongtai=$max_dai_max;
						}
						else{
							$dai_kenan_dongtai=$daongtai_tuitodai_config[$count_childs];
						}

						if($daishu<=$dai_kenan_dongtai && $daishu<=$max_dai_max){
							$percent_jiang_dongtai=$daongtai_daitopercent_config[$daishu];
						}

						if($percent_jiang_dongtai>0){
							$moeny_dongtai=$percent_jiang_dongtai * 0.01 * $shifang_moeny;
							if($p_member['package_type']>2 || ($p_member['package_type']<=2 && $daishu==1)){
								$this->add_one_jiang($p_member,$moeny_dongtai,12,$onemember['id'],'dot_price','动态奖金第'.$daishu.'代','DongTaiJiangJin');
							}
							
							//$str_log=$str_log.$p_member['username']."获得动态奖金，金额：".$moeny_dongtai."；";
							//\Think\Log::record($p_member['username']."获得动态奖金，金额：".$moeny_dongtai."；");
							//感恩奖
							$moeny_ganen=($moeny_dongtai*0.1)/$count_childs;
							foreach($mychilds as $one_ganen_child){
								if($one_ganen_child['package_type']>2 && $one_ganen_child['status'] == 1){
									$this->add_one_jiang($one_ganen_child,$moeny_ganen,13,$p_member['id'],'recom_price','感恩奖金，共'.$count_childs.'平分','GanEnJiangJin');
								}
								
								//$str_log=$str_log.$one_ganen_child['username']."获得感恩奖金，".$count_childs."人平分，金额：".$moeny_ganen."；";
								//\Think\Log::record($one_ganen_child['username']."获得感恩奖金，".$count_childs."人平分，金额：".$moeny_ganen."；");
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
			if($onemember['r_id']==$pid){
				$arr_r[]=$onemember;
			}
		}
		return $arr_r;
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
				$jiang_col=>$ChangeLogInfo[$jiang_col]+$shifang_moeny,
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
        
        //更新领奖数额
        M("Member")->where(array("id"=>$onemember['id']))->setInc('money_s',$shifang_moeny);
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
						"order_id"=>'',
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
				$jiang_col=>$ChangeLogInfo[$jiang_col]+$shifang_moeny,
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
        
        M("Member")->where(array("id"=>$onemember['id']))->setInc('money_s',$shifang_moeny);
	}
}