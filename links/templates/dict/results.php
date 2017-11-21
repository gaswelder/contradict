<?= tpl('dict/header') ?>
<table>
<tr>
	<th>Q</th>
	<th>A</th>
	<th>Expected</th>
	<th></th>
</tr>
<?php foreach ($results as $r): ?>
	<tr>
		<td>{{$r['q']}}</td>
		<td>{{$r['a']}}</td>
		<td>{{$r['expected']}}</td>
		<td>{{$r['ok'] ? 'ok' : 'nope'}}</td>
	</tr>
<?php endforeach; ?>
</table>