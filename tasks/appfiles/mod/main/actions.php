<?php

namespace main;

use h3;
use h3\response;
use h3\user;
use h3\request;
use h3\lib\uploads;
use t, comment, task;

class actions
{
	static function add_comment()
	{
		$uid = user::id();

		$tid = request::post( 'task_id' );
		$text = request::post( 'text' );

		if( !task_permission( $uid, $tid, 'add-comment' ) ) {
			return response::FORBIDDEN;
		}

		$c = new comment();
		$c->author_id( $uid );
		$c->task_id( $tid );
		$c->text( $text );
		$cid = $c->save();

		$files = uploads::save(uploads::get('files'), 'uploads');
		if( $files ) {
			foreach( $files as $path ) {
				t::add_comment_file( $cid, $path );
			}
		}

		if( request::post( 'subscribe' ) ) {
			t::subscribe_user( $uid, $tid );
		}

		$t = new task( $tid, 'name' );
		$title = 'Comment on task #'.$tid;
		$text = $t->name() . "\n\n" . $text;
		$text .= "\n\n".request::post( 'task_url' );

		$S = t::get_subscribers_r( $tid );
		foreach( $S as $s )
		{
			if( $s['user_id'] == $uid ) continue;
			$email = $s['email'];
			if( !$email ) continue;
			send_mail( $email, $text, $title );
		}

		return response::redirect(h3::url('main\main::task', $tid));
	}

	static function take_task()
	{
		$user_id = user::id();
		$task_id = request::post( 'task-id' );

		if( !$user_id || !$task_id )
			return response::BADREQ;

		$t = new task( $task_id );
		$t->worker_id( $user_id );
		$t->save();
	}

	static function check_task()
	{
		$user_id = user::id();
		$task_id = request::post( 'task-id' );
		if( !$user_id || !$task_id )
			return response::BADREQ;

		$t = new task( $task_id );
		$t->status( 'done' );
		$hours = floatval( request::post( 'hours' ) );
		$t->hours( $hours );
		$t->save();
	}

	static function uncheck_task()
	{
		$user_id = user::id();
		$task_id = request::post( 'task-id' );
		if( !$user_id || !$task_id )
			return response::BADREQ;

		$t = new task( $task_id );
		$t->status( 'open' );
		$t->save();
	}

	static function close_task()
	{
		$user_id = user::id();
		$task_id = request::post( 'task-id' );

		// TODO: check permissions

		$t = new task( $task_id );
		$t->status( 'closed' );
		$t->save();
	}

	static function create_task()
	{
		$uid = user::id();

		$t = new task();
		$t->name( rus_text_stylize( request::post( 'name' ) ) );
		$t->priority( request::post( 'priority' ) );
		$t->project_id( request::post( 'project_id' ) );
		$t->author_id( $uid );
		$tid = $t->save();

		$desc = request::post( 'description' );
		$files = accept_uploads( 'files', 'uploads' );
		if( !empty( $files ) || $desc )
		{
			$c = new comment();
			$c->task_id( $tid );
			$c->author_id( $uid );
			$c->text( $desc );
			$cid = $c->save();
			foreach( $files as $path ) {
				t::add_comment_file( $cid, $path );
			}
		}

		if( request::post( 'subscribe' ) ) {
			t::subscribe_user( $uid, $tid );
		}

		redirect(url_t('tasks/'.$tid));
	}

	static function change_task_priority()
	{
		$user_id = user::id();
		$task_id = request::post( 'task-id' );
		$priority = request::post( 'priority' );

		if( !$user_id || !$task_id )
			return response::BADREQ;

		$t = new task( $task_id );
		if( $t->status() == 'closed' || $t->status() == 'cancelled' ) {
			$t->status( 'open' );
		}
		$t->priority( $priority );
		$t->save();
	}

	static function change_settings()
	{
		$user_id = user::id();
		$email = request::post( 'email' );
		$name = request::post( 'name' );
		$u = new user_record( $user_id );
		$u->email( $email );
		$u->name( $name );
		$u->save();
	}

	static function delete_task()
	{
		$user_id = user::id();
		$task_id = request::post( 'task-id' );

		if( !task_permission( $user_id, $task_id, 'delete-task' ) ) {
			return response::FORBIDDEN;
		}

		tasks::delete( $task_id );
	}

	static function cancel_task()
	{
		$user_id = user::id();
		$task_id = request::post( 'task-id' );

		// TODO: check permissions

		$t = new task( $task_id );
		$t->status( 'cancelled' );
		$t->save();
	}

	static function edit_participant()
	{
		$pid = request::post( 'project-id' );
		$uid = request::post( 'user-id' );
		$perms = request::posts( 'perm-' );
		t::set_project_permissions( $uid, $pid, $perms );
	}

	static function create_project()
	{
		$p = new project();
		$p->name( request::post( 'name' ) );
		$p->author_id( user::id() );
		$p->save();
	}

	static function change_password()
	{
		$old = request::post( 'old-password' );
		$new1 = request::post( 'new-password-1' );
		$new2 = request::post( 'new-password-2' );

		if( $new1 != $new2 ) trigger_error('new1 != new2');
		if( !t::check_user_password( user::login(), $old ) ) {
			return 'wrong old password';
		}

		$u = new user_record( user::id() );
		$u->password_hash( PasswordHash::generate( $new1 ) );
		$u->save();
	}

	static function add_user()
	{
		$login = request::post( 'login' );
		$pass = request::post( 'password' );
		$name = request::post( 'name' );
		if( !$login ) return response::BADREQ;
		t::create_user( $login, $pass, $name );
	}

	static function subscribe()
	{
		$uid = user::id();
		$tid = request::post( 'task-id' );

		if( request::post( 'subscribe' ) ) {
			t::subscribe_user( $uid, $tid );
		} else {
			t::unsubscribe_user( $uid, $tid );
		}
	}
}

?>
