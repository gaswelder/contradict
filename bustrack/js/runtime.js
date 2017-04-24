require('babel-polyfill');

if (!window.Promise) {
	window.Promise = require('promise-polyfill');
}

require('whatwg-fetch');
