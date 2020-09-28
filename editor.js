function editorCallback(a, b) {
	return function() {
		var editor = document.querySelector('#mainEditor');
		var scroll = editor.scrollTop;
		var s = editor.selectionStart;
		var e = editor.selectionEnd;
		var selected = editor.value.substring(s, e);
		editor.value = editor.value.substring(0, s) + a + selected + b + editor.value.substring(e);
		editor.focus();
		if (s == e) editor.selectionEnd = s+a.length;
		else				editor.selectionEnd = e+a.length+b.length;
		editor.scrollTop = scroll;
	};
}

var editorStrong = editorCallback("\'\'\'", "\'\'\'");
var editorEm = editorCallback("\'\'", "\'\'");
var editorStrike = editorCallback("--", "--");
var editorMuted = editorCallback("~~", "~~");
var editorSup = editorCallback("^^", "^^");
var editorSub = editorCallback(",,", ",,");
var editorU = editorCallback("__", "__");
var editorInlink = editorCallback("[[", "]]");
var editorUl = editorCallback("\n * ", "");
var editorBlockquote = editorCallback("\n > ", "");
var editorIndent = editorCallback("\n : ", "");
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
