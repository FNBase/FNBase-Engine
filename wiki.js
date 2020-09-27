if(screen.width <= 1024){
    var isMobile = true;
}else{
    var isMoblie = false;
}

var prevHisNum = false;
var hisPage = false;

function alertWait(){
    alert('열심히 개발중입니다..\n조금만 기다려주세요.');
}

//알림 받아오기
var timeset = 30000;
var noticount = 0;

window.onload = function () {
    if (window.Notification) {
        Notification.requestPermission();
    }
    notify();
}

function notify() {
    if(noticount < 10){
        fetch('/php/notifyjs.php').then(function(response){ //알림이 있는지 물음
            response.text().then(function(text){
                if(text == 0){
                    timeset += 90000;
                }else{
                    if (Notification.permission !== 'granted') {
                        console.log('알림 수신 거부됨.');
                    }
                    else {
                        var notification = new Notification('FNBase - 새 알림이 있습니다.', {
                            icon: '/icon.png',
                            body: text,
                        });

                        notification.onclick = function () {
                            window.open('http://fnbase.xyz/nofi', '_self');
                        };
                    }
                }
            })
            if(response.status != '200'){
                console.log('서버 통신 오류');
            }
        })

        setTimeout(function setTimeout() {
            notify();
        }, timeset);
    }
}

function wikiEdit(arg){
    fetch('/wiki_f.php?title='+arg).then(function(response){
        response.text().then(function(text){
            document.querySelector('#mainContent').style.cssText = 'display:none';
            document.querySelector('#editPlace').style.cssText = '';
            document.querySelector("#editPlaceText").innerHTML = text;
        })
        if(response.status != '200'){
            console.log('서버 통신 오류');
        }else{
            document.querySelector("#wikiModeText").innerHTML = " (편집)";
                /*if(isMobile){
                    document.querySelectorAll('#wFClose')[0].click();
                }*/
            wikiKeepWrite();
        }
    })
}

function wikiHistory(arg){
    if(hisPage){
        hisPage++
    }else{
        hisPage = 1
    }
    fetch('/w_history/'+hisPage+'/'+arg).then(function(response){
        response.text().then(function(text){
            document.querySelector('#mainContent').style.cssText = 'display:none';
            document.querySelector('#editPlace').style.cssText = '';
            document.querySelector("#editPlaceText").innerHTML = text;
            if(!text){
                hisPage = 0
                wikiHistory(arg)
            }
        })
        if(response.status != '200'){
            console.log('서버 통신 오류');
        }else{
            document.querySelector("#wikiModeText").innerHTML = " (기록)";
        }
    })
}

function wikiHisRev(arg){
    fetch('/wiki_h.php?mode=view&num='+arg).then(function(response){
        response.text().then(function(text){
            document.querySelector("#wHrText").innerHTML = text;
        })
        if(response.status != '200'){
            console.log('서버 통신 오류');
        }else{
            document.querySelectorAll('#wHrClose')[0].click();
        }
    })
    prevHisNum = arg;
}

function wikiHisRaw(){
    fetch('/wiki_h.php?mode=raw&num='+prevHisNum).then(function(response){
        response.text().then(function(text){
            document.querySelector("#wHrText").innerHTML = text;
        })
        if(response.status != '200'){
            console.log('서버 통신 오류');
        }
    })
}

function wikiDiscuss(arg){
    fetch('/wiki_d.php?title='+arg).then(function(response){
        response.text().then(function(text){
            document.querySelector('#mainContent').style.cssText = 'display:none';
            document.querySelector('#editPlace').style.cssText = '';
            document.querySelector("#editPlaceText").innerHTML = text;
        })
        if(response.status != '200'){
            console.log('서버 통신 오류');
        }else{
            document.querySelector("#wikiModeText").innerHTML = " (토론)";
                        if(isMobile){
                document.querySelectorAll('#wFClose')[0].click();
            }
        }
    })
}

function wikiManage(arg){
    fetch('/wiki_m.php?title='+arg).then(function(response){
        response.text().then(function(text){
            document.querySelector('#mainContent').style.cssText = 'display:none';
            document.querySelector('#editPlace').style.cssText = '';
            document.querySelector("#editPlaceText").innerHTML = text;
        })
        if(response.status != '200'){
            console.log('서버 통신 오류');
        }else{
            document.querySelector("#wikiModeText").innerHTML = " (조정)";
                        if(isMobile){
                document.querySelectorAll('#wFClose')[0].click();
            }
        }
    })
}

function wikiPreview(arg){
    const form = document.getElementById('contentForm');
        event.preventDefault();
        const formattedFormData = new FormData(form);
        postData(formattedFormData);

    async function postData(formattedFormData){
        const response = await fetch('/wiki_v.php?title='+arg,{
            method: 'POST',
            body: formattedFormData
        });
        const data = await response.text();
        document.querySelector("#wPreText").innerHTML = data;
        document.querySelectorAll('#wPreClose')[0].click();
    }
}

function wikiSave(){
    const form = document.getElementById('contentForm');
    form.addEventListener('click', function(event){
        event.preventDefault();
        const formattedFormData = new FormData(form);
        postData(formattedFormData);
    });

    async function postData(formattedFormData){
        const response = await fetch('/wiki_s.php',{
            method: 'POST',
            body: formattedFormData
        });
        const data = await response.text();
        location.reload();
    }
}

function editCancle(){
    document.querySelector('#mainContent').style.cssText = '';
    document.querySelector('#editPlace').style.cssText = 'display:none';
    document.querySelector("#wikiModeText").innerHTML = '';
    notSubmit = false;
}

function foldSpan(){
    if(document.querySelector('.foldSpan').style.cssText == ''){
        document.querySelector('.foldSpan').style.cssText = 'display:none';
    }else{
        document.querySelector('.foldSpan').style.cssText = '';
    }
}

function wikiRollback(arg, num){
    fetch('/wiki_v.php?mode=rollback&title='+arg+'&num='+num).then(function(response){
        response.text().then(function(text){
            alert(text);
            if(text.includes("성공")){
                location.href = '/h/'+arg;
            }
        })
        if(response.status != '200'){
            console.log('서버 통신 오류');
        }
    })
}

function wikiRollConf(){
    if(prevHisNum != false){
        if(confirm('정말 되돌리시겠습니까?')){
            wikiRollback(wikiTitle, prevHisNum);
        }
    }
}

function wikiNotes(arg){
    document.querySelector("#wPreNoteTxt").innerHTML = arg;
    document.querySelectorAll('#wPreNote')[0].click();
}

function wikiKeepWrite(){
    notSubmit = true;
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
}

function wikiHide(arg, num, isHidden){
    fetch('/wiki_v.php?mode=hide&title='+arg+'&num='+num+'&hidden='+String(isHidden)).then(function(response){
        response.text().then(function(text){
            alert(text);
            if (text.includes("성공")) {
                location.href = '/h/'+arg;
            }
        })
        if(response.status != '200'){
            console.log('서버 통신 오류');
        }
    })
}

function wikiHideConf(isHidden) {
  var message = "정말 숨기시겠습니까?"
  if (isHidden) message = "정말 복구하시겠습니까?";
  if (prevHisNum != false) {
    if (confirm(message)) {
      wikiHide(wikiTitle, prevHisNum, isHidden);
    }
  }
  else {
    alert("판을 찾을 수 없습니다.");
    history.go(-1);
  }
}

function tempSave(){
    const form = document.getElementById('contentForm');
    const formattedFormData = new FormData(form);
    postData(formattedFormData);

    async function postData(formattedFormData){
        const response = await fetch('/sub/tempsave.php',{
            method: 'POST',
            body: formattedFormData
        });
        const data = await response.text();
        alert('저장됨.');
    }
}
function tempLoad(){
    fetch('/sub/tempsave.php').then(function(response){
        response.text().then(function(text){
            document.querySelector('#mainEditor').innerHTML = text;
        })
        if(response.status == '403'){
            alert('로그인 되어있지 않음!');
        }else if(response.status != '200'){
            alert('서버 통신 오류');
        }else if(response.status == '200'){
            alert('불러오기 완료!')
            console.log(text);
        }
    })
}
