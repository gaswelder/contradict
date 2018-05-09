<?= tpl('header') ?>

<section class="test-stats">
<p>{{ round($stats->right / ($stats->right + $stats->wrong) * 100) }} %</p>
</section>
<table>
<tr>
	<th>Q</th>
	<th>Expected</th>
	<th>A</th>
</tr>
<?php foreach ($fail as $r) : ?>
	<tr class="nope">
		<td>{{ $r['question']->q() }}</td>
		<td>
			<a href="/entries/{{$r['question']->id()}}">{{ $r['question']->a() }}</a>
			<?php if ($wiki = $r['question']->wikiURL()) : ?>
				<small>(<a href="{{ $wiki }}">wiki</a>)</small>
			<?php endif; ?>
		</td>
		<td>{{ $r['answer'] }}</td>
	</tr>
<?php endforeach; ?>
</table>

<table>
<tr>
	<th>Q</th>
	<th>A</th>
	<th></th>
</tr>
<?php foreach ($ok as $r) : ?>
	<tr>
		<td>{{ $r['question']->q() }}</td>
		<td>{{ $r['question']->a() }}</td>
		<td>ok</td>
	</tr>
<?php endforeach; ?>
</table>

<nav>
	<a class="btn" href="/{{ $dict_id }}/test">New test</a>
	<a class="btn" href="/">Home</a>
</nav>
