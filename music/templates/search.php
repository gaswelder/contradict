<?= tpl( 'top' ) ?>

<?php if($q): ?>
	<h1>Search results for query "{{$q}}"</h1>
<?php else: ?>
	<h1>Search</h1>
<?php endif; ?>

<ul>
<?php foreach($bands as $band): ?>
	<li><a href="/music/bands/{{$band->id}}">{{$band->name}}</a></li>
<?php endforeach; ?>
</ul>

<?= tpl( 'bottom' ) ?>
