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
    <div id="editBar">
      <button class="pseudo" id="editor-strong"><strong class="editor-icon">B</strong></button>
      <button class="pseudo" id="editor-em"><em class="editor-icon">I</em></button>
      <button class="pseudo" id="editor-strike"><strike>S</strike></button>
      <button class="pseudo" id="editor-muted"><span style="color: gray;">G</span></button>
      <button class="pseudo" id="editor-sup"><span>x²</span></button>
      <button class="pseudo" id="editor-sub"><span>x₂</span></button>
      <button class="pseudo" id="editor-u"><u>U</u></button>
      <button class="pseudo" id="editor-inlink"><i class="icofont-ui-clip"></i></button>
      <button class="pseudo" id="editor-ul"><i class="icofont-listine-dots"></i></button>
      <button class="pseudo" id="editor-blockquote"><i class="icofont-quote-left"></i></button>
      <button class="pseudo" id="editor-indent"><i class="icofont-login"></i></button>
      <label for="editor-modal-style" class="button pseudo" id="editor-style"><i class="icofont-magic"></i></label>
      <div class="modal">
        <input type="checkbox" id="editor-modal-style">
        <label for="editor-modal-style" class="overlay"></label>
        <article>
          <header>
            <h3>스타일 적용</h3>
            <label for="editor-modal-style" class="close">&times;</label>
          </header>
          <section class="content">
            <input id="editor-style-style" checked="checked" type="radio" name="editor-style" value="style"/>
            <label for="editor-style-style" class="checkable">스타일 직접 적용</label>
            <input id="editor-style-style-value" type="text" value="" /><br/>

            <input id="editor-style-color" type="radio" name="editor-style" value="color" />
            <label for="editor-style-color" class="checkable">색상 지정</label>
            <input id="editor-style-color-value" type="color" value="" /><br/>

            <input id="editor-style-size" type="radio" name="editor-style" value="size" />
            <label for="editor-style-size" class="checkable">크기 조정</label>
            <select id="editor-style-size-value">
              <option value="m5">-5</option>
              <option value="m4">-4</option>
              <option value="m3">-3</option>
              <option value="m2">-2</option>
              <option value="m1">-1</option>
              <option value="p1">+1</option>
              <option value="p2">+2</option>
              <option value="p3">+3</option>
              <option value="p4">+4</option>
              <option value="p5">+5</option>
            </select>
          </section>
          <footer>
            <a id="editor--style" href="#" class="button">삽입</a>
            <label for="editor-modal-style" class="button dangerous">
              취소
            </label>
          </footer>
        </article>
      </div>
    </div>
    <form method="POST" id="contentForm"><?=$wfWarn?>
    <textarea id="mainEditor" name="content" placeholder="내용을 비울 수 없습니다!" style="min-height:20em;border:0;" required><?=$document['content']?></textarea>
    <hr><input type="text" name="comment" placeholder="편집자 의견 (100자 이내)" maxlength="100" formaction="/javascript:void(0)">
    <button type="button" style="background:slateblue" class="full" onclick="notSubmit=false;wikiPreview('<?=$fnwTitle?>')"><i class="icofont-file-presentation"></i> 미리보기</button>
    <button type="button" style="background:green" class="full" onclick="notSubmit=false;wikiSave()"><i class="icofont-diskette"></i> 저장하기</button>
    <button type="button" style="background:gray" class="full" onclick="editCancle()"><i class="icofont-error"></i> 취소하기</button>
    <input type="hidden" name="title" value="<?=$document['title']?>"></form>
		<script src="/editor.js"></script>
    <?php
}else{
    echo '<div id="contentForm">편집 권한이 부족하여, 원본 텍스트만 표시됩니다.
    <hr><textarea id="mainEditor" placeholder="내용을 비울 수 없습니다!" style="min-height:20em;border:0;" readonly>'.$document['content'].'</textarea><hr>';
    echo '<button type="button" style="background:gray" class="full" onclick="editCancle()"><i class="icofont-error"></i> 취소하기</button></div>';
}
?>
