<?php header("HTTP/1.1 451 Unavailable For Legal Reasons"); require_once 'setting.php'; include 'func.php';
  $sql = "SELECT `siteBan` FROM `_account` WHERE `id` = '$id'";
  $result = mysqli_query($conn, $sql);
  $sB = mysqli_fetch_assoc($result);
  if($sB['siteBan'] <= 0){
    echo '<script>location.href = "/main"</script>';
    exit;
?>
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
    <title><?=$fnTitle?> - 전체 차단</title>
    <meta name="description" content="FNBase Engine 2">
    <meta property="og:type" content="website">

    <!-- 불러오기 -->
    <link rel="stylesheet" href="/default.css">
    <link rel="stylesheet" href="/picnic.css">
    <link rel="stylesheet" type="text/css" href="/icofont2/icofont.min.css">
  </head>
  <body>
    <!-- 상단바 -->
    <header style="height:5em">
        <nav class="nav" style="background-color: #5998d6">
            <a href="/2" class="brand">
                <span style="color:#fff"><?=$fnTitle?></span>
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
                <blockquote class="graybq" style="border-left: 10px solid red;">
                <p>귀하께서는 이용 약관을 위반하여 접근이 제한되셨습니다.</p>
                </blockquote>
                <p>
                    대상 아이디 : <?=$_SESSION['fnUserId']?><br>
                    <span class="subInfo">기한은 고지되지 않으며, 소명을 원하시는 경우 <a href="/misc>vindicate">여기</a>서 글을 작성하십시오.</span><br>
                    <span class="subInfo">계정을 새로 가입하는 등의 방식으로 차단을 회피할 시 기한이 연장되거나 다른 처벌을 받게되실 수 있습니다.</span>
                </p>
            </section>
            <aside class="hidMob" id="nofiSec">
            </aside>
    </div>
</main>    
<hr>
</body>
</html>
