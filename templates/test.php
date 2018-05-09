<?= tpl('header') ?>

<form method="post" class="test-form">
	<section>
	<?php foreach ($tuples1 as $question) : ?>
	<div>
		<input type="hidden" name="q[]" value="{{$question->id()}}">
		<input type="hidden" name="dir[]" value="0">
		<label>{{$question->q()}} <small>({{$question->times()}})</small></label>
		<input name="a[]" value="" autocomplete="off" placeholder="{{$question->hint()}}">
	</div>
	<?php endforeach; ?>
	</section>

	<section>
	<?php foreach ($tuples2 as $question) : ?>
	<div>
		<input type="hidden" name="q[]" value="{{$question->id()}}">
		<input type="hidden" name="dir[]" value="1">
		<label>{{$question->q()}} <small>({{$question->times()}})</small></label>
		<input name="a[]" value="" autocomplete="off" placeholder="{{$question->hint()}}">
	</div>
	<?php endforeach; ?>
	</section>
	<div>
		<button>Submit</button>
	</div>
</form>
