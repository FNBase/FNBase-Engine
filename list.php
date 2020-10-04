<?php
    $name = $_SESSION['fnUserName'];
    if(!$name){
        $name = '익명';
    }
    $sql = "SELECT * FROM `_board` WHERE `slug` = '$lsBoard';"; #게시판 설정 로드
    $lsResult = mysqli_query($conn, $sql);
    if($lsResult === FALSE){
        die('데이터베이스 오류');
    }
    if(mysqli_num_rows($lsResult) == 1){
        $lsBoard = mysqli_fetch_assoc($lsResult);
    }else{
        die('게시판 아이디 값이 바르지 않습니다.');
    }

    $board = $lsBoard['slug']; #변수에 값 넣기
    $ownerId = $lsBoard['id'];
    $ownerName = $lsBoard['name'];
    $boardName = $lsBoard['title'];
    $boardNick = $lsBoard['nickTitle'];
    $boardIntro = $lsBoard['boardIntro'];
    $subs = $lsBoard['subs'];
    $tags = $lsBoard['tagSet'];
    $kpr = $lsBoard['keeper'];
    $kcd = $lsBoard['kicked'];
    $relate = $lsBoard['related'];
    $boardType = $lsBoard['type'];
    $alNotice = $lsBoard['notice'];
    $boardOpt = $lsBoard['option'];

    $notin = "NOT IN ('trash', 'relaynovel', 'kkutu', 'arrow')";

    echo '<script>document.title = "'.$lsBoard['title'].'";isTitCh = true</script>'; #제목 변경

    if($_SESSION['fnUserId'] === $ownerId){ #환경 설정 권한 여부
        $lsTemp = '<a class="button error" href="/b>'.$board.'>maint"><i class="icofont-settings"></i><h-m> 환경 설정</h-m></a>&nbsp;';
        $isOwner = TRUE;
        $isStaff = TRUE;
    }elseif(mb_strpos($kpr, $_SESSION['fnUserId']) !== FALSE){
        $isStaff = TRUE;
    }

    if(!empty($lsBoard['icon']) and $lsBoard['icon'] != '0'){
        $boardIcon = '<i class="icofont-'.$lsBoard['icon'].'"></i> ';
    }

    switch ($boardType) { #게시판 종류
        case 'AUTO_GENER':
            $boardType = '자동';
            $boardTypeStyle = 'class="label" style="background:gray"';
            $isAG = TRUE;
            break;
        case 'DIRECT_OPT':
            $boardType = '공설';
            $boardTypeStyle = 'class="label"';
            break;
        case 'PRIVAT_OPT':
            $boardType = '사설';
            $boardTypeStyle = 'class="label" style="background:whitesmoke;color:black"';
            break;
        case 'CREAT_SOME':
            $boardType = '창작';
            $boardTypeStyle = 'class="label" style="background:purple;color:white"';
            break;
        case 'OWNER_ONLY':
            $boardType = '개인';
            $boardTypeStyle = 'class="label" style="background:green;color:white"';
            if(!$isOwner){
                $isAG = TRUE;
            }
            $isOW = TRUE;
            break;
        case '_READ_ONLY':
            $boardType = '읽기전용';
            $boardTypeStyle = 'class="label warning"';
            break;
        case '__DISABLED':
            $boardType = '비활성화';
            $boardTypeStyle = 'class="label dangerous"';
            break;
    }

    if(!empty($uS['display_none']) and $uS['display_none'] != '0'){
        $sad = "and `id` NOT IN (".$uS['display_none'].")";
    }else{
        $sad = NULL;
    }

    if(!empty($uS['listNum']) and $uS['listNum'] != '0'){ #1페이지에 표시될 글 수
        $lsPgN = $uS['listNum'];
    }else{
        $lsPgN = 15;
    }

    if(empty($_GET['p']) and $_GET['p'] != '0'){
        $limit = '0, '.$lsPgN;
        $lsPg = 1;
    }else{
        $lsPg = filt($_GET['p'], '123');
        $b = $lsPg * $lsPgN - $lsPgN;
        $limit = $b.', '.$lsPgN;
    }

    $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
    $result = mysqli_query($conn, $sql);
    $iA = mysqli_fetch_assoc($result);
        if($iA['isAdmin']){
            $isAdmin = TRUE;
        }

    if($lsBoard['type'] == 'AUTO_GENER'){ #글 정렬
        switch ($board) {
            case 'HOF':
                $sql = "SELECT * FROM `_content` WHERE `voteCount_UP` - `voteCount_Down` > 19 and `viewCount` > 499 and `commentCount` > 49 ORDER BY `num` DESC LIMIT $limit";
                break;
            case 'trash':
                $sql = "SELECT * FROM `_content` WHERE `type` IN ('COMMON', 'ANON_WRITE') and `board` = 'trash' $sad ORDER BY `num` DESC LIMIT $limit";
                break;
            case 'guide':
                $sql = "SELECT * FROM `_content` WHERE `board` = 'guide' ORDER BY `num` DESC LIMIT $limit";
                break;
            case 'whole':
                $sql = "SELECT * FROM `_content` WHERE `type` IN ('COMMON', 'ANON_WRITE') and `board` NOT IN ('trash') $sad ORDER BY `num` DESC LIMIT $limit";
                break;
            case 'fresh':
                $sql = "SELECT * FROM `_content` WHERE `actmeter` not like `at` and `actmeter` is not null ORDER BY `actmeter` DESC LIMIT $limit";
                break;
            
            default:
                $sql = "SELECT * FROM `_content` WHERE `hideMain` IS NULL $sad AND `board` IN (SELECT `slug` FROM `_board` WHERE `type` IN ('DIRECT_OPT', 'PRIVAT_OPT')) ORDER BY `num` DESC LIMIT $limit";
                break;
        }
    }else{
        $sql = "SELECT * FROM `_content` WHERE `board` = '$board' and `type` IN ('COMMON', 'ANON_WRITE') $sad ORDER BY `num` DESC LIMIT $limit";
    }

    $listResult = mysqli_query($conn, $sql);
    if(mysqli_num_rows($listResult) > 0){
        $isEmpty = FALSE;
        if($alNotice !== NULL){
            $idAlert = 'notice';
        }
    }else{
        $isEmpty = TRUE;
        $idAlert = 'empty';
    }

    //검색
    if(!empty($_GET['q']) and $_GET['q'] != '0'){
        $qS = filt($_GET['q'], 'oth');
        $qM = filt($_GET['qm'], 'oth');
        $qP = filt($_GET['qp'], 'oth');
        $qR = filt($_GET['qr'], 'oth');

        if(empty($qP) and $qP != '0'){
            $qP = 1;
        }
        $l = $qP * 10;
        $lc = $l - 10;
        $l = $lc.', 10';

        if(empty($qR) and $qR != '0'){
            $ps = '%';
        }else{
            $ps = '';
            $qr = ' selected';
        }

        if($board == 'recent' or $board == 'whole'){
            $sbn = '통합';
        }else{
            $sbn = $boardName;
            $spsp = "AND `board` = '$board'";
        }

        switch ($qM) {
            case 'title':
                $sql_ = "SELECT `num` FROM `_content` WHERE `title` like '$ps$qS$ps' AND (`staffOnly` IS NULL OR `staffOnly` like '$ps$name$ps' OR `id` = '$id') $spsp";
                $sql = "SELECT * FROM `_content` WHERE `title` like '$ps$qS$ps' AND (`staffOnly` IS NULL OR `staffOnly` like '$ps$name$ps' OR `id` = '$id') $spsp ORDER BY `at` DESC LIMIT $l";
                $qT = '(제목)';
                break;
            case 'content':
                $sql_ = "SELECT `num` FROM `_content` WHERE `content` like '$ps$qS$ps' AND (`staffOnly` IS NULL OR `staffOnly` like '$ps$name$ps' OR `id` = '$id') $spsp";
                $sql = "SELECT * FROM `_content` WHERE `content` like '$ps$qS$ps' AND (`staffOnly` IS NULL OR `staffOnly` like '$ps$name$ps' OR `id` = '$id') $spsp ORDER BY `at` DESC LIMIT $l";
                $qT = '(내용)';
                break;
            case 'author':
                $sql_ = "SELECT `num` FROM `_content` WHERE `name` like '$ps$qS$ps' AND (`staffOnly` IS NULL OR `staffOnly` like '$ps$name$ps' OR `id` = '$id') $spsp";
                $sql = "SELECT * FROM `_content` WHERE `name` like '$ps$qS$ps' AND (`staffOnly` IS NULL OR `staffOnly` like '$ps$name$ps' OR `id` = '$id') $spsp ORDER BY `at` DESC LIMIT $l";
                $qT = '(글쓴이)';
                break;
            case 'comment':
                $sql_ = "SELECT s.`num`
                FROM `_content` AS c
                RIGHT JOIN `_comment` AS s
                ON c.num = s.from
                WHERE s.`content` like '$ps$qS$ps' AND (`staffOnly` IS NULL OR `staffOnly` like '$ps$name$ps')";
                $sql = "SELECT c.*, s.num AS cn, s.content AS cc
                FROM `_content` AS c
                RIGHT JOIN `_comment` AS s
                ON c.num = s.from
                WHERE s.`content` like '$ps$qS$ps' AND (`staffOnly` IS NULL OR `staffOnly` like '$ps$name$ps') ORDER BY `at` DESC LIMIT $l";
                $qPlus = '<span class="subInfo">댓글 검색은 내용 일치 여부만 봅니다.'.$sql_.'</span><br>';
                $qT = '(댓글)';
                break;
            
            default:
                $sql_ = "SELECT `num` FROM `_content` WHERE (`title` like '$ps$qS$ps' OR `content` like '$ps$qS$ps') AND (`staffOnly` IS NULL OR `staffOnly` like '$ps$name$ps' OR `id` = '$id') $spsp";
                $sql = "SELECT * FROM `_content` WHERE (`title` like '$ps$qS$ps' OR `content` like '$ps$qS$ps') AND (`staffOnly` IS NULL OR `staffOnly` like '$ps$name$ps' OR `id` = '$id') $spsp ORDER BY `at` DESC LIMIT $l";
                $qM = 'both';
                break;
        }
        $result = mysqli_query($conn, $sql_);
        $qC = mysqli_num_rows($result);
        $qR = ' 결과 - '.$qC.'건 '.$qT;

        $result = mysqli_query($conn, $sql);

        $lsSearch = '<article class="card">
        <header>
        <h3 class="muted"><i class="icofont-search-document"></i> '.$sbn.' 검색'.$qR.'</h3>
        </header>
            <section class="content">';
            if(mysqli_num_rows($result) < 1){
                $lsSearch .= '<span class="muted">검색 결과가 없습니다..</span>';
            }else{
                $lsSearch .= '<table class="list full">
                <thead>
                    <tr>
                        <th width="10%" class="hidMob">&nbsp;종류</th>
                        <th width="50%">&nbsp;제목</th>
                        <th width="40%">&nbsp;정보</th>
                    </tr>
                </thead>
                <tbody>';
                if($qM !== 'comment'){
                    while($row = mysqli_fetch_assoc($result)){ #일반 글 조회
                        $time = get_timeFlies($row['at']);

                        $lsSearch .= '<tr>';
                        $lsSearch .= '<td class="hidMob muted">'.$row['category'].'</td>';
                        $lsSearch .= '<td class="black"><a href="/b/'.$row['board'].'/'.$row['num'].'-'.$lsPg.'">'.textalter($row['title'], 3).'</a>
                        <green class="little">['.$row['commentCount'].']</green>';
                        if(!empty($row['staffOnly']) and $row['staffOnly'] != '0'){
                            $lsSearch .= '<i class="icofont-lock"></i>';
                        }
                        $lsSearch .= '</td>';
                        $lsSearch .= '<td class="infoList"><i class="icofont-user-alt-7"></i>
                        <a class="muted" href="/u/'.$row['id'].'_'.$board.'">'.$row['name'].'</a><h-d><br></h-d>';
                        $lsSearch .= ' <i class="icofont-clock-time"></i> '.$time.'<h-m> <green>'.$row['viewCount'].'</green></h-m></td>';
                        $lsSearch .= '</tr>';
                    }
                }else{
                    while($row = mysqli_fetch_assoc($result)){ #댓글 조회
                        $time = get_timeFlies($row['at']);

                        $lsSearch .= '<tr>';
                        $lsSearch .= '<td class="hidMob muted">'.$row['category'].'</td>';
                        $lsSearch .= '<td class="black"><a href="/b/'.$row['board'].'/'.$row['num'].'-'.$lsPg.'#cmt-'.$row['cn'].'">'.textalter($row['title'], 3).'</a>
                        <green class="little">['.$row['commentCount'].']</green>';
                        if(!empty($row['staffOnly']) and $row['staffOnly'] != '0'){
                            $lsSearch .= '<i class="icofont-lock"></i>';
                        }
                        if(strlen($row['cc']) > 50){
                            $row['cc'] = mb_substr($row['cc'], 0, 47).'...';
                        }
                        $lsSearch .= '<br><span class="subInfo">'.$row['cc'].'</span>';
                        $lsSearch .= '</td>';
                        $lsSearch .= '<td class="infoList"><i class="icofont-user-alt-7"></i>
                        <a class="muted" href="/u/'.$row['id'].'_'.$board.'">'.$row['name'].'</a><h-d><br></h-d>';
                        $lsSearch .= ' <i class="icofont-clock-time"></i> '.$time.'</td>';
                        $lsSearch .= '</tr>';
                    }
                }
                $lsSearch .= '</tbody></table><br>'.$qPlus;
                $lsSearch .= '<!-- 페이지 이동 -->
                <div class="center">
                    <div class="pagination">
                        <a href="/?qm='.$qM.'&q='.$qS.'&qp=1&b='.$board.'">&laquo;</a>';

                        $cac = $qC / 10;
                        $qlast = ceil($cac);

                        $cal = $qC % 10;
                        if($cal == 0){
                            $qlast = $qlast - 1;
                        }

                        $cac2 = $qP - 2;
                        if($cac2 <= 0){
                            $qPg = 1;
                        }else{
                            $qPg = $cac2;
                        }

                        $i = 1;
                        while (1) {
                            if($qPg == $qP){
                                $lsSearch .= '<a class="active" href="/?qm='.$qM.'&q='.$qS.'&qp='.$qPg.'&b='.$board.'">'.$qPg.'</a>';
                            }else{
                                $lsSearch .= '<a href="/?qm='.$qM.'&q='.$qS.'&qp='.$qPg.'&b='.$board.'">'.$qPg.'</a>';
                            }
                            if($i == 5){
                            break;
                            }
                            if($qPg == $qlast){
                            break;
                            }
                            $qPg++;
                            $i++;
                        }

                        $lsSearch .= '<a href="/?qm='.$qM.'&q='.$qS.'&qp='.$qlast.'&b='.$board.'">&raquo;</a>';
                    $lsSearch .= '</div>
                </div>';
            }
            $lsSearch .= '
                <section class="search">
                    <hr>
                    <form action="/main" method="get">
                        <select name="qm">
                            <option value="both">제목/내용</option>
                            <option value="title">제목</option>
                            <option value="content">내용</option>
                            <option value="author">글쓴이</option>
                            <option value="comment">댓글</option>
                        </select>
                        <select name="qr">
                            <option value="">포함</option>
                            <option value="single"'.$qr.'>단일</option>
                        </select>
                        <input type="text" placeholder="검색어 입력" name="q" value="'.$qS.'">
                        <input type="submit" value="검색">
                        <input type="hidden" name="b" value="'.$board.'">
                    </form>
                    <hr>
                </section>
            </section>
        </article>';
    }
?>
    <main>
        <div class="flex">
        <!-- 상단 보조메뉴 -->
            <section id="mainSec" class="half listMain">
            <?php require 'alert.php'; ?>
                <hr>
                    &nbsp;<a href="/b/<?=$board?>" class="lager"><?=$boardIcon.$boardName?></a><label for="boardInfoModal" <?=$boardTypeStyle?>><?=$boardType?></label><br>
                    &nbsp;<span class="subInfo"><?=$boardIntro?>
                    <span class="right"><?=$lsTemp?><?php if($id){if(!$isAG){ #게시글 작성·수정 옵션
                    if(!$isEditor){
                    echo '<a href="/b/'.$board.'>write"
                    class="button" style="background:green"><i class="icofont-edit"></i><h-m> 글쓰기</h-m></a></span></span>';}else{echo '
                    <label for="optModal" style="float:right;background:#6633ff" class="button">
                    <i class="icofont-plus-square"></i><h-m> 부가 기능</h-m></label></span></span>';
                    echo '<!-- 글 옵션 모달 호출 -->
                    <form method="post" id="contForm" action="/save.php';
                    if($edContent){
                        echo '?e=dit">';
                        echo '<input type="hidden" name="n" value="'.$idTemp_5.'">';
                    }else{
                        echo '">';
                    }
                    echo '
                    <input type="hidden" name="b" value="'.$board.'">
                    <input type="hidden" name="bn" value="'.$boardNick.'">
                    <div class="modal">
                        <input id="optModal" type="checkbox" />
                        <label for="optModal" class="overlay"></label>
                        <article>
                            <header>
                            <h3>부가 기능</h3>
                            <label for="optModal" class="close">&times;</label>
                            </header>
                            <section class="content">
                                <label>태그 달기 ';
                                if($edContent){
                                    $dt = $edContent['category'];
                                    $dt = "<option value=\"$dt\" selected>$dt</option>";
                                }
                                    echo '<select name="category" id="catSel" class="pcwidth">
                                        '.$dt;
                                        $tagArr = array_map('trim', explode(',', $tags));
                                        foreach($tagArr as $arr){
                                            echo '<option value="'.$arr.'">'.$arr.'</option>';
                                        }
                                        if($isStaff){
                                        echo '<option value="공지">공지</option>';
                                        }
                                    echo '</select>
                                </label><br><hr>
                                <label>등급 심의
                                    <select name="rate" id="ratSel" class="pcwidth">
                                        <option value="G">전 연령에 적합</option>
                                        <option value="PG" selected>12세 관람가</option>
                                        <option value="R">15세 관람가</option>
                                    </select>
                                </label><br><hr>
                                <label>서식 적용
                                    <select name="isMd" id="ratSel" class="pcwidth">
                                        <option value="'.$edContent['isMarkdown'].'" selected>선택 안 함</option>
                                        <option value="0">미사용</option>
                                        <option value="1">Markdown</option>
                                    </select>
                                </label><br><hr>';
                                if($edContent){
                                    echo '<script>
                                        var rt = "'.$edContent['rate'].'";
                                        var sel = document.getElementById("ratSel");
                                        var selOpt = sel.options;

                                        for (var opt, j = 0; opt = selOpt[j]; j++) {
                                            if (opt.value == rt) {
                                                sel.selectedIndex = j;
                                                break;
                                            }
                                        }
                                    </script>';
                                }
                                echo '<label>열람 제한
                                    <select id="secSelect" class="pcwidth" onchange="secselect()">
                                        <option value="none" selected>사용 안 함</option>
                                        <option value="myself">나만 보기</option>
                                        <option value="select">사용자 지정</option>
                                    </select>
                                </label><br>
                                <label id="secInputLabel" style="display:none"><br>
                                    <input id="secInput" name="staffOnly" type="text" placeholder="닉네임 입력">
                                    <span class="subInfo">닉네임은 쉼표(,)로 구분하여 주세요.</span><br>
                                    <span class="subInfo">자신의 닉네임이 적혀있지 않아도 됩니다.</span>
                                </label>';
                                if($edContent){ echo '<script>
                                    var sO = "'.$edContent['staffOnly'].'";
                                    var user = "'.$_SESSION['fnUserName'].'";

                                        if(sO == ""){
                                            var public = true;
                                        }else if(sO == user){
                                            var public = false;
                                            var text = "myself";
                                            document.getElementById(\'secInput\').value = user;
                                        }else{
                                            var public = false;
                                            var text = "select";
                                            document.getElementById(\'secInput\').value = sO;
                                            document.getElementById(\'secInputLabel\').style.display = \'\';
                                        }
                                        if(!public){
                                            var sel = document.getElementById("secSelect");
                                            var selOpt = sel.options;

                                            for (var opt, j = 0; opt = selOpt[j]; j++) {
                                                if (opt.value == text) {
                                                    sel.selectedIndex = j;
                                                    break;
                                                }
                                            }
                                        }
                                </script>';
                                }
                                echo '<script>
                                    function secselect() {
                                        if(document.getElementById(\'secSelect\').value == \'select\'){
                                            document.getElementById(\'secInputLabel\').style.display = \'\';
                                        }else if(document.getElementById(\'secSelect\').value == \'myself\'){
                                            document.getElementById(\'secInput\').value = \''.$_SESSION['fnUserName'].'\';
                                        }else{
                                            document.getElementById(\'secInput\').value = null;
                                            document.getElementById(\'secInputLabel\').style.display = \'none\';
                                        }
                                    }
                                </script>
                                <hr>
                                <label for="optModal" class="button success full"><i class="icofont-check"></i> 부가 기능 적용</label><br>
                                <button class="default full" onclick="location.href=\'/b/maint/3\'" type="button"><i class="icofont-info-square"></i> 부가기능 사용법</button>
                                <span class="subInfo">부가 기능 사용 방침은 각 게시판마다 다를 수 있습니다.</span><br>
                                <span class="subInfo">열람 제한 기능을 악용하지 마십시오.</span>';
                                echo '<hr>
                                <label>에디터 변경
                                <select name="editor">
                                    <option value="">Textarea</option>
                                    <option value="s">Summernote</option>
                                    <option value="q">Quill(권장)</option>
                                </select></label>
                                <button type="submit" formaction="/save.php?yes=please" style="background:green" class="button full"><i class="icofont-edit"></i> 에디터 변경</button><br>
                                <span class="subInfo">Summernote는 글자 서식 용도로만 사용해주세요.</span><br>
                                <span class="subInfo">Textarea 선택시 글쓰기 창에서 이미지 업로드 가능. (업로드 권한 필요)</span>
                                <br>
                                <button class="half right" style="background:mint" onclick="tempSave()" type="button"><i class="icofont-diskette"></i> 현재 글 임시 저장</button>
                                <button class="half" style="background:orange" onclick="tempLoad()" type="button"><i class="icofont-ui-clip"></i> 임시 저장 불러오기</button>
                                ';
                            echo '</section>
                        </article>
                    </div>
                    <script>
                        var notSubmit = true;
                        window.onbeforeunload = function (e) {
                            if(notSubmit){
                                var message = "Are you sure ?";
                                var firefox = /Firefox[\/\s](\d+)/.test(navigator.userAgent);
                                if (firefox) {
                                    var dialog = document.createElement("div");
                                    document.body.appendChild(dialog);
                                    dialog.id = "dialog";
                                    dialog.style.visibility = "hidden";
                                    dialog.innerHTML = message; 
                                    var left = document.body.clientWidth / 2 - dialog.clientWidth / 2;
                                    dialog.style.left = left + "px";
                                    dialog.style.visibility = "visible";  
                                    var shadow = document.createElement("div");
                                    document.body.appendChild(shadow);
                                    shadow.id = "shadow";		
                                    //tip with setTimeout
                                    setTimeout(function () {
                                        document.body.removeChild(document.getElementById("dialog"));
                                        document.body.removeChild(document.getElementById("shadow"));
                                    }, 0);
                                }
                                return message;
                            }
                        }
                    </script>';
                    }
                }else{echo '</span></span>';}}elseif($board == 'uita'){echo '<a href="/misc>anonwrite"
                    class="button" style="background:green"><i class="icofont-edit"></i><h-m> 비회원 글쓰기</h-m></a></span></span>';}else{echo '</span></span>';}?>
                <hr>
        <!-- 페이지 추가 로드 -->
        <?php
            if(mb_strpos($kcd, $id) !== FALSE){ #채널 추방 여부
                $sql = "SELECT `at` FROM `_othFunc` WHERE `type` = 'BOARD_KICK' and `value` = '$board' and `target` = '$id' and `at` > NOW() and `isSuccess` = '1'";
                $result = mysqli_query($conn, $sql);
                if(mysqli_num_rows($result) !== 1){ #차단 기간 만료로 인한 해제
                    $sql = "UPDATE `_othFunc` SET `isSuccess` = '0' WHERE `type` = 'BOARD_KICK' and `value` = '$board' and `target` = '$id' and `at` < NOW()";
                    $result = mysqli_query($conn, $sql);
                    $s = $kcd;
                    $s = str_ireplace($id, '', $s);
                    $s = preg_replace('/[,]{2,}/m', ',', $s);
                    $s = preg_replace('/^,/', '', $s);
                    $s = preg_replace('/,$/', '', $s);
                    $sql = "UPDATE `_board` SET `kicked` = '$s' WHERE `slug` = '$board'";
                    $result = mysqli_query($conn, $sql);
                    if($result){
                        die('<script>location.reload()</script>');
                    }else{
                        die('데이터베이스 연결 실패');
                    }
                }else{
                    $row = mysqli_fetch_assoc($result);
                    echo $row['at'].'까지 차단됨! </section><aside class="hidMob"></aside></div><hr></body></html>';
                    exit;
                }
            }
            if(!empty($lsPlus) and $lsPlus != '0'){
                echo $lsPlus;
            }elseif(!empty($lsSearch) and $lsSearch != '0'){
                echo $lsSearch;
            }
            if($lsShowPg){
                require 'page.php';
            }
            $boardType = $lsBoard['type'];
            echo '<!-- 게시글 정렬 -->';
            if($board == 'quiz'){
                $isQuiz = TRUE;
            }
            if(!$isEmpty){
                    if($board){ #개별 게시판
                        echo '<table class="list full">
                        <thead>
                            <tr>
                                <th width="80%">제목/정보</th>
                                <th width="20%"><span class="right">태그</span></th>
                            </tr>
                        </thead>
                        <tbody>';

                        include 'siteNotice.php';

                        $sql = "SELECT * FROM `_content` WHERE `category` = '공지' and `board` = '$board' ORDER BY `num` DESC"; #공지 조회
                        $result = mysqli_query($conn, $sql);
                        if(mysqli_num_rows($result) != 0){
                            while($row = mysqli_fetch_assoc($result)){

                                if($row['type'] == 'COMMON'){
                                    if(mb_strpos($kpr.','.$ownerId, $row['id']) !== FALSE){
                                        $u = 'suited';
                                    }else{
                                        $u = 'alt-7';
                                    }
                                    $pgUser = '<i class="icofont-user-'.$u.'"></i> <a class="muted" href="/u/'.$row['id'].'_'.$board.'">'.$row['name'].'</a>';
                                }elseif($isAdmin){
                                    $pgUser = '<i class="icofont-invisible"></i> <a class="muted" href="/misc>manageCenter>'.$row['ip'].'">'.$row['name'].'</a>';
                                }else{
                                    $ip_s = preg_replace('/([0-9]+\.[0-9]+)\.[0-9]+\.[0-9]+/i', '$1', $row['ip']);
                                    $pgUser = '<a class="muted tooltip-bottom" data-tooltip="\''.$ip_s.'\' 가입하지 않은 사용자입니다."><i class="icofont-invisible"></i>
                                    '.$row['name'].'</a>';
                                }

                                $time = get_timeFlies($row['at']);
                                $media = NULL;
                                if($row['isMedia'] !== NULL){
                                    if($row['isMedia'] == 2){
                                        $media = ' <blue><i class="icofont-ui-video-play "></i></blue>';
                                    }elseif($row['isMedia'] == 3){
                                        $media = ' <red><i class="icofont-youtube-play"></i></red>';
                                    }else{
                                        $media = ' <blue><i class="icofont-image"></i></blue>';
                                    }
                                }
                                echo '<tr>';
                                if($row['rate'] == 'R' && $board == 'recent'){
                                    echo '<td><a class="subInfo" href="/b/'.$board.'/'.$row['num'].'-'.$lsPg.'">15세 관람가 게시물입니다.
                                    <green class="little">['.$row['commentCount'].']</green></a>';
                                }else{
                                    echo '<td class="black noGray"><b><a href="/b/'.$board.'/'.$row['num'].'">'.textalter($row['title'], 3).'</a></b>
                                    <green class="little">['.$row['commentCount'].']</green>'.$media;
                                }
                                if(!empty($row['staffOnly']) and $row['staffOnly'] != '0'){
                                    if(preg_match('/(^|,)'.$name.'($|,)/', $row['staffOnly'])){
                                        $isOpen = TRUE;
                                    }elseif($row['id'] == $id){
                                        $isOpen = TRUE;
                                    }
                                    if($isOpen){
                                        echo '<i class="icofont-unlock"></i>';
                                    }elseif($board !== 'mafia'){
                                        echo '<span class="tooltip-top" data-tooltip="'.$row['staffOnly'].'"><i class="icofont-lock"></i></span>';
                                    }else{
                                        echo '<span class="tooltip-top" data-tooltip="마피아 게임 채널에서는 기밀 대상이 가려집니다."><i class="icofont-lock"></i></span>';
                                    }
                                    $isOpen = FALSE;
                                }
                                echo '<br><span class="little">'.$pgUser.'
                                <i class="icofont-clock-time"></i> '.$time.' <blue>('.$row['viewCount'].')</blue></span></td>';
                                echo '</td>';
                                echo '<td><b class="little right">공지</b></td>';
                                echo '</tr>';
                                $pgUser = NULL;
                            }
                        }

                        while($row = mysqli_fetch_assoc($listResult)){ #일반 글 조회
                            if($isQuiz){
                                $sql = "SELECT `isSuccess` FROM `_othFunc` WHERE `target` = '".$row['num']."'";
                                $result = mysqli_query($conn, $sql);
                                $r = mysqli_fetch_assoc($result);
                                if($r['isSuccess']){
                                    $QLabel = '<span class="label success">완료</span>';
                                }elseif($r['isSuccess'] === '0'){
                                    $QLabel = '<span class="label warning">진행</span>';
                                }else{
                                    $QLabel = '<span class="label" style="background:gray">제외</span>';
                                }
                            }
                            $time = get_timeFlies($row['at']);
                            $media = NULL;
                            if($row['isMedia'] !== NULL){
                                if($row['isMedia'] == 2){
                                    $media = ' <blue><i class="icofont-ui-video-play "></i></blue>';
                                }elseif($row['isMedia'] == 3){
                                    $media = ' <red><i class="icofont-youtube-play"></i></red>';
                                }else{
                                    $media = ' <blue><i class="icofont-image"></i></blue>';
                                }
                            }

                            if($row['type'] == 'COMMON'){
                                if($_SESSION['fnUserId'] === $row['id'] or mb_strpos($kpr, $row['id']) !== FALSE){
                                    $u = 'suited';
                                }else{
                                    $u = 'alt-7';
                                }
                                $pgUser = '<i class="icofont-user-'.$u.'"></i> <a class="muted" href="/u/'.$row['id'].'_'.$board.'">'.$row['name'].'</a>';
                            }elseif($isAdmin){
                                $pgUser = '<i class="icofont-invisible"></i> <a class="muted" href="/misc>manageCenter>'.$row['ip'].'">'.$row['name'].'</a>';
                            }else{
                                $ip_s = preg_replace('/([0-9]+\.[0-9]+)\.[0-9]+\.[0-9]+/i', '$1', $row['ip']);
                                $pgUser = '<a class="muted tooltip-bottom" data-tooltip="\''.$ip_s.'\' 가입하지 않은 사용자입니다."><i class="icofont-invisible"></i>
                                '.$row['name'].'</a>';
                            }

                            echo '<tr>';
                            if($row['rate'] == 'R' && $board == 'recent'){
                                echo '<td><a class="subInfo" href="/b/'.$board.'/'.$row['num'].'-'.$lsPg.'">15세 관람가 게시물입니다.
                                <green class="little">['.$row['commentCount'].']</green></a>';
                            }else{
                                echo '<td class="black"><a href="/b/'.$board.'/'.$row['num'].'-'.$lsPg.'">'.textalter($row['title'], 3).$QLabel.'
                                <green class="little">['.$row['commentCount'].']</green></a>'.$media;
                            }
                            if(!empty($row['staffOnly']) and $row['staffOnly'] != '0'){
                                if(preg_match('/(^|,)'.$name.'($|,)/', $row['staffOnly'])){
                                    $isOpen = TRUE;
                                }elseif($row['id'] == $id){
                                    $isOpen = TRUE;
                                }
                                if($isOpen){
                                    echo '<i class="icofont-unlock"></i>';
                                }elseif($board !== 'mafia'){
                                    echo '<span class="tooltip-top" data-tooltip="'.$row['staffOnly'].'"><i class="icofont-lock"></i></span>';
                                }else{
                                    echo '<span class="tooltip-top" data-tooltip="마피아 게임 채널에서는 기밀 대상이 가려집니다."><i class="icofont-lock"></i></span>';
                                }
                                $isOpen = FALSE;
                            }
                            echo '<br><span class="little lite">'.$pgUser.'
                            <i class="icofont-clock-time"></i> '.$time.' <blue>('.$row['viewCount'].')</blue></span></td>';
                            if(!$isAG){
                                echo '<td class="black"><a href="/b/'.$row['board'].'/tags_'.$row['category'].'" class="little right">'.$row['category'].'</a></td>';
                            }else{
                                echo '<td class="black"><a href="/b/'.$row['board'].'" class="little right">'.$row['boardName'].'</a></td>';
                            }echo '</tr>';
                            $pgUser = NULL;
                        }

                    }

                    echo '</tbody>
                </table>';
?>
                <section class="search">
                    <hr>
                    <form action="/main" method="get">
                        <select name="qm">
                            <option value="both">제목/내용</option>
                            <option value="title">제목</option>
                            <option value="content">내용</option>
                            <option value="author">글쓴이</option>
                            <option value="comment">댓글</option>
                        </select>
                        <input type="text" placeholder="검색어 입력" name="q">
                        <input type="submit" value="검색">
                        <input type="hidden" name="b" value="<?=$board?>">
                    </form>
                    <hr>
                </section>
<?php
        echo '<!-- 페이지 이동 -->
                <div class="center">
                    <div class="pagination">
                        <a href="/b/'.$board.'-1">&laquo;</a>';

                        if($lsBoard['type'] == 'AUTO_GENER'){ #글 수 조회
                            switch ($board) {
                                case 'HOF':
                                    $sql = "SELECT COUNT(`num`) as `cnt` FROM `_content` WHERE `voteCount_UP` - `voteCount_Down` > 19 and `viewCount` > 499 and `commentCount` > 49";
                                    break;
                                case 'whole':
                                    $sql = "SELECT COUNT(`num`) as `cnt` FROM `_content` WHERE `board` NOT IN ('trash')";
                                    break;
                    
                                default:
                                    $sql = "SELECT COUNT(`num`) as `cnt` FROM `_content` WHERE `hideMain` IS NULL $sad AND `board` IN (SELECT `slug` FROM `_board` WHERE `type` IN ('DIRECT_OPT', 'PRIVAT_OPT'))";
                                    break;
                            }
                        }else{
                            $sql = "SELECT COUNT(`num`) as `cnt` FROM `_content` WHERE `board` = '$board'";
                        }

                        $result = mysqli_query($conn, $sql);
                        $cntCtt = mysqli_fetch_assoc($result);
                        $cnt = $cntCtt['cnt'];
                        $cnt--;

                        $lsPgEnd = 1;
                        $lsPgCacVal = $cnt / $lsPgN;
                        while($lsPgEnd < $lsPgCacVal){
                            $lsPgEnd++;
                        }

                        $lsPgStart = $lsPg - 2; #시작 페이지 값
                        if($lsPgStart < 1){
                            $lsPgStart = 1;
                        }

                        $i = 1;
                        while ($lsPgStart <= $lsPgEnd) {
                            if($lsPg == $lsPgStart){
                                echo '<a class="active" href="/b/'.$board.'-'.$lsPgStart.'">'.$lsPgStart.'</a>';
                            }else{
                                echo '<a href="/b/'.$board.'-'.$lsPgStart.'">'.$lsPgStart.'</a>';
                            }
                            if($i == 5){
                            break;
                            }
                            $lsPgStart++;
                            $i++;
                        }

                        echo '<a href="/b/'.$board.'-'.$lsPgEnd.'">&raquo;</a>';
                    echo '</div>
                </div>';
            }
                    if(!empty($boardOpt) and $boardOpt != '0'){
                        switch ($boardOpt) {
                            case 'NSTAT':
                                echo '<hr><br><hr>';
                                include 'sub/nstat.php';
                                break;
                            case 'RANDP':
                                include 'sub/randp.php';
                                break;
                        }
                    }


    //광고 / 인기 게시판 처리
    require 'adswitch.php';

    if(isMobile()){
            echo '<div class="card">
                <header>
                    광고 <a href="/adv" target="_blank" class="little right">등록하기</a>
                </header>
                <section id="advSec"><p>
                    <a href="'.$VJlink.'"><img src="'.$VJimg.'"></a>
                </p><hr><p style="font-size: 0.7em;text-align:right"><b>광고는 커뮤니티를 지탱하는 기둥입니다.</b><br>
                    이 카드가 화면을 가릴 경우,<br>
                    \'내 정보\'에서 해제하실 수 있습니다.</p>
                </section>
            </div>';
        echo '</section>
        <aside class="hidMob"><h-m>';
    }else{
            echo '</section>
            <aside class="hidMob" id="nofiSec">
                <h-m style="position:absolute;right:2em;margin:9px;opacity:0.7">';
        
        if($uS['hideAdv'] != 1 || $isLogged == FALSE){
            if(strlen($boardName) > 9){
                $brbr = '<br>';
            }
            if($boardType !== 'AUTO_GENER'){
                echo '<div class="card" style="width:300px">
                    <header>
                        '.$boardName.'의 '.$brbr.'인기 게시글
                    </header>
                    <section><table style="font-size:0.8em" class="list full">';
                $sql = "SELECT * FROM `_content` WHERE `board` = '$board' and `rate` NOT LIKE 'R' and `staffOnly` IS NULL and `voteCount_Up` > 3 ORDER BY `at` DESC LIMIT 5"; #인기글 조회
                $result = mysqli_query($conn, $sql);
                if(mysqli_num_rows($result) != 0){
                    while($row = mysqli_fetch_assoc($result)){
                        $time = get_timeFlies($row['at']);
                        echo '<tr>';
                        echo '<td><b><a href="/b/'.$row['board'].'/'.$row['num'].'" target="_blank">'.textalter($row['title'], 3).'</a></b> <green class="little">['.$row['commentCount'].']</green>';
                        echo '<br><i class="icofont-user-suited"></i>
                        <a class="muted" target="_blank" href="/u/'.$row['id'].'">'.$row['name'].'</a></td>';
                        echo '</tr>';
                    }
                }else{
                    echo '지지받는 게시글 없음..';
                }
                    echo '</table></section>
                </div>';
            }else{
                echo '<div class="card" style="width:300px">
                    <header>
                        '.$fnTitle.$brbr.' 인기 게시판
                    </header>
                    <section><table style="font-size:0.8em" class="list full">';
                $sql = "SELECT * FROM `_board` WHERE `type` NOT IN ('OWNER_ONLY', 'AUTO_GENER') ORDER BY `subs` DESC LIMIT 5"; #인기글 조회
                $result = mysqli_query($conn, $sql);
                if(mysqli_num_rows($result) != 0){
                    while($row = mysqli_fetch_assoc($result)){
                        $time = get_timeFlies($row['at']);
                        echo '<tr>';
                        echo '<td><b><a href="/b/'.$row['slug'].'">'.$row['title'].'</a>';
                        echo '<br><i class="icofont-user-suited"></i>
                        <a class="muted" href="/u/'.$row['id'].'">'.$row['name'].'</a></td>';
                        echo '</tr>';
                    }
                }
                    echo '</table></section>
                </div>';
            }
                echo '<div class="card" style="width:300px">
                    <header>
                        광고 <a href="/adv" target="_blank" class="little right">등록하기</a>
                    </header>
                    <section id="advSec"><p>
                        <a href="'.$VJlink.'"><img src="'.$VJimg.'"></a>
                    </p><hr><p style="font-size: 0.7em;text-align:right"><b>광고는 커뮤니티를 지탱하는 기둥입니다.</b><br>
                        우측 카드가 화면을 가릴 경우,<br>
                        \'내 정보\'에서 해제하실 수 있습니다.</p>
                    </section>
                </div>';
        }
    }
        // 연관게시판 관리
        if(strlen($relate) > 0){
            $relArr = array_map('trim', explode(',', $relate));
            $relBoard = '<table class="full">';
            foreach($relArr as $arr){
                $sql = "SELECT `title` FROM `_board` WHERE `slug` = '$arr'";
                $result = mysqli_query($conn, $sql);
                $result = mysqli_fetch_assoc($result);
                $relBoard .= '<tr><td><a href="/b/'.$arr.'">'.$result['title'].'</a></td></tr>';
            }
            $relBoard .= '</table>';
        }else{
            $relBoard = '<span class="subInfo">없음</span>';
        }
        if(strlen($kpr) > 0){
            $relArr = array_map('trim', explode(',', $kpr));
            foreach($relArr as $arr){
                $sql = "SELECT `name` FROM `_account` WHERE `id` = '$arr'";
                $result = mysqli_query($conn, $sql);
                $result = mysqli_fetch_assoc($result);
                $kprBoard .= '<a href="/u/'.$arr.'">@'.$result['name'].'</a> ';
            }
        }
        ?>
                </h-m>
            </aside>
        </div>
        <div class="modal">
        <input id="boardInfoModal" type="checkbox" />
        <label for="boardInfoModal" class="overlay"></label>
            <article>
                <header>
                <h3>게시판 정보</h3>
                <label for="boardInfoModal" class="close">&times;</label>
                </header>
                <section class="content">
                    소유주 : <a href="/u/<?=$ownerId?>">@<?=$ownerName?></a><br><hr>
                    <?php
                        if($kprBoard){
                            echo '관리인 : '.$kprBoard.'<br><hr>';
                        }
                    ?>
                    연관 게시판 : <?=$relBoard?><hr>
                    차단 목록 <a onclick="viewBoardKick('<?=$board?>')">보기</a>
                    <span id="viewBoardKick"></span>
                <?php
                $board = $lsBoard['slug'];
                if($isAdmin){
                    echo '<hr>채널 이양 :<br><form action="/php/mkBoard.php" method="post"><input type="text" name="tsfId" placeholder="id"><br>
                    <input type="submit" class="full" value="소유권 넘기기">
                    <input type="hidden" name="tsfSlug" value="'.$board.'"></form><hr>';
                }
                ?>
                </section>
                <?php
                if($isLogged){
                    if($board != 'recent'){
                        echo '<footer>
                        <form method="post" action="/php/sub.php">';
                        echo '<input type="hidden" name="board" value="'.$board.'">';
                        if($uS['subs'] == NULL){
                            $isSubed = FALSE;
                        }else{
                            if(preg_match('/'.$board.'/', $uS['subs'])){
                                $isSubed = TRUE;
                            }else{
                                $isSubed = FALSE;
                            }
                        }
                        if(!$isSubed){
                        echo '<button class="outline" style="width:100%">구독하기</button>';
                        }else{
                        echo '<button class="outline-blue" style="width:100%">구독 취소</button>';
                        }
                        echo '</form>
                        </footer>';
                    }
                }
                ?>
            </article>
        </div>
    </main>