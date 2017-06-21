function DX( baseUrl )
{
	/*
	 * RTT estimation and time of the last request.
	 */
	var rtt = 0;
	var t = 0;

	this.RTT = function() { return rtt; }

	this.get = function( path, args )
	{
		t = Date.now();
		var url = baseUrl + '/' + path;
		return http.get( url, args ).then( check );
	};

	this.post = function( path, data )
	{
		t = Date.now();
		var url = baseUrl + '/' + path;
		return http.post( url, data ).then( check );
	};

	this.postForm = function( path, formData )
	{
		t = Date.now();
		var url = baseUrl + '/' + path;
		return http.postForm( url, formData ).then( check );
	};

	function check( data )
	{
		rtt = Date.now() - t;

		if( typeof data != "object" ) {
			throw "Malformed response";
		}
		if( !("errno" in data) || !("errstr" in data) ) {
			throw "Wrong response format";
		}
		if( data.errno ) {
			throw data.errstr;
		}
		return data.data;
	}
}
