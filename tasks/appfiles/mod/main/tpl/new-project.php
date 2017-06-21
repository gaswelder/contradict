<?php _header(); ?>

<h1>Новый проект</h1>

<form method="post" action="<?= h3::url( 'actions::create_project' ) ?>">
	<div>
		<label>Название</label>
		<input name="name">
	</div>
	<button type="submit">Создать</button>
</form>

<?php _footer(); ?>
