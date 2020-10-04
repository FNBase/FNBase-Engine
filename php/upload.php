<?php
require_once '../setting.php';

$ip = get_client_ip();
$id = $_SESSION['fnUserId'];

if(!empty($id) and $id != '0'){
    $sql = "SELECT `siteBan`, `canUpload` FROM `_account` WHERE `id` = '$id'";
    $result = mysqli_query($conn, $sql);
    $sB = mysqli_fetch_assoc($result);
    if($sB['siteBan'] >= 1){
        exit;
    }
    if(!$sB['canUpload']){
        die('업로드 권한 없음');
    }
}else{
    exit;
}

$date = time();
if(!empty($_FILES['myfile']) and $_FILES['myfile'] != '0'){
// 설정
$uploads_dir = '../upload';
$allowed_ext = array('jpg','jpeg','png','webp', 'JPG', 'PNG');
 
// 변수 정리
$error = $_FILES['myfile']['error'];
$name = $_FILES['myfile']['name'];
$ext = array_pop(explode('.', $name));
 
// 오류 확인
if( $error != UPLOAD_ERR_OK ) {
	switch( $error ) {
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			echo "파일이 너무 큽니다. ($error)";
			break;
		case UPLOAD_ERR_NO_FILE:
			echo "파일이 첨부되지 않았습니다. ($error)";
			break;
		default:
			echo "파일이 제대로 업로드되지 않았습니다. ($error)";
	}
	exit;
}

if(!in_array($ext, $allowed_ext) ) {
	die('<script>alert(".png, .jpg/.jpeg, .webp 파일만 올려주세요.");history.back();</script>');
}
if($_FILES['myfile']['size'] > 2e+6){
    die('<script>alert("이미지의 크기를 2MB 미만으로로 줄이세요.");history.back();</script>');
}
// 파일 이동
move_uploaded_file($_FILES['myfile']['tmp_name'], "$uploads_dir/$date$name");
$sql = "INSERT INTO `_upload` (`filename`, `at`, `ip`) VALUES ('$date$name', CURRENT_TIMESTAMP, '$ip');";
$result = mysqli_query($conn, $sql);
$name = str_ireplace(' ', '%20', $name);
// 파일 정보 출력
echo '<!-- FNBase Engine 2 -->
<!DOCTYPE html>
<html lang="ko-KR">
  <head>
    <meta charset="UTF-8">
    <meta name="robots" content="ALL">
    <meta name="author" content="Estrella3">
    <meta name="theme-color" content="#5998d6">
    <meta name="classification" content="html">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- 정보 -->
    <title>FNBase - 업로드 완료</title>
    <meta name="description" content="FNBase Engine 2">
    <meta property="og:type" content="website">

    <!-- 불러오기 -->
    <link rel="stylesheet" href="../default.css">
    <link rel="stylesheet" href="../picnic.css">
  </head>
  <body>
    <!-- 상단바 -->
    <header style="height:5em">
        <nav class="nav" style="background-color: #5998d6">
            <a href="/" class="brand">
                <span style="color:#fff">FNBase</span>
            </a>
        </nav>
    </header>
    <!-- 페이지 로드 -->
            <main>
        <div class="flex">
        <!-- 상단 보조메뉴 -->
            <section class="hidMob">
            </section>
            <section id="mainSec" class="half">
                <blockquote class="graybq">
                <p>아래 링크를 붙여넣으세요.</p>
                </blockquote>
	<h2>파일 정보</h2>
	<ul>
		<li>파일명: '.$date.$name.'</li>
		<li>확장자: '.$ext.'</li>
		<li>주소 복사: <input readonly id="imgsrc" value="'.$fnPath.'/upload/'.$date.$name.'"></li>
	</ul>
	<button class="full" onclick="copy_to_clipboard()">복사</button>
		<img style="max-width:100%" src="../upload/'.$date.$name.'">
</section>
<aside id="nofiSec" class="hidMob">
</aside>';
}else{
    echo '파일이 없어요!';
}
?>	
<script>
function copy_to_clipboard() {
var copyText = document.getElementById("imgsrc");
copyText.select();
document.execCommand("Copy");
history.go(-3);
}
</script>
</body>
</html>