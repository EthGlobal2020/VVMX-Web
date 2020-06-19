<?php
namespace Admin\Controller;
use Think\Controller;
class DataController extends CommonController {
    //管理员列表
    public function admin(){
        $this->model = session("model");
        session("model",null);
        $count = M("Admin")->where(array("delete"=>0))->count();
        $Page = new \Think\Page($count,10);
        $show = $Page->show();
        $list = M("Admin")->where(array("delete"=>0))->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('Admin',$list);
        $this->assign('page',$show);
        $this->p = I("p",0);
        $this->display();
    }
    //添加管理员
    public function addAdmin(){
        $id = I("id");
        if(IS_POST){
            if($id!=""){
                $_POST["id"] = $id;
                if(I("userpass")==""){
                    $Member = M("Admin")->where(array("id"=>$id))->find();
                    $_POST["userpass"] = $Member["userpass"];
                    $_POST["repass"] = $Member["userpass"];
                }
            }
            $_POST['picture'] = UploadImg('picture',"Admin",$_POST["id"]);
            $AddAdmin = D("AddAdmin");
            if (!$AddAdmin->create()){
                session("model",1);
                $this->error($AddAdmin->getError(),U('Admin/Data/addAdmin',array("id"=>$id,"p"=>I('p'))));
            }else{
                if($id!=""){
                    if($id==1){
                        $_POST["status"] = 1;
                    }
                    $result = $AddAdmin->save();
                    if($result||$result==0){
                        session("model",4);
                    }else{
                        session("model",3);
                    }
                }else{
                    if($AddAdmin->add()){
                        session("model",4);
                    }else{
                        session("model",3);
                    }
                }
                $this->redirect('Admin/Data/admin', array("p"=>I('p')), 0, '页面跳转中...');
            }
        }else{
            if($id!=""){
                $this->Admin = M("Admin")->where(array("id"=>$id,"delete"=>0))->find();
            }
            $this->p = I("p");
            $this->display();
        }
    }
    //管理员状态
    public function statusAdmin(){
        $id = I("id");
        if($id==1){
            $this->error("管理员账户无法关闭。",U("Admin/Data/admin"));
        }
        $result = M("Admin")->where(array("id"=>$id))->data(array("status"=>I("status")))->save();
        if($result||$result==0){
            session("model",4);
        }else{
            session("model",3);
        }
        $this->redirect('Admin/Data/admin',array("p"=>I('p')), 0, '页面跳转中...');
    }
    //删除管理员
    public function deleteAdmin(){
        $id = I("id");
        if($id==1){
            $this->error("管理员账户无法删除。",U("Admin/Data/admin"));
        }
        if(del("Admin",$id)){
            session("model",4);
        }else{
            session("model",3);
        }
        $this->redirect('Admin/Data/admin',array("p"=>I('p')), 0, '页面跳转中...');
    }
    public function clear(){
        $KEY = I("KEY");
        if($KEY=="DELETE_ADMIN"){
            $file = rmFile("./Application/Runtime/Cache/",'Admin');
            session("model",4);
            $this->redirect('Admin/Data/clear',array("file"=>$file), 0, '页面跳转中...');
        }
        if($KEY=="DELETE_HOME"){
            $file = rmFile("./Application/Runtime/Cache/",'Home');
            session("model",4);
            $this->redirect('Admin/Data/clear',array("file"=>$file), 0, '页面跳转中...');
        }
        $this->file = I("file");
        $this->model = session("model");
        session("model",null);
        $this->display();
    }
    public function save() {
        $DataDir = "Install/Data/";
        mkdir($DataDir);
        if (!empty($_GET['Action'])) {
            $config = array(
                'host' => C('DB_HOST'),
                'port' => C('DB_PORT'),
                'userName' => C('DB_USER'),
                'userPassword' => C('DB_PWD'),
                'dbprefix' => C('DB_PREFIX'),
                'charset' => 'UTF8',
                'path' => $DataDir,
                'isCompress' => 0, //是否开启gzip压缩
                'isDownload' => 0
            );
            $mr = new \Org\Util\MySQLReback($config);
            $mr->setDBName(C('DB_NAME'));
            if ($_GET['Action'] == 'backup') {
                $mr->backup();
                session("model",4);
				$this->redirect(U("Admin/Data/save"),array(),0,"");
                //数据库备份成功！
            } elseif ($_GET['Action'] == 'RL') {
                $mr->recover($_GET['File']);
                session("model",4);
				$this->redirect(U("Admin/Data/save"),array(),0,"");
                // 数据库还原成功！
            } elseif ($_GET['Action'] == 'Del') {
                if (@unlink($DataDir . $_GET['File'])) {
                    //删除成功
                    session("model",4);
					$this->redirect(U("Admin/Data/save"),array(),0,"");
                } else {
                    session("model",3);
                }
            }
            if ($_GET['Action'] == 'download') {
                function DownloadFile($fileName) {
                    ob_end_clean();
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Length: ' . filesize($fileName));
                    header('Content-Disposition: attachment; filename=' . basename($fileName));
                    readfile($fileName);
                }
                DownloadFile($DataDir . $_GET['file']);
                exit();
            }
        }
        $this->model = session("model");
        session("model",null);
        $lists = $this->MyScandir('Install/Data/');
        $this->assign("datadir",$DataDir);
        $this->assign("lists", $lists);
        $this->display();
    }

    private function MyScandir($FilePath = './', $Order = 0) {
        $FilePath = opendir($FilePath);
        while (false !== ($filename = readdir($FilePath))) {
            $FileAndFolderAyy[] = $filename;
        }
        $Order == 0 ? sort($FileAndFolderAyy) : rsort($FileAndFolderAyy);
        return $FileAndFolderAyy;
    }
}
