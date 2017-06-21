<?= tpl('header') ?>

<nav id="navbar">
	<ol class="breadcrumbs">
		<li><a href="/tasks">Tasks</a></li>
		<li><a href="/tasks/{{$task->project()->id}}">{{$task->project()->name}}</a></li>
		<li>#{{$task->id}}</li>
	</ol>
</nav>

<div id="content">
	<h1>#{{$task->id}}. {{$task->name}}</h1>

	<?php if(count($comments) == 0): ?>
		<p>No comments for this task.</p>
	<?php endif ?>

	<?php foreach($comments as $comment): ?>
		<?= tpl('task-comment', compact('comment')) ?>
	<?php endforeach ?>

	<form method="post" enctype="multipart/form-data" class="comment-form">
		<input type="hidden" name="task_id" value="{{$task->id}}">
		<div>
			<textarea name="text" placeholder="Comment" required></textarea>
		</div>
		<button type="submit">Add</button>
	</form>
</div>

<form method="post">
	<input type="hidden" name="task-id" value="{{$task->id}}">
	<input name="priority" value="{{$task->priority}}" type="number" min="-1" step="1" size="2">
	<button type="submit">Set</button>
</form>

<?= tpl('footer') ?>
