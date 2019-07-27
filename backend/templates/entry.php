<?= tpl('header') ?>

<form method="post">
	<input name="q" value="{{$entry['q']}}" required>
	<input name="a" value="{{$entry['a']}}" required>
	<button>Save</button>
</form>
