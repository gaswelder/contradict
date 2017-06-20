<template>
	<div>
		<input type="text" v-bind:list="id" v-bind:value="value" v-bind:required="required" v-on:input="update($event.target.value)">
		<datalist v-bind:id="id">
			<option v-for="option in options" v-bind:value="option"/>
		</datalist>
	</div>
</template>

<script>
export default {
	props: ['options', 'value', 'required'],

	data() {
		return {
			'id': 'id' + Date.now() + ':' + Math.random()
		};
	},

	methods: {
		update(newVal) {
			this.$emit('input', newVal);
		}
	}
};
</script>
