"use strict";

var http = (function()
{
	var http = {};

	/*
	 * Send a GET request using vars as query arguments.
	 * The URL may have arguments too.
	 */
	http.get = function( url, vars )
	{
		if( vars ) {
			url = createURL( url, vars );
		}
		var req = new XMLHttpRequest();
		var p = promise( req );
		req.open( "GET", url );
		req.send();
		return p;
	};

	/*
	 * Send a POST request using data as post values.
	 */
	http.post = function( url, data )
	{
		var req = new XMLHttpRequest();
		var p = promise( req );
		req.open( "POST", url );
		var body = encodeForm( data );
		req.setRequestHeader( "Content-Type", "application/x-www-form-urlencoded" );
		req.send( body );
		return p;
	};

	http.postForm = function( url, formData )
	{
		if( !formData instanceof FormData ) {
			throw new TypeError( "formData must be a FormData object" );
		}

		var req = new XMLHttpRequest();
		var p = promise( req );
		req.open( "POST", url );
		req.send( formData );
		return p;
	};

	function encodeForm( data )
	{
		var lines = [];
		for( var k in data ) {
			lines.push( k + "=" + encodeURIComponent( data[k] ) );
		}
		return lines.join( "&" );
	}

	/*
	 * Creates urls. "vars" is a dict with query vars. "base" can have
	 * variables in it too.
	 * Example: createURL( '/?v=json&b=mapdata', {p: bounds, lat: ...} )
	 */
	function createURL( base, vars )
	{
		var url = base;
		var haveQ = url.indexOf( '?' ) != -1;

		for( var i in vars )
		{
			if( typeof vars[i] == "undefined" ) continue;

			if( !haveQ ) {
				url += '?';
				haveQ = true;
			} else {
				url += '&';
			}

			url += i + "=" + encodeURIComponent( vars[i] );
		}
		return url;
	};

	/*
	 * Creates a Promise for given XMLHttpRequest object.
	 */
	function promise( req )
	{
		var ph = {};

		var p = new Promise( function( ok, fail ) {
			ph.ok = ok;
			ph.fail = fail;
		});

		var aborted = false;

		p.abort = function() {
			if( aborted ) return;
			aborted = true;
			req.abort();
		};

		req.onreadystatechange = function() {
			/*
			 * If still working, continue.
			 */
			if( req.readyState != 4 ) { // 4 = DONE
				return;
			}

			/*
			 * If the HTTP response status is not OK, fail the promise
			 * with this status.
			 */
			if( req.status != 200 ) { // 200 = HTTP_OK
				ph.fail( req.statusText );
				return;
			}

			if( aborted ) {
				ph.fail( "aborted" );
				return;
			}

			ph.ok( responseValue( req ) );
		};
		return p;
	}

	function responseValue( req )
	{
		var type = req.getResponseHeader( "Content-Type" );
		if( !type ) type = "text/html; charset=UTF-8";

		if( type.indexOf( "application/json" ) == 0 ) {
			return JSON.parse( req.responseText );
		}

		return req.response;
	}

	return http;
})();
