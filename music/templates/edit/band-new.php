<?= tpl('edit/top') ?>

<h1>New band</h1>

<form method="post">
	<div>
		<label>Name</label>
		<input name="name" required>
	</div>
	<button>Create</button>
</form>

<?= tpl('edit/bottom') ?>
