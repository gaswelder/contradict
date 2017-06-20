import EventForm from './src/event-form.vue';
import EventLog from './src/event-log.vue';

Vue.component('app', {
	components: {
		'event-log': EventLog,
		'event-form': EventForm
	},

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
