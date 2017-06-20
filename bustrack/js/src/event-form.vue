<template>
	<form v-on:submit.prevent="submit">
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
	</form>
</template>

<script>
import client from './client.js';
import InputAutocomplete from './input-autocomplete.vue';

export default {
	components: {'input-autocomplete': InputAutocomplete},

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
};
</script>
