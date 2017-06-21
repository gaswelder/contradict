<?php _header(); ?>

<h1>Новый пользователь</h1>

<form method="post">
<div>
	<label>Логин</label>
	<input name="login" required>
</div>
<div>
	<label>Пароль</label>
	<input type="password" name="password">
</div>
<div>
	<label>Имя</label>
	<input name="name" required>
</div>
<button type="submit">Сохранить</button>
<form>

<?php _footer(); ?>
