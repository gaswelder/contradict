<style scoped>
.app {
	display: flex;
	flex-direction: column;
	height: 100%;
}
.container {
	flex: 1 1 auto;
	overflow-y: scroll;
}
</style>
<template>
	<div class="app">
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
</template>

<script>
import EventForm from './event-form.vue';
import EventLog from './event-log.vue';

export default {
	components: {
		'event-log': EventLog,
		'event-form': EventForm
	},

	data() {
		return {
			page: 'form'
		}
	}
};
</script>
