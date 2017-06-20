const client = {
	addEvent(data) {
		var p = [
			['bus', data.bus],
			['stop', data.stop],
			['time', Math.round(data.time.getTime() / 1000)]
		];

		var body = p.map(x => x[0] + '=' + encodeURIComponent(x[1])).join('&');

		return fetch('events', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded'
			},
			body: body
		})
	},

	init() {
		return fetch('init')
			.then(function(response) {
				if (response.status != 200) {
					throw new Error(response.status + ': ' + response.statusText);
				}
				return response.json();
			});
	},

	getLog() {
		return fetch('events')
			.then(response => response.json())
	}
};

export default client;
