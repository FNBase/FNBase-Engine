var editor = document.querySelector('#mainEditor');

document.querySelector('#editor-strong').addEventListener('click', editorStrong);
function editorStrong() {
	var s = editor.selectionStart;
	var e = editor.selectionEnd;
	var selected = editor.value.substring(s, e);
	editor.value = editor.value.substring(0, s) + "\'\'\'" + selected + "\'\'\'" + editor.value.substring(e);
	editor.focus();
	if (s == e) editor.selectionEnd = s+3;
	else				editor.selectionEnd = e+6;
}

document.querySelector('#editor-em').addEventListener('click', editorEm);
function editorEm() {
	var s = editor.selectionStart;
	var e = editor.selectionEnd;
	var selected = editor.value.substring(s, e);
	editor.value = editor.value.substring(0, s) + "\'\'" + selected + "\'\'" + editor.value.substring(e);
	editor.focus();
	if (s == e) editor.selectionEnd = s+2;
	else				editor.selectionEnd = e+4;
}

document.querySelector('#editor-strike').addEventListener('click', editorStrike);
function editorStrike() {
	var s = editor.selectionStart;
	var e = editor.selectionEnd;
	var selected = editor.value.substring(s, e);
	editor.value = editor.value.substring(0, s) + "--" + selected + "--" + editor.value.substring(e);
	editor.focus();
	if (s == e) editor.selectionEnd = s+2;
	else				editor.selectionEnd = e+4;
}

document.querySelector('#editor-muted').addEventListener('click', editorMuted);
function editorMuted() {
	var s = editor.selectionStart;
	var e = editor.selectionEnd;
	var selected = editor.value.substring(s, e);
	editor.value = editor.value.substring(0, s) + "~~" + selected + "~~" + editor.value.substring(e);
	editor.focus();
	if (s == e) editor.selectionEnd = s+2;
	else				editor.selectionEnd = e+4;
}

document.querySelector('#editor-sup').addEventListener('click', editorSup);
function editorSup() {
	var s = editor.selectionStart;
	var e = editor.selectionEnd;
	var selected = editor.value.substring(s, e);
	editor.value = editor.value.substring(0, s) + "^^" + selected + "^^" + editor.value.substring(e);
	editor.focus();
	if (s == e) editor.selectionEnd = s+2;
	else				editor.selectionEnd = e+4;
}

document.querySelector('#editor-sub').addEventListener('click', editorSub);
function editorSub() {
	var s = editor.selectionStart;
	var e = editor.selectionEnd;
	var selected = editor.value.substring(s, e);
	editor.value = editor.value.substring(0, s) + ",," + selected + ",," + editor.value.substring(e);
	editor.focus();
	if (s == e) editor.selectionEnd = s+2;
	else 				editor.selectionEnd = e+4;
}

document.querySelector('#editor-u').addEventListener('click', editorU);
function editorU() {
	var s = editor.selectionStart;
	var e = editor.selectionEnd;
	var selected = editor.value.substring(s, e);
	editor.value = editor.value.substring(0, s) + "__" + selected + "__" + editor.value.substring(e);
	editor.focus();
	if (s == e)	editor.selectionEnd = s+2;
	else				editor.selectionEnd = e+4;
}

document.querySelector('#editor-ul').addEventListener('click', editorUl);
function editorUl() {
	var s = editor.selectionStart;
	var e = editor.selectionEnd;
	var selected = editor.value.substring(s, e);
	editor.value = editor.value.substring(0, s) + "\n * " + selected + "\n" + editor.value.substring(e);
	editor.focus();
	if (s == e) editor.selectionEnd = s+4;
	else				editor.selectionEnd = e+5;
}

document.querySelector('#editor-blockquote').addEventListener('click', editorBlockquote);
function editorBlockquote() {
	var s = editor.selectionStart;
	var e = editor.selectionEnd;
	var selected = editor.value.substring(s, e);
	editor.value = editor.value.substring(0, s) + "\n > " + selected + "\n" + editor.value.substring(e);
	editor.focus();
	if (s == e) editor.selectionEnd = s+4;
	else				editor.selectionEnd = e+5;
}

document.querySelector("#editor-inlink").addEventListener('click', editorInlink);
function editorInlink() {
	var s = editor.selectionStart;
	var e = editor.selectionEnd;
	var selected = editor.value.substring(s, e);
	editor.value = editor.value.substring(0, s) + "[[" + selected + "]]" + editor.value.substring(e);
	editor.focus();
	if (s == e) editor.selectionEnd = s+2;
	else				editor.selectionEnd = e+4;
}

document.querySelector("#exlink-title").addEventListener('keyup', checkExist);
function checkExist() {
	var name = document.querySelector("#exlink-title");
	var prefix = "https://cors-anywhere.herokuapp.com/";
	var title = name.value;
	switch(document.querySelector('input[name="exlink"]:checked').value) {
		case 'locate':
			prefix += ""; break;
		case 'kowiki':
			prefix += "https://ko.wikipedia.org/wiki/"; break;
		case 'enwiki':
			prefix += "https://en.wikipedia.org/wiki/"; break;
		case 'flqmfp':
			prefix += "https://librewiki.net/wiki/"; break;
		case 'skarks':
			prefix += "https://namu.wiki/w/"; break;
		case 'fnbase':
			prefix += "https://fnbase.xyz/"; break;
	}
	url = prefix + title;
	console.log(url);
	name.style.border = "1px solid red";
	fetch(url).then(function(response) {
		console.log(responce.text);
		if (response.status == '200') {
			name.style.border = "1px solid blue";
		}
	});
}

document.querySelector("#editor-insertExlink").addEventListener('click', editorInsertExlink);
function editorInsertExlink() {

}
