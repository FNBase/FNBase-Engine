<?php
    $fnMultiNum = 2;
    include_once 'setting.php';
    include_once 'func.php';
    $fnwTitle = filt(urldecode($_GET['title']), 'htm');
    include_once 'wiki_p.php';
    if(empty($fnwTitle) and $fnwTitle != '0'){
        die('제목이 비어있습니다.');
    }

    if(empty($id) and $id != '0'){
        if(strstr($ip, ':')){
            die('ipv6 대역은 익명 작성이 불가능합니다.');
        }
    }

    $fnwTitle = documentRender($fnwTitle, TRUE);
    $sql = "SELECT * FROM `_article` WHERE `title` = '$fnwTitle'";
    $document = mysqli_query($conn, $sql);
    if(mysqli_num_rows($document) < 1){
        unset($document);
        $document['content'] = '';
        $document['title'] = $fnwTitle;
    }else{
        $document = mysqli_query($conn, $sql);
        $document = mysqli_fetch_assoc($document);
    }

    if($document['ACL'] == 'none'){
        die('<strong><green>안내)</green> 이 문서는 잠겨있습니다.</strong><br>
        ACL 조정 권한이 있으실 경우 잠금을 해제해보세요.');
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

            if(!$id){
                $wfWarn = documentRender('<strong><red>주의!</red> 비로그인 상태로 편집할 시 귀하의 아이피 주소(___ADDRESS___)가 영구히 기록됩니다!</strong><hr>', true);
            }

if($canEdit){
    ?>
    <form method="POST" id="contentForm"><?=$wfWarn?>
    <textarea id="mainEditor" name="content" placeholder="내용을 비울 수 없습니다!" style="min-height:20em;border:0;" required><?=$document['content']?></textarea>
    <hr><input type="text" name="comment" placeholder="편집자 의견 (100자 이내)" maxlength="100" formaction="/javascript:void(0)">
    <button type="button" style="background:slateblue" class="full" onclick="notSubmit=false;wikiPreview('<?=$fnwTitle?>')"><i class="icofont-file-presentation"></i> 미리보기</button>
    <button type="button" style="background:green" class="full" onclick="notSubmit=false;wikiSave()"><i class="icofont-diskette"></i> 저장하기</button>
    <button type="button" style="background:gray" class="full" onclick="editCancle()"><i class="icofont-error"></i> 취소하기</button>
    <input type="hidden" name="title" value="<?=$document['title']?>"></form>
    <?php
}else{
    echo '<div id="contentForm">편집 권한이 부족하여, 원본 텍스트만 표시됩니다.
    <hr><textarea id="mainEditor" placeholder="내용을 비울 수 없습니다!" style="min-height:20em;border:0;" readonly>'.$document['content'].'</textarea><hr>';
    echo '<button type="button" style="background:gray" class="full" onclick="editCancle()"><i class="icofont-error"></i> 취소하기</button></div>';
}
?>
