<?php
    $fnMultiNum = 2;
    require_once 'setting.php';
    require_once 'func.php';

    if(!empty($id) and $id != '0'){
        $sql = "SELECT `siteBan` FROM `_account` WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if($row['siteBan'] >= 1){
            if($_GET['miscmode'] !== 'vindicate'){
                die('<script>location.href = \'/banned.php\'</script>');
            }
        }
        $sql = "SELECT `wikiColor` FROM `_userSet` WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if($row['wikiColor'] !== NULL){
            $tc = explode(',', $row['wikiColor']);
            if(!empty($tc[0])){
                $fnPColor = '#'.$tc[0];
                $fnSColor = '#'.$tc[1];
            }
        }
    }

    $fnwTitle = filt(urldecode($_GET['title']), 'htm');
    require_once 'wiki_p.php';
    if(empty($fnwTitle) and $fnwTitle != '0'){
        $fnwTitle = '대문';
    }

    $fnwTitle = documentRender($fnwTitle, TRUE);
    $sql = "SELECT * FROM `_article` WHERE `title` = '$fnwTitle'";
    $document = mysqli_query($conn, $sql);
    if(mysqli_num_rows($document) < 1){
        unset($document);
        $document['title'] = $fnwTitle;
        $document['content'] = '___404TEXT___';
        $document['execute'] = 'search';
        $isNew = '작성';
        $isNewIcon = 'edit';
    }else{
        $isNew = '수정';
        $isNewIcon = 'eraser';
        $document = mysqli_fetch_assoc($document);
    }
    $title = documentRender(filt($document['title'], 'htm'), TRUE);
    $url = $title;
    if($document['namespace']){
        $title = '<span class="muted">'.documentRender($document['namespace'], TRUE).':</span>'.$title;
    }

        //ACL
        $sqla = "SELECT `isAdmin` FROM `_account` WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sqla);
        $iA = mysqli_fetch_assoc($result);
        $iA = $iA['isAdmin'];

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

        $sql = "SELECT `title` FROM `_article` WHERE `type` = 'COMMON' and `title` not like '%/%' ORDER BY rand() LIMIT 1";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $fnwSuggest = $row['title'];
?>

<!DOCTYPE html>
<html lang="<?=$fnLang?>">
    <head>
        <meta charset="UTF-8">
        <meta name="robots" content="noarchive">
        <meta name="author" content="FNBase Team">
        <meta name="theme-color" content="<?=$fnPColor?>">
        <meta name="classification" content="html">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- 정보 -->
        <title><?=$fnTitle?></title>
        <meta name="description" content="<?=$fnDesc?>">
        <meta property="og:type" content="website">

        <!-- 불러오기 -->
        <style>
            @media (min-width: 300px) and (max-width: 1800px) {
                html {
                    scrollbar-width: none;
                }
                h-m {
                    display: none;
                }
                .hidMob {
                    display:none;
                }
            }
            @media (min-width: 1900px) {
                html {
                    scrollbar-width: thin;
                }
                h-d {
                    display: none;
                }
            }
            html { overflow-y:scroll; word-break: break-word }
            ul,ol {
                margin: 0;
            }
        </style>
        <link rel="stylesheet" as="font" crossorigin="crossorigin" type="text/css" href="/icofont/icofont.min.css">
        <link rel="stylesheet" href="/wiki.css">
        <link rel="stylesheet" href="/default.css">
        <link rel="stylesheet" href="/picnic.css">
        <link rel="shortcut icon" href="/wiki.png">
        <?=$fnPHead.$lsHead?>
    </head>
    <body style="background:<?=$fnBColor?>">
        <a id="top"></a>
        <header>
            <nav class="nav" style="position:static;background: <?=$fnPColor?>">
                <a href="/w/" class="brand">
                    <span style="color:#fff"><?=$fnTitle?></span>
                </a>
                <a href="/w/최근 바뀜" class="brand-r">
                    <span style="color:#fff"><i class="icofont-ui-rotation"></i><h-m> 최근 바뀜</h-m></span>
                </a>
                <a href="/w/최근 토론" class="brand-r">
                    <span style="color:#fff"><i class="icofont-users-alt-2"></i><h-m> 최근 토론</h-m></span>
                </a>
                <a href="/w/특수 문서 목록" class="brand-r">
                    <span style="color:#fff"><i class="icofont-file-exe"></i><h-m> 다른 기능</h-m></span>
                </a>
                <a href="/" class="brand-r">
                    <span style="color:#fff"><i class="icofont-listing-box"></i><h-m> 게시판</h-m></span>
                </a>
                <?php
                    if($_SESSION['fnUserId']){
                        echo '<div class="menu">
                            <label for="userModal"><img height="40" id="myGravatar" src="'.get_gravatar($_SESSION['fnUserMail'], 40, 'identicon', 'pg').'"></label>
                        </div>';
                        $isLogged = TRUE;
                    }else{
                        echo '<div class="menu">
                            <a href="/w/로그인" class="button success"><h-m><i class="icofont-login"></i> 로그인</h-m><h-d><i class="icofont-invisible"></i></h-d></a>
                        </div>';
                        $isLogged = FALSE;
                    }
                ?>
            </nav>
            <form action="javascript:void(0)" onsubmit="if(!document.querySelector('#search-top').value){document.querySelector('#search-top').value = document.querySelector('#search-top').placeholder;};location.href = '/w/'+encodeURIComponent(document.getElementById('search-top').value).replace(/%23/g, '%2523').replace(/%2F/g, '/')">
                <div id="topNoticeLine" style="background:<?=$fnSColor?>;text-overflow:unset;color:white">
                    <a href="/w/임의 문서로" class="label success">검색</a><input id="search-top" style="background:transparent;border:none;height:1.2em" placeholder="<?=$fnwSuggest?>">
                </div>
            </form>
        </header>
        <!-- 페이지 로드 -->
        <article class="flex">
            <div class="hidMob">
            </div>
            <section id="mainArticle" class="half">
                <?php
                if($_GET['on'] == 'discussPage'){
                    $pgNum = filt($_GET['d_num'], '123');
                    $pgMent = filt($_GET['ment'], '123');
                    if($isLogged){
                        if($pgMent){
                            $sql = "SELECT `target` FROM `_ment` WHERE `num` = '$pgMent'";
                            $result = mysqli_query($conn, $sql);
                            if(mysqli_num_rows($result) == 1){
                                $sql = "UPDATE `_ment` SET `isSuccess` = 1 WHERE `num` = '$pgMent'";
                                $result = mysqli_query($conn, $sql);
                                if(!$result){
                                    echo '데이터베이스 연결 실패';
                                }
                            }
                        }
                    }

                    $sql = "SELECT * FROM `_discuss` WHERE `num` = '$pgNum'";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    $d_num = $row['num'];
                    $d_title = $row['discussName'];
                    echo '<div class="card" class="wikiDiscussCard">
                            <header style="background:#f3f3f3;border-bottom:1px solid #e6e6e6">
                                <h4 class="black noGray"><a href="/w/'.myUrlEncode($row['title']).'"><i class="icofont-page"></i> '.$row['title'].'</a></h4>
                            </header>
                            <section>';
                            if($row['status'] == 'ACTIVE'){
                                $dC = 'green';
                                $dT = '#fff';
                            }elseif($row['status'] == 'PAUSE'){
                                $dC = 'orange';
                                $dT = '#000';
                                $dSt = 'PAUSE';
                            }else{
                                $dC = 'black';
                                $dT = '#fff';
                                $dSt = 'DISABLED';
                            }
                            $sql = "SELECT count(*) as `cnt` FROM `_discussThread` WHERE `origin` = '$pgNum'";
                            $result = mysqli_query($conn, $sql);
                            $count = mysqli_fetch_assoc($result);
                            echo '<div class="card" style="border: 2px dashed gainsboro" class="wikiDiscussCard">
                                    <header style="background:'.$dC.';color:'.$dT.';border-bottom:1px solid #e6e6e6">
                                        <h4><i class="icofont-users-alt-2"></i> '.$row['discussName'].' ( '.$count['cnt'].' )</h4>
                                    </header>
                                    <section>';
                            $sql = "SELECT * FROM `_discussThread` WHERE `origin` = '$pgNum'";
                            $result = mysqli_query($conn, $sql);

                            $d = 1;
                            while($row = mysqli_fetch_assoc($result)){
                                $wE = $row['id'];
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

                                $content = nl2br(documentRender($row['content'], 'discuss'));
                                $content = filt(preg_replace('/<\/h(\d)><br \/>/m', '</h$1>', $content), 'oth');
                                echo '<div class="card" class="wikiDiscussCard" id="discuss_'.$d.'">
                                    <header class="black noGray" style="background:#f6f6f6;border-bottom:1px solid #e6e6e6">
                                        <b>#'.$d.'</b> <span class="muted"><i class="icofont-'.$icon.'"></i> <a href="'.$href.'">'.$name.'</a></span><span class="subInfo"><h-d><br></h-d><h-m>
                                        </h-m>('.$row['at'].')</span>
                                    </header>
                                    <section style="padding-bottom:.45em">
                                        '.$content.'
                                    </section>
                                </div>';
                                $d++;
                            }
                                        if($_SESSION['fnUserName']){
                                            $icon = 'user-alt-7';
                                            $name = $_SESSION['fnUserName'];
                                        }else{
                                            $icon = 'invisible';
                                            $name = $ip;
                                        }

                                        if($canEdit || $id){
                                        echo '<br><a id="discussBtm"></a><div class="card" class="wikiDiscussCard">
                                            <header class="black noGray" style="background:#f6f6f6;border-bottom:1px solid #e6e6e6">
                                                <b><i class="icofont-mic"></i> 발언하기</b><span class="subInfo"> <i class="icofont-'.$icon.'"></i> '.$name.'<span>
                                            </header>
                                            <section style="padding-bottom:.45em">
                                            <form action="/wiki_d.php?mode=speak&title='.$fnwTitle.'" method="post">
                                                <input type="hidden" name="num" value="'.$d_num.'">';
                                            if(!$dSt){
                                                echo '<textarea name="content" placeholder="예의를 지킵시다. (1000자 이내 작성)" style="border:0" maxlength="1000" required></textarea>
                                                <input type="submit" class="button success full" value="발언하기">';
                                            }else{
                                                echo '<textarea style="border:0" disabled>이 토론은 활성 상태가 아닙니다.</textarea>';
                                            }
                                            echo '</form>';
                                            if($iA){
                                                echo '<form method="post" action="/wiki_v.php?discuss=pause"><input type="hidden" name="num" value="'.$d_num.'">';
                                                if(!$dSt){
                                                    echo '<button type="submit" class="full" style="background:orange">이 토론 멈추기</button><br>';
                                                    echo '<button type="submit" class="error full" formaction="/wiki_v.php?discuss=stop">이 토론 숨기기</button>';
                                                }else{
                                                    echo '<button type="submit" class="success full" formaction="/wiki_v.php?discuss=play">이 토론 활성화</button>';
                                                }
                                                echo '</form>';
                                            }
                                            echo '</section>
                                        </div>';
                                        }
                                    echo '</section>
                                </div>';
                            echo '</section>
                        </div>';
                        $fnwTitle = '(토론) '.$d_title;
                }else{ ?>
                <div id="mainTitle">
                    <h2 id="wikiTitleText" class="black noGray"><a href="/w/<?=myUrlEncode($url)?>"><?=str_ireplace('&amp;apos;', "'", myUrlDecode($title))?><span id="wikiModeText" class="muted"></span></a></h2>
                    <hr><span id="wikiTitleRaw" style="display:none"><?=$fnwTitle?></span>
                    <script>
                        if(document.URL.includes('?from=')){
                            rediFrom = document.URL.split('?from=')
                            document.querySelector('#wikiTitleRaw').style.display = ''
                            document.querySelector('#wikiTitleRaw').innerHTML = '<a href="/wiki/'+rediFrom[1]+'">'+decodeURI(rediFrom[1])+'</a> 에서 넘어옴<hr>'
                        }
                    </script>
                </div>
                <div id="mainContent">
                    <?php
                        $parStr = documentRender('___PARENT___', TRUE);
                        if(strlen($parStr) > 0){
                            if($parStr == '틀'){
                                echo '<a href="/w/틀">틀</a> 문서입니다.<hr style="margin-top:4px">';
                            }elseif($parStr == '분류'){
                                echo '<a href="/w/분류">분류</a> 문서입니다.<hr style="margin-top:4px">';
                                $catTitle = explode('/', $fnwTitle, 2);
                                $catTitle = $catTitle[1];
                                $document['execute'] = 'category';
                            }else{
                                echo '상위 문서 : <a href="/w/'.myUrlEncode($parStr).'">'.$parStr.'</a><hr style="margin-top:4px">';
                            }
                        }
                        $content = nl2br(documentRender($document['content']));
                        echo preg_replace('/<br( \/)*>\n<hr>/m', '<hr>', preg_replace('/(src="|<hr>)(.*)<br( \/)*>/m', '$1$2', preg_replace('/<\/h(\d)><br \/>/m', '</h$1>', $content)));
                        echo '</table>';
                        if($document['execute']){
                            switch ($document['execute']) {
                                case 'list':
                                    echo '<br><table class="full"><tbody>';
                                    $sql = "SELECT `title`, `lastEdit`, `whoEdited` FROM `_article` WHERE `type` = 'SpecialDOC' ORDER BY `num`";
                                    $result = mysqli_query($conn, $sql);
                                    while($row = mysqli_fetch_assoc($result)){
                                        echo '<tr><td class="black"><a href="/w/'.myUrlEncode($row['title']).'"><i class="icofont-gears"></i> '.$row['title'].'</td></tr>';
                                    }
                                    echo '</tbody></table>';
                                    break;
                                case 'login':
                                    if($id){
                                        session_unset();
                                        echo '<script>alert("로그아웃 되었습니다.");history.back()</script>';
                                    }else{
                                        echo '<br><form method="post" action="/sub/login.php"><hr>
                                            <label><input type="text" name="id" placeholder="아이디" style="border:0" required></label><hr>
                                            <label><input type="password" name="pw" placeholder="비밀번호" style="border:0" required></label>
                                            <input type="hidden" name="from" value="<?=$idPath?>"><hr>
                                            <a href="/forgot_password">비밀번호를 잊으셨나요?</a>
                                            <a href="/sub/ip_login.php" style="float:right"><i class="icofont-checked"></i><h-m> ip 로그인</h-m></a>
                                            <button class="button success full" type="submit">로그인</button>
                                        </form>';
                                    }
                                    break;
                                case 'recent':
                                        $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
                                        $result = mysqli_query($conn, $sql);
                                        $iA = mysqli_fetch_assoc($result);
                                            if($iA['isAdmin']){
                                                $isAdmin = TRUE;
                                            }
                                    echo '<br><table class="full"><tbody>';
                                    $sql = "SELECT * FROM `_article` ORDER BY `lastEdit` DESC LIMIT 30";
                                    $result = mysqli_query($conn, $sql);
                                    while($row = mysqli_fetch_assoc($result)){
                                        $wE = $row['whoEdited'];
                                        $sqln = "SELECT `name` FROM `_account` WHERE `id` = '$wE'";
                                        $resultn = mysqli_query($conn, $sqln);

                                        if(mysqli_num_rows($resultn) < 1){
                                            $name = $wE;

                                            $icon = 'invisible';
                                            $href = '/misc%3EmanageCenter%3E'.$wE;
                                            if($isAdmin){
                                                $href = '/misc>manageCenter>'.$name;
                                            }
                                        }else{
                                            $name = mysqli_fetch_assoc($resultn);
                                            $name = $name['name'];

                                            $icon = 'user-alt-7';
                                            $href = '/u/'.$wE;
                                        }
                                        echo '<tr><td class="black"><a href="/w/'.myUrlEncode($row['title']).'"><i class="icofont-page"></i> '.$row['title'].'<br>
                                        <span class="subInfo"><i class="icofont-eraser"></i> '.get_timeFlies($row['lastEdit']).'
                                        <i class="icofont-'.$icon.'"></i> <a class="subInfo" href="'.$href.'">'.$name.'</a></span></td></tr>';
                                    }
                                    echo '</tbody></table>';
                                    break;
                                case 'discussRecent':
                                    echo '<br><table class="full"><tbody>';
                                    $sql = "SELECT * FROM `_discuss` WHERE `status` IN ('ACTIVE') ORDER BY `lastEdit` DESC LIMIT 30";
                                    $result = mysqli_query($conn, $sql);
                                    while($row = mysqli_fetch_assoc($result)){

                                        echo '<tr><td class="black"><a href="/discuss/'.$row['num'].'"><i class="icofont-users-alt-7"></i> '.$row['discussName'].'<br>
                                        <span class="subInfo"><i class="icofont-eraser"></i> '.get_timeFlies($row['lastEdit']).' /</span>
                                        <a href="/w/'.myUrlEncode($row['title']).'" class="subInfo"><i class="icofont-page"></i> '.$row['title'].'</a></td></tr>';
                                    }
                                    echo '</tbody></table>';
                                    break;
                                case 'random':
                                    $sql = "SELECT `title` FROM `_article` WHERE `type` = 'COMMON' ORDER BY rand() LIMIT 1";
                                    $result = mysqli_query($conn, $sql);
                                    $row = mysqli_fetch_assoc($result);
                                    echo '<script>location.href = "/w/'.myUrlEncode($row['title']).'"</script>';
                                    break;
                                case 'search':
                                    echo '<br><hr><h3>검색 결과</h3><b>제목 일치</b><table class="full"><tbody>';
                                    $sql = "SELECT * FROM `_article` WHERE `title` like '%$fnwTitle%' ORDER BY `lastEdit` DESC LIMIT 50";
                                    $result = mysqli_query($conn, $sql);
                                    if(mysqli_num_rows($result) > 0){
                                        while($row = mysqli_fetch_assoc($result)){
                                            $wE = $row['whoEdited'];
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
                                            echo '<tr><td class="black noGray"><a href="/w/'.myUrlEncode($row['title']).'"><i class="icofont-page"></i> '.$row['title'].'<br>
                                            <span class="subInfo"><i class="icofont-eraser"></i> '.get_timeFlies($row['lastEdit']).'
                                            <i class="icofont-'.$icon.'"></i> <a class="subInfo" href="'.$href.'">'.$name.'</a></span></td></tr>';
                                        }
                                    }else{
                                        echo '<tr><td>검색 결과가 없습니다..</td></tr>';
                                    }
                                    echo '</tbody></table>';
                                    echo '<br><b>내용 일치</b><table class="full"><tbody>';
                                    $sql = "SELECT * FROM `_article` WHERE `content` like '%$fnwTitle%' ORDER BY `lastEdit` DESC LIMIT 30";
                                    $result = mysqli_query($conn, $sql);
                                    if(mysqli_num_rows($result) > 0){
                                        while($row = mysqli_fetch_assoc($result)){
                                            $wE = $row['whoEdited'];
                                            $sqln = "SELECT `name` FROM `_account` WHERE `id` = '$wE'";
                                            $resultn = mysqli_query($conn, $sqln);

                                            preg_match('/(.{0,80})('.$fnwTitle.')(.{0,80})/mu', $row['content'], $stxt);
                                            $stxt = str_ireplace($fnwTitle, '<mark>'.$fnwTitle.'</mark>', $stxt);

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
                                            echo '<tr><td class="black noGray"><a href="/w/'.myUrlEncode($row['title']).'"><i class="icofont-page"></i> '.$row['title'].'<br>
                                            <blockquote>..'.preg_replace('/&lt;(\/){0,1}mark&gt;/', '<$1mark>', htmlspecialchars($stxt[0])).'..</blockquote>
                                            <span class="subInfo"><i class="icofont-eraser"></i> '.get_timeFlies($row['lastEdit']).'
                                            <i class="icofont-'.$icon.'"></i> <a class="subInfo" href="'.$href.'">'.$name.'</a></span><br></td></tr>';
                                        }
                                    }else{
                                        echo '<tr><td>검색 결과가 없습니다..</td></tr>';
                                    }
                                    echo '</tbody></table>';
                                    echo '<br><hr><form action="javascript:void(0)" onsubmit="location.href = \'/w/\'+document.getElementById(\'search-query\').value">
                                    <input type="text" id="search-query" placeholder="검색..">
                                    <button type="submit" class="success full"><i class="icofont-search-folder"></i> 검색하기</button>
                                    <a class="button full" href="/w/임의 문서로"><i class="icofont-random"></i> 임의 문서로</a></form>';
                                    echo '<br><hr><button style="background:gray;color:white;border:black solid 2px"
                                    onclick="location.href=\'https://ko.wikipedia.org/wiki/'.$fnwTitle.'\'">위키백과<h-m>에서 보기</h-m></button>
                                    <button style="background:#4188f1;color:white;border:blue solid 2px"
                                    onclick="location.href=\'https://librewiki.net/wiki/'.$fnwTitle.'\'">리브레위키<h-m>에서 보기</h-m></button>
                                    <button style="background-image:linear-gradient(to right, #00a495 30%, #13ad65);color:white;border:green solid 2px;float:right"
                                    onclick="location.href=\'https://namu.wiki/w/'.$fnwTitle.'\'">나무위키<h-m>에서 보기</h-m></button>';
                                    break;
                                case 'articles':
                                    $qP = filt($_GET['ap'], '123');

                                    if(empty($qP) and $qP != '0'){
                                        $qP = 1;
                                    }
                                    $l = $qP * 50;
                                    $lc = $l - 50;
                                    $l = $lc.', 50';
                                    $sql_ = "SELECT `num` FROM `_article` WHERE `type` = 'COMMON' ORDER BY `lastEdit` ASC";
                                    $sql = "SELECT `title`, `viewCount`, `lastEdit` FROM `_article` WHERE `type` = 'COMMON' ORDER BY `lastEdit` ASC LIMIT $l";
                                    $result = mysqli_query($conn, $sql_);
                                    $qC = mysqli_num_rows($result);
                                    $result = mysqli_query($conn, $sql);

                                    echo '<br><table class="full"><tbody>';
                                    while($row = mysqli_fetch_assoc($result)){
                                        echo '<tr><td class="black"><a href="/wiki/'.myUrlEncode($row['title']).'"><i class="icofont-page"></i> '.$row['title'].'<br>
                                        <span class="subInfo"><i class="icofont-eraser"></i> '.get_timeFlies($row['lastEdit']).' /</span> <green class="little">'.$row['viewCount'].'</green></td></tr>';
                                    }
                                    echo '</tbody></table>';

                                    echo '<!-- 페이지 이동 --><br>
                                        <div class="center">
                                            <div class="pagination">
                                                <a href="/w_1_ap/'.$fnwTitle.'">&laquo;</a>';

                                                $cac = $qC / 50;
                                                $qlast = ceil($cac);

                                                $cal = $qC % 50;
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
                                                        echo '<a class="active" href="/w_'.$qPg.'_ap/'.$fnwTitle.'">'.$qPg.'</a>';
                                                    }else{
                                                        echo '<a href="/w_'.$qPg.'_ap/'.$fnwTitle.'">'.$qPg.'</a>';
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

                                                echo '<a href="/w_'.$qlast.'_ap/'.$fnwTitle.'">&raquo;</a>';
                                            echo '</div>
                                        </div>';
                                    break;
                                case 'insolvent':
                                    $qP = filt($_GET['ip'], '123');

                                    if(empty($qP) and $qP != '0'){
                                        $qP = 1;
                                    }
                                    $l = $qP * 50;
                                    $lc = $l - 50;
                                    $l = $lc.', 50';
                                    $sql_ = "SELECT `num` FROM `_article` WHERE `type` = 'COMMON' and `title` not like '틀/%' and `title` not like '분류/%' and `content` not like '\#넘겨주기 %' and `content` not like '\#redirect %' ORDER BY CHAR_LENGTH(`content`) ASC";
                                    $sql = "SELECT `title`, `viewCount`, `lastEdit`, CHAR_LENGTH(`content`) as `length` FROM `_article`
                                    WHERE `type` = 'COMMON' and `title` not like '틀/%' and `title` not like '분류/%' and `content` not like '\#넘겨주기%' and `content` not like '\#redirect%' ORDER BY CHAR_LENGTH(`content`) ASC LIMIT $l";
                                    $result = mysqli_query($conn, $sql_);
                                    $qC = mysqli_num_rows($result);
                                    $result = mysqli_query($conn, $sql);

                                    echo '<br><table class="full"><tbody>';
                                    while($row = mysqli_fetch_assoc($result)){
                                        echo '<tr><td class="black"><a href="/wiki/'.myUrlEncode($row['title']).'"><i class="icofont-page"></i> '.$row['title'].'<br>
                                        <span class="subInfo"><b>'.$row['length'].' 글자</b> <i class="icofont-eraser"></i> '.get_timeFlies($row['lastEdit']).' /</span> <green class="little">'.$row['viewCount'].'</green></td></tr>';
                                    }
                                    echo '</tbody></table>';

                                    echo '<!-- 페이지 이동 --><br>
                                        <div class="center">
                                            <div class="pagination">
                                                <a href="/w_1_ip/'.$fnwTitle.'">&laquo;</a>';

                                                $cac = $qC / 50;
                                                $qlast = ceil($cac);

                                                $cal = $qC % 50;
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
                                                        echo '<a class="active" href="/w_'.$qPg.'_ip/'.$fnwTitle.'">'.$qPg.'</a>';
                                                    }else{
                                                        echo '<a href="/w_'.$qPg.'_ip/'.$fnwTitle.'">'.$qPg.'</a>';
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

                                                echo '<a href="/w_'.$qlast.'_ip/'.$fnwTitle.'">&raquo;</a>';
                                            echo '</div>
                                        </div>';
                                    break;
                                case 'unctgrzd':
                                    $qP = filt($_GET['gp'], '123');

                                    if(empty($qP) and $qP != '0'){
                                        $qP = 1;
                                    }
                                    $l = $qP * 50;
                                    $lc = $l - 50;
                                    $l = $lc.', 50';
                                    $sql_ = "SELECT `num` FROM `_article` WHERE `type` = 'COMMON' and `content` not like '\#넘겨주기%' and `content` not like '\#redirect%' ORDER BY `lastEdit` ASC";
                                    $sql = "SELECT `title`, `viewCount`, `lastEdit` FROM `_article` WHERE `type` = 'COMMON' and `content` not like '\#넘겨주기%' and `content` not like '\#redirect%' ORDER BY `lastEdit` ASC LIMIT $l";
                                    $result = mysqli_query($conn, $sql_);
                                    $qC = mysqli_num_rows($result);
                                    $result = mysqli_query($conn, $sql);

                                    echo '<br><table class="full"><tbody>';
                                    while($row = mysqli_fetch_assoc($result)){
                                        echo '<tr><td class="black"><a href="/wiki/'.myUrlEncode($row['title']).'"><i class="icofont-page"></i> '.$row['title'].'<br>
                                        <span class="subInfo"><i class="icofont-eraser"></i> '.get_timeFlies($row['lastEdit']).' /</span> <green class="little">'.$row['viewCount'].'</green></td></tr>';
                                    }
                                    echo '</tbody></table>';

                                    echo '<!-- 페이지 이동 --><br>
                                        <div class="center">
                                            <div class="pagination">
                                                <a href="/w_1_gp/'.$fnwTitle.'">&laquo;</a>';

                                                $cac = $qC / 50;
                                                $qlast = ceil($cac);

                                                $cal = $qC % 50;
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
                                                        echo '<a class="active" href="/w_'.$qPg.'_gp/'.$fnwTitle.'">'.$qPg.'</a>';
                                                    }else{
                                                        echo '<a href="/w_'.$qPg.'_gp/'.$fnwTitle.'">'.$qPg.'</a>';
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

                                                echo '<a href="/w_'.$qlast.'_gp/'.$fnwTitle.'">&raquo;</a>';
                                            echo '</div>
                                        </div>';
                                    break;
                                case 'abcasc':
                                    $qP = filt($_GET['bp'], '123');

                                    if(empty($qP) and $qP != '0'){
                                        $qP = 1;
                                    }
                                    $l = $qP * 50;
                                    $lc = $l - 50;
                                    $l = $lc.', 50';
                                    $sql_ = "SELECT `num` FROM `_article` WHERE `type` = 'COMMON' ORDER BY `title` ASC";
                                    $sql = "SELECT `title`, `viewCount`, `lastEdit` FROM `_article` WHERE `type` = 'COMMON' ORDER BY `title` ASC LIMIT $l";
                                    $result = mysqli_query($conn, $sql_);
                                    $qC = mysqli_num_rows($result);
                                    $result = mysqli_query($conn, $sql);

                                    echo '<br><table class="full"><tbody>';
                                    while($row = mysqli_fetch_assoc($result)){
                                        echo '<tr><td class="black"><a href="/wiki/'.myUrlEncode($row['title']).'"><i class="icofont-page"></i> '.$row['title'].'<br>
                                        <span class="subInfo"><i class="icofont-eraser"></i> '.get_timeFlies($row['lastEdit']).' /</span> <green class="little">'.$row['viewCount'].'</green></td></tr>';
                                    }
                                    echo '</tbody></table>';

                                    echo '<!-- 페이지 이동 --><br>
                                        <div class="center">
                                            <div class="pagination">
                                                <a href="/w_1_bp/'.$fnwTitle.'">&laquo;</a>';

                                                $cac = $qC / 50;
                                                $qlast = ceil($cac);

                                                $cal = $qC % 50;
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
                                                        echo '<a class="active" href="/w_'.$qPg.'_bp/'.$fnwTitle.'">'.$qPg.'</a>';
                                                    }else{
                                                        echo '<a href="/w_'.$qPg.'_bp/'.$fnwTitle.'">'.$qPg.'</a>';
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

                                                echo '<a href="/w_'.$qlast.'_bp/'.$fnwTitle.'">&raquo;</a>';
                                            echo '</div>
                                        </div>';
                                    break;
                                case 'category':
                                    $qP = filt($_GET['cp'], '123');

                                    if(empty($qP) and $qP != '0'){
                                        $qP = 1;
                                    }
                                    $l = $qP * 50;
                                    $lc = $l - 50;
                                    $l = $lc.', 50';
                                    $sql_ = "SELECT `num` FROM `_article` WHERE `type` = 'COMMON' and `content` like '%[[분류/$catTitle]]%' ORDER BY `title` ASC";
                                    $sql = "SELECT `title`, `viewCount`, `lastEdit` FROM `_article` WHERE `type` = 'COMMON' and `content` like '%[[분류/$catTitle]]%' ORDER BY `title` ASC LIMIT $l";
                                    $result = mysqli_query($conn, $sql_);
                                    $qC = mysqli_num_rows($result);
                                    $result = mysqli_query($conn, $sql);

                                    echo '<br><br><table class="full"><tbody>';
                                    while($row = mysqli_fetch_assoc($result)){
                                        echo '<tr><td class="black"><a href="/wiki/'.myUrlEncode($row['title']).'"><i class="icofont-page"></i> '.$row['title'].'<br>
                                        <span class="subInfo"><i class="icofont-eraser"></i> '.get_timeFlies($row['lastEdit']).' /</span> <green class="little">'.$row['viewCount'].'</green></td></tr>';
                                    }
                                    echo '</tbody></table><br>';

                                    echo '<!-- 페이지 이동 -->
                                        <div class="center">
                                            <div class="pagination">
                                                <a href="/w_1_cp/'.$fnwTitle.'">&laquo;</a>';

                                                $cac = $qC / 50;
                                                $qlast = ceil($cac);

                                                $cal = $qC % 50;
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
                                                        echo '<a class="active" href="/w_'.$qPg.'_cp/'.$fnwTitle.'">'.$qPg.'</a>';
                                                    }else{
                                                        echo '<a href="/w_'.$qPg.'_cp/'.$fnwTitle.'">'.$qPg.'</a>';
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

                                                echo '<a href="/w_'.$qlast.'_cp/'.$fnwTitle.'">&raquo;</a>';
                                            echo '</div>
                                        </div>';
                                    break;

                                default:
                                    echo '<br><table class="full">';
                                    echo '<tbody>';

                                    echo '<tr><td colspan="2" style="background:'.$fnPColor.'"><strong>사이트 정보</strong></td></tr>';
                                    echo '<tr><td>사이트 이름</td><td>'.$fnTitle.'</td></tr>';
                                    echo '<tr><td>사이트 설명</td><td>'.$fnDesc.'</td></tr>';
                                    echo '<tr><td>기본 언어 설정</td><td>'.$fnLang.'</td></tr>';
                                    echo '<tr><td>기본 시간대 값</td><td>'.$fnTz.'</td></tr>';

                                    echo '<tr><td colspan="2" style="background:'.$fnPColor.'"><strong>스킨 정보</strong></td></tr>';
                                    echo '<tr><td>적용 스킨</td><td>Default</td></tr>';
                                    echo '<tr><td>기본 색상</td><td style="color:'.$fnPColor.'">'.$fnPColor.'</td></tr>';
                                    echo '<tr><td>보조 색상</td><td><span style="color:'.$fnSColor.'">'.$fnSColor.'</span><sub>2단계</sub> /
                                    <span style="color:'.$fnBColor.';background:rgba(0,0,0,0.5)">'.$fnBColor.'</span><sub>배경</sub></td></tr>';

                                    echo '<tr><td colspan="2" style="background:'.$fnPColor.'"><strong>서버 환경</strong></td></tr>';
                                    echo '<tr><td>PHP 버전</td><td>'.phpversion().'</td></tr>';
                                    echo '<tr><td><a href="https://github.com/FNBase/FNBase-Engine">FNBE</a> 버전</td><td><a href="/w/버전별 변경점#'.$fnVersion.'">'.$fnVersion.'</a></td></tr>';

                                    echo '<tr><td colspan="2" style="background:'.$fnPColor.'"><strong>오픈소스 정보</strong></td></tr>';
                                    echo '<tr><td><a href="https://picnicss.com">Picnic CSS</a></td><td>MIT - Francisco Presencia</td></tr>';
                                    echo '<tr><td><a href="https://icofont.org">IcoFont</a></td><td>MIT - Icofont</td></tr>';
                                    echo '<tr><td><a href="https://htmlpurifier.org">HTML Purifier</a></td><td>LGPL - HTML Purifier</td></tr>';
                                    echo '</tbody>';

                                    echo '</table><br><br>';


                                    echo '<br><table class="full">';
                                    echo '<tbody>';

                                    $sql = "SELECT count(`num`) as `PageCount`, sum(`viewCount`) as `ViewCount`, max(`lastEdit`) as `RCTEdit` FROM `_article` ";
                                    $result = mysqli_query($conn, $sql);
                                    $fnWA = mysqli_fetch_assoc($result);
                                    echo '<tr><td colspan="2" style="background:'.$fnPColor.'"><strong>위키 통계</strong></td></tr>';
                                    echo '<tr><td>위키 유형</td><td>Open</td></tr>';
                                    echo '<tr><td>문서 수 합</td><td>'.$fnWA['PageCount'].'</td></tr>';
                                    echo '<tr><td>조회 수 합</td><td>'.$fnWA['ViewCount'].'</td></tr>';
                                    echo '<tr><td>최근 편집</td><td>'.$fnWA['RCTEdit'].' ('.get_timeFlies($fnWA['RCTEdit']).')</td></tr>';

                                    echo '</table><br><br>';
                                    break;
                            }
                        }
                    ?>
                </div>
                <div id="editPlace" style="display:none">
                    <div id="editPlaceText"></div>
                </div>
                <div id="contentFooter" class="subInfo" style="text-align:right;min-width:100%">
                    <hr>
                    <?php
                        $sql = "SELECT * FROM `_article` WHERE `title` = '_Footer'";
                        $footer = mysqli_query($conn, $sql);
                        if($footer){
                            $footer = mysqli_fetch_assoc($footer);
                            echo nl2br(documentRender($footer['content']));
                        }
                    ?>
                </div>
                    <?php } ?>
            </section>
            <aside class="hidMob">
                    <section id="asideFunction">
                        <div>
                            <br><br><br><br>
                        <?php
                        if(!$pgNum){
                            if($document['ACL'] !== 'none'){
                                    echo '&nbsp;<button type="button" onclick="wikiEdit(\''.myUrlEncode($fnwTitle).'\')"';
                                    if(!$canEdit){
                                        echo ' class="outline"';
                                        $isNew = '원본';
                                        $isNewIcon = 'file-text';
                                    }else{
                                        echo ' class="outline-green"';
                                    }
                                    echo'><i class="icofont-'.$isNewIcon.'"></i> '.$isNew.'</button><br>';
                                    echo '&nbsp;<button type="button" class="outline-blue" onclick="wikiDiscuss(\''.myUrlEncode($fnwTitle).'\')"><i class="icofont-users-alt-2"></i> 토론</button><br>';
                            }
                                    echo '&nbsp;<button type="button" class="outline" onclick="wikiHistory(\''.myUrlEncode($fnwTitle).'\')"><i class="icofont-history"></i> 기록</button>';
                                    echo '<br>&nbsp;<button type="button" class="outline-danger" onclick="wikiManage(\''.myUrlEncode($fnwTitle).'\')"><i class="icofont-gears"></i> 조정</button>';
                        }
                        ?>
                        </div>
                    </section>
            </aside>
        </article>
        <script>
            //임시 기능
            var re = /plusAll\(([0-9+\-\*\/\.]+)\)/;
            if(document.body.querySelector('#mainArticle').innerHTML.match(re)){
                while(document.body.querySelector('#mainArticle').innerHTML.match(re)){
                    var val = document.body.querySelector('#mainArticle').innerHTML.match(re)
                    document.body.querySelector('#mainArticle').innerHTML = document.body.querySelector('#mainArticle').innerHTML.replace(val[0], eval(val[1]))
                }
            }
        </script>
        <hr>
        <!-- 로그인 모달 호출 -->
            <?php
                if($_SESSION['fnUserId']){
                    echo '
                    <!-- 유저 모달 호출 -->
                    <div class="modal">
                        <input id="userModal" type="checkbox" />
                        <label for="userModal" class="overlay"></label>
                        <article>
                            <header>
                            <h3>메뉴</h3>
                            <label for="userModal" class="close">&times;</label>
                            </header>
                            <form method="post" action="/login.php">
                                <section class="content">
                                    회원 전용 기능입니다. 사용해보세요!
                                </section>
                                <footer class="lilMob">
                                    <a class="button" href="/u/'.$_SESSION['fnUserId'].'">내 정보</a>
                                    <span class="right">
                                    <a class="button dangerous" href="/wiki/로그인">로그아웃</a>
                                    </span>
                                </footer>
                            </form>
                        </article>
                    </div>
                    ';
                }
            ?>
            </a>
            <div class="modal">
                <input id="wikiOption" type="checkbox" />
                <label for="wikiOption" class="overlay"></label>
                <article>
                    <header>
                    <h3><i class="icofont-plus-square"></i> 표시 설정</h3>
                    <label for="wikiOption" class="close">&times;</label>
                    </header>
                    <form method="post" action="/sub/wikiOpt.php">
                        <section class="content">
                            <label><input name="topColor" placeholder="상단바 색상 (hex)" class="pcwidth" value="<?=$fnPColor?>" required></label><br>
                            <label><input name="seaColor" placeholder="검색창 색상 (hex)" class="pcwidth" value="<?=$fnSColor?>" required></label><hr>
                            <!--<label>
                                <input type="checkbox" name="disrespect"<--?=$disRedi?>>
                                <span class="checkable">준비중</span>
                            </label><h-d><br></h-d>
                            <label>
                                <input type="checkbox" name="blindingLights"<--?=$bldLits?>>
                                <span class="checkable">색 반전 모드</span>
                            </label><h-d><br></h-d>
                            <label>
                                <input type="checkbox" name="forGrandpa"<--?=$forG?>>
                                <span class="checkable">글씨 크게</span>
                            </label>-->

                            <button class="button full" type="submit">설정 저장</button>
                        </section>
                    </form>
                    <span class="subInfo">추후 더 많은 기능이 추가될 예정입니다.</span>
                </article>
            </div>
            <div class="modal">
                <input id="wikiFunction" type="checkbox" />
                <label for="wikiFunction" class="overlay" id="wFClose"></label>
                <article>
                    <header>
                    <h3><i class="icofont-options"></i> 문서 옵션</h3>
                    <label for="wikiFunction" class="close">&times;</label>
                    </header>
                    <footer class="lilMob">
                        <button type="button" class="outline-blue" onclick="wikiDiscuss('<?=myUrlEncode($fnwTitle)?>')"><i class="icofont-users-alt-2"></i> 토론</button>
                        <button type="button" class="outline" onclick="wikiHistory('<?=myUrlEncode($fnwTitle)?>')"><i class="icofont-history"></i> <h-m>편집 </h-m>기록</button>
                        <button type="button" class="outline-danger" onclick="wikiManage('<?=myUrlEncode($fnwTitle)?>')"><i class="icofont-gears"></i> 조정</button>&nbsp;
                        <button type="button" onclick="wikiEdit('<?=myUrlEncode($fnwTitle)?>')" class="<?php
                            if(!$canEdit){
                                echo 'outline';
                                $isNew = '원본';
                                $isNewIcon = 'file-text';
                            }else{
                                echo 'outline-green';
                            }
                        ?> right"><i class="icofont-<?=$isNewIcon?>"></i> <?=$isNew?></button>
                    </footer>
                </article>
            </div>
            <div class="modal">
                <input id="wikiSearch" type="checkbox" />
                <label for="wikiSearch" class="overlay"></label>
                <article>
                    <header>
                    <h3><i class="icofont-search-document"></i> 문서 도구</h3>
                    <label for="wikiSearch" class="close">&times;</label>
                    </header>
                    <footer class="pcwidth">
                    <div id="tocDiv" style="display:block">
                        <span class="lager">목차</span><hr>
                    </div>
                    <form action="javascript:void(0)" onsubmit="location.href = '/w/'+document.getElementById('search-query-modal').value">
                        <input type="text" id="search-query-modal" placeholder="검색..">
                        <button type="submit" class="success full"><i class="icofont-search-folder"></i> 검색하기</button>
                        <a class="button full" href="/w/임의 문서로"><i class="icofont-random"></i> 임의 문서로</a>
                    </form>
                    </footer>
                </article>
            </div>
            <div class="modal">
                <input id="wikiHistoryRevView" type="checkbox" />
                <label for="wikiHistoryRevView" id="wHrClose" class="overlay"></label>
                <article>
                    <header>
                    <h3><i class="icofont-search-document"></i> 편집 기록 열람</h3>
                    <label for="wikiHistoryRevView" class="close">&times;</label>
                    </header>
                    <section class="lilMob" style="border-bottom: 2px dashed gainsboro">
                        <button class="success" type="button" onclick="wikiRollConf()"><i class="icofont-spinner-alt-3"></i> 이 버전으로 되돌리기</button>
                        <button class="right" type="button" onclick="wikiHisRaw()"><i class="icofont-file-text"></i> Raw</button>
                    </section>
                    <footer class="pcwidth" id="wHrText">
                        표시 대기중...
                    </footer>
                </article>
            </div>
            <div class="modal">
                <input id="wikiPreview" type="checkbox" />
                <label for="wikiPreview" id="wPreClose" class="overlay"></label>
                <article>
                    <header>
                    <h3><i class="icofont-search-document"></i> 편집 미리보기</h3>
                    <label for="wikiPreview" class="close">&times;</label>
                    </header>
                    <footer class="pcwidth" id="wPreText">
                        표시 대기중...
                    </footer>
                </article>
            </div>
            <div class="modal">
                <input id="wikiNotes" type="checkbox" />
                <label for="wikiNotes" id="wPreNote" class="overlay"></label>
                <article>
                    <header>
                    <h3><i class="icofont-search-document"></i> 각주 표시</h3>
                    <label for="wikiNotes" class="close">&times;</label>
                    </header>
                    <footer class="pcwidth" id="wPreNoteTxt">
                        표시 대기중...
                    </footer>
                </article>
            </div>
            <!-- 알림창 -->
            <?php
            if($isLogged){
                $sql = "SELECT `type` FROM `_ment` WHERE `target` = '$id' and `isSuccess` = 0";
                $result = mysqli_query($conn, $sql);
                if(mysqli_num_rows($result) > 0){
                    echo '<a id="nofiBox" href="/nofi">
                        <span style="color:yellow">[알림] 새 알림이 있습니다!</span>';
                    $cmC = 0;
                    $mtC = 0;
                    while($row = mysqli_fetch_assoc($result)){
                        $i++;
                        if($row['type'] == 'NOFI_CMMNT' || $row['type'] == 'NOFI_REPLY'){
                            $cmC++;
                        }else{
                            $mtC++;
                        }
                    }
                    echo '<br><span class="nofiText">(호출 '.$mtC.'건, 댓글 '.$cmC.'건)</span><br>';
                }elseif($board == 'recent'){
                    echo '<a id="nofiBox" href="/nofi" style="opacity: 0.7">
                        <span class="nofiText">[알림] 새 알림이 없습니다.</span><br>';
                }
            }
            echo '</a>';
            ?>
            <!-- Top / Bottom -->
            <div id="pgOption">
                <label for="wikiOption"><i class="icofont-options"></i></label>
                <label for="wikiSearch"><i class="icofont-search-document"></i></label>
                <label for="wikiFunction"><i class="icofont-plus-square"></i></label>
            </div>
            <div id="pgUpDown">
                <a href="#top"><i class="icofont-swoosh-up"></i></a>
                <a href="#bottom"><i class="icofont-swoosh-down"></i></a>
            </div>
            <!-- 알림창 -->
            <?php
            if($isLogged){
                $sql = "SELECT `type` FROM `_ment` WHERE `target` = '$id' and `isSuccess` = 0";
                $result = mysqli_query($conn, $sql);
                if(mysqli_num_rows($result) > 0){
                    echo '<a id="nofiBox" href="/nofi">
                        <span style="color:yellow">[알림] 새 알림이 있습니다!</span>';
                    $cmC = 0;
                    $mtC = 0;
                    while($row = mysqli_fetch_assoc($result)){
                        $i++;
                        if($row['type'] == 'NOFI_CMMNT' || $row['type'] == 'NOFI_REPLY'){
                            $cmC++;
                        }else{
                            $mtC++;
                        }
                    }
                    echo '<br><span class="nofiText">(호출 '.$mtC.'건, 댓글 '.$cmC.'건)</span><br>';
                }
            }
            echo '</a>';
            ?>
            <!-- 하단바 -->
        <footer>
            <div class="flex">
                <div>
                    <p class="right">(c) 2020 FNBase</p>
                </div>
                <div>
                    <p class="left muted">
                        <a href="/w/이용 안내">이용 안내</a>
                    </p>
                </div>
            </div>
        </footer>
            <a id="bottom"></a>
        <script type="text/javascript" src="/wiki.js"></script>
        <script type="text/javascript" src="/editor.js"></script>
        <?php $pageTitle = str_ireplace('&apos;', "\'", str_ireplace('&quot;', '\"', $fnwTitle)); ?>
        <script>
            document.title = "<?=$pageTitle.' - '.$fnTitle?>"
            wikiTitle = "<?=myUrlEncode($fnwTitle)?>"
            window.onload = function () {
                if(document.getElementById("mainContent")){
                    //목차
                    var toc = "";
                    var level = 0;

                    document.getElementById("mainContent").innerHTML =
                        document.getElementById("mainContent").innerHTML.replace(
                            /<h([2-5])>([^<]+)<\/h([2-5])>/gi,
                            function (str, openLevel, titleText, closeLevel) {
                                if (openLevel != closeLevel) {
                                    return str;
                                }
                                if (openLevel > level) {
                                    toc += (new Array(openLevel - level + 1)).join("<ol>");
                                } else if (openLevel < level) {
                                    toc += (new Array(level - openLevel + 1)).join("</ol>");
                                }
                                level = parseInt(openLevel);
                                var anchor = titleText.trim();
                                toc += "<li><a class=\"black\" href=\"#" + anchor + "\">" + titleText
                                    + "</a></li>";
                                return "<h" + openLevel + "><a class=\"black\" name=\"" + anchor + "\">"
                                    + titleText + "</a></h" + closeLevel + ">";
                            }
                        );

                    if (level) {
                        toc += (new Array(level + 1)).join("</ol>");
                    }

                    if (!toc){
                        document.querySelector('#tocDiv').style.display = 'none';
                    } else {
                        document.getElementById("tocDiv").innerHTML += toc+'\n<hr>';
                    }
                }
            };
            </script>
        <?php
            if($_GET['on'] == 'discuss'){
                echo '<script>wikiDiscuss(\''.myUrlEncode($fnwTitle).'\');</script>';
            }elseif($_GET['on'] == 'edit'){
                echo '<script>wikiEdit(\''.myUrlEncode($fnwTitle).'\');</script>';
            }elseif($_GET['on'] == 'history'){
                echo '<script>wikiHistory(\''.myUrlEncode($fnwTitle).'\');</script>';
            }

            $sql = "UPDATE `_article` SET `viewCount` = `viewCount` + 1 WHERE `title` = '$fnwTitle'";
            $result = mysqli_query($conn, $sql);
        ?>
    </body>
</html>
