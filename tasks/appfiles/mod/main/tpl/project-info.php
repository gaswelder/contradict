<?php _header(); ?>

<?php
use h3\user;

$pid = arg(1);
$user_id = user::id();
$project = new project( $pid, 'name' );
$parts = t::get_project_participants_r( $pid );

$can_give_rights = project_permission( $user_id, $pid, 'add-participant' );

$t = new table( array(
	'name' => 'Имя',
));
foreach( $parts as $p )
{
	$r = array(
		'name' => $p['name'],
	);
	if( $can_give_rights )
	{
		$r['edit'] = '<a href="'.h3::url( "participant", $pid, $p['user_id'] ).'">Изменить</a>';
	}
	$t->add_row( $r );
}
?>

<h1>Участники проекта &laquo;<?= $project->name() ?>&raquo;</h1>

<?php if( $can_give_rights ): ?>
	<p><a class="create" href="<?= h3::url( 'participant', $pid ) ?>">Добавить участника</a></p>
<?php endif; ?>

<?= $t ?>

<?php _footer(); ?>
