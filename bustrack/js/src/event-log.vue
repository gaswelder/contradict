<template>
	<main>
		<section v-for="(records, stopName) in stopInfo">
			<h3>{{stopName}}</h3>
			<stop-schedule v-bind:records="records"/>
		</section>
	</main>
</template>

<script>
import client from './client.js';
import StopSchedule from './stop-schedule.vue';

export default {
	data: function() {
		return {
			log: []
		};
	},

	components: {'stop-schedule': StopSchedule},

	computed: {
		stopInfo() {
			let stopInfo = {};

			this.log.forEach(function(entry) {
				let {bus, stop, time} = entry;
				if(!stopInfo[stop]) {
					stopInfo[stop] = [{bus, time}];
				} else {
					stopInfo[stop].push({bus, time});
				}
			});

			return stopInfo;
		}
	},

	created() {
		client.getLog()
			.then((log) => {
				this.log = log;
			})
	}
};
</script>
