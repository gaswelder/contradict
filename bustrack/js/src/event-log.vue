<template>
	<table>
		<tr v-for="entry in log">
			<td>{{entry.time | fmt}}</td>
			<td>{{entry.bus}}</td>
			<td>{{entry.stop}}</td>
		</tr>
	</table>
</template>

<script>
import client from './client.js';

export default {
	data: function() {
		return {
			log: []
		};
	},

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
};
</script>
