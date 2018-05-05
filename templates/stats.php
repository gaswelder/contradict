<?= tpl('header') ?>

<table>
	<?php foreach ($results as $r) : ?>
	<tr>
		<td>{{date('Y-m-d', $r->t)}}</td>
		<td>{{round($r->right / ($r->right + $r->wrong) * 100)}}</td>
	</tr>
	<?php endforeach; ?>
</table>