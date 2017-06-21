<?= tpl('header') ?>

<nav id="navbar">
	<ol class="breadcrumbs">
		<li>Tasks</li>
	</ol>
</nav>

<h1>Projects</h1>

<?php foreach($projects as $project): ?>
	<article class="project-list-item">
		<b><a href="/tasks/{{$project->id}}">{{$project->name}}</a></b>
		<p>{{$project->tasksCount()}} open tasks</p>
	</article>
<?php endforeach; ?>

<?= tpl('footer') ?>
