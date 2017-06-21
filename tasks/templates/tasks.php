<?= tpl('header') ?>

<nav id="navbar">
	<ol class="breadcrumbs">
		<li><a href="/tasks">Tasks</a></li>
		<li>{{$project->name}}</li>
	</ol>
</nav>

<h1>{{$project->name}}</h1>

<a class="new-task-link" href="/tasks/new">New task</a>

<?php foreach($tasks as $task): ?>
	<article class="tasks-list-item {{$task->status}}">
		<a href="/tasks/task/{{$task->id}}"><h2>{{$task->name}}</h2></a>
		<?php if($task->status == 'open'): ?>
			<p class="priority">{{$task->priority}}</p>
		<?php else: ?>
			<p>{{$task->status}}</p>
		<?php endif ?>
	</article>
<?php endforeach; ?>

<?= tpl('footer') ?>
