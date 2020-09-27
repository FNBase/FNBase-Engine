<?php
    $fnMultiNum = 2;
    include_once 'setting.php';
    include_once 'func.php';
    $fnwTitle = filt(urldecode($_GET['title']), 'htm');
    include_once 'wiki_p.php';
    $fnwTitle = myUrlDecode($fnwTitle);

    $fnwTitle = documentRender($fnwTitle, TRUE);

    $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
    $result = mysqli_query($conn, $sql);
    $iA = mysqli_fetch_assoc($result);
    if ($iA['isAdmin']) {
        $isAdmin = TRUE;
    }

if($_GET['mode'] == 'view'){
    $num = filt($_GET['num'], 'htm');
    if(empty($num) and $num != '0'){
        die('번호가 비어있습니다.');
    }

    $sql = "SELECT `rev`, `comment`, `id`, `name`, `modify?`, `ACL` FROM `_history` WHERE `num` = '$num'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    if($row['modify?'] != ''){
        echo '<div style="border: 1px solid gainsboro;color:gray;border-radius:5px">'.$row['modify?'].'<br>
        <a href="/u/'.$row['id'].'" class="right muted">-- <i class="icofont-user-alt-7"></i> '.$row['name'].'</a><br></div>';
    }elseif($row['comment'] != ''){
        echo '<div style="border: 1px solid gainsboro;color:gray;border-radius:5px">'.$row['comment'].'<br>
        <a href="/u/'.$row['id'].'" class="right muted">-- <i class="icofont-user-alt-7"></i> '.$row['name'].'</a><br></div>';
    }
    $content = nl2br(documentRender($row['rev']));
    if ($row['ACL'] == 'admin') {
      echo '<strong><red>안내)</red> 이 판은 숨겨져 있습니다.</strong><br />';
      if ($isAdmin) {
        echo '<button class="dangerous" type="button" onclick="wikiHideConf(1)" style="font-size: .8em;"><i class="icofont-key"></i>복구하기</button><br />';
        echo preg_replace('/<br( \/)*>\n<hr>/m', '<hr>', preg_replace('/(src="|<hr>)(.*)<br( \/)*>/m', '$1$2', preg_replace('/<\/h(\d)><br \/>/m', '</h$1>', $content)));
      }
    }
    else {
      if ($isAdmin) {
        echo '<button class="dangerous" type="button" onclick="wikiHideConf(0)" style="font-size: .8em;"><i class="icofont-lock"></i>숨기기</button></br />';
      }
      echo preg_replace('/<br( \/)*>\n<hr>/m', '<hr>', preg_replace('/(src="|<hr>)(.*)<br( \/)*>/m', '$1$2', preg_replace('/<\/h(\d)><br \/>/m', '</h$1>', $content)));
    }
}elseif($_GET['mode'] == 'raw'){
    $num = filt($_GET['num'], 'htm');
    if(empty($num) and $num != '0'){
        die('번호가 비어있습니다.');
    }

    $sql = "SELECT `rev` FROM `_history` WHERE `num` = '$num'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    echo nl2br(htmlspecialchars($row['rev']));
}elseif($_GET['mode'] == 'diff'){
    $num = filt($_GET['num'], 'htm');
    if(empty($num) and $num != '0'){
        die('번호가 비어있습니다.');
    }

    $sql = "SELECT `rev` FROM `_history` WHERE `num` = '$num'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    echo '<link rel="stylesheet" type="text/css" href="/editor/jsdifflib/diffview.css"/>
	<script type="text/javascript" src="/editor/jsdifflib/diffview.js"></script>
    <script type="text/javascript" src="/editor/jsdifflib/difflib.js"></script>
    <script type="text/javascript">
        function diffUsingJS(viewType) {
            var byId = function (id) { return document.getElementById(id); },
                base = difflib.stringAsLines(byId("baseText").value),
                newtxt = difflib.stringAsLines(byId("newText").value),
                sm = new difflib.SequenceMatcher(base, newtxt),
                opcodes = sm.get_opcodes(),
                diffoutputdiv = byId("diffoutput"),
                contextSize = byId("contextSize").value;

            diffoutputdiv.innerHTML = "";
            contextSize = contextSize || null;

            diffoutputdiv.appendChild(diffview.buildView({
                baseTextLines: base,
                newTextLines: newtxt,
                opcodes: opcodes,
                baseTextName: "Base Text",
                newTextName: "New Text",
                contextSize: contextSize,
                viewType: viewType
            }));
        }
    </script>';
    echo nl2br('<span id="baseText">'.$row['rev'].'</span>');
    echo nl2br('<span id="newText">'.$row['rev'].'</span>');
    echo '<div id="diffoutput"> </div>';
}else{
    if(empty($fnwTitle) and $fnwTitle != '0'){
        die('제목이 비어있습니다.');
    }

    $sql = "SELECT count(*) as `cnt` FROM `_history` WHERE `title` = '$fnwTitle'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $i = 1;
    $c = $row['cnt'];

    $l = filt($_GET['p'], '123');
    if($l){
        $l = $l * 30;
        $lc = $l - 30;
        $c -= $lc;
        $l = $lc.', 31';
    }else{
        $l = '31';
    }

    $sql = "SELECT * FROM `_history` WHERE `title` = '$fnwTitle' ORDER BY `num` DESC LIMIT $l";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) < 1){
        exit;
    }

    echo '<table class="full"><tbody>';
    while($row = mysqli_fetch_assoc($result)){
        if($i !== 1){
            $strLCac = $prevLen - mb_strlen($row['rev']);
            if($strLCac < 0){
                $color = 'red';
            }elseif($strLCac > 0){
                $strLCac = '+'.$strLCac;
                $color = 'green';
            }else{
                $color = 'blue';
            }
            echo '/ <'.$color.'>('.$strLCac.')</'.$color.'></span></td></tr>';
        }

        if($i > 30){
            break;
        }

        $wE = $row['id'];
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
          echo '<tr><td class="black muted"><a href="javascript:void(0)" onclick="wikiHisRev('.$row['num'].')">#'.$c.'번째 편집 ('.$row['at'].')</a>
        <br><span class="subInfo"><i class="icofont-'.$icon.'"></i><a href="'.$href.'"> '.$name.'</a> ';
        $i++;
        $c--;
        $prevLen = mb_strlen($row['rev']);
    }
    echo '</tbody></table>';
}
?>
