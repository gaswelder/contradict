<?php _header(); ?>

<?php
$pid = arg(1);
$uid = arg(2);

$u = new user_record( $uid );

if( $uid )
{
	$p_add_tasks = project_permission( $uid, $pid, 'create-task' );
	$p_close_tasks = project_permission( $uid, $pid, 'change-priority' );
	$p_add_comments = project_permission( $uid, $pid, 'add-comment' );
	?><h1>Участник <?= $u->name() ?></h1><?php
}
else
{
	?><h1>Новый участник</h1><?php
	$p_add_tasks = $p_close_tasks = $p_add_comments = false;
}
?>

<form method="post" action="<?= h3::url( 'actions::edit_participant' ) ?>">
	<input type="hidden" name="project-id" value="<?= $pid ?>">
	<?php
	if( $uid ) {
		?>
		<input type="hidden" name="user-id" value="<?= $uid ?>">
		<?php
	}
	else {
		?>
		<div>
			<label>Id (системный номер)</label>
			<input name="user-id" required>
		</div>
		<?php
	}
	?>

	<div>
		<?= html::labelled_checkbox( 'Просмотр проекта',
			'view-project', 'none', true ) ?>
	</div>

	<?php
	$names = perm::$project_perms;
	foreach( $names as $name => $label )
	{
		$can = $uid && project_permission( $uid, $pid, $name );
		?>
		<div>
		<?= snippets::labelled_checkbox( $label,
			'perm-'.$name, '1', $can ) ?>
		</div>
	<?php
	}
	?>
	<button type="submit">Сохранить</button>
</form>

<?php _footer(); ?>
