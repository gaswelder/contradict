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
	},
	getLog: function getLog() {
		return fetch('events').then(function (response) {
			return response.json();
		});
	}
};

Vue.component('event-form', {
	template: '<form v-on:submit.prevent="submit">\n\t\t<div class="input-field">\n\t\t\t<label>Bus</label>\n\t\t\t<input v-model="bus" list="buses" type="text" required>\n\t\t\t<datalist id="buses">\n\t\t\t\t<option v-for="bus in buses" v-bind:value="bus"/>\n\t\t\t</datalist>\n\t\t</div>\n\t\t<div class="input-field">\n\t\t\t<label>Stop</label>\n\t\t\t<input type="text" v-model="stop" list="stops" required>\n\t\t\t<datalist id="stops">\n\t\t\t\t<option v-for="stop in stops" v-bind:value="stop"/>\n\t\t\t</datalist>\n\t\t</div>\n\t\t<div class="input-field">\n\t\t\t<label>Time</label>\n\t\t\t<input type="text" readnoly v-bind:value="time">\n\t\t</div>\n\t\t<div>\n\t\t\t<input type="checkbox" v-model="freeze" id="freeze-switch">\n\t\t\t<label for="freeze-switch">Freeze time</label>\n\t\t</div>\n\t\t<div class="fixed-action-btn">\n\t\t\t<button type="submit" v-bind:disabled="sending" class="btn-floating btn-large red waves-effect waves-light">Save</button>\n\t\t</div>\n\t</form>',

	data: function data() {
		return {
			bus: "",
			stop: "",
			time: new Date(),
			sending: false,
			buses: [],
			stops: [],
			freeze: false
		};
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


		// Post the event on the server
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
				// Add new bus and stop name to the autocompletion
				if (!t.buses.includes(t.bus)) {
					t.buses.push(t.bus);
				}
				if (!t.stops.includes(t.stop)) {
					t.stops.push(t.stop);
				}

				t.freeze = false;
				Materialize.toast('Sent', 2000);
			}).catch(function (err) {
				alert(err);
			}).then(function () {
				_this2.sending = false;
			});
		}
	}
});

Vue.component('event-log', {
	data: function data() {
		return {
			log: []
		};
	},

	template: '<table>\n\t\t\t<tr v-for="entry in log">\n\t\t\t\t<td>{{entry.time | fmt}}</td>\n\t\t\t\t<td>{{entry.bus}}</td>\n\t\t\t\t<td>{{entry.stop}}</td>\n\t\t\t</tr>\n\t\t</table>',

	filters: {
		fmt: function fmt(utc) {
			var d = new Date(utc * 1000);
			return d.toString();
		}
	},

	created: function created() {
		var _this3 = this;

		client.getLog().then(function (log) {
			_this3.log = log;
		});
	}
});

Vue.component('app', {
	data: function data() {
		return {
			page: 'form'
		};
	},

	template: '\n\t<div>\n\t\t<nav>\n\t\t\t<div class="nav-wrapper">\n\t\t\t  <ul id="nav-mobile" class="left">\n\t\t\t\t<li><a v-on:click="page = \'form\'">Form</a></li>\n\t\t\t\t<li><a v-on:click="page = \'log\'">Log</a></li>\n\t\t\t  </ul>\n\t\t\t</div>\n\t\t</nav>\n\t\t<div class="container">\n\t\t\t<event-form v-if="page == \'form\'"/>\n\t\t\t<event-log v-else-if="page == \'log\'"/>\n\t\t</div>\n\t</div>\n\t'
});

var app = new Vue({
	el: '#app'
});