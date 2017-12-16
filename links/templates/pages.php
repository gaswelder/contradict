<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
html {
	height: 100%;
}

body {
	font-size: 14pt;

	display: flex;
	height: 100%;
	margin: 0;
}

nav {
	flex: 0 0 200px;
	padding: 1em;
}

nav li, nav ol {
	list-style-type: none;
	margin: 0;
	padding: 0;
}

form {
	flex: 1 1 200px;
	font-size: 1em;
	display: flex;
	flex-direction: column;
}

form button {
	margin: 1em;
}

form > div {
	order: 1;
}

form textarea {
	flex: 1 1 auto;
	font-size: 11pt;
	padding: 6pt;
}

#preview {
	flex: 1 1 200px;
	height: calc(100% - 2.5em);
	overflow-y: scroll;
	padding: 1em;
}

#preview pre {
	padding: 10px;
	margin-left: 2em;
	font-size: 90%;
	font-weight: normal;
	font-family: monospace;
}
</style>
</head>
<body>
	<nav>
		<ol>
			<?php foreach($menu as $name): ?>
				<li><a href="/pages/{{$name}}">{{$name}}</a></li>
			<?php endforeach; ?>
		</ol>
	</nav>

	<form method="post">
		<div>
			<button>Save</button>
		</div>
		<textarea name="content">{{$page->content}}</textarea>
	</form>

	<div id="preview"></div>
<script src="https://unpkg.com/markdown-it@8.4.0/dist/markdown-it.min.js"></script>
<script>
const editor = document.querySelector('textarea');
const preview = document.getElementById('preview');

var md = window.markdownit();

function sync() {
	preview.innerHTML = md.render(editor.value);
}

editor.addEventListener('keydown', function(e) {
	if (e.key == 'Tab') {
		e.preventDefault();
		const t = e.target;
		const p1 = t.selectionStart;
		const p2 = t.selectionEnd;
		t.value = t.value.substr(0, p1) + "\t" + t.value.substr(p2);
		t.selectionStart = t.selectionEnd = p1 + 1;
		sync();
	}
});

editor.addEventListener('input', function(e) {
	sync();
});

sync();

function markdown(md) {
	return md
		.replace(/    /g, "\t")
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/\t/g, "&nbsp;&nbsp;&nbsp;&nbsp;")
		.replace(/\n/g, "<br>\n");
}
</script>
</body>
</html>
