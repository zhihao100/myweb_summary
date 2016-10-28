<?php
$servername = "localhost";
$username = "root";
$password = "";
$db_database="myweb";
   // 创建连接
$link= mysqli_connect($servername, $username, $password);
   // 检测连接
if (!$link) {
die("Connection failed: " . mysqli_connect_error());
}
   // 打开指定数据库
mysqli_select_db($link,$db_database) or die("数据库无法打开.ERROR:".mysqli_errno($link).":".mysqli_error($link));

//单文件上传函数如下
function uploadFile($fileInfo){
     // 获取上传文件的扩展名
    $exte = pathinfo($fileInfo['name'],PATHINFO_EXTENSION);
    $maxsize = 52428800;
    $filePath = "upload";
    $allowexte = array('doc','png', 'jpg', 'jpeg', 'gif', 'rar','zip','docx', 'ppt','pptx','xls','xlsx','txt','pdf','c','cpp','mp4','avi');
    // 1.判断文件上传错误信息,只有错误信息为0或UPLOAD_ERRO_OK时上传成功
    if ($fileInfo['error'] == 0) {
        // 2.判断文件是否满足设定的上传大小
        if ($fileInfo['size'] > $maxsize) {
            exit("上传文件过大超过了系统设置的最大上传大小" . $maxsize /(1024*1024) .'M');
        }
         //3.判断上传文件类型是否满足设置的条件
        if (!in_array($exte, $allowexte)) {
            exit("上传文件类型非法");
        }
    
        // 4.检测上传文件是否存在且有效（$fileInfo['tmp_name']为文件临时路径）
        if (! is_uploaded_file($fileInfo['tmp_name'])) {
            exit("文件不存在或文件无效");
        }
        // 5.判断指定路径是否存在,如果不存在就创建该路径目录
        if (! file_exists($filePath)) {
            mkdir($filePath, 0777, true);
            chmod($filePath, 0777);
        }
        // 6.随机产生一个文件名，防止上传的文件名相同时产生覆盖
        $uniFilename = md5(uniqid(microtime(true), true)); 
        $destination = $filePath . '/' . $uniFilename . '.' . $exte;
        //move_uploaded_file()接收两个参数，原临时路径和目标路径
        if (!@move_uploaded_file($fileInfo['tmp_name'], $destination)) {
            exit("文件上传失败");
        }
        echo "文件".$fileInfo['name']."上传成功！<br />";
        return array('fileName'=>$uniFilename.'.'.$exte, 'fileRelname'=>$fileInfo['name'],'type'=>$fileInfo['type'],'size'=>$fileInfo['size']);
    } else {
        // 7.错误信息匹配
        $meg="";
        switch ($fileInfo['error']) {
            case 1:
                $meg = "上传文件大小超过PHP配置文件中MAX_UPLOAD_SIZE的大小";
                break;
            case 2:
                $meg =  "上传文件超过了表单设置的最大大小";
                break;
            case 3:
                $meg =  "文件被部分上传";
                break;
            case 4:
                $meg =  "没有上传文件！";
                break;
            case 6:
                $meg =  "没有找到临时文件";
                break;
            case 7:
            case 8:
                $meg =  "系统错误";
                break;
        }
        echo $meg;
    }
}

//前期准备完成，准备上传
$del_id=$_POST['del_id'];
$files = $_FILES;
$a=array();
//$del_id为删除序号的字符串，用explode()将其转换成数组
$a=explode(',',$del_id);
if($del_id!=="") {
    for ($m = 0; $m < count($a); $m++) {
        $id = $a[$m];
        $files['files']['name'][$id] = "";
    }
}

$time=date("Y-m-d H:i:s");

for($k = 0;$k<count($files['files']['name']);$k++){
    if ($files['files']['name'][$k]=="") {

    }else {
            $name = @$files['files']['name'][$k];
            $type = @$files['files']['type'][$k];
            $tmp_name = @$files['files']['tmp_name'][$k];
            $size = @$files['files']['size'][$k];
            $error = @$files['files']['error'][$k];
            $fileSingle = null;
            $fileSingle = Array('name' => $name, 'type' => $type, 'tmp_name' => $tmp_name, 'size' => $size, 'error' => $error);
            $fileSingle = uploadFile($fileSingle);
            $lf_r_name = $fileSingle['fileRelname'];
            $lf_name = $fileSingle['fileName'];
            $type = $fileSingle['type'];
            $size = ceil($fileSingle['size'] / 1024) . 'KB';
            if (ceil($fileSingle['size'] / 1024) > 1024) {
                $size = ceil($fileSingle['size'] / (1024 * 1024)) . 'MB';
            }
            $sql = "insert into pic_file(lf_r_name,lf_name,lf_type,lf_size,time) VALUES ('$lf_r_name','$lf_name','$type','$size','$time')";
            $file_result = mysqli_query($link, $sql) or die("图片上传有错误");

        }
    }

if($file_result){
    alertMes("xxxxx.php", "上传成功");
}else{
    alertMes("xxxxx.php", "上传失败");
}
?>
