var client = {
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

Vue.component('input-autocomplete', {
	props: ['options', 'value', 'required'],

	data() {
		return {
			'id': 'id' + Date.now() + ':' + Math.random()
		};
	},

	template: `<div>
		<input type="text" v-bind:list="id" v-bind:value="value" v-bind:required="required" v-on:input="update($event.target.value)">
		<datalist v-bind:id="id">
			<option v-for="option in options" v-bind:value="option"/>
		</datalist>
	</div>`,

	methods: {
		update(newVal) {
			this.$emit('input', newVal);
		}
	}
});


Vue.component('event-form', {
	template: `<form v-on:submit.prevent="submit">
		<div class="input-field">
			<label>Bus</label>
			<input-autocomplete v-bind:options="buses" v-model="bus" required />
		</div>
		<div class="input-field">
			<label>Stop</label>
			<input-autocomplete v-bind:options="stops" v-model="stop" required />
		</div>
		<div class="input-field">
			<label>Time</label>
			<input type="text" readnoly v-bind:value="time">
		</div>
		<div>
			<button type="button" class="btn" v-on:click="setTime">Set current time</button>
		</div>
		<div class="fixed-action-btn">
			<button type="submit" v-bind:disabled="sending" class="btn-floating btn-large red waves-effect waves-light">Save</button>
		</div>
	</form>`,

	data: function() {
		return {
			bus: "",
			stop: "",
			time: new Date(),
			sending: false,
			buses: [],
			stops: []
		};
	},

	created() {
		client.init().then(x => {
			this.buses = x.buses;
			this.stops = x.stops;
		});
	},

	methods: {
		setTime() {
			this.time = new Date();
		},

		// Post the event on the server
		submit(e) {
			this.sending = true;

			var data = {
				bus: this.bus,
				stop: this.stop,
				time: this.time
			};

			var t = this;

			client.addEvent(data)
				.then(function() {
					// Add new bus and stop name to the autocompletion
					if (!t.buses.includes(t.bus)) {
						t.buses.push(t.bus);
					}
					if (!t.stops.includes(t.stop)) {
						t.stops.push(t.stop);
					}

					Materialize.toast('Sent', 2000);
				})
				.catch((err) => {
					alert(err);
				})
				.then(() => {
					this.sending = false;
				})
		}
	}
});

Vue.component('event-log', {
	data: function() {
		return {
			log: []
		};
	},

	template: `<table>
			<tr v-for="entry in log">
				<td>{{entry.time | fmt}}</td>
				<td>{{entry.bus}}</td>
				<td>{{entry.stop}}</td>
			</tr>
		</table>`,

	filters: {
		fmt(utc) {
			var d = new Date(utc * 1000);
			return d.toString();
		}
	},

	created() {
		client.getLog()
			.then((log) => {
				this.log = log;
			})
	}
});

Vue.component('app', {
	data: function() {
		return {
			page: 'form'
		}
	},

	template: `
	<div>
		<nav>
			<div class="nav-wrapper">
			  <ul id="nav-mobile" class="left">
				<li><a v-on:click="page = 'form'">Form</a></li>
				<li><a v-on:click="page = 'log'">Log</a></li>
			  </ul>
			</div>
		</nav>
		<div class="container">
			<event-form v-if="page == 'form'"/>
			<event-log v-else-if="page == 'log'"/>
		</div>
	</div>
	`
});

var app = new Vue({
	el: '#app',
});
