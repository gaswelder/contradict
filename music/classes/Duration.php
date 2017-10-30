<?php

class Duration
{
	private $seconds;

	function __construct($seconds) {
		$this->seconds = $seconds;
	}

	function format() {
		$t = $this->seconds;
		$s = $t % 60;
		$t = floor( $t / 60 );
	
		$m = $t % 60;
		$t = floor( $t / 60 );
	
		$h = $t;
	
		if($h) {
			return sprintf( "%d:%02d:%02d", $h, $m, $s );
		}
		return sprintf( "%d:%02d", $m, $s );
	}
}
