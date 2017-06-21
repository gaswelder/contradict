<?php

class dbfmt
{
	static function format_tracklist( $list )
	{
		$s = "";
		foreach( $list as $t )
		{
			$line = "[$t[id]] <$t[band_id]> $t[name] - " . format_length( $t['len'] );
			if( $t['comment'] ) {
				$line .= " [$t[comment]]";
			}
			$s .= $line . "\n";
		}
		return $s;
	}

	static function parse_tracklist( $s, &$err )
	{
		$lines = array_map( 'trim', preg_split( '/\r?\n/', $s ) );
		$R = array();
		$number = 0;
		foreach( $lines as $line )
		{
			if( strlen( $line ) == 0 ) continue;
			$number++;

			$r = array(
				'id' => '',
				'band_id' => '',
				'num' => $number,
				'name' => '',
				'length' => '',
				'comment' => ''
			);

			/*
			 * If there is [...] marker at the beginning, it's the id.
			 */
			if( $line[0] == '[' )
			{
				$pos = strpos( $line, ']' );
				if( !$pos ) {
					$err = "']' expected at line $n";
					return null;
				}

				$r['id'] = trim( substr( $line, 1, $pos - 1 ) );
				$line = ltrim( substr( $line, $pos + 1 ) );

				if( !is_numeric( $r['id'] ) ) {
					$err = "Non-numeric identifier: $r[id]";
					return null;
				}
			}

			/*
			 * If there is <...>, it's the band identifier.
			 */
			if( $line[0] == '<' )
			{
				$pos = strpos( $line, '>' );
				if( $pos === false ) {
					$err = "'>' expected";
					return null;
				}
				$r['band_id'] = intval( substr( $line, 1, $pos - 1 ) );
				$line = ltrim( substr( $line, $pos + 1 ) );
			}

			/*
			 * If there is [...] at the end, it's a comment.
			 */
			if( substr( $line, -1 ) == "]" ) {
				$pos = strrpos( $line, "[" );
				if( $pos === false ) {
					$err = "unmatched ']' at the end.";
					return null;
				}
				$r['comment'] = substr( $line, $pos + 1, -1 );
				$line = rtrim( substr( $line, 0, $pos ) );
			}

			/*
			 * Pop the length from the end.
			 */
			$pos = strrpos( $line, "-" );
			if( $pos === false ) {
				$err = "No '-' markers";
				return null;
			}
			$lenstr = trim( substr( $line, $pos + 1 ) );
			$line = trim( substr( $line, 0, $pos ) );
			$r['len'] = parse_length( $lenstr );
			if( !$r['len'] ) {
				$err = "Could not parse length: '$lenstr'";
				return null;
			}

			/*
			 * The rest is the name.
			 */
			$r['name'] = $line;
			$R[] = $r;
		}
		return $R;
	}

	static function format_lineup( $L )
	{
		/*
		 * Group the records to obtain hash => (desc, track numbers)
		 */
		$G = array();
		foreach( $L as $i => $l )
		{
			$hash = md5( "$l[stagename]|$l[name]|$l[roles]|$l[guest]" );
			if( !isset( $G[$hash] ) ) {
				$G[$hash] = array(
					'tracks' => array(),
					'person_id' => $l['person_id'],
					'name' => $l['name'],
					'stagename' => $l['stagename'],
					'roles' => $l['roles'],
					'guest' => $l['guest']
				);
			}
			$G[$hash]['tracks'][] = $l['num'];
		}

		/*
		 * Compress track lists to editable form.
		 */
		foreach( $G as $hash => $g ) {
			$G[$hash]['tracks'] = format_ranges( $g['tracks'] );
		}

		$s = "";
		foreach( $G as $g )
		{
			$line = "";

			if( $g['guest'] ) {
				$line .= "+ ";
			}

			if( $g['stagename'] ) {
				$line .= "$g[stagename] ($g[name])";
			}
			else {
				$line .= $g['name'];
			}

			$line .= " - $g[roles] [$g[tracks]]";
			$s .= $line . "\n";
		}

		return $s;
	}

	static function parse_lineup( $s, &$err )
	{
		$lines = array_filter(
			array_map( 'trim', preg_split( '/\r?\n/', $s ) ),
			'strlen'
		);

		foreach( $lines as $line )
		{
			if( $line == "" ) continue;

			$r = array(
				'guest' => 0,
				'roles' => '',
				'name' => '',
				'stagename' => '',
				'tracks' => ''
			);

			if( $line[0] == "+" ) {
				pop_left( $line, "+" );
				$r['guest'] = 1;
			}

			/*
			 * If ends with [...], it's a tracks enumerator.
			 */
			$s = pop_right( $line, "[", "]" );
			if( $s ) {
				$r['tracks'] = $s;
			}

			/*
			 * Pop roles from the end.
			 */
			$r['roles'] = pop_right( $line, "-" );
			if( !$r['roles'] ) {
				$err = "'-' expected";
				return null;
			}

			/*
			 * The rest is a name in one of two forms:
			 * (1) stagename (name)
			 * (2) name
			 */
			$s = pop_right( $line, "(", ")" );
			if( $s ) {
				$r['name'] = $s;
				$r['stagename'] = $line;
			}
			else {
				$r['name'] = $line;
			}

			$R[] = $r;
		}

		$L = array();
		foreach( $R as $r )
		{
			$roles = trim_explode( ",", $r['roles'] );
			$tracks = parse_ranges( $r['tracks'] );
			unset( $r['roles'] );
			unset( $r['tracks'] );

			foreach( $roles as $role ) {
				foreach( $tracks as $track ) {
					$p = array(
						'role' => $role,
						'track' => $track
					);
					$L[] = array_merge( $r, $p );
				}
			}
		}

		return $L;
	}

	static function format_studios( $album_id )
	{
		$R = DB::getRecords( "
			SELECT
				rt.num,
				ts.studio_id,
				s.name,
				GROUP_CONCAT( ts.role ORDER BY ts.role SEPARATOR ', ' ) AS roles
			FROM release_tracks rt
			JOIN track_studios ts ON ts.track_id = rt.track_id
			JOIN studios s ON s.id = ts.studio_id
			WHERE release_id = %d
			GROUP BY rt.num, ts.studio_id, s.name", $album_id );
		$studios = array();
		foreach( $R as $i => $r )
		{
			$hash = $r['studio_id'].$r['roles'];
			$studios[$hash] = $r;
			$r['hash'] = $hash;
			unset( $r['studio_id'] );
			unset( $r['roles'] );
			$R[$i] = $r;
		}

		$R = group( $R, 'hash' );
		foreach( $R as $hash => $tracks ) {
			$R[$hash] = array_column( $tracks, 'num' );
		}

		$lines = array();
		foreach( $R as $hash => $tracks ) {
			$s = $studios[$hash];
			$list = format_ranges( $tracks );
			$lines[] = "$s[name] - $s[roles] [$list]";
		}
		$s = implode( PHP_EOL, $lines );
		return $s;
	}

	static function parse_studios( $s )
	{
		$lines = array_filter(
			array_map( 'trim', explode( "\n", $s ) ), 'strlen' );
		$S = array();
		foreach( $lines as $line )
		{
			$sname = pop_left( $line, ' - ' );
			$tracks = pop_right( $line, '[', ']' );
			$S[] = array(
				'studio_id' => get_studio_id( $sname ),
				'tracks' => parse_ranges( $tracks ),
				'roles' => array_map( 'trim', explode( ',', $line ) )
			);
		}
		return $S;
	}

	static function format_lyrics( $album_id )
	{
		$T = DB::getRecords( "
			SELECT t.name, t.lyrics
			FROM release_tracks rt
			JOIN tracks t ON t.id = rt.track_id
			WHERE rt.release_id = %d
			ORDER BY rt.num", $album_id );
		$lines = array();
		foreach( $T as $t )
		{
			$lines[] = "# $t[name]\n";
			$lines[] = $t['lyrics'];
		}
		$lines[] = "";
		return implode( "\n", $lines );
	}

	static function parse_lyrics( $s, &$err )
	{
		$tracks = array();
		$lines = trim_explode( "\n", $s );
		while( !empty( $lines ) )
		{
			/*
			 * Get title
			 */
			while( !empty( $lines ) )
			{
				$line = array_shift( $lines );
				if( $line == "" ) continue;
				if( $line[0] != "#" ) {
					$err = "Expected '#', got line '$line'";
					return null;
				}

				$title = trim( substr( $line, 1 ) );
				if( $title == "" ) {
					$err = "Empty title";
					return null;
				}
				break;
			}

			/*
			 * Get text
			 */
			$text = "";
			while( !empty( $lines ) ) {
				if( $lines[0] != "" && $lines[0][0] == "#" ) {
					break;
				}
				$text .= array_shift( $lines )."\n";
			}
			$text = trim( $text );
			$tracks[] = array(
				'name' => $title,
				'text' => $text
			);
		}
		return $tracks;
	}
}

function get_studio_id( $name ) {
	$name = trim( $name );
	if( $name == "" ) {
		trigger_error( "Empty name" );
		return null;
	}
	$id = DB::getValue( "SELECT id FROM studios
		WHERE name = '%s'", $name );
	if( $id ) return $id;
	return DB::insertRecord( 'studios', array( 'name' => $name ) );
}

function trim_explode( $delim, $s, $limit = PHP_INT_MAX ) {
	return array_map( 'trim', explode( $delim, $s, $limit ) );
}

function pop_right( &$line, $d1, $d2 = null )
{
	$a = strrpos( $line, $d1 );
	if( $a === false ) {
		return null;
	}

	if( $d2 )
	{
		$b = strrpos( $line, $d2 );
		if( $b === false || $b < $a ) {
			return null;
		}
		$s = trim( substr( $line, $a + strlen( $d1 ), $b - $a - strlen( $d1 ) ) );
		$line = rtrim( substr( $line, 0, $a ) );
		return $s;
	}

	$s = trim( substr( $line, $a + strlen( $d1 ) ) );
	$line = rtrim( substr( $line, 0, $a ) );
	return $s;
}

function pop_left( &$line, $d1, $d2 = null )
{
	$a = strpos( $line, $d1 );
	if( $a === false ) {
		return null;
	}

	if( $d2 )
	{
		$b = strpos( $line, $d2 );
		if( $b === false || $b < $a ) {
			return null;
		}
		$s = trim( substr( $line, $a + strlen( $d1 ), $b - $a - strlen( $d1 ) ) );
		$line = ltrim( substr( $line, $a + strlen( $d1 ) ) );
		return $s;
	}

	$s = trim( substr( $line, 0, $a ) );
	$line = ltrim( substr( $line, $a + strlen( $d1 ) ) );
	return $s;
}

?>
