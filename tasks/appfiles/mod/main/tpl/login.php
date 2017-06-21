<?php
use h3\lib\f;
?>
<?php _header(); ?>

<form method="post" id="login-form">
	<h1>Вход</h1>
	<?php if($err): ?>
	<p><?= f::h($err) ?></p>
	<?php endif; ?>
	<div>
		<label>Имя пользователя</label>
		<input name="login" autofocus required>
	</div>
	<div>
		<label>Пароль</label>
		<input type="password" name="password">
	</div>
	<button type="submit">Войти</button>
</form>

<?php _footer(); ?>
