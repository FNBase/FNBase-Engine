document.querySelector('#editor-strong').addEventListener('click', editorStrong);
function editorStrong() {
	var editor = document.querySelector("#mainEditor");
	var s = editor.selectionStart;
	var e = editor.selectionEnd;
	var selected = editor.value.substring(s, e);
	editor.value = editor.value.substring(0, s) + "\'\'\'" + selected + "\'\'\'" + editor.value.substring(e);
	editor.focus();
	if (s == e) editor.selectionEnd = s+3;
	else	    editor.selectionEnd = e+6;
}

document.querySelector('#editor-em').addEventListener('click', editorEm);
function editorEm() {
	var editor = document.querySelector("#mainEditor");
	var s = editor.selectionStart;
	var e = editor.selectionEnd;
	var selected = editor.value.substring(s, e);
	editor.value = editor.value.substring(0, s) + "\'\'" + selected + "\'\'" + editor.value.substring(e);
	editor.focus();
	if (s == e) editor.selectionEnd = s+2;
	else	    editor.selectionEnd = e+4;
}

document.querySelector('#editor-strike').addEventListener('click', editorStrike);
function editorStrike() {
	var editor = document.querySelector("#mainEditor");
	var s = editor.selectionStart;
	var e = editor.selectionEnd;
	var selected = editor.value.substring(s, e);
	editor.value = editor.value.substring(0, s) + "--" + selected + "--" + editor.value.substring(e);
	editor.focus();
	if (s == e) editor.selectionEnd = s+2;
	else	    editor.selectionEnd = e+4;
}

document.querySelector('#editor-muted').addEventListener('click', editorMuted);
function editorMuted() {
	var editor = document.querySelector("#mainEditor");
	var s = editor.selectionStart;
	var e = editor.selectionEnd;
	var selected = editor.value.substring(s, e);
	editor.value = editor.value.substring(0, s) + "~~" + selected + "~~" + editor.value.substring(e);
	editor.focus();
	if (s == e) editor.selectionEnd = s+2;
	else	    editor.selectionEnd = e+4;
}

document.querySelector('#editor-sup').addEventListener('click', editorSup);
function editorSup() {
	var editor = document.querySelector("#mainEditor");
	var s = editor.selectionStart;
	var e = editor.selectionEnd;
	var selected = editor.value.substring(s, e);
	editor.value = editor.value.substring(0, s) + "^^" + selected + "^^" + editor.value.substring(e);
	editor.focus();
	if (s == e) editor.selectionEnd = s+2;
	else	    editor.selectionEnd = e+4;
}

document.querySelector('#editor-sub').addEventListener('click', editorSub);
function editorSub() {
	var editor = document.querySelector("#mainEditor");
	var s = editor.selectionStart;
	var e = editor.selectionEnd;
	var selected = editor.value.substring(s, e);
	editor.value = editor.value.substring(0, s) + ",," + selected + ",," + editor.value.substring(e);
	editor.focus();
	if (s == e) editor.selectionEnd = s+2;
	else	    editor.selectionEnd = e+4;
}

document.querySelector('#editor-u').addEventListener('click', editorU);
function editorU() {
	var editor = document.querySelector("#mainEditor");
	var s = editor.selectionStart;
	var e = editor.selectionEnd;
	var selected = editor.value.substring(s, e);
	editor.value = editor.value.substring(0, s) + "__" + selected + "__" + editor.value.substring(e);
	editor.focus();
	if (s == e) editor.selectionEnd = s+2;
	else	    editor.selectionEnd = e+4;
}
