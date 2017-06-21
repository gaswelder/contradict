<?php

require __DIR__.'/../hl/app.php';
require __DIR__.'/vendor/autoload.php';

use Cake\Collection\Collection;

ini_set('display_errors', 'on');

function markdown_output($s) {
	$p = new \cebe\markdown\Markdown();
	$p->html5 = true;
	return $p->parse($s);
}

$app = new App(__DIR__);

// $app->beforeDispatch(function ($url) {
// 	if (!user::select('user') && $url != '/links/login') {
//         return Response::redirect('/links/login');
//     }
// });

$app->setPrefix('/tasks');

class Project extends dbobject
{
	const TABLE_NAME = 'projects';

	static function all() {
		return self::fromRows(db()->getRecords('select * from projects'));
	}

	function tasks() {
		return Task::fromRows(db()->getRecords('select * from tasks where project_id = ?', $this->id));
	}

	function tasksCount() {
		return db()->getValue('select count(*) from tasks where project_id = ? and "status" = ?', $this->id, 'open');
	}
}

class Task extends dbobject
{
	const TABLE_NAME = 'tasks';

	function comments() {
		return Comment::fromRows(db()->getRecords('select * from comments where task_id = ? order by time_added', $this->id));
	}

	function project() {
		return Project::get($this->project_id);
	}
}

class Comment extends dbobject
{

}



/*
 * Projects list
 */
$app->get('/', function() {
	$projects = Project::all();
	return tpl('main', compact('projects'));
});

/*
 * Tasks list for a project
 */
$app->get('/{\d+}', function($projectId) {
	$project = Project::get($projectId);
	if(!$project) return 404;
	$tasks = (new Collection($project->tasks()))
		->sortBy(function($a) {
			$score = $a->status == 'open' ? 1 : 2;
			$score .= "|$a->status";
			if($a->status == 'open') {
				$score .= '|' . dechex(10 - $a->priority);
			}
			return $score;
		}, SORT_ASC, SORT_STRING)->toArray();


	return tpl('tasks', compact('project', 'tasks'));
});

/*
 * Task view
 */
$app->get('/task/{\d+}', function($taskId) {
	$task = Task::get($taskId);
	if(!$task) return 404;
	$comments = $task->comments();
	return tpl('task', compact('task', 'comments'));
});

/*
 * New project form
 */
$app->get('/new-project', function() {
	return h3::tpl('new-project');
});

/*
 * New task form
 */
$app->get('/new-task/{\d+}', function($projectId) {
	return h3::tpl('new-task');
});


$app->get('/api', function() {
	$projects = Project::all();
	return response::json($projects);
});

$app->get('/api/{\d+}', function($projectId) {
	$project = Project::get($projectId);
	if(!$project) return 404;
	return response::json($project->tasks());
});

$app->get('/api/{\d+}/{\d+}', function($projectId, $taskId) {
	$project = Project::get($projectId);
	if(!$project) return 404;

	$task = Task::get($taskId);
	if(!$task || $task->project_id != $projectId) return 404;

	return response::json($task);
});

$app->get('/api/{\d+}/{\d+}/comments', function($projectId, $taskId) {
	$project = Project::get($projectId);
	if(!$project) return 404;

	$task = Task::get($taskId);
	if(!$task || $task->project_id != $projectId) return 404;

	return response::json($task->comments());
});



// $map = [
// 	'/add-user' => 'main\main::add_user',
// 	'/login' => 'main\main::login',
// 	'/logout' => 'main\main::logout',
// 	'/participant/<\d+>/<\d*>' => 'main\main::participant',
// 	'/project-info/<\d+>' => 'main\main::project_info',
// 	'/settings' => [
// 		'/subscriptions' => 'main\settings::subscriptions',
// 		'/password' => 'main\settings::password',
// 		'/' => 'main\settings::account'
// 	],
// 	'/a' => [
// 		'/check-task' => 'main\actions::check_task',
// 		'/take-task' => 'main\actions::take_task',
// 		'/delete-task' => 'main\actions::delete_task',
// 		'/change-priority' => 'main\actions::change_task_priority',
// 		'/close-task' => 'main\actions::close_task',
// 		'/subscribe' => 'main\actions::subscribe',
// 		'/uncheck-task' => 'main\actinos::uncheck_task',
// 		'/cancel-task' => 'main\actions::cancel_task',
// 		'/change-settings' => 'main\actions::change_settings',
// 		'/change-password' => 'main\actions::change_password',
// 		'/create_project' => 'main\actions::create_project',
// 		'/edit-participant' => 'main\actions::edit_participant',
// 		'/create-task' => 'main\actions::create_task',
// 		'/add-comment' => 'main\actions::add_comment'
// 	],
// 	'/bb' => [
// 		'/projects' => 'bb\main::projects', // list projects
// 		'/projects/<\d+>' => 'bb\main::tasks',
// 		'/tasks/<\d+>' => 'bb\main::task',
// 		'/tasks/<\d+>/comments' => 'bb\main::comments',
// 		'/comments/<\d+>' => 'bb\main::comment'
// 	]
// ];
//


// static function add_user()
// {
// 	return h3::tpl('add-user');
// }
//
// static function login()
// {
// 	$err = '';
// 	if (request::post('login')) {
// 		$login = request::post('login');
// 		$pass = request::post('password');
// 		$id = accounts::check_login($login, $pass);
// 		if ($id) {
// 			user::auth('user', $id);
// 			return response::redirect(h3::url('index'));
// 		}
// 		$err = 'wrong!';
// 	}
// 	return h3::tpl('login', ['err' => $err]);
// }
//
// static function participant()
// {
// 	return h3::tpl('participant');
// }
//
// static function project_info($project_id)
// {
// 	return h3::tpl('project-info');
// }

$app->run();
