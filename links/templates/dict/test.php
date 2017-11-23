<?= tpl('dict/header') ?>
<style>
form > section {
	display: inline-block;
	vertical-align: top;
	margin-bottom: 1em;
	margin-right: 1em;
}
</style>
<form method="post">
	<section>
	<?php foreach ($tuples1 as $t): ?>
	<div>
		<input type="hidden" name="q[]" value="{{$t[0]}}">
		<input type="hidden" name="dir[]" value="0">
		<label>{{$t[0]}}</label>
		<input name="a[]" value="" autocomplete="off">
	</div>
	<?php endforeach; ?>
	</section>

	<section>
	<?php foreach ($tuples2 as $t): ?>
	<div>
		<input type="hidden" name="q[]" value="{{$t[1]}}">
		<input type="hidden" name="dir[]" value="1">
		<label>{{$t[1]}}</label>
		<input name="a[]" value="" autocomplete="off">
	</div>
	<?php endforeach; ?>
	</section>
	<div>
		<button>Submit</button>
	</div>
</form>
