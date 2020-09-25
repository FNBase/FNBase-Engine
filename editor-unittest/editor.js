var editor = document.querySelector('#mainEditor');

function editorCallback(a, b) {
	return function() {
		var s = editor.selectionStart;
		var e = editor.selectionEnd;
		var selected = editor.value.substring(s, e);
		editor.value = editor.value.substring(0, s) + a + selected + b + editor.value.substring(e);
		editor.focus();
		if (s == e) editor.selectionEnd = s+a.length;
		else				editor.selectionEnd = e+a.length+b.length;
	};
}

document.querySelector('#editor-strong').addEventListener('click', editorCallback("\'\'\'", "\'\'\'"));
document.querySelector('#editor-em').addEventListener('click', editorCallback("\'\'", "\'\'"));
document.querySelector('#editor-strike').addEventListener('click', editorCallback("--", "--"));
document.querySelector('#editor-muted').addEventListener('click', editorCallback("~~", "~~"));
document.querySelector('#editor-sup').addEventListener('click', editorCallback("^^", "^^"));
document.querySelector('#editor-sub').addEventListener('click', editorCallback(",,", ",,"));
document.querySelector('#editor-u').addEventListener('click', editorCallback("__", "__"));
document.querySelector("#editor-inlink").addEventListener('click', editorCallback("[[", "]]"));
document.querySelector('#editor-ul').addEventListener('click', editorCallback("\n * ", ""));
document.querySelector('#editor-blockquote').addEventListener('click', editorCallback("\n > ", ""));
document.querySelector("#editor-indent").addEventListener('click', editorCallback("\n : ", ""));
document.querySelector("#editor-magic").addEventListener('click', editorMagic);
function editorMagic() {

}
