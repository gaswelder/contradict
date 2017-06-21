<?php

class fmt
{
	/**
	 * Parses a length string in format [hours:]minutes:seconds and
	 * returns total number of seconds.
	 *
	 * @param string $s
	 * @return int
	 */
	static function parseDuration($s)
	{
		$len = 0;
		$parts = explode( ':', $s );

		/*
		 * Check that all parts are numeric.
		 */
		$n = count( array_filter( array_map( 'is_numeric', $parts ) ) );
		if( $n != count( $parts ) ) return false;

		/*
		 * At least h:s must be present.
		 */
		if( count( $parts ) < 2 ) {
			return null;
		}

		/*
		 * Seconds.
		 */
		$p = array_pop( $parts );
		if( $p < 0 || $p > 59 ) return null;
		$len += $p;

		/*
		 * Minutes.
		 */
		$p = array_pop( $parts );
		if( $p < 0 || $p > 59 ) return null;
		$len += $p * 60;

		/*
		 * Hours.
		 */
		$p = array_pop( $parts );
		if( $p ) {
			$len += $p * 3600;
		}

		return $len;
	}
}
