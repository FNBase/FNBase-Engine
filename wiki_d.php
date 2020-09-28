<?php
    $fnMultiNum = 2;
    include_once 'setting.php';
    include_once 'func.php';
    $fnwTitle = filt(urldecode($_GET['title']), 'htm');
    include_once 'wiki_p.php';
    $fnwTitle = myUrlDecode($fnwTitle);
    if(empty($fnwTitle) and $fnwTitle != '0'){
        die('대상 문서 값이 비어있습니다.');
    }

    if(empty($id) and $id != '0'){
        die('토론은 익명 작성이 불가능합니다.');
    }

    if($document['ACL'] == 'none'){
        die('<strong><red>주의!</red> 특수 문서에서는 토론을 하실 수 없습니다.</strong><br>
        기술적 자문이나 기타 문의사항은 <a href="/b>maint">운영실에서 해주세요.</a>');
    }

            //ACL
            $sqla = "SELECT `isAdmin` FROM `_account` WHERE `id` = '$id'";
            $result = mysqli_query($conn, $sqla);
            $iA = mysqli_fetch_assoc($result);
            $iA = $iA['isAdmin'];

            if($document['ACL'] === 'all'){
                if($id){
                    $canEdit = TRUE;
                }
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

    if($_GET['mode'] == 'discuss'){
        if($canEdit){
            if($_SESSION['fnUserId']){
                $id = $_SESSION['fnUserId'];
                $name = $_SESSION['fnUserName'];
            }else{
                $id = $ip;
                $name = $ip;
            }

            $now = date('Y-m-d H:i:s');
            $discussName = filt($_POST['name'], 'htm');
            $content = filt($_POST['content'], 'oth');

            $sql = "INSERT INTO `_discuss` (`at`, `title`, `discussName`, `id`, `status`) VALUES ('$now', '$fnwTitle', '$discussName', '$id', 'ACTIVE')";
            $result = mysqli_query($conn, $sql);
            $sql = "SELECT `num` FROM `_discuss` WHERE `at` = '$now' and `id` = '$id' and `title` = '$fnwTitle'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            $origin = $row['num'];

                        //호출 처리
                        preg_match_all('/@[^\s\n<>]+/', $content, $out_arr);
                        $i = 0;
                        foreach( $out_arr['0'] as $value ){
                            $mnt_name = str_replace('@', '', $value);
                            $sql = "SELECT `id` from `_account` WHERE `name` = '$mnt_name'";
                            $result = mysqli_query($conn, $sql);
                            $mnt_id = mysqli_fetch_assoc($result);
                            if(mysqli_num_rows($result) == 1){
                                $content = preg_replace('/'.$value.'/', '<a href="/u/'.$mnt_id['id'].'">'.$value.'</a>', $content); #내용에서 변경
                                $mid = $mnt_id['id'];
                                if($id !== $mid){ #호출 반영
                                    $sql = "INSERT INTO `_ment` (`id`, `name`, `type`, `value`, `target`, `cmt_id`, `reason`, `ip`, `isSuccess`)
                                    VALUES ('$id', '$name', 'WIKI_MENTN', 'discuss/$origin', '$mid', '', '', '$ip', '0')";
                                    $result = mysqli_query($conn, $sql);
                                }

                                $i++;
                                if($i > 20){ #안전장치
                                    break;
                                }
                            }
                        }

            $sql = "INSERT INTO `_discussThread` (`origin`, `id`, `name`, `content`, `status`) VALUES ('$origin', '$id', '$name', '$content', 'ACTIVE')";
            $result = mysqli_query($conn, $sql);
        }
        die('<script>location.href = "/discuss/'.$origin.'#discussBtm"</script>');
    }elseif($_GET['mode'] == 'speak'){
        if($canEdit || $id){
            if($_SESSION['fnUserId']){
                $id = $_SESSION['fnUserId'];
                $name = $_SESSION['fnUserName'];
            }else{
                $id = $ip;
                $name = $ip;
            }

            $now = date('Y-m-d H:i:s');
            $num = filt($_POST['num'], '123');
            $content = filt($_POST['content'], 'oth');

            $sql = "SELECT * FROM `_discuss` WHERE `num` = '$num'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            if($row['status'] != 'ACTIVE'){
                die('<script>alert("이 토론은 활성 상태가 아닙니다.");history.back()</script>');
            }

                    //호출 처리
                    preg_match_all('/@[^\s\n<>]+/', $content, $out_arr);
                    $i = 0;
                    foreach( $out_arr['0'] as $value ){
                        $mnt_name = str_replace('@', '', $value);
                        $sql = "SELECT `id` from `_account` WHERE `name` = '$mnt_name'";
                        $result = mysqli_query($conn, $sql);
                        $mnt_id = mysqli_fetch_assoc($result);
                        if(mysqli_num_rows($result) == 1){
                            $content = preg_replace('/'.$value.'/', '<a href="/u/'.$mnt_id['id'].'">'.$value.'</a>', $content); #내용에서 변경
                            $mid = $mnt_id['id'];
                            if($id !== $mid){ #호출 반영
                                $sql = "INSERT INTO `_ment` (`id`, `name`, `type`, `value`, `target`, `cmt_id`, `reason`, `ip`, `isSuccess`)
                                VALUES ('$id', '$name', 'WIKI_MENTN', 'discuss/$num', '$mid', '', '', '$ip', '0')";
                                $result = mysqli_query($conn, $sql);
                            }

                            $i++;
                            if($i > 20){ #안전장치
                                break;
                            }
                        }
                    }

            $sql = "INSERT INTO `_discussThread` (`origin`, `id`, `name`, `content`, `status`) VALUES ('$num', '$id', '$name', '$content', 'ACTIVE')";
            $result = mysqli_query($conn, $sql);

            $sql = "UPDATE `_discuss` SET `lastEdit` = NOW() WHERE `num` = '$num'";
            $result = mysqli_query($conn, $sql);
        }
        die('<script>location.href = "/discuss/'.$num.'#discussBtm"</script>');
    }

    if(!$id){
        $wfWarn = documentRender('<strong><red>주의!</red> 비로그인 상태로 참여할 시 귀하의 아이피 주소(___ADDRESS___)가 영구히 기록됩니다!</strong>', true);
    }

    if($canEdit){
        $newDiscuss = '<form action="/wiki_d.php?mode=discuss&title='.$fnwTitle.'" method="post"><h3>새 토론 주제 제출</h3>';
        $newDiscuss .= '<hr><input type="text" name="name" placeholder="제목" style="border:0;" maxlength="100" required>';
        $newDiscuss .= '<hr><textarea name="content" placeholder="내용 (1000자 이내 작성)" style="min-height:5em;border:0;" maxlength="1000" required></textarea><hr>';
        $newDiscuss .= '<input type="hidden" name="title" value="'.$fnwTitle.'">';
        $newDiscuss .= '<button type="submit" class="full success">토론 제출</button>';
        $newDiscuss .= '<span class="subInfo">반드시 해당 문서 편집과 관련된 주제여야만 합니다!<br>관련 없는 토론은 삭제되거나 변경될 수 있습니다.</span>'.'<br>'.$wfWarn;
        $newDiscuss .= '</form>';
    }else{
        $newDiscuss = '<span class="subInfo">이 문서에서 새 토론을 만들 권한이 없습니다.<br>높은 확률로 로그인을 하지 않았기 때문입니다. 또는 보호된 문서이거나, 특수 문서입니다.</span>';
    }

//토론 불러오기
    $sql = "SELECT * FROM `_discuss` WHERE `title` = '$fnwTitle' and `status` IN ('ACTIVE', 'PAUSE') ORDER BY `status`";
    $discuss = mysqli_query($conn, $sql);
    if(mysqli_num_rows($discuss) < 1){
        unset($discuss);
        $discuss['discussTitle'] = '<span class="muted">'.$fnwTitle.'</span>';
        $wdPlus = '문서에 대해 논의할게 있으시다면 토론을 직접 열어보세요!';
        $count = 0;
        die('<div class="card" class="wikiDiscussCard">
            <header style="background:#f3f3f3;border-bottom:1px solid #e6e6e6">
                <h4><i class="icofont-page"></i> '.$discuss['discussTitle'].' <green>[ '.$count.' ]</green></h4>
            </header>
            <section>
                '.$wdPlus.'
            </section>
            <footer>
                '.$newDiscuss.'
            </footer>
        </div>');
    }

    $sql = "SELECT count(*) as `cnt` FROM `_discuss` WHERE `title` = '$fnwTitle' and `status` IN ('ACTIVE', 'PAUSE') ORDER BY `lastEdit` LIMIT 50";
    $result = mysqli_query($conn, $sql);
    $count = mysqli_fetch_assoc($result);
    $_SESSION['tempWTitle'] = $fnwTitle;
    echo '<div class="card" class="wikiDiscussCard">
            <header style="background:#f3f3f3;border-bottom:1px solid #e6e6e6">
                <h4><i class="icofont-page"></i> '.$fnwTitle.' <green>[ '.$count['cnt'].' ]</green></h4>
            </header>
            <section>';
            while($row = mysqli_fetch_assoc($discuss)){
                $sql = "SELECT count(*) as `cnt` FROM `_discussThread` WHERE `origin` = '".$row['num']."'";
                $result = mysqli_query($conn, $sql);
                $count = mysqli_fetch_assoc($result);

                if($row['status'] == 'ACTIVE'){
                    $dC = 'green';
                    $dT = '#fff';

                    $isActive = TRUE;
                }elseif($row['status'] == 'PAUSE'){
                    $dC = 'orange';
                    $dT = '#000';
                }else{
                    $dC = 'black';
                    $dT = '#fff';
                }

                echo '<div class="card" style="border: 2px dashed gainsboro" class="wikiDiscussCard">
                    <header style="background:'.$dC.';color:'.$dT.';border-bottom:1px solid #e6e6e6">
                        <h4><i class="icofont-users-alt-2"></i> <a style="color:'.$dT.'" href="/discuss/'.$row['num'].'">'.$row['discussName'].'</a> ( '.$count['cnt'].' )</h4>
                    </header>
                    <section>';
                    $sql = "SELECT * FROM `_discussThread` WHERE `origin` = '".$row['num']."' ORDER BY `at` LIMIT 3";
                    $result = mysqli_query($conn, $sql);
                    $d = 1;
                    while($dThread = mysqli_fetch_assoc($result)){
                        $wE = $dThread['id'];
                        $sqln = "SELECT `name` FROM `_account` WHERE `id` = '$wE'";
                        $resultn = mysqli_query($conn, $sqln);

                        if(mysqli_num_rows($resultn) < 1){
                            $name = $wE;

                            $icon = 'invisible';
                            $href = '/misc%3EmanageCenter%3E'.$wE;
                        }else{
                            $name = mysqli_fetch_assoc($resultn);
                            $name = $name['name'];

                            $icon = 'user-alt-7';
                            $href = '/u/'.$wE;
                        }
                        $content = nl2br(documentRender($dThread['content'], 'discuss'));
                        $content = preg_replace('/<br( \/)*>\n<hr>/m', '<hr>', preg_replace('/(src="|<hr>)(.*)<br( \/)*>/m', '$1$2', preg_replace('/<\/h(\d)><br \/>/m', '</h$1>', $content)));
                        if($count['cnt'] > 3){
                            $opc = '0.'.abs($d * 3 - 12);
                        }else{
                            $opc = 1;
                        }
                        echo '<div class="card" class="wikiDiscussCard" id="discuss_'.$d.'" style="opacity:'.$opc.'">
                            <header class="black noGray" style="background:#f6f6f6;border-bottom:1px solid #e6e6e6">
                                <b>#'.$d.'</b> <span class="muted"><i class="icofont-'.$icon.'"></i> <a href="'.$href.'">'.$name.'</a></span><span class="subInfo"><h-d><br></h-d><h-m>
                                </h-m>('.$dThread['at'].')</span>
                            </header>
                            <section style="padding-bottom:.45em">
                                '.$content.'
                            </section>
                        </div>';
                        $d++;
                    }
                    $d--;
                    echo '<div id="wd_'.$row['num'].'"></div>';
                        if($count['cnt'] > $d){
                            echo '<button class="outline-blue full" onclick="location.href = \'/discuss/'.$row['num'].'\'">모두 보기</button><br>';
                        }
                        if($_SESSION['fnUserName']){
                            $icon = 'user-alt-7';
                            $name = $_SESSION['fnUserName'];
                        }else{
                            $icon = 'invisible';
                            $name = $ip;
                        }
                    echo '</section>
                </div>';
            }
            echo '</section>
            <footer>
                '.$newDiscuss.'
            </footer>
        </div>';
?>
