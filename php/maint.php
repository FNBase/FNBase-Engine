<?php
    include '../setting.php';
    require_once '../editor/htmlpurifier/library/HTMLPurifier.auto.php';

    // 기본 문자열 필터링 함수
    function filt($arg, $opt){
            $purifier = new HTMLPurifier();
            $val = $purifier->purify($arg);
            if($val == ''){
                $val = 'NULL';
            }else{
                $val = str_ireplace('"', "'", $val);
                $val = "\"$val\"";
            }
        return $val;
    }

    $t = filt($_POST['t'], 'oth'); #이름
    $n = filt($_POST['nn'], 'oth'); #별명
    $i = filt($_POST['i'], 'oth'); #설명
    $r = filt($_POST['r'], 'oth'); #연관 게시판
    $nt = filt($_POST['nt'], 'oth'); #상단 공지
    $k = filt($_POST['k'], 'oth'); #보조 관리인
    $e = filt($_POST['e'], 'oth'); #이모티콘
    $ty = filt($_POST['ty'], 'oth'); #게시판 타입
    $ts = filt($_POST['tS'], 'oth'); #게시판 타입
    $rct = filt($_POST['rct'], 'oth'); #노출 여부

    $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
    $result = mysqli_query($conn, $sql);
    $iA = mysqli_fetch_assoc($result);
    if($iA['isAdmin']){
        $iA = TRUE;
    }
    else $iA = FALSE;

    if($ty == 'DIRECT_OPT' && $iA == FALSE){
        die('권한이 없습니다.');
    }

    $sql = "SELECT `id`, `num`, `slug` FROM `_board` WHERE `title` = $t";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $nm = $row['num'];
    if($_SESSION['fnUserId'] == $row['id']){
        $sql = "UPDATE `_board` SET `nickTitle` = $n, `boardIntro` = $i, `related` = $r
       , `notice` = $nt, `keeper` = $k, `icon` = $e, `type` = $ty, `rct` = $rct, `tagSet` = $ts WHERE `num` = '$nm'";
        $result = mysqli_query($conn, $sql);
        if($result){
            $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `at`, `value`, `target`, `reason`, `ip`, `isSuccess`, `tagSet`)
            VALUES ('$id', '$name', 'AUDIT_LOG', CURRENT_TIMESTAMP(), 'maint', '$t', '별명: $n | 설명: $i<br> 연관 게시판: $r<br> 상단 공지: $nt<br>보조 관리인: $k | 타입: $ty', '$ip', '1')";
            $result = mysqli_query($conn, $sql);
            die('<script>window.location.href = "../b>'.$row['slug'].'"</script>');
        }
    }
    echo $sql;
?>
