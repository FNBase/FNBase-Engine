<?php
# 이 파일은 FNBase Engine 2의 설정 파일입니다.
$fnVersion = '2.2'; #세팅 파일이 작성될 때의 버전입니다.

#데이터베이스 연결 설정입니다.
$fnSiteDB = 'localhost'; #데이터베이스 주소
$fnSiteDBuser = ''; #데이터베이스 유저
$fnSiteDBpw = ''; #데이터베이스 비밀번호
$fnSiteDBname = ''; #기본 데이터베이스 이름
$conn = mysqli_connect("$fnSiteDB", "$fnSiteDBuser", "$fnSiteDBpw", "$fnSiteDBname");


/* 이 아래는 일반적인 경우 수정하지 않으시는게 좋습니다. */
if(!$fnMultiNum){
  $fnMultiNum = 1;
}
$query = "SELECT * from `_setting` WHERE `num` = $fnMultiNum";
$query_result = mysqli_query($conn, $query);
if($query_result !== FALSE){
    while($setting = mysqli_fetch_assoc($query_result)){
        $fnTitle = $setting['siteTitle'];
        $fnDesc = $setting['siteDesc'];
        $fnPath = $setting['sitePath'];
        $fnFab = $setting['siteFab'];
        $fnLang = $setting['siteLang'];
        $fnEmMail = $setting['siteEmMail'];
        $fnPHead = $setting['pageHead'];
        $fnPLeft = $setting['pageLeft'];
        $fnPColor = $setting['pageColor'];
        $fnSColor = $setting['pageSubColor'];
        $fnBColor = $setting['pageBgColor'];
        $fnPFooter = $setting['pageFooter'];
        $fnTz = $setting['siteTimezone'];
        $fnRctHide = $setting['recentHide'];
        $fnType = $setting['type'];
    }
}else{
/* 데이터베이스 연결에 실패할 경우 메시지 출력 */
    die("데이터베이스 연결 실패.");
}

date_default_timezone_set($fnTz);
session_start();
$id = $_SESSION['fnUserId'];
$name = $_SESSION['fnUserName'];
function get_client_ip(){
    if(getenv('HTTP_X_FORWARDED_FOR'))
      $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
      $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
      $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
      $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
      $ipaddress = getenv('REMOTE_ADDR');
    else
      $ipaddress = '0.4.0.4';

    return $ipaddress;
}

$ip = get_client_ip();

if($fnType == 'OFF'){
    if(!$isNot){
        die('<script>location.href = "/sub/"</script>');
    }
}
?>
