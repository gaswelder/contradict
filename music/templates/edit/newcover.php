<?= tpl('top') ?>

<h1>New cover</h1>

<form method="post" enctype="multipart/form-data">
	<input type="file" name="file" accept="image/jpeg">
	<button>Upload</button>
</form>

<?= tpl('bottom') ?>
