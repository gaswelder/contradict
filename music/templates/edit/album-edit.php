<?= tpl('top') ?>

<h1>Edit album</h1>

<form method="post" action="/albums/{{$album->id}}">
	<div>
		<label>Data</label>
		<textarea name="data">{{json_encode($album->toJSON(), JSON_PRETTY_PRINT)}}</textarea>
	</div>
	<button>Update</button>
</form>

<?= tpl('bottom') ?>
