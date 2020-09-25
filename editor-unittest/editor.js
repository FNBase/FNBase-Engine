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
document.querySelector("#editor--style").addEventListener('click', editorStyle);
function editorStyle() {
	var styleType = document.querySelector("input[name=\"editor-style\"]:checked").value;
	var prefix = "";
	switch (styleType) {
		case "style":
			prefix = "!#wiki style=\"" + document.querySelector("#editor-style-style-value").value + "\""; break;
		case "color":
			prefix = document.querySelector("#editor-style-color-value").value; break;
		case "size":
			var size = document.querySelector("#editor-style-size-value").selectedIndex;
			switch (size) {
				case 0: prefix = "-5"; break;
				case 1: prefix = "-4"; break;
				case 2: prefix = "-3"; break;
				case 3: prefix = "-2"; break;
				case 4: prefix = "-1"; break;
				case 5: prefix = "+1"; break;
				case 6: prefix = "+2"; break;
				case 7: prefix = "+3"; break;
				case 8: prefix = "+4"; break;
				case 9: prefix = "+5"; break;
			}
			break;
	}
	document.getElementById('editor-modal-style').checked = false;
	editorCallback("{{{" + prefix + ' ', "}}}")();
}
