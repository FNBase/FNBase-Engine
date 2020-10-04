<?php
if(!file_exists('setting.php')){
    require 'install/index.php';
    exit;
}
require_once 'setting.php';
require_once 'func.php';

error_reporting(E_ERROR);

$idTemp_1 = filt($_GET['b'], 'abc');
$idTemp_2 = filt($_GET['n'], '123');
$idTemp_3 = filt($_GET['mode'], 'abc');
$idTemp_4 = filt($_GET['board'], 'abc');
$idAlert = filt($_GET['alert'], 'abc');

if(empty($idTemp_1) and $idTemp_1 != '0'){ #게시판 값이 비어있음
    $isBoard = FALSE;
}elseif(!empty($idTemp_2) and $idTemp_2 != '0'){ #글 번호 있음
    $lsBoard = $idTemp_1;
    $pgNumber = $idTemp_2;
    $isBoard = TRUE;
    $isPage = TRUE;
}elseif(!empty($idTemp_1) and $idTemp_1 != '0'){ #게시판 값만 있음
    $lsBoard = $idTemp_1;
    $isBoard = TRUE;
    $isPage = FALSE;
}

if(!$isBoard){
    if(!empty($idTemp_3) and $idTemp_3 != '0'){ #모드 값이 있음
        $idPage = $idTemp_3;
        if($idTemp_4){
            $lsBoard = $idTemp_4;
        }else{
            $lsBoard = 'recent';
        }
    }else{ #아무 값도 없음
        $idPage = 'list';
        $lsBoard = 'recent';
        $isMain = TRUE;
    }
}elseif($isPage){
    $idPage = 'page';
}else{
    $idPage = 'list';
}

if(!empty($id) and $id != '0'){
    $sql = "SELECT `siteBan`, `canUpload` FROM `_account` WHERE `id` = '$id'";
    $result = mysqli_query($conn, $sql);
    $sB = mysqli_fetch_assoc($result);
    if($sB['siteBan'] >= 1){
        if($_GET['miscmode'] !== 'vindicate'){
            die('<script>location.href = \'banned.php\'</script>');
        }
    }

    $sql = "SELECT * FROM `_userSet` WHERE `id` = '$id'";
    $result = mysqli_query($conn, $sql);
    $uS = mysqli_fetch_assoc($result);
    if($uS['editor'] == 's' && $idPage == 'write' || $uS['editor'] == 's' && $idPage == 'edit'){
        $lsHead = '<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.16/dist/summernote-lite.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.16/dist/summernote-lite.min.js"></script>';
    }elseif($uS['editor'] == 'c' && $idPage == 'write' || $uS['editor'] == 'c' && $idPage == 'edit'){
        $lsHead = "<script src=\"/editor/ckeditor5/build/ckeditor.js\"></script>";
    }

    if(!empty($uS['homepage']) && $isMain){
        $lsBoard = $uS['homepage'];
    }
}

$sql = "SELECT `ip` FROM `_ipban` WHERE `ip` = '$ip'";
$result = mysqli_query($conn, $sql);
if(mysqli_num_rows($result) > 0){
    if($_GET['miscmode'] !== 'vindicate'){
        die('<script>location.href = \'banned.php\'</script>');
    }
}

    $yn = mt_rand(0,10);
    switch ($yn) {
        case 0:
        case 1:
            $isEmpty = TRUE;
            break;
        case 2:
            $sql = "SELECT count(*) as `cnt` FROM `_content` WHERE `rate` NOT LIKE 'R' and `staffOnly` IS NOT NULL and `voteCount_Up` > 10 and `at` > DATE_SUB(NOW(), INTERVAL 21 DAY)";
            $res = mysqli_query($conn, $sql);
            $res = mysqli_fetch_assoc($res);
            $cnt = $res['cnt'] - 1;
            $n = mt_rand(0, $cnt);
            $tnLabel = '추천';

            if($cnt < 0){
                $isEmpty = TRUE;
            }else{
                $sql = "SELECT `title`, `num` FROM `_content` WHERE `rate` NOT LIKE 'R' and `staffOnly` IS NOT NULL and `voteCount_Up` > 10 and `at` > DATE_SUB(NOW(), INTERVAL 21 DAY)";
                $res = mysqli_query($conn, $sql);
                $res = mysqli_fetch_assoc($res);

                    $tnHref = '/b>recent>'.$res['num'];
                    $tnText = $res['title'];
            }
            break;
        case 3:
            $sql = "SELECT count(*) as `cnt` FROM `_content` WHERE `rate` NOT LIKE 'R' and `staffOnly` IS NOT NULL and `viewCount` > 1000 and `at` > DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $res = mysqli_query($conn, $sql);
            $res = mysqli_fetch_assoc($res);
            $cnt = $res['cnt'] - 1;
            $n = mt_rand(0, $cnt);
            $tnLabel = '인기';

            if($cnt < 0){
                $isEmpty = TRUE;
            }else{
                $sql = "SELECT `title`, `num` FROM `_content` WHERE `rate` NOT LIKE 'R' and `staffOnly` IS NOT NULL and `viewCount` > 500 and `at` > DATE_SUB(NOW(), INTERVAL 7 DAY)";
                $res = mysqli_query($conn, $sql);
                $res = mysqli_fetch_assoc($res);

                    $tnHref = '/b>recent>'.$res['num'];
                    $tnText = $res['title'];
            }
            break;
        default:
            $sql = "SELECT COUNT(DISTINCT(`ad`)) as `cnt` FROM c180test.`_ad` WHERE `at` > DATE_SUB(NOW(), INTERVAL 3 DAY) and `type` = 'USER_ADVER'";
            $res = mysqli_query($conn, $sql);
            $res = mysqli_fetch_assoc($res);
            $cnt = $res['cnt'] - 1;
            $n = mt_rand(0, $cnt);
            $tnLabel = '광고';

            if($cnt < 0){
                $isEmpty = TRUE;
            }else{
                $sql = "SELECT `ad`, `link` FROM `_ad` WHERE `at` > DATE_SUB(NOW(), INTERVAL 3 DAY) and `type` = 'USER_ADVER' and `ad` IS NOT NULL GROUP BY `ad` ORDER BY `at` DESC LIMIT $n, 1";
                $res = mysqli_query($conn, $sql);
                $res = mysqli_fetch_assoc($res);

                    $tnHref = $res['link'];
                    $tnText = $res['ad'];
            }
    }
    if($isEmpty){
        $sql = "SELECT COUNT(*) as `cnt` FROM `_ad` WHERE `at` > DATE_SUB(NOW(), INTERVAL 14 DAY) and `type` = 'PUB_S_ADVT'";
        $res = mysqli_query($conn, $sql);
        $res = mysqli_fetch_assoc($res);
        $cnt = $res['cnt'] - 1;
        $n = mt_rand(0, $cnt);
        $tnLabel = '광고';

        if($cnt < 0){
            $isEmpty = TRUE;
        }else{
            $sql = "SELECT `ad`, `link` FROM `_ad` WHERE `at` > DATE_SUB(NOW(), INTERVAL 14 DAY) and `type` = 'PUB_S_ADVT' and `ad` IS NOT NULL ORDER BY `at` DESC LIMIT $n, 1";
            $res = mysqli_query($conn, $sql);
            $res = mysqli_fetch_assoc($res);

                $tnHref = $res['link'];
                $tnText = $res['ad'];
        }
    }
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
                    scrollbar-color: gainsboro transparent;
                }
                h-m {
                    display: none;
                }
                .hidMob {
                    display:none;
                }
                .brand-r h-d {
                    font-size: 0.7em;
                }
            }
            @media (min-width: 1900px) {
                html {
                    scrollbar-width: thin;
                    scrollbar-color: gainsboro transparent;
                }
                h-d {
                    display: none;
                }
            }
            html { overflow-y:scroll; word-break: break-word }
        </style>
        <link rel="stylesheet" href="/icofont/icofont.min.css">
        <link rel="stylesheet" href="/default.css">
        <link rel="stylesheet" href="/picnic.css">
        <link rel="shortcut icon" href="<?=$fnFab?>">
        <link rel="manifest" href="/manifest.webmanifest">
        <?=$fnPHead.$lsHead?>
    </head>
    <body style="background:<?=$fnBColor?>">
        <a id="pgUp"></a>
        <!-- 상단바 #6149ad -->
        <header>
            <nav class="nav" style="position:static;background:<?=$fnPColor?>">
                <a href="/main" class="brand">
                    <span id="siteTitle" style="color:#fff"><?=$fnTitle?></span>
                </a>
                <a href="/sublist" class="brand-r">
                    <span style="color:#fff"><i class="icofont-folder"></i><h-m> 구독 게시판</h-m><h-d> 구독</h-d></span>
                </a>
                <a href="/board" class="brand-r">
                    <span style="color:#fff"><i class="icofont-listine-dots"></i><h-m> 게시판 목록</h-m><h-d> 목록</h-d></span>
                </a>
                <a href="/wiki" class="brand-r">
                    <span style="color:#fff"><i class="icofont-institution"></i><h-m> 위키</h-m><h-d> 위키</h-d></span>
                </a>
                <?php
                    if($_SESSION['fnUserId']){
                        echo '<div class="menu">
                            <label for="userModal"><img height="40" id="myGravatar" src="'.get_gravatar($_SESSION['fnUserMail'], 40, 'identicon', 'pg').'"></label>
                        </div>';
                        $isLogged = TRUE;
                    }else{
                        echo '<div class="menu">
                            <a href="/login" class="button"><h-m><i class="icofont-login"></i> 로그인</h-m><h-d><i class="icofont-invisible"></i></h-d></a>
                        </div>';
                        $isLogged = FALSE;
                    }
                ?>
            </nav>
            <span style="color:white">
                <div id="topNoticeLine" style="background:<?=$fnSColor?>">
                    <a href="/misc>adv" class="label"><?=$tnLabel?></a> <a onclick="if(confirm('광고 링크로 이동하시겠습니까?')){location.href = '<?=$tnHref?>'}" style="color:white"><?=$tnText?></a>
                </div>
            </span>
        </header>
        <h-m><br></h-m>
    <!-- 페이지 로드 -->
        <?php
            switch ($idPage) {
                #페이지
                case 'list':
                    require 'list.php';
                    break;
                case 'page':
                    $lsShowPg = TRUE;
                    require 'list.php';
                    break;
                case 'signin':
                    require 'login.php';
                    break;
                case 'register':
                    require 'register.php';
                    break;
                case 'mailAuth':
                    $code = filt($_GET['code'], 'htm');
                    $sql = "SELECT `type` FROM `_auth` WHERE `value` = '$code'";
                    $result = mysqli_query($conn, $sql);
                    $mA = mysqli_fetch_assoc($result);
                    if($mA['type'] == 'password'){
                        require 'php/change_password.php';
                    }elseif($mA['type'] == 'complete'){
                        echo '이미 가입을 완료했습니다!';
                    }else{
                        require 'register2.php';
                    }
                    break;
                case 'comment':
                    include 'comment.php';
                    break;

                #직접표시
                case 'login':
                    if(!empty($_SESSION['fnUserId']) and $_SESSION['fnUserId'] != '0'){
                        echo '<script>history.back()</script>';
                    }
                    $lsPlus = '<article class="card">
                    <header>
                    <h3 class="muted"><i class="icofont-sign-in"></i> 로그인</h3>
                    </header>
                    <form method="post" action="/login.php">
                        <section class="content">
                            <label><input type="text" name="id" placeholder="아이디" required></label><br>
                            <label><input type="password" name="pw" placeholder="비밀번호" required></label>
                            <input type="hidden" name="from" value="/2/">
                        </section>
                        <footer>
                            <a href="/forgot_password">비밀번호를 잊으셨나요?</a>
                            <a href="/php/ip_login.php" style="float:right"><i class="icofont-checked"></i> ip 로그인</a>
                            <button class="button success full" type="submit">로그인</button>
                            <span class="subInfo">계정이 없으신가요? <a href="/register">만들어보세요!</a></span>
                        </footer>
                    </form>
                    </article>';
                    $lsBoard = 'recent';
                    include 'list.php';
                    break;

                case 'maint':
                    if(!$_SESSION['fnUserId']){
                        die('<script>alert("회원가입 후 이용해주세요.");location.href="/register"</script>');
                    }
                    $sql = "SELECT * FROM `_board` WHERE `id` = '$id'";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                            if(strtolower($id) !== strtolower($row['id'])){ #환경 설정 권한 여부
                                if(!preg_match('/(^|,)'.$id.'($|,)/', $row['keeper'])){
                                    die('권한 없음.');
                                }
                            }

                    $sql = "SELECT * FROM `_board` WHERE `slug` = '$lsBoard'";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    $lsPlus = '<article class="card">
                    <header>
                    <h3 class="muted"><i class="icofont-settings"></i> 환경 설정</h3>
                    </header>
                    <form method="post" action="/php/maint.php">
                        <section class="content">
                            <label>게시판 이름<input type="text" name="t" placeholder="게시판 이름" value="'.$row['title'].'" readonly></label><br>
                            <span class="subInfo">게시판 이름 변경은 사이트 관리자에게 문의해주세요.</span><br>
                            <label>게시판 별명<input type="text" name="nn" placeholder="예) 사회 게시판 -> 사회" value="'.$row['nickTitle'].'" required></label><br>
                            <span class="subInfo">2~4 글자 이내로 입력해주세요.</span><br>
                            <label>게시판 설명<input type="text" name="i" placeholder="이 게시판은 어떻습니다." value="'.$row['boardIntro'].'" required></label><br>
                            <span class="subInfo">필요한 내용만 간결하게 입력하시는 것을 권장합니다.</span><br>
                            <hr>
                            <label>연관 게시판<input type="text" name="r" placeholder="게시판 주소 입력" value="'.$row['related'].'"></label><br>
                            <span class="subInfo">쉼표로 구분해주세요. 3개 이내를 권장합니다.</span><br>
                            <label>상단 공지<input type="text" name="nt" placeholder="30자 이내 권장" value="'.$row['notice'].'"></label><br>
                            <span class="subInfo">단기적으로 중요하거나, 알리고 싶은 이벤트가 있으시다면 입력해주세요.</span><br>
                            <label>태그 설정<input type="text" name="tS" placeholder="60자 이내. 쉼표로 구분." value="'.$row['tagSet'].'"></label><br>
                            <span class="subInfo">글 분류(말머리)입니다. 따로 설정하지 않아도 소유주 및 관리인은 "공지" 태그를 이용할 수 있습니다.</span><br>
                            <hr>
                            <label>보조 관리인<input type="text" name="k" placeholder="아이디를 쉼표로 구분" value="'.$row['keeper'].'"></label>
                            <span class="subInfo">게시글 블라인드 및 등급 조정, 공지 지정 권한을 가집니다.</span><br>
                            <span class="subInfo">게시판 소유권 위임은 운영실에서 문의해주세요.</span>
                            <hr>
                            <label>종합 글 목록<select name="rct">
                            <option value="'.$row['rct'].'">선택해주세요.</option>
                            <option value="1">노출을 원함</option>
                            <option value="0">노출을 원하지 않음</option>
                            </select></label>
                            <hr>
                            <label>게시판 타입<select name="ty">
                            <option value="'.$row['type'].'">선택해주세요.</option>
                            <option value="PRIVAT_OPT">전체 공개</option>
                            <option value="CREAT_SOME">창작 공간</option>
                            <option value="OWNER_ONLY">소유주 전용</option>';
                            $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
                            $result = mysqli_query($conn, $sql);
                            $iA = mysqli_fetch_assoc($result);
                                if($iA['isAdmin']){
                                    $lsPlus .= '<option value="DIRECT_OPT">운영진 직영</option>';
                                }
                            $lsPlus .= '</select></label>
                            <br>
                            <span class="subInfo">전체 공개시 누구나 글을 쓸 수 있습니다. 소유주 전용 선택시 소유주만 가능합니다.</span><br>
                            <span class="subInfo">게시판 열람이나 추천 등은 금지할 수 없는 점 알려드립니다.</span>
                            <hr>
                            <label>게시판 아이콘<input type="text" name="e" placeholder="예) favourite" value="'.$row['icon'].'"></label>
                            <span class="subInfo"><a href="/icofont/demo.html" target="_blank">여기</a>서 원하시는 아이콘을 찾아보실 수 있습니다.</span>
                        </section>
                        <footer>
                            <button class="button full" type="submit">설정 저장</button>
                            <span class="subInfo">'.$fnTitle.'의 사설 게시판 관리 서비스 이용시 <a href="/b%3Emaint%3E213">사설 게시판 이용수칙</a>에 동의한 것으로 간주됩니다.</span>
                        </footer>';
                        if($lsBoard == 'trash'){
                            $lsPlus .= '<button class="error" formaction="/php/modifyCon.php?mode=T"><i class="icofont-bin"></i> 휴지통 비우기</button>
                            <input type="hidden" name="b" value="trash">';
                        }elseif($lsBoard == 'mafia'){
                            $lsPlus .= '<button class="error" formaction="/php/modifyCon.php?mode=T"><i class="icofont-error"></i> 기밀 해제</button>
                            <input type="hidden" name="b" value="mafia">';
                        }elseif($lsBoard == 'recent'){
                            if($fnType == 'board'){
                                $lsPlus .= '<button class="error" formaction="/php/modifyCon.php?mode=OFF"><i class="icofont-toggle-off"></i> 사이트 끄기</button>';
                            }else{
                                $lsPlus .= '<button class="error" formaction="/php/modifyCon.php?mode=ON"><i class="icofont-toggle-on"></i> 사이트 켜기</button>';
                            }
                        }elseif($lsBoard == '3'){
                            if($fnType == 'board'){
                                $lsPlus .= '<button class="error" formaction="/php/modifyCon.php?mode=OFF"><i class="icofont-toggle-off"></i> 사이트 끄기</button>';
                            }else{
                                $lsPlus .= '<button class="error" formaction="/php/modifyCon.php?mode=ON"><i class="icofont-toggle-on"></i> 사이트 켜기</button>';
                            }
                        }
                    echo '</form>
                    </article>';
                    include 'list.php';
                    break;

                case 'write':
                    if(!$_SESSION['fnUserId']){
                        die('<script>alert("회원가입 없이는 자유 게시판 에서만 글을 작성하실 수 있습니다.");location.href="/misc>anonwrite"</script>');
                    }else{
                        $_SESSION['fnUserId'] = $_SESSION['fnUserId'];
                        $_SESSION['fnUserName'] = $_SESSION['fnUserName'];
                        $_SESSION['fnUserMail'] = $_SESSION['fnUserMail'];
                    }
                    if($lsBoard == 'recent'){
                        die('<script>alert("종합 글 목록에는 글을 작성하실 수 없습니다.");location.href="/main"</script>');
                    }

                    if($uS['editor'] == NULL){
                        $lsEditor = '<textarea id="mainEditor" name="content" placeholder="내용 (기본 Textarea 사용중)" style="border:none;color:gray;height:12em"></textarea>';
                        $lsEditorS = '<hr><button onclick="notSubmit=false;" class="button full" style="background:green" type="submit">작성 완료</button>';
                    }elseif($uS['editor'] == 's'){
                        $lsEditor = '<span style="font-size:17px">
                        <textarea id="summernote" name="content"></textarea>'."
                        <script>
                        $('#summernote').summernote({
                            placeholder: '내용 (Summernote 에디터 사용중)',
                            tabsize: 2,
                            height: 400,
                            toolbar: [
                                ['font', ['bold', 'underline', 'strikethrough', 'superscript', 'subscript', 'italic', 'link', 'fontsize']],
                                ['clear', ['clear']],
                                ['color', ['color']],
                                ['style', ['style']],
                                ['para', ['ul', 'ol', 'paragraph', 'hr']],
                                ['table', ['table']],
                                ['view', ['codeview']],
                                ['do', ['undo', 'redo']]
                            ]
                        });
                        </script>
                        </span>";
                        $lsEditorPlusV = "document.querySelector('.note-editable').innerHTML += '<img src=\"'+data+'\" style=\"width:50%\"><br>';document.querySelector('.note-editable').click()";
                        $lsEditorS = '<hr><button onclick="notSubmit=false;" class="button full" style="background:green" type="submit">작성 완료</button>';
                    }elseif($uS['editor'] == 'q'){
                        $lsEditor = '<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
                        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

                        <div id="editor" style="height:15em;font-size:1em"></div>
                        <textarea id="txtAreaEditor" name="content" style="display:none"></textarea>
                        '."
                        <script>
                        var options = {
                            placeholder: '내용 (Quill 사용중)',
                            modules: {
                                toolbar: [
                                    [{ 'header': [1, 2, 3, false] }],
                                    ['bold', 'italic', 'underline', 'strike', { 'script': 'sub'}, { 'script': 'super' }, 'link'],
                                    [{ 'indent': '-1'}, { 'list': 'ordered'}, { 'list': 'bullet' }, { 'indent': '+1' }],
                                    ['background', 'color', 'align', 'video', 'blockquote', 'divider'],
                                    ['clean']
                                  ]
                            },
                            theme: 'snow'
                        };
                        var editor = new Quill('#editor', options);
                        function quillSubmit(){
                            document.querySelector('#txtAreaEditor').innerHTML = document.querySelector('.ql-editor').innerHTML;
                            document.querySelector('#contForm').submit();
                        }
                        </script>";
                        $lsEditorPlusV = "document.querySelector('.ql-editor').innerHTML += '<img src=\"'+data+'\" style=\"width:50%\"><br>';document.querySelector('.ql-editor').click()";
                        $lsEditorS = '<hr><button onclick="notSubmit=false;quillSubmit()" class="button full" style="background:green" type="button">작성 완료</button>';
                    }
                    if($sB['canUpload']){
                        $lsEditorPlus .= '<hr><form id="contentImage" enctype="multipart/form-data"><red style="font-size:0.7em">.png / .webp / .jpg / .jpeg 허용. 5MB 미만.</red>
                        <input type="file" name="myfile">
                        <button id="submitImage" class="full" type="button">이미지 업로드</button></form>'.
                        "<script>
                        const button = document.querySelector('#submitImage');
                        button.addEventListener('click', () => {
                        const form = new FormData(document.querySelector('#contentImage'));
                        const url = '/sub/fetchUpload.php'
                        const request = new Request(url, {
                            method: 'POST',
                            body: form
                        });
                        var imgSrc = null;
                        fetch(request)
                            .then(response => response.text())
                            .then(data => {
                                if(document.querySelector('#mainEditor') != null){
                                    document.querySelector('#mainEditor').innerHTML += data+' ';
                                }else{
                                    ".$lsEditorPlusV."
                                }
                            })

                        });
                        </script><hr>
                        ";
                    }
                    if($_GET['board'] == 'quiz'){
                        $Quiz = '<input type="text" name="answer" style="border:none;font-size:0.8em" placeholder="퀴즈 정답 (20자 이내)" maxlength="20"><hr>';
                        $Quiz .= '<input type="number" name="prize" style="border:none;font-size:0.8em" placeholder="정답 상금 (10만 포인트 이내)" min="300" max="100000"><hr>';
                    }
                    $lsPlus = '<article class="card">
                        <header>
                        <h3 class="muted"><i class="icofont-edit"></i> 글쓰기</h3>
                        </header>
                            <section class="content">
                                <hr>
                                <input type="text" name="title" placeholder="제목" style="border:none;color:gray">
                                <hr>
                                '.$Quiz.'
                                '.$lsEditor.$lsEditorS.'
                                <span class="subInfo">상단 우측의 \'부가 기능(보라색)\'을 눌러 더 많은 기능을 사용하실 수 있습니다.</span><br>
                                <span class="subInfo">링크 삽입: 유튜브/이미지 파일 - 링크만 입력 (처리 없이)</span><br>
                                <red style="font-size:0.7em">이미지 업로드: <a href="/b>maint">이미지 업로드 권한 요청</a>(<a href="https://iloveimg.com/ko">압축</a>) /
                                <a href="https://imgbb.com">외부 업로드</a></red>
                            </section>
                    </article>
                    </form>';
                    if($lsEditorPlus){
                        $lsPlus .= $lsEditorPlus;
                    }
                    $isEditor = TRUE;
                    include 'list.php';
                    break;

                case 'edit':
                    if(!$_SESSION['fnUserId']){
                        die('<script>alert("회원가입 후 이용해주세요.");location.href="/register"</script>');
                    }else{
                        $_SESSION['fnUserId'] = $_SESSION['fnUserId'];
                        $_SESSION['fnUserName'] = $_SESSION['fnUserName'];
                        $_SESSION['fnUserMail'] = $_SESSION['fnUserMail'];
                    }
                    if($lsBoard == 'recent'){
                        die('<script>alert("종합 글 목록에서는 글을 수정하실 수 없습니다.");location.href="/main"</script>');
                    }

                    $idTemp_5 = filt($_POST['n'], '123');
                    if($idTemp_5 == ''){
                        $idTemp_5 = $_SESSION['last'];
                    }else{
                        $_SESSION['last'] = $idTemp_5;
                    }
                    $sql = "SELECT * FROM `_content` WHERE `board` = '$lsBoard' and `num` = '$idTemp_5'";
                    $contResult = mysqli_query($conn, $sql);
                    if(mysqli_num_rows($contResult) == 1){
                        $edContent = mysqli_fetch_assoc($contResult);
                    }

                    if(strtolower($_SESSION['fnUserId']) !== strtolower($edContent['id'])){
                        die('아이디가 일치하지 않습니다.');
                    }

                    if($uS['editor'] == NULL){
                        $lsEditor = '<textarea id="mainEditor" name="content" placeholder="내용 (기본 Textarea 사용중)"  style="border:none;color:gray;height:12em">'.$edContent['content'].'</textarea>';
                        $lsEditorS = '<button onclick="notSubmit=false;" class="button full" style="background:green" type="submit">작성 완료</button>';
                    }elseif($uS['editor'] == 's'){
                        $lsEditor = '<span style="font-size:17px">
                        <textarea id="summernote" name="content">'.$edContent['content'].'</textarea>'."
                        <script>
                        $('#summernote').summernote({
                            placeholder: '내용 (Summernote 에디터 사용중)',
                            tabsize: 2,
                            height: 400,
                            toolbar: [
                                ['font', ['bold', 'underline', 'strikethrough', 'superscript', 'subscript', 'italic', 'link', 'fontsize']],
                                ['clear', ['clear']],
                                ['color', ['color']],
                                ['style', ['style']],
                                ['para', ['ul', 'ol', 'paragraph', 'hr']],
                                ['table', ['table']],
                                ['view', ['codeview']],
                                ['do', ['undo', 'redo']]
                            ]
                        });
                        </script>
                        </span>";
                        $lsEditorPlusV = "document.querySelector('.note-editable').innerHTML += '<img src=\"'+data+'\" style=\"width:50%\"><br>';document.querySelector('.note-editable').click()";
                        $lsEditorS = '<button onclick="notSubmit=false;" class="button full" style="background:green" type="submit">작성 완료</button>';
                    }elseif($uS['editor'] == 'q'){
                        $lsEditor = '<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
                        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

                        <div id="editor" style="height:15em;font-size:1em">'.$edContent['content'].'</div>
                        <textarea id="txtAreaEditor" name="content" style="display:none"></textarea>
                        '."
                        <script>
                        var options = {
                            placeholder: '내용 (Quill 사용중)',
                            modules: {
                                toolbar: [
                                    [{ 'header': [1, 2, 3, false] }],
                                    ['bold', 'italic', 'underline', 'strike', { 'script': 'sub'}, { 'script': 'super' }, 'link'],
                                    [{ 'indent': '-1'}, { 'list': 'ordered'}, { 'list': 'bullet' }, { 'indent': '+1' }],
                                    ['background', 'color', 'align', 'video', 'blockquote'],
                                    ['clean']
                                  ]
                            },
                            theme: 'snow'
                        };
                        var editor = new Quill('#editor', options);
                        function quillSubmit(){
                            document.querySelector('#txtAreaEditor').innerHTML = document.querySelector('.ql-editor').innerHTML;
                            document.querySelector('#contForm').submit();
                        }
                        </script>";
                        $lsEditorPlusV = "document.querySelector('.ql-editor').innerHTML += '<img src=\"'+data+'\" style=\"width:50%\"><br>';document.querySelector('.ql-editor').click()";
                        $lsEditorS = '<button onclick="notSubmit=false;quillSubmit()" class="button full" style="background:green" type="button">작성 완료</button>';
                    }
                    if($sB['canUpload']){
                        $lsEditorPlus .= '<hr><form id="contentImage" enctype="multipart/form-data"><red style="font-size:0.7em">.png / .webp / .jpg / .jpeg 허용. 5MB 미만.</red>
                        <input type="file" name="myfile">
                        <button id="submitImage" class="full" type="button">이미지 업로드</button></form>'.
                        "<script>
                        const button = document.querySelector('#submitImage');
                        button.addEventListener('click', () => {
                        const form = new FormData(document.querySelector('#contentImage'));
                        const url = '/sub/fetchUpload.php'
                        const request = new Request(url, {
                            method: 'POST',
                            body: form
                        });
                        var imgSrc = null;
                        fetch(request)
                            .then(response => response.text())
                            .then(data => {
                                if(document.querySelector('#mainEditor') != null){
                                    document.querySelector('#mainEditor').innerHTML += data+' ';
                                }else{
                                    $lsEditorPlusV
                                }
                            })

                        });
                        </script><hr>
                        ";
                    }
                    $lsPlus = '<article class="card">
                        <header>
                        <h3 class="muted"><i class="icofont-eraser"></i> 글 수정</h3>
                        </header>
                            <section class="content">
                                <hr>
                                <input type="text" name="title" placeholder="제목" style="border:none;color:gray" value="'.preg_replace('/<\/{0,1}red>/mu', '', $edContent['title']).'" required>
                                <hr>
                                '.$lsEditor.'
                                <hr>
                                '.$lsEditorS.'
                                <span class="subInfo">상단 우측의 \'부가 기능(보라색)\'을 눌러 더 많은 기능을 사용하실 수 있습니다.</span><br>
                                <span class="subInfo">링크 삽입: 유튜브/이미지 파일 - 링크만 입력 (처리 없이)</span><br>
                                <red style="font-size:0.7em">이미지 업로드: <a href="/b>maint">이미지 업로드 권한 요청</a>(<a href="https://iloveimg.com/ko">압축</a>) /
                                <a href="https://imgbb.com">외부 업로드</a></red>
                            </section>
                    </article></form>';
                    if($lsEditorPlus){
                        $lsPlus .= $lsEditorPlus;
                    }
                    $isEditor = TRUE;
                    include 'list.php';
                    break;

                case 'delete':
                    if(!$_SESSION['fnUserId']){
                        die('<script>alert("회원가입 후 이용해주세요.");location.href="/register"</script>');
                    }
                    if($lsBoard == 'recent'){
                        die('<script>alert("종합 글 목록에서는 글을 관리하실 수 없습니다.");location.href="/main"</script>');
                    }
                    $tit = filt($_POST['t'], 'htm');
                    $num = filt($_POST['n'], '123');
                    $lsPlus = '<form method="post" action="/save.php?e=dlt">
                        <article class="card">
                            <header>
                            <h3 class="muted"><i class="icofont-bin"></i> 글 삭제</h3>
                            </header>
                                <section class="content">
                                    <hr>
                                    <input type="text" name="title" placeholder="제목" style="border:none;color:gray" value="'.$tit.'" readonly>
                                    <hr>
                                        삭제한 게시글은 복구할 수 없습니다! 정말 삭제하시겠습니까?
                                </section>
                                <footer>
                                    <button class="button full" style="background:red" type="submit">삭제하기</button>
                                </footer>
                        </article>
                        <input type="hidden" name="n" value="'.$num.'">
                    </form>
                    ';
                    $isEditor = FALSE;
                    $isDelete = TRUE;
                    include 'list.php';
                    break;

                case 'password':
                    $lsPlus = '<article class="card">
                    <header>
                    <h3 class="muted"><i class="icofont-key"></i> 비밀번호 찾기</h3>
                    </header>
                    <form method="post" action="/php/find_password.php">
                        <section class="content">
                            <label><input type="email" name="mail" placeholder="이메일 주소" required></label><br>
                            <label><input type="text" name="id" placeholder="아이디" required></label>
                        </section>
                        <footer>
                            <button class="button full" type="submit">메일 보내기</button>
                            <span class="subInfo">등록된 이메일 주소와 아이디가 일치하지 않을 경우 아무 일도 일어나지 않으며, 아이디 찾기는 지원하지 않습니다.</span><br>
                            <span class="subInfo">이메일 발송이 느릴 수 있으나, 절대 이메일을 여러번 보내지 마세요! 아무리 기다려도 오지 않는다면 스팸메일함을 확인해보세요.</span>
                        </footer>
                    </form>
                    </article>';
                    include 'list.php';
                    break;

                case 'userInfo':
                    $uid = filt($_GET['u'], 'htm');
                    $unm = filt($_GET['u_name'], 'htm');
                    if(empty($uid) or $uid != '0' && !empty($unm) and $unm != '0'){
                        $sql = "SELECT `id` FROM `_account` WHERE `name` = '$unm'";
                        $result = mysqli_query($conn, $sql);
                        if(mysqli_num_rows($result) != 0){
                            $uIrow = mysqli_fetch_assoc($result);
                            $uid = $uIrow['id'];
                        }
                    }

                    $sql = "SELECT * FROM `_account` WHERE `id` = '$uid'";
                    $result = mysqli_query($conn, $sql);
                    if(mysqli_num_rows($result) != 0){
                        $uIrow = mysqli_fetch_assoc($result);
                    }else{
                        die('<script>alert("없는 사용자입니다.")</script>');
                    }

                    $sql = "SELECT COUNT(`num`) as `cnt` FROM `_content` WHERE `id` = '$uid'";
                    $result = mysqli_query($conn, $sql);
                    $aC = mysqli_fetch_assoc($result);

                    $sql = "SELECT COUNT(`num`) as `cnt` FROM `_comment` WHERE `id` = '$uid'";
                    $result = mysqli_query($conn, $sql);
                    $cC = mysqli_fetch_assoc($result);

                    $sql = "SELECT `at` FROM `_log` WHERE `id` = '$uid' ORDER BY `num` DESC LIMIT 1";
                    $result = mysqli_query($conn, $sql);
                    $lL = mysqli_fetch_assoc($result);

                    $infoUser = $uIrow['userIntro'];
                    $infoUser .= '<hr><span class="subInfo">작성 글 수 <green>'.$aC['cnt'].'</green> |
                        댓글 수 <green>'.$cC['cnt'].'</green> |
                        보유 포인트 <blue>'.$uIrow['point'].'</blue><br>
                        가입 '.$uIrow['at'].' / 마지막 접속 '.$lL['at'].'</span>
                    ';

                    $sql = "SELECT `num`, `title`, `at`, `board`, `commentCount` FROM `_content` WHERE `id` = '$uid' ORDER BY `num` DESC LIMIT 10";
                    $result = mysqli_query($conn, $sql);
                    if(mysqli_num_rows($result) != 0){
                        $articleUser = '<table class="full"><tbody>';
                        while($row = mysqli_fetch_assoc($result)){ #작성 글 조회
                            $time = explode(' ', $row['at']);

                            $articleUser .= '<tr>';
                            $articleUser .= '<td class="black"><a href="/b>'.$row['board'].'>'.$row['num'].'">'.$row['title'].'</a>
                            <green class="little">['.$row['commentCount'].']</green></td>';
                            $articleUser .= '<td class="subInfo"><i class="icofont-clock-time"></i><h-d> 작성 일시</h-d>
                            '.$time[0].' <h-d><br></h-d>'.$time[1].'</td>';
                            $articleUser .= '</tr>';
                        }
                        $articleUser .= '</tbody></table>';
                    }else{
                        $articleUser = '<span class="subInfo">작성한 글이 없습니다.</span>';
                    }

                    $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
                    $result = mysqli_query($conn, $sql);
                    $iA = mysqli_fetch_assoc($result);
                        if($iA['isAdmin']){
                            $isAdmin = TRUE;
                        }

                    if($uid == $_SESSION['fnUserId'] or $isAdmin){
                        $sql = "SELECT * FROM `_comment` WHERE `id` = '$uid' ORDER BY `num` DESC LIMIT 10";
                        $result = mysqli_query($conn, $sql);
                        if(mysqli_num_rows($result) != 0){
                            $commentUser = '<table class="full"><tbody>';
                            while($row = mysqli_fetch_assoc($result)){ #작성 댓글 조회
                                $time = explode(' ', $row['at']);

                                if(strlen($row['content']) > 100){
                                    $cont = mb_substr($row['content'], 0, 96).'..';
                                }else{
                                    $cont = $row['content'];
                                }

                                $commentUser .= '<tr>';
                                $commentUser .= '<td><a class="subInfo" href="/b>recent>'.$row['from'].'#cmt-'.$row['num'].'">'.$cont.'</a></td>';
                                $commentUser .= '<td class="subInfo"><i class="icofont-clock-time"></i><h-d> 작성 일시</h-d>
                                '.$time[0].' <h-d><br></h-d>'.$time[1].'</td>';
                                $commentUser .= '</tr>';
                            }
                            $commentUser .= '</tbody></table>';
                        }else{
                            $commentUser = '<span class="subInfo">작성한 댓글이 없습니다.</span>';
                        }
                    }

                    if(!empty($_SESSION['fnUserId']) and $_SESSION['fnUserId'] != '0'){
                        if($uid == $_SESSION['fnUserId']){
                            $sql = "SELECT * FROM `_userSet` WHERE `id` = '$uid'";
                            $result = mysqli_query($conn, $sql);
                            if(mysqli_num_rows($result) != 0){
                                $uSrow = mysqli_fetch_assoc($result);
                            }else{
                                die('<script>alert("없는 사용자입니다.")</script>');
                            }

                            $manageUser = '<br><h4>회원 정보 수정</h4>';
                            $manageUser .= '<form method="post" action="/save.php?e=usr">';
                            $manageUser .= '<label>닉네임 변경 <input type="text" name="title" value="'.$uIrow['name'].'"></label>';
                            $manageUser .= '<span class="subInfo">1000 포인트가 소모되며, 영문/국문/숫자 또는 공백 문자(_)를 20글자 이내로 입력해주세요.</span>';
                            $manageUser .= '<hr>';
                            $manageUser .= '<label>메일 주소 <span class="subInfo">(변경 불가)</span><input type="text" value="'.$uIrow['mail'].'" readonly></label>';
                            $manageUser .= '<span class="subInfo">프로필 사진 설정은 메일과 연동됩니다!</span><br>';
                            $manageUser .= '<span class="subInfo"><a href="http://ko.gravatar.com/">여기</a>에 위의 이메일 주소로 가입하고 프로필을 설정하세요.</span>';
                            $manageUser .= '<span class="subInfo">기본적으로 지원되지 않는 기능이며, 편의를 위해 제공됩니다.</span><br>';
                            $manageUser .= '<hr>';
                            $manageUser .= '<p><label>현재 비밀번호 <input type="password" name="password_old"></label></p>';
                            $manageUser .= '<p><label>바꿀 비밀번호 <input type="password" name="password_new"></label></p>';
                            $manageUser .= '<span class="subInfo">최소 6자리 이상, 유추하기 어려운 번호를 권장합니다.</span>';
                            $manageUser .= '<hr>';
                            $manageUser .= '<label>메인 페이지 설정<input type="text" name="homepage" value="'.$uSrow['homepage'].'"></label>';
                            $manageUser .= '<span class="subInfo">메인 페이지 접속 시, 입력하신 게시판으로 이동시켜드립니다.</span>';
                            $manageUser .= '<span class="subInfo">예시 : `maint` 입력 > 메인 페이지에서 운영실로 이동.</span>';
                            $manageUser .= '<hr>';
                            $manageUser .= '<p><label>자동 로그인 사용 여부 <select name="aL">';
                            if($uIrow['autoLogin'] === '1'){
                                $uITemp = ' selected';
                            }
                            $manageUser .= '<option value="no">안 함</option><option value="yes"'.$uITemp.'>함</option></select></label></p>';
                            $manageUser .= '<span class="subInfo">등록한 브라우저와 아이피를 이용해, 알아서 로그인 시켜줍니다.</span><br>';
                            $manageUser .= '<span class="subInfo">로그인 창에서 "<i class="icofont-checked"></i> ip 로그인"을 눌러 이용 가능합니다.';
                            $manageUser .= ' <strong>보안 상 심각한 위험이 있을 수 있으니 주의 바랍니다.</strong></span>';
                            $manageUser .= '<p>현재 로그인 정보로 고정<br>';
                            $manageUser .= '<a class="button warning" href="/php/c.php">'.$ip.'</a></p>';
                            $manageUser .= '<span class="subInfo">자동 로그인 사용 시 반드시 버튼을 눌러 저장해주세요!</span><br>';
                            $manageUser .= '<span class="subInfo">고정하신 정보는 다른 정보로 로그인 해도 변하지 않습니다.</span>';
                            $manageUser .= '<hr>';
                            $manageUser .= '<p><label>인기글 및 광고 미표시 <select name="hA">';
                            if($uSrow['hideAdv'] === '1'){
                                $uITemp = ' selected';
                            }else{
                                unset($uITemp);
                            }
                            $manageUser .= '<option value="no">안 함</option><option value="yes"'.$uITemp.'>함</option></select></label></p>';
                            $manageUser .= '<span class="subInfo">태블릿 등 화면 호환이 되지 않을 경우를 대비하여 만들어진 기능입니다.</span><br>';
                            $manageUser .= '<span class="subInfo">가급적 끄지 않으시는 것을 추천드립니다.</span>';
                            $manageUser .= '<hr>';
                            if($uSrow['listNum'] === NULL){
                                $uITemp = 10;
                            }else{
                                $uITemp = $uSrow['listNum'];
                            }
                            $manageUser .= '<p><label>1페이지 당 글 개수 <input type="number" min="5" max="50" name="listNum" value="'.$uITemp.'"></label></p>';
                            $manageUser .= '<span class="subInfo">기본 설정은 10개이며, 최대 50개까지 설정하실 수 있습니다.</span>';
                            $manageUser .= '<hr>';
                            $manageUser .= '<textarea name="content">'.$uIrow['userIntro'].'</textarea>';
                            $manageUser .= '<span class="subInfo">HTML 코드를 사용하실 수 없으며, 최대 250자까지 서술 가능합니다.</span>';
                            $manageUser .= '<hr>';
                            $manageUser .= '<p>회원 탈퇴<br><a class="button error" href="/quit">계정 삭제 페이지</a></p>';
                            $manageUser .= '<hr>';
                            $manageUser .= '<input type="hidden" name="b" value="'.$uid.'">';
                            $manageUser .= '<button type="submit" class="full">수정하기</button>';
                            $manageUser .= '<hr>';
                            $manageUser .= '</form>';

                            $disOpt = ' disabled';
                        }else{ #자신의 권한은 설정할 수 없음
                            $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
                            $result = mysqli_query($conn, $sql);
                            $iA = mysqli_fetch_assoc($result);
                                if($iA['isAdmin']){
                                    $manageUser = '<h4>권한 조정</h4>';
                                    $manageUser .= '<form method="post">';
                                        if($uIrow['siteBan'] == 1){
                                            $manageUser .= '<button class="outline" type="submit" formaction="/php/quarantine.php"><i class="icofont-gavel"></i> 완전 격리</button> ';
                                            $manageUser .= '<input type="hidden" name="target" value="'.$uid.'">';
                                            $manageUser .= '<button class="warning" type="submit" formaction="/save.php?e=manag&m=siteBan"><i class="icofont-gavel"></i> 전체 차단 해제</button> ';
                                            $manageUser .= '<input type="hidden" name="sn" value="0"><input type="hidden" name="title" value="'.$uid.'">';
                                        }elseif($uIrow['siteBan'] < 1){
                                            $manageUser .= '<button class="error" type="submit" formaction="/save.php?e=manag&m=siteBan"><i class="icofont-ban"></i> 사이트 전체 차단</button> ';
                                            $manageUser .= '<input type="hidden" name="sn" value="1"><input type="hidden" name="title" value="'.$uid.'">';
                                        }
                                        if($uIrow['canUpload'] == 1){
                                            $manageUser .= '<button class="warning" type="submit" formaction="/save.php?e=manag&m=canUpload"><i class="icofont-close-squared-alt"></i> 업로드 권한 회수</button>';
                                            $manageUser .= '<input type="hidden" name="sni" value="0"><input type="hidden" name="title" value="'.$uid.'">';
                                        }else{
                                            $manageUser .= '<button class="success" type="submit" formaction="/save.php?e=manag&m=canUpload"><i class="icofont-upload"></i> 업로드 권한 부여</button>';
                                            $manageUser .= '<input type="hidden" name="sni" value="1"><input type="hidden" name="title" value="'.$uid.'">';
                                        }
                                    $manageUser .= '<input type="hidden" name="content" value="something else">';
                                    $manageUser .= '</form><hr>';
                                }
                        }
                    }

                    if($uIrow['siteBan'] == 1){
                        $manageUser .= '<button class="error"><i class="icofont-ban"></i> 전체 차단된 이용자</button> ';
                    }elseif($uIrow['siteBan'] > 1){
                        $manageUser .= '<button disabled><i class="icofont-ban"></i> 추방된 이용자</button> ';
                    }

                    $lsPlus = '<article class="card">
                        <header>
                        <h3 class="muted"><i class="icofont-id"></i> 사용자 정보 - '.$uIrow['name'].'</h3>
                        </header>
                        <section class="content">
                            <h4>소개 및 정보</h4>'.$infoUser.'
                            <h4>작성한 게시글</h4>'.$articleUser;
                        if($uid == $_SESSION['fnUserId'] or $isAdmin){
                            $lsPlus .= '<h4>작성한 댓글</h4>'.$commentUser;
                        }
                            $lsPlus .= $manageUser.'
                        </section>
                        <footer><form action="/php/dis_none.php" method="post">';
                        $lsPlus .= '<a class="button warning full" href="/misc>point>'.$uid.'" type="submit"'.$disOpt.'><i class="icofont-rouble"></i> 포인트 주러가기</a>';
                        if(!preg_match('/'.$uid.'/', $uS['display_none'])){
                            $lsPlus .= '<button class="button error full" type="submit"'.$disOpt.'><i class="icofont-volume-mute"></i> 이 사용자 차단</button>';
                        }else{
                            $lsPlus .= '<button class="button warning full" type="submit"'.$disOpt.'><i class="icofont-volume-down"></i>
                            차단 해제</button>';
                        }
                            $lsPlus .= '<input type="hidden" name="i" value="'.$uid.'">
                            <span class="subInfo">차단시 이 사용자의 게시글이 보이지 않게 됩니다.</span>
                        </form></footer>
                    </article>';
                    include 'list.php';
                    break;

                case 'boardList':
                    echo '<main>
                        <div class="flex">
                            <section id="mainSec" class="half black noGray listMain">
                                <hr>&nbsp;<a class="lager"><i class="icofont-listine-dots"></i> 상위 게시판 목록</a><br>
                                    &nbsp;<span class="subInfo">구독자 수 기준 상위 게시판들입니다.</span>
                                    <a href="/mkBoard" style="float:right;color:#0074d9;text-decoration:none;font-size:0.7em">게시판 만들기</a><hr>
                                        <table class="list full">
                                            <thead>
                                                <tr>
                                                    <th style="width:6em">&nbsp;종류</th>
                                                    <th>&nbsp;이름</th>
                                                </tr>
                                            </thead>
                                            <tbody>';
                                $sql = "SELECT * FROM `_board` WHERE `type` NOT IN ('_READ_ONLY', '__DISABLED', 'AUTO_GENER', 'OWNER_ONLY') ORDER BY `subs` DESC LIMIT 8";
                                $result = mysqli_query($conn, $sql);
                                while($row = mysqli_fetch_assoc($result)){
                                    $boardType = $row['type'];
                                    switch ($boardType) { #게시판 종류
                                        case 'DIRECT_OPT':
                                            $boardType = '공설';
                                            break;
                                        case 'PRIVAT_OPT':
                                            $boardType = '사설';
                                            break;
                                        case 'CREAT_SOME':
                                            $boardType = '창작';
                                            break;
                                    }
                                            echo '<tr>
                                                <td>'.$boardType.'</td>
                                                <td><a href="/b%3E'.$row['slug'].'"><b>'.$row['title'].'</b><br>
                                                <span class="muted">'.$row['boardIntro'].'</span></a></td>
                                            </tr>';
                                }
                                            echo '<tr>
                                                <td>목록</td>
                                                <td><a href="/misc>owner_only"><mark><b>소유주 전용 게시판</b></mark><br>
                                                <span class="muted">개인용 게시판들의 목록입니다.</span></a></td>
                                            </tr>';
                                            echo '<tr>
                                                <td>목록</td>
                                                <td><a href="/misc>board"><mark><b>모든 공개 게시판</b></mark><br>
                                                <span class="muted">모든 공개 게시판들의 목록입니다.</span></a></td>
                                            </tr>';
                                            echo '</tbody>
                                        </table>
                                    <br><hr>&nbsp;<a class="lager"><i class="icofont-navigation-menu"></i> 특수 게시판</a><br>
                                        &nbsp;<span class="subInfo">특별한 기능을 하는 게시판입니다.</span><hr>
                                            <table class="list full">
                                                <thead>
                                                    <tr>
                                                        <th style="width:6em">&nbsp;종류</th>
                                                        <th>&nbsp;이름</th>
                                                    </tr>
                                                </thead>
                                                <tbody>';
                                    $sql = "SELECT * FROM `_board` WHERE `type` = 'AUTO_GENER' ORDER BY `subs` DESC";
                                    $result = mysqli_query($conn, $sql);
                                    while($row = mysqli_fetch_assoc($result)){
                                        $boardType = $row['nickTitle'];
                                                echo '<tr>
                                                    <td>'.$boardType.'</td>
                                                    <td><a href="/b%3E'.$row['slug'].'"><b>'.$row['title'].'</b><br>
                                                    <span class="muted">'.$row['boardIntro'].'</span></a></td>
                                                </tr>';
                                    }
                                                echo '</tbody>
                                            </table>
                                            <br>
                                    <br><hr>&nbsp;<a class="lager"><i class="icofont-plus-square"></i> 부가 기능</a><br>
                                        &nbsp;<span class="subInfo">다른 기능들을 찾고있나요? 확인해보세요.</span><hr>
                                            <table class="list full">
                                                <thead>
                                                    <tr>
                                                        <th style="width:6em">&nbsp;종류</th>
                                                        <th>&nbsp;이름</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>특수</td>
                                                        <td><a href="/emoticon"><b>FNBCon Store</b><br>
                                                        <span class="muted">FNBCon을 사고 파실 수 있습니다.</span></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td>특수</td>
                                                        <td><a href="/adv"><b>광고 등록</b><br>
                                                        <span class="muted">3일간 표시될 광고를 등록할 수 있습니다.</span></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td>특수</td>
                                                        <td><a href="/misc>attendance"><b><mark>출석 체크</mark></b><br>
                                                        <span class="muted">매일 최대 5,000 포인트를 획득 가능합니다.</span></a></td>
                                                    </tr>
                                                    ';
                                                    if($sB['canUpload']){
                                                    echo '<tr>
                                                        <td>특수</td>
                                                        <td><a href="/misc>upload"><b>이미지 업로드</b><br>
                                                        <span class="muted">이미지를 업로드 할 수 있습니다.</span></a></td>
                                                    </tr>';
                                                    }
                                                    echo '
                                                    <tr>
                                                        <td>목록</td>
                                                        <td><a href="/misc"><b>기타 페이지</b><br>
                                                        <span class="muted">포인트 랭킹, 포인트 게임, 차단 소명 등이 있습니다.</span></a></td>
                                                    </tr>
                                                </tbody>
                                            </table>';
                                echo '</section>';
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
    }?>
                                        </h-m>
                                    </aside>
                                <?php
                            echo '</div>
                        </main>';
                    break;

                case 'subList':
                    if(!$isLogged){
                        die('<script>location.href="/login"</script>');
                    }
                    echo '<main>
                        <div class="flex">
                            <section id="mainSec" class="half listMain">
                            <hr>&nbsp;<a class="lager"><i class="icofont-listine-dots"></i> 구독한 게시판 목록</a><br>
                            &nbsp;<span class="subInfo">현재 구독중인 게시판의 목록입니다.</span><hr>
                                <table class="list full">
                                    <thead>
                                        <tr>
                                        <th width="10%" class="hidMob">&nbsp;종류</th>
                                        <th width="65%">&nbsp;이름</th>
                                        <th width="25%">&nbsp;정보</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                                    $in = $uS['subs'];
                                    if(empty($in) and $in != '0'){
                                        echo '<tr><td></td><td>구독한 게시판이 없습니다.</td><td></td></tr>';
                                        echo '</tbody></table>';
                                    }else{
                                        $in = explode(',', $in);
                                        $in = array_map('trim', $in);
                                        if(count($in) == 1){
                                            $in = implode($in);
                                            $sql = "SELECT `num` FROM `_content` WHERE `board` = '$in' ORDER BY `at` DESC LIMIT 10";
                                        }else{
                                            $i = 0;
                                            foreach ($in as $in_v) {
                                                if($i !== 0){
                                                    $in_s .= ',';
                                                }
                                                $in_s .= "'".$in_v."'";
                                                $i++;
                                            }
                                            $sql = "SELECT `num` FROM `_content` WHERE `board` IN ($in_s) ORDER BY `at` DESC LIMIT 10";
                                        }
                                    }
                                    if(empty($in) and $in != '0'){
                                        echo '<tr><td></td><td>구독한 게시판이 없습니다.</td><td></td></tr>';
                                    }else{
                                        if(count($in) == 1){
                                            $sql = "SELECT * FROM `_board` WHERE `slug` = '$in' ORDER BY `at`";
                                        }else{
                                            $sql = "SELECT * FROM `_board` WHERE `slug` IN ($in_s) ORDER BY `at`";
                                        }
                                        $result = mysqli_query($conn, $sql);
                                        while($row = mysqli_fetch_assoc($result)){ #일반 글 조회
                                            $time = get_timeFlies($row['at']);

                                            $boardType = $row['type'];
                                            switch ($boardType) { #게시판 종류
                                                case 'DIRECT_OPT':
                                                    $boardType = '공설';
                                                    break;
                                                case 'PRIVAT_OPT':
                                                    $boardType = '사설';
                                                    break;
                                                case 'OWNER_ONLY':
                                                    $boardType = '개인';
                                                    break;
                                                case 'CREAT_SOME':
                                                    $boardType = '창작';
                                                    break;
                                                case 'AUTO_GENER':
                                                    $boardType = '자동';
                                                    break;
                                            }

                                            echo '<tr>';
                                            echo '<td class="hidMob">'.$boardType.'</td>';
                                            echo '<td class="black"><a href="/b/'.$row['slug'].'">'.$row['title'].'</a></td>';
                                            echo '<td><form method="post" action="/php/sub.php"><input type="hidden" name="board" value="'.$row['slug'].'">
                                            <button type="submit" class="error right"><i class="icofont-error"></i><h-m> 구독 취소</h-m></button></form></td>';
                                            echo '</tr>';
                                        }
                                        echo '</tbody></table>';
                                        echo '<a href="/feed" class="button full" style="background:gray">구독 피드로 가기</a>';
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
    }?>
                                    </h-m>
                                </aside>
                            <?php
                        echo '</div>
                    </main>';
                    break;
                case 'subFeed':
                    if(!$isLogged){
                        die('<script>location.href="/login"</script>');
                    }
                    echo '<main>
                        <div class="flex">
                            <section id="mainSec" class="half listMain">
                            <hr>&nbsp;<a class="lager"><i class="icofont-page"></i> 구독 글 목록</a><br>
                            &nbsp;<span class="subInfo">구독하신 게시판의 최신 글을 보여드립니다.</span><hr>
                                <table class="list full">';
                                    $in = $uS['subs'];
                                    if(empty($in) and $in != '0'){
                                        echo '<tr><td></td><td>구독한 게시판이 없습니다.</td><td></td></tr>';
                                    }else{
                                        $in = explode(',', $in);
                                        $in = array_map('trim', $in);
                                        if(count($in) == 1){
                                            $in = implode($in);
                                            $sql = "SELECT * FROM `_content` WHERE `board` = '$in' ORDER BY `at` DESC LIMIT 10";
                                        }else{
                                            $i = 0;
                                            foreach ($in as $in_v) {
                                                if($i !== 0){
                                                    $in_s .= ',';
                                                }
                                                $in_s .= "'".$in_v."'";
                                                $i++;
                                            }
                                            $sql = "SELECT * FROM `_content` WHERE `board` IN ($in_s) ORDER BY `at` DESC LIMIT 10";
                                        }
                                        $result = mysqli_query($conn, $sql);
                                        while($pgContent = mysqli_fetch_assoc($result)){ #구독 글 조회
                                            $time = get_timeFlies($row['at']);

                                            echo '<tr>';
                                                    $vc = $pgContent['viewCount'];
                                                    $uv = $pgContent['voteCount_Up'];
                                                    $dv = $pgContent['voteCount_Down'];
                                                    $cc = $pgContent['commentCount'];
                                                    $iE = $pgContent['isEdited'];
                                                    $eU = $pgContent['whoEdited'];
                                                    $cac = $uv - $dv;
                                                    if($cac == 0){
                                                        $em = '중립적';
                                                    }elseif($cac > 0){
                                                        $em = '<green>동의함</green>';
                                                    }elseif($cac > 10){
                                                        $em = '<blue>적극 동의</blue>';
                                                    }else{
                                                        $em = '<red>반대함</red>';
                                                    }
                                                    $link = '/b>'.$pgContent['board'].'>'.$pgContent['num'];
                                                    $so = $pgContent['staffOnly'];
                                                    $name = $_SESSION['fnUserName'];

                                                    if($so !== NULL){
                                                        if($_SESSION['fnUserId'] == NULL){
                                                            break;
                                                        }
                                                        if(!preg_match('/(^|,)'.$name.'($|,)/', $so)){
                                                            if(!$isAdmin){
                                                            break;
                                                            }
                                                        }
                                                    }
                                            ?>
                                            <div class="card">
                                                <header style="background:#f3f3f3;border-bottom:1px solid #e6e6e6">
                                                    <h3 id="title"><?=$pgContent['title']?></h3><br>
                                                    <span class="subInfo"><i class="icofont-user-alt-7"></i> <a class="muted" href="/u%3E<?=$pgContent['id']?>"><?=$pgContent['name']?></a>
                                                    <i class="icofont-clock-time"></i> <?=get_timeFlies($pgContent['at'])?>
                                                    | 반응 : <?=$em?> | 조회수 <green id="count"><?=$vc?></green> | 댓글 수 <blue><?=$cc?></blue>
                                                    </span>
                                                </header>
                                                <article class="mainCon" id="mainCon">
                                                    <p><?php
                                                    if($isWrong){
                                                        echo '글이 삭제되었거나, 주소가 잘못되었거나, 데이터베이스 연결 오류입니다.';
                                                    }else{
                                                        $cont = textAlter($pgContent['content'], 1);
                                                        echo nl2br($cont);
                                                    }
                                                    ?></p>
                                                </article>
                                                <footer>
                                                    <a href="<?=$link?>" class="button full" style="color:gray;background:inherit;border:1px solid gray;border-radius:3px">원글 보기</a>
                                                </footer>
                                            </div>
                                            <?php
                                            echo '</tr>';
                                        }
                                        echo '</tbody></table>';
                                    }
                                    echo '<a href="/sublist" class="button full" style="background:gray">구독 게시판 목록으로 가기</a>';
                                echo '</section>';
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
    ?>
                                    </h-m>
                                </aside>
                            <?php
                        echo '</div>
                    </main>';
                    break;

                case 'emoji':
                    $id = $_SESSION['fnUserId'];
                    if(empty($id) and $id != '0'){
                        echo '</section></article></section><aside class="hidMob"></aside></div></body></html>';
                        die('<script>alert("로그인이 필요합니다.");history.back()</script>');
                    }
                    if($_GET['folder']){
                        $f = filt($_GET['folder'], 'oth');
                        echo '<main>
                        <div class="flex">
                            <section class="hidMob">
                            </section>
                            <section id="mainSec" class="half">
                                <article class="card">
                                    <header>
                                        <h3><a href="/emoticon" style="color:#218470"><i class="icofont-simple-smile">
                                        </i> FNBCon Store</a> <span class="subInfo">> <span id="tbreplace">'.$f.'</span></span></h3>
                                        <a style="font-size:0.7em;float:right" href="/b%3Emaint%3E3518">등록하기</a>
                                    </header>
                                    <section>';
                                    $sql = "SELECT * FROM `_fnbcon` WHERE `folder` = '$f' ORDER BY `use` DESC";
                                    $result = mysqli_query($conn, $sql);
                                    $row = mysqli_fetch_assoc($result);
                                    echo '<script>document.getElementById(\'tbreplace\').innerHTML = \''.$row['title'].'\';</script>';
                                        echo '<div class="card comm">
                                            <div class="cimg">
                                                <img height="70" src="/fnbcon/'.$row['folder'].'/main.png">
                                            </div>
                                            <div class="card">
                                                <header>
                                                    <h4>'.$row['title'].'</h4> <h-d><br></h-d><span class="subInfo">'.$row['content'].'</span><br>
                                                    <span class="subInfo">';
                                                    if(empty($row['id']) and $row['id'] != '0'){
                                                        echo '<a class="muted"><i class="icofont-user-suited"></i> ';
                                                    }else{
                                                        echo '<a class="muted" href="/u/'.$row['id'].'"><i class="icofont-user-alt-7"></i> ';
                                                    }

                                                    echo $row['name'].'</a> / '.get_timeFlies($row['at']).'
                                                </header>
                                                <section>
                                                    <span id="ico_'.$row['folder'].'">';
                                                $i = 0;
                                                while($i < $row['count']){
                                                    $i++;
                                                    echo '<img height="125" src="/fnbcon/'.$row['folder'].'/icon_'.$i.'.'.$row['ext'].'"> ';
                                                }
                                                    echo '</span>
                                                </section>
                                                <footer>
                                                    <form method="post">';
                                                    $sql = "SELECT `fnbcon` FROM `_userSet` WHERE `id` = '$id'";
                                                    $result = mysqli_query($conn, $sql);
                                                    $uS = mysqli_fetch_assoc($result);
                                                    $uS = $uS['fnbcon'];
                                                    if(preg_match('/(^|,)'.$row['folder'].'($|,)/', $uS)){
                                                        echo '<button class="warning" type="submit"
                                                        formaction="/php/emoji.php?f='.$row['folder'].'"><i class="icofont-ui-rate-remove"></i> 사용안함</button>';
                                                    }else{
                                                        echo '<button class="button" type="submit"
                                                        formaction="/php/emoji.php?f='.$row['folder'].'"><i class="icofont-ui-rate-add"></i> 사용하기</button>';
                                                    }
                                                    echo'</form>
                                                </footer>
                                            </div>
                                        </div>
                                    </section>
                                    <footer>
                                        <span class="subInfo">위 이모티콘의 저작권은 원작자에게 있으며, 사이트 내 사용 외에 다른 목적으로 사용할 시 원작자의 허가를 받아야 합니다.</span><br>
                                        <span class="subInfo">FNBCon을 너무 많이 사용하실 경우 사이트 로딩 속도와 데이터 사용량이 급격히 증가할 수 있습니다.</span>
                                    </footer>
                                </article>
                            </section>
                            <aside class="hidMob"></aside>
                        </div>
                    </main>
                    ';
                    }else{
                    echo '<main>
                        <div class="flex">
                            <section class="hidMob">
                            </section>
                            <section id="mainSec" class="half">
                                <article class="card">
                                    <header>
                                        <h3 style="color:#218470"><i class="icofont-simple-smile"></i> FNBCon Store</h3>
                                        <a style="font-size:0.7em;float:right" href="/b%3Emaint%3E3518">등록하기</a>
                                    </header>
                                    <section>';
                                    if(empty($id) and $id != '0'){
                                        die('<script>alert("로그인이 반드시 필요한 서비스입니다.");history.back()</script>');
                                    }
                                    $sql = "SELECT `fnbcon` FROM `_userSet` WHERE `id` = '$id'";
                                    $result = mysqli_query($conn, $sql);
                                    $uS = mysqli_fetch_assoc($result);
                                    $uS = $uS['fnbcon'];

                                    $sql = "SELECT * FROM `_fnbcon` ORDER BY `use` DESC";
                                    $result = mysqli_query($conn, $sql);
                                    while($row = mysqli_fetch_assoc($result)){
                                        $emCTxt = FALSE;
                                        $emCBG = FALSE;
                                        if($row['cost'] !== '0'){
                                            $emCTxt = '<span class="subInfo"><red>*유료</red>('.$row['cost'].'ⓟ) </span>';
                                            $emCBG = ' style="background:yellow"';
                                        }
                                        echo '<div class="card comm"'.$emCBG.'>
                                            <div class="cimg">
                                                <img height="70" src="/fnbcon/'.$row['folder'].'/main.png">
                                            </div>
                                            <div class="card">
                                                <header>
                                                    <h4>'.$emCTxt.$row['title'].'</h4> <h-d><br></h-d><span class="subInfo">'.$row['content'].'</span><br>
                                                    <span class="subInfo">';
                                                    if(empty($row['id']) and $row['id'] != '0'){
                                                        echo '<a class="muted"><i class="icofont-user-suited"></i> ';
                                                    }else{
                                                        echo '<a class="muted" href="/u/'.$row['id'].'"><i class="icofont-user-alt-7"></i> ';
                                                    }

                                                    echo $row['name'].'</a> / '.get_timeFlies($row['at']).' / <a href="/emoticon>'.$row['folder'].'">이모티콘 전체 보기</a>
                                                </header>
                                                <section>
                                                    <span id="ico_'.$row['folder'].'">';
                                                $i = 0;
                                                while($i < $row['count']){
                                                    $i++;
                                                    echo '<img height="50" src="/fnbcon/'.$row['folder'].'/icon_'.$i.'.'.$row['ext'].'"> ';
                                                    if($i > 4){
                                                        $rc = $row['count'] - 5;
                                                        echo '<a href="/emoticon>'.$row['folder'].'">'.$rc.'개 더 보기</a>';
                                                        break;
                                                    }
                                                }
                                                    echo '</span>
                                                </section>
                                                <footer>
                                                    <form method="post">';
                                                    if(preg_match('/(^|,)'.$row['folder'].'($|,)/', $uS)){
                                                        echo '<button class="warning" type="submit"
                                                        formaction="/php/emoji.php?f='.$row['folder'].'"><i class="icofont-ui-rate-remove"></i> 사용안함</button>';
                                                    }else{
                                                        echo '<button class="button" type="submit"
                                                        formaction="/php/emoji.php?f='.$row['folder'].'"><i class="icofont-ui-rate-add"></i> 사용하기</button>';
                                                    }
                                                    echo'</form>
                                                </footer>
                                            </div>
                                        </div>';
                                    }
                                    echo '</section>
                                    <footer>
                                        <span class="subInfo">위 이모티콘의 저작권은 원작자에게 있으며, 사이트 내 사용 외에 다른 목적으로 사용할 시 원작자의 허가를 받아야 합니다.</span><br>
                                        <span class="subInfo">FNBCon을 너무 많이 사용하실 경우 사이트 로딩 속도와 데이터 사용량이 급격히 증가할 수 있습니다.</span>
                                    </footer>
                                </article>
                                <script>
                                    function fnbc(arg){
                                        if(document.getElementById(arg).style.display == \'none\'){
                                            var divsToHide = document.getElementsByClassName(\'ico\');
                                            for(var i = 0; i < divsToHide.length; i++){
                                                divsToHide[i].style.display = "none";
                                            }
                                            document.getElementById(arg).style.display = \'\';
                                        }else{
                                            var divsToHide = document.getElementsByClassName(\'ico\');
                                            for(var i = 0; i < divsToHide.length; i++){
                                                divsToHide[i].style.display = "none";
                                            }
                                        }
                                    }
                                </script>
                            </section>
                            <aside class="hidMob"></aside>
                        </div>
                    </main>
                    ';
                    }
                    break;

                case 'nofi':
                    $lsPlus = '<article class="card">
                    <header>
                    <h3 class="muted"><i class="icofont-notification"></i> 알림 센터</h3>
                    </header>
                    <form method="post" action="/php/notify.php">
                        <section class="content black">';
                if($isLogged){
                    $sql = "SELECT * FROM `_ment` WHERE `target` = '$id' and `isSuccess` = 0";
                    $result = mysqli_query($conn, $sql);
                    if(mysqli_num_rows($result) > 0){
                        while($row = mysqli_fetch_assoc($result)){
                            $i++;
                            if($row['type'] == 'NOFI_CMMNT'){
                                $lsPlus .= '<a href="/'.$row['value'].'_'.$row['num'].$row['cmt_id'].'">['.$row['name'].']님이 ['.$row['reason'].']에 댓글을 다셨습니다.</a><br>';
                            }elseif($row['type'] == 'NOFI_REPLY'){
                                $lsPlus .= '<a href="/'.$row['value'].'_'.$row['num'].$row['cmt_id'].'">['.$row['name'].']님이 ['.$row['reason'].']에 다신 댓글에 답글을 다셨습니다.</a><br>';
                            }elseif($row['type'] == 'NOFI_MENTN'){
                                $lsPlus .= '<a href="'.$row['value'].'_'.$row['num'].$row['cmt_id'].'">['.$row['name'].']님이 ['.$row['reason'].']글에서 부르셨습니다.</a><br>';
                            }elseif($row['type'] == 'QUIZ_ANSWR'){
                                $lsPlus .= '<a href="/'.$row['value'].'_'.$row['num'].'#discussBtm">['.$row['name'].']님이 퀴즈 정답을 맞추셨습니다.</a><br>';
                            }else{
                                $lsPlus .= '<a href="/'.$row['value'].'_'.$row['num'].'#discussBtm">['.$row['name'].']님이 위키 토론에 부르셨습니다.</a><br>';
                            }
                        }
                        $col = ' error"';
                    }else{
                        $lsPlus .= '새 알림이 없습니다.';
                        $col = '" disabled';
                    }
                    $lsPlus .= '</section>
                    <footer>
                        <button class="button full'.$col.' type="submit"><i class="icofont-bin"></i> 전체 삭제</button>
                    </footer>';
                }else{
                    $lsPlus .= '<a href="/login"><i class="icofont-sign-in"></i> 로그인</a>이 필요합니다.</section>';
                }
                    $lsPlus .= '</form>
                    </article>';
                    $lsBoard = 'recent';
                    include 'list.php';
                    break;
                case 'adv':
                    $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
                    $result = mysqli_query($conn, $sql);
                    $iA = mysqli_fetch_assoc($result);
                        if($iA['isAdmin']){
                            $advPs = '<select name="type">
                                <option value="none" selected>일반</option>
                                <option value="PUB">후원 광고</option>
                            </select>';
                        }
                    $lsPlus = '<article class="card">
                    <header>
                    <h3 class="muted"><i class="icofont-restaurant-menu"></i> 광고 등록</h3>
                    </header>
                    <form method="post" action="/php/ad.php">
                        <section class="content">
                            <label><input type="text" name="ad" placeholder="표시할 문구" required></label>
                            <label><input type="text" name="link" placeholder="이동할 링크" required></label>'.$advPs.'
                        </section>
                        <footer>
                            <button class="button full" type="submit">3일간 등록</button>
                            <span class="subInfo">등록시 <b>5000포인트가 소모</b>되며, 100자 이내로 기재 바랍니다.<br>
                            <b>일반 광고는 사이트 내부 사안 홍보 목적으로만 가능합니다! 상업적으로 이용하거나 내용 없는 글을 작성하지 마세요.<br>
                            광고는 일 3회만 등록 가능합니다. 다중 계정으로 등록할 경우 차단 대상입니다!</b></span>
                        </footer>
                    </form>
                    </article>';
                    include 'list.php';
                    break;
                case 'mkBoard':
                    if(empty($id) and $id != '0'){
                        die('<script>alert("로그인이 반드시 필요한 서비스입니다.");history.back()</script>');
                    }
                    $lsPlus = '<article class="card">
                    <header>
                    <h3 class="muted"><i class="icofont-plus-square"></i> 게시판 개설</h3>
                    </header>
                    <form method="post" action="/php/mkBoard.php">
                        <section class="content">
                            <label><input type="text" maxlength="50" name="slug" placeholder="게시판 아이디" required></label>
                            <span class="subInfo"><b>변경이 불가능합니다.</b> 영문 50글자 내로 겹치지 않게 적어주세요.</span><br>
                            <span class="subInfo"><b>예시)</b> maint</span><br>
                            <label><input type="text" maxlength="50" name="title" placeholder="게시판 이름" required></label>
                            <span class="subInfo">변경이 어려우니 신중하게 적어주세요.</span><br>
                            <span class="subInfo"><b>예시)</b> 운영 게시판</span><br>
                            <label><input type="text" maxlength="4" name="nickTitle" placeholder="게시판 별명" required></label>
                            <span class="subInfo">2~4글자 내로 적어주세요. 게시판의 별명입니다.</span><br>
                            <span class="subInfo"><b>예시)</b> 운영실</span><br>
                            <label><input type="text" maxlength="50" name="boardIntro" placeholder="게시판 설명" required></label>
                            <span class="subInfo">필요한 내용만 간결하게 적어주세요.</span><br>
                            <span class="subInfo">다른 기능이 필요하신가요? 개설 후 "환경 설정"을 이용해보세요.</span><br>
                        </section>
                        <footer>
                            <button class="button full" type="submit">게시판 개설</button>
                            <span class="subInfo">등록시 5000 포인트가 소모되며, 게시판 이용 수칙을 등록하셔야 합니다.</span><br>
                            <span class="subInfo">게시판 개설시, <a href="/b%3Emaint%3E213">사설 게시판 관리 서비스 이용 수칙</a>에 동의한 것으로 간주됩니다.</span>
                            <br><b>꼭 필요한 게시판인지 다시 한 번 확인해주세요.</b>
                        </footer>
                    </form>
                    </article>';
                    include 'list.php';
                    break;
                case 'userDelete':
                    if(empty($id) and $id != '0'){
                        die('<script>alert("로그인이 반드시 필요한 서비스입니다.");history.back()</script>');
                    }
                    $lsPlus = '<article class="card">
                    <header>
                    <h3 style="color:red"><i class="icofont-warning"></i> 계정 삭제</h3>
                    </header>
                    <form method="post" action="/php/quit.php">
                        <section class="content">
                            <label>본인 확인
                                <input name="password" type="password" placeholder="비밀번호 입력" required>
                            </label>
                            <br><br>
                            <red><b>정말 탈퇴하시겠습니까?</b></red><br>
                            탈퇴하실 경우 되돌릴 수 없습니다.
                        </section>
                        <footer>
                            <button class="button error full" type="submit">계정 삭제</button>
                            <span class="subInfo">게시글은 사라지지 않으며, <b>재가입 방지를 위해 이메일 등의 정보는 보존</b>됩니다.</span><br>
                            <span class="subInfo"><a href="/terms.html">이용 약관</a>에 따라 보존되는 항목 -
                            (회원이 제공한 아이디, 이메일 주소, 닉네임, 활동 기록, ip 주소)</span>
                        </footer>
                    </form>
                    </article>';
                    include 'list.php';
                    break;
                case 'misc':
                    require 'misc.php';
                    break;
            }
        ?>
    <hr>
    <!-- 로그인 모달 호출 -->
    <div class="modal">
        <input id="loginModal" type="checkbox" />
        <label for="loginModal" class="overlay"></label>
        <article>
            <header>
            <h3>로그인이 필요합니다!</h3>
            <label for="loginModal" class="close">&times;</label>
            </header>
            <section class="content">
            회원제 서비스를 이용하기 위해서는 로그인이 필요합니다.<br>
            <?=$fnTitle?> 계정이 없으시다면 가입해보세요! 3분도 채 걸리지 않습니다.
            </section>
            <footer>
            <a class="button" style="color: #fff;background-color: #6633FF;" href="/register">회원가입</a>
            <a href="/login" style="float:right" class="button">로그인</a>
            </footer>
        </article>
    </div>
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
                            <a class="button dangerous" href="/login.php?from='.$idPath.'">로그아웃</a>
                            </span>
                        </footer>
                    </form>
                </article>
            </div>
            ';
        }
    ?>
    </a>
    <!-- Top / Bottom -->
    <div id="pgUpDown">
        <a href="#pgUp"><i class="icofont-swoosh-up"></i></a>
        <a href="#pgDown"><i class="icofont-swoosh-down"></i></a>
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
        }else{
            echo '<span id="nofiBox"></span>';
        }
    }
    echo '</a>';
    ?>
    <!-- 하단바 -->
    <footer>
        <div class="flex">
            <div>
                <p class="right"><?=$fnPFooter?></p>
            </div>
            <div>
                <p class="left muted">
                    <a href="/w/이용%20안내" target="_blank">이용 안내</a>
                </p>
            </div>
        </div>
    </footer>
    <br>
    <a id="pgDown"></a>
    <script type="text/javascript" src="/board.js"></script>
  </body>
</html>
