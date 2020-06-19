<?php
namespace Home\Controller;
use Think\Controller;
class NewsController extends CommonController {
    public function index(){
        $this->display();
    }
	public function news(){
		$News = M('News'); 
		$list = $News->where('status=1')->order('id desc')->select();
		$this->assign('list',$list);
		$this->display();
	}
}