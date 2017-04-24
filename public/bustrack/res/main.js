'use strict';

var client = {
	addEvent: function addEvent(data) {
		var p = [['bus', data.bus], ['stop', data.stop], ['time', Math.round(data.time.getTime() / 1000)]];

		var body = p.map(function (x) {
			return x[0] + '=' + encodeURIComponent(x[1]);
		}).join('&');

		return fetch('events', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded'
			},
			body: body
		});
	},
	init: function init() {
		return fetch('init').then(function (response) {
			if (response.status != 200) {
				throw new Error(response.status + ': ' + response.statusText);
			}
			return response.json();
		});
	}
};

var app = new Vue({
	el: '#app',
	data: {
		bus: "",
		stop: "",
		time: new Date(),
		sending: false,
		buses: [],
		stops: [],
		freeze: false
	},

	created: function created() {
		var _this = this;

		client.init().then(function (x) {
			_this.buses = x.buses;
			_this.stops = x.stops;
		});
		setInterval(this.tick.bind(this), 1000);
	},


	methods: {
		tick: function tick() {
			if (!this.freeze) {
				this.time = new Date();
			}
		},
		submit: function submit(e) {
			var _this2 = this;

			this.sending = true;

			var data = {
				bus: this.bus,
				stop: this.stop,
				time: this.time
			};

			var t = this;

			client.addEvent(data).then(function () {
				if (!t.buses.includes(t.bus)) {
					t.buses.push(t.bus);
				}
				if (!t.stops.includes(t.stop)) {
					t.stops.push(t.stop);
				}
			}).catch(function (err) {
				alert(err);
			}).then(function () {
				_this2.sending = false;
			});
		}
	}
});