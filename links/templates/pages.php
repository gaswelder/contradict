<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
html {
	height: 100%;
}

body {
	font-size: 12pt;
	line-height: 1.3em;

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

</body>
</html>
