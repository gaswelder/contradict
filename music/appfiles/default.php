<?php
lib( "dx" );

dx::output( main() );

function main()
{
	switch( poparg() )
	{
		case "albums":
			return q_albums();
		default:
			return dx::error( "Unknown path" );
	}
}

function q_albums()
{
	$id = poparg();
	$field = poparg();
	if( !$id || !$field ) {
		return dx::error( "Missing id or field" );
	}

	$get = $_SERVER["REQUEST_METHOD"] == "GET";

	$a = new release( $id );
	if( !$a->exists() ) {
		return dx::error( "Invalid id" );
	}

	$fields = array( "tracklist", "lineup", "studios", "lyrics", "cover" );
	if( in_array( $field, $fields ) )
	{
		$func = "q_$field";
		if( $get ) $func .= "_get";
		else $func .= "_post";
		return $func( $id );
	}

	$allowed = array( "info" );
	if( !in_array( $field, $allowed ) ) {
		return dx::error( "Invalid field name: '$field'" );
	}

	if( $get ) {
		return $a->$field();
	}
	else {
		$data = vars::post( "value" );
		if( $data === null ) {
			return dx::error( "Missing value" );
		}
		$a->$field( $data );
		$a->save();
	}
}


function q_tracklist_get( $id ) {
	return dbfmt::format_tracklist( Database::tracks( $id ) );
}

function q_tracklist_post( $id )
{
	$s = vars::post( "value" );
	if( $s === null ) return dx::error( "Missing value" );

	$list = dbfmt::parse_tracklist( $s, $err );
	if( $err ) {
		return dx::error( $err );
	}

	DB::exec( "START TRANSACTION" );

	/*
	 * Detach all tracks.
	 */
	DB::exec( "DELETE FROM release_tracks
		WHERE release_id = %d", $id );
	foreach( $list as $i => $t )
	{
		/*
		 * If track id is given, update it.
		 * If not, create new.
		 */
		if( $t['id'] )
		{
			DB::exec( "UPDATE tracks
				SET name = '%s',
				comment = '%s',
				length = SEC_TO_TIME(%d)
				WHERE id = %d",
				$t['name'],
				$t['comment'],
				$t['len'],
				$t['id']
			);
		}
		else
		{
			if( !$t['band_id'] ) {
				DB::exec( "ROLLBACK" );
				return dx::error( "Unknown band for track '$t[name]'" );
			}

			$t['id'] = DB::insertRecord( "tracks", array(
				'band_id' => $t['band_id'],
				'name' => $t['name'],
				'comment' => $t['comment'],
				'length' => format_length( $t['len'], true )
			));
		}

		/*
		 * Attach the track to the release.
		 */
		DB::insertRecord( "release_tracks", array(
			'release_id' => $id,
			'track_id' => $t['id'],
			'num' => $i + 1
		));
	}

	/*
	 * Clean unattached tracks.
	 */
	DB::exec( "DELETE FROM tracks
		WHERE id NOT IN (SELECT track_id FROM release_tracks)" );

	DB::exec( "COMMIT" );
}

function q_lineup_get( $id ) {
	return dbfmt::format_lineup( Database::release_lineup( $id ) );
}

function q_lineup_post( $id )
{
	/*
	 * Get the map of track numbers to track identifiers.
	 */
	$tracks = DB::getRecords(
		"SELECT num, track_id
		FROM release_tracks
		WHERE release_id = %d", $id );
	$tracks = array_column( $tracks, 'track_id', 'num' );

	/*
	 * Parse the lineup string.
	 */
	$s = vars::post( 'value' );
	if( $s === null ) {
		return dx::error( "No value" );
	}
	$L = dbfmt::parse_lineup( $s, $err );
	if( $err ) {
		return dx::error( $err );
	}

	DB::exec( "START TRANSACTION" );
	DB::deleteRecords( "track_performers", array(
		'track_id' => $tracks ) );
	foreach( $L as $l )
	{
		$num = $l['track'];
		if( !isset( $tracks[$num] ) ) {
			DB::exec( "ROLLBACK" );
			return dx::error( "Unknown track number: $num" );
		}
		DB::insertRecord( 'track_performers', array(
			'track_id' => $tracks[$num],
			'person_id' => get_person_id( $l['name'] ),
			'role' => $l['role'],
			'stagename' => $l['stagename'],
			'guest' => $l['guest']
		));
	}
	DB::exec( "COMMIT" );
}

function get_person_id( $name )
{
	$name = trim( $name );
	if( !$name ) {
		trigger_error( "Empty name" );
		return null;
	}

	$R = DB::getValues( "SELECT id FROM people
		WHERE name = '%s' LIMIT 2", $name );
	$n = count( $R );
	if( $n == 0 ) {
		return DB::insertRecord( 'people', array(
			'name' => $name
		));
	}

	if( $n == 1 ) {
		return $R[0];
	}

	trigger_error( "Ambiguous name: $name" );
	return null;
}

function q_studios_get( $album_id ) {
	return dbfmt::format_studios( $album_id );
}

function q_studios_post( $album_id )
{
	$s = vars::post( 'value' );
	if( $s === null ) return dx::error( "No value" );
	$S = dbfmt::parse_studios( $s );

	DB::exec( "START TRANSACTION" );

	$tracks = DB::getValues( "SELECT track_id
		FROM release_tracks
		WHERE release_id = %d
		ORDER BY num", $album_id );

	if( !empty( $tracks ) )
	{
		$list = '('.implode( ',', $tracks ).')';
		DB::exec( "DELETE FROM track_studios
			WHERE track_id IN $list" );
	}

	foreach( $S as $s )
	{
		$sid = $s['studio_id'];
		foreach( $s['tracks'] as $num )
		{
			if( !isset( $tracks[$num-1] ) ) {
				DB::exec( "ROLLBACK" );
				return dx::error( "Unknown track number: $num" );
			}
			foreach( $s['roles'] as $role ) {
				DB::insertRecord( 'track_studios', array(
					'track_id' => $tracks[$num-1],
					'studio_id' => $sid,
					'role' => $role
				));
			}
		}
	}
	DB::exec( "COMMIT" );
}

function q_lyrics_get( $album_id ) {
	return dbfmt::format_lyrics( $album_id );
}

function q_lyrics_post( $album_id ) {
	$s = vars::post( 'value' );
	if( $s === null ) return dx::error( "No value" );
	$T = dbfmt::parse_lyrics( $s, $err );
	if( $err ) {
		return dx::error( $err );
	}

	$tracks = DB::getRecords( "
		SELECT LOWER(t.name) AS name, t.id
		FROM release_tracks rt
		JOIN tracks t ON t.id = rt.track_id
		WHERE rt.release_id = %d
		ORDER BY rt.num", $album_id
	);
	$tracks = array_column( $tracks, 'id', 'name' );

	DB::exec( "START TRANSACTION" );
	foreach( $T as $t )
	{
		$name = mb_strtolower( $t['name'] );
		if( !isset( $tracks[$name] ) ) {
			DB::exec( "ROLLBACK" );
			return dx::error( "Unknown track: $name" );
		}
		$tid = $tracks[$name];

		DB::updateRecord( "tracks",
			array( 'lyrics' => $t['text'] ),
			array( 'id' => $tid )
		);
	}

	DB::exec( "COMMIT" );
}

function q_cover_post( $id )
{
	$f = uploaded_files( 'value' );
	if( count( $f ) != 1 ) {
		return dx::error( "Exactly one file expected" );
	}
	$f = $f[0];
	if( $f['error'] ) {
		return dx::error( $f['error'] );
	}
	$size = getimagesize( $f['tmp_name'] );
	if( !$size ) {
		return dx::error( "Could not get image info" );
	}

	if( $size['mime'] != "image/jpeg" ) {
		return dx::error( "JPEG file expected" );
	}

	$path = "covers/$id.jpg";
	$stamp = null;
	if( file_exists( $path ) ) {
		$stamp = date( 'Y-m-d' );
		rename( $path, "covers/$id-$stamp.jpg" );
	}

	if( !move_uploaded_file( $f['tmp_name'], "covers/$id.jpg" ) ) {
		if( $stamp ) {
			rename( "covers/$id-$stamp.jpg", $path );
		}
		return dx::error( "Could not move file" );
	}

	if( !$stamp ) {
		$r = new release( $id );
		$r->coverpath( $path );
		$r->save();
	}
}

function q_engineers_get( $id ) {
	return dbfmt::format_engineers( $id );
}

function q_engineers_post( $id ) {
	$s = vars::post( "value" );
	if( $s === null ) return dx::error( "Missing value" );
	
	$E = dbfmt::parse_engineers( $s );
}

?>
