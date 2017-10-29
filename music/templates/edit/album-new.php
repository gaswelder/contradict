<?= tpl('top') ?>

<h1>New album</h1>

<form method="post" action="/albums">
	<div>
		<label>Data</label>
		<textarea name="data"></textarea>
	</div>
	<button>Create</button>
</form>

<?= tpl('bottom') ?>
