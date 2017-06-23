<template>
	<table class="stop-schedule">
		<tr v-for="(records, day) in daysInfo">
			<td>{{day | dayOfWeek}}</td>
			<td><stop-day-schedule v-bind:records="records"/></td>
		</tr>
	</table>
</template>

<style>
.stop-schedule td {
	vertical-align: top;
}
</style>

<script>
import TimesList from './times-list.vue';

Vue.component('stop-day-schedule', {
	props: ['records'],
	components: {TimesList},

	template: `<table>
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
			this.records.forEach(function({bus, time}) {
				let date = new Date(time * 1000);
				let day = date.getDay();
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
