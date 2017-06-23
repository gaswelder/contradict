<template>
	<main>
		<section v-for="(s, bus) in stats">
			<h3>{{bus}}</h3>

			<section v-for="(times, stop) in s">
				<h4>{{stop}}</h4>

				<table>
					<tr v-for="(t, day) in times">
						<td>{{day | dayOfWeek}}</td>
						<td>{{t | timesList}}</td>
					</tr>
				</table>
			</section>
		</section>
	</main>
</template>

<script>
import client from './client.js';

export default {
	data: function() {
		return {
			log: []
		};
	},

	computed: {
		stats() {
			let stats = {};
			this.log.forEach(function(entry) {
				let {bus, stop, time} = entry;
				if (!stats[bus]) {
					stats[bus] = {};
				}
				if (!stats[bus][stop]) {
					stats[bus][stop] = {};
				}

				let d = new Date(time * 1000);
				let day = d.getDay();
				if (!stats[bus][stop][day]) {
					stats[bus][stop][day] = [];
				}
				stats[bus][stop][day].push(d);
			});
			return stats;
		}
	},

	filters: {
		timesList(list) {
			function formatTime(d) {
				let h = d.getHours();
				let m = d.getMinutes();
				if (h < 10) h = '0' + h.toString();
				if (m < 10) m = '0' + m.toString();
				return h + ':' + m;
			}

			function cmp(a, b) {
				return a.replace(':', '') - b.replace(':', '');
			}
			return list.map(formatTime).sort(cmp).join(', ');
		},

		dayOfWeek(i) {
			return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][i];
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
