<?php

require __DIR__.'/../hl/app.php';

ini_set('display_errors', 'on');


class TrackStudio extends dbobject
{
	const TABLE_NAME = 'track_studios';
}

class AlbumStudio
{
	public $roles = [];
	public $track_ids = [];
	public $studio_id;

	function push($k, $v)
	{
		if(in_array($v, $this->$k)) return;
		$this->{$k}[] = $v;
	}
}

class TrackPerformer extends dbobject
{
	const TABLE_NAME = 'track_performers';
}

class TrackStaff extends dbobject
{
	const TABLE_NAME = 'track_staff';
}

class Studio extends dbobject
{
	const TABLE_NAME = 'studios';
}

class AlbumArtist extends dbobject
{
	const TABLE_NAME = 'album_artists';
}

class Performer
{
	public function person()
	{
		return Person::get($this->person_id);
	}
}

class Person extends dbobject
{
	const TABLE_NAME = 'people';
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

function array_alt( $array, $key, $default_value )
{
	if( array_key_exists( $key, $array ) ) {
		return $array[$key];
	}
	else return $default_value;
}


/*
 * Returns its first non-"empty" argument or the last argument if all
 * are "empty".
 * alt( "hello", "world" ) // "hello"
 * alt( "", "world" ) // "world"
 */
function alt( $value1, $value2 )
{
	$args = func_get_args();
	$n = count( $args );
	for( $i = 0; $i < $n; $i++ ){
		if( $args[$i] ){
			return $args[$i];
		}
	}
	return $args[$n-1];
}

/*
 * Returns true if at least one track from the tracklist has length
 * more than an hour.
 */
function have_hours( $T ) {
	foreach( $T as $t ) {
		if( $t['len'] > 3600 ) return true;
	}
	return false;
}

/*
 * Formats given length in seconds as a string. If $hours is true,
 * includes the hours part even if it is zero.
 */
function formatDuration( $t, $hours = false )
{
	$s = $t % 60;
	$t = floor( $t / 60 );

	$m = $t % 60;
	$t = floor( $t / 60 );

	$h = $t;

	if( $h || $hours ) {
		return sprintf( "%d:%02d:%02d", $h, $m, $s );
	}
	return sprintf( "%d:%02d", $m, $s );
}

function format_ranges( $a, $dash = '-' )
{
	if( count( $a ) < 2 ) {
		if( count( $a ) == 1 ) {
			return $a[0];
		}
		else {
			return "";
		}
	}

	sort( $a );
	$min = $a[0];
	$max = $a[count( $a ) - 1];

	$groups = array();
	$group = array( $min, $max );
	$n = 0;
	foreach( $a as $next )
	{
		if( $next == $n + 1 ) {
			$n = $next;
			continue;
		}

		$group[1] = $n;
		$groups[] = $group;
		$n = $next;
		$group = array( $n, $max );
	}
	$groups[] = $group;

	$S = array();
	foreach( $groups as $g )
	{
		if( $g[0] == $g[1] ) {
			$S[] = $g[0];
		}
		else if( $g[0] == $g[1] - 1 ) {
			$S[] = "$g[0], $g[1]";
		}
		else {
			$S[] = "$g[0]$dash$g[1]";
		}
	}

	return implode( ", ", $S );
}

function parse_ranges( $s )
{
	$a = array();
	$parts = array_map( 'trim', explode( ',', $s ) );
	foreach( $parts as $p )
	{
		$pos = strpos( $p, '-' );
		if( $pos === false ) {
			$a[] = intval( $p );
			continue;
		}
		list( $from, $to ) = explode( '-', $p, 2 );
		$a = array_merge( $a, range( $from, $to ) );
	}
	return $a;
}




function main_page_ids()
{
	$date = date( "Y-m-d" );

	$data = files::get( "main_page_releases" );
	if( $data ) $data = unserialize( $data );

	if( !$data || $data['date'] != $date ) {
		$random = Release::getRandom(9);
		$ids = array_map(function($release) {
			return $release->id;
		}, $random);
		$data = array( 'date' => $date, 'ids' => $ids );
		files::save( "main_page_releases", serialize( $data ) );
	}
	return $data['ids'];
}



$app = new App(__DIR__);

$app->setPrefix('/music');

$app->get('/', function() {
	$ids = main_page_ids();
	$albums = Release::getMultiple($ids);
	return tpl('home', ['albums' => $albums]);
});

$app->get('/bands/{\d+}', function($id) {
	$band = Band::get($id);
	if (!$band) return 404;
	return tpl('band', compact('band'));
});

$app->get('/bands', function() {
	return tpl('bands');
});

$app->get('/albums/{\d+}', function($id) {
	$album = Release::get($id);
	if(!$album) return 404;
	return tpl('album', ['album' => $album]);
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

function year($s)
{
	preg_match('/(\d\d\d\d)-(\d\d)-(\d\d)/', $s, $m);
	if(!$m) {
		throw new Exception("Couldn't parse datetime: '$s'");
	}
	return $m[1];
}

class SilentBob
{
	private $__data;

	function __construct($data)
	{
		$this->__data = $data;
	}

	static function wrap($v)
	{
		if(is_scalar($v)) return $v;
		if(is_array($v)) {
			return array_map(function($element) {
				return self::wrap($element);
			}, $v);
		}
		return new static($v);
	}

	function __get($k)
	{
		if(property_exists($this->__data, $k)) {
			return self::wrap($this->__data->$k);
		}
		return null;
	}
}

function coalesce(...$args) {
	foreach($args as $arg) {
		if($arg) break;
	}
	return $arg;
}

$app->post('/edit/albums', function() {
	$data = request::post('data');
	$data = json_decode($data);
	if(!$data) {
		throw new Exception("Couldn't parse JSON data: ".json_last_error_msg());
	}
	$data = new SilentBob($data);

	db()->begin();

	$band = Band::findOrCreate(["name" => $data->band]);

	$album = new Release();
	$album->label = $data->label->name;
	$album->date = $data->label->date;
	$album->name = $data->name;
	$album->year = year($album->date);
	$album->save();

	foreach($data->tracklist as $trackData) {
		$track = new Track();
		$track->album_id = $album->id;
		$track->band_id = $band->id;
		$track->name = $trackData->name;
		$track->length = $trackData->length;
		if(strlen($track->length < 6)) {
			$track->length = '00:'.$track->length;
		}
		$track->comment = coalesce($trackData->comment, '');
		$track->save();

		foreach($data->lineup as $lineupData) {
			$person = Person::findOrCreate(["name" => $lineupData->name]);
			foreach($lineupData->roles as $role) {
				$p = new TrackPerformer();
				$p->track_id = $track->id;
				$p->person_id = $person->id;
				$p->role = $role;
				$p->save();
			}
		}

		foreach($data->staff as $staffData) {
			$person = Person::findOrCreate(["name" => $staffData->name]);
			foreach($staffData->roles as $role) {
				$p = new TrackStaff();
				$p->track_id = $track->id;
				$p->person_id = $person->id;
				$p->role = $role;
				$p->save();
			}
		}

		foreach($data->studios as $studioData) {
			$studio = Studio::findOrCreate(["name" => $studioData->name]);
			foreach($studioData->roles as $role) {
				$s = new TrackStudio();
				$s->track_id = $track->id;
				$s->studio_id = $studio->id;
				$s->role = $role;
				$s->save();
			}
		}
	}

	foreach($data->cover->artists as $artistData) {
		$person = Person::findOrCreate(["name" => $artistData->name]);
		$a = new AlbumArtist();
		$a->person_id = $person->id;
		$a->release_id = $album->id;
	}

	db()->end();

	dd($data);
});

$app->post('/edit/bands/{\d+}', function($id) {
	dd($_POST);
});



$app->run();
