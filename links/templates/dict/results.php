<?= tpl('dict/header') ?>

<style>
.nope td:nth-child(3) {
	color: red;
	text-decoration: line-through;
}
</style>
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
			<a href="/dict/entries/{{$r['question']->id()}}">{{ $r['question']->a() }}</a>
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
	<a class="btn" href="/dict/test">New test</a>
	<a class="btn" href="/dict">Home</a>
</nav>
