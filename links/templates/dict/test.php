<?= tpl('dict/header') ?>

<form method="post">
	<?php foreach ($tuples1 as $t): ?>
	<div>
		<input type="hidden" name="q[]" value="{{$t[0]}}">
		<input type="hidden" name="dir[]" value="0">
		<label>{{$t[0]}}</label>
		<input name="a[]" value="">
	</div>
	<?php endforeach; ?>

	<?php foreach ($tuples2 as $t): ?>
	<div>
		<input type="hidden" name="q[]" value="{{$t[1]}}">
		<input type="hidden" name="dir[]" value="1">
		<label>{{$t[1]}}</label>
		<input name="a[]" value="">
	</div>
	<?php endforeach; ?>
	<button>Submit</button>
</form>
