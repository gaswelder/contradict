<?= tpl('header') ?>

<?php
function Stats($dict)
{
	$stats = $dict->stats();
	?>
	<ul>
		<li>Total: {{$stats['pairs']}}; progress: {{round($stats['progress'] * 100, 1)}} %</li>
		<li>(finished {{$stats['finished']}}, started {{$stats['started']}})</li>
		<li>Success rate {{ $stats['successRate'] }}</li>
	</ul>
	<?php

}
?>

<?php foreach ($dicts as $dict) : ?>
	<section class="dict-preview">
		<b>{{ $dict->name }}</b>
		<a href="/{{ $dict->id }}/add">Add words</a>
		<?php Stats($dict); ?>
		<a class="btn test-button" href="/{{ $dict->id }}/test">Test</a>
	</section>
<?php endforeach; ?>
