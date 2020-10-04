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
	die('<script>파일만 올려주세요.</script>');
}
if($_FILES['myfile']['size'] > 5e+6){
    die('<script>이미지의 크기를 5MB 미만으로 줄이세요.</script>');
}
// 파일 이동
move_uploaded_file($_FILES['myfile']['tmp_name'], "$uploads_dir/$date$name");
$sql = "INSERT INTO `_upload` (`filename`, `at`, `ip`) VALUES ('$date$name', CURRENT_TIMESTAMP, '$ip');";
$result = mysqli_query($conn, $sql);
$name = str_ireplace(' ', '%20', $name);
// 파일 정보 출력
echo $fnPath.'/upload/'.$date.$name;
}
?>