<?= tpl( "top" ) ?>

<h1>{{$band->name}}</h1>

<?= tpl('parts/albums-list', ['albums' => $band->albums()]) ?>

<?= tpl( "bottom" ) ?>
