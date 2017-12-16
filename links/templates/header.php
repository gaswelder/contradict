<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="/res/style.css">
</head>
<body>
<header id="header">
	<?php if (tplvar('loggedIn')) : ?>
    <nav class="container">
        <a href="/links/">View links</a>
        <a href="/links/new">Add new link</a>
        <a href="/links/export">Export</a>
        <a href="/links/import">Import</a>

        <form method="post" action="/links/logout">
            <button type="submit">Logout</button>
        </form>
    </nav>
    <?php endif; ?>
</header>

    <div class="container">
