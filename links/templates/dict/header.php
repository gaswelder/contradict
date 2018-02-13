<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width">
<link rel="stylesheet" href="/res/style.css">
<style>
body {
	margin: 2cm;
	padding-top: 4em;
}

@media (max-width: 400px) {
	body {
		margin: 1em;
	}
}

#stats {
	background-color: #aae;
	position: fixed;
	left: 0;
	right: 0;
	top: 0;
	padding: 0 1em;
}

#stats a {
	display: inline-block;
	float: left;
	margin: 1em 1em 0 0;
}
</style>
</head>
<body>
<aside id="stats">
	<?php $stats = stats(); ?>
	<a href="/dict">Home</a>
	<p>Total: {{$stats['pairs']}}; progress: {{round($stats['progress'] * 100, 1)}} %</p>
</aside>
