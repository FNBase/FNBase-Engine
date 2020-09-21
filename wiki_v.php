<?php
    $fnMultiNum = 2;
    include_once 'setting.php';
    include_once 'func.php';

    $fnwTitle = filt(urldecode($_GET['title']), 'htm');
    require_once 'wiki_p.php';
    if(empty($fnwTitle) and $fnwTitle != '0'){
        $fnwTitle = '대문';
    }

    //ACL
    $sqla = "SELECT `isAdmin` FROM `_account` WHERE `id` = '$id'";
    $resulta = mysqli_query($conn, $sqla);
    $iA = mysqli_fetch_assoc($resulta);
    $iA = $iA['isAdmin'];

    if($_GET['discuss']){
        if ($iA == 0) die("권한 부족!");
        $num = filt($_POST['num'], '123');
        if($_GET['discuss'] == 'pause'){ //PAUSE
            $sql = "INSERT INTO `_discussThread` (`origin`, `id`, `name`, `content`, `status`) VALUES ('$num', '$id', '$name', '<span style=\"color:darkorange\">토론을 비활성화함.</span>', 'ACTIVE')";
            $result = mysqli_query($conn, $sql);
            
            $sql = "UPDATE `_discuss` SET `status` = 'PAUSE' WHERE `num` = '$num'";
        }elseif($_GET['discuss'] == 'play'){ //ACTIVE
            $sql = "INSERT INTO `_discussThread` (`origin`, `id`, `name`, `content`, `status`) VALUES ('$num', '$id', '$name', '<span style=\"color:green\">토론을 다시 활성화함.</span>', 'ACTIVE')";
            $result = mysqli_query($conn, $sql);
            
            $sql = "UPDATE `_discuss` SET `status` = 'ACTIVE' WHERE `num` = '$num'";
        }else{ //DISABLED
            $sql = "INSERT INTO `_discussThread` (`origin`, `id`, `name`, `content`, `status`) VALUES ('$num', '$id', '$name', '<span style=\"text-shadow: 0 0 4px black;\">토론을 숨김.</span>', 'ACTIVE')";
            $result = mysqli_query($conn, $sql);
            
            $sql = "UPDATE `_discuss` SET `status` = 'DISABLED' WHERE `num` = '$num'";
        }
        $result = mysqli_query($conn, $sql);
        if($result){
            die('<script>alert("요청하신 동작을 수행했습니다.");history.go(-1)</script>');
        }
    }

    if($_GET['mode'] != ''){
        $sql = "SELECT * FROM `_article` WHERE `title` = '$fnwTitle'";
        $result = mysqli_query($conn, $sql);
        $document = mysqli_fetch_assoc($result);
    
            $num = filt($_GET['num'], '123');
            $sqla = "SELECT `rev` FROM `_history` WHERE `num` = '$num'";
            $resulta = mysqli_query($conn, $sqla);
            $rev = mysqli_fetch_assoc($resulta);
            $content = filt($rev['rev'], 'oth');
    
            if($document['ACL'] === 'all'){
                $canEdit = TRUE;
            }elseif($document['ACL'] == 'none'){
                $canEdit = FALSE;
            }elseif($document['ACL'] == 'user' || $document['ACL'] == NULL){
                if($id){
                    $canEdit = TRUE;
                }else{
                    $canEdit = FALSE;
                }
            }else{
                $canEdit = FALSE;
            }
    
            if(!$iA){
                $canManage = FALSE;
            }else{
                $canEdit = TRUE;
                $canManage = TRUE;
            }
    
        if($_GET['mode'] == 'rollback'){
            if($canEdit){
                if($_SESSION['fnUserId']){
                    $id = $_SESSION['fnUserId'];
                    $name = $_SESSION['fnUserName'];
                    $isAnon = 0;
                }else{
                    $id = $ip;
                    $name = $ip;
                    $isAnon = 1;
                }

                    $sql = "INSERT INTO `_history` (`title`, `id`, `name`, `rev`, `at`) VALUES ('$fnwTitle', '$id', '$name', '$content', NOW())";
                    $result = mysqli_query($conn, $sql);
                    $sql = "UPDATE `_article` SET `content` = '$content', `lastEdit` = NOW(), `whoEdited` = '$id' WHERE `title` = '$fnwTitle'";
                    $result = mysqli_query($conn, $sql);

                    die('#'.$num.' 으로 되돌리는데 성공했습니다.');
            }else{
                die('권한 부족');
            }
        }
    }

    $fnwTitle = documentRender($fnwTitle, TRUE);
    $sql = "SELECT * FROM `_article` WHERE `title` = '$fnwTitle'";
    $document = mysqli_query($conn, $sql);
    $document = mysqli_fetch_assoc($document);

    $content = nl2br(documentRender(str_ireplace("\\'", "'", filt($_REQUEST['content'], 'oth'))));
    die(preg_replace('/<br( \/)*>\n<hr>/m', '<hr>', preg_replace('/(src="|<hr>)(.*)<br( \/)*>/m', '$1$2', preg_replace('/<\/h(\d)><br \/>/m', '</h$1>', $content))));
?>
