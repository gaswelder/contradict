<template>
	<main>
		<section v-for="(records, day) in daysInfo">
			<h4>{{day}}</h4>
			<stop-day-schedule v-bind:records="records"/>
		</section>
	</main>
</template>

<script>
import TimesList from './times-list.vue';

Vue.component('stop-day-schedule', {
	props: ['records'],
	components: {TimesList},

	template: `<table class="day-schedule">
		<tr v-for="(times, bus) in busInfo">
			<td>{{bus}}</td>
			<td><TimesList v-bind:times="times"/></td>
		</tr>
		</table>`,

	computed: {
		busInfo() {
			let info = {};
			this.records.forEach(function({bus, date}) {
				if(!info[bus]) info[bus] = [];
				info[bus].push(date);
			});
			return info;
		}
	}
});


export default {
	props: ['records'],

	computed: {
		daysInfo() {
			let days = {};

			function dayName(day) {
				if(day == 0 || day == 6) {
					return 'Sat, Sun';
				}
				return 'Mon-Fri';
			}

			this.records.forEach(function({bus, time}) {
				let date = new Date(time * 1000);
				let day = dayName(date.getDay());
				if(!days[day]) {
					days[day] = [];
				}
				days[day].push({bus, date});
			});
			return days;
		}
	},

	filters: {
		dayOfWeek(i) {
			return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][i];
		}
	}
};
</script>
