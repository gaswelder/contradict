<?php _header(); ?>

<?php add_js( 'scripts/new-task.js' ); ?>

<?php

$project_id = arg(1);
if( !$project_id ) {
	error_notfound();
}

?>

<h1>Новая задача</h1>

<form method="post" id="task-form" enctype="multipart/form-data"
	action="<?= h3::url( 'actions::create-task' ) ?>">
	<input type="hidden" name="project_id" value="<?= $project_id ?>">
	<div>
		<label>Краткое описание</label>
		<input name="name" required autofocus>
	</div>
	<div>
		<label>Комментарии (если надо)</label>
		<textarea name="description"></textarea>
	</div>
	<div>
		<label class="inline">Прикрепить файл</label>
		<input type="file" name="files">
	</div>
	<div>
		<label>Приоритет</label>
		<input type="number" value="1" min="1" step="1" name="priority">
	</div>
	<div>
		<input type="checkbox" name="subscribe" value="1"
		id="cb-subscribe">
		<label for="cb-subscribe">Уведомлять меня по почте</label>
	</div>
	<button type="submit">Сохранить</button>
	
</form>

<?php _footer(); ?>
