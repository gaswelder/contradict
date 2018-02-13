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
	<?php foreach ($tuples1 as $t) : ?>
	<div>
		<input type="hidden" name="q[]" value="{{$t->q}}">
		<input type="hidden" name="dir[]" value="0">
		<label>{{$t->q}} <small>({{$t->answers1}})</small></label>
		<input name="a[]" value="" autocomplete="off">
	</div>
	<?php endforeach; ?>
	</section>

	<section>
	<?php foreach ($tuples2 as $t) : ?>
	<div>
		<input type="hidden" name="q[]" value="{{$t->a}}">
		<input type="hidden" name="dir[]" value="1">
		<label>{{$t->a}} <small>({{$t->answers2}})</small></label>
		<input name="a[]" value="" autocomplete="off">
	</div>
	<?php endforeach; ?>
	</section>
	<div>
		<button>Submit</button>
	</div>
</form>
