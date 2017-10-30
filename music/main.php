<?php

use havana\app;
use havana\dbobject;
use havana\request;
use havana\response;

require __DIR__.'/../hl/main.php';

class Studio extends dbobject
{
	const TABLE_NAME = 'studios';
}

class files
{
	static function writedir()
	{
		return __DIR__.'/var';
	}

	static function get($name)
	{
		$path = self::writedir().'/'.$name;
		if(!file_exists($path)) {
			return null;
		}
		return file_get_contents($path);
	}

	static function save($name, $data)
	{
		$path = self::writedir().'/'.$name;
		return file_put_contents($path, $data);
	}
}

$app = new app(__DIR__);

$app->get('/', function() {
	return tpl('home', ['albums' => Release::todays()]);
});

$app->get('/bands/{\d+}', function($id) {
	$band = Band::get($id);
	if (!$band) return 404;
	return tpl('band', compact('band'));
});

$app->get('/bands', function() {
	return tpl('bands');
});

$app->get('/albums/new', function() {
	return tpl('edit/album-new');
});

$app->post('/albums', function() {
	$data = json_decode(request::post('data'), true);

	db()->exec('start transaction');

	$album = new Release;
	$album->name = $data['name'];
	$album->year = $data['year'];
	$album->label = $data['label'] ?? '';
	$album->save();

	foreach ($data['parts'] as $part) {

		$bands = Band::find(['name' => $part['band']]);
		if (count($bands) > 1) {
			panic("Ambiguous band name: $part[band]");
		}
		if (count($bands) == 1) {
			$band = $bands[0];
		} else {
			$band = new Band();
			$band->name = $part['band'];
			$band->save();
		}

		$lineup = [];
		foreach ($part['lineup'] as $name => $roles) {
			$people = Person::find(['name' => $name]);
			if (count($people) > 1) {
				panic("Ambiguous person name: $name");
			}
			if (count($people) == 0) {
				$person = new Person();
				$person->name = $name;
				$person->save();
			} else {
				$person = $people[0];
			}
			$lineup[] = [$person, $roles];
		}

		foreach ($part['tracks'] as $i => $data) {
			$track = new Track;
			$track->band_id = $band->id;
			$track->length = $data['length'];
			$track->name = $data['name'];
			$track->album_id = $album->id;
			$track->num = $i + 1;
			$track->save();
		}
	}

	db()->exec('commit');

	return response::redirect('/albums/'.$album->id);
});

$app->get('/albums/{\d+}', function($id) {
	$album = Release::get($id);
	if(!$album) return 404;
	return tpl('album', ['album' => $album]);
});

$app->get('/albums/{\d+}/json', function($id) {
	$album = Release::get($id);
	if (!$album) return 404;
	return $album->toJSON();
});

$app->get('/albums/{\d+}/newcover', function($id) {
	$album = Release::get($id);
	if (!$album) return 404;
	return tpl('edit/newcover', compact('album'));
});

$app->post('/albums/{\d+}/newcover', function($id) {
	$album = Release::get($id);
	if (!$album) return 404;
	request::files('file')[0]->saveTo('covers/'.$id.'.jpg');
	$album->coverpath = 'covers/'.$id.'.jpg';
	$album->save();
	return response::redirect('/albums/'.$id);
});

$app->get('/rationale', function() {
	return tpl('rationale');
});

$app->get('/search', function() {
	$q = request::get('q');
	$bands = Band::search($q);
	return tpl('search', compact('q', 'bands'));
});

$app->get('/edit', function() {
	return tpl('edit/index');
});

$app->get('/edit/bands', function() {
	return tpl('edit/band-new');
});

$app->post('/edit/bands', function() {
	$name = request::post('name');
	$band = new Band;
	$band->name = $name;
	$id = $band->save();
	return response::redirect('/music/edit/bands/'.$id);
});

$app->get('/edit/bands/{\d+}', function($id) {
	$band = Band::get($id);
	if(!$band) return 404;
	return tpl('edit/band', compact('band'));
});

$app->get('/edit/albums', function() {
	return tpl('edit/album-new');
});

function msg($m) {
	echo $m, "\n";
}

function counter($id) {
	static $counters = [];
	if (!isset($counters[$id])) {
		$counters[$id] = 0;
	}
	$counters[$id]++;
	return $counters[$id];
}

$app->run();
