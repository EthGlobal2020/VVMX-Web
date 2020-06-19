<?php


//处理方法
function rmdirr($dirname) {
    if (!file_exists($dirname)) {
        return false;
    }
    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }
    $dir = dir($dirname);
    if ($dir) {
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            //递归
            rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
        }
    }
}

//公共函数
//获取文件修改时间
function getfiletime($file, $DataDir) {
    $a = filemtime($DataDir . $file);
    $time = date("Y-m-d H:i:s", $a);
    return $time;
}

//获取文件的大小
function getfilesize($file, $DataDir) {
    $perms = stat($DataDir . $file);
    $size = $perms['size'];
    // 单位自动转换函数
    $kb = 1024;         // Kilobyte
    $mb = 1024 * $kb;   // Megabyte
    $gb = 1024 * $mb;   // Gigabyte
    $tb = 1024 * $gb;   // Terabyte

    if ($size < $kb) {
        return $size . " B";
    } else if ($size < $mb) {
        return round($size / $kb, 2) . " KB";
    } else if ($size < $gb) {
        return round($size / $mb, 2) . " MB";
    } else if ($size < $tb) {
        return round($size / $gb, 2) . " GB";
    } else {
        return round($size / $tb, 2) . " TB";
    }
}


/*状态变更*/
function changeStatus($table,$id,$status,$table_name = "status"){
    if(empty($id)||$status==""||empty($table)){
        return false;
    }
	$result = M($table)->where(array("id"=>$id))->data(array($table_name=>$status))->save();
    if($result||result==0){
        return true;
    }else{
        return false;
    }
}
/*变成推荐*/
function changeRecom($table,$id,$status,$table_name = "recom"){
    if(empty($id)||$status==""||empty($table)){
        return false;
    }
    if(M($table)->where(array("id"=>$id))->data(array($table_name=>$status))->save()){
        return true;
    }else{
        return false;
    }
}
/*回收站*/
function del($table,$id,$result = false){
    if(!$result){
        $User = SESSION("User");
        $tableList = array(
            "PackageType"=>"套餐分类",
            "News"=>"新闻",
            "Information"=>"信息",
            "Admin"=>"后台用户管理",
            "Member"=>"会员",
            "Cash"=>"Cash存款",
            "Split"=>"拆分记录"
        );
        if(!$rs = M($table)->where(array("id"=>$id,"delete"=>0))->find()){
            return false;
        }
        $title = empty($rs['title'])?$rs['name']:$rs['title'];
        $addTime = date("Y-m-d H:i:s",time());
        M("delete")->where(array("delete_id"=>$id,"delete_table"=>$table))->delete();
        if($result = M("delete")->data(array("delete_id"=>$id,"delete_table"=>$table,"user_id"=>$User['id'],"user_name"=>$User['name'],"delete_type"=>$tableList[$table],"delete_title"=>$title,"addtime"=>$addTime))->add()){
            if(M($table)->where(array("id"=>$id))->data(array("delete"=>1))->save()){
                return true;
            }else{
                M("delete")->where(array("id"=>$result))->delete();
                return false;
            }
        }else{
            return false;
        }
    }else{
        if(M($table)->where(array("id"=>$id))->delete()){
            return true;
        }else{
            return false;
        }
    }
}
?>