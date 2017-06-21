
<form id="change-password-form" method="post" action="<?= h3::url( 'actions::change_password' ) ?>">
	<h1>Смена пароля</h1>
	<div>
		<label>Старый пароль</label>
		<input type="password" name="old-password">
	</div>
	<div>
		<label>Новый пароль, два раза</label>
		<input type="password" name="new-password-1">
		<input type="password" name="new-password-2">
	</div>
	<button type="submit">Сменить пароль</button>
</form>
