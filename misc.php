<?php

    function rps_win($cpu, $me){
        if($cpu == '가위'){
            if($me == '바위'){
                $isWin = TRUE;
            }else{
                $isWin = FALSE;
            }
        }else{
            if($cpu == '바위'){
                if($me == '보'){
                    $isWin = TRUE;
                }else{
                    $isWin = FALSE;
                }
            }elseif($cpu == '보'){
                if($me == '가위'){
                    $isWin = TRUE;
                }else{
                    $isWin = FALSE;
                }
            }
        }

        if($cpu == $me){
            return '무승부';
        }elseif($isWin == FALSE){
            return '패배';
        }else{
            return '승리';
        }
    }
if($_GET['miscmode'] !== 'board'){
    echo '<main>
    <div class="flex">
        <section class="hidMob">
        </section>
        <section id="mainSec" class="half">';
}
        switch ($_GET['miscmode']) {
            case 'ranking':
                echo '<hr>&nbsp;<a href="/misc" class="lager"><i class="icofont-rouble"></i> 포인트 랭킹</a><br>
                &nbsp;<span class="subInfo">열심히 활동해 순위권에 들어보세요!</span><hr>
                    <table class="list full black noGray">
                        <thead>
                            <tr>
                                <th style="width:6em">&nbsp;등수</th>
                                <th>&nbsp;이름</th>
                            </tr>
                        </thead>
                        <tbody>';
                        $sql = "SELECT `point`, `id`, `name`, `userIntro` FROM `_account` WHERE `siteBan` = 0 and `point` > 300 and `id` NOT LIKE 'admin' ORDER BY `point` DESC LIMIT 50";
                        $result = mysqli_query($conn, $sql);
                        $i = 1;
                        while($row = mysqli_fetch_assoc($result)){
                            
                            if(strlen($row['userIntro']) > 100){
                                $cont = mb_substr($row['userIntro'], 0, 96).'..';
                            }else{
                                $cont = $row['userIntro'];
                            }

                            echo '<tr>
                                <td>'.$i.'</td>
                                <td><a href="/u/'.$row['id'].'"><b>'.$row['name'].'</b><br>
                                <span class="muted"> ( '.$row['point'].' ⓟ) / '.$cont.'</span></a></td>
                            </tr>';
                            $i++;
                        }
                        echo '</tbody>
                    </table>';
                break;
            case 'hall_of_shame':
                echo '<hr>&nbsp;<a href="/misc" class="lager"><i class="icofont-ban"></i> 불명예의 전당</a><br>
                &nbsp;<span class="subInfo">사이트 전체에서 차단당했거나 기록이 말소된 이용자들의 목록입니다.</span><hr>
                    <table class="list full black noGray">
                        <thead>
                            <tr>
                                <th>&nbsp;이름</th>
                            </tr>
                        </thead>
                        <tbody>';
                        $sql = "SELECT `id`, `name`, `userIntro` FROM `_account` WHERE `siteBan` > 0 ORDER BY `at` DESC LIMIT 100";
                        $result = mysqli_query($conn, $sql);
                        while($row = mysqli_fetch_assoc($result)){
                            
                            if(strlen($row['userIntro']) > 100){
                                $cont = mb_substr($row['userIntro'], 0, 96).'..';
                            }else{
                                $cont = $row['userIntro'];
                            }

                            echo '<tr>
                                <td><a href="/u/'.$row['id'].'"><b>'.$row['name'].'</b><br>
                                <span class="muted">'.$cont.'</span></a></td>
                            </tr>';
                        }
                        echo '</tbody>
                    </table>';
                break;
            case 'manageCenter':
                $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
                $result = mysqli_query($conn, $sql);
                $iA = mysqli_fetch_assoc($result);
                    if(!$iA['isAdmin']){
                        echo '이 사용자는 가입되지 않았습니다.';
                        break;
                    }
                echo '<hr>&nbsp;<a href="/misc" class="lager"><i class="icofont-ban"></i> 관리 센터</a><br>
                &nbsp;<span class="subInfo">아이피나 아이디를 직접 입력해 차단할 수 있습니다.</span><hr>
                <form method="post" action="/ban_page.php">
                <input type="text" placeholder="ip 주소 입력, ipv6 가능." name="ip" value="'.filt($_GET['miscplus'], 'htm').'">
                <red><b>주의!</b> 격리조치시 되돌리지 못합니다.</red>
                <button type="submit" class="error full">격리 조치</button>
                </form>';
                break;
            case 'nstat':
                $isMain = TRUE;
                include 'sub/nstat.php';
                break;
            case 'adv':
                echo '<hr>&nbsp;<a href="/misc" class="lager"><i class="icofont-interface"></i> 광고 목록</a><br>
                &nbsp;<span class="subInfo">30일 이내에 등록된 광고들의 목록입니다.</span><a class="right" style="font-size:.75em" href="/adv">광고 등록</a><hr>
                    <table class="list full black noGray">
                        <thead>
                            <tr>
                                <th>&nbsp;일반 광고</th>
                            </tr>
                        </thead>
                        <tbody>';
                        $sql = "SELECT * FROM `_ad` WHERE `type` = 'USER_ADVER' and `at` > DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY `ad` ORDER BY `at` DESC LIMIT 30";
                        $result = mysqli_query($conn, $sql);
                        while($row = mysqli_fetch_assoc($result)){
                            
                            if(strlen($row['ad']) > 100){
                                $cont = mb_substr($row['ad'], 0, 96).'..';
                            }else{
                                $cont = $row['ad'];
                            }

                            echo '<tr>
                                <td><a href="/u/'.$row['id'].'"><b>'.$row['name'].'</b></a><br>
                                <a href="'.$row['link'].'" style="color:gray">'.$cont.'</a></td>
                            </tr>';
                        }
                        echo '</tbody>
                    </table>
                    <table class="list full black noGray">
                        <thead>
                            <tr>
                                <th>&nbsp;후원 광고</th>
                            </tr>
                        </thead>
                        <tbody>';
                        $sql = "SELECT * FROM `_ad` WHERE `type` = 'PUB_S_ADVT' and `at` > DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY `ad` ORDER BY `at` DESC LIMIT 30";
                        $result = mysqli_query($conn, $sql);
                        while($row = mysqli_fetch_assoc($result)){
                            
                            if(strlen($row['ad']) > 100){
                                $cont = mb_substr($row['ad'], 0, 96).'..';
                            }else{
                                $cont = $row['ad'];
                            }

                            echo '<tr>
                            <td><a href="/u/'.$row['id'].'"><b>'.$row['name'].'</b></a><br>
                            <a href="'.$row['link'].'" style="color:gray">'.$cont.'</a></td>
                        </tr>';
                        }
                        echo '</tbody>
                    </table>';
                break;
            case 'anonwrite':
                if(strstr($ip, ':')){
                    die('ipv6 대역은 비회원 작성이 불가능합니다.');
                }
                if(date('H') >= 22 or date('H') <= 17){
                    echo '익명 글 작성은 오후 6시부터 오후 10시 까지만 가능합니다.<br>
                    <a href="/register">회원가입</a> 후 제약 없이 작성 가능.';
                    break;
                }
                $sqls = "SELECT * FROM `_ipban` WHERE `ip` = '$ip'";
                $results = mysqli_query($conn, $sqls);
                if(mysqli_num_rows($results) > 0){
                    echo '부적절한 이용으로 인해 차단됨!'; break;
                }
                if(empty($_SESSION['fnAnonNick']) and $_SESSION['fnAnonNick'] != '0'){
                    $nickname = '비회원_'.GenStr(5);
                    $_SESSION['fnAnonNick'] = $nickname;
                }else{
                    $nickname = $_SESSION['fnAnonNick'];
                }
                echo '<input type="checkbox" id="anonwriten">
                <article class="card" style="text-align:center;">
                    <p>1분당 최대 1개씩만 작성 바람.</p>
                    <label for="anonwriten" class="close">×</a>
                </article>';
                echo '<form method="post" action="/sub/anonwrite.php"><h3 style="display:inline">비회원 글쓰기</h3> <span class="muted">(자유 게시판)</span>';
                echo '<hr><input type="text" name="title" style="border:none" maxlength="20" placeholder="제목"><hr>';
                echo '<input type="text" name="nickname" style="border:none;font-size:0.75em" maxlength="10" placeholder="닉네임" value="'.$nickname.'"><hr>';
                echo '<textarea name="content" style="border:none;height:7em" maxlength="1000" placeholder="내용 (500자 이내)"></textarea><hr>
                <div style="height:3em"><button class="right success">등록</button></div></form>수정 및 삭제가 불가능하오니 신중하게 작성하세요.<br>모든 책임은 작성자에게 있으며 대한민국 법률이 적용됨.<br>';
                echo '<a href="/register">회원가입</a> 후 글을 쓸 경우 부가기능을 적용할 수 있고, 내용을 길게 적을 수 있음.';
                break;
            case 'owner_only':
                echo '<hr>&nbsp;<a href="/misc" class="lager"><i class="icofont-castle"></i> 소유주 전용 게시판 목록</a><br>
                &nbsp;<span class="subInfo">소유주만 글을 쓸 수 있는, 개인용 게시판들입니다.</span><hr>
                <input type="text" id="boardSearch" onkeyup="boardSearch()" placeholder="게시판 검색">
                    <table class="list full black noGray">
                        <thead>
                            <tr>
                                <th>&nbsp;이름</th>
                            </tr>
                        </thead>
                        <tbody>';
                        $sql = "SELECT `id`, `name`, `slug`, `title`, `boardIntro` FROM `_board` WHERE `type` = 'OWNER_ONLY' ORDER BY `subs` DESC LIMIT 100";
                        $result = mysqli_query($conn, $sql);
                        while($row = mysqli_fetch_assoc($result)){
                            echo '<tr>
                                <td class="boardName muted"><a href="/b>'.$row['slug'].'"><b>'.$row['title'].'</b><br>
                                <a style="color:green" href="/u/'.$row['id'].'">@'.$row['name'].'</a> / '.$row['boardIntro'].'</a></td>
                            </tr>';
                        }
                        echo '</tbody>
                    </table>'."
                    <script>
                    function boardSearch(){
                        var query = document.querySelector('#boardSearch').value;
                        document.querySelectorAll('.boardName').forEach(element => {
                            if(query == ''){
                                element.parentElement.style.display = '';
                            }else if(element.innerHTML.search(query) < 1){
                                element.parentElement.style.display = 'none';
                            }else{
                                element.parentElement.style.display = '';
                            }
                        });
                    }
                </script>";
                break;
            case 'vindicate':
                echo '<hr>&nbsp;<a href="/misc" class="lager"><i class="icofont-file-text"></i> 차단 소명하기</a><br>
                &nbsp;<span class="subInfo">사이트 전체 차단자만 이용 바랍니다.</span><hr>';
                if(!empty($id) and $id != '0'){
                    $sql = "SELECT `siteBan` FROM `_account` WHERE `id` = '$id'";
                    $result = mysqli_query($conn, $sql);
                    $sB = mysqli_fetch_assoc($result);
                    if($sB['siteBan'] == 1){
                        echo '<form action="/php/vindicate.php" method="post">
                            <hr>
                                <input type="text" name="t" placeholder="제목" style="border:none">
                            <hr>
                            <textarea name="c" placeholder="차단이 해제되어야 하는 이유" style="border:none"></textarea>
                            <hr>
                            <span class="subInfo">차단 소명은 수정이 불가능하며, 단 2회만 작성 가능합니다.<br>차단 소명 이유는 200자 이내로 기술하십시오.</span>
                            <button type="submit" class="warning full">소명하기</button>
                        </form>';
                    }elseif($sB['siteBan'] > 1){
                        echo '차단 소명 불가.<br>
                        다른 커뮤니티는 아직 귀하께 열려있습니다.<br>
                        <a href="https://www.google.com/search?q=%EC%BB%A4%EB%AE%A4%EB%8B%88%ED%8B%B0+%EC%82%AC%EC%9D%B4%ED%8A%B8+%EB%AA%A8%EC%9D%8C">Google 검색결과 보기</a>';
                    }
                }
                    echo '<table class="list full black noGray">
                        <thead>
                            <tr>
                                <th>&nbsp;이름</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                <b>관리자</b><br>
                                <span class="muted">
                                <b>차단 소명 처리시, 크게 3가지 사항을 고려합니다.</b>
                                <br>1. 전체 차단 사유가 정당하지 않은가?
                                <br>2. 차단 사유가 정당하지만, 충분히 해당 사유를 반성하였는가?
                                <br>3. 차단을 해제해도 사이트에 피해를 끼치지 않을 마음가짐을 갖추었는가?</span>
                                </td>
                            </tr>';
                        $sql = "SELECT `id`, `name`, `value`, `reason` FROM `_othFunc` WHERE `type` = 'VINDICATE_' ORDER BY `at` DESC LIMIT 20";
                        $result = mysqli_query($conn, $sql);
                        while($row = mysqli_fetch_assoc($result)){
                            
                            if(strlen($row['reason']) > 250){
                                $cont = mb_substr($row['reason'], 0, 248).'..';
                            }else{
                                $cont = $row['reason'];
                            }

                            echo '<tr>
                                <td><a href="/u/'.$row['id'].'"><b>'.$row['name'].'</b><br>
                                <span class="muted"><b>'.$row['value'].'</b><br>'.$cont.'</span></a></td>
                            </tr>';
                        }
                        echo '</tbody>
                    </table>';
                break;
            case 'point':
                echo '<hr>&nbsp;<a href="/misc" class="lager"><i class="icofont-rouble"></i> 포인트 주기</a><br>
                &nbsp;<span class="subInfo">다른 이용자에게 포인트를 넘겨줄 수 있습니다.</span><hr>';
                if(!empty($id) and $id != '0'){
                    $sql = "SELECT `point` FROM `_account` WHERE `id` = '$id'";
                    $result = mysqli_query($conn, $sql);
                    $pt = mysqli_fetch_assoc($result);
                    if($pt['point'] > 300){
                        $ptm = $pt['point'] - 100;
                        echo '<form action="/php/point.php" method="post">
                            <hr>
                                <input type="text" name="t" placeholder="받을 사람 ID" value="'.$_GET['miscplus'].'" style="border:none">
                            <hr>
                                <input type="number" min="300" max="'.$ptm.'" name="v" placeholder="보낼 포인트" style="border:none">
                            <hr>
                            <span class="subInfo">아이디를 정확하게 입력하십시오! 수수료는 없습니다.</span>
                            <red style="font-size:0.7em">현재 잔액보다 더 많은 금액을 보내지 마세요!</red>
                            <button type="submit" class="warning full">입금하기</button>
                        </form>';
                    }else{
                        echo '300ⓟ 이상의 금액이 필요합니다.';
                    }
                }
                echo '<hr>
                <p align="center">현재 잔액 : '.$pt['point'].'ⓟ</p><hr>';
                    echo '<table class="list full black noGray">
                        <thead>
                            <tr>
                                <th>최근 거래내역</th>
                            </tr>
                        </thead>
                        <tbody>';
                        $sql = "SELECT `id`, `name`, `value`, `reason`, `target` FROM `_othFunc` WHERE `type` = 'POINT_GIVE' ORDER BY `at` DESC LIMIT 20";
                        $result = mysqli_query($conn, $sql);
                        while($row = mysqli_fetch_assoc($result)){
                            echo '<tr>
                                <td><a href="/u/'.$row['id'].'">'.$row['name'].'</a> > <a href="/u/'.$row['target'].'">'.$row['reason'].'</a><br>
                                <span class="muted">( '.$row['value'].' ⓟ )<br></span></td>
                            </tr>';
                        }
                        echo '</tbody>
                    </table>';
                break;

            case 'rps_game':
                echo '<hr>&nbsp;<a href="/misc" class="lager"><i class="icofont-hand-power"></i> 가위바위보</a><br>
                &nbsp;<span class="subInfo">이길때마다 2배의 포인트를 획득할 수 있습니다.</span><hr>';
                if(!empty($id) and $id != '0'){
                    echo '<input type="checkbox" id="game_warning">
                    <article class="card" style="text-align:center;">
                        <p>과도한 도박은 심각한 부작용을 초래할 수 있습니다.</p>
                        <label for="game_warning" class="close">×</a>
                    </article>';

                    $sql = "SELECT `point` FROM `_account` WHERE `id` = '$id'";
                    $result = mysqli_query($conn, $sql);
                    $pt = mysqli_fetch_assoc($result);
                    if($pt['point'] >= 600){
                        $ptm = $pt['point'] - 100;
                        echo '<form method="post">
                            <div class="flex">
                                <p align="center"> <button style="width:100%;height:200px" formaction="/php/rps.php?p=1" class="error">가위</button> </p>
                                <p align="center"> <button style="width:100%;height:200px" formaction="/php/rps.php?p=2" class="">바위</button> </p>
                                <p align="center"> <button style="width:100%;height:200px" formaction="/php/rps.php?p=3" class="success">보</button> </p>
                            </div>
                            <hr>
                                <input type="number" min="300" max="5000" name="v" placeholder="걸어볼 포인트" style="border:none" required>
                            <hr>
                            <span class="subInfo">2배로 얻거나 잃으며, 최대 5,000ⓟ 까지 걸 수 있습니다.</span><br>
                            <span class="subInfo">FNBase는 국내법을 준수하며, 이 사이트의 재화는 실제 현금으로 반출되지 않습니다.</span><br><br>
                        </form>';
                    }else{
                        echo '600ⓟ 이상의 금액이 필요합니다.';
                    }
                }else{
                    echo '로그인이 필요합니다...';
                }
                    echo '<hr>
                    <p align="center">현재 잔액 : '.$pt['point'].'ⓟ</p>
                    <hr>
                    <table class="list full black noGray">
                        <thead>
                            <tr>
                                <th>최근 게임 내역</th>
                            </tr>
                        </thead>
                        <tbody>';
                        $sql = "SELECT `id`, `name`, `value`, `reason`, `target`, `at` FROM `_othFunc` WHERE `type` = 'POINT_RPSG' ORDER BY `at` DESC LIMIT 20";
                        $result = mysqli_query($conn, $sql);
                        while($row = mysqli_fetch_assoc($result)){
                            echo '<tr>
                                <td><a href="/u/'.$row['id'].'">'.$row['name'].'</a> <span class="subInfo">('.get_timeFlies($row['at']).')</span><br>
                                <span class="muted">'.$row['value'].' ⓟ - <b>'.rps_win($row['target'], $row['reason']).
                                '</b> ( CPU : '.$row['target'].' | '.$row['name'].' : '.$row['reason'].' )</span></td>
                            </tr>';
                        }
                        echo '</tbody>
                    </table>';
                break;

            case 'ready_shoot':
                echo '<hr>&nbsp;<a href="/misc" class="lager"><i class="icofont-penalty-card"></i> 즉석복권</a><br>
                &nbsp;<span class="subInfo">최대 500,000ⓟ 획득 가능</span><hr>';
                if(!empty($id) and $id != '0'){
                    if(!empty($_COOKIE['fnGameRpsR']) and $_COOKIE['fnGameRpsR'] != '0'){
                        echo $_COOKIE['fnGameRpsP'];
                    }
                    echo '<input type="checkbox" id="game_warning">
                    <article class="card" style="text-align:center;">
                        <p>과도한 도박은 심각한 부작용을 초래할 수 있습니다.</p>
                        <label for="game_warning" class="close">×</a>
                    </article>';
                
                $sql = "SELECT * FROM `_othFunc` WHERE `at` > curdate() and `type` = 'READYSHOOT' and `id` = '$id'";
                $result = mysqli_query($conn, $sql);
                if(mysqli_num_rows($result) === 0){
                    $sql = "SELECT `point` FROM `_account` WHERE `id` = '$id'";
                    $result = mysqli_query($conn, $sql);
                    $pt = mysqli_fetch_assoc($result);
                    if($pt['point'] > 1000){
                        $ptm = $pt['point'] - 100;
                        echo '<form method="post">
                            <button style="width:100%;height:200px" formaction="/php/lotto.php" class="error">1,000포인트를 소모하여<br>복권 구매</button>
                            <span class="subInfo">1일 1회 구매할 수 있습니다.</span><br>
                            <span class="subInfo">FNBase는 국내법을 준수하며, 이 사이트의 재화는 실제 현금으로 반출되지 않습니다.</span>
                        </form>';
                    }else{
                        echo '1000ⓟ 이상의 금액이 필요합니다.';
                    }
                }else{
                    $sql = "SELECT `point` FROM `_account` WHERE `id` = '$id'";
                    $result = mysqli_query($conn, $sql);
                    $pt = mysqli_fetch_assoc($result);
                    echo '<form>
                        <button style="width:100%;height:200px" class="error" disabled>1,000포인트를 소모하여<br>복권 구매</button>
                        <span class="subInfo">1일 1회 구매할 수 있습니다.</span><br>
                        <span class="subInfo">FNBase는 국내법을 준수하며, 이 사이트의 재화는 실제 현금으로 반출되지 않습니다.</span>
                    </form>';
                }
                }else{
                    echo '로그인이 필요합니다...';
                }
                    echo '<hr>
                    <p align="center">현재 잔액 : '.$pt['point'].'ⓟ</p>
                    <hr>
                    <table class="list full black noGray">
                        <thead>
                            <tr>
                                <th>최근 추첨 내역</th>
                            </tr>
                        </thead>
                        <tbody>';
                        $sql = "SELECT `id`, `name`, `value`, `reason`, `target`, `at` FROM `_othFunc` WHERE `type` = 'READYSHOOT' ORDER BY `at` DESC LIMIT 20";
                        $result = mysqli_query($conn, $sql);
                        while($row = mysqli_fetch_assoc($result)){
                            if($row['value'] <= 1000){
                                echo '<tr>';
                            }else{
                                echo '<tr style="background:yellow">';
                            }
                            echo '
                                <td><a href="/u/'.$row['id'].'">'.$row['name'].'</a> <span class="subInfo">('.get_timeFlies($row['at']).')</span><br>
                                <span class="muted">'.$row['value'].' ⓟ ('.$row['reason'].')</span></td>
                            </tr>';
                        }
                        echo '</tbody>
                    </table>';
                break;

            case 'tags':
                $lsPg = filt($_GET['tagspage'], '123');
                $lsPgN = 10; #1페이지에 표시될 글 수
                    if(empty($lsPg) and $lsPg != '0'){
                        $limit = '0, '.$lsPgN;
                        $lsPg = 1;
                    }else{
                        $b = $lsPg * $lsPgN - $lsPgN;
                        $limit = $b.', '.$lsPgN;
                    }
                $limit = 'LIMIT '.$limit;
                $lsBoard = filt($_GET['miscboard'], 'abc');
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
                $kpr = $lsBoard['keeper'];
                $kcd = $lsBoard['kicked'];
                $relate = $lsBoard['related'];
                $boardType = $lsBoard['type'];
                $lsNotice = $lsBoard['subIntro'];
                $alNotice = $lsBoard['notice'];
            
                if(!empty($lsBoard['icon']) and $lsBoard['icon'] != '0'){
                    $boardIcon = '<i class="icofont-'.$lsBoard['icon'].'"></i> ';
                }

                $tgMode = $_GET['tagsmode'];
                switch ($tgMode) {
                    case 'tags':
                        $tgName = filt($_GET['tagsname'], '영한');
                        $sqlW = "WHERE `board` = '$board' and `category` = '$tgName'";
                        break;
                    
                    default:
                        $tgName = '없음';
                        $sqlW = "WHERE `board` = '$board'";
                        break;
                }

                echo '<hr>&nbsp;<a href="/misc" class="lager">'.$boardIcon.$boardName.'<span class="muted">#'.$tgName.'</span></a><br>
                &nbsp;<span class="subInfo">'.$boardIntro.'</span><hr>';
                $sql = "SELECT * FROM `_content` $sqlW ORDER BY `at` DESC $limit";
                $result = mysqli_query($conn, $sql);
                echo '<table class="list full">
                <thead>
                    <tr>
                        <th width="10%" class="hidMob">&nbsp;종류</th>
                        <th width="50%">&nbsp;제목</th>
                        <th width="40%">&nbsp;정보</th>
                    </tr>
                </thead>
                <tbody>';
                while($row = mysqli_fetch_assoc($result)){ #일반 글 조회
                    $time = get_timeFlies($row['at']);

                    echo '<tr>';
                    echo '<td class="hidMob muted">'.$row['category'].'</td>';
                    echo '<td class="black"><a href="/b/'.$row['board'].'/'.$row['num'].'-'.$lsPg.'">'.$row['title'].'</a>
                    <green class="little">['.$row['commentCount'].']</green>';
                    if(!empty($row['staffOnly']) and $row['staffOnly'] != '0'){
                        echo '<i class="icofont-lock"></i>';
                    }
                    echo '</td>';
                    echo '<td class="infoList"><i class="icofont-user-alt-7"></i>
                    <a class="muted" href="/u/'.$row['id'].'_'.$board.'">'.$row['name'].'</a><h-d><br></h-d>';
                    echo ' <i class="icofont-clock-time"></i> '.$time.'</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
                echo '<!-- 페이지 이동 --><br>
                <div class="center">
                    <div class="pagination">
                        <a href="/b/'.$board.'/'.$tgMode.'_'.$tgName.'-1">&laquo;</a>';

                        $sql = "SELECT COUNT(`num`) as `cnt` FROM `_content` $sqlW";

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
                                echo '<a class="active" href="/b/'.$board.'/'.$tgMode.'_'.$tgName.'-'.$lsPgStart.'">'.$lsPgStart.'</a>';
                            }else{
                                echo '<a href="/b/'.$board.'/'.$tgMode.'_'.$tgName.'-'.$lsPgStart.'">'.$lsPgStart.'</a>';
                            }
                            if($i == 5){
                            break;
                            }
                            $lsPgStart++;
                            $i++;
                        }

                        echo '<a href="/b/'.$board.'/'.$tgMode.'_'.$tgName.'-'.$lsPgEnd.'">&raquo;</a>';
                    echo '</div>
                </div>';
                break;

            case 'upload':
                if(!$sB['canUpload']){
                    die('업로드 권한 없음');
                }
                echo '<span class="lager"><i class="icofont-upload"></i> 이미지 업로드</span>
                <hr>
                <br>
                <form enctype="multipart/form-data" action="/php/upload.php" method="post">
                    <input type="file" name="myfile">
                    <br>
                    <span class="subInfo">".png, .jpg/.jpeg, .webp" 파일만 업로드 가능합니다.</span><br>
                    <span class="subInfo">2MB 미만으로 올려주세요.</span>
                    <input type="submit" style="width:100%" value="업로드하기">
                    <span class="subInfo">2MB를 초과하거나, 지원하지 않는 파일 형식인가요?.</span>
                    <a href="https://ko.imgbb.com/" class="button" style="background:green;width:100%">imgBB 사용</a>
                    <br>
                <br>
                <span class="lager"><i class="icofont-file-jpg"></i> 이미지 사용법</span><br>
                    <span class="muted">
                    이미지는 거창할 것 없이 <strong>주소만 붙여넣으면 됩니다.</strong><br>
                    글과 댓글 모두 사용할 수 있으며, 자동으로 처리되므로 HTML 태그가 필요 없습니다.
                    </span>
                </form>';
                break;

            case 'attendance':
                echo '<script>document.title = "출석 체크"</script>
                <hr> &nbsp;<span class="lager"><i class="icofont-checked"></i> 출석 체크</span>
                <hr>';
                $sql = "SELECT * FROM `_othFunc` WHERE `type` = 'ATTENDANCE' ORDER BY `at` DESC LIMIT 20";
                $result = mysqli_query($conn, $sql);
                echo '<table class="list full">
                <thead>
                    <tr>
                        <th>&nbsp;이름</th>
                        <th>&nbsp;획득</th>
                    </tr>
                </thead>
                <tbody>';
                while($row = mysqli_fetch_assoc($result)){ #출석 조회
                    $time = get_timeFlies($row['at']);

                    if($row['value'] == 100){
                        echo '<tr>';
                    }else{
                        echo '<tr style="background:yellow">';
                    }
                    echo '<td class="infoList">
                    <a style="font-weight:700;color:black" href="/u%3E'.$row['id'].'">'.$row['name'].'</a><br>';
                    echo ' <i class="icofont-clock-time"></i> '.$row['at'].'<h-d><br></h-d><h-m> </h-m>('.$time.')</td>';
                    echo '<td>'.$row['value'].' ⓟ</td>';
                    echo '</tr>';
                }
                if(mysqli_num_rows($result) < 1){
                    echo '<tr><td>아직 출석한 사람이 없습니다!</td><td></td></tr>';
                }
                echo '</tbody></table>';
                $sql = "SELECT * FROM `_othFunc` WHERE `at` > curdate() and `type` = 'ATTENDANCE' and `id` = '$id'";
                $result = mysqli_query($conn, $sql);
                if(mysqli_num_rows($result) == 1){
                    echo '<button class="full" disabled><i class="icofont-checked"></i> 출석 완료!</button>';
                }else{
                    echo '<a href="/php/attendance.php" class="button full"><i class="icofont-check"></i> 출석하기</a>';
                }
                echo '<hr><span class="muted">출석시 기본적으로 100포인트를 획득하며, 매일 0시에 초기화됩니다.<br>
                30분의 1의 확률로 최대 5000포인트, 6분의 1의 확률로 최대 500포인트를 획득할 수 있습니다.</span>';
                break;
            case 'board':
                echo '<main>
                <div class="flex">
                    <section id="mainSec" class="half black noGray listMain">
                                <hr>&nbsp;<a href="/misc" class="lager"><i class="icofont-listine-dots"></i> 게시판 목록</a><br>
                                    &nbsp;<span class="subInfo">모든 게시판의 목록입니다.</span>
                                    <a href="/mkBoard" style="float:right;color:#0074d9;text-decoration:none;font-size:0.7em">게시판 만들기</a><hr>
                                        <input type="text" id="boardSearch" onkeyup="boardSearch()" placeholder="게시판 검색">
                                        <table class="list full">
                                            <thead>
                                                <tr>
                                                    <th style="width:6em">&nbsp;종류</th>
                                                    <th>&nbsp;이름</th>
                                                </tr>
                                            </thead>
                                            <tbody>';
                                $sql = "SELECT * FROM `_board` WHERE `type` NOT IN ('_READ_ONLY', '__DISABLED', 'AUTO_GENER', 'OWNER_ONLY') ORDER BY `subs` DESC";
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
                                            $boardTypeStyle = 'class="label" style="background:purple;color:white"';
                                            break;
                                    }
                                            echo '<tr>
                                                <td>'.$boardType.'</td>
                                                <td class="boardName"><a href="/b%3E'.$row['slug'].'"><b>'.$row['title'].'</b><br>
                                                <span class="muted">'.$row['boardIntro'].'</span></a></td>
                                            </tr>';
                                }
                                echo '</tbody></table></section>';
                                ?>
                                        <script>
                                            function boardSearch(){
                                                var query = document.querySelector('#boardSearch').value;
                                                document.querySelectorAll('.boardName').forEach(element => {
                                                    if(query == ''){
                                                        element.parentElement.style.display = '';
                                                    }else if(element.innerHTML.search(query) < 1){
                                                        element.parentElement.style.display = 'none';
                                                    }else{
                                                        element.parentElement.style.display = '';
                                                    }
                                                });
                                            }
                                        </script>
                                <?php
                break;
                
            default:
                echo '<hr>&nbsp;<a href="/misc" class="lager"><i class="icofont-interface"></i> 기타 페이지</a><br>
                &nbsp;<span class="subInfo">계속 추가될 예정입니다. 필요하신게 있다면 <a href="/b>maint">운영실</a>에서 건의해주세요.</span><hr>
                    <table class="list full black noGray">
                        <thead>
                            <tr>
                                <th style="width:6em">&nbsp;종류</th>
                                <th>&nbsp;이름</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>순위</td>
                                <td><a href="/misc>ranking"><b>포인트 랭킹</b><br>
                                <span class="muted">활동 포인트가 많은 순서대로 이용자들을 보여줍니다.</span></a></td>
                            </tr>
                            <tr>
                                <td>게임</td>
                                <td><a href="/misc>rps_game"><b>가위바위보</b><br>
                                <span class="muted">포인트를 걸고 2배로 불려보세요!</span></a></td>
                            </tr>
                            <tr>
                                <td>게임</td>
                                <td><a href="/misc>ready_shoot"><b>즉석복권</b><br>
                                <span class="muted">최대 500,000ⓟ! 1일 1회 구매 가능.</span></a></td>
                            </tr>
                            <tr>
                                <td>기능</td>
                                <td><a href="/misc>point"><b>포인트 주기</b><br>
                                <span class="muted">다른 이용자에게 포인트를 넘겨줄 수 있습니다.</span></a></td>
                            </tr>
                            <tr>
                                <td>목록</td>
                                <td><a href="/misc>hall_of_shame"><b>불명예의 전당</b><br>
                                <span class="muted">사이트 전체 차단당한 이용자들의 목록입니다.</span></a></td>
                            </tr>
                            <tr>
                                <td>목록</td>
                                <td><a href="/misc>vindicate"><b>차단 소명</b><br>
                                <span class="muted">억울하게 차단당하셨나요? 결백을 증명해보세요.</span></a></td>
                            </tr>
                            <tr>
                                <td>목록</td>
                                <td><a href="/misc>adv"><b>광고 목록</b><br>
                                <span class="muted">30일 이내에 등록된 광고들의 목록입니다.</span></a></td>
                            </tr>
                            <tr>
                            <td>목록</td>
                            <td><a href="/misc>board"><b>게시판 목록</b><br>
                            <span class="muted">개인용 게시판을 제외한 게시판들의 목록입니다.</span></a></td>
                        </tr>
                        <tr>
                            <td>목록</td>
                            <td><a href="/misc>owner_only"><b>개인용 게시판 목록</b><br>
                            <span class="muted">개인용 게시판의 목록입니다.</span></a></td>
                        </tr>
                        </tbody>
                    </table>';
                break;
        }
        echo '</section>
        <aside class="hidMob"></aside>
    </div></main>';
?>