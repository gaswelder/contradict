<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="/links/res/style.css">
</head>
<body>
<header id="header">
	<?php if (user::select('user')) : ?>
    <nav class="container">
        <a href="/links/">View links</a>
        <a href="/links/new">Add new link</a>

        <form method="post" action="/links/logout">
            <button type="submit">Logout</button>
        </form>
    </nav>
    <?php endif; ?>
</header>

    <div class="container">
