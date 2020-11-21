<?php
require_once 'setting.php';
require 'func.php';
ini_set('pcre.backtrack_limit', '3000000000');

$id = $_SESSION['fnUserId'];
$name = $_SESSION['fnUserName'];
$mail = $_SESSION['fnUserMail'];

if($id == NULL){
    die('<script>alert("로그인이 필요합니다.");history.back()</script>');
}

if (!empty($id) and $id != '0') {
    $sql = "SELECT `siteBan` FROM `_account` WHERE `id` = '$id'";
    $result = mysqli_query($conn, $sql);
    $sB = mysqli_fetch_assoc($result);
    if ($sB['siteBan'] >= 1){
        die('<script>location.href = \'banned.php\'</script>');
    }
}
$sql = "SELECT `ip` FROM `_ipban` WHERE `ip` = '$ip'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    die('<script>location.href = \'banned.php\'</script>');
}

$title = filt($_POST['title'], 'htm');
$desc = filt($_POST['content'], 'con');
$b = filt($_POST['b'], 'htm');
$n = filt($_POST['n'], '123');
$sn = filt($_POST['sn'], '123');
$sni = filt($_POST['sni'], '123');
$bn = filt($_POST['bn'], 'htm');
$cat = filt($_POST['category'], 'htm');
$rate = filt($_POST['rate'], 'abc');
$im = filt($_POST['isMd'], '123');
$sO = filt($_POST['staffOnly'], 'csv');
$m = filt($_GET['m'], 'htm');

if($_POST['editor'] == ''){
    $e = 'NULL';
}else{
    $e = "'".filt($_POST['editor'], 'abc')."'";
}

if($_GET['yes'] == 'please'){ #에디터 변경
    if($_SESSION['fnUserId']){
        $sql = "UPDATE `_userSet` SET `editor` = $e WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        die('<script>window.location.href = document.referrer;</script>');
    }
}

if($_GET['e'] == 'dlt'){
    sleep(1);
}elseif(empty($id) and $id != '0'){
    die('<script>alert("로그인이 필요합니다.");location.href="/register"</script>');
}elseif(empty($title) and $title != '0'){
    $title = '<red>(제목 없음)</red>';
}elseif(empty($desc) and $desc != '0'){
    die('<script>alert("내용이 비어있습니다.");history.back()</script>');
}

#약식 등급 심의
$pattern = '/(((시|씨|쉬|ㅅ)[0-9]*(발|빨|펄|빨|ㅂ))|((지|쥐|ㅈ)[0-9]*(랄|럴|롤|ㄹ))|((미|ㅁ)[0-9]*(친|쳣|쳤|ㅊ))|한(녀|남)|운지|우흥|재기해|냄져|자살|담배|찐(내|따|빠)|(봊|쥬|뷰)(지|이)|(보|자)지|씹|(수|화|면|강)간|히토미|야동|(겠|맞|렸|아니)노|좆|썅|니미|느금|느개비)/mi';
if(preg_match($pattern, $title.' '.$desc)){
    $rate = 'R';
}

if(preg_match('/(discord\.gg|open\.kakao\.com)/m', $desc)){
    $sql = "UPDATE `_account` SET `siteBan` = '1' WHERE `id` = '$id';";
    $result = mysqli_query($conn, $sql);
    exit;
}elseif(preg_match('/(discord\.gg|open\.kakao\.com)/m', $title)){
    $sql = "UPDATE `_account` SET `siteBan` = '1' WHERE `id` = '$id';";
    $result = mysqli_query($conn, $sql);
    exit;
}

$s = $sO;
if(empty($sO) and $sO != '0'){
    $sO = 'NULL';
}else{
    $sOq = "'";
}

if($cat == '공지'){
    $sql = "SELECT `id`, `keeper` FROM `_board` WHERE `slug` like '$b'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if(strcasecmp($row['id'], $id) !== 0){
        if(mb_strpos($row['keeper'], $id) === FALSE){
            $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = '$id'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            if(!$row['isAdmin']){
                $cat = '기본';
            }
        }
    }
}else{
    $sql = "SELECT `tagSet` as `t` FROM `_board` WHERE `slug` like '$b'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if(mb_strpos($row['t'], $cat) === FALSE){
        if(!$row['isAdmin']){
            $cat = '기본';
        }
    }
}

$ip = get_client_ip();

$now = date('Y-m-d H:i:s');


if($_GET['e'] == 'dit'){ #수정
    $sql = "SELECT * FROM `_content` WHERE `num` = '$n'";
    $outcome = mysqli_query($conn, $sql);
    $outcome = mysqli_fetch_assoc($outcome);

    if($outcome['id'] !== $id){
        die('아이디가 일치하지 않습니다!');
    }

    if($outcome['content'] == $desc){
        if($outcome['rate'] == $rate){
            if($outcome['category'] == $cat){
                if($outcome['title'] == $title){
                    if($outcome['staffOnly'] == $s){
                        if($outcome['isMarkdown'] == $im){
                            die('<script>alert("변경 사항이 전혀 없습니다!");history.back()</script>');
                        }
                    }
                }
            }
        }
    }

    if(preg_match('/(youtube\.com|youtu.be)/m', $desc)){
        $isMedia = "'3'";
    }elseif(preg_match('/(http:\/\/|https:\/\/).+\/.+\.(mp4|mov|avi|ogg|ogv|flv|3gp|webm|mkv)/m', $desc)){
        $isMedia = "'2'";
    }elseif(preg_match('/(http:\/\/|https:\/\/).+\/.+\.(png|jpg|jpeg|gif|webp|svg)/m', $desc)){
        $isMedia = "'1'";
    }else{
        $isMedia = 'NULL';
    }

    $sql = "UPDATE `_content` SET `type` = 'COMMON', `title` = '$title', `content` = '$desc',
    `category` = '$cat', `rate` = '$rate', `isMarkdown` = '$im', `staffOnly` =  $sOq$sO$sOq, `isEdited` = '$now', `whoEdited` = '$name', `isMedia` = $isMedia, `actmeter` = NOW() WHERE `num` = '$n';";

    $result = mysqli_query($conn, $sql);
    if(!$result){
        die($sql);
    }

            //호출 처리
            preg_match_all('/[^\"][\>]@[^\s\n<>]+/', $desc, $out_arr);
            $i = 0;
            foreach( $out_arr['0'] as $value ){
                $mnt_name = explode('@', $value);
                $mnt_name = $mnt_name[1];
                $sql = "SELECT `id` from `_account` WHERE `name` = '$mnt_name'";
                $result = mysqli_query($conn, $sql);
                $mnt_id = mysqli_fetch_assoc($result);
                if(mysqli_num_rows($result) == 1){
                    $desc = preg_replace('/@'.$mnt_name.'/', '<a href="/u/'.$mnt_id['id'].'">@'.$mnt_name.'</a>', $desc); #내용에서 변경
                    $i++;
                    if($i > 40){ #안전장치
                        break;
                    }
                }
            }
            $sql = "UPDATE `_content` SET `content` = '$desc' WHERE `num` = '$n'"; #멘션으로 변경된 내용 반영
            $result = mysqli_query($conn, $sql);

    if($outcome){
        die('<script>location.href = "b>'.$b.'>'.$outcome['num'].'"</script>');
    }else{
        die('데이터베이스 연결 오류');
    }
}elseif($_GET['e'] == 'dlt'){ #삭제
    $sql = "SELECT * FROM `_content` WHERE `num` = '$n'";
    $outcome = mysqli_query($conn, $sql);
    $outcome = mysqli_fetch_assoc($outcome);

    if($outcome['id'] !== $id){
        die('아이디가 일치하지 않습니다!');
    }
    if($outcome['commentCount'] > 0){
        die('작성된 댓글이 있어, 수정이 불가능합니다.');
    }

    $sql = "DELETE FROM `_content` WHERE `num` = '$n'";

    $result = mysqli_query($conn, $sql);
    if(!$result){
        die($sql);
    }

    $sql = "UPDATE `_account` SET `point` = `point` - 10 WHERE `id` = '$id';";
    $result = mysqli_query($conn, $sql);
    if(!$result){
        die($sql);
    }

    if($result){
        die('<script>location.href = "b>'.$outcome['board'].'"</script>');
    }else{
        die('데이터베이스 연결 오류');
    }
}elseif($_GET['e'] == 'manag'){ #권한 조정
    $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
    $result = mysqli_query($conn, $sql);
    $iA = mysqli_fetch_assoc($result);
    if($m == 'canUpload'){
        $sn = $sni;
    }
        if($iA['isAdmin']){
            $sql = "UPDATE `_account` SET `$m` = '$sn' WHERE `id` = '$title';";
            $result = mysqli_query($conn, $sql);
            if(!$result){
                die($sql);
            }else{
                die('<script>window.location.href = document.referrer;</script>');
            }
        }else{
            die('권한이 없습니다.');
        }
}elseif($_GET['e'] == 'usr'){ #정보 수정
    $title = preg_replace('/[^0-9a-zA-Zㄱ-ㅎ가-힣_]/', '', $title);
    if($b == $_SESSION['fnUserId']){
        if($_POST['aL'] == 'no'){
            $al = 0;
        }else{
            $al = 1;
        }
        if($_POST['hA'] == 'no'){
            $ha = 0;
        }else{
            $ha = 1;
        }
        if(empty($_POST['homepage']) and $_POST['homepage'] != '0'){
            $hp = 'recent';
        }else{
            $hp = filt($_POST['homepage'], 'abc');
        }
        $sql = "UPDATE `_account` SET";
        $sql .= " `name` = '$title',";
                if(!empty($_POST['password_old']) and $_POST['password_old'] != '0'){
                    $sql_ = "SELECT `password` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
                    $result = mysqli_query($conn, $sql_);
                    $r = mysqli_fetch_assoc($result);

                    $verify = password_verify($_POST['password_old'], $r['password']);
                    if($verify){
                        $pw = filt($_POST['password_new'], 'htm');
                        $pw = password_hash($pw, PASSWORD_DEFAULT);
                        $sql .= " `password` = '$pw',";
                    }else{
                        die('<script>alert("비밀번호가 일치하지 않습니다.");history.back()</script>');
                    }
                }
                if($title != $_SESSION['fnUserName']){
                    $sql_ = "SELECT `point` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
                    $result = mysqli_query($conn, $sql_);
                    $r = mysqli_fetch_assoc($result);

                    if($r['point'] < 1000){
                        die('<script>alert("포인트가 부족합니다.");history.back()</script>');
                    }else{
                        $sql .= ' `point` = `point` - 1000,';
                    }
                }
        $desc = filt($desc, 'htm');
        $ln = filt($_POST['listNum'], '123');
        if($ln > 50){
            $ln = 50;
        }
        $sql .= " `userIntro` = '$desc', `autoLogin` = '$al'";
        $sql .= " WHERE `id` = '$b';";
        $result = mysqli_query($conn, $sql);
        if(!$result){
            die('<script>alert("닉네임이 중복되거나 데이터베이스 오류입니다.");location.href = "/login"</script>');
        }else{
            $sql_ = "UPDATE `_board` SET `name` = '$title' WHERE `id` = \"".$_SESSION['fnUserId'].'"';
            $result = mysqli_query($conn, $sql_);
            $sql_ = "UPDATE `_userSet` SET `hideAdv` = '$ha', `homepage` = '$hp', `listNum` = '$ln' WHERE `id` = \"".$_SESSION['fnUserId'].'"';
            $result = mysqli_query($conn, $sql_);
            session_unset();
            die('<script>location.href = "/login"</script>');
        }
    }else{
        die('본인이 아닙니다.');
    }
}else{ #작성
    $sql = "SELECT Count(*) as `cnt` FROM `_content` WHERE `id` = '$id' and `at` > DATE_SUB(NOW(), INTERVAL 2 SECOND)";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    if($row['cnt'] >= 1){
        die('<script>alert("글 작성 빈도가 너무 짧습니다.");history.back()</script>');
    }
    $sql = "SELECT Count(*) as `cnt` FROM `_content` WHERE `id` = '$id' and `at` > DATE_SUB(NOW(), INTERVAL 10 SECOND)";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    if($row['cnt'] >= 3){
        die('<script>alert("글 작성 빈도가 너무 짧습니다.");history.back()</script>');
    }

    if(preg_match('/(youtube\.com|youtu.be)/m', $desc)){
        $isMedia = "'3'";
    }elseif(preg_match('/(http:\/\/|https:\/\/).+\/.+\.(mp4|mov|avi|ogg|ogv|flv|3gp|webm|mkv)/m', $desc)){
        $isMedia = "'2'";
    }elseif(preg_match('/(http:\/\/|https:\/\/).+\/.+\.(png|jpg|jpeg|gif|webp|svg)/m', $desc)){
        $isMedia = "'1'";
    }else{
        $isMedia = 'NULL';
    }

    if($b == 'quiz'){
        $ans = filt($_POST['answer'], 'htm');
        $prz = filt($_POST['prize'], '123');
        if(strlen($ans) > 20){
            $ans = mb_substr($ans, 0, 20);
        }
        if($prz > 100000){
            $prz = 100000;
        }elseif($prz < 300){
            $prz = 300;
        }
        $sql = "SELECT `point` FROM `_account` WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        $r = mysqli_fetch_assoc($result);
        if($r['point'] > $prz){
            $cat = '진행';
        }else{
            $cat = '제외';
        }
    }

    //글 저장
    $sql = "INSERT INTO `_content` (`id`, `name`, `type`, `at`, `mail`,
    `title`, `content`, `board`, `boardName`, `category`, `rate`, `staffOnly`,
    `ip`, `isEdited`, `whoEdited`, `voteCount_Up`, `voteCount_Down`, `viewCount`, `commentCount`, `isMarkdown`, `isMedia`)
    VALUES ('$id', '$name', 'COMMON', '$now', '$mail', '$title', '$desc', '$b', '$bn', '$cat', '$rate', $sOq$sO$sOq,
    '$ip', NULL, NULL, '0', '0', '0', '0', '$im', $isMedia)";

    $result = mysqli_query($conn, $sql);
    if(!$result){
        die($desc);
    }

    $sql = "SELECT `id` FROM `_board` WHERE `slug` = '$b'";
    $boardId = mysqli_query($conn, $sql);
    $boardId = mysqli_fetch_assoc($boardId);
    $boardId = $boardId['id'];

    $sql = "SELECT `num` FROM `_content` WHERE `at` = '$now' and `id` = '$id'";
    $outcome = mysqli_query($conn, $sql);
    $outcome = mysqli_fetch_assoc($outcome);

    $sql = "UPDATE `_account` SET `point` = `point` + 10 WHERE `id` = '$id'";
    $result = mysqli_query($conn, $sql);
    if(!$result){
        die($sql);
    }else{
        $sql = "UPDATE `_account` SET `point` = `point` + 1 WHERE `id` = '$boardId'";
        $result = mysqli_query($conn, $sql);
    }

    if($outcome){
        $n = $outcome['num'];

        $sql = "SELECT Count(*) as `cnt` FROM `_content` WHERE `ip` = '$ip' and `at` > DATE_SUB(NOW(), INTERVAL 30 SECOND)";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if($row['cnt'] >= 5){
            $link = '/b>recent>'.$n;
            $sql = "INSERT INTO `_ment` (`id`, `name`, `type`, `value`, `target`, `reason`, `ip`, `isSuccess`)
            VALUES ('__AUTO', '시스템 경고', 'NOFI_MENTN', '$link', 'admin', '게시글 도배', '127.0.0.1', '0')";
            $result = mysqli_query($conn, $sql);
            $sql = "SELECT Count(*) as `cnt` FROM `_content` WHERE `id` = '$id' and `at` > DATE_SUB(NOW(), INTERVAL 60 SECOND)";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            if($row['cnt'] >= 12){
                $sql = "UPDATE `_account` SET `siteBan` = '1' WHERE `id` = '$id';";
                $result = mysqli_query($conn, $sql);
                die('<script>alert("귀하께서는 도배로 인하여 광역차단 되셨습니다.");location.href = \'/\'</script>');
            }else{
                die('<script>alert("게시글 작성 빈도가 너무 짧습니다.");history.back()</script>');
            }
        }

        if($b == 'quiz'){
            if($r['point'] > $prz){
                if(!empty($ans) and $ans != '0'){
                    $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `at`, `value`, `target`, `reason`, `isSuccess`)
                    VALUES ('$id', '$name', 'QUIZ_QUEST', '$now', '$ans', '$n', '$prz', '0')";
                    $result = mysqli_query($conn, $sql);
                }
            }
        }

        $sql = "SELECT `type`, `rct` FROM `_board` WHERE `slug` = '$b'";
        $boardType = mysqli_query($conn, $sql);
        $boardType = mysqli_fetch_assoc($boardType);
        if($boardType['type'] == 'OWNER_ONLY'){
            $sql = "UPDATE `_content` SET `hideMain` = 1 WHERE `num` = '$n'";
            $result = mysqli_query($conn, $sql);
        }elseif($boardType['rct'] == 0){
            $sql = "UPDATE `_content` SET `hideMain` = 1 WHERE `num` = '$n'";
            $result = mysqli_query($conn, $sql);
        }

        //호출 처리
        preg_match_all('/@[^\s\n<>]+/', $desc, $out_arr);
        $i = 0;
        foreach( $out_arr['0'] as $value ){
            $mnt_name = str_replace('@', '', $value);
            $sql = "SELECT `id` from `_account` WHERE `name` = '$mnt_name'";
            $result = mysqli_query($conn, $sql);
            $mnt_id = mysqli_fetch_assoc($result);
            if(mysqli_num_rows($result) == 1){
                $desc = preg_replace('/'.$value.'/', '<a href="/u/'.$mnt_id['id'].'">'.$value.'</a>', $desc); #내용에서 변경
                $mid = $mnt_id['id'];
                if($id !== $mid){ #호출 반영
                    $sql = "INSERT INTO `_ment` (`id`, `name`, `type`, `value`, `target`, `cmt_id`, `reason`, `ip`, `isSuccess`)
                    VALUES ('$id', '$name', 'NOFI_MENTN', '/b/$b/$n', '$mid', '', '$title', '$ip', '0')";
                    $result = mysqli_query($conn, $sql);
                }

                $i++;
                if($i > 40){ #안전장치
                    break;
                }
            }
        }
        $sql = "UPDATE `_content` SET `content` = '$desc' WHERE `num` = '$n'"; #멘션으로 변경된 내용 반영
        $result = mysqli_query($conn, $sql);

        die('<script>location.href = "/b/'.$b.'/'.$n.'"</script>');
    }else{
        die('데이터베이스 연결 오류');
    }
}
?>
